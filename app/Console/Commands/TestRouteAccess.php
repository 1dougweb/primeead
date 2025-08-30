<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Middleware\AdminMiddleware;

class TestRouteAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:route-access {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test specific route access for a user';

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
        
        $this->info("🔍 Testing route access for: {$user->name} ({$user->email})");
        $this->info("   Tipo: {$user->tipo_usuario}");
        $this->info("   Is Admin: " . ($user->isAdmin() ? 'YES' : 'NO'));
        
        // Test specific routes
        $routes = [
            'admin.inscricoes' => 'inscricoes.index',
            'admin.matriculas.index' => 'matriculas.index',
            'admin.kanban.index' => 'kanban.index',
            'admin.contracts.index' => 'contratos.index',
            'contacts.index' => 'contatos.index',
            'admin.files.index' => 'arquivos.index',
            'admin.whatsapp.templates.index' => 'whatsapp.templates.index'
        ];
        
        $this->info("\n🧪 Testing route permissions:");
        foreach ($routes as $route => $permission) {
            $hasPermission = $user->hasPermission($permission);
            $status = $hasPermission ? '✅ YES' : '❌ NO';
            $this->info("   - {$route} ({$permission}): {$status}");
        }
        
        // Test AdminMiddleware logic
        $this->info("\n🔧 Testing AdminMiddleware logic:");
        
        // Check if user is admin
        if ($user->isAdmin()) {
            $this->info("   - User is admin: ✅ ACCESS TO ALL");
        } else {
            $this->info("   - User is not admin: Checking specific permissions...");
            
            // Check route permission map
            $routePermissionMap = [
                'admin.inscricoes' => 'inscricoes.index',
                'admin.inscricoes.*' => 'inscricoes.index',
                'admin.matriculas.*' => 'matriculas.index',
                'admin.kanban.*' => 'kanban.index',
                'admin.files.*' => 'arquivos.index',
                'admin.parceiros.*' => 'parceiros.index',
                'admin.contracts.*' => 'contratos.index',
                'admin.settings.whatsapp' => 'whatsapp.admin',
                'admin.settings.whatsapp.*' => 'whatsapp.admin',
                'admin.whatsapp.templates.*' => 'whatsapp.templates.index',
                'admin.email-campaigns.*' => 'whatsapp.admin',
                'admin.usuarios.*' => 'usuarios.index',
                'admin.settings.*' => 'configuracoes.index',
                'admin.monitoramento' => 'monitoramento.index',
                'admin.permissions.*' => 'permissoes.index',
                'contacts.*' => 'contatos.index',
            ];
            
            foreach ($routePermissionMap as $route => $permission) {
                if (in_array($route, ['admin.inscricoes', 'admin.inscricoes.*', 'admin.matriculas.*', 'admin.kanban.*', 'admin.contracts.*', 'contacts.*'])) {
                    $hasPermission = $user->hasPermission($permission);
                    $status = $hasPermission ? '✅ ACCESS' : '❌ DENIED';
                    $this->info("   - {$route} ({$permission}): {$status}");
                }
            }
            
            // Check fallback routes by user type
            $this->info("\n   Fallback routes for {$user->tipo_usuario}:");
            switch ($user->tipo_usuario) {
                case 'colaborador':
                    $allowedRoutes = [
                        'admin.inscricoes',
                        'admin.inscricoes.*',
                        'admin.matriculas.*'
                    ];
                    break;
                case 'vendedor':
                    $allowedRoutes = [
                        'admin.inscricoes',
                        'admin.inscricoes.*',
                        'admin.matriculas.*',
                        'admin.contracts.*',
                        'admin.kanban.*',
                        'contacts.*'
                    ];
                    break;
                default:
                    $allowedRoutes = [];
            }
            
            foreach ($allowedRoutes as $route) {
                $this->info("   - {$route}: ✅ ALLOWED");
            }
        }
        
        $this->info("\n✅ Route access test completed!");
        return 0;
    }
} 