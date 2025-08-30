<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar papéis básicos
        $adminRole = Role::create([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        $vendedorRole = Role::create([
            'name' => 'Vendedor',
            'guard_name' => 'web',
        ]);

        $colaboradorRole = Role::create([
            'name' => 'Colaborador',
            'guard_name' => 'web',
        ]);

        $midiaRole = Role::create([
            'name' => 'Mídia',
            'guard_name' => 'web',
        ]);

        $parceiroRole = Role::create([
            'name' => 'Parceiro',
            'guard_name' => 'web',
        ]);

        // Definir módulos do sistema
        $modules = [
            'dashboard' => 'Dashboard',
            'usuarios' => 'Usuários',
            'inscricoes' => 'Inscrições',
            'matriculas' => 'Matrículas',
            'financeiro' => 'Financeiro',
            'contratos' => 'Contratos',
            'parceiros' => 'Parceiros',
            'configuracoes' => 'Configurações',
            'relatorios' => 'Relatórios',
            'kanban' => 'Kanban',
            'arquivos' => 'Arquivos',
        ];

        // Criar permissões para cada módulo
        foreach ($modules as $moduleSlug => $moduleName) {
            // Permissão de visualização
            Permission::create([
                'name' => "Ver {$moduleName}",
                'guard_name' => 'web',
            ]);

            // Permissão de criação
            Permission::create([
                'name' => "Criar {$moduleName}",
                'guard_name' => 'web',
            ]);

            // Permissão de edição
            Permission::create([
                'name' => "Editar {$moduleName}",
                'guard_name' => 'web',
            ]);

            // Permissão de exclusão
            Permission::create([
                'name' => "Excluir {$moduleName}",
                'guard_name' => 'web',
            ]);
        }

        // Atribuir todas as permissões ao papel de administrador
        $adminRole->givePermissionTo(Permission::all());

        // Atribuir permissões específicas ao papel de vendedor
        $vendedorPermissions = Permission::whereIn('name', [
            'Ver Dashboard', 'Ver Inscrições', 'Ver Matrículas', 'Ver Contratos', 'Ver Parceiros', 'Ver Kanban', 'Ver Arquivos'
        ])->get();
        $vendedorRole->givePermissionTo($vendedorPermissions);

        // Atribuir permissões específicas ao papel de mídia
        $midiaPermissions = Permission::whereIn('name', [
            'Ver Dashboard', 'Ver Inscrições', 'Ver Relatórios'
        ])->get();
        $midiaRole->givePermissionTo($midiaPermissions);

        // Atribuir permissões específicas ao papel de colaborador
        $colaboradorPermissions = Permission::whereIn('name', [
            'Ver Dashboard', 'Ver Inscrições', 'Ver Matrículas'
        ])->get();
        $colaboradorRole->givePermissionTo($colaboradorPermissions);

        // Atribuir permissões específicas ao papel de parceiro
        $parceiroPermissions = Permission::whereIn('name', [
            'Ver Dashboard', 'Ver Contratos', 'Ver Arquivos'
        ])->get();
        $parceiroRole->givePermissionTo($parceiroPermissions);

        // Atribuir papéis aos usuários existentes com base no tipo_usuario
        $users = User::all();
        foreach ($users as $user) {
            switch ($user->tipo_usuario) {
                case 'admin':
                    $user->assignRole($adminRole);
                    break;
                case 'vendedor':
                    $user->assignRole($vendedorRole);
                    break;
                case 'colaborador':
                    $user->assignRole($colaboradorRole);
                    break;
                case 'midia':
                    $user->assignRole($midiaRole);
                    break;
                case 'parceiro':
                    $user->assignRole($parceiroRole);
                    break;
            }
        }
    }
} 