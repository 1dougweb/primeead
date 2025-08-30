<?php

namespace App\Console\Commands;

use App\Models\Matricula;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Services\MercadoPagoService;
use App\Services\PaymentNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateMonthlyPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:generate-monthly {--dry-run : Run without creating payments} {--matricula-id= : Generate for specific matricula ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly payments for active installment plans';

    protected $mercadoPagoService;
    protected $paymentNotificationService;
    protected $paymentSettings;

    /**
     * Create a new command instance.
     */
    public function __construct(MercadoPagoService $mercadoPagoService, PaymentNotificationService $paymentNotificationService)
    {
        parent::__construct();
        $this->mercadoPagoService = $mercadoPagoService;
        $this->paymentNotificationService = $paymentNotificationService;
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
        $matriculaId = $this->option('matricula-id');
        
        if ($dryRun) {
            $this->info('🔍 Executando em modo dry-run - nenhum pagamento será criado');
        }

        $this->info('🚀 Iniciando geração de mensalidades mensais...');

        // Buscar matrículas com parcelamento ativo
        $query = Matricula::where('parcelas_ativas', true)
            ->where('parcelas_geradas', '<', 'numero_parcelas')
            ->whereNotNull('numero_parcelas')
            ->whereNotNull('valor_mensalidade')
            ->whereNotNull('dia_vencimento');

        if ($matriculaId) {
            $query->where('id', $matriculaId);
        }

        $matriculas = $query->get();

        $this->info("Encontradas {$matriculas->count()} matrículas com parcelamento ativo");

        $totalGenerated = 0;
        $totalErrors = 0;

        foreach ($matriculas as $matricula) {
            try {
                $result = $this->processMatricula($matricula, $dryRun);
                if ($result['generated']) {
                    $totalGenerated++;
                    $this->line("  ✅ Mensalidade gerada para {$matricula->nome_completo} - Parcela {$result['parcela_numero']}");
                } else {
                    $this->line("  ⏭️  {$matricula->nome_completo} - {$result['reason']}");
                }
            } catch (\Exception $e) {
                $totalErrors++;
                $this->error("  ❌ Erro para {$matricula->nome_completo}: {$e->getMessage()}");
                Log::error('Erro ao gerar mensalidade', [
                    'matricula_id' => $matricula->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("✅ Processamento concluído!");
        $this->info("📊 Resumo: {$totalGenerated} mensalidades geradas, {$totalErrors} erros");
    }

    /**
     * Process a single matricula for payment generation
     */
    protected function processMatricula(Matricula $matricula, $dryRun = false)
    {
        $nextParcelaNumber = $matricula->parcelas_geradas + 1;
        
        // Verificar se ainda há parcelas a serem geradas
        if ($nextParcelaNumber > $matricula->numero_parcelas) {
            return [
                'generated' => false,
                'reason' => 'Todas as parcelas já foram geradas'
            ];
        }

        // Calcular data de vencimento da próxima parcela
        $nextDueDate = $this->calculateNextDueDate($matricula, $nextParcelaNumber);
        
        // Verificar se já é hora de gerar a próxima parcela (7 dias antes do vencimento)
        $shouldGenerate = $nextDueDate->diffInDays(now(), false) <= 7;
        
        if (!$shouldGenerate) {
            return [
                'generated' => false,
                'reason' => "Próxima parcela vence em {$nextDueDate->format('d/m/Y')} - ainda não é hora de gerar"
            ];
        }

        // Verificar se já existe pagamento para esta parcela
        $existingPayment = Payment::where('matricula_id', $matricula->id)
            ->where('numero_parcela', $nextParcelaNumber)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingPayment) {
            return [
                'generated' => false,
                'reason' => "Parcela {$nextParcelaNumber} já existe (ID: {$existingPayment->id})"
            ];
        }

        if ($dryRun) {
            return [
                'generated' => false,
                'reason' => "Simulação - Parcela {$nextParcelaNumber} seria gerada para {$nextDueDate->format('d/m/Y')}"
            ];
        }

        // Criar o pagamento
        $payment = $this->createMonthlyPayment($matricula, $nextParcelaNumber, $nextDueDate);
        
        // Atualizar contador de parcelas geradas
        $matricula->increment('parcelas_geradas');
        
        // Verificar se todas as parcelas foram geradas
        if ($matricula->parcelas_geradas >= $matricula->numero_parcelas) {
            $matricula->update(['parcelas_ativas' => false]);
        }

        return [
            'generated' => true,
            'parcela_numero' => $nextParcelaNumber,
            'payment_id' => $payment->id,
            'due_date' => $nextDueDate->format('d/m/Y')
        ];
    }

    /**
     * Calculate next due date for installment
     */
    protected function calculateNextDueDate(Matricula $matricula, $parcelaNumber)
    {
        // Se há valor de matrícula, as mensalidades começam no próximo mês
        // Parcela 1 = mês 1, Parcela 2 = mês 2, etc. (quando há valor de matrícula)
        // Sem valor de matrícula: Parcela 1 = mês 1, Parcela 2 = mês 2, etc.
        $monthsToAdd = $matricula->valor_matricula > 0 ? $parcelaNumber : $parcelaNumber;
        
        return now()->addMonths($monthsToAdd)->day($matricula->dia_vencimento);
    }

    /**
     * Create monthly payment
     */
    protected function createMonthlyPayment(Matricula $matricula, $parcelaNumber, $dueDate)
    {
        $totalParcelas = $matricula->numero_parcelas + ($matricula->valor_matricula > 0 ? 1 : 0);
        
        $payment = Payment::create([
            'matricula_id' => $matricula->id,
            'valor' => $matricula->valor_mensalidade,
            'forma_pagamento' => $matricula->forma_pagamento_mensalidade ?: $matricula->forma_pagamento,
            'data_vencimento' => $dueDate,
            'descricao' => "Mensalidade {$parcelaNumber}/{$matricula->numero_parcelas} - {$matricula->curso}",
            'numero_parcela' => $matricula->valor_matricula > 0 ? $parcelaNumber + 1 : $parcelaNumber,
            'total_parcelas' => $totalParcelas,
            'status' => 'pending',
        ]);

        // Integrar com Mercado Pago
        if ($this->paymentSettings['mercadopago_enabled']) {
            try {
                $mercadoPagoPayment = $this->mercadoPagoService->createPayment($payment);
                
                $payment->update([
                    'mercadopago_id' => $mercadoPagoPayment['id'],
                    'mercadopago_status' => $mercadoPagoPayment['status'],
                    'mercadopago_data' => $mercadoPagoPayment['full_response']
                ]);

                Log::info('Mensalidade mensal criada no Mercado Pago', [
                    'payment_id' => $payment->id,
                    'mercadopago_id' => $mercadoPagoPayment['id'],
                    'matricula_id' => $matricula->id,
                    'parcela' => $parcelaNumber
                ]);

            } catch (\Exception $e) {
                Log::error('Erro ao criar mensalidade mensal no Mercado Pago', [
                    'payment_id' => $payment->id,
                    'matricula_id' => $matricula->id,
                    'parcela' => $parcelaNumber,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Enviar notificações
        try {
            $this->paymentNotificationService->sendPaymentCreatedNotifications($payment);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificações da mensalidade mensal', [
                'payment_id' => $payment->id,
                'matricula_id' => $matricula->id,
                'error' => $e->getMessage()
            ]);
        }

        return $payment;
    }
}
