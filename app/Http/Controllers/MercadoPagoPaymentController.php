<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Services\BoletoSecondViaService;
use Illuminate\Support\Facades\Http;
use App\Models\SystemSetting;

class MercadoPagoPaymentController extends Controller
{
    protected $boletoService;
    
    public function __construct(BoletoSecondViaService $boletoService)
    {
        $this->boletoService = $boletoService;
    }
    
    /**
     * Gerar link direto de pagamento do Mercado Pago
     */
    public function generatePaymentLink(Request $request)
    {
        try {
            $request->validate([
                'payment_id' => 'required|integer|exists:payments,id',
                'email' => 'required|email'
            ]);
            
            $payment = Payment::with('matricula')->findOrFail($request->payment_id);
            
            // Verificar se o email corresponde à matrícula
            if ($payment->matricula->email !== $request->email) {
                return response()->json([
                    'error' => 'Email não autorizado para este pagamento'
                ], 403);
            }
            
            // Verificar se o pagamento está pendente (pode estar vencido ou não)
            if ($payment->status !== 'pending') {
                return response()->json([
                    'error' => 'Pagamento não está pendente (status: ' . $payment->status . ')'
                ], 400);
            }
            
            // Verificar se o pagamento não está muito vencido (mais de 30 dias)
            if ($payment->data_vencimento < now()->subDays(30)) {
                return response()->json([
                    'error' => 'Pagamento muito vencido (mais de 30 dias)'
                ], 400);
            }
            
            // Verificar se já existe um link válido no banco
            if ($payment->payment_link && $payment->payment_link_expires_at && $payment->payment_link_expires_at > now()) {
                $paymentLink = $payment->payment_link;
            } else {
                // Gerar novo link baseado na forma de pagamento
                $paymentLink = $this->generateMercadoPagoLink($payment);
                
                // Salvar o link no banco de dados
                $this->savePaymentLink($payment, $paymentLink);
            }
            
            return response()->json([
                'success' => true,
                'payment_link' => $paymentLink,
                'payment_info' => [
                    'id' => $payment->id,
                    'descricao' => $payment->descricao,
                    'valor' => $payment->valor,
                    'data_vencimento' => $payment->data_vencimento->format('d/m/Y'),
                    'forma_pagamento' => $payment->forma_pagamento,
                    'days_overdue' => now()->diffInDays($payment->data_vencimento)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao gerar link de pagamento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Gerar link específico do Mercado Pago baseado na forma de pagamento
     */
    protected function generateMercadoPagoLink(Payment $payment): string
    {
        // Usar as configurações corretas do Mercado Pago
        $isSandbox = SystemSetting::get('mercadopago_sandbox', false);
        $accessToken = $isSandbox 
            ? SystemSetting::get('mercadopago_sandbox_access_token')
            : SystemSetting::get('mercadopago_access_token');
        
        if (!$accessToken) {
            throw new \Exception('Token do Mercado Pago não configurado');
        }
        
        // Mapear formas de pagamento para os tipos corretos
        $paymentType = $this->mapPaymentType($payment->forma_pagamento);
        
        switch ($paymentType) {
            case 'boleto':
                return $this->generateBoletoLink($payment, $accessToken);
                
            case 'pix':
                return $this->generatePixLink($payment, $accessToken);
                
            case 'cartao':
                return $this->generateCardLink($payment, $accessToken);
                
            default:
                return $this->generateGenericLink($payment, $accessToken);
        }
    }
    
    /**
     * Mapear forma de pagamento para tipo do Mercado Pago
     */
    protected function mapPaymentType(string $formaPagamento): string
    {
        $mapping = [
            'boleto' => 'boleto',
            'cartao_credito' => 'cartao',
            'cartao_debito' => 'cartao',
            'cartao' => 'cartao',
            'pix' => 'pix',
            'pagamento_a_vista' => 'boleto', // Padrão para pagamento à vista
            'pagamento_parcelado' => 'cartao'
        ];
        
        return $mapping[$formaPagamento] ?? 'boleto';
    }
    
    /**
     * Gerar link para boleto
     */
    protected function generateBoletoLink(Payment $payment, string $accessToken): string
    {
        try {
            // Verificar se pode gerar segunda via
            if (!$this->boletoService->canGenerateSecondVia($payment)) {
                throw new \Exception('Pagamento não elegível para segunda via');
            }
            
            // Gerar nova via do boleto
            $boletoVia = $this->boletoService->generateSecondVia($payment);
            
            if ($boletoVia && $boletoVia->boleto_url) {
                return $boletoVia->boleto_url;
            }
            
            throw new \Exception('Não foi possível gerar o boleto');
            
        } catch (\Exception $e) {
            // Se falhar, criar link direto para geração
            return $this->createDirectBoletoLink($payment, $accessToken);
        }
    }
    
    /**
     * Criar link direto para boleto no Mercado Pago
     */
    protected function createDirectBoletoLink(Payment $payment, string $accessToken): string
    {
        try {
            // Preparar dados do pagador com endereço obrigatório para boleto
            $payerData = [
                'email' => $payment->matricula->email ?? 'teste@exemplo.com',
                'first_name' => explode(' ', $payment->matricula->nome_completo ?? 'Nome')[0],
                'last_name' => explode(' ', $payment->matricula->nome_completo ?? 'Sobrenome')[1] ?? 'Sobrenome',
                'identification' => [
                    'type' => 'CPF',
                    'number' => preg_replace('/[^0-9]/', '', $payment->matricula->cpf ?? '11111111111')
                ],
                'address' => [
                    'zip_code' => preg_replace('/[^0-9]/', '', $payment->matricula->cep ?? '01310100'),
                    'street_name' => $payment->matricula->logradouro ?? 'Av. Paulista',
                    'street_number' => $payment->matricula->numero ?? '1000',
                    'neighborhood' => $payment->matricula->bairro ?? 'Bela Vista',
                    'city' => $payment->matricula->cidade ?? 'São Paulo',
                    'state' => strtoupper(substr($payment->matricula->estado ?? 'SP', 0, 2))
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'boleto_' . $payment->id . '_' . time()
            ])->post('https://api.mercadopago.com/v1/orders', [
                'type' => 'online',
                'processing_mode' => 'automatic',
                'external_reference' => 'payment_' . $payment->id,
                'total_amount' => (string) $payment->valor, // API v1/orders usa valor direto, não centavos
                'description' => $payment->descricao ?? 'Pagamento de matrícula',
                'payer' => $payerData,
                'transactions' => [
                    'payments' => [
                        [
                            'amount' => (string) $payment->valor,
                            'payment_method' => [
                                'id' => 'boleto',
                                'type' => 'ticket'
                            ],
                            'expiration_time' => 'P3D' // 3 dias para vencimento
                        ]
                    ]
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Verificar se tem ticket_url na resposta principal ou nas transações
                $ticketUrl = $data['ticket_url'] ?? 
                            $data['transactions']['payments'][0]['payment_method']['ticket_url'] ?? 
                            null;
                
                if ($ticketUrl) {
                    return $ticketUrl;
                }
                
                throw new \Exception('ticket_url não encontrado na resposta: ' . json_encode($data));
            }
            
            throw new \Exception('Erro na API do Mercado Pago: ' . $response->status() . ' - ' . $response->body());
            
        } catch (\Exception $e) {
            \Log::error('Erro ao criar boleto no Mercado Pago', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            
            // Fallback para link de geração
            return config('app.url') . '/api/boleto/generate-second-via?payment_id=' . $payment->id;
        }
    }
    
    /**
     * Gerar link para PIX
     */
    protected function generatePixLink(Payment $payment, string $accessToken): string
    {
        try {
            $payerData = [
                'email' => $payment->matricula->email ?? 'teste@exemplo.com',
                'first_name' => explode(' ', $payment->matricula->nome_completo ?? 'Nome')[0],
                'last_name' => explode(' ', $payment->matricula->nome_completo ?? 'Sobrenome')[1] ?? 'Sobrenome',
                'identification' => [
                    'type' => 'CPF',
                    'number' => preg_replace('/[^0-9]/', '', $payment->matricula->cpf ?? '11111111111')
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'pix_' . $payment->id . '_' . time()
            ])->post('https://api.mercadopago.com/v1/orders', [
                'type' => 'online',
                'processing_mode' => 'automatic',
                'external_reference' => 'payment_' . $payment->id,
                'total_amount' => (string) $payment->valor, // API v1/orders usa valor direto, não centavos
                'description' => $payment->descricao ?? 'Pagamento de matrícula',
                'payer' => $payerData,
                'transactions' => [
                    'payments' => [
                        [
                            'amount' => (string) $payment->valor,
                            'payment_method' => [
                                'id' => 'pix',
                                'type' => 'bank_transfer'
                            ],
                            'expiration_time' => 'P1D' // 1 dia para vencimento
                        ]
                    ]
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Para PIX, verificar ticket_url ou qr_code
                $ticketUrl = $data['ticket_url'] ?? 
                            $data['transactions']['payments'][0]['payment_method']['ticket_url'] ?? 
                            null;
                
                if ($ticketUrl) {
                    return $ticketUrl;
                }
                
                throw new \Exception('ticket_url não encontrado na resposta PIX: ' . json_encode($data));
            }
            
            throw new \Exception('Erro na API do Mercado Pago PIX: ' . $response->status() . ' - ' . $response->body());
            
        } catch (\Exception $e) {
            \Log::error('Erro ao criar PIX no Mercado Pago', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            
            return config('app.url') . '/api/payment/generate-pix?payment_id=' . $payment->id;
        }
    }
    
    /**
     * Gerar link para cartão
     */
    protected function generateCardLink(Payment $payment, string $accessToken): string
    {
        try {
            // Para cartão, criar preferência de pagamento
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => [
                    [
                        'title' => 'Pagamento do Curso - ' . ($payment->matricula->inscricao->curso->nome ?? 'Curso'),
                        'quantity' => 1,
                        'unit_price' => (float) $payment->matricula->valor_total_curso // Usar valor total do curso
                    ]
                ],
                'payer' => [
                    'email' => $payment->matricula->email ?? 'teste@exemplo.com',
                    'name' => $payment->matricula->nome_completo ?? 'Nome Completo',
                    'identification' => [
                        'type' => 'CPF',
                        'number' => preg_replace('/[^0-9]/', '', $payment->matricula->cpf ?? '11111111111')
                    ]
                ],
                'external_reference' => 'payment_' . $payment->id,
                'notification_url' => route('webhook.mercadopago'),
                'back_urls' => [
                    'success' => config('app.url') . '/payment/success?payment_id=' . $payment->id,
                    'failure' => config('app.url') . '/payment/failure?payment_id=' . $payment->id,
                    'pending' => config('app.url') . '/payment/pending?payment_id=' . $payment->id
                ],
                'payment_methods' => [
                    'installments' => 12, // Máximo de 12 parcelas
                    'default_installments' => 12, // Padrão: 1 parcela (à vista)
                    'installments_cost' => 0, // Custo de juros zero para o comprador
                    'excluded_payment_methods' => [],
                    'excluded_payment_types' => []
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['init_point'])) {
                    return $data['init_point'];
                }
                
                throw new \Exception('init_point não encontrado na resposta: ' . json_encode($data));
            }
            
            throw new \Exception('Erro na API do Mercado Pago (Cartão): ' . $response->status() . ' - ' . $response->body());
            
        } catch (\Exception $e) {
            \Log::error('Erro ao criar preferência de cartão no Mercado Pago', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            
            return config('app.url') . '/payment/checkout/' . $payment->id;
        }
    }
    
    /**
     * Gerar link genérico
     */
    protected function generateGenericLink(Payment $payment, string $accessToken): string
    {
        // Para formas genéricas, tentar boleto como padrão
        return $this->generateBoletoLink($payment, $accessToken);
    }
    
    /**
     * Salvar link de pagamento no banco de dados
     */
    protected function savePaymentLink(Payment $payment, string $paymentLink): void
    {
        try {
            $paymentType = $this->mapPaymentType($payment->forma_pagamento);
            $expiresAt = now()->addDays(30); // Links expiram em 30 dias
            
            $updateData = [
                'payment_link' => $paymentLink,
                'payment_type' => $paymentType,
                'payment_link_expires_at' => $expiresAt
            ];
            
            // Adicionar campos específicos baseados no tipo
            switch ($paymentType) {
                case 'boleto':
                    $updateData['boleto_url'] = $paymentLink;
                    break;
                    
                case 'pix':
                    // Para PIX, extrair QR code se disponível
                    if (str_contains($paymentLink, 'ticket_url')) {
                        $updateData['pix_qr_code'] = $paymentLink;
                    }
                    break;
                    
                case 'cartao':
                    $updateData['init_point'] = $paymentLink;
                    break;
            }
            
            $payment->update($updateData);
            
        } catch (\Exception $e) {
            // Log do erro, mas não falhar o processo
            \Log::error('Erro ao salvar link de pagamento', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
