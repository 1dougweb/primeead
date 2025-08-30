<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Mail\ContractReminderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendContractReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:send-reminders {--days=7,3,1 : Days before expiration to send reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails for contracts that are about to expire';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando envio de lembretes de contratos...');

        // Obter dias de lembrete da opção
        $reminderDays = explode(',', $this->option('days'));
        $reminderDays = array_map('intval', $reminderDays);

        $totalSent = 0;
        $totalErrors = 0;

        foreach ($reminderDays as $days) {
            $this->info("Processando contratos que expiram em {$days} dias...");

            // Buscar contratos que expiram em X dias
            $targetDate = Carbon::now()->addDays($days)->startOfDay();
            $endDate = $targetDate->copy()->endOfDay();

            $contracts = Contract::where('status', 'sent')
                ->orWhere('status', 'viewed')
                ->whereBetween('access_expires_at', [$targetDate, $endDate])
                ->whereDoesntHave('remindersSent', function($query) use ($days) {
                    $query->where('reminder_type', 'expiration')
                          ->where('days_before', $days)
                          ->where('sent_at', '>=', Carbon::now()->subDay());
                })
                ->get();

            $this->info("Encontrados {$contracts->count()} contratos para lembrete de {$days} dias.");

            foreach ($contracts as $contract) {
                try {
                    // Enviar email de lembrete
                    Mail::to($contract->student_email)->send(new ContractReminderMail($contract, $days));

                    // Registrar o envio (implementar tabela de controle depois)
                    $this->line("✅ Lembrete enviado para {$contract->student_email} - Contrato {$contract->contract_number}");
                    $totalSent++;

                } catch (\Exception $e) {
                    $this->error("❌ Erro ao enviar para {$contract->student_email}: {$e->getMessage()}");
                    $totalErrors++;
                }
            }
        }

        // Verificar contratos expirados e atualizar status
        $this->info('Verificando contratos expirados...');
        $expiredContracts = Contract::whereIn('status', ['sent', 'viewed'])
            ->where('access_expires_at', '<', Carbon::now())
            ->get();

        foreach ($expiredContracts as $contract) {
            $contract->update(['status' => 'expired']);
            $this->line("⏰ Contrato {$contract->contract_number} marcado como expirado");
        }

        $this->info("Processamento concluído!");
        $this->info("📧 Total de lembretes enviados: {$totalSent}");
        $this->info("❌ Total de erros: {$totalErrors}");
        $this->info("⏰ Contratos expirados atualizados: {$expiredContracts->count()}");

        return Command::SUCCESS;
    }
} 