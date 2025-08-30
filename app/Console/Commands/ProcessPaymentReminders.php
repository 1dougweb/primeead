<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentSchedule;
use App\Models\PaymentNotification;
use App\Models\SystemSetting;
use App\Services\MercadoPagoService;
use App\Services\WhatsAppService;
use App\Mail\PaymentReminderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:process-reminders {--dry-run : Run without sending notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process payment reminders and send notifications for overdue and upcoming payments';

    protected $mercadoPagoService;
    protected $whatsAppService;
    protected $paymentSettings;

    /**
     * Create a new command instance.
     */
    public function __construct(MercadoPagoService $mercadoPagoService, WhatsAppService $whatsAppService)
    {
        parent::__construct();
        $this->mercadoPagoService = $mercadoPagoService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->paymentSettings = SystemSetting::getPaymentSettings();
        
        if (!$this->paymentSettings['mercadopago_enabled']) {
            $this->info('Sistema de pagamentos desabilitado. Comando cancelado.');
            return;
        }

        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 Executando em modo dry-run - nenhuma notificação será enviada');
        }

        $this->info('🚀 Iniciando processamento de lembretes de pagamento...');

        // Processar pagamentos em atraso
        $this->processOverduePayments($dryRun);

        // Processar pagamentos próximos do vencimento
        $this->processUpcomingPayments($dryRun);

        // Processar agendamentos de pagamento (desabilitado - modelo não existe)
        // $this->processPaymentSchedules($dryRun);

        // Sincronizar status com Mercado Pago
        $this->syncMercadoPagoStatus($dryRun);

        $this->info('✅ Processamento concluído!');
    }

    /**
     * Process overdue payments
     */
    protected function processOverduePayments($dryRun = false)
    {
        $this->info('📋 Processando pagamentos em atraso...');

        $overduePayments = Payment::where('status', 'pending')
            ->where('data_vencimento', '<', now()->startOfDay())
            ->get();

        $this->info("Encontrados {$overduePayments->count()} pagamentos em atraso");

        foreach ($overduePayments as $payment) {
            $daysOverdue = now()->diffInDays($payment->data_vencimento);
            
            $matricula = $payment->matricula;
            $studentName = $matricula ? $matricula->nome_completo : 'Sem matrícula';
            $this->line("  • Pagamento #{$payment->id} - {$studentName} - {$daysOverdue} dias em atraso");

            // Verificar se já foi enviado lembrete recentemente
            $recentNotification = PaymentNotification::where('payment_id', $payment->id)
                ->where('type', 'overdue_reminder')
                ->where('created_at', '>=', now()->subDays(1))
                ->exists();

            if ($recentNotification) {
                $this->line("    ⏭️  Lembrete já enviado nas últimas 24h");
                continue;
            }

            // Determinar frequência de lembretes baseada nos dias em atraso
            $shouldSendReminder = false;
            if ($daysOverdue <= 7) {
                $shouldSendReminder = true; // Diário na primeira semana
            } elseif ($daysOverdue <= 30) {
                $shouldSendReminder = ($daysOverdue % 3 === 0); // A cada 3 dias
            } else {
                $shouldSendReminder = ($daysOverdue % 7 === 0); // Semanal após 30 dias
            }

            if ($shouldSendReminder) {
                $this->sendPaymentReminder($payment, 'overdue', $dryRun);
            }
        }
    }

    /**
     * Process upcoming payments
     */
    protected function processUpcomingPayments($dryRun = false)
    {
        $this->info('📅 Processando pagamentos próximos do vencimento...');

        $upcomingPayments = Payment::where('status', 'pending')
            ->whereBetween('data_vencimento', [
                now()->addDays(1)->startOfDay(),
                now()->addDays(7)->endOfDay()
            ])
            ->get();

        $this->info("Encontrados {$upcomingPayments->count()} pagamentos próximos do vencimento");

        foreach ($upcomingPayments as $payment) {
            $daysUntilDue = now()->diffInDays($payment->due_date, false);
            
            $matricula = $payment->matricula;
            $studentName = $matricula ? $matricula->nome_completo : 'Sem matrícula';
            $this->line("  • Pagamento #{$payment->id} - {$studentName} - vence em {$daysUntilDue} dias");

            // Enviar lembrete 7, 3 e 1 dias antes do vencimento
            if (in_array($daysUntilDue, [7, 3, 1])) {
                // Verificar se já foi enviado lembrete para este período
                $recentNotification = PaymentNotification::where('payment_id', $payment->id)
                    ->where('type', 'upcoming_reminder')
                    ->where('created_at', '>=', now()->subHours(12))
                    ->exists();

                if (!$recentNotification) {
                    $this->sendPaymentReminder($payment, 'upcoming', $dryRun);
                }
            }
        }
    }

    /**
     * Process payment schedules
     */
    protected function processPaymentSchedules($dryRun = false)
    {
        $this->info('🔄 Processando agendamentos de pagamento...');

        $activeSchedules = PaymentSchedule::where('status', 'active')
            ->with(['paymentPlan', 'payments'])
            ->get();

        $this->info("Encontrados {$activeSchedules->count()} agendamentos ativos");

        foreach ($activeSchedules as $schedule) {
            $this->line("  • Agendamento #{$schedule->id} - {$schedule->student_name}");

            // Verificar se precisa gerar próximo pagamento
            $nextPaymentDate = $schedule->getNextPaymentDate();
            
            if ($nextPaymentDate && $nextPaymentDate->isPast()) {
                $this->line("    📝 Gerando próximo pagamento para {$nextPaymentDate->format('d/m/Y')}");
                
                if (!$dryRun) {
                    $this->generateNextPayment($schedule);
                }
            }

            // Verificar se agendamento foi completado
            if ($schedule->isCompleted()) {
                $this->line("    ✅ Agendamento completado");
                
                if (!$dryRun) {
                    $schedule->update([
                        'status' => 'completed',
                        'completed_at' => now()
                    ]);
                }
            }
        }
    }

    /**
     * Sync payment status with Mercado Pago
     */
    protected function syncMercadoPagoStatus($dryRun = false)
    {
        $this->info('🔄 Sincronizando status com Mercado Pago...');

        $paymentsToSync = Payment::where('status', 'pending')
            ->whereNotNull('mercadopago_id')
            ->where('updated_at', '<', now()->subMinutes(30))
            ->limit(50)
            ->get();

        $this->info("Sincronizando {$paymentsToSync->count()} pagamentos");

        foreach ($paymentsToSync as $payment) {
            try {
                $mpPayment = $this->mercadoPagoService->getPayment($payment->mercadopago_id);
                
                if ($mpPayment && $mpPayment->status !== $payment->mercadopago_status) {
                    $newStatus = $this->mercadoPagoService->mapPaymentStatus($mpPayment->status);
                    
                    $this->line("  • Pagamento #{$payment->id}: {$payment->status} → {$newStatus}");
                    
                    if (!$dryRun) {
                        $payment->update([
                            'status' => $newStatus,
                            'mercadopago_status' => $mpPayment->status,
                            'mercadopago_status_detail' => $mpPayment->status_detail,
                            'paid_at' => $mpPayment->status === 'approved' ? now() : null,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $this->error("Erro ao sincronizar pagamento #{$payment->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Send payment reminder
     */
    protected function sendPaymentReminder(Payment $payment, $type, $dryRun = false)
    {
        $this->line("    📧 Enviando lembrete de pagamento ({$type})");

        if ($dryRun) {
            $this->line("    ⏭️  Simulação - lembrete não enviado");
            return;
        }

        try {
            // Criar registro de notificação
            $matricula = $payment->matricula;
            $email = $matricula ? $matricula->email : null;
            
            $notification = PaymentNotification::create([
                'payment_id' => $payment->id,
                'type' => $type === 'overdue' ? 'overdue_reminder' : 'upcoming_reminder',
                'channel' => 'email',
                'recipient' => $email,
                'status' => 'pending',
                'scheduled_at' => now(),
            ]);

            // Enviar email
            if (($this->paymentSettings['mercadopago_email_notifications'] ?? true) && $email) {
                Mail::to($email)->send(new PaymentReminderMail($payment, $type));
                
                $notification->update([
                    'status' => 'sent',
                    'sent_at' => now()
                ]);
                
                $this->line("    ✅ Email enviado");
            }

            // Enviar WhatsApp
            $matricula = $payment->matricula;
            $phone = $matricula ? $matricula->telefone_celular : null;
            
            if (($this->paymentSettings['mercadopago_whatsapp_notifications'] ?? false) && $phone) {
                try {
                    $whatsAppNotification = PaymentNotification::create([
                        'payment_id' => $payment->id,
                        'type' => $type === 'overdue' ? 'overdue_reminder' : 'upcoming_reminder',
                        'channel' => 'whatsapp',
                        'recipient' => $phone,
                        'status' => 'pending',
                        'scheduled_at' => now(),
                    ]);

                    $message = $this->buildWhatsAppMessage($payment, $type);
                    $this->whatsAppService->sendMessage($phone, $message);
                    
                    $whatsAppNotification->update([
                        'status' => 'sent',
                        'sent_at' => now()
                    ]);
                    
                    $this->line("    ✅ WhatsApp enviado");
                } catch (\Exception $e) {
                    $this->error("    ❌ Erro ao enviar WhatsApp: {$e->getMessage()}");
                }
            }

        } catch (\Exception $e) {
            $this->error("    ❌ Erro ao enviar lembrete: {$e->getMessage()}");
            
            if (isset($notification)) {
                $notification->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Generate next payment for schedule
     */
    protected function generateNextPayment(PaymentSchedule $schedule)
    {
        $nextPaymentNumber = $schedule->payments_made + 1;
        $nextPaymentDate = $schedule->getNextPaymentDate();

        $payment = Payment::create([
            'payment_plan_id' => $schedule->payment_plan_id,
            'payment_schedule_id' => $schedule->id,
            'payer_name' => $schedule->student_name,
            'payer_email' => $schedule->student_email,
            'payer_phone' => $schedule->student_phone,
            'amount' => $schedule->monthly_amount,
            'due_date' => $nextPaymentDate,
            'status' => 'pending',
            'installment_number' => $nextPaymentNumber,
            'total_installments' => $schedule->total_installments,
            'description' => "Mensalidade {$nextPaymentNumber}/{$schedule->total_installments} - {$schedule->course_name}",
        ]);

        Log::info('Próximo pagamento gerado automaticamente', [
            'payment_id' => $payment->id,
            'schedule_id' => $schedule->id,
            'due_date' => $nextPaymentDate->format('Y-m-d')
        ]);

        return $payment;
    }

    /**
     * Build WhatsApp message
     */
    protected function buildWhatsAppMessage(Payment $payment, $type)
    {
        $dueDate = $payment->data_vencimento->format('d/m/Y');
        $amount = 'R$ ' . number_format($payment->valor, 2, ',', '.');

        $matricula = $payment->matricula;
        $studentName = $matricula ? $matricula->nome_completo : 'Cliente';
        
        if ($type === 'overdue') {
            $daysOverdue = now()->diffInDays($payment->data_vencimento);
            return "🚨 *Pagamento em Atraso*\n\n" .
                   "Olá {$studentName},\n\n" .
                   "Seu pagamento está em atraso há {$daysOverdue} dias.\n\n" .
                   "💰 Valor: {$amount}\n" .
                   "📅 Vencimento: {$dueDate}\n\n" .
                   "Por favor, regularize sua situação o quanto antes.\n\n" .
                   "Em caso de dúvidas, entre em contato conosco.";
        } else {
            $daysUntilDue = now()->diffInDays($payment->data_vencimento, false);
            return "⏰ *Lembrete de Pagamento*\n\n" .
                   "Olá {$studentName},\n\n" .
                   "Seu pagamento vence em {$daysUntilDue} dias.\n\n" .
                   "💰 Valor: {$amount}\n" .
                   "📅 Vencimento: {$dueDate}\n\n" .
                   "Não se esqueça de efetuar o pagamento até a data de vencimento.\n\n" .
                   "Obrigado!";
        }
    }
}
