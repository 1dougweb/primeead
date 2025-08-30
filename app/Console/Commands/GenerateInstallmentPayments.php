<?php

namespace App\Console\Commands;

use App\Models\Matricula;
use App\Models\Payment;
use App\Services\MercadoPagoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateInstallmentPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:generate-installments {--dry-run : Run without creating payments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate installment payments for matrículas with parceled payments';

    protected $mercadoPagoService;

    /**
     * Create a new command instance.
     */
    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        parent::__construct();
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 Executando em modo dry-run - nenhum pagamento será criado');
        }

        $this->info('🚀 Iniciando geração de pagamentos parcelados...');

        // Buscar matrículas com pagamentos parcelados
        $matriculas = Matricula::where('numero_parcelas', '>', 1)
            ->whereRaw('parcelas_geradas < numero_parcelas')
            ->get();

        $this->info("Encontradas {$matriculas->count()} matrículas com pagamentos parcelados");

        foreach ($matriculas as $matricula) {
            $this->processMatricula($matricula, $dryRun);
        }

        $this->info('✅ Geração de pagamentos parcelados concluída!');
    }

    /**
     * Process a single matrícula
     */
    protected function processMatricula(Matricula $matricula, $dryRun = false)
    {
        $this->line("  📋 Processando matrícula #{$matricula->id} - {$matricula->nome_completo}");

        // Calcular quantas parcelas já foram geradas
        $paymentsCount = Payment::where('matricula_id', $matricula->id)->count();
        $remainingPayments = $matricula->numero_parcelas - $paymentsCount;

        if ($remainingPayments <= 0) {
            $this->line("    ✅ Todas as parcelas já foram geradas");
            return;
        }

        $this->line("    📊 Parcelas restantes: {$remainingPayments}");

        // Calcular data de vencimento da próxima parcela
        $lastPayment = Payment::where('matricula_id', $matricula->id)
            ->orderBy('data_vencimento', 'desc')
            ->first();

        $nextDueDate = $lastPayment 
            ? $lastPayment->data_vencimento->addMonth()
            : Carbon::createFromDate(now()->year, now()->month, $matricula->dia_vencimento);

        // Gerar próximas parcelas
        for ($i = 1; $i <= $remainingPayments; $i++) {
            $paymentNumber = $paymentsCount + $i;
            $dueDate = $nextDueDate->copy()->addMonths($i - 1);

            $this->line("    💳 Gerando parcela {$paymentNumber}/{$matricula->numero_parcelas} - Vencimento: {$dueDate->format('d/m/Y')}");

            if (!$dryRun) {
                try {
                    $payment = Payment::create([
                        'matricula_id' => $matricula->id,
                        'valor' => $matricula->valor_mensalidade,
                        'forma_pagamento' => $matricula->forma_pagamento,
                        'data_vencimento' => $dueDate,
                        'numero_parcela' => $paymentNumber,
                        'total_parcelas' => $matricula->numero_parcelas,
                        'status' => 'pending',
                        'descricao' => "Mensalidade {$paymentNumber}/{$matricula->numero_parcelas} - {$matricula->curso}",
                    ]);

                    // Criar pagamento no Mercado Pago
                    $mpOrder = $this->mercadoPagoService->createOrder([
                        'external_reference' => "pagamento{$payment->id}",
                        'total_amount' => $payment->valor,
                        'description' => $payment->descricao,
                        'payer' => [
                            'email' => $matricula->email,
                            'first_name' => explode(' ', $matricula->nome_completo)[0] ?? '',
                            'last_name' => explode(' ', $matricula->nome_completo)[1] ?? '',
                            'identification' => [
                                'type' => 'CPF',
                                'number' => $matricula->cpf
                            ]
                        ],
                        'payment_method' => [
                            'id' => $matricula->forma_pagamento === 'pix' ? 'pix' : 'bolbradesco',
                            'type' => $matricula->forma_pagamento === 'pix' ? 'bank_transfer' : 'ticket'
                        ]
                    ]);

                    if ($mpOrder) {
                        $payment->update([
                            'mercadopago_id' => $mpOrder['id'],
                            'mercadopago_status' => $mpOrder['status'],
                            'mercadopago_data' => $mpOrder
                        ]);

                        $this->line("    ✅ Pagamento #{$payment->id} criado com sucesso");
                    } else {
                        $this->error("    ❌ Erro ao criar pagamento no Mercado Pago");
                    }

                } catch (\Exception $e) {
                    $this->error("    ❌ Erro ao criar pagamento: {$e->getMessage()}");
                }
            } else {
                $this->line("    ⏭️  Simulação - pagamento não criado");
            }
        }

        // Atualizar contador de parcelas geradas
        if (!$dryRun) {
            $matricula->update([
                'parcelas_geradas' => $matricula->numero_parcelas
            ]);
        }
    }
} 