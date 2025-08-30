<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Models\SystemSetting;
use App\Mail\PaymentCreatedMail;
use App\Mail\PaymentLinksAvailableMail; // Added this import
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PaymentNotificationService
{
    protected $whatsAppService;
    protected $paymentSettings;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
        
        // Verificar se estamos em contexto de migração ou teste
        if ($this->isInMigrationContext()) {
            $this->paymentSettings = $this->getDefaultPaymentSettings();
            return;
        }

        try {
            $this->paymentSettings = SystemSetting::getPaymentSettings();
        } catch (\Exception $e) {
            // Se falhar ao acessar configurações (ex: durante migrações), usar configurações padrão
            Log::warning('Erro ao carregar configurações de pagamento, usando configurações padrão: ' . $e->getMessage());
            $this->paymentSettings = $this->getDefaultPaymentSettings();
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
    private function getDefaultPaymentSettings(): array
    {
        return [
            'mercadopago_enabled' => false,
            'mercadopago_email_notifications' => true,
            'mercadopago_whatsapp_notifications' => false,
            'mercadopago_sms_notifications' => false,
            'mercadopago_auto_reminders' => true,
            'payment_auto_charge' => true,
            'payment_email_notifications' => true,
            'payment_whatsapp_notifications' => true,
            'payment_reminder_days' => 3,
            'payment_overdue_days' => 7,
        ];
    }

    /**
     * Enviar notificações quando um pagamento é criado
     */
    public function sendPaymentCreatedNotifications(Payment $payment)
    {
        try {
            Log::info('Enviando notificações de pagamento criado', [
                'payment_id' => $payment->id,
                'matricula_id' => $payment->matricula_id
            ]);

            $matricula = $payment->matricula;
            
            if (!$matricula) {
                Log::warning('Matrícula não encontrada para pagamento', ['payment_id' => $payment->id]);
                return;
            }

            // Enviar email se habilitado
            if ($this->paymentSettings['mercadopago_email_notifications'] ?? true) {
                $this->sendEmailNotification($payment, $matricula);
            }

            // 🚨 WhatsApp APENAS para o primeiro payment da matrícula (evita spam)
            $gateway = $matricula->payment_gateway ?? 'mercado_pago';
            if (($this->paymentSettings['mercadopago_whatsapp_notifications'] ?? false) && $gateway === 'mercado_pago') {
                // Verificar se é o primeiro payment criado para esta matrícula
                $isFirstPayment = $matricula->payments()->count() === 1;
                
                if ($isFirstPayment) {
                    // Enviar apenas uma mensagem consolidada
                    $this->sendConsolidatedWhatsAppMessage($payment, $matricula);
                }
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificações de pagamento criado', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar notificação por email
     */
    protected function sendEmailNotification(Payment $payment, $matricula)
    {
        try {
            // Criar registro de notificação
            $notification = PaymentNotification::create([
                'payment_id' => $payment->id,
                'contact_id' => null, // Não temos tabela contacts para matrículas
                'type' => 'payment_created',
                'channel' => 'email',
                'status' => 'pending',
                'subject' => 'Cobrança Gerada - ' . $payment->descricao,
                'message' => $this->buildEmailMessage($payment, $matricula),
                'recipient' => $matricula->email,
                'scheduled_at' => now(),
                'metadata' => [
                    'matricula_id' => $matricula->id,
                    'student_name' => $matricula->nome_completo
                ]
            ]);

            // Enviar email
            Mail::to($matricula->email)->send(new PaymentCreatedMail($payment, $matricula));
            
            // Atualizar status da notificação
            $notification->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            Log::info('Email de cobrança enviado com sucesso', [
                'payment_id' => $payment->id,
                'email' => $matricula->email
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de cobrança', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            if (isset($notification)) {
                $notification->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Enviar notificação por WhatsApp
     */
    protected function sendWhatsAppNotification(Payment $payment, $matricula)
    {
        try {

            // Verificar se tem telefone válido
            $phone = $matricula->telefone_celular ?: $matricula->telefone_fixo;
            
            if (!$phone) {
                Log::warning('Telefone não encontrado para envio de WhatsApp', [
                    'payment_id' => $payment->id,
                    'matricula_id' => $matricula->id
                ]);
                return;
            }

            // Criar registro de notificação
            $notification = PaymentNotification::create([
                'payment_id' => $payment->id,
                'contact_id' => null,
                'type' => 'payment_created',
                'channel' => 'whatsapp',
                'status' => 'pending',
                'message' => $this->buildWhatsAppMessage($payment, $matricula),
                'recipient' => $phone,
                'scheduled_at' => now(),
                'metadata' => [
                    'matricula_id' => $matricula->id,
                    'student_name' => $matricula->nome_completo
                ]
            ]);

            // Verificar se WhatsApp está configurado
            if (!$this->whatsAppService->hasValidSettings()) {
                throw new \Exception('WhatsApp não está configurado');
            }

            // Construir mensagem
            $message = $this->buildWhatsAppMessage($payment, $matricula);
            
            // Enviar mensagem
            $this->whatsAppService->sendMessage($phone, $message);
            
            // Atualizar status da notificação
            $notification->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            Log::info('WhatsApp de cobrança enviado com sucesso', [
                'payment_id' => $payment->id,
                'phone' => $phone
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar WhatsApp de cobrança', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            if (isset($notification)) {
                $notification->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Construir mensagem de email
     */
    protected function buildEmailMessage(Payment $payment, $matricula)
    {
        $message = "Nova cobrança gerada para {$matricula->nome_completo}\n\n";
        $message .= "Detalhes do pagamento:\n";
        $message .= "- Valor: R$ " . number_format($payment->valor, 2, ',', '.') . "\n";
        $message .= "- Vencimento: " . $payment->data_vencimento->format('d/m/Y') . "\n";
        $message .= "- Forma de pagamento: " . ucfirst($payment->forma_pagamento) . "\n";
        $message .= "- Descrição: " . $payment->descricao . "\n";
        
        if ($payment->numero_parcela && $payment->total_parcelas > 1) {
            $message .= "- Parcela: {$payment->numero_parcela}/{$payment->total_parcelas}\n";
        }

        return $message;
    }

    /**
     * Construir mensagem do WhatsApp
     */
    protected function buildWhatsAppMessage(Payment $payment, $matricula)
    {
        $message = "💳 *PAGAMENTO GERADO*\n\n";
        $message .= "Olá *{$matricula->nome_completo}*!\n\n";
        $message .= "Um novo pagamento foi gerado para você:\n\n";
        $message .= "💰 *Valor:* R$ " . number_format($payment->valor, 2, ',', '.') . "\n";
        $message .= "📅 *Vencimento:* " . $payment->data_vencimento->format('d/m/Y') . "\n";
        $message .= "💳 *Forma de pagamento:* " . ucfirst($payment->forma_pagamento) . "\n";
        $message .= "📝 *Descrição:* " . $payment->descricao . "\n";
        
        if ($payment->numero_parcela && $payment->total_parcelas > 1) {
            $message .= "🔢 *Parcela:* {$payment->numero_parcela}/{$payment->total_parcelas}\n";
        }

        $message .= "\n📞 *Dúvidas?* Entre em contato conosco!\n\n";
        $message .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";

        return $message;
    }

    /**
     * Enviar notificação de pagamento aprovado
     */
    public function sendPaymentApprovedNotifications(Payment $payment)
    {
        try {
            Log::info('Enviando notificações de pagamento aprovado', [
                'payment_id' => $payment->id
            ]);

            $matricula = $payment->matricula;
            
            if (!$matricula) {
                Log::warning('Matrícula não encontrada para pagamento aprovado', ['payment_id' => $payment->id]);
                return;
            }

            // Enviar email se habilitado
            if ($this->paymentSettings['mercadopago_email_notifications'] ?? true) {
                $this->sendPaymentApprovedEmail($payment, $matricula);
            }

            // Enviar WhatsApp se habilitado E for Mercado Pago
            $gateway = $matricula->payment_gateway ?? 'mercado_pago';
            if (($this->paymentSettings['mercadopago_whatsapp_notifications'] ?? false) && $gateway === 'mercado_pago') {
                $this->sendPaymentApprovedWhatsApp($payment, $matricula);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificações de pagamento aprovado', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar email de pagamento aprovado
     */
    protected function sendPaymentApprovedEmail(Payment $payment, $matricula)
    {
        try {
            $notification = PaymentNotification::create([
                'payment_id' => $payment->id,
                'contact_id' => null,
                'type' => 'payment_confirmed',
                'channel' => 'email',
                'status' => 'pending',
                'subject' => 'Pagamento Confirmado - ' . $payment->descricao,
                'message' => $this->buildPaymentApprovedEmailMessage($payment, $matricula),
                'recipient' => $matricula->email,
                'scheduled_at' => now(),
                'metadata' => [
                    'matricula_id' => $matricula->id,
                    'student_name' => $matricula->nome_completo
                ]
            ]);

            // Aqui você pode criar um mail específico para pagamento aprovado
            // Mail::to($matricula->email)->send(new PaymentApprovedMail($payment, $matricula));
            
            $notification->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            Log::info('Email de pagamento aprovado enviado', [
                'payment_id' => $payment->id,
                'email' => $matricula->email
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de pagamento aprovado', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar WhatsApp de pagamento aprovado
     */
    protected function sendPaymentApprovedWhatsApp(Payment $payment, $matricula)
    {
        try {
            $phone = $matricula->telefone_celular ?: $matricula->telefone_fixo;
            
            if (!$phone) {
                return;
            }

            $notification = PaymentNotification::create([
                'payment_id' => $payment->id,
                'contact_id' => null,
                'type' => 'payment_confirmed',
                'channel' => 'whatsapp',
                'status' => 'pending',
                'message' => $this->buildPaymentApprovedWhatsAppMessage($payment, $matricula),
                'recipient' => $phone,
                'scheduled_at' => now(),
                'metadata' => [
                    'matricula_id' => $matricula->id,
                    'student_name' => $matricula->nome_completo
                ]
            ]);

            $message = $this->buildPaymentApprovedWhatsAppMessage($payment, $matricula);
            $this->whatsAppService->sendMessage($phone, $message);
            
            $notification->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            Log::info('WhatsApp de pagamento aprovado enviado', [
                'payment_id' => $payment->id,
                'phone' => $phone
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar WhatsApp de pagamento aprovado', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Construir mensagem de email para pagamento aprovado
     */
    protected function buildPaymentApprovedEmailMessage(Payment $payment, $matricula)
    {
        $message = "Pagamento confirmado para {$matricula->nome_completo}\n\n";
        $message .= "Seu pagamento foi processado com sucesso!\n\n";
        $message .= "Detalhes do pagamento:\n";
        $message .= "- Valor: R$ " . number_format($payment->valor, 2, ',', '.') . "\n";
        $message .= "- Data do pagamento: " . $payment->data_pagamento->format('d/m/Y H:i') . "\n";
        $message .= "- Descrição: " . $payment->descricao . "\n";
        
        return $message;
    }

    /**
     * Construir mensagem de WhatsApp para pagamento aprovado
     */
    protected function buildPaymentApprovedWhatsAppMessage(Payment $payment, $matricula)
    {
        $message = "✅ *PAGAMENTO CONFIRMADO!*\n\n";
        $message .= "Olá *{$matricula->nome_completo}*!\n\n";
        $message .= "🎉 Seu pagamento foi processado com sucesso!\n\n";
        $message .= "💰 *Valor:* R$ " . number_format($payment->valor, 2, ',', '.') . "\n";
        $message .= "📅 *Data do pagamento:* " . $payment->data_pagamento->format('d/m/Y H:i') . "\n";
        $message .= "📝 *Descrição:* " . $payment->descricao . "\n\n";
        $message .= "✅ *Status:* Confirmado\n\n";
        $message .= "Obrigado por manter seus pagamentos em dia!\n\n";
        $message .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";

        return $message;
    }

    /**
     * Enviar notificações de atualização com links de pagamento
     */
    public function sendPaymentLinksNotifications(Payment $payment)
    {
        try {
            Log::info('Enviando notificações de links de pagamento', [
                'payment_id' => $payment->id,
                'matricula_id' => $payment->matricula_id
            ]);

            $matricula = $payment->matricula;
            
            if (!$matricula) {
                Log::warning('Matrícula não encontrada para pagamento', ['payment_id' => $payment->id]);
                return;
            }

            // Verificar se há dados do Mercado Pago com links
            if (!$payment->mercadopago_data) {
                Log::info('Sem dados do Mercado Pago para enviar links', ['payment_id' => $payment->id]);
                return;
            }

            $mpData = $payment->mercadopago_data;
            $hasPaymentLinks = false;

            // Verificar se há links de pagamento disponíveis
            switch ($payment->forma_pagamento) {
                case 'pix':
                    $hasPaymentLinks = isset($mpData['point_of_interaction']['transaction_data']['qr_code']);
                    break;
                case 'boleto':
                    $hasPaymentLinks = isset($mpData['transaction_details']['external_resource_url']);
                    break;
                case 'cartao_credito':
                    // Verificar se o campo init_point está salvo no modelo ou nos dados do MP
                    $hasPaymentLinks = !empty($payment->init_point) 
                        || isset($mpData['point_of_interaction']['transaction_data']['init_point'])
                        || isset($mpData['init_point']);
                    break;
            }

            if (!$hasPaymentLinks) {
                Log::info('Sem links de pagamento disponíveis', ['payment_id' => $payment->id]);
                return;
            }

            // Enviar email se habilitado
            if ($this->paymentSettings['mercadopago_email_notifications'] ?? true) {
                $this->sendPaymentLinksEmail($payment, $matricula);
            }

            // Enviar WhatsApp se habilitado E for Mercado Pago
            $gateway = $matricula->payment_gateway ?? 'mercado_pago';
            if (($this->paymentSettings['mercadopago_whatsapp_notifications'] ?? false) && $gateway === 'mercado_pago') {
                $this->sendPaymentLinksWhatsApp($payment, $matricula);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificações de links de pagamento', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar email com links de pagamento
     */
    protected function sendPaymentLinksEmail(Payment $payment, $matricula)
    {
        try {
            // Criar registro de notificação
            $notification = PaymentNotification::create([
                'payment_id' => $payment->id,
                'contact_id' => null,
                'type' => 'payment_links',
                'channel' => 'email',
                'status' => 'pending',
                'message' => 'Links de pagamento disponíveis',
                'recipient' => $matricula->email,
                'scheduled_at' => now(),
                'metadata' => [
                    'matricula_id' => $matricula->id,
                    'student_name' => $matricula->nome_completo,
                    'payment_method' => $payment->forma_pagamento
                ]
            ]);

            // Enviar email
            Mail::to($matricula->email)->send(new PaymentLinksAvailableMail($payment, $matricula));
            
            // Atualizar status da notificação
            $notification->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);

            Log::info('Email de links de pagamento enviado com sucesso', [
                'payment_id' => $payment->id,
                'email' => $matricula->email
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar email de links de pagamento', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            if (isset($notification)) {
                $notification->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Enviar WhatsApp com links de pagamento
     */
    protected function sendPaymentLinksWhatsApp(Payment $payment, $matricula)
    {
        try {

            // Verificar se tem telefone válido
            $phone = $matricula->telefone_celular ?: $matricula->telefone_fixo;
            
            if (!$phone) {
                Log::warning('Telefone não encontrado para envio de WhatsApp', [
                    'payment_id' => $payment->id,
                    'matricula_id' => $matricula->id
                ]);
                return;
            }

            // Verificar se WhatsApp está configurado
            if (!$this->whatsAppService->hasValidSettings()) {
                throw new \Exception('WhatsApp não está configurado');
            }

            // Para PIX, enviar mensagens separadas
            if ($payment->forma_pagamento === 'pix' && $payment->mercadopago_data) {
                $mpData = $payment->mercadopago_data;
                
                if (isset($mpData['point_of_interaction']['transaction_data']['qr_code'])) {
                    // Primeira mensagem: QR Code
                    $qrCodeMessage = "🔑 *QR CODE PIX*\n\n";
                    $qrCodeMessage .= "Olá *{$matricula->nome_completo}*!\n\n";
                    $qrCodeMessage .= "Escaneie o QR Code abaixo para pagar:\n\n";
                    $qrCodeMessage .= "📞 *Dúvidas?* Entre em contato conosco!\n\n";
                    $qrCodeMessage .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";
                    
                    // Criar notificação para QR Code
                    $qrNotification = PaymentNotification::create([
                        'payment_id' => $payment->id,
                        'contact_id' => null,
                        'type' => 'payment_qr_code',
                        'channel' => 'whatsapp',
                        'status' => 'pending',
                        'message' => $qrCodeMessage,
                        'recipient' => $phone,
                        'scheduled_at' => now(),
                        'metadata' => [
                            'matricula_id' => $matricula->id,
                            'student_name' => $matricula->nome_completo,
                            'payment_method' => $payment->forma_pagamento
                        ]
                    ]);
                    
                    // Enviar QR Code
                    $result1 = $this->whatsAppService->sendMessage($phone, $qrCodeMessage);
                    
                    if ($result1['success']) {
                        $qrNotification->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                            'external_id' => $result1['message_id'] ?? null
                        ]);
                    } else {
                        $qrNotification->update([
                            'status' => 'failed',
                            'error_message' => $result1['error'] ?? 'Erro desconhecido'
                        ]);
                    }
                    
                    // Aguardar 3 segundos
                    sleep(3);
                    
                    // Segunda mensagem: Código PIX
                    $pixCodeMessage = "🔑 *CÓDIGO PIX*\n\n";
                    $pixCodeMessage .= "Ou copie o código PIX abaixo:\n\n";
                    $pixCodeMessage .= "`{$mpData['point_of_interaction']['transaction_data']['qr_code']}`\n\n";
                    $pixCodeMessage .= "📱 *Como pagar:*\n";
                    $pixCodeMessage .= "1. Copie o código PIX acima\n";
                    $pixCodeMessage .= "2. Abra seu banco ou carteira digital\n";
                    $pixCodeMessage .= "3. Escolha PIX → Pagar → Colar código\n";
                    $pixCodeMessage .= "4. Confirme o pagamento\n\n";
                    $pixCodeMessage .= "⚡ *Pagamento instantâneo!*\n\n";
                    $pixCodeMessage .= "📞 *Dúvidas?* Entre em contato conosco!\n\n";
                    $pixCodeMessage .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";
                    
                    // Criar notificação para código PIX
                    $pixNotification = PaymentNotification::create([
                        'payment_id' => $payment->id,
                        'contact_id' => null,
                        'type' => 'payment_pix_code',
                        'channel' => 'whatsapp',
                        'status' => 'pending',
                        'message' => $pixCodeMessage,
                        'recipient' => $phone,
                        'scheduled_at' => now(),
                        'metadata' => [
                            'matricula_id' => $matricula->id,
                            'student_name' => $matricula->nome_completo,
                            'payment_method' => $payment->forma_pagamento
                        ]
                    ]);
                    
                    // Enviar código PIX
                    $result2 = $this->whatsAppService->sendMessage($phone, $pixCodeMessage);
                    
                    if ($result2['success']) {
                        $pixNotification->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                            'external_id' => $result2['message_id'] ?? null
                        ]);
                        
                        Log::info('WhatsApp PIX QR Code e código enviados com sucesso', [
                            'payment_id' => $payment->id,
                            'phone' => $phone
                        ]);
                    } else {
                        $pixNotification->update([
                            'status' => 'failed',
                            'error_message' => $result2['error'] ?? 'Erro desconhecido'
                        ]);
                        throw new \Exception($result2['error'] ?? 'Erro ao enviar código PIX');
                    }
                    
                    return;
                }
            }
            
            // Para outros métodos de pagamento, usar o método original
            $message = $this->buildPaymentLinksWhatsAppMessage($payment, $matricula);
            
            // Criar registro de notificação
            $notification = PaymentNotification::create([
                'payment_id' => $payment->id,
                'contact_id' => null,
                'type' => 'payment_links',
                'channel' => 'whatsapp',
                'status' => 'pending',
                'message' => $message,
                'recipient' => $phone,
                'scheduled_at' => now(),
                'metadata' => [
                    'matricula_id' => $matricula->id,
                    'student_name' => $matricula->nome_completo,
                    'payment_method' => $payment->forma_pagamento
                ]
            ]);

            // Enviar mensagem
            $result = $this->whatsAppService->sendMessage($phone, $message);

            if ($result['success']) {
                $notification->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'external_id' => $result['message_id'] ?? null
                ]);

                Log::info('WhatsApp de links de pagamento enviado com sucesso', [
                    'payment_id' => $payment->id,
                    'phone' => $phone
                ]);
            } else {
                throw new \Exception($result['error'] ?? 'Erro desconhecido');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar WhatsApp de links de pagamento', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            if (isset($notification)) {
                $notification->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Construir mensagem do WhatsApp com links de pagamento
     */
    protected function buildPaymentLinksWhatsAppMessage(Payment $payment, $matricula)
    {
        if ($payment->mercadopago_data) {
            $mpData = $payment->mercadopago_data;
            
            switch ($payment->forma_pagamento) {
                case 'pix':
                    if (isset($mpData['transactions']['payments'][0]['payment_method']['ticket_url'])) {
                        $paymentData = $mpData['transactions']['payments'][0];
                        $ticketUrl = $paymentData['payment_method']['ticket_url'];
                        $qrCode = $paymentData['payment_method']['qr_code'] ?? null;
                        
                        $message = "💳 *PIX INSTANTÂNEO*\n\n";
                        $message .= "Olá *{$matricula->nome_completo}*!\n\n";
                        $message .= "Seu PIX está disponível:\n\n";
                        $message .= "🔗 *Link para pagamento:*\n";
                        $message .= "{$ticketUrl}\n\n";
                        
                        if ($qrCode) {
                            $message .= "📱 *Código PIX (Copia e Cola):*\n";
                            $message .= "`{$qrCode}`\n\n";
                        }
                        
                        $message .= "📱 *Como pagar:*\n";
                        $message .= "1. Use o link acima ou\n";
                        $message .= "2. Copie o código PIX\n";
                        $message .= "3. Pague no seu app bancário\n";
                        $message .= "4. Confirmação imediata\n\n";
                        $message .= "📞 *Dúvidas?* Entre em contato conosco!\n\n";
                        $message .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";
                        
                        return $message;
                    }
                    break;
                    
                case 'boleto':
                    if (isset($mpData['transactions']['payments'][0]['payment_method']['ticket_url'])) {
                        $paymentData = $mpData['transactions']['payments'][0];
                        $ticketUrl = $paymentData['payment_method']['ticket_url'];
                        $barcodeContent = $paymentData['payment_method']['barcode_content'] ?? null;
                        $digitableLine = $paymentData['payment_method']['digitable_line'] ?? null;
                        
                        $message = "🧾 *BOLETO BANCÁRIO*\n\n";
                        $message .= "Olá *{$matricula->nome_completo}*!\n\n";
                        $message .= "Seu boleto está disponível:\n\n";
                        $message .= "🔗 *Link para pagamento:*\n";
                        $message .= "{$ticketUrl}\n\n";
                        
                        if ($barcodeContent) {
                            $message .= "📱 *Código de Barras:*\n";
                            $message .= "`{$barcodeContent}`\n\n";
                        }
                        
                        if ($digitableLine) {
                            $message .= "📱 *Linha Digitável:*\n";
                            $message .= "`{$digitableLine}`\n\n";
                        }
                        
                        $message .= "📱 *Como pagar:*\n";
                        $message .= "1. Use o link acima ou\n";
                        $message .= "2. Copie o código de barras\n";
                        $message .= "3. Pague em qualquer banco ou app\n";
                        $message .= "4. Prazo: até 3 dias úteis\n\n";
                        $message .= "📞 *Dúvidas?* Entre em contato conosco!\n\n";
                        $message .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";
                        
                        return $message;
                    }
                    break;
                    
                case 'cartao_credito':
                    // Verificar se o campo init_point está salvo no modelo Payment
                    $initPoint = $payment->init_point;
                    
                    // Se não estiver no modelo, tentar extrair dos dados do Mercado Pago
                    if (!$initPoint && isset($mpData['point_of_interaction']['transaction_data']['init_point'])) {
                        $initPoint = $mpData['point_of_interaction']['transaction_data']['init_point'];
                    }
                    
                    // Fallback para outros campos possíveis
                    if (!$initPoint && isset($mpData['init_point'])) {
                        $initPoint = $mpData['init_point'];
                    }
                    
                    if ($initPoint) {
                        $message = "💳 *CARTÃO DE CRÉDITO*\n\n";
                        $message .= "Olá *{$matricula->nome_completo}*!\n\n";
                        $message .= "🔗 *Link de pagamento seguro:*\n";
                        $message .= "{$initPoint}\n\n";
                        $message .= "📱 *Como pagar:*\n";
                        $message .= "1. Clique no link acima\n";
                        $message .= "2. Insira dados do cartão\n";
                        $message .= "3. Confirme o pagamento\n";
                        $message .= "4. Processamento imediato\n\n";
                        $message .= "🔒 *Ambiente 100% seguro - MercadoPago*\n";
                        $message .= "💰 *Aproveite: Parcelamento sem juros!*\n\n";
                        $message .= "📞 *Dúvidas?* Entre em contato conosco!\n\n";
                        $message .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";
                        
                        return $message;
                    }
                    break;
            }
        }

        // Fallback se não há dados do Mercado Pago
        $message = "💳 *DADOS DE PAGAMENTO*\n\n";
        $message .= "Olá *{$matricula->nome_completo}*!\n\n";
        $message .= "Os dados de pagamento serão enviados em breve.\n\n";
        $message .= "📞 *Dúvidas?* Entre em contato conosco!\n\n";
        $message .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";

        return $message;
    }

    /**
     * Build payment due today message
     */
    protected function buildPaymentDueTodayMessage(Payment $payment, $matricula)
    {
        $valor = 'R$ ' . number_format($payment->valor, 2, ',', '.');
        $dataVencimento = $payment->data_vencimento->format('d/m/Y');
        $parcela = $payment->numero_parcela > 1 ? " ({$payment->numero_parcela}/{$payment->total_parcelas})" : '';

        $message = "🚨 *PAGAMENTO VENCE HOJE!*\n\n";
        $message .= "Olá *{$matricula->nome_completo}*!\n\n";
        $message .= "Seu pagamento vence *HOJE*!\n\n";
        $message .= "💰 *Valor:* {$valor}{$parcela}\n";
        $message .= "📅 *Vencimento:* {$dataVencimento}\n";
        $message .= "📋 *Descrição:* {$payment->descricao}\n\n";
        $message .= "⚠️ *ATENÇÃO:* Evite juros e multas!\n\n";
        $message .= "📞 *Dúvidas?* Entre em contato conosco!\n\n";
        $message .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";

        return $message;
    }

    /**
     * Build payment due tomorrow message
     */
    protected function buildPaymentDueTomorrowMessage(Payment $payment, $matricula)
    {
        $valor = 'R$ ' . number_format($payment->valor, 2, ',', '.');
        $dataVencimento = $payment->data_vencimento->format('d/m/Y');
        $parcela = $payment->numero_parcela > 1 ? " ({$payment->numero_parcela}/{$payment->total_parcelas})" : '';

        $message = "⏰ *PAGAMENTO VENCE AMANHÃ!*\n\n";
        $message .= "Olá *{$matricula->nome_completo}*!\n\n";
        $message .= "Seu pagamento vence *AMANHÃ*!\n\n";
        $message .= "💰 *Valor:* {$valor}{$parcela}\n";
        $message .= "📅 *Vencimento:* {$dataVencimento}\n";
        $message .= "📋 *Descrição:* {$payment->descricao}\n\n";
        $message .= "💡 *DICA:* Pague hoje e evite atrasos!\n\n";
        $message .= "📞 *Dúvidas?* Entre em contato conosco!\n\n";
        $message .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";

        return $message;
    }

    /**
     * Send payment due today notification
     */
    public function sendPaymentDueTodayNotification(Payment $payment)
    {
        $matricula = $payment->matricula;
        if (!$matricula) {
            Log::warning('Tentativa de enviar notificação para pagamento sem matrícula', [
                'payment_id' => $payment->id
            ]);
            return;
        }

        try {
            $message = $this->buildPaymentDueTodayMessage($payment, $matricula);
            $this->whatsAppService->sendMessage($matricula->telefone_celular, $message);
            
            Log::info('Notificação de vencimento hoje enviada com sucesso', [
                'payment_id' => $payment->id,
                'phone' => $matricula->telefone_celular
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação de vencimento hoje: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'phone' => $matricula->telefone_celular
            ]);
        }
    }

    /**
     * Send payment due tomorrow notification
     */
    public function sendPaymentDueTomorrowNotification(Payment $payment)
    {
        $matricula = $payment->matricula;
        if (!$matricula) {
            Log::warning('Tentativa de enviar notificação para pagamento sem matrícula', [
                'payment_id' => $payment->id
            ]);
            return;
        }

        try {
            $message = $this->buildPaymentDueTomorrowMessage($payment, $matricula);
            $this->whatsAppService->sendMessage($matricula->telefone_celular, $message);
            
            Log::info('Notificação de vencimento amanhã enviada com sucesso', [
                'payment_id' => $payment->id,
                'phone' => $matricula->telefone_celular
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação de vencimento amanhã: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'phone' => $matricula->telefone_celular
            ]);
        }
    }

    /**
     * Send payment upcoming notification
     */
    public function sendPaymentUpcomingNotification(Payment $payment)
    {
        $matricula = $payment->matricula;
        if (!$matricula) {
            Log::warning('Tentativa de enviar notificação para pagamento sem matrícula', [
                'payment_id' => $payment->id
            ]);
            return;
        }

        try {
            $message = $this->buildPaymentUpcomingMessage($payment, $matricula);
            $this->whatsAppService->sendMessage($matricula->telefone_celular, $message);
            
            Log::info('Notificação de vencimento próximo enviada com sucesso', [
                'payment_id' => $payment->id,
                'phone' => $matricula->telefone_celular
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação de vencimento próximo: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'phone' => $matricula->telefone_celular
            ]);
        }
    }

    /**
     * Build payment upcoming message
     */
    protected function buildPaymentUpcomingMessage(Payment $payment, $matricula)
    {
        $valor = 'R$ ' . number_format($payment->valor, 2, ',', '.');
        $dataVencimento = $payment->data_vencimento->format('d/m/Y');
        $parcela = $payment->numero_parcela > 1 ? " ({$payment->numero_parcela}/{$payment->total_parcelas})" : '';

        $message = "📅 *LEMBRETE DE PAGAMENTO*\n\n";
        $message .= "Olá *{$matricula->nome_completo}*!\n\n";
        $message .= "Seu pagamento vence em 3 dias!\n\n";
        $message .= "💰 *Valor:* {$valor}{$parcela}\n";
        $message .= "📅 *Vencimento:* {$dataVencimento}\n";
        $message .= "📋 *Descrição:* {$payment->descricao}\n\n";
        $message .= "📱 *Prepare-se:* Organize o pagamento!\n\n";
        $message .= "📞 *Dúvidas?* Entre em contato conosco!\n\n";
        $message .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";

        return $message;
    }

    /**
     * Enviar mensagem consolidada do WhatsApp com resumo de todos os pagamentos
     */
    protected function sendConsolidatedWhatsAppMessage(Payment $firstPayment, $matricula)
    {
        try {
            // Verificar se tem telefone válido
            $phone = $matricula->telefone_celular ?: $matricula->telefone_fixo;
            
            if (!$phone) {
                Log::warning('Telefone não encontrado para envio de WhatsApp consolidado', [
                    'matricula_id' => $matricula->id
                ]);
                return;
            }

            // Verificar se WhatsApp está configurado
            if (!$this->whatsAppService->hasValidSettings()) {
                throw new \Exception('WhatsApp não está configurado');
            }

            // Buscar todos os payments da matrícula
            $payments = $matricula->payments()->where('status', 'pending')->orderBy('numero_parcela')->get();
            
            if ($payments->isEmpty()) {
                return;
            }

            // Construir mensagem consolidada com links
            $message = "💳 *PAGAMENTOS GERADOS*\n\n";
            $message .= "Olá *{$matricula->nome_completo}*!\n\n";
            
            if ($payments->count() > 1) {
                $message .= "Seus {$payments->count()} pagamentos foram gerados:\n\n";
            } else {
                $message .= "Seu pagamento foi gerado:\n\n";
            }
            
            // Buscar payments com links do MP
            $paymentsComLinks = $payments->filter(function($payment) {
                return $payment->mercadopago_data && 
                       isset($payment->mercadopago_data['transactions']['payments'][0]['payment_method']['ticket_url']);
            });
            
            foreach ($payments as $payment) {
                $message .= "🧾 *Parcela {$payment->numero_parcela}:*\n";
                $message .= "💰 R$ " . number_format($payment->valor, 2, ',', '.') . " - " . $payment->data_vencimento->format('d/m/Y') . "\n";
                
                // Adicionar link do pagamento se disponível
                $paymentLink = null;
                
                if ($payment->mercadopago_data) {
                    // Para boleto e PIX: buscar ticket_url
                    if (isset($payment->mercadopago_data['transactions']['payments'][0]['payment_method']['ticket_url'])) {
                        $paymentLink = $payment->mercadopago_data['transactions']['payments'][0]['payment_method']['ticket_url'];
                    }
                    // Para cartão de crédito: buscar init_point
                    elseif (isset($payment->mercadopago_data['init_point'])) {
                        $paymentLink = $payment->mercadopago_data['init_point'];
                    }
                }
                
                if ($paymentLink) {
                    $message .= "🔗 {$paymentLink}\n";
                } else {
                    $message .= "⏳ *Link em processamento...*\n";
                }
                $message .= "\n";
            }
            
            $message .= "📱 *Como pagar:*\n";
            $message .= "1. Clique no link da parcela\n";
            $message .= "2. Imprima ou pague online\n";
            $message .= "3. Confirmação em até 72h úteis\n\n";
            
            if ($paymentsComLinks->count() < $payments->count()) {
                $message .= "ℹ️ *Links em processamento serão enviados em breve*\n\n";
            }
            
            $message .= "📞 *Dúvidas?* Entre em contato conosco!\n\n";
            $message .= "_Atenciosamente,_\n*Equipe EJA Supletivo*";

            // Enviar mensagem única
            $result = $this->whatsAppService->sendMessage($phone, $message);

            if ($result['success']) {
                Log::info('WhatsApp unificado enviado com sucesso', [
                    'matricula_id' => $matricula->id,
                    'phone' => $phone,
                    'payments_count' => $payments->count(),
                    'links_enviados' => $paymentsComLinks->count()
                ]);
                
            } else {
                throw new \Exception($result['error'] ?? 'Erro desconhecido');
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar WhatsApp consolidado', [
                'matricula_id' => $matricula->id,
                'error' => $e->getMessage()
            ]);
        }
    }


} 