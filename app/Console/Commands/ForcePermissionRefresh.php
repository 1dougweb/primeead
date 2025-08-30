<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ForcePermissionRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:force-refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force refresh all user permissions and clear all caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Force refreshing all permissions...');

        // 1. Limpar todos os caches
        $this->info('Clearing all caches...');
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
        \Artisan::call('permission:cache-reset');

        // 2. Limpar cache de permissões do Spatie
        $this->info('Clearing Spatie permission cache...');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 3. Recarregar todas as permissões e roles
        $this->info('Reloading all permissions and roles...');
        $permissions = Permission::all();
        $roles = Role::all();
        $users = User::with(['roles', 'permissions'])->get();

        $this->info("Found {$permissions->count()} permissions, {$roles->count()} roles, {$users->count()} users");

        // 4. Forçar recarregamento das permissões para cada usuário
        $this->info('Refreshing user permissions...');
        $bar = $this->output->createProgressBar($users->count());
        
        foreach ($users as $user) {
            // Forçar recarregamento das permissões
            $user->load(['roles.permissions', 'permissions']);
            
            // Testar algumas permissões para garantir que estão funcionando
            $testPermissions = ['contatos.index', 'parceiros.index', 'inscricoes.index'];
            foreach ($testPermissions as $permission) {
                $user->hasPermissionTo($permission);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->line('');

        // 5. Verificar se as permissões estão funcionando
        $this->info('Testing permissions after refresh...');
        $testUser = User::where('tipo_usuario', 'vendedor')->first();
        
        if ($testUser) {
            $testUser->load(['roles.permissions', 'permissions']);
            
            $this->info("Testing user: {$testUser->name}");
            $this->info("contatos.index: " . ($testUser->hasPermissionTo('contatos.index') ? '✅ YES' : '❌ NO'));
            $this->info("parceiros.index: " . ($testUser->hasPermissionTo('parceiros.index') ? '✅ YES' : '❌ NO'));
        }

        $this->info('✅ Permission refresh completed!');
        $this->info('💡 Now try accessing the frontend. If it still doesn\'t work:');
        $this->info('   1. Clear browser cache (Ctrl+Shift+Delete)');
        $this->info('   2. Log out and log back in');
        $this->info('   3. Try a different browser or incognito mode');

        return 0;
    }
}
