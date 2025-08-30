<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Matricula;
use App\Services\PaymentNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SchedulePaymentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:schedule-notifications {--dry-run : Run without sending notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule payment notifications for installment payments based on due dates';

    protected $paymentNotificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(PaymentNotificationService $paymentNotificationService)
    {
        parent::__construct();
        $this->paymentNotificationService = $paymentNotificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 Executando em modo dry-run - nenhuma notificação será enviada');
        }

        $this->info('🚀 Iniciando agendamento de notificações de pagamentos...');

        // Processar pagamentos que vencem hoje
        $this->processTodayPayments($dryRun);

        // Processar pagamentos que vencem amanhã
        $this->processTomorrowPayments($dryRun);

        // Processar pagamentos que vencem em 3 dias
        $this->processUpcomingPayments($dryRun);

        $this->info('✅ Agendamento concluído!');
    }

    /**
     * Process payments due today
     */
    protected function processTodayPayments($dryRun = false)
    {
        $this->info('📅 Processando pagamentos que vencem hoje...');

        $todayPayments = Payment::where('status', 'pending')
            ->whereDate('data_vencimento', now()->startOfDay())
            ->with('matricula')
            ->get();

        $this->info("Encontrados {$todayPayments->count()} pagamentos que vencem hoje");

        foreach ($todayPayments as $payment) {
            $matricula = $payment->matricula;
            $studentName = $matricula ? $matricula->nome_completo : 'Sem matrícula';
            
            $this->line("  • Pagamento #{$payment->id} - {$studentName} - R$ " . number_format($payment->valor, 2));

            if (!$dryRun) {
                try {
                    // Enviar notificação de pagamento vencendo hoje
                    $this->paymentNotificationService->sendPaymentDueTodayNotification($payment);
                    $this->line("    ✅ Notificação enviada");
                } catch (\Exception $e) {
                    $this->error("    ❌ Erro ao enviar notificação: {$e->getMessage()}");
                }
            } else {
                $this->line("    ⏭️  Simulação - notificação não enviada");
            }
        }
    }

    /**
     * Process payments due tomorrow
     */
    protected function processTomorrowPayments($dryRun = false)
    {
        $this->info('📅 Processando pagamentos que vencem amanhã...');

        $tomorrowPayments = Payment::where('status', 'pending')
            ->whereDate('data_vencimento', now()->addDay())
            ->with('matricula')
            ->get();

        $this->info("Encontrados {$tomorrowPayments->count()} pagamentos que vencem amanhã");

        foreach ($tomorrowPayments as $payment) {
            $matricula = $payment->matricula;
            $studentName = $matricula ? $matricula->nome_completo : 'Sem matrícula';
            
            $this->line("  • Pagamento #{$payment->id} - {$studentName} - R$ " . number_format($payment->valor, 2));

            if (!$dryRun) {
                try {
                    // Enviar notificação de pagamento vencendo amanhã
                    $this->paymentNotificationService->sendPaymentDueTomorrowNotification($payment);
                    $this->line("    ✅ Notificação enviada");
                } catch (\Exception $e) {
                    $this->error("    ❌ Erro ao enviar notificação: {$e->getMessage()}");
                }
            } else {
                $this->line("    ⏭️  Simulação - notificação não enviada");
            }
        }
    }

    /**
     * Process payments due in 3 days
     */
    protected function processUpcomingPayments($dryRun = false)
    {
        $this->info('📅 Processando pagamentos que vencem em 3 dias...');

        $upcomingPayments = Payment::where('status', 'pending')
            ->whereDate('data_vencimento', now()->addDays(3))
            ->with('matricula')
            ->get();

        $this->info("Encontrados {$upcomingPayments->count()} pagamentos que vencem em 3 dias");

        foreach ($upcomingPayments as $payment) {
            $matricula = $payment->matricula;
            $studentName = $matricula ? $matricula->nome_completo : 'Sem matrícula';
            
            $this->line("  • Pagamento #{$payment->id} - {$studentName} - R$ " . number_format($payment->valor, 2));

            if (!$dryRun) {
                try {
                    // Enviar notificação de pagamento vencendo em 3 dias
                    $this->paymentNotificationService->sendPaymentUpcomingNotification($payment);
                    $this->line("    ✅ Notificação enviada");
                } catch (\Exception $e) {
                    $this->error("    ❌ Erro ao enviar notificação: {$e->getMessage()}");
                }
            } else {
                $this->line("    ⏭️  Simulação - notificação não enviada");
            }
        }
    }
} 