<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class PaymentApiController extends Controller
{
    protected $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Criar pagamento PIX
     */
    public function createPixPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'transaction_amount' => 'required|numeric|min:0.01',
                'description' => 'required|string|max:255',
                'payment_method_id' => 'required|string',
                'payer' => 'required|array',
                'payer.email' => 'required|email',
                'payer.first_name' => 'required|string',
                'payer.last_name' => 'required|string',
                'payer.identification' => 'required|array',
                'payer.identification.type' => 'required|string',
                'payer.identification.number' => 'required|string',
            ]);

            // Inicializar MercadoPago
            $this->initializeMercadoPago();
            $paymentClient = new PaymentClient();

            // Criar dados do pagamento
            $paymentData = [
                'transaction_amount' => (float) $validated['transaction_amount'],
                'description' => $validated['description'],
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $validated['payer']['email'],
                    'first_name' => $validated['payer']['first_name'],
                    'last_name' => $validated['payer']['last_name'],
                    'identification' => [
                        'type' => 'CPF',
                        'number' => $this->cleanCpf($validated['payer']['identification']['number'])
                    ]
                ]
            ];

            $mpPayment = $paymentClient->create($paymentData);

            Log::info('Pagamento PIX criado via API', [
                'mp_payment_id' => $mpPayment->id,
                'amount' => $validated['transaction_amount'],
                'mp_status' => $mpPayment->status
            ]);



            // Processar dados do PIX (QR Code, etc.)
            $mercadoPagoService = app(\App\Services\MercadoPagoService::class);
            
            // Criar um objeto Payment temporário para processamento
            $tempPayment = new \App\Models\Payment([
                'matricula_id' => null, // Será associado depois se necessário
                'mercadopago_id' => $mpPayment->id,
                'forma_pagamento' => 'pix',
                'valor' => $validated['transaction_amount'],
                'descricao' => $validated['description'],
                'data_vencimento' => now()->addDays(1), // PIX com 1 dia para vencer
                'status' => 'pending',
                'numero_parcela' => 1,
                'total_parcelas' => 1
            ]);
            
            // Salvar temporariamente para ter um ID
            $tempPayment->save();
            
            // Processar resposta do MercadoPago (incluindo QR Code)
            $mercadoPagoService->processPaymentResponse($tempPayment, json_decode(json_encode($mpPayment), true));

            // Recarregar o payment com os dados atualizados
            $tempPayment->refresh();

            return response()->json([
                'success' => true,
                'id' => $mpPayment->id,
                'status' => $mpPayment->status,
                'point_of_interaction' => $mpPayment->point_of_interaction,
                'transaction_details' => $mpPayment->transaction_details,
                'date_created' => $mpPayment->date_created,
                'qr_code' => $tempPayment->codigo_pix,
                'qr_code_base64' => $tempPayment->qr_code_base64,
                'ticket_url' => $tempPayment->ticket_url,
                'local_payment_id' => $tempPayment->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação no pagamento PIX via API', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro de validação',
                'validation_errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao criar pagamento PIX via API', [
                'error_code' => $e->getCode()
                // getMessage(), trace e request removidos por segurança
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno. Verifique os dados e tente novamente.'
            ], 500);
        }
    }

    /**
     * Criar pagamento Boleto
     */
    public function createBoletoPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'transaction_amount' => 'required|numeric|min:0.01',
                'description' => 'required|string|max:255',
                'payment_method_id' => 'required|string',
                'payer' => 'required|array',
                'payer.email' => 'required|email',
                'payer.first_name' => 'required|string',
                'payer.last_name' => 'required|string',
                'payer.identification' => 'required|array',
                'payer.identification.type' => 'required|string',
                'payer.identification.number' => 'required|string',
                'payer.address' => 'required|array',
                'payer.address.zip_code' => 'required|string',
                'payer.address.street_name' => 'required|string',
                'payer.address.street_number' => 'required|string',
                'payer.address.neighborhood' => 'required|string',
                'payer.address.city' => 'required|string',
                'payer.address.federal_unit' => 'required|string|max:2',
            ]);

            // Inicializar MercadoPago
            $this->initializeMercadoPago();
            $paymentClient = new PaymentClient();

            // Criar dados do pagamento
            $paymentData = [
                'transaction_amount' => (float) $validated['transaction_amount'],
                'description' => $validated['description'],
                'payment_method_id' => 'bolbradesco',
                'payer' => [
                    'email' => $validated['payer']['email'],
                    'first_name' => $validated['payer']['first_name'],
                    'last_name' => $validated['payer']['last_name'],
                    'identification' => [
                        'type' => 'CPF',
                        'number' => $this->cleanCpf($validated['payer']['identification']['number'])
                    ],
                    'address' => [
                        'zip_code' => $this->cleanCep($validated['payer']['address']['zip_code'] ?? ''),
                        'street_name' => $validated['payer']['address']['street_name'] ?? '',
                        'street_number' => $validated['payer']['address']['street_number'] ?? '',
                        'neighborhood' => $validated['payer']['address']['neighborhood'] ?? '',
                        'city' => $validated['payer']['address']['city'] ?? '',
                        'federal_unit' => $validated['payer']['address']['federal_unit'] ?? ''
                    ]
                ]
            ];

            $mpPayment = $paymentClient->create($paymentData);

            Log::info('Pagamento Boleto criado via API', [
                'mp_payment_id' => $mpPayment->id,
                'amount' => $validated['transaction_amount'],
                'mp_status' => $mpPayment->status,
                'mp_payment_method' => $mpPayment->payment_method_id ?? 'N/A'
            ]);



            // Processar dados do boleto (PDF, linha digitável, etc.)
            $mercadoPagoService = app(\App\Services\MercadoPagoService::class);
            
            // Criar um objeto Payment temporário para processamento
            $tempPayment = new \App\Models\Payment([
                'matricula_id' => null, // Será associado depois se necessário
                'mercadopago_id' => $mpPayment->id,
                'forma_pagamento' => 'boleto',
                'valor' => $validated['transaction_amount'],
                'descricao' => $validated['description'],
                'data_vencimento' => now()->addDays(3), // Boleto com 3 dias para vencer
                'status' => 'pending',
                'numero_parcela' => 1,
                'total_parcelas' => 1
            ]);
            
            // Salvar temporariamente para ter um ID
            $tempPayment->save();
            
            // Processar resposta do MercadoPago (incluindo download do PDF)
            $mercadoPagoService->processPaymentResponse($tempPayment, json_decode(json_encode($mpPayment), true));

            // Recarregar o payment com os dados atualizados
            $tempPayment->refresh();

            return response()->json([
                'success' => true,
                'id' => $mpPayment->id,
                'status' => $mpPayment->status,
                'transaction_details' => $mpPayment->transaction_details,
                'date_created' => $mpPayment->date_created,
                'ticket_url' => $tempPayment->ticket_url,
                'digitable_line' => $tempPayment->digitable_line,
                'arquivo_boleto' => $tempPayment->arquivo_boleto,
                'local_payment_id' => $tempPayment->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação no pagamento Boleto via API', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro de validação',
                'validation_errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao criar pagamento Boleto via API', [
                'error_code' => $e->getCode()
                // getMessage(), trace e request removidos por segurança
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno. Verifique os dados e tente novamente.'
            ], 500);
        }
    }

    /**
     * Criar preference para cartão de crédito
     */
    public function createCardPreference(Request $request)
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.title' => 'required|string',
                'items.*.unit_price' => 'required|numeric|min:0.01',
                'items.*.quantity' => 'required|integer|min:1',
                'payer' => 'nullable|array',
                'payer.email' => 'nullable|email',
                'payer.name' => 'nullable|string',
                'payer.identification' => 'nullable|array',
            ]);

            // Inicializar MercadoPago
            $this->initializeMercadoPago();
            $preferenceClient = new PreferenceClient();

            // Criar dados da preference com estrutura mínima que funciona
            $preferenceData = [
                'items' => array_map(function($item) {
                    return [
                        'title' => $item['title'],
                        'unit_price' => (float) $item['unit_price'],
                        'quantity' => (int) $item['quantity'],
                        'currency_id' => 'BRL'
                    ];
                }, $validated['items'])
            ];

            // Adicionar payer somente se fornecido (opcional)
            if (!empty($validated['payer']['email'])) {
                $preferenceData['payer'] = [
                    'email' => $validated['payer']['email']
                ];
                
                // Adicionar name se fornecido
                if (!empty($validated['payer']['name'])) {
                    $preferenceData['payer']['name'] = $validated['payer']['name'];
                }
            }

            // Log dos dados antes de enviar (dados sensíveis removidos)
            Log::info('Criando preference para cartão de crédito', [
                'payment_amount' => $preferenceData['transaction_amount'] ?? 'N/A'
            ]);

            $preference = $preferenceClient->create($preferenceData);

            Log::info('Preference para cartão criada via API', [
                'preference_id' => $preference->id
            ]);

            return response()->json([
                'success' => true,
                'id' => $preference->id,
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao criar preference para cartão via API', [
                'error_code' => $e->getCode()
                // getMessage(), trace e request_data removidos por segurança
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno. Verifique os dados e tente novamente.'
            ], 500);
        }
    }

    /**
     * Inicializar configurações do MercadoPago
     */
    private function initializeMercadoPago()
    {
        $accessToken = $this->mercadoPagoService->getAccessToken();
        
        if (empty($accessToken)) {
            throw new \Exception('Token de acesso do MercadoPago não configurado');
        }

        MercadoPagoConfig::setAccessToken($accessToken);

        // Configurar ambiente
        if ($this->mercadoPagoService->isSandbox()) {
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
        } else {
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);
        }
    }

    /**
     * Limpar CPF
     */
    private function cleanCpf($cpf)
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }

    /**
     * Limpar CEP
     */
    private function cleanCep($cep)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $cep);
        return str_pad($cleaned, 8, '0', STR_PAD_LEFT);
    }
} 