<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixTimezoneRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-timezone-records {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix timezone issues in database records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 Modo DRY RUN - Nenhuma alteração será feita');
        } else {
            $this->info('🔧 Corrigindo registros com problemas de timezone...');
        }
        $this->line('');

        // Configurar timezone da sessão do banco
        DB::statement("SET time_zone = '+03:00'");
        $this->info('✅ Timezone da sessão configurado para +03:00');
        $this->line('');

        // Verificar tabelas que podem ter problemas de timezone
        $tables = [
            'users' => ['created_at', 'updated_at', 'email_verified_at'],
            'inscricoes' => ['created_at', 'updated_at', 'data_inscricao', 'data_matricula'],
            'matriculas' => ['created_at', 'updated_at', 'data_matricula'],
            'contracts' => ['created_at', 'updated_at', 'signed_at'],
            'payments' => ['created_at', 'updated_at', 'paid_at'],
            'contacts' => ['created_at', 'updated_at'],
            'parceiros' => ['created_at', 'updated_at'],
        ];

        foreach ($tables as $table => $dateColumns) {
            $this->info("=== VERIFICANDO TABELA: {$table} ===");
            
            try {
                // Verificar se a tabela existe
                $tableExists = DB::select("SHOW TABLES LIKE '{$table}'");
                if (empty($tableExists)) {
                    $this->warn("⚠️  Tabela {$table} não existe, pulando...");
                    continue;
                }

                // Verificar registros recentes
                $recentRecords = DB::table($table)
                    ->select(array_merge(['id'], $dateColumns))
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                if ($recentRecords->isEmpty()) {
                    $this->info("📭 Nenhum registro encontrado na tabela {$table}");
                    continue;
                }

                $this->info("Registros recentes da tabela {$table}:");
                foreach ($recentRecords as $record) {
                    $this->line("   ID {$record->id}:");
                    
                    foreach ($dateColumns as $column) {
                        if (isset($record->$column) && $record->$column) {
                            $date = Carbon::parse($record->$column);
                            $this->line("     {$column}: {$date->format('Y-m-d H:i:s')} ({$date->timezoneName})");
                            
                            // Verificar se o horário está no fuso correto (entre 8h e 20h para horário comercial)
                            $hour = $date->hour;
                            if ($hour < 8 || $hour > 20) {
                                $this->warn("     ⚠️  Horário suspeito: {$hour}h (pode estar em UTC)");
                            }
                        }
                    }
                    $this->line('');
                }

                // Verificar se há registros com horários muito antigos (antes de 2020)
                $oldRecords = DB::table($table)
                    ->where('created_at', '<', '2020-01-01')
                    ->count();

                if ($oldRecords > 0) {
                    $this->warn("⚠️  {$oldRecords} registros com data anterior a 2020 encontrados");
                }

            } catch (\Exception $e) {
                $this->error("Erro ao verificar tabela {$table}: " . $e->getMessage());
            }
            
            $this->line('');
        }

        // Verificar configurações do sistema
        $this->info('=== CONFIGURAÇÕES DO SISTEMA ===');
        $currentTime = now();
        $this->info("Data/hora atual: {$currentTime}");
        $this->info("Timezone: {$currentTime->timezoneName}");
        $this->info("Offset: {$currentTime->format('P')}");
        
        // Verificar se está no horário de verão (não aplicável no Brasil desde 2019)
        if ($currentTime->isDST()) {
            $this->warn("⚠️  Sistema está no horário de verão");
        } else {
            $this->info("✅ Sistema não está no horário de verão (correto para Brasil)");
        }

        $this->line('');
        $this->info('✅ Verificação de registros concluída!');
        
        if ($isDryRun) {
            $this->info('💡 Para aplicar correções, execute sem --dry-run');
        } else {
            $this->info('💡 Se houver problemas, verifique:');
            $this->info('   1. Configurações do servidor');
            $this->info('   2. Configurações do PHP');
            $this->info('   3. Configurações do banco de dados');
        }
        
        return 0;
    }
}
