<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GoogleDrivePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔐 Criando permissões do Google Drive...');

        // Definir as permissões do Google Drive usando a estrutura antiga
        $permissions = [
            [
                'name' => 'Visualizar Arquivos',
                'slug' => 'arquivos.index',
                'module' => 'arquivos',
                'description' => 'Visualizar arquivos do Google Drive',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Criar Arquivos',
                'slug' => 'arquivos.create',
                'module' => 'arquivos',
                'description' => 'Criar pastas e fazer upload de arquivos',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Editar Arquivos',
                'slug' => 'arquivos.edit',
                'module' => 'arquivos',
                'description' => 'Editar arquivos e pastas',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Excluir Arquivos',
                'slug' => 'arquivos.delete',
                'module' => 'arquivos',
                'description' => 'Excluir arquivos e pastas',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Compartilhar Arquivos',
                'slug' => 'arquivos.share',
                'module' => 'arquivos',
                'description' => 'Compartilhar arquivos e pastas',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Criar as permissões usando DB diretamente
        foreach ($permissions as $permissionData) {
            $existing = DB::table('permissions')
                ->where('name', $permissionData['slug'])
                ->first();
                
            if (!$existing) {
                // Ajustar para a estrutura da tabela
                $insertData = [
                    'name' => $permissionData['slug'],
                    'guard_name' => 'web',
                    'module' => $permissionData['module'],
                    'description' => $permissionData['description'],
                    'is_active' => $permissionData['is_active'],
                    'created_at' => $permissionData['created_at'],
                    'updated_at' => $permissionData['updated_at']
                ];
                DB::table('permissions')->insert($insertData);
                $this->command->line("✅ Permissão criada: {$permissionData['name']}");
            } else {
                $this->command->line("⚠️  Permissão já existe: {$permissionData['name']}");
            }
        }

        $this->command->info('🎭 Criando roles se não existirem...');
        $this->createRolesIfNotExist();

        $this->command->info('🔗 Atribuindo permissões aos roles...');
        $this->assignPermissionsToRoles();

        $this->command->info('✅ Permissões do Google Drive configuradas com sucesso!');
    }

    private function createRolesIfNotExist(): void
    {
        $roles = [
            [
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Acesso completo ao sistema',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Vendedor',
                'slug' => 'vendedor',
                'description' => 'Acesso às funcionalidades de vendas',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Colaborador',
                'slug' => 'colaborador',
                'description' => 'Acesso básico ao sistema',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Mídia',
                'slug' => 'midia',
                'description' => 'Acesso às funcionalidades de mídia',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($roles as $roleData) {
            $existing = DB::table('roles')
                ->where('name', $roleData['slug'])
                ->first();
                
            if (!$existing) {
                // Ajustar para a estrutura da tabela
                $insertData = [
                    'name' => $roleData['slug'],
                    'guard_name' => 'web',
                    'created_at' => $roleData['created_at'],
                    'updated_at' => $roleData['updated_at']
                ];
                DB::table('roles')->insert($insertData);
                $this->command->line("✅ Role criado: {$roleData['name']}");
            } else {
                $this->command->line("⚠️  Role já existe: {$roleData['name']}");
            }
        }
    }

    private function assignPermissionsToRoles(): void
    {
        // Buscar IDs dos roles
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $vendedorRole = DB::table('roles')->where('name', 'vendedor')->first();
        $colaboradorRole = DB::table('roles')->where('name', 'colaborador')->first();
        $midiaRole = DB::table('roles')->where('name', 'midia')->first();

        // Buscar IDs das permissões do Google Drive
        $drivePermissions = DB::table('permissions')->where('module', 'arquivos')->get();
        $permissionIds = $drivePermissions->pluck('id')->toArray();

        // Admin: todas as permissões
        if ($adminRole && !empty($permissionIds)) {
            foreach ($permissionIds as $permissionId) {
                $existing = DB::table('role_has_permissions')
                    ->where('role_id', $adminRole->id)
                    ->where('permission_id', $permissionId)
                    ->first();
                    
                if (!$existing) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $adminRole->id,
                        'permission_id' => $permissionId
                    ]);
                }
            }
            $this->command->line("✅ Admin: todas as permissões atribuídas");
        }

        // Vendedor: visualizar, criar e compartilhar
        if ($vendedorRole) {
            $vendedorPermissionSlugs = ['arquivos.index', 'arquivos.create', 'arquivos.share'];
            $vendedorPermissions = $drivePermissions->whereIn('name', $vendedorPermissionSlugs);
            
            foreach ($vendedorPermissions as $permission) {
                $existing = DB::table('role_has_permissions')
                    ->where('role_id', $vendedorRole->id)
                    ->where('permission_id', $permission->id)
                    ->first();
                    
                if (!$existing) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $vendedorRole->id,
                        'permission_id' => $permission->id
                    ]);
                }
            }
            $this->command->line("✅ Vendedor: permissões de visualização, criação e compartilhamento");
        }

        // Colaborador: visualizar e criar
        if ($colaboradorRole) {
            $colaboradorPermissionSlugs = ['arquivos.index', 'arquivos.create'];
            $colaboradorPermissions = $drivePermissions->whereIn('name', $colaboradorPermissionSlugs);
            
            foreach ($colaboradorPermissions as $permission) {
                $existing = DB::table('role_has_permissions')
                    ->where('role_id', $colaboradorRole->id)
                    ->where('permission_id', $permission->id)
                    ->first();
                    
                if (!$existing) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $colaboradorRole->id,
                        'permission_id' => $permission->id
                    ]);
                }
            }
            $this->command->line("✅ Colaborador: permissões de visualização e criação");
        }

        // Mídia: visualizar, criar e compartilhar
        if ($midiaRole) {
            $midiaPermissionSlugs = ['arquivos.index', 'arquivos.create', 'arquivos.share'];
            $midiaPermissions = $drivePermissions->whereIn('name', $midiaPermissionSlugs);
            
            foreach ($midiaPermissions as $permission) {
                $existing = DB::table('role_has_permissions')
                    ->where('role_id', $midiaRole->id)
                    ->where('permission_id', $permission->id)
                    ->first();
                    
                if (!$existing) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $midiaRole->id,
                        'permission_id' => $permission->id
                    ]);
                }
            }
            $this->command->line("✅ Mídia: permissões de visualização, criação e compartilhamento");
        }
    }
} 