<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class WhatsAppTestCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testar criação de instância WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testando criação de instância WhatsApp...');
        $this->newLine();

        $service = new WhatsAppService();

        // Verificar configurações
        if (!$service->hasValidSettings()) {
            $this->error('❌ Configurações incompletas. Configure primeiro a URL da API, chave e nome da instância.');
            return 1;
        }

        $this->info('✅ Configurações válidas encontradas');
        $this->newLine();

        try {
            // Executar diagnóstico primeiro
            $this->info('📋 Executando diagnóstico...');
            $diagnostic = $service->diagnosticConnectivity();
            
            if (!$diagnostic['success']) {
                $this->error('❌ Falha no diagnóstico:');
                foreach ($diagnostic['tests'] as $test) {
                    if (!$test['success']) {
                        $this->line("   • {$test['name']}: {$test['message']}");
                    }
                }
                return 1;
            }
            
            $this->info('✅ Diagnóstico passou');
            $this->newLine();

            // Verificar se instância já existe
            $this->info('🔍 Verificando se instância já existe...');
            $exists = $service->instanceExists();
            
            if ($exists) {
                $this->warn('⚠️  Instância já existe. Deletando para testar criação...');
                try {
                    $service->deleteInstance();
                    $this->info('✅ Instância deletada');
                    sleep(2); // Aguardar antes de criar
                } catch (\Exception $e) {
                    $this->warn('⚠️  Não foi possível deletar: ' . $e->getMessage());
                }
            } else {
                $this->info('✅ Instância não existe - pode prosseguir');
            }
            
            $this->newLine();

            // Tentar criar instância
            $this->info('🚀 Criando nova instância...');
            $startTime = microtime(true);
            
            $result = $service->createInstance();
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->info("✅ Instância criada com sucesso em {$duration}ms");
            $this->line('📄 Resultado:');
            $this->line('   Instance Name: ' . ($result['instance']['instanceName'] ?? 'N/A'));
            $this->line('   Status: ' . ($result['instance']['status'] ?? 'N/A'));
            $this->line('   Message: ' . ($result['message'] ?? 'N/A'));
            
            $this->newLine();

            // Testar geração de QR Code
            $this->info('📱 Testando geração de QR Code...');
            $startTime = microtime(true);
            
            $qrResult = $service->getQrCode();
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($qrResult['success']) {
                $this->info("✅ QR Code gerado com sucesso em {$duration}ms");
                
                if (isset($qrResult['connected']) && $qrResult['connected']) {
                    $this->info('📱 Dispositivo já conectado');
                } else {
                    $this->info('📱 QR Code disponível para escaneamento');
                    $hasQr = isset($qrResult['qrcode']) && !empty($qrResult['qrcode']);
                    $this->line('   QR Code: ' . ($hasQr ? 'Presente' : 'Ausente'));
                }
            } else {
                $this->error("❌ Falha ao gerar QR Code: " . ($qrResult['message'] ?? 'Erro desconhecido'));
            }

        } catch (\Exception $e) {
            $this->error('❌ Erro durante o teste: ' . $e->getMessage());
            $this->line('📄 Detalhes: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }

        $this->newLine();
        $this->info('🎉 Teste concluído com sucesso!');
        return 0;
    }
}
