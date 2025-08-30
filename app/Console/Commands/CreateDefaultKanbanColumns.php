<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\KanbanColumn;

class CreateDefaultKanbanColumns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kanban:create-default-columns {--user-id= : Create columns for specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default Kanban columns for users who don\'t have any columns';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        if ($userId) {
            // Criar colunas para usuário específico
            $user = User::find($userId);
            if (!$user) {
                $this->error("Usuário com ID {$userId} não encontrado.");
                return 1;
            }
            
            $this->createColumnsForUser($user);
        } else {
            // Criar colunas para todos os usuários que não têm colunas
            $usersWithoutColumns = User::whereDoesntHave('kanbanColumns')->get();
            
            if ($usersWithoutColumns->isEmpty()) {
                $this->info('Todos os usuários já possuem colunas do Kanban.');
                return 0;
            }
            
            $this->info("Encontrados {$usersWithoutColumns->count()} usuários sem colunas do Kanban.");
            
            $bar = $this->output->createProgressBar($usersWithoutColumns->count());
            $bar->start();
            
            foreach ($usersWithoutColumns as $user) {
                $this->createColumnsForUser($user);
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("Colunas padrão criadas para {$usersWithoutColumns->count()} usuários.");
        }
        
        return 0;
    }
    
    private function createColumnsForUser(User $user)
    {
        // Verificar se o usuário já tem colunas
        if ($user->kanbanColumns()->count() > 0) {
            $this->warn("Usuário {$user->name} já possui colunas do Kanban. Pulando...");
            return;
        }
        
        $user->createDefaultKanbanColumns();
        $this->info("Colunas padrão criadas para usuário: {$user->name}");
    }
}
