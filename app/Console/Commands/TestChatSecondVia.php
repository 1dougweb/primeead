<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChatService;
use App\Services\ChatGptService;
use App\Services\BoletoSecondViaService;

class TestChatSecondVia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:test-second-via {message} {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testar se o chat está processando comandos de segunda via corretamente';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $message = $this->argument('message');
        $email = $this->argument('email') ?? 'teste@example.com';
        
        $this->info('🧪 Testando processamento de comandos de segunda via no chat...');
        $this->info("📝 Mensagem: {$message}");
        $this->info("📧 Email: {$email}");
        
        try {
            // Criar instâncias dos serviços
            $chatGptService = app(ChatGptService::class);
            $boletoService = app(BoletoSecondViaService::class);
            $chatService = app(ChatService::class);
            
            $this->info('✅ Serviços carregados com sucesso');
            
            // Testar detecção de comandos
            $this->info("\n🔍 Testando detecção de comandos...");
            
            // Usar reflexão para acessar método protegido
            $reflection = new \ReflectionClass($chatService);
            $processCommandsMethod = $reflection->getMethod('processSecondViaCommands');
            $processCommandsMethod->setAccessible(true);
            
            $commands = $processCommandsMethod->invoke($chatService, $message, $email);
            
            if (empty($commands)) {
                $this->warn('⚠️  Nenhum comando de segunda via detectado');
            } else {
                $this->info('✅ Comandos detectados:');
                foreach ($commands as $command) {
                    $this->info("   - Tipo: {$command['type']}");
                    $this->info("   - Payment ID: " . ($command['payment_id'] ?? 'N/A'));
                    $this->info("   - Ação: {$command['action']}");
                }
            }
            
            // Testar execução de comandos se houver
            if (!empty($commands)) {
                $this->info("\n⚡ Testando execução de comandos...");
                
                $executeCommandsMethod = $reflection->getMethod('executeSecondViaCommands');
                $executeCommandsMethod->setAccessible(true);
                
                $results = $executeCommandsMethod->invoke($chatService, $commands, $email);
                
                $this->info('📊 Resultados da execução:');
                foreach ($results as $result) {
                    if ($result['success']) {
                        $this->info("   ✅ {$result['command']['type']} executado com sucesso");
                        if (isset($result['result']['message'])) {
                            $this->info("      Mensagem: {$result['result']['message']}");
                        }
                    } else {
                        $this->error("   ❌ {$result['command']['type']} falhou: {$result['error']}");
                    }
                }
            }
            
            // Testar processamento completo da mensagem
            $this->info("\n🚀 Testando processamento completo da mensagem...");
            
            $sessionId = 'test_' . time();
            $result = $chatService->processMessage($sessionId, $message, $email);
            
            if ($result['success']) {
                $this->info('✅ Mensagem processada com sucesso');
                $this->info("   Resposta: " . substr($result['response'], 0, 100) . '...');
                $this->info("   Conversation ID: {$result['conversation_id']}");
                
                if (isset($result['second_via_results']) && !empty($result['second_via_results'])) {
                    $this->info('   📋 Resultados de segunda via: ' . count($result['second_via_results']));
                }
            } else {
                $this->error("❌ Erro ao processar mensagem: {$result['error']}");
            }
            
            $this->info("\n🎉 Teste concluído com sucesso!");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Erro durante o teste: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
