<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PopulatePaymentLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:populate-links {--force : Forçar regeneração de links existentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Popular links de pagamento do Mercado Pago para pagamentos existentes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Populando links de pagamento do Mercado Pago...');
        
        try {
            // Obter configurações do Mercado Pago
            $isSandbox = SystemSetting::get('mercadopago_sandbox', false);
            $accessToken = $isSandbox 
                ? SystemSetting::get('mercadopago_sandbox_access_token')
                : SystemSetting::get('mercadopago_access_token');
            
            if (!$accessToken) {
                $this->error('❌ Token do Mercado Pago não configurado');
                return 1;
            }
            
            $this->info('✅ Token do Mercado Pago configurado');
            $this->info('🌍 Ambiente: ' . ($isSandbox ? 'Sandbox' : 'Produção'));
            
            // Buscar pagamentos pendentes e vencidos
            $payments = Payment::with('matricula')
                ->where('status', 'pending')
                ->where('data_vencimento', '<', now())
                ->get();
            
            $this->info("📊 Encontrados {$payments->count()} pagamentos vencidos");
            
            $bar = $this->output->createProgressBar($payments->count());
            $bar->start();
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($payments as $payment) {
                try {
                    // Verificar se já tem link válido
                    if (!$this->option('force') && $payment->payment_link && $payment->payment_link_expires_at && $payment->payment_link_expires_at > now()) {
                        $bar->advance();
                        continue;
                    }
                    
                    // Gerar link baseado na forma de pagamento
                    $paymentLink = $this->generatePaymentLink($payment, $accessToken);
                    
                    $this->info("  📝 Payment {$payment->id}: {$payment->forma_pagamento} -> " . ($paymentLink ?: 'null'));
                    
                    if ($paymentLink) {
                        // Salvar no banco
                        $this->savePaymentLink($payment, $paymentLink);
                        $successCount++;
                        $this->info("  ✅ Link salvo com sucesso");
                    } else {
                        $this->warn("  ❌ Falha ao gerar link");
                    }
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->warn("⚠️ Erro no pagamento {$payment->id}: " . $e->getMessage());
                    Log::error('Erro ao gerar link para pagamento', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage()
                    ]);
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            
            $this->info("✅ Links gerados com sucesso: {$successCount}");
            if ($errorCount > 0) {
                $this->warn("⚠️ Erros encontrados: {$errorCount}");
            }
            
            $this->info('🎉 Processo concluído!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Gerar link de pagamento baseado na forma de pagamento
     */
    protected function generatePaymentLink(Payment $payment, string $accessToken): ?string
    {
        $paymentType = $this->mapPaymentType($payment->forma_pagamento);
        
        switch ($paymentType) {
            case 'boleto':
                return $this->generateBoletoLink($payment, $accessToken);
            case 'pix':
                return $this->generatePixLink($payment, $accessToken);
            case 'cartao':
                return $this->generateCardLink($payment, $accessToken);
            default:
                return $this->generateBoletoLink($payment, $accessToken);
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
            'pagamento_a_vista' => 'boleto',
            'pagamento_parcelado' => 'cartao'
        ];
        
        return $mapping[$formaPagamento] ?? 'boleto';
    }
    
    /**
     * Gerar link para boleto
     */
    protected function generateBoletoLink(Payment $payment, string $accessToken): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'boleto_' . $payment->id . '_' . time()
            ])->post('https://api.mercadopago.com/v1/orders', [
                'type' => 'online',
                'processing_mode' => 'automatic',
                'external_reference' => 'payment_' . $payment->id,
                'total_amount' => (string)($payment->valor * 100),
                'description' => $payment->descricao,
                'payer' => [
                    'email' => $payment->matricula->email,
                    'first_name' => explode(' ', $payment->matricula->nome_completo)[0] ?? '',
                    'last_name' => explode(' ', $payment->matricula->nome_completo)[1] ?? '',
                    'identification' => [
                        'type' => 'CPF',
                        'number' => $payment->matricula->cpf ?? '00000000000'
                    ],
                    'address' => [
                        'zip_code' => '00000000',
                        'street_name' => 'Rua Teste',
                        'street_number' => '123',
                        'neighborhood' => 'Centro',
                        'city' => 'São Paulo',
                        'state' => 'SP'
                    ]
                ],
                'transactions' => [
                    'payments' => [
                        [
                            'amount' => (string)($payment->valor * 100),
                            'payment_method' => [
                                'id' => 'boleto',
                                'type' => 'ticket'
                            ],
                            'expiration_time' => 'P30D' // 30 dias
                        ]
                    ]
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['ticket_url'] ?? null;
            }
            
            return null;
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Gerar link para PIX
     */
    protected function generatePixLink(Payment $payment, string $accessToken): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => 'pix_' . $payment->id . '_' . time()
            ])->post('https://api.mercadopago.com/v1/orders', [
                'type' => 'online',
                'processing_mode' => 'automatic',
                'external_reference' => 'payment_' . $payment->id,
                'total_amount' => (string)($payment->valor * 100),
                'description' => $payment->descricao,
                'payer' => [
                    'email' => $payment->matricula->email,
                    'first_name' => explode(' ', $payment->matricula->nome_completo)[0] ?? '',
                    'last_name' => explode(' ', $payment->matricula->nome_completo)[1] ?? '',
                    'identification' => [
                        'type' => 'CPF',
                        'number' => $payment->matricula->cpf ?? '00000000000'
                    ],
                    'address' => [
                        'zip_code' => '00000000',
                        'street_name' => 'Rua Teste',
                        'street_number' => '123',
                        'neighborhood' => 'Centro',
                        'city' => 'São Paulo',
                        'state' => 'SP'
                    ]
                ],
                'transactions' => [
                    'payments' => [
                        [
                            'amount' => (string)($payment->valor * 100),
                            'payment_method' => [
                                'id' => 'pix',
                                'type' => 'bank_transfer'
                            ],
                            'expiration_time' => 'P1D' // 1 dia
                        ]
                    ]
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['ticket_url'] ?? null;
            }
            
            return null;
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Gerar link para cartão
     */
    protected function generateCardLink(Payment $payment, string $accessToken): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => [
                    [
                        'title' => $payment->descricao,
                        'quantity' => 1,
                        'unit_price' => $payment->valor
                    ]
                ],
                'payer' => [
                    'email' => $payment->matricula->email,
                    'name' => $payment->matricula->nome_completo
                ],
                'external_reference' => 'payment_' . $payment->id,
                'notification_url' => config('app.url') . '/api/webhooks/mercadopago',
                'back_urls' => [
                    'success' => config('app.url') . '/payment/success',
                    'failure' => config('app.url') . '/payment/failure',
                    'pending' => config('app.url') . '/payment/pending'
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['init_point'] ?? null;
            }
            
            return null;
            
        } catch (\Exception $e) {
            return null;
        }
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
                    $updateData['pix_qr_code'] = $paymentLink;
                    break;
                    
                case 'cartao':
                    $updateData['init_point'] = $paymentLink;
                    break;
            }
            
            $payment->update($updateData);
            
        } catch (\Exception $e) {
            Log::error('Erro ao salvar link de pagamento', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
