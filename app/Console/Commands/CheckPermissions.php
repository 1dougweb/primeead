<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CheckPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:check {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar permissões de um usuário específico ou do sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        if ($userId) {
            $this->checkUserPermissions($userId);
        } else {
            $this->checkSystemPermissions();
        }
    }

    /**
     * Verificar permissões de um usuário específico
     */
    private function checkUserPermissions($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado.");
            return;
        }

        $this->info("=== Verificando permissões do usuário: {$user->name} ===");
        $this->line("Email: {$user->email}");
        $this->line("Tipo: {$user->tipo_usuario}");
        $this->line("Admin: " . ($user->isAdmin() ? 'Sim' : 'Não'));
        $this->line("");

        // Verificar roles
        $this->info("=== ROLES ===");
        $roles = $user->roles;
        if ($roles->count() > 0) {
            foreach ($roles as $role) {
                $this->line("- {$role->name}");
            }
        } else {
            $this->line("Nenhum role atribuído.");
        }
        $this->line("");

        // Verificar permissões
        $this->info("=== PERMISSÕES ===");
        $permissions = $user->getAllPermissions();
        if ($permissions->count() > 0) {
            foreach ($permissions as $permission) {
                $this->line("- {$permission->name}");
            }
        } else {
            $this->line("Nenhuma permissão encontrada.");
        }
        $this->line("");

        // Testar algumas permissões específicas
        $this->info("=== TESTES DE PERMISSÕES ===");
        $testPermissions = [
            'dashboard.index',
            'inscricoes.index',
            'usuarios.index',
            'configuracoes.index'
        ];

        foreach ($testPermissions as $permission) {
            $hasPermission = $user->hasPermissionTo($permission);
            $status = $hasPermission ? '✅ YES' : '❌ NO';
            $this->line("   - {$permission}: {$status}");
        }
    }

    /**
     * Verificar permissões do sistema
     */
    private function checkSystemPermissions()
    {
        $this->info("=== VERIFICAÇÃO DO SISTEMA DE PERMISSÕES ===");
        
        // Estatísticas gerais
        $totalPermissions = Permission::count();
        $totalRoles = Role::count();
        $totalUsers = User::count();
        $usersWithRoles = User::whereHas('roles')->count();

        $this->line("Total de permissões: {$totalPermissions}");
        $this->line("Total de roles: {$totalRoles}");
        $this->line("Total de usuários: {$totalUsers}");
        $this->line("Usuários com roles: {$usersWithRoles}");
        $this->line("");

        // Listar roles
        $this->info("=== ROLES DISPONÍVEIS ===");
        $roles = Role::with('permissions')->get();
        foreach ($roles as $role) {
            $this->line("- {$role->name} ({$role->permissions->count()} permissões)");
        }
        $this->line("");

        // Verificar integridade
        $this->info("=== VERIFICAÇÃO DE INTEGRIDADE ===");
        
        // Permissões órfãs (sem roles)
        $orphanPermissions = Permission::whereDoesntHave('roles')->count();
        if ($orphanPermissions > 0) {
            $this->warn("⚠️  {$orphanPermissions} permissões sem roles associados");
        }

        // Roles órfãos (sem usuários)
        $orphanRoles = Role::whereDoesntHave('users')->count();
        if ($orphanRoles > 0) {
            $this->warn("⚠️  {$orphanRoles} roles sem usuários associados");
        }

        // Usuários sem roles (exceto admins)
        $usersWithoutRoles = User::whereDoesntHave('roles')->where('tipo_usuario', '!=', 'admin')->count();
        if ($usersWithoutRoles > 0) {
            $this->warn("⚠️  {$usersWithoutRoles} usuários sem roles (excluindo admins)");
        }

        if ($orphanPermissions == 0 && $orphanRoles == 0 && $usersWithoutRoles == 0) {
            $this->info("✅ Sistema de permissões está íntegro!");
        }
    }
} 