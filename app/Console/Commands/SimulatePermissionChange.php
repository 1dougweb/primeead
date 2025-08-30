<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SimulatePermissionChange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:simulate-change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate a permission change and test cache clearing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Simulating permission change...');

        // 1. Testar permissão antes da mudança
        $user = User::where('tipo_usuario', 'vendedor')->first();
        $this->info("User: {$user->name}");
        $this->info("Before change - contatos.index: " . ($user->hasPermissionTo('contatos.index') ? 'YES' : 'NO'));

        // 2. Simular alteração de permissão (remover temporariamente)
        $role = Role::where('name', 'Vendedor')->first();
        $permission = Permission::where('name', 'contatos.index')->first();
        
        if ($role && $permission) {
            $this->info('Removing contatos.index permission temporarily...');
            $role->revokePermissionTo($permission);
            
            // 3. Limpar cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            \Cache::flush();
            
            $this->info('Cache cleared!');
            
            // 4. Testar permissão após remoção
            $user->load(['roles.permissions', 'permissions']);
            $this->info("After removal - contatos.index: " . ($user->hasPermissionTo('contatos.index') ? 'YES' : 'NO'));
            
            // 5. Restaurar permissão
            $this->info('Restoring contatos.index permission...');
            $role->givePermissionTo($permission);
            
            // 6. Limpar cache novamente
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            \Cache::flush();
            
            $this->info('Cache cleared again!');
            
            // 7. Testar permissão após restauração
            $user->load(['roles.permissions', 'permissions']);
            $this->info("After restoration - contatos.index: " . ($user->hasPermissionTo('contatos.index') ? 'YES' : 'NO'));
        }

        $this->info('✅ Permission change simulation completed!');
        return 0;
    }
}
