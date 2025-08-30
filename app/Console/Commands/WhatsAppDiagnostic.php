<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class WhatsAppDiagnostic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:diagnostic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executar diagnóstico de conectividade da API WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Executando diagnóstico de conectividade WhatsApp...');
        $this->newLine();

        $service = new WhatsAppService();
        $result = $service->diagnosticConnectivity();

        // Status geral
        if ($result['success']) {
            $this->info('✅ Status Geral: SUCESSO');
        } else {
            $this->error('❌ Status Geral: FALHA');
        }

        $this->info('🕒 Timestamp: ' . $result['timestamp']);
        $this->newLine();

        // Configurações
        $this->info('⚙️  Configurações:');
        $this->line('   Base URL: ' . $result['config']['base_url']);
        $this->line('   Instância: ' . $result['config']['instance']);
        $this->line('   API Key: ' . ($result['config']['has_api_key'] ? 'Configurada' : 'Não configurada'));
        $this->newLine();

        // Testes detalhados
        $this->info('🧪 Testes:');
        foreach ($result['tests'] as $key => $test) {
            $status = $test['success'] ? '<fg=green>✅</>' : '<fg=red>❌</>';
            $duration = isset($test['duration_ms']) ? " ({$test['duration_ms']}ms)" : '';
            
            $this->line("   {$status} {$test['name']}: {$test['message']}{$duration}");
            
            if (isset($test['error'])) {
                $this->line("      <fg=red>Erro:</> {$test['error']}");
            }
            
            if (isset($test['status_code'])) {
                $this->line("      HTTP Status: {$test['status_code']}");
            }
            
            if (isset($test['instance_exists'])) {
                $existsText = $test['instance_exists'] ? 'SIM' : 'NÃO';
                $this->line("      Instância existe: {$existsText}");
            }
        }

        $this->newLine();

        // Recomendações
        if (!$result['success']) {
            $this->warn('💡 Recomendações:');
            
            foreach ($result['tests'] as $test) {
                if (!$test['success']) {
                    switch ($test['name']) {
                        case 'Conectividade Básica':
                            $this->line('   - Verifique se o servidor Evolution API está online');
                            $this->line('   - Confirme se a URL base está correta');
                            break;
                            
                        case 'Autenticação da API':
                            $this->line('   - Verifique se a API Key está correta e não expirou');
                            $this->line('   - Confirme as configurações no painel da Evolution API');
                            break;
                            
                        case 'Endpoint Create Instance':
                            $this->line('   - O endpoint pode estar temporariamente indisponível');
                            $this->line('   - Tente novamente em alguns minutos');
                            break;
                    }
                }
            }
        } else {
            $this->info('✨ Tudo funcionando corretamente! Você pode usar as funcionalidades do WhatsApp.');
        }

        return $result['success'] ? 0 : 1;
    }
}
