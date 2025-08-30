<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Matricula;
use Illuminate\Support\Facades\Log;

class UpdateMatriculaStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matricula:update-status {--force : Force update even if already confirmed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update matrícula status from pre_matricula to matricula_confirmada based on confirmed payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando matrículas que precisam de atualização de status...');
        
        // Find all payments with status 'paid'
        $paidPayments = Payment::where('status', 'paid')
            ->with('matricula')
            ->get();
        
        if ($paidPayments->isEmpty()) {
            $this->warn('❌ Nenhum pagamento confirmado encontrado.');
            return 0;
        }
        
        $this->info("📊 Encontrados {$paidPayments->count()} pagamentos confirmados.");
        
        $updatedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        
        foreach ($paidPayments as $payment) {
            $matricula = $payment->matricula;
            
            if (!$matricula) {
                $this->warn("⚠️  Pagamento {$payment->id} não tem matrícula associada.");
                $errorCount++;
                continue;
            }
            
            $this->line("📝 Processando matrícula {$matricula->id} ({$matricula->nome_completo})");
            $this->line("   Status atual: {$matricula->status}");
            $this->line("   Pagamento: {$payment->id} - R$ {$payment->valor}");
            
            // Check if matrícula needs update
            if ($matricula->status === 'pre_matricula') {
                $oldStatus = $matricula->status;
                $matricula->status = 'matricula_confirmada';
                $matricula->save();
                
                $this->info("✅ Status atualizado: {$oldStatus} → {$matricula->status}");
                $updatedCount++;
                
                // Log the update
                Log::info('Matrícula status updated via command', [
                    'matricula_id' => $matricula->id,
                    'old_status' => $oldStatus,
                    'new_status' => $matricula->status,
                    'payment_id' => $payment->id,
                    'payment_status' => $payment->status,
                    'updated_by' => 'command'
                ]);
                
            } else {
                if ($this->option('force')) {
                    $oldStatus = $matricula->status;
                    $matricula->status = 'matricula_confirmada';
                    $matricula->save();
                    
                    $this->info("🔄 Status forçado: {$oldStatus} → {$matricula->status}");
                    $updatedCount++;
                } else {
                    $this->line("⏭️  Status já confirmado: {$matricula->status}");
                    $skippedCount++;
                }
            }
            
            $this->line('');
        }
        
        // Summary
        $this->newLine();
        $this->info('📋 RESUMO DA EXECUÇÃO:');
        $this->info("✅ Matrículas atualizadas: {$updatedCount}");
        $this->info("⏭️  Matrículas ignoradas: {$skippedCount}");
        $this->info("❌ Erros encontrados: {$errorCount}");
        
        if ($updatedCount > 0) {
            $this->info('🎉 Processo concluído com sucesso!');
        } else {
            $this->warn('⚠️  Nenhuma matrícula foi atualizada.');
        }
        
        return 0;
    }
}
