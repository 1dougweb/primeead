<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;

class UpdateContractVariables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:update-variables 
                            {--id= : ID específico do contrato para atualizar}
                            {--all : Atualizar todos os contratos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualizar variáveis dos contratos com dados atuais da matrícula';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('id')) {
            $this->updateSingleContract($this->option('id'));
        } elseif ($this->option('all')) {
            $this->updateAllContracts();
        } else {
            $this->error('Você deve especificar --id=X ou --all');
            return 1;
        }

        return 0;
    }

    /**
     * Atualizar um contrato específico
     */
    private function updateSingleContract($id)
    {
        $contract = Contract::find($id);
        
        if (!$contract) {
            $this->error("Contrato com ID {$id} não encontrado.");
            return;
        }

        $this->info("Atualizando contrato: {$contract->contract_number}");
        
        if ($contract->updateVariables()) {
            $this->info("✅ Contrato {$contract->contract_number} atualizado com sucesso!");
            
            // Mostrar algumas variáveis atualizadas
            $this->table(['Variável', 'Valor'], [
                ['Valor Matrícula', $contract->variables['enrollment_value'] ?? 'N/A'],
                ['Valor Mensalidade', $contract->variables['tuition_value'] ?? 'N/A'],
                ['Forma de Pagamento', $contract->variables['payment_method'] ?? 'N/A'],
                ['Nome do Aluno', $contract->variables['student_name'] ?? 'N/A'],
            ]);
        } else {
            $this->error("❌ Erro ao atualizar contrato {$contract->contract_number}");
        }
    }

    /**
     * Atualizar todos os contratos
     */
    private function updateAllContracts()
    {
        $contracts = Contract::with('matricula')->get();
        
        if ($contracts->isEmpty()) {
            $this->info('Nenhum contrato encontrado.');
            return;
        }

        $this->info("Encontrados {$contracts->count()} contratos para atualizar.");
        
        $bar = $this->output->createProgressBar($contracts->count());
        $bar->start();

        $updated = 0;
        $failed = 0;

        foreach ($contracts as $contract) {
            if ($contract->updateVariables()) {
                $updated++;
            } else {
                $failed++;
                $this->newLine();
                $this->error("❌ Erro ao atualizar contrato {$contract->contract_number}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("✅ Atualização concluída!");
        $this->info("📊 Contratos atualizados: {$updated}");
        
        if ($failed > 0) {
            $this->warn("⚠️  Contratos com erro: {$failed}");
        }
    }
}
