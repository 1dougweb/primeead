<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestInscricoesAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:inscricoes-access {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test specific access to inscricoes';

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
        
        $this->info("🔍 Testing inscricoes access for: {$user->name} ({$user->email})");
        
        // Simulate login
        Auth::login($user);
        
        $this->info("   Authenticated: " . (Auth::check() ? 'YES' : 'NO'));
        $this->info("   Current user: " . (Auth::user() ? Auth::user()->name : 'NONE'));
        
        // Test specific permission
        $hasPermission = $user->hasPermission('inscricoes.index');
        $this->info("   Has inscricoes.index: " . ($hasPermission ? 'YES' : 'NO'));
        
        // Test isAdmin
        $isAdmin = $user->isAdmin();
        $this->info("   Is Admin: " . ($isAdmin ? 'YES' : 'NO'));
        
        // Test session
        $this->info("   Session admin_tipo: " . session('admin_tipo'));
        $this->info("   Session admin_id: " . session('admin_id'));
        
        // Test AdminMiddleware logic manually
        $this->info("\n🔧 Testing AdminMiddleware logic manually:");
        
        // Route permission map
        $routePermissionMap = [
            'admin.inscricoes' => 'inscricoes.index',
            'admin.inscricoes.*' => 'inscricoes.index',
        ];
        
        foreach ($routePermissionMap as $route => $permission) {
            $hasPermission = $user->hasPermission($permission);
            $status = $hasPermission ? '✅ ACCESS' : '❌ DENIED';
            $this->info("   - {$route} ({$permission}): {$status}");
        }
        
        // Check fallback routes
        $this->info("\n   Fallback routes for {$user->tipo_usuario}:");
        switch ($user->tipo_usuario) {
            case 'colaborador':
                $allowedRoutes = [
                    'admin.inscricoes',
                    'admin.inscricoes.*',
                    'admin.matriculas.*'
                ];
                break;
            default:
                $allowedRoutes = [];
        }
        
        foreach ($allowedRoutes as $route) {
            $this->info("   - {$route}: ✅ ALLOWED");
        }
        
        // Test cache
        $this->info("\n💾 Testing cache:");
        $cacheKey = "permissions_user_{$user->id}_permission_inscricoes.index";
        $cached = \Cache::has($cacheKey);
        $this->info("   - Cache for inscricoes.index: " . ($cached ? 'EXISTS' : 'NOT FOUND'));
        
        if ($cached) {
            $cachedValue = \Cache::get($cacheKey);
            $this->info("   - Cached value: " . ($cachedValue ? 'TRUE' : 'FALSE'));
        }
        
        $this->info("\n✅ Inscricoes access test completed!");
        return 0;
    }
} 