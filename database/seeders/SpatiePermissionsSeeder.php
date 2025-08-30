<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class SpatiePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resetar cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar permissões
        $this->createPermissions();

        // Criar roles
        $this->createRoles();

        // Atribuir permissões aos roles
        $this->assignPermissionsToRoles();

        // Criar usuário admin padrão se não existir
        $this->createDefaultAdminUser();
    }

    /**
     * Criar todas as permissões do sistema
     */
    private function createPermissions(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.index', 'description' => 'Visualizar dashboard'],
            
            // Inscrições
            ['name' => 'inscricoes.index', 'description' => 'Listar inscrições'],
            ['name' => 'inscricoes.create', 'description' => 'Criar inscrições'],
            ['name' => 'inscricoes.edit', 'description' => 'Editar inscrições'],
            ['name' => 'inscricoes.delete', 'description' => 'Deletar inscrições'],
            ['name' => 'inscricoes.export', 'description' => 'Exportar inscrições'],
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
            ['name' => 'roles.index', 'description' => 'Listar roles'],
            ['name' => 'roles.create', 'description' => 'Criar roles'],
            ['name' => 'roles.edit', 'description' => 'Editar roles'],
            ['name' => 'roles.delete', 'description' => 'Deletar roles'],
            ['name' => 'permissoes.migrate', 'description' => 'Migrar permissões'],
            
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
            
            // Contract Templates
            ['name' => 'contract-templates.index', 'description' => 'Listar templates de contratos'],
            ['name' => 'contract-templates.create', 'description' => 'Criar templates de contratos'],
            ['name' => 'contract-templates.edit', 'description' => 'Editar templates de contratos'],
            ['name' => 'contract-templates.delete', 'description' => 'Deletar templates de contratos'],
            ['name' => 'contract-templates.generate-ai', 'description' => 'Gerar templates de contratos com IA'],
            
            // Payment Templates
            ['name' => 'payment-templates.index', 'description' => 'Listar templates de pagamentos'],
            ['name' => 'payment-templates.create', 'description' => 'Criar templates de pagamentos'],
            ['name' => 'payment-templates.edit', 'description' => 'Editar templates de pagamentos'],
            ['name' => 'payment-templates.delete', 'description' => 'Deletar templates de pagamentos'],
            ['name' => 'payment-templates.generate-ai', 'description' => 'Gerar templates de pagamentos com IA'],
            
            // Enrollment Templates
            ['name' => 'enrollment-templates.index', 'description' => 'Listar templates de inscrições'],
            ['name' => 'enrollment-templates.create', 'description' => 'Criar templates de inscrições'],
            ['name' => 'enrollment-templates.edit', 'description' => 'Editar templates de inscrições'],
            ['name' => 'enrollment-templates.delete', 'description' => 'Deletar templates de inscrições'],
            ['name' => 'enrollment-templates.generate-ai', 'description' => 'Gerar templates de inscrições com IA'],
            
            // Matriculation Templates
            ['name' => 'matriculation-templates.index', 'description' => 'Listar templates de matrículas'],
            ['name' => 'matriculation-templates.create', 'description' => 'Criar templates de matrículas'],
            ['name' => 'matriculation-templates.edit', 'description' => 'Editar templates de matrículas'],
            ['name' => 'matriculation-templates.delete', 'description' => 'Deletar templates de matrículas'],
            ['name' => 'matriculation-templates.generate-ai', 'description' => 'Gerar templates de matrículas com IA'],
            
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

        $this->command->info('Permissões criadas com sucesso!');
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

        $this->command->info('Roles criados com sucesso!');
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
            'inscricoes.index',
            'inscricoes.show',
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
            'relatorios.index',
            'relatorios.export',
        ])->get();
        $midia->givePermissionTo($midiaPermissions);

        // Parceiro - permissões muito limitadas
        $parceiro = Role::findByName('Parceiro');
        $parceiroPermissions = Permission::whereIn('name', [
            'dashboard.index',
            'inscricoes.index',
            'inscricoes.show',
            'matriculas.index',
            'matriculas.show',
        ])->get();
        $parceiro->givePermissionTo($parceiroPermissions);

        // Vendedor
        $vendedorRole = Role::where('name', 'Vendedor')->first();
        if ($vendedorRole) {
            $vendedorPermissions = Permission::whereIn('name', [
                'dashboard.index',
                'inscricoes.index', 'inscricoes.create', 'inscricoes.edit', 'inscricoes.delete', 'inscricoes.show',
                'matriculas.index', 'matriculas.create', 'matriculas.edit', 'matriculas.delete', 'matriculas.show',
                'usuarios.show',
                'pagamentos.index', 'pagamentos.create', 'pagamentos.edit', 'pagamentos.delete', 'pagamentos.show',
                'contratos.index', 'contratos.create', 'contratos.edit', 'contratos.delete', 'contratos.show',
                'contatos.index', 'contatos.create', 'contatos.edit', 'contatos.delete', 'contatos.show',
                'kanban.index', 'kanban.edit', 'kanban.move',
                'email-templates.index', 'email-templates.create', 'email-templates.edit', 'email-templates.delete',
                'email-campaigns.index', 'email-campaigns.create', 'email-campaigns.edit', 'email-campaigns.delete', 'email-campaigns.send',
                'whatsapp.index', 'whatsapp.send', 'whatsapp.templates',
                'google-drive.index', 'google-drive.upload', 'google-drive.delete',
                'contract-templates.index', 'contract-templates.create', 'contract-templates.edit', 'contract-templates.generate-ai',
                'payment-templates.index', 'payment-templates.create', 'payment-templates.edit', 'payment-templates.generate-ai',
                'enrollment-templates.index', 'enrollment-templates.create', 'enrollment-templates.edit', 'enrollment-templates.generate-ai',
                'matriculation-templates.index', 'matriculation-templates.create', 'matriculation-templates.edit', 'matriculation-templates.generate-ai',
            ])->get();
            $vendedorRole->syncPermissions($vendedorPermissions);
        }

        $this->command->info('Permissões atribuídas aos roles com sucesso!');
    }

    /**
     * Criar usuário admin padrão
     */
    private function createDefaultAdminUser(): void
    {
        $adminUser = User::where('email', 'admin@sistema.com')->first();
        
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Administrador',
                'email' => 'admin@sistema.com',
                'password' => bcrypt('admin123'),
                'tipo_usuario' => 'admin',
                'ativo' => true,
                'email_verified_at' => now(),
            ]);
        }

        // Atribuir role de Super Admin
        $adminUser->assignRole('Super Admin');

        $this->command->info('Usuário admin padrão criado/atualizado com sucesso!');
    }
}
