<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TestPermissionsRealtime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:test-realtime {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test permissions in real time for a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $user = User::find($userId);
        } else {
            $user = User::where('tipo_usuario', 'vendedor')->first();
        }

        if (!$user) {
            $this->error('User not found!');
            return 1;
        }

        $this->info("Testing permissions for user: {$user->name} ({$user->email})");
        $this->info("User type: {$user->tipo_usuario}");
        $this->line('');

        // Forçar recarregamento das permissões
        $user->load(['roles.permissions', 'permissions']);

        // Testar permissões específicas
        $testPermissions = [
            'contatos.index',
            'parceiros.index',
            'inscricoes.index',
            'matriculas.index',
            'usuarios.index',
            'permissoes.index'
        ];

        $this->info('=== TESTING PERMISSIONS ===');
        foreach ($testPermissions as $permission) {
            $hasPermission = $user->hasPermissionTo($permission);
            $status = $hasPermission ? '✅ YES' : '❌ NO';
            $this->line("   {$permission}: {$status}");
        }

        $this->line('');
        $this->info('=== USER ROLES ===');
        foreach ($user->roles as $role) {
            $this->line("   Role: {$role->name}");
            $this->line("   Permissions:");
            foreach ($role->permissions as $permission) {
                $this->line("     - {$permission->name}");
            }
            $this->line('');
        }

        $this->info('=== DIRECT PERMISSIONS ===');
        if ($user->permissions->count() > 0) {
            foreach ($user->permissions as $permission) {
                $this->line("   - {$permission->name}");
            }
        } else {
            $this->line("   No direct permissions");
        }

        $this->line('');
        $this->info('✅ Permission test completed!');

        return 0;
    }
}
