<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CorrectTimezoneRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:correct-timezone-records {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Correct timezone records from UTC to America/Sao_Paulo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 Modo DRY RUN - Nenhuma alteração será feita');
        } else {
            $this->info('🔧 Corrigindo registros de UTC para America/Sao_Paulo...');
        }
        $this->line('');

        // Configurar timezone da sessão do banco
        DB::statement("SET time_zone = '+03:00'");
        $this->info('✅ Timezone da sessão configurado para +03:00');
        $this->line('');

        // Tabelas e colunas para corrigir
        $tables = [
            'users' => ['created_at', 'updated_at', 'email_verified_at'],
            'parceiros' => ['created_at', 'updated_at'],
        ];

        $totalCorrected = 0;

        foreach ($tables as $table => $dateColumns) {
            $this->info("=== CORRIGINDO TABELA: {$table} ===");
            
            try {
                // Verificar se a tabela existe
                $tableExists = DB::select("SHOW TABLES LIKE '{$table}'");
                if (empty($tableExists)) {
                    $this->warn("⚠️  Tabela {$table} não existe, pulando...");
                    continue;
                }

                foreach ($dateColumns as $column) {
                    // Verificar se a coluna existe
                    $columnExists = DB::select("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
                    if (empty($columnExists)) {
                        $this->warn("⚠️  Coluna {$column} não existe na tabela {$table}, pulando...");
                        continue;
                    }

                    // Encontrar registros com horários suspeitos (UTC)
                    $suspiciousRecords = DB::table($table)
                        ->whereNotNull($column)
                        ->where(function($query) use ($column) {
                            // Horários que parecem estar em UTC (0-6h ou 22-23h)
                            $query->whereRaw("HOUR({$column}) BETWEEN 0 AND 6")
                                  ->orWhereRaw("HOUR({$column}) BETWEEN 22 AND 23");
                        })
                        ->get();

                    if ($suspiciousRecords->isEmpty()) {
                        $this->info("✅ Nenhum registro suspeito encontrado na coluna {$column}");
                        continue;
                    }

                    $this->info("Encontrados {$suspiciousRecords->count()} registros suspeitos na coluna {$column}:");
                    
                    foreach ($suspiciousRecords as $record) {
                        $originalDate = Carbon::parse($record->$column);
                        $correctedDate = $originalDate->addHours(3); // UTC + 3 = America/Sao_Paulo
                        
                        $this->line("   ID {$record->id}:");
                        $this->line("     Original: {$originalDate->format('Y-m-d H:i:s')} (UTC)");
                        $this->line("     Corrigido: {$correctedDate->format('Y-m-d H:i:s')} (America/Sao_Paulo)");
                        
                        if (!$isDryRun) {
                            DB::table($table)
                                ->where('id', $record->id)
                                ->update([$column => $correctedDate]);
                            
                            $this->info("     ✅ Corrigido!");
                            $totalCorrected++;
                        }
                        $this->line('');
                    }
                }

            } catch (\Exception $e) {
                $this->error("Erro ao corrigir tabela {$table}: " . $e->getMessage());
            }
            
            $this->line('');
        }

        // Verificar configurações futuras
        $this->info('=== CONFIGURAÇÕES FUTURAS ===');
        $this->info('Para evitar problemas futuros, certifique-se de:');
        $this->info('1. Configurar o timezone do banco de dados para +03:00');
        $this->info('2. Verificar se o PHP está usando America/Sao_Paulo');
        $this->info('3. Configurar o timezone do servidor se necessário');

        $this->line('');
        if ($isDryRun) {
            $this->info('✅ Verificação concluída!');
            $this->info('💡 Para aplicar as correções, execute sem --dry-run');
        } else {
            $this->info("✅ Correção concluída! {$totalCorrected} registros corrigidos.");
        }
        
        return 0;
    }
}
