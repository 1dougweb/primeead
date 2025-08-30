<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestUserAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-access {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user access to different areas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id') ?? 6; // Default to Test User
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return 1;
        }
        
        $this->info("🔍 Testing access for: {$user->name} ({$user->email})");
        $this->info("   Tipo: {$user->tipo_usuario}");
        $this->info("   Is Admin: " . ($user->isAdmin() ? 'YES' : 'NO'));
        $this->info("   Roles: " . $user->roles->pluck('name')->join(', '));
        
        // Test menu permissions
        $menuPermissions = [
            'admin.permissions.*' => 'permissoes.index',
            'admin.usuarios.*' => 'usuarios.index',
            'admin.settings.*' => 'configuracoes.index',
            'admin.monitoramento' => 'monitoramento.index',
            'admin.whatsapp.*' => 'whatsapp.admin',
            'admin.whatsapp.templates.*' => 'whatsapp.templates.index',
            'admin.email-campaigns.*' => 'whatsapp.admin',
            'admin.files.*' => 'arquivos.index',
            'contacts.*' => 'contatos.index',
            'admin.parceiros.*' => 'parceiros.index',
            'admin.contracts.*' => 'contratos.index',
            'admin.kanban.*' => 'kanban.index',
            'admin.matriculas.*' => 'matriculas.index',
            'admin.inscricoes' => 'inscricoes.index',
            'dashboard' => 'dashboard.index'
        ];
        
        $this->info("\n🧪 Testing menu permissions:");
        foreach ($menuPermissions as $route => $permission) {
            $hasPermission = $user->hasPermission($permission);
            $status = $hasPermission ? '✅ YES' : '❌ NO';
            $this->info("   - {$route} ({$permission}): {$status}");
        }
        
        // Test specific areas
        $this->info("\n🎯 Testing specific areas:");
        $areas = [
            'Dashboard' => 'dashboard.index',
            'Inscrições' => 'inscricoes.index',
            'Matrículas' => 'matriculas.index',
            'Contratos' => 'contratos.index',
            'Kanban' => 'kanban.index',
            'Contatos' => 'contatos.index',
            'Arquivos' => 'arquivos.index',
            'WhatsApp Templates' => 'whatsapp.templates.index'
        ];
        
        foreach ($areas as $area => $permission) {
            $hasPermission = $user->hasPermission($permission);
            $status = $hasPermission ? '✅ ACCESS' : '❌ DENIED';
            $this->info("   - {$area}: {$status}");
        }
        
        $this->info("\n✅ Access test completed!");
        return 0;
    }
} 