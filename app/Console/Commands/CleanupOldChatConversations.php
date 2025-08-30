<?php

namespace App\Console\Commands;

use App\Services\ChatService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupOldChatConversations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:cleanup {--days=30 : Número de dias para considerar conversas como antigas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpar conversas antigas do chat para liberar espaço no banco de dados';

    /**
     * Execute the console command.
     */
    public function handle(ChatService $chatService)
    {
        $days = (int) $this->option('days');
        
        $this->info("Iniciando limpeza de conversas do chat com mais de {$days} dias...");
        
        try {
            $deletedCount = $chatService->cleanupOldConversations($days);
            
            if ($deletedCount > 0) {
                $this->info("✅ {$deletedCount} conversas antigas foram removidas com sucesso!");
                
                // Obter estatísticas atualizadas
                $stats = $chatService->getChatStats();
                $this->table(
                    ['Métrica', 'Valor'],
                    [
                        ['Total de Conversas', $stats['total_conversations']],
                        ['Conversas Ativas', $stats['active_conversations']],
                        ['Total de Mensagens', $stats['total_messages']],
                        ['Mensagens Hoje', $stats['today_messages']],
                        ['Média Msgs/Conversa', $stats['avg_messages_per_conversation']]
                    ]
                );
            } else {
                $this->info("ℹ️  Nenhuma conversa antiga foi encontrada para remoção.");
            }
            
            $this->info("Limpeza concluída com sucesso!");
            
        } catch (\Exception $e) {
            $this->error("❌ Erro durante a limpeza: " . $e->getMessage());
            Log::error('Erro no comando de limpeza do chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
        
        return 0;
    }
}
