<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignUserPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:permissions 
                            {action : assign-role, remove-role, assign-permission, remove-permission, list-users}
                            {--user= : ID do usuário}
                            {--role= : Nome do role}
                            {--permission= : Nome da permissão}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gerenciar permissões e roles de usuários';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list-users':
                $this->listUsers();
                break;
            case 'assign-role':
                $this->assignRole();
                break;
            case 'remove-role':
                $this->removeRole();
                break;
            case 'assign-permission':
                $this->assignPermission();
                break;
            case 'remove-permission':
                $this->removePermission();
                break;
            default:
                $this->error('Ação inválida. Use: list-users, assign-role, remove-role, assign-permission, remove-permission');
        }
    }

    /**
     * Listar todos os usuários
     */
    private function listUsers()
    {
        $users = User::with('roles', 'permissions')->get();

        $this->info('=== USUÁRIOS DO SISTEMA ===');
        
        foreach ($users as $user) {
            $this->line("ID: {$user->id} | Nome: {$user->name} | Email: {$user->email}");
            $this->line("Tipo: {$user->tipo_usuario} | Admin: " . ($user->isAdmin() ? 'Sim' : 'Não'));
            
            if ($user->roles->count() > 0) {
                $this->line("Roles: " . $user->roles->pluck('name')->implode(', '));
            }
            
            if ($user->permissions->count() > 0) {
                $this->line("Permissões Diretas: " . $user->permissions->pluck('name')->implode(', '));
            }
            
            $this->line("Total de Permissões: " . $user->getAllPermissions()->count());
            $this->line('---');
        }
    }

    /**
     * Atribuir role a um usuário
     */
    private function assignRole()
    {
        $userId = $this->option('user');
        $roleName = $this->option('role');

        if (!$userId || !$roleName) {
            $this->error('Você deve especificar --user e --role');
            $this->showAvailableRoles();
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado");
            return;
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Role '{$roleName}' não encontrado");
            $this->showAvailableRoles();
            return;
        }

        if ($user->hasRole($roleName)) {
            $this->warn("Usuário {$user->name} já possui o role '{$roleName}'");
            return;
        }

        $user->assignRole($roleName);
        $this->info("✅ Role '{$roleName}' atribuído ao usuário {$user->name}");
    }

    /**
     * Remover role de um usuário
     */
    private function removeRole()
    {
        $userId = $this->option('user');
        $roleName = $this->option('role');

        if (!$userId || !$roleName) {
            $this->error('Você deve especificar --user e --role');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado");
            return;
        }

        if (!$user->hasRole($roleName)) {
            $this->warn("Usuário {$user->name} não possui o role '{$roleName}'");
            return;
        }

        $user->removeRole($roleName);
        $this->info("✅ Role '{$roleName}' removido do usuário {$user->name}");
    }

    /**
     * Atribuir permissão direta a um usuário
     */
    private function assignPermission()
    {
        $userId = $this->option('user');
        $permissionName = $this->option('permission');

        if (!$userId || !$permissionName) {
            $this->error('Você deve especificar --user e --permission');
            $this->showAvailablePermissions();
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado");
            return;
        }

        $permission = Permission::where('name', $permissionName)->first();
        if (!$permission) {
            $this->error("Permissão '{$permissionName}' não encontrada");
            $this->showAvailablePermissions();
            return;
        }

        if ($user->permissions()->where('name', $permissionName)->exists()) {
            $this->warn("Usuário {$user->name} já possui a permissão '{$permissionName}' diretamente");
            return;
        }

        $user->givePermissionTo($permissionName);
        $this->info("✅ Permissão '{$permissionName}' atribuída ao usuário {$user->name}");
    }

    /**
     * Remover permissão direta de um usuário
     */
    private function removePermission()
    {
        $userId = $this->option('user');
        $permissionName = $this->option('permission');

        if (!$userId || !$permissionName) {
            $this->error('Você deve especificar --user e --permission');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado");
            return;
        }

        if (!$user->permissions()->where('name', $permissionName)->exists()) {
            $this->warn("Usuário {$user->name} não possui a permissão '{$permissionName}' diretamente");
            return;
        }

        $user->revokePermissionTo($permissionName);
        $this->info("✅ Permissão '{$permissionName}' removida do usuário {$user->name}");
    }

    /**
     * Mostrar roles disponíveis
     */
    private function showAvailableRoles()
    {
        $roles = Role::all();
        $this->info('Roles disponíveis:');
        foreach ($roles as $role) {
            $this->line("- {$role->name}");
        }
    }

    /**
     * Mostrar permissões disponíveis
     */
    private function showAvailablePermissions()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });
        
        $this->info('Permissões disponíveis por módulo:');
        foreach ($permissions as $module => $modulePermissions) {
            $this->line("📁 {$module}:");
            foreach ($modulePermissions as $permission) {
                $this->line("  - {$permission->name}");
            }
        }
    }
}
