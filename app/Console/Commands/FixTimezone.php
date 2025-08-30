<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixTimezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-timezone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix and verify timezone settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Verificando e corrigindo configurações de timezone...');
        $this->line('');

        // 1. Verificar timezone da aplicação
        $this->info('=== CONFIGURAÇÕES DA APLICAÇÃO ===');
        $appTimezone = config('app.timezone');
        $this->info("Timezone da aplicação: {$appTimezone}");
        
        $currentTime = now();
        $utcTime = now()->utc();
        $this->info("Data/hora atual (aplicação): {$currentTime}");
        $this->info("Data/hora UTC: {$utcTime}");
        $this->info("Diferença do UTC: " . $currentTime->diffInHours($utcTime) . " horas");
        $this->line('');

        // 2. Verificar timezone do PHP
        $this->info('=== CONFIGURAÇÕES DO PHP ===');
        $phpTimezone = date_default_timezone_get();
        $this->info("Timezone do PHP: {$phpTimezone}");
        
        $phpTime = date('Y-m-d H:i:s');
        $this->info("Data/hora do PHP: {$phpTime}");
        $this->line('');

        // 3. Verificar timezone do banco de dados
        $this->info('=== CONFIGURAÇÕES DO BANCO DE DADOS ===');
        try {
            $dbTimezones = DB::select('SELECT @@global.time_zone as global_tz, @@session.time_zone as session_tz');
            $globalTz = $dbTimezones[0]->global_tz;
            $sessionTz = $dbTimezones[0]->session_tz;
            
            $this->info("Timezone global do banco: {$globalTz}");
            $this->info("Timezone da sessão do banco: {$sessionTz}");
            
            // Verificar se o banco está usando timezone correto
            if ($globalTz === 'SYSTEM' || $sessionTz === 'SYSTEM') {
                $this->warn('⚠️  Banco usando timezone do sistema. Configurando para America/Sao_Paulo...');
                
                // Configurar timezone da sessão
                DB::statement("SET time_zone = '+03:00'");
                $this->info('✅ Timezone da sessão configurado para +03:00');
            }
            
            // Verificar data/hora do banco
            $dbTime = DB::select('SELECT NOW() as `current_time`')[0]->current_time;
            $this->info("Data/hora do banco: {$dbTime}");
            
        } catch (\Exception $e) {
            $this->error("Erro ao verificar banco de dados: " . $e->getMessage());
        }
        $this->line('');

        // 4. Verificar se há problemas de timezone em registros
        $this->info('=== VERIFICANDO REGISTROS ===');
        try {
            // Verificar alguns registros recentes
            $recentRecords = DB::table('users')
                ->select('id', 'name', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
            
            $this->info('Registros recentes de usuários:');
            foreach ($recentRecords as $record) {
                $this->line("   ID {$record->id} - {$record->name}");
                $this->line("   Criado: {$record->created_at}");
                $this->line("   Atualizado: {$record->updated_at}");
                $this->line('');
            }
            
        } catch (\Exception $e) {
            $this->error("Erro ao verificar registros: " . $e->getMessage());
        }

        // 5. Recomendações
        $this->info('=== RECOMENDAÇÕES ===');
        if ($appTimezone === 'America/Sao_Paulo') {
            $this->info('✅ Timezone da aplicação está correto');
        } else {
            $this->warn('⚠️  Timezone da aplicação precisa ser corrigido para America/Sao_Paulo');
        }
        
        if ($phpTimezone === 'America/Sao_Paulo') {
            $this->info('✅ Timezone do PHP está correto');
        } else {
            $this->warn('⚠️  Timezone do PHP precisa ser corrigido para America/Sao_Paulo');
        }

        $this->line('');
        $this->info('✅ Verificação de timezone concluída!');
        
        return 0;
    }
}
