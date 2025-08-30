<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class FixMercadoPagoIdsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:mercadopago-ids {--dry-run : Executar sem fazer alterações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrigir IDs do Mercado Pago nos pagamentos existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando pagamentos com IDs de ordem...');

        // Buscar pagamentos com IDs de ordem
        $paymentsWithOrderIds = Payment::whereNotNull('mercadopago_id')
            ->where('mercadopago_id', 'like', 'ORD%')
            ->get();

        $this->info("Encontrados {$paymentsWithOrderIds->count()} pagamentos com IDs de ordem");

        if ($paymentsWithOrderIds->isEmpty()) {
            $this->info('Nenhum pagamento com ID de ordem encontrado.');
            return 0;
        }

        $this->table(
            ['ID', 'MercadoPago ID', 'Valor', 'Status'],
            $paymentsWithOrderIds->map(function ($payment) {
                return [
                    $payment->id,
                    $payment->mercadopago_id,
                    'R$ ' . number_format($payment->valor, 2),
                    $payment->status
                ];
            })
        );

        if ($this->option('dry-run')) {
            $this->warn('Modo dry-run: Nenhuma alteração será feita');
            return 0;
        }

        if (!$this->confirm('Deseja limpar os IDs de ordem dos pagamentos? Esta ação não pode ser desfeita.')) {
            $this->info('Operação cancelada.');
            return 0;
        }

        $updated = 0;
        foreach ($paymentsWithOrderIds as $payment) {
            try {
                $oldId = $payment->mercadopago_id;
                
                // Limpar o ID de ordem
                $payment->update([
                    'mercadopago_id' => null,
                    'mercadopago_status' => null
                ]);

                $this->line("✓ Pagamento ID {$payment->id}: {$oldId} → null");
                $updated++;

                Log::info('ID de ordem removido do pagamento', [
                    'payment_id' => $payment->id,
                    'old_mercadopago_id' => $oldId
                ]);

            } catch (\Exception $e) {
                $this->error("✗ Erro ao atualizar pagamento ID {$payment->id}: {$e->getMessage()}");
            }
        }

        $this->info("✓ {$updated} pagamentos atualizados com sucesso!");

        // Verificar pagamentos restantes
        $remainingPayments = Payment::whereNotNull('mercadopago_id')->get();
        $this->info("Total de pagamentos com mercadopago_id: {$remainingPayments->count()}");

        return 0;
    }
} 