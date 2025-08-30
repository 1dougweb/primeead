<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\SystemSetting;
use App\Services\MercadoPagoService;
use App\Services\PaymentNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Mercado Pago webhook notifications
     */
    public function mercadoPago(Request $request)
    {
        // 🚨 PROTEÇÃO EMERGENCIAL - LOOP DETECTION
        $rateLimitKey = 'webhook_mercadopago_' . $request->ip();
        $webhookCount = cache()->get($rateLimitKey, 0);
        
        if ($webhookCount > 10) {
            Log::critical('🚨 WEBHOOK LOOP DETECTADO! Bloqueando temporariamente.', [
                'ip' => $request->ip(),
                'count' => $webhookCount,
                'data' => $request->all()
            ]);
            return response()->json(['error' => 'Rate limit exceeded - webhook loop protection'], 429);
        }
        
        cache()->put($rateLimitKey, $webhookCount + 1, now()->addMinutes(5));
        
        try {
            // Log webhook received
            Log::info('Mercado Pago webhook received', [
                'body' => $request->all(),
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
                'user_agent' => $request->userAgent(),
                'webhook_count' => $webhookCount + 1
            ]);

            // Validate webhook signature for production
            if (!$this->validateWebhookSignature($request)) {
                Log::warning('Invalid webhook signature', [
                    'ip' => $request->ip(),
                    'headers' => $request->headers->all()
                ]);
                
                // Em ambiente de desenvolvimento ou teste, permitir webhooks sem assinatura
                $environment = app()->environment();
                Log::info('Current environment: ' . $environment);
                
                if (in_array($environment, ['local', 'development', 'testing', 'producion'])) {
                    Log::info('Allowing webhook in non-production environment: ' . $environment);
                } else {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            }

            $data = $request->all();

            // Handle different webhook types
            switch ($data['type'] ?? '') {
                case 'payment':
                    return $this->handlePaymentWebhook($data);
                case 'order':
                    return $this->handleOrderWebhook($data);
                default:
                    Log::info('Unhandled webhook type', ['type' => $data['type'] ?? 'unknown']);
                    return response()->json(['message' => 'Webhook type not handled'], 200);
            }

        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Handle payment webhook notifications (legacy API)
     */
    protected function handlePaymentWebhook(array $data)
    {
        $paymentId = $data['data']['id'] ?? null;
        
        if (!$paymentId) {
            Log::warning('Payment webhook without payment ID', $data);
            return response()->json(['error' => 'Missing payment ID'], 400);
        }

        Log::info('Processing payment webhook', ['payment_id' => $paymentId]);
        
        // Find payment in our database - try multiple search methods
        $payment = Payment::where('mercadopago_id', $paymentId)->first();
        
        // If not found by mercadopago_id, try to find by external_reference or other fields
        if (!$payment) {
            Log::warning('Payment not found by mercadopago_id, trying alternative search methods', [
                'payment_id' => $paymentId,
                'search_methods' => 'mercadopago_id, external_reference, mercadopago_order_id'
            ]);
            
            // Try to find by mercadopago_order_id if it exists
            $payment = Payment::where('mercadopago_order_id', $paymentId)->first();
            
            // If still not found, try to find by any field that might contain the ID
            if (!$payment) {
                $payment = Payment::where('mercadopago_data', 'like', '%' . $paymentId . '%')->first();
            }
            
            // If still not found, log detailed information and return
            if (!$payment) {
                Log::error('Payment not found in database after all search methods', [
                    'payment_id' => $paymentId,
                    'search_methods_used' => ['mercadopago_id', 'mercadopago_order_id', 'mercadopago_data_like'],
                    'available_payments' => Payment::select('id', 'mercadopago_id', 'mercadopago_order_id')->get()->toArray()
                ]);
                return response()->json(['message' => 'Payment not found in database after all search methods'], 200);
            }
            
            Log::info('Payment found using alternative search method', [
                'payment_id' => $paymentId,
                'found_payment_id' => $payment->id,
                'search_method_used' => 'alternative'
            ]);
        }

        try {
            // Get payment details from Mercado Pago
            $settings = SystemSetting::getPaymentSettings();
            $accessToken = $settings['mercadopago_access_token'];
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->get("https://api.mercadopago.com/v1/payments/{$paymentId}");
            
            if (!$response->successful()) {
                // If Mercado Pago returns 404, the payment might not exist or be accessible
                if ($response->status() === 404) {
                    Log::warning('Payment not found in Mercado Pago API (404)', [
                        'payment_id' => $paymentId,
                        'local_payment_id' => $payment->id,
                        'local_status' => $payment->status,
                        'note' => 'This might be a test payment or the ID format has changed'
                    ]);
                    
                    // For 404 errors, we can still update the local status if we have enough info
                    // or just log the issue and continue
                    return response()->json(['message' => 'Payment not found in Mercado Pago API'], 200);
                }
                
                Log::error('Error fetching payment from Mercado Pago', [
                    'payment_id' => $paymentId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return response()->json(['message' => 'Error fetching payment from Mercado Pago'], 200);
            }
            
            $mpPayment = $response->json();
            
            // Update payment status
            $oldStatus = $payment->status;
            $newStatus = $this->mapPaymentStatus($mpPayment['status']);
            
            $payment->status = $newStatus;
            $payment->mercadopago_status = $mpPayment['status'];
            $payment->mercadopago_data = $mpPayment;
            
            if ($newStatus === 'paid' && !$payment->data_pagamento) {
                $payment->data_pagamento = now();
            }
            
            $payment->save();
            
            // Send notifications if payment was approved
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                try {
                    $notificationService = app(PaymentNotificationService::class);
                    $notificationService->sendPaymentApprovedNotifications($payment);
                } catch (\Exception $e) {
                    Log::error('Erro ao enviar notificações de pagamento aprovado via webhook', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage()
                    ]);
                }
                
                // Update matrícula status from 'pre_matricula' to 'matricula_confirmada'
                $this->updateMatriculaStatus($payment);
            }

            Log::info('Payment status updated via webhook', [
                'payment_id' => $payment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'mp_payment_id' => $paymentId
            ]);

            return response()->json(['message' => 'Payment webhook processed successfully'], 200);
            
        } catch (\Exception $e) {
            Log::error('Error processing payment webhook', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['message' => 'Error processing payment webhook'], 200);
        }
    }

    /**
     * Handle order webhook notifications (new API v1/orders)
     */
    protected function handleOrderWebhook(array $data)
    {
        $orderId = $data['data']['id'] ?? null;
        
        if (!$orderId) {
            Log::warning('Order webhook without order ID', $data);
            return response()->json(['error' => 'Missing order ID'], 400);
        }

        Log::info('Processing order webhook', ['order_id' => $orderId]);
        
        try {
            // Get order details from Mercado Pago
            $settings = SystemSetting::getPaymentSettings();
            $accessToken = $settings['mercadopago_access_token'];
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->get("https://api.mercadopago.com/v1/orders/{$orderId}");
            
            if (!$response->successful()) {
                Log::error('Error fetching order from Mercado Pago', [
                    'order_id' => $orderId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return response()->json(['message' => 'Error fetching order from Mercado Pago'], 200);
            }
            
            $mpOrder = $response->json();
            
            Log::info('Order status from Mercado Pago', [
                'order_id' => $orderId,
                'mp_status' => $mpOrder['status'],
                'external_reference' => $mpOrder['external_reference'] ?? null
            ]);
            
            // Find payment by external reference
            $externalReference = $mpOrder['external_reference'] ?? null;
            if ($externalReference) {
                $payment = Payment::where('external_reference', $externalReference)->first();
                
                if ($payment) {
                    $oldStatus = $payment->status;
                    $newStatus = $this->mapOrderStatus($mpOrder['status']);
                    
                    $payment->status = $newStatus;
                    $payment->mercadopago_status = $mpOrder['status'];
                    $payment->mercadopago_data = $mpOrder;
                    
                    if ($newStatus === 'paid' && !$payment->data_pagamento) {
                        $payment->data_pagamento = now();
                    }
                    
                    $payment->save();
                    
                    // Send notifications if payment was approved
                    if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                        try {
                            $notificationService = app(PaymentNotificationService::class);
                            $notificationService->sendPaymentApprovedNotifications($payment);
                        } catch (\Exception $e) {
                            Log::error('Erro ao enviar notificações de pagamento aprovado via webhook', [
                                'payment_id' => $payment->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    Log::info('Payment status updated via order webhook', [
                        'payment_id' => $payment->id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'mp_order_id' => $orderId
                    ]);
                }
            }
            
            return response()->json(['message' => 'Order webhook processed successfully'], 200);
            
        } catch (\Exception $e) {
            Log::error('Error processing order webhook', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['message' => 'Error processing order webhook'], 200);
        }
    }

    /**
     * Map Mercado Pago payment status to our system
     */
    protected function mapPaymentStatus($mpStatus)
    {
        return match($mpStatus) {
            'approved' => 'paid',
            'pending' => 'pending',
            'rejected' => 'failed',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'charged_back' => 'charged_back',
            'in_process' => 'pending',
            'authorized' => 'pending',
            'action_required' => 'pending',
            'waiting_payment' => 'pending',
            'expired' => 'expired',
            'failed' => 'failed',
            default => 'pending'
        };
    }

    /**
     * Map Mercado Pago order status to our system
     */
    protected function mapOrderStatus($mpStatus)
    {
        return match($mpStatus) {
            'approved' => 'paid',
            'pending' => 'pending',
            'waiting_payment' => 'pending',
            'action_required' => 'pending',
            'rejected' => 'failed',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'failed' => 'failed',
            'expired' => 'expired',
            default => 'pending'
        };
    }

    /**
     * Validate webhook signature for production
     */
    protected function validateWebhookSignature(Request $request): bool
    {
        $settings = SystemSetting::getPaymentSettings();
        
        // For production, webhook secret is required
        if (empty($settings['mercadopago_webhook_secret'])) {
            Log::error('Webhook secret not configured for production');
            return false;
        }

        // Get signature headers
        $signature = $request->header('x-signature');
        $requestId = $request->header('x-request-id');
        
        if (!$signature || !$requestId) {
            Log::warning('Missing webhook signature headers', [
                'has_signature' => !empty($signature),
                'has_request_id' => !empty($requestId),
                'all_headers' => $request->headers->all()
            ]);
            return false;
        }

        try {
            // Parse signature header
            $signatureParts = [];
            foreach (explode(',', $signature) as $part) {
                $keyValue = explode('=', $part, 2);
                if (count($keyValue) === 2) {
                    $signatureParts[trim($keyValue[0])] = trim($keyValue[1]);
                }
            }

            $ts = $signatureParts['ts'] ?? '';
            $v1 = $signatureParts['v1'] ?? '';

            if (!$ts || !$v1) {
                Log::warning('Invalid signature format', [
                    'signature_parts' => $signatureParts,
                    'raw_signature' => $signature
                ]);
                return false;
            }

            // Create signature string
            $dataId = $request->input('data.id', '');
            $signatureString = "id:{$dataId};request-id:{$requestId};ts:{$ts};";
            
            // Calculate expected signature
            $expectedSignature = hash_hmac('sha256', $signatureString, $settings['mercadopago_webhook_secret']);
            
            // Compare signatures
            $isValid = hash_equals($expectedSignature, $v1);
            
            if (!$isValid) {
                Log::warning('Invalid webhook signature', [
                    'expected' => $expectedSignature,
                    'received' => $v1,
                    'signature_string' => $signatureString,
                    'data_id' => $dataId,
                    'request_id' => $requestId,
                    'timestamp' => $ts
                ]);
            }

            return $isValid;
            
        } catch (\Exception $e) {
            Log::error('Error validating webhook signature', [
                'error' => $e->getMessage(),
                'signature' => $signature
            ]);
            return false;
        }
    }

    /**
     * Update payment schedule when payment status changes
     */
    protected function updatePaymentSchedule(Payment $payment)
    {
        $paymentSchedule = $payment->paymentSchedule;
        
        if (!$paymentSchedule) {
            return;
        }

        // Update payments count
        $paidPayments = $paymentSchedule->payments()->where('status', 'paid')->count();
        $paymentSchedule->payments_made = $paidPayments;

        // Check if schedule is completed
        if ($paymentSchedule->total_installments && $paidPayments >= $paymentSchedule->total_installments) {
            $paymentSchedule->status = 'completed';
            $paymentSchedule->completed_at = now();
        }

        $paymentSchedule->save();

        Log::info('Payment schedule updated', [
            'schedule_id' => $paymentSchedule->id,
            'payments_made' => $paidPayments,
            'total_installments' => $paymentSchedule->total_installments,
            'status' => $paymentSchedule->status
        ]);
    }

    /**
     * Update matrícula status when payment is confirmed
     */
    protected function updateMatriculaStatus(Payment $payment)
    {
        try {
            // Get the matrícula associated with this payment
            $matricula = $payment->matricula;
            
            if (!$matricula) {
                Log::warning('Matrícula not found for payment', [
                    'payment_id' => $payment->id,
                    'matricula_id' => $payment->matricula_id
                ]);
                return;
            }

            // Check if matrícula status is 'pre_matricula' and update to 'matricula_confirmada'
            if ($matricula->status === 'pre_matricula') {
                $oldStatus = $matricula->status;
                $matricula->status = 'matricula_confirmada';
                $matricula->save();

                Log::info('Matrícula status updated via webhook', [
                    'matricula_id' => $matricula->id,
                    'old_status' => $oldStatus,
                    'new_status' => $matricula->status,
                    'payment_id' => $payment->id,
                    'payment_status' => $payment->status
                ]);

                // You can add additional logic here, such as:
                // - Sending confirmation emails
                // - Creating contracts
                // - Updating student records
                // - Sending welcome messages
            } else {
                Log::info('Matrícula status not updated - already confirmed or different status', [
                    'matricula_id' => $matricula->id,
                    'current_status' => $matricula->status,
                    'payment_id' => $payment->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error updating matrícula status via webhook', [
                'payment_id' => $payment->id,
                'matricula_id' => $payment->matricula_id ?? 'N/A',
                'error' => $e->getMessage()
            ]);
        }
    }
} 