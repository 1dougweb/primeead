<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WhatsAppNetworkTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:network-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testar conectividade de rede com a Evolution API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌐 Testando conectividade de rede com Evolution API...');
        $this->newLine();

        $service = new WhatsAppService();
        
        if (!$service->hasValidSettings()) {
            $this->error('❌ Configurações incompletas.');
            return 1;
        }

        $baseUrl = config('app.evolution_api_base_url', 'https://evolutionapi.autotxt.online');
        
        // Teste 1: Resolução DNS
        $this->info('🔍 Teste 1: Resolução DNS');
        $host = parse_url($baseUrl, PHP_URL_HOST);
        $ip = gethostbyname($host);
        
        if ($ip === $host) {
            $this->error("❌ Falha na resolução DNS para {$host}");
        } else {
            $this->info("✅ DNS resolvido: {$host} → {$ip}");
        }
        $this->newLine();

        // Teste 2: Conectividade básica
        $this->info('🔍 Teste 2: Conectividade HTTP básica');
        try {
            $startTime = microtime(true);
            $response = Http::timeout(30)->connectTimeout(10)->get($baseUrl);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            if ($response->successful()) {
                $this->info("✅ Conectividade OK ({$duration}ms)");
                $this->line("   Status: {$response->status()}");
            } else {
                $this->warn("⚠️  Resposta HTTP: {$response->status()}");
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro de conectividade: " . $e->getMessage());
        }
        $this->newLine();

        // Teste 3: Endpoint específico fetchInstances
        $this->info('🔍 Teste 3: Endpoint fetchInstances');
        try {
            $startTime = microtime(true);
            
            // Testar sem API key primeiro
            $response = Http::timeout(30)->connectTimeout(10)
                ->get("{$baseUrl}/instance/fetchInstances");
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->line("   Sem API Key: Status {$response->status()} ({$duration}ms)");
            
            if ($response->status() === 401) {
                $this->info("   ✅ Endpoint acessível (401 é esperado sem API key)");
            } else {
                $this->warn("   ⚠️  Status inesperado: {$response->status()}");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erro: " . $e->getMessage());
        }
        $this->newLine();

        // Teste 4: Com API Key
        $this->info('🔍 Teste 4: Com API Key');
        try {
            $startTime = microtime(true);
            $response = Http::timeout(30)->connectTimeout(10)
                ->withHeaders(['apikey' => 'test-key'])
                ->get("{$baseUrl}/instance/fetchInstances");
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->line("   Com API Key teste: Status {$response->status()} ({$duration}ms)");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erro: " . $e->getMessage());
        }
        $this->newLine();

        // Teste 5: Informações do servidor
        $this->info('📋 Informações do Servidor:');
        $this->line('   PHP Version: ' . PHP_VERSION);
        $this->line('   cURL Version: ' . curl_version()['version']);
        $this->line('   Environment: ' . app()->environment());
        $this->line('   User Agent: ' . (Http::getUserAgent() ?? 'N/A'));
        
        $this->newLine();
        
        // Comandos sugeridos
        $this->warn('🔧 Comandos para testar manualmente no servidor:');
        $this->line("curl -I {$baseUrl} --connect-timeout 10");
        $this->line("curl -v {$baseUrl}/instance/fetchInstances --connect-timeout 10");
        $this->line("ping -c 4 {$host}");
        $this->line("traceroute {$host}");
        
        return 0;
    }
}
