<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class AdminPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definir módulos administrativos
        $adminModules = [
            'permissions' => 'Permissões',
            'users' => 'Usuários',
            'settings' => 'Configurações',
            'monitoring' => 'Monitoramento',
            'whatsapp' => 'WhatsApp',
            'email_campaigns' => 'Campanhas de Email',
            'files' => 'Arquivos',
            'contacts' => 'Contatos',
            'partners' => 'Parceiros',
            'contracts' => 'Contratos',
            'kanban' => 'Kanban',
            'enrollments' => 'Matrículas',
            'inscriptions' => 'Inscrições',
        ];

        // Criar permissões para cada módulo administrativo
        foreach ($adminModules as $moduleSlug => $moduleName) {
            // Verificar se a permissão já existe
            $viewPermission = Permission::where('slug', "view_{$moduleSlug}")->first();
            
            if (!$viewPermission) {
                // Permissão de visualização
                Permission::create([
                    'name' => "Ver {$moduleName}",
                    'slug' => "view_{$moduleSlug}",
                    'module' => 'admin',
                    'description' => "Permite visualizar o módulo {$moduleName} na área administrativa",
                ]);
            }

            // Verificar se a permissão já existe
            $createPermission = Permission::where('slug', "create_{$moduleSlug}")->first();
            
            if (!$createPermission) {
                // Permissão de criação
                Permission::create([
                    'name' => "Criar {$moduleName}",
                    'slug' => "create_{$moduleSlug}",
                    'module' => 'admin',
                    'description' => "Permite criar no módulo {$moduleName} na área administrativa",
                ]);
            }

            // Verificar se a permissão já existe
            $editPermission = Permission::where('slug', "edit_{$moduleSlug}")->first();
            
            if (!$editPermission) {
                // Permissão de edição
                Permission::create([
                    'name' => "Editar {$moduleName}",
                    'slug' => "edit_{$moduleSlug}",
                    'module' => 'admin',
                    'description' => "Permite editar no módulo {$moduleName} na área administrativa",
                ]);
            }

            // Verificar se a permissão já existe
            $deletePermission = Permission::where('slug', "delete_{$moduleSlug}")->first();
            
            if (!$deletePermission) {
                // Permissão de exclusão
                Permission::create([
                    'name' => "Excluir {$moduleName}",
                    'slug' => "delete_{$moduleSlug}",
                    'module' => 'admin',
                    'description' => "Permite excluir no módulo {$moduleName} na área administrativa",
                ]);
            }
        }

        // Atribuir todas as permissões administrativas ao papel de administrador
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminPermissions = Permission::where('module', 'admin')->get();
            $adminRole->permissions()->syncWithoutDetaching($adminPermissions);
        }

        // Atribuir permissões específicas ao papel de vendedor
        $vendedorRole = Role::where('slug', 'vendedor')->first();
        if ($vendedorRole) {
            $vendedorPermissions = Permission::whereIn('slug', [
                'view_inscriptions', 'view_enrollments', 'view_contracts', 'view_kanban', 'view_contacts'
            ])->get();
            $vendedorRole->permissions()->syncWithoutDetaching($vendedorPermissions);
        }

        // Atribuir permissões específicas ao papel de mídia
        $midiaRole = Role::where('slug', 'midia')->first();
        if ($midiaRole) {
            $midiaPermissions = Permission::whereIn('slug', [
                'view_email_campaigns', 'view_kanban'
            ])->get();
            $midiaRole->permissions()->syncWithoutDetaching($midiaPermissions);
        }

        // Atribuir permissões específicas ao papel de colaborador
        $colaboradorRole = Role::where('slug', 'colaborador')->first();
        if ($colaboradorRole) {
            $colaboradorPermissions = Permission::whereIn('slug', [
                'view_inscriptions', 'view_enrollments'
            ])->get();
            $colaboradorRole->permissions()->syncWithoutDetaching($colaboradorPermissions);
        }

        // Atribuir permissões específicas ao papel de parceiro
        $parceiroRole = Role::where('slug', 'parceiro')->first();
        if ($parceiroRole) {
            $parceiroPermissions = Permission::whereIn('slug', [
                'view_partners'
            ])->get();
            $parceiroRole->permissions()->syncWithoutDetaching($parceiroPermissions);
        }
    }
} 