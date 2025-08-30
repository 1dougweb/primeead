<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class SpatiePermissionsMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('=== MIGRAÇÃO DE PERMISSÕES PARA SPATIE ===');
        
        // Verificar se já existem permissões
        if (Permission::count() > 0) {
            $this->command->warn('Permissões já existem. Pulando criação...');
            return;
        }

        $this->command->info('1. Criando permissões...');
        $this->createPermissions();
        
        $this->command->info('2. Criando roles...');
        $this->createRoles();
        
        $this->command->info('3. Atribuindo permissões aos roles...');
        $this->assignPermissionsToRoles();
        
        $this->command->info('4. Atribuindo roles aos usuários...');
        $this->assignRolesToUsers();
        
        $this->command->info('✅ Migração concluída com sucesso!');
    }

    /**
     * Criar permissões do sistema
     */
    private function createPermissions(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.index', 'description' => 'Acessar dashboard'],
            
            // Inscrições
            ['name' => 'inscricoes.index', 'description' => 'Listar inscrições'],
            ['name' => 'inscricoes.create', 'description' => 'Criar inscrições'],
            ['name' => 'inscricoes.edit', 'description' => 'Editar inscrições'],
            ['name' => 'inscricoes.delete', 'description' => 'Deletar inscrições'],
            ['name' => 'inscricoes.show', 'description' => 'Visualizar detalhes das inscrições'],
            
            // Matrículas
            ['name' => 'matriculas.index', 'description' => 'Listar matrículas'],
            ['name' => 'matriculas.create', 'description' => 'Criar matrículas'],
            ['name' => 'matriculas.edit', 'description' => 'Editar matrículas'],
            ['name' => 'matriculas.delete', 'description' => 'Deletar matrículas'],
            ['name' => 'matriculas.show', 'description' => 'Visualizar detalhes das matrículas'],
            
            // Usuários
            ['name' => 'usuarios.index', 'description' => 'Listar usuários'],
            ['name' => 'usuarios.create', 'description' => 'Criar usuários'],
            ['name' => 'usuarios.edit', 'description' => 'Editar usuários'],
            ['name' => 'usuarios.delete', 'description' => 'Deletar usuários'],
            ['name' => 'usuarios.show', 'description' => 'Visualizar detalhes dos usuários'],
            ['name' => 'usuarios.impersonate', 'description' => 'Impersonar usuários'],
            
            // Permissões e Roles
            ['name' => 'permissoes.index', 'description' => 'Listar permissões'],
            ['name' => 'permissoes.create', 'description' => 'Criar permissões'],
            ['name' => 'permissoes.edit', 'description' => 'Editar permissões'],
            ['name' => 'permissoes.delete', 'description' => 'Deletar permissões'],
            ['name' => 'permissoes.migrate', 'description' => 'Migrar permissões'],
            ['name' => 'roles.index', 'description' => 'Listar roles'],
            ['name' => 'roles.create', 'description' => 'Criar roles'],
            ['name' => 'roles.edit', 'description' => 'Editar roles'],
            ['name' => 'roles.delete', 'description' => 'Deletar roles'],
            
            // Configurações
            ['name' => 'configuracoes.index', 'description' => 'Acessar configurações'],
            ['name' => 'configuracoes.edit', 'description' => 'Editar configurações'],
            
            // Parceiros
            ['name' => 'parceiros.index', 'description' => 'Listar parceiros'],
            ['name' => 'parceiros.create', 'description' => 'Criar parceiros'],
            ['name' => 'parceiros.edit', 'description' => 'Editar parceiros'],
            ['name' => 'parceiros.delete', 'description' => 'Deletar parceiros'],
            ['name' => 'parceiros.show', 'description' => 'Visualizar detalhes dos parceiros'],
            
            // Pagamentos
            ['name' => 'pagamentos.index', 'description' => 'Listar pagamentos'],
            ['name' => 'pagamentos.create', 'description' => 'Criar pagamentos'],
            ['name' => 'pagamentos.edit', 'description' => 'Editar pagamentos'],
            ['name' => 'pagamentos.delete', 'description' => 'Deletar pagamentos'],
            ['name' => 'pagamentos.show', 'description' => 'Visualizar detalhes dos pagamentos'],
            
            // Contratos
            ['name' => 'contratos.index', 'description' => 'Listar contratos'],
            ['name' => 'contratos.create', 'description' => 'Criar contratos'],
            ['name' => 'contratos.edit', 'description' => 'Editar contratos'],
            ['name' => 'contratos.delete', 'description' => 'Deletar contratos'],
            ['name' => 'contratos.show', 'description' => 'Visualizar detalhes dos contratos'],
            
            // Contatos
            ['name' => 'contatos.index', 'description' => 'Listar contatos'],
            ['name' => 'contatos.create', 'description' => 'Criar contatos'],
            ['name' => 'contatos.edit', 'description' => 'Editar contatos'],
            ['name' => 'contatos.delete', 'description' => 'Deletar contatos'],
            ['name' => 'contatos.show', 'description' => 'Visualizar detalhes dos contatos'],
            
            // Kanban
            ['name' => 'kanban.index', 'description' => 'Visualizar kanban'],
            ['name' => 'kanban.edit', 'description' => 'Editar kanban'],
            ['name' => 'kanban.move', 'description' => 'Mover cards no kanban'],
            
            // Email Templates
            ['name' => 'email-templates.index', 'description' => 'Listar templates de email'],
            ['name' => 'email-templates.create', 'description' => 'Criar templates de email'],
            ['name' => 'email-templates.edit', 'description' => 'Editar templates de email'],
            ['name' => 'email-templates.delete', 'description' => 'Deletar templates de email'],
            
            // Email Campaigns
            ['name' => 'email-campaigns.index', 'description' => 'Listar campanhas de email'],
            ['name' => 'email-campaigns.create', 'description' => 'Criar campanhas de email'],
            ['name' => 'email-campaigns.edit', 'description' => 'Editar campanhas de email'],
            ['name' => 'email-campaigns.delete', 'description' => 'Deletar campanhas de email'],
            ['name' => 'email-campaigns.send', 'description' => 'Enviar campanhas de email'],
            
            // WhatsApp
            ['name' => 'whatsapp.index', 'description' => 'Acessar WhatsApp'],
            ['name' => 'whatsapp.send', 'description' => 'Enviar mensagens WhatsApp'],
            ['name' => 'whatsapp.templates', 'description' => 'Gerenciar templates WhatsApp'],
            
            // Monitoramento
            ['name' => 'monitoramento.index', 'description' => 'Acessar monitoramento'],
            
            // Relatórios
            ['name' => 'relatorios.index', 'description' => 'Acessar relatórios'],
            ['name' => 'relatorios.export', 'description' => 'Exportar relatórios'],
            
            // Google Drive
            ['name' => 'google-drive.index', 'description' => 'Acessar Google Drive'],
            ['name' => 'google-drive.upload', 'description' => 'Upload para Google Drive'],
            ['name' => 'google-drive.delete', 'description' => 'Deletar do Google Drive'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('  ✅ Permissões criadas: ' . Permission::count());
    }

    /**
     * Criar roles do sistema
     */
    private function createRoles(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'guard_name' => 'web',
                'description' => 'Acesso total ao sistema'
            ],
            [
                'name' => 'Admin',
                'guard_name' => 'web',
                'description' => 'Administrador do sistema'
            ],
            [
                'name' => 'Vendedor',
                'guard_name' => 'web',
                'description' => 'Vendedor com acesso limitado'
            ],
            [
                'name' => 'Colaborador',
                'guard_name' => 'web',
                'description' => 'Colaborador com acesso básico'
            ],
            [
                'name' => 'Mídia',
                'guard_name' => 'web',
                'description' => 'Responsável por mídia e campanhas'
            ],
            [
                'name' => 'Parceiro',
                'guard_name' => 'web',
                'description' => 'Parceiro externo com acesso limitado'
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role['name'],
                'guard_name' => $role['guard_name'],
            ]);
        }

        $this->command->info('  ✅ Roles criados: ' . Role::count());
    }

    /**
     * Atribuir permissões aos roles
     */
    private function assignPermissionsToRoles(): void
    {
        // Super Admin - todas as permissões
        $superAdmin = Role::findByName('Super Admin');
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - quase todas as permissões
        $admin = Role::findByName('Admin');
        $adminPermissions = Permission::whereNotIn('name', [
            'usuarios.impersonate',
        ])->get();
        $admin->givePermissionTo($adminPermissions);

        // Vendedor - permissões de vendas
        $vendedor = Role::findByName('Vendedor');
        $vendedorPermissions = Permission::whereIn('name', [
            'dashboard.index',
            'inscricoes.index',
            'inscricoes.create',
            'inscricoes.edit',
            'inscricoes.show',
            'matriculas.index',
            'matriculas.create',
            'matriculas.edit',
            'matriculas.show',
            'contatos.index',
            'contatos.create',
            'contatos.edit',
            'contatos.show',
            'kanban.index',
            'kanban.edit',
            'kanban.move',
            'pagamentos.index',
            'pagamentos.show',
            'contratos.index',
            'contratos.show',
        ])->get();
        $vendedor->givePermissionTo($vendedorPermissions);

        // Colaborador - permissões básicas
        $colaborador = Role::findByName('Colaborador');
        $colaboradorPermissions = Permission::whereIn('name', [
            'dashboard.index',
            'inscricoes.index',
            'inscricoes.show',
            'matriculas.index',
            'matriculas.show',
            'contatos.index',
            'contatos.show',
            'kanban.index',
        ])->get();
        $colaborador->givePermissionTo($colaboradorPermissions);

        // Mídia - permissões de marketing
        $midia = Role::findByName('Mídia');
        $midiaPermissions = Permission::whereIn('name', [
            'dashboard.index',
            'email-templates.index',
            'email-templates.create',
            'email-templates.edit',
            'email-templates.delete',
            'email-campaigns.index',
            'email-campaigns.create',
            'email-campaigns.edit',
            'email-campaigns.delete',
            'email-campaigns.send',
            'whatsapp.index',
            'whatsapp.send',
            'whatsapp.templates',
            'monitoramento.index',
            'relatorios.index',
            'relatorios.export',
        ])->get();
        $midia->givePermissionTo($midiaPermissions);

        // Parceiro - permissões limitadas
        $parceiro = Role::findByName('Parceiro');
        $parceiroPermissions = Permission::whereIn('name', [
            'dashboard.index',
            'inscricoes.index',
            'inscricoes.show',
            'contatos.index',
            'contatos.show',
        ])->get();
        $parceiro->givePermissionTo($parceiroPermissions);

        $this->command->info('  ✅ Permissões atribuídas aos roles');
    }

    /**
     * Atribuir roles aos usuários
     */
    private function assignRolesToUsers(): void
    {
        $users = User::all();
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        
        if (!$superAdminRole) {
            $this->command->warn('  ⚠️ Role Super Admin não encontrado');
            return;
        }

        foreach ($users as $user) {
            // Verificar se o usuário já tem roles
            if ($user->roles()->count() == 0) {
                // Atribuir role baseado no tipo_usuario
                $roleName = $this->getRoleByUserType($user->tipo_usuario);
                $role = Role::where('name', $roleName)->first();
                
                if ($role) {
                    $user->assignRole($role);
                    $this->command->line("  - Usuário {$user->name}: role {$roleName} atribuído");
                } else {
                    // Fallback para Super Admin
                    $user->assignRole($superAdminRole);
                    $this->command->line("  - Usuário {$user->name}: role Super Admin atribuído (fallback)");
                }
            }
        }

        $this->command->info('  ✅ Roles atribuídos aos usuários');
    }

    /**
     * Obter role baseado no tipo de usuário
     */
    private function getRoleByUserType(?string $tipoUsuario): string
    {
        return match($tipoUsuario) {
            'admin' => 'Super Admin',
            'vendedor' => 'Vendedor',
            'colaborador' => 'Colaborador',
            'midia' => 'Mídia',
            default => 'Super Admin'
        };
    }
}
