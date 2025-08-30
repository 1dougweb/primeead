<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BoletoSecondViaService;
use App\Models\Payment;

class TestBoletoSecondVia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boleto:test {payment_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testar funcionalidade de segunda via de boleto';

    /**
     * Execute the console command.
     */
    public function handle(BoletoSecondViaService $boletoService): int
    {
        $this->info('🧪 Testando funcionalidade de segunda via de boleto...');
        
        try {
            // Verificar se o serviço está funcionando
            $this->info('✅ Serviço BoletoSecondViaService carregado com sucesso');
            
            // Verificar se há pagamentos no sistema
            $paymentsCount = Payment::count();
            $this->info("📊 Total de pagamentos no sistema: {$paymentsCount}");
            
            if ($paymentsCount === 0) {
                $this->warn('⚠️  Nenhum pagamento encontrado no sistema');
                return 0;
            }
            
            // Se um payment_id foi fornecido, testar com ele
            if ($paymentId = $this->argument('payment_id')) {
                $payment = Payment::with('matricula')->find($paymentId);
                
                if (!$payment) {
                    $this->error("❌ Pagamento com ID {$paymentId} não encontrado");
                    return 1;
                }
                
                $this->info("🔍 Testando com pagamento ID: {$paymentId}");
                $this->info("   Descrição: {$payment->descricao}");
                $this->info("   Valor: R$ " . number_format($payment->valor, 2, ',', '.'));
                $this->info("   Status: {$payment->status}");
                
                // Verificar elegibilidade
                $eligibility = $boletoService->canGenerateSecondVia($payment);
                
                $this->info("\n📋 Verificação de elegibilidade:");
                $this->info("   Pode gerar: " . ($eligibility['can_generate'] ? '✅ Sim' : '❌ Não'));
                $this->info("   Vias atuais: {$eligibility['current_vias']}");
                $this->info("   Máximo de vias: {$eligibility['max_vias']}");
                
                if (!empty($eligibility['reasons'])) {
                    $this->warn("   Motivos para não poder gerar:");
                    foreach ($eligibility['reasons'] as $reason) {
                        $this->warn("     - {$reason}");
                    }
                }
                
                // Verificar histórico de vias
                $history = $boletoService->getBoletoViasHistory($payment);
                $this->info("\n📚 Histórico de vias:");
                $this->info("   Total de vias: " . count($history));
                
                foreach ($history as $via) {
                    $status = match($via['status']) {
                        'active' => '🟢 Ativa',
                        'expired' => '🔴 Expirada',
                        'paid' => '✅ Paga',
                        'cancelled' => '❌ Cancelada',
                        default => '❓ Desconhecido'
                    };
                    
                    $this->info("     {$via['via_number_formatted']}: {$status} - {$via['generated_at']}");
                }
                
            } else {
                // Mostrar estatísticas gerais
                $stats = $boletoService->getBoletoViasStats();
                
                $this->info("\n📊 Estatísticas gerais de vias de boleto:");
                $this->info("   Total de vias: {$stats['total_vias']}");
                $this->info("   Vias ativas: {$stats['active_vias']}");
                $this->info("   Vias expiradas: {$stats['expired_vias']}");
                $this->info("   Vias pagas: {$stats['paid_vias']}");
                $this->info("   Vias canceladas: {$stats['cancelled_vias']}");
                $this->info("   Taxa de sucesso: {$stats['success_rate']}%");
            }
            
            $this->info("\n🎉 Teste concluído com sucesso!");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Erro durante o teste: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
