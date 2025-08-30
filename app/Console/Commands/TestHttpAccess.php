<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;

class TestHttpAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:http-access {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test HTTP access simulation';

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
        
        $this->info("🔍 Testing HTTP access for: {$user->name} ({$user->email})");
        
        // Simulate login
        auth()->login($user);
        
        $this->info("   Authenticated: " . (auth()->check() ? 'YES' : 'NO'));
        $this->info("   Current user: " . (auth()->user() ? auth()->user()->name : 'NONE'));
        
        // Test specific routes
        $routes = [
            'admin.inscricoes' => '/dashboard/inscricoes',
            'admin.matriculas.index' => '/dashboard/matriculas',
            'admin.kanban.index' => '/dashboard/kanban',
            'admin.contracts.index' => '/dashboard/contracts',
        ];
        
        $this->info("\n🧪 Testing route access:");
        foreach ($routes as $routeName => $routePath) {
            $this->info("   Testing: {$routeName} ({$routePath})");
            
            // Check if route exists
            $route = Route::getRoutes()->getByName($routeName);
            if (!$route) {
                $this->warn("     Route not found!");
                continue;
            }
            
            // Check permission
            $permission = $this->getPermissionForRoute($routeName);
            if ($permission) {
                $hasPermission = $user->hasPermission($permission);
                $this->info("     Permission {$permission}: " . ($hasPermission ? '✅ YES' : '❌ NO'));
            }
            
            // Check if user is admin
            if ($user->isAdmin()) {
                $this->info("     User is admin: ✅ ACCESS");
            } else {
                // Check fallback routes
                $allowed = $this->isRouteAllowedForUserType($routeName, $user->tipo_usuario);
                $this->info("     Fallback for {$user->tipo_usuario}: " . ($allowed ? '✅ ALLOWED' : '❌ DENIED'));
            }
        }
        
        $this->info("\n✅ HTTP access test completed!");
        return 0;
    }
    
    private function getPermissionForRoute($routeName)
    {
        $routePermissionMap = [
            'admin.inscricoes' => 'inscricoes.index',
            'admin.inscricoes.*' => 'inscricoes.index',
            'admin.matriculas.*' => 'matriculas.index',
            'admin.kanban.*' => 'kanban.index',
            'admin.contracts.*' => 'contratos.index',
            'contacts.*' => 'contatos.index',
        ];
        
        foreach ($routePermissionMap as $route => $permission) {
            if (str_starts_with($routeName, $route)) {
                return $permission;
            }
        }
        
        return null;
    }
    
    private function isRouteAllowedForUserType($routeName, $userType)
    {
        $allowedRoutes = [];
        
        switch ($userType) {
            case 'colaborador':
                $allowedRoutes = [
                    'admin.inscricoes',
                    'admin.inscricoes.*',
                    'admin.matriculas.*',
                    'admin.kanban.*',
                    'admin.contracts.*'
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
        }
        
        foreach ($allowedRoutes as $allowedRoute) {
            if (str_starts_with($routeName, $allowedRoute)) {
                return true;
            }
        }
        
        return false;
    }
} 