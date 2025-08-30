<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\PaymentSchedule;
use App\Models\Contact;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Exceptions\MPApiException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MercadoPagoService
{
    protected $paymentSettings;
    protected $paymentClient;
    protected $preApprovalClient;
    protected $preferenceClient;

    public function __construct()
    {
        // Verificar se estamos em contexto de migração ou teste
        if ($this->isInMigrationContext()) {
            $this->paymentSettings = $this->getDefaultSettings();
            return;
        }

        try {
            $this->paymentSettings = SystemSetting::getPaymentSettings();
            
            if ($this->paymentSettings['mercadopago_enabled'] && !empty($this->paymentSettings['mercadopago_access_token'])) {
                $this->initializeMercadoPago();
            }
        } catch (\Exception $e) {
            // Se falhar ao acessar configurações (ex: durante migrações), usar configurações padrão
            Log::warning('Erro ao carregar configurações do MercadoPago, usando configurações padrão: ' . $e->getMessage());
            $this->paymentSettings = $this->getDefaultSettings();
        }
    }

    /**
     * Verificar se estamos em contexto de migração ou comando artisan
     */
    private function isInMigrationContext(): bool
    {
        // Verificar se estamos rodando via linha de comando
        if (app()->runningInConsole()) {
            $command = $_SERVER['argv'][1] ?? '';
            
            // Lista de comandos que não devem tentar acessar o banco
            $migrationCommands = [
                'migrate',
                'migrate:fresh',
                'migrate:refresh',
                'migrate:reset',
                'migrate:rollback',
                'migrate:status',
                'db:seed',
                'db:wipe',
                'config:cache',
                'config:clear',
                'cache:clear',
                'route:cache',
                'route:clear',
                'view:cache',
                'view:clear',
                'optimize',
                'optimize:clear'
            ];
            
            foreach ($migrationCommands as $migrationCommand) {
                if (str_contains($command, $migrationCommand)) {
                    return true;
                }
            }
        }
        
        // Verificar se estamos em ambiente de teste
        if (app()->environment('testing')) {
            return true;
        }
        
        return false;
    }

    /**
     * Obter configurações padrão quando não é possível acessar o banco
     */
    private function getDefaultSettings(): array
    {
        return [
            'mercadopago_enabled' => false,
            'mercadopago_sandbox' => true,
            'mercadopago_access_token' => '',
            'mercadopago_public_key' => '',
            'mercadopago_webhook_secret' => '',
            'mercadopago_sandbox_access_token' => '',
            'mercadopago_sandbox_public_key' => '',
            'mercadopago_email_notifications' => true,
            'mercadopago_whatsapp_notifications' => false,
            'mercadopago_sms_notifications' => false,
            'mercadopago_auto_reminders' => true,
            'mercadopago_auto_generation' => true,
            'mercadopago_currency' => 'BRL',
            'mercadopago_country' => 'BR',
            'payment_auto_charge' => true,
            'payment_email_notifications' => true,
            'payment_whatsapp_notifications' => true,
            'payment_reminder_days' => 3,
            'payment_overdue_days' => 7,
        ];
    }

    private function initializeMercadoPago()
    {
        try {
            // Determinar qual token usar baseado no modo sandbox
            $accessToken = $this->getAccessToken();
            
            if (empty($accessToken)) {
                throw new \Exception('Token de acesso não configurado para o modo atual');
            }
            
            MercadoPagoConfig::setAccessToken($accessToken);
            
            // Usar setRuntimeEnviroment ao invés de setEnvironment
            if ($this->paymentSettings['mercadopago_sandbox']) {
                MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
            } else {
                MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);
            }

            $this->paymentClient = new PaymentClient();
            $this->preApprovalClient = new PreApprovalClient();
            $this->preferenceClient = new PreferenceClient();
            
        } catch (\Exception $e) {
            Log::error('Erro ao inicializar MercadoPago: ' . $e->getMessage());
            throw new \Exception('Erro na configuração do Mercado Pago: ' . $e->getMessage());
        }
    }

    /**
     * Obter o token de acesso correto baseado no modo sandbox
     */
    public function getAccessToken(): string
    {
        if ($this->paymentSettings['mercadopago_sandbox']) {
            // Se sandbox está ativo, usar token sandbox específico ou fallback para token de produção
            return $this->paymentSettings['mercadopago_sandbox_access_token'] 
                ?: $this->paymentSettings['mercadopago_access_token'];
        } else {
            // Se produção, usar token de produção (campo existente)
            return $this->paymentSettings['mercadopago_access_token'];
        }
    }

    /**
     * Obter a chave pública correta baseada no modo sandbox
     */
    public function getPublicKey(): string
    {
        if ($this->paymentSettings['mercadopago_sandbox']) {
            // Se sandbox está ativo, usar chave sandbox específica ou fallback para chave de produção
            return $this->paymentSettings['mercadopago_sandbox_public_key'] 
                ?: $this->paymentSettings['mercadopago_public_key'];
        } else {
            // Se produção, usar chave de produção (campo existente)
            return $this->paymentSettings['mercadopago_public_key'];
        }
    }

    /**
     * Verificar se está no modo sandbox
     */
    public function isSandbox(): bool
    {
        return $this->paymentSettings['mercadopago_sandbox'] ?? false;
    }

    /**
     * Criar pagamento único
     */
    public function createSinglePayment(Contact $contact, PaymentPlan $plan, array $options = [])
    {
        if (!$this->paymentSettings['mercadopago_enabled']) {
            throw new \Exception('Mercado Pago não está habilitado');
        }

        try {
            $payment = Payment::create([
                'contact_id' => $contact->id,
                'payment_plan_id' => $plan->id,
                'amount' => $plan->amount + $plan->setup_fee,
                'description' => $plan->description ?? $plan->name,
                'due_date' => now()->addDays(7), // 7 dias para pagamento
                'status' => 'pending'
            ]);

            $paymentData = [
                'transaction_amount' => (float) $payment->amount,
                'description' => $payment->description,
                'payment_method_id' => $options['payment_method_id'] ?? 'pix',
                'payer' => [
                    'email' => $contact->email,
                    'first_name' => $contact->nome,
                    'identification' => [
                        'type' => 'CPF',
                        'number' => $this->cleanCpf($contact->cpf ?? '')
                    ]
                ],
                'external_reference' => "payment_{$payment->id}",
                'notification_url' => route('webhook.mercadopago'),
                'metadata' => [
                    'payment_id' => $payment->id,
                    'contact_id' => $contact->id,
                    'plan_id' => $plan->id
                ]
            ];

            $mpPayment = $this->paymentClient->create($paymentData);
            
            $payment->update([
                'mercadopago_payment_id' => $mpPayment->id,
                'mercadopago_data' => $mpPayment->jsonSerialize()
            ]);

            Log::info('Pagamento criado no Mercado Pago', [
                'payment_id' => $payment->id,
                'mp_payment_id' => $mpPayment->id
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'mercadopago_payment' => $mpPayment
            ];

        } catch (MPApiException $e) {
            Log::error('Erro na API do Mercado Pago: ' . $e->getMessage(), [
                'contact_id' => $contact->id,
                'plan_id' => $plan->id
            ]);
            
            return [
                'success' => false,
                'error' => 'Erro na criação do pagamento: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Criar assinatura recorrente
     */
    public function createSubscription(Contact $contact, PaymentPlan $plan, array $options = [])
    {
        if (!$this->paymentSettings['mercadopago_enabled']) {
            throw new \Exception('Mercado Pago não está habilitado');
        }

        try {
            $schedule = PaymentSchedule::create([
                'contact_id' => $contact->id,
                'payment_plan_id' => $plan->id,
                'start_date' => now(),
                'next_payment_date' => now()->addDays($plan->trial_period_days ?: 0),
                'total_payments' => $plan->installments > 1 ? $plan->installments : null,
                'auto_charge' => $options['auto_charge'] ?? true,
                'email_notifications' => $options['email_notifications'] ?? true,
                'whatsapp_notifications' => $options['whatsapp_notifications'] ?? true,
                'days_before_due' => $options['days_before_due'] ?? 3,
                'status' => 'active'
            ]);

            $preApprovalData = [
                'reason' => $plan->name,
                'external_reference' => "subscription_{$schedule->id}",
                'payer_email' => $contact->email,
                'back_url' => route('payments.success'),
                'auto_recurring' => [
                    'frequency' => 1,
                    'frequency_type' => $this->getFrequencyType($plan->frequency),
                    'transaction_amount' => (float) $plan->amount,
                    'currency_id' => 'BRL',
                    'repetitions' => $plan->installments > 1 ? $plan->installments : null,
                    'billing_day' => now()->day,
                    'billing_day_proportional' => false,
                    'free_trial' => $plan->trial_period_days > 0 ? [
                        'frequency' => $plan->trial_period_days,
                        'frequency_type' => 'days'
                    ] : null
                ],
                'notification_url' => route('webhook.mercadopago'),
                'metadata' => [
                    'schedule_id' => $schedule->id,
                    'contact_id' => $contact->id,
                    'plan_id' => $plan->id
                ]
            ];

            $preApproval = $this->preApprovalClient->create($preApprovalData);
            
            $schedule->update([
                'metadata' => [
                    'mercadopago_subscription_id' => $preApproval->id,
                    'init_point' => $preApproval->init_point
                ]
            ]);

            Log::info('Assinatura criada no Mercado Pago', [
                'schedule_id' => $schedule->id,
                'mp_subscription_id' => $preApproval->id
            ]);

            return [
                'success' => true,
                'schedule' => $schedule,
                'subscription' => $preApproval,
                'init_point' => $preApproval->init_point
            ];

        } catch (MPApiException $e) {
            Log::error('Erro na criação da assinatura: ' . $e->getMessage(), [
                'contact_id' => $contact->id,
                'plan_id' => $plan->id
            ]);
            
            return [
                'success' => false,
                'error' => 'Erro na criação da assinatura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Processar webhook do Mercado Pago
     */
    public function processWebhook(array $data)
    {
        try {
            Log::info('Webhook recebido do Mercado Pago', $data);

            $type = $data['type'] ?? null;
            $dataId = $data['data']['id'] ?? null;

            if (!$type || !$dataId) {
                Log::warning('Webhook inválido: tipo ou ID não fornecido');
                return false;
            }

            switch ($type) {
                case 'payment':
                    return $this->processPaymentWebhook($dataId);
                case 'subscription_preapproval':
                    return $this->processSubscriptionWebhook($dataId);
                default:
                    Log::info('Tipo de webhook não processado: ' . $type);
                    return true;
            }

        } catch (\Exception $e) {
            Log::error('Erro no processamento do webhook: ' . $e->getMessage());
            return false;
        }
    }

    private function processPaymentWebhook($paymentId)
    {
        try {
            $mpPayment = $this->paymentClient->get($paymentId);
            $externalReference = $mpPayment->external_reference;
            
            if (!$externalReference || !str_starts_with($externalReference, 'payment_')) {
                Log::warning('Referência externa inválida: ' . $externalReference);
                return false;
            }

            $localPaymentId = str_replace('payment_', '', $externalReference);
            $payment = Payment::find($localPaymentId);

            if (!$payment) {
                Log::warning('Pagamento local não encontrado: ' . $localPaymentId);
                return false;
            }

            $oldStatus = $payment->status;
            $newStatus = $this->mapMercadoPagoStatus($mpPayment->status);

            $payment->update([
                'status' => $newStatus,
                'payment_method' => $mpPayment->payment_method_id,
                'paid_at' => $mpPayment->status === 'approved' ? now() : null,
                'mercadopago_data' => $mpPayment->jsonSerialize()
            ]);

            // Se o pagamento foi aprovado, atualizar o schedule se existir
            if ($newStatus === 'approved' && $payment->paymentPlan) {
                $schedule = PaymentSchedule::where('contact_id', $payment->contact_id)
                    ->where('payment_plan_id', $payment->payment_plan_id)
                    ->where('status', 'active')
                    ->first();

                if ($schedule) {
                    $schedule->updateNextPaymentDate();
                    $schedule->checkCompletion();
                }
            }

            Log::info('Pagamento atualizado via webhook', [
                'payment_id' => $payment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Erro no processamento do webhook de pagamento: ' . $e->getMessage());
            return false;
        }
    }

    private function processSubscriptionWebhook($subscriptionId)
    {
        try {
            $subscription = $this->preApprovalClient->get($subscriptionId);
            $externalReference = $subscription->external_reference;
            
            if (!$externalReference || !str_starts_with($externalReference, 'subscription_')) {
                Log::warning('Referência externa de assinatura inválida: ' . $externalReference);
                return false;
            }

            $scheduleId = str_replace('subscription_', '', $externalReference);
            $schedule = PaymentSchedule::find($scheduleId);

            if (!$schedule) {
                Log::warning('Schedule não encontrado: ' . $scheduleId);
                return false;
            }

            $oldStatus = $schedule->status;
            $newStatus = $this->mapSubscriptionStatus($subscription->status);

            $schedule->update([
                'status' => $newStatus,
                'metadata' => array_merge($schedule->metadata ?? [], [
                    'mercadopago_subscription_data' => $subscription->jsonSerialize()
                ])
            ]);

            Log::info('Assinatura atualizada via webhook', [
                'schedule_id' => $schedule->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Erro no processamento do webhook de assinatura: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Métodos auxiliares
     */
    private function getFrequencyType($frequency)
    {
        return match($frequency) {
            'monthly' => 'months',
            'quarterly' => 'months', // 3 meses
            'semester' => 'months', // 6 meses
            'annual' => 'years',
            default => 'months'
        };
    }

    private function mapMercadoPagoStatus($mpStatus)
    {
        return match($mpStatus) {
            'approved' => 'approved',
            'pending' => 'pending',
            'rejected' => 'rejected',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'charged_back' => 'charged_back',
            default => 'pending'
        };
    }

    private function mapSubscriptionStatus($mpStatus)
    {
        return match($mpStatus) {
            'authorized' => 'active',
            'paused' => 'paused',
            'cancelled' => 'cancelled',
            'finished' => 'completed',
            default => 'active'
        };
    }

    private function cleanCpf($cpf)
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }

    /**
     * Limpar CEP removendo caracteres especiais
     */
    private function cleanCep($cep)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $cep);
        return str_pad($cleaned, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar se o serviço está configurado
     */
    public function isConfigured()
    {
        return $this->paymentSettings['mercadopago_enabled'] && 
               !empty($this->getAccessToken());
    }

    /**
     * Testar conexão com Mercado Pago
     */
    public function testConnection($accessToken = null, $sandbox = null)
    {
        // Se não foram fornecidos parâmetros, usar configuração atual
        if (!$accessToken) {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Mercado Pago não está configurado'
                ];
            }
            $accessToken = $this->getAccessToken();
            $sandbox = $this->paymentSettings['mercadopago_sandbox'];
        }

        try {
            // Definir configurações do MercadoPago
            \MercadoPago\MercadoPagoConfig::setAccessToken($accessToken);
            
            // Definir ambiente de runtime (LOCAL para sandbox, SERVER para produção)
            if ($sandbox) {
                \MercadoPago\MercadoPagoConfig::setRuntimeEnviroment(\MercadoPago\MercadoPagoConfig::LOCAL);
            } else {
                \MercadoPago\MercadoPagoConfig::setRuntimeEnviroment(\MercadoPago\MercadoPagoConfig::SERVER);
            }
            
            // Criar cliente de teste
            $testClient = new \MercadoPago\Client\Payment\PaymentClient();
            
            // Tentar criar um request de busca simples para testar a conexão
            $searchRequest = new \MercadoPago\Net\MPSearchRequest(1, 0);
            $response = $testClient->search($searchRequest);
            
            return [
                'success' => true,
                'message' => 'Conexão com Mercado Pago estabelecida com sucesso',
                'data' => [
                    'environment' => $sandbox ? 'sandbox' : 'production',
                    'access_token' => substr($accessToken, 0, 10) . '...'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro na conexão: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Criar pagamento para matrícula usando a nova API v1/orders
     */
    public function createPayment(Payment $payment)
    {
        if (!$this->paymentSettings['mercadopago_enabled']) {
            throw new \Exception('Mercado Pago não está habilitado');
        }

        try {
            $matricula = $payment->matricula;
            
            if (!$matricula) {
                throw new \Exception('Matrícula não encontrada para o pagamento');
            }

            // Preparar dados do pagador
            $payerData = [
                'email' => $matricula->email ?: 'teste@example.com',
                'first_name' => $matricula->nome_completo ?: 'Nome',
                'last_name' => 'Sobrenome',
                'identification' => [
                    'type' => 'CPF',
                    'number' => $this->cleanCpf($matricula->cpf ?: '11111111111')
                ]
            ];

            // Adicionar endereço para boleto (obrigatório)
            if ($payment->forma_pagamento === 'boleto') {
                $payerData['address'] = [
                    'zip_code' => $this->cleanCep($matricula->cep ?: '01310100'),
                    'street_name' => $matricula->logradouro ?: 'Av. Paulista',
                    'street_number' => $matricula->numero ?: '1000',
                    'neighborhood' => $matricula->bairro ?: 'Bela Vista',
                    'city' => $matricula->cidade ?: 'São Paulo',
                    'state' => $matricula->estado ?: 'SP'
                ];
            }

            // Preparar dados da transação
            $transactionData = [
                'payments' => [
                    [
                        'amount' => (string) $payment->valor,
                        'payment_method' => [
                            'id' => $this->getPaymentMethodId($payment->forma_pagamento),
                            'type' => $this->getPaymentMethodType($payment->forma_pagamento)
                        ]
                    ]
                ]
            ];

            // Para cartão de crédito, usar uma abordagem diferente
            if ($payment->forma_pagamento === 'cartao_credito') {
                // Para cartão de crédito, vamos usar a API de preference em vez de orders
                // pois orders requer token e installments que não temos
                $result = $this->createCardPreference($payment);
                
                // Log para debug
                Log::info('Resultado do createCardPreference', [
                    'payment_id' => $payment->id,
                    'result' => $result,
                    'has_full_response' => isset($result['full_response']),
                    'has_init_point' => isset($result['full_response']['init_point'])
                ]);
                
                // Processar a resposta para salvar o init_point
                if (isset($result['full_response']['init_point'])) {
                    Log::info('Salvando init_point', [
                        'payment_id' => $payment->id,
                        'init_point' => $result['full_response']['init_point']
                    ]);
                    $payment->init_point = $result['full_response']['init_point'];
                    $payment->save();
                } else {
                    Log::warning('init_point não encontrado no resultado', [
                        'payment_id' => $payment->id,
                        'result_keys' => array_keys($result)
                    ]);
                }
                
                return $result;
            }

            // Adicionar expiração apenas para PIX e Boleto
            if ($payment->forma_pagamento === 'boleto') {
                $transactionData['payments'][0]['expiration_time'] = 'P3D'; // 3 dias
            } elseif ($payment->forma_pagamento === 'pix') {
                $transactionData['payments'][0]['expiration_time'] = 'P1D'; // 1 dia para PIX
            }
            // Para cartão de crédito, não adicionar expiration_time

            // Dados da ordem
            $orderData = [
                'type' => 'online',
                'processing_mode' => 'automatic',
                'external_reference' => "matricula_pagamento_{$payment->id}",
                'total_amount' => (string) $payment->valor,
                'description' => $payment->descricao,
                'payer' => $payerData,
                'transactions' => $transactionData
            ];

            // Headers obrigatórios
            $headers = [
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'X-Idempotency-Key' => uniqid('payment_' . $payment->id . '_'),
                'Content-Type' => 'application/json'
            ];

            // Determinar a URL baseada no ambiente
            $baseUrl = $this->paymentSettings['mercadopago_sandbox'] 
                ? 'https://api.mercadopago.com/v1/orders' 
                : 'https://api.mercadopago.com/v1/orders';

            Log::info('Enviando dados para nova API do Mercado Pago', [
                'payment_id' => $payment->id,
                'url' => $baseUrl,
                'order_data' => $orderData
            ]);

            // Fazer requisição HTTP diretamente
            $response = \Http::withHeaders($headers)
                ->timeout(30)
                ->post($baseUrl, $orderData);

            if (!$response->successful()) {
                $errorBody = $response->json();
                Log::error('Erro na nova API do Mercado Pago', [
                    'payment_id' => $payment->id,
                    'status' => $response->status(),
                    'error' => $errorBody
                ]);
                
                // Se há dados na resposta, processar mesmo com erro
                if (isset($errorBody['data'])) {
                    $mpOrder = $errorBody['data'];
                    Log::info('Processando ordem mesmo com erro', [
                        'payment_id' => $payment->id,
                        'order_id' => $mpOrder['id'] ?? 'N/A'
                    ]);
                    
                    // Processar resposta mesmo com erro
                    $this->processPaymentResponse($payment, $mpOrder);
                    
                    return [
                        'id' => $mpOrder['id'],
                        'status' => $mpOrder['status'],
                        'payment_method_id' => $mpOrder['transactions']['payments'][0]['payment_method']['id'] ?? null,
                        'transaction_amount' => $mpOrder['total_amount'],
                        'date_created' => $mpOrder['created_date'] ?? now()->toISOString(),
                        'full_response' => $mpOrder,
                        'has_error' => true,
                        'error_details' => $errorBody['errors'] ?? []
                    ];
                }
                
                throw new \Exception('Erro na API do Mercado Pago: ' . json_encode($errorBody));
            }

            $mpOrder = $response->json();
            
            Log::info('Ordem criada na nova API do Mercado Pago', [
                'payment_id' => $payment->id,
                'order_id' => $mpOrder['id'] ?? 'N/A'
            ]);

            // Processar resposta baseada no tipo de pagamento
            $this->processPaymentResponse($payment, $mpOrder);

            return [
                'id' => $mpOrder['id'],
                'status' => $mpOrder['status'],
                'payment_method_id' => $mpOrder['transactions']['payments'][0]['payment_method']['id'] ?? null,
                'transaction_amount' => $mpOrder['total_amount'],
                'date_created' => $mpOrder['created_date'] ?? $mpOrder['date_created'] ?? now()->toISOString(),
                'full_response' => $mpOrder
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao criar pagamento na nova API do Mercado Pago', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Erro na criação do pagamento: ' . $e->getMessage());
        }
    }

    /**
     * Criar preference para cartão de crédito
     */
    private function createCardPreference(Payment $payment)
    {
        try {
            $matricula = $payment->matricula;
            
            if (!$matricula) {
                throw new \Exception('Matrícula não encontrada para o pagamento');
            }

            // Para cartão de crédito, usar o valor total do curso (pagamento único)
            $valorTotal = $matricula->valor_total_curso;
            
            // Preparar dados da preference
            $preferenceData = [
                'items' => [
                    [
                        'title' => 'Pagamento do Curso - ' . ($matricula->inscricao->curso->nome ?? 'Curso'),
                        'unit_price' => (float) $valorTotal, // Valor total do curso para cartão
                        'quantity' => 1,
                        'currency_id' => 'BRL'
                    ]
                ],
                'payer' => [
                    'email' => $matricula->email ?: 'teste@example.com',
                    'name' => $matricula->nome_completo ?: 'Nome',
                    'identification' => [
                        'type' => 'CPF',
                        'number' => $this->cleanCpf($matricula->cpf ?: '11111111111')
                    ]
                ],
                'external_reference' => "matricula_pagamento_{$payment->id}",
                'expires' => true,
                'expiration_date_to' => now()->addDays(1)->toISOString(),
                'payment_methods' => [
                    'installments' => 12, // Máximo de 12 parcelas
                    'default_installments' => 1, // Padrão: 1 parcela (à vista)
                    'installments_cost' => 0, // Custo de juros zero para o comprador
                    'excluded_payment_methods' => [],
                    'excluded_payment_types' => []
                ]
            ];

            // Headers obrigatórios
            $headers = [
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json'
            ];

            // Determinar a URL baseada no ambiente
            $baseUrl = 'https://api.mercadopago.com/checkout/preferences';

            Log::info('Criando preference para cartão de crédito', [
                'payment_id' => $payment->id,
                'url' => $baseUrl,
                'preference_data' => $preferenceData
            ]);

            // Fazer requisição HTTP
            $response = \Http::withHeaders($headers)
                ->timeout(30)
                ->post($baseUrl, $preferenceData);

            if (!$response->successful()) {
                $errorBody = $response->json();
                Log::error('Erro na API de preference do Mercado Pago', [
                    'payment_id' => $payment->id,
                    'status' => $response->status(),
                    'error' => $errorBody
                ]);
                
                throw new \Exception('Erro na API do Mercado Pago: ' . json_encode($errorBody));
            }

            $mpPreference = $response->json();
            
            Log::info('Preference criada no Mercado Pago', [
                'payment_id' => $payment->id,
                'preference_id' => $mpPreference['id'] ?? 'N/A'
            ]);

            // Atualizar o pagamento com os dados da preference
            $payment->update([
                'mercadopago_id' => $mpPreference['id'],
                'mercadopago_status' => 'pending',
                'mercadopago_data' => $mpPreference,
                'init_point' => $mpPreference['init_point'] ?? null
            ]);

            Log::info('Pagamento de cartão de crédito criado com sucesso', [
                'payment_id' => $payment->id,
                'preference_id' => $mpPreference['id'],
                'init_point' => $mpPreference['init_point'] ?? null
            ]);

            // Retornar dados no formato esperado
            return [
                'id' => $mpPreference['id'],
                'status' => 'pending',
                'payment_method_id' => 'credit_card',
                'transaction_amount' => $payment->valor,
                'date_created' => $mpPreference['date_created'] ?? now()->toISOString(),
                'full_response' => $mpPreference,
                'init_point' => $mpPreference['init_point'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao criar preference para cartão de crédito', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Erro na criação do pagamento: ' . $e->getMessage());
        }
    }

    /**
     * Processar resposta do pagamento baseada no tipo
     */
    public function processPaymentResponse(Payment $payment, array $mpOrder)
    {
        $paymentData = $mpOrder['transactions']['payments'][0] ?? null;
        
        if (!$paymentData) {
            Log::warning('Dados de pagamento não encontrados na resposta', [
                'payment_id' => $payment->id,
                'order_response' => $mpOrder
            ]);
            return;
        }

        $updateData = [
            'mercadopago_id' => $paymentData['id'], // ID do pagamento individual, não da ordem
            'mercadopago_status' => $mpOrder['status'],
            'mercadopago_data' => $mpOrder
        ];

        // Processar PIX
        if ($payment->forma_pagamento === 'pix') {
            $qrCode = $paymentData['point_of_interaction']['transaction_data']['qr_code'] ?? null;
            $qrCodeBase64 = $paymentData['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null;
            $ticketUrl = $paymentData['point_of_interaction']['transaction_data']['ticket_url'] ?? null;
            
            if ($qrCode) {
                $updateData['codigo_pix'] = $qrCode;
            }
            
            if ($qrCodeBase64) {
                $updateData['qr_code_base64'] = $qrCodeBase64;
            }
            
            if ($ticketUrl) {
                $updateData['ticket_url'] = $ticketUrl;
            }
            
            Log::info('Dados PIX processados', [
                'payment_id' => $payment->id,
                'has_qr_code' => !empty($qrCode),
                'has_qr_code_base64' => !empty($qrCodeBase64),
                'has_ticket_url' => !empty($ticketUrl)
            ]);
        }

        // Processar Boleto
        if ($payment->forma_pagamento === 'boleto') {
            // Nova API: dados estão em payment_method
            $ticketUrl = $paymentData['payment_method']['ticket_url'] ?? 
                        $paymentData['transaction_details']['external_resource_url'] ?? null;
            $digitableLine = $paymentData['payment_method']['digitable_line'] ?? 
                            $paymentData['transaction_details']['digitable_line'] ?? null;
            $barcodeContent = $paymentData['payment_method']['barcode_content'] ?? 
                             $paymentData['transaction_details']['barcode']['content'] ?? null;
            $financialInstitution = $paymentData['payment_method']['financial_institution'] ?? 
                                   $paymentData['transaction_details']['financial_institution'] ?? null;
            
            if ($ticketUrl) {
                $updateData['ticket_url'] = $ticketUrl;
                $this->downloadAndSaveBoletoPdf($payment, $ticketUrl);
            }
            
            if ($digitableLine) {
                $updateData['digitable_line'] = $digitableLine;
            }
            
            if ($barcodeContent) {
                $updateData['barcode_content'] = $barcodeContent;
            }
            
            if ($financialInstitution) {
                $updateData['financial_institution'] = $financialInstitution;
            }
            
            Log::info('Dados Boleto processados', [
                'payment_id' => $payment->id,
                'has_ticket_url' => !empty($ticketUrl),
                'has_digitable_line' => !empty($digitableLine),
                'has_barcode_content' => !empty($barcodeContent),
                'financial_institution' => $financialInstitution
            ]);
        }

        // Processar Cartão de Crédito
        if ($payment->forma_pagamento === 'cartao_credito') {
            // O campo pode estar em diferentes níveis dependendo da resposta da API
            $initPoint = $paymentData['point_of_interaction']['transaction_data']['init_point']
                ?? $paymentData['init_point']
                ?? $mpOrder['init_point']
                ?? null;

            // Log para debug
            Log::info('Processando cartão de crédito - init_point', [
                'payment_id' => $payment->id,
                'init_point' => $initPoint,
                'payment_data_keys' => array_keys($paymentData),
                'mp_order_keys' => array_keys($mpOrder),
                'has_point_of_interaction' => isset($paymentData['point_of_interaction']),
                'point_of_interaction_keys' => isset($paymentData['point_of_interaction']) ? array_keys($paymentData['point_of_interaction']) : []
            ]);

            if ($initPoint) {
                $updateData['init_point'] = $initPoint;
                Log::info('init_point salvo com sucesso', [
                    'payment_id' => $payment->id,
                    'init_point' => $initPoint
                ]);
            } else {
                Log::warning('init_point não encontrado na resposta do Mercado Pago', [
                    'payment_id' => $payment->id,
                    'payment_data' => $paymentData,
                    'mp_order' => $mpOrder
                ]);
            }
        }

        // Atualizar o pagamento
        $payment->update($updateData);
    }

    /**
     * Mapear forma de pagamento para método do Mercado Pago
     */
    private function getPaymentMethodId($formaPagamento)
    {
        return match($formaPagamento) {
            'pix' => 'pix',
            'boleto' => 'boleto', // Usar método genérico de boleto
            'cartao_credito' => 'master', // Usar master como padrão para cartão de crédito
            'cartao_debito' => 'debit_card',
            default => 'pix'
        };
    }

    /**
     * Obter tipo do método de pagamento para a nova API
     */
    private function getPaymentMethodType($formaPagamento)
    {
        return match($formaPagamento) {
            'pix' => 'bank_transfer',
            'boleto' => 'ticket',
            'cartao_credito' => 'credit_card',
            'cartao_debito' => 'debit_card',
            default => 'bank_transfer'
        };
    }

    /**
     * Baixar e salvar PDF do boleto da API do Mercado Pago
     */
    private function downloadAndSaveBoletoPdf(Payment $payment, string $ticketUrl)
    {
        try {
            Log::info('Iniciando download do PDF do boleto', [
                'payment_id' => $payment->id,
                'ticket_url' => $ticketUrl
            ]);

            // Fazer download do PDF
            $pdfContent = file_get_contents($ticketUrl);
            
            if ($pdfContent === false) {
                throw new \Exception('Erro ao baixar o PDF do boleto');
            }

            // Gerar nome do arquivo
            $fileName = $payment->getBoletoFileName();
            $boletoDir = public_path('storage/boletos');
            $filePath = $boletoDir . '/' . $fileName;

            // Criar diretório se não existir
            if (!file_exists($boletoDir)) {
                mkdir($boletoDir, 0755, true);
            }

            // Salvar o arquivo
            if (file_put_contents($filePath, $pdfContent) === false) {
                throw new \Exception('Erro ao salvar o PDF do boleto');
            }

            // Atualizar o payment com o nome do arquivo
            $payment->update(['arquivo_boleto' => $fileName]);

            Log::info('PDF do boleto baixado e salvo com sucesso', [
                'payment_id' => $payment->id,
                'file_name' => $fileName,
                'file_size' => strlen($pdfContent)
            ]);

            return $fileName;

        } catch (\Exception $e) {
            Log::error('Erro ao baixar e salvar PDF do boleto', [
                'payment_id' => $payment->id,
                'ticket_url' => $ticketUrl,
                'error' => $e->getMessage()
            ]);
            
            // Não falhar o pagamento por causa do PDF
            return null;
        }
    }

    /**
     * Cancela um pagamento no Mercado Pago
     * 
     * @param string|int $paymentId ID do pagamento no Mercado Pago
     * @return array Resultado da operação
     */
    public function cancelPayment($paymentId)
    {
        if (!$this->paymentSettings['mercadopago_enabled']) {
            throw new \Exception('Mercado Pago não está habilitado');
        }

        if (!$this->paymentClient) {
            $this->initializeMercadoPago();
        }

        try {
            Log::info('Tentando cancelar pagamento no Mercado Pago', ['payment_id' => $paymentId]);

            // Verificar se é um ID de ordem (string) ou pagamento (número)
            if (is_string($paymentId) && strpos($paymentId, 'ORD') === 0) {
                Log::warning('Tentativa de cancelar ID de ordem em vez de pagamento', [
                    'payment_id' => $paymentId,
                    'message' => 'IDs de ordem não podem ser cancelados diretamente'
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Não é possível cancelar este pagamento. O ID é de uma ordem, não de um pagamento individual.',
                    'error_code' => 'ORDER_ID_NOT_CANCELLABLE'
                ];
            }

            // Garantir que o paymentId seja um inteiro para pagamentos individuais
            $paymentId = (int) $paymentId;

            // Verificar status atual do pagamento
            $paymentInfo = $this->paymentClient->get($paymentId);
            
            // Se já estiver em um estado final, não podemos cancelar
            if (in_array($paymentInfo->status, ['approved', 'rejected', 'cancelled', 'refunded'])) {
                Log::info('Pagamento já está em estado final, não pode ser cancelado', [
                    'payment_id' => $paymentId,
                    'status' => $paymentInfo->status
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Pagamento já está em estado final: ' . $paymentInfo->status,
                    'status' => $paymentInfo->status
                ];
            }
            
            // Cancelar o pagamento
            $cancelRequest = [
                'status' => 'cancelled'
            ];
            
            $result = $this->paymentClient->update($paymentId, $cancelRequest);
            
            Log::info('Pagamento cancelado no Mercado Pago', [
                'payment_id' => $paymentId,
                'new_status' => $result->status
            ]);
            
            return [
                'success' => true,
                'message' => 'Pagamento cancelado com sucesso',
                'status' => $result->status,
                'payment' => $result
            ];
            
        } catch (MPApiException $e) {
            Log::error('Erro ao cancelar pagamento no Mercado Pago: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'error_code' => $e->getCode()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao cancelar pagamento: ' . $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar pagamento: ' . $e->getMessage(), [
                'payment_id' => $paymentId
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao cancelar pagamento: ' . $e->getMessage()
            ];
        }
    }
} 