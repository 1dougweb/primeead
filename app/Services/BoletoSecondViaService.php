<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\BoletoVia;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class BoletoSecondViaService
{
    protected $mercadopagoAccessToken;
    protected $mercadopagoSandbox;

    public function __construct()
    {
        $paymentSettings = SystemSetting::getPaymentSettings();
        $this->mercadopagoAccessToken = $paymentSettings['mercadopago_sandbox'] 
            ? $paymentSettings['mercadopago_sandbox_access_token']
            : $paymentSettings['mercadopago_access_token'];
        $this->mercadopagoSandbox = $paymentSettings['mercadopago_sandbox'];
    }

    /**
     * Verificar se pode gerar segunda via
     */
    public function canGenerateSecondVia(Payment $payment): array
    {
        $checks = [
            'payment_status' => $payment->status !== 'paid',
            'not_expired' => $payment->data_vencimento->isFuture(),
            'can_generate' => $payment->can_generate_second_via,
            'within_limit' => $payment->boleto_vias_count < $payment->max_boleto_vias,
            'has_matricula' => $payment->matricula_id !== null
        ];

        $canGenerate = !in_array(false, $checks, true);
        $reasons = [];

        if (!$checks['payment_status']) {
            $reasons[] = 'Pagamento já foi realizado';
        }
        if (!$checks['not_expired']) {
            $reasons[] = 'Boleto já venceu';
        }
        if (!$checks['can_generate']) {
            $reasons[] = 'Segunda via não permitida para este pagamento';
        }
        if (!$checks['within_limit']) {
            $reasons[] = 'Limite de vias atingido (máximo ' . $payment->max_boleto_vias . ')';
        }
        if (!$checks['has_matricula']) {
            $reasons[] = 'Pagamento não está vinculado a uma matrícula';
        }

        return [
            'can_generate' => $canGenerate,
            'checks' => $checks,
            'reasons' => $reasons,
            'current_vias' => $payment->boleto_vias_count,
            'max_vias' => $payment->max_boleto_vias
        ];
    }

    /**
     * Gerar segunda via do boleto
     */
    public function generateSecondVia(Payment $payment, ?Carbon $newDueDate = null): array
    {
        // Verificar se pode gerar
        $validation = $this->canGenerateSecondVia($payment);
        if (!$validation['can_generate']) {
            throw new Exception('Não é possível gerar segunda via: ' . implode(', ', $validation['reasons']));
        }

        DB::beginTransaction();
        
        try {
            // Cancelar vias ativas anteriores
            $this->cancelActiveVias($payment);
            
            // Gerar nova via via Mercado Pago
            $boletoData = $this->generateBoletoViaMercadoPago($payment, $newDueDate);
            
            // Salvar nova via
            $boletoVia = $this->saveBoletoVia($payment, $boletoData);
            
            // Atualizar contador de vias
            $payment->increment('boleto_vias_count');
            $payment->update([
                'last_boleto_generated_at' => now(),
                'boleto_history' => $this->updateBoletoHistory($payment, $boletoVia)
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'boleto_via' => $boletoVia,
                'boleto_data' => $boletoData,
                'via_number' => $boletoVia->via_number,
                'message' => 'Segunda via gerada com sucesso!'
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao gerar segunda via do boleto', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new Exception('Erro ao gerar segunda via: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar vias ativas anteriores
     */
    protected function cancelActiveVias(Payment $payment): void
    {
        BoletoVia::where('payment_id', $payment->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);
    }

    /**
     * Gerar boleto via Mercado Pago
     */
    protected function generateBoletoViaMercadoPago(Payment $payment, ?Carbon $newDueDate = null): array
    {
        $dueDate = $newDueDate ?: $payment->data_vencimento;
        
        // Dados do pagador
        $payer = [
            'email' => $payment->matricula->email ?? 'contato@ensinocerto.com.br',
            'first_name' => $payment->matricula->nome_completo ?? 'Aluno',
            'last_name' => '',
            'identification' => [
                'type' => 'CPF',
                'number' => $payment->matricula->cpf ?? '00000000000'
            ]
        ];

        // Dados do endereço (se disponível)
        if ($payment->matricula->endereco) {
            $payer['address'] = [
                'zip_code' => $payment->matricula->cep ?? '00000000',
                'street_name' => $payment->matricula->endereco ?? 'Rua não informada',
                'street_number' => $payment->matricula->numero ?? '0',
                'neighborhood' => $payment->matricula->bairro ?? 'Bairro não informado',
                'city' => $payment->matricula->cidade ?? 'Cidade não informada',
                'state' => $payment->matricula->estado ?? 'SP'
            ];
        }

        // Dados da ordem
        $orderData = [
            'type' => 'online',
            'processing_mode' => 'automatic',
            'external_reference' => 'PAY_' . $payment->id . '_VIA_' . ($payment->boleto_vias_count + 1),
            'total_amount' => $payment->valor * 100, // Mercado Pago usa centavos
            'description' => $payment->descricao . ' - ' . ($payment->boleto_vias_count + 1) . 'ª via',
            'payer' => $payer,
            'transactions' => [
                [
                    'payments' => [
                        [
                            'payment_method' => [
                                'id' => 'boleto',
                                'type' => 'ticket'
                            ],
                            'expiration_time' => $dueDate->addDays(3)->toISOString()
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->mercadopagoAccessToken,
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => uniqid('boleto_', true)
                ])
                ->post('https://api.mercadopago.com/v1/orders', $orderData);

            if ($response->successful()) {
                $data = $response->json();
                
                // Extrair dados do boleto
                $boletoData = $this->extractBoletoData($data);
                
                return $boletoData;
            } else {
                throw new Exception('Erro na API do Mercado Pago: ' . $response->status() . ' - ' . $response->body());
            }
            
        } catch (Exception $e) {
            Log::error('Erro ao gerar boleto via Mercado Pago', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            
            throw new Exception('Erro ao gerar boleto: ' . $e->getMessage());
        }
    }

    /**
     * Extrair dados do boleto da resposta do Mercado Pago
     */
    protected function extractBoletoData(array $response): array
    {
        $boletoData = [];
        
        if (isset($response['transactions'][0]['payments'][0])) {
            $payment = $response['transactions'][0]['payments'][0];
            
            $boletoData = [
                'boleto_url' => $payment['ticket_url'] ?? null,
                'digitable_line' => $payment['digitable_line'] ?? null,
                'barcode_content' => $payment['barcode_content'] ?? null,
                'financial_institution' => $payment['financial_institution'] ?? null,
                'expires_at' => isset($payment['expiration_time']) 
                    ? $this->parseExpirationTime($payment['expiration_time']) 
                    : null,
                'order_id' => $response['id'] ?? null,
                'payment_id' => $payment['id'] ?? null
            ];
        }
        
        return $boletoData;
    }

    /**
     * Salvar nova via do boleto
     */
    protected function saveBoletoVia(Payment $payment, array $boletoData): BoletoVia
    {
        $viaNumber = $payment->boleto_vias_count + 1;
        
        return BoletoVia::create([
            'payment_id' => $payment->id,
            'via_number' => $viaNumber,
            'generated_at' => now(),
            'expires_at' => $boletoData['expires_at'],
            'boleto_url' => $boletoData['boleto_url'],
            'digitable_line' => $boletoData['digitable_line'],
            'barcode_content' => $boletoData['barcode_content'],
            'financial_institution' => $boletoData['financial_institution'],
            'status' => 'active',
            'metadata' => [
                'order_id' => $boletoData['order_id'],
                'payment_id' => $boletoData['payment_id'],
                'generated_by' => 'system',
                'previous_vias_cancelled' => true
            ]
        ]);
    }

    /**
     * Atualizar histórico de boletos
     */
    protected function updateBoletoHistory(Payment $payment, BoletoVia $boletoVia): array
    {
        $history = $payment->boleto_history ?? [];
        
        $history[] = [
            'via_id' => $boletoVia->id,
            'via_number' => $boletoVia->via_number,
            'generated_at' => $boletoVia->generated_at->toISOString(),
            'status' => $boletoVia->status,
            'boleto_url' => $boletoVia->boleto_url
        ];
        
        return $history;
    }

    /**
     * Obter histórico de vias de um pagamento
     */
    public function getBoletoViasHistory(Payment $payment): array
    {
        $vias = BoletoVia::where('payment_id', $payment->id)
            ->orderBy('via_number', 'desc')
            ->get();
            
        return $vias->map(function($via) {
            return [
                'id' => $via->id,
                'via_number' => $via->via_number,
                'via_number_formatted' => $via->via_number_formatted,
                'generated_at' => $via->generated_at_formatted,
                'expires_at' => $via->expires_at_formatted,
                'status' => $via->status,
                'status_formatted' => $via->status_formatted,
                'boleto_url' => $via->boleto_url,
                'digitable_line' => $via->digitable_line,
                'barcode_content' => $via->barcode_content,
                'financial_institution' => $via->financial_institution,
                'is_active' => $via->isActive(),
                'is_expired' => $via->isExpired(),
                'is_paid' => $via->isPaid(),
                'is_cancelled' => $via->isCancelled()
            ];
        })->toArray();
    }

    /**
     * Obter estatísticas de vias de boleto
     */
    public function getBoletoViasStats(): array
    {
        $totalVias = BoletoVia::count();
        $activeVias = BoletoVia::where('status', 'active')->count();
        $expiredVias = BoletoVia::where('status', 'expired')->count();
        $paidVias = BoletoVia::where('status', 'paid')->count();
        $cancelledVias = BoletoVia::where('status', 'cancelled')->count();
        
        return [
            'total_vias' => $totalVias,
            'active_vias' => $activeVias,
            'expired_vias' => $expiredVias,
            'paid_vias' => $paidVias,
            'cancelled_vias' => $cancelledVias,
            'success_rate' => $totalVias > 0 ? round(($paidVias / $totalVias) * 100, 2) : 0
        ];
    }

    /**
     * Parse expiration time from Mercado Pago response
     * Handles both ISO 8601 duration strings (P3D) and datetime strings
     */
    private function parseExpirationTime($expirationTime)
    {
        try {
            // Check if it's an ISO 8601 duration string (starts with P)
            if (is_string($expirationTime) && str_starts_with($expirationTime, 'P')) {
                // Convert duration string to actual expiration date
                $interval = new \DateInterval($expirationTime);
                return now()->add($interval);
            }
            
            // Try to parse as a regular datetime string
            return Carbon::parse($expirationTime);
            
        } catch (\Exception $e) {
            // Log the error and return a default expiration (3 days from now)
            \Log::warning('Failed to parse expiration_time from Mercado Pago', [
                'expiration_time' => $expirationTime,
                'error' => $e->getMessage()
            ]);
            
            return now()->addDays(3);
        }
    }
}
