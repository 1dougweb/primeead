<?php

namespace App\Console\Commands;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Console\Command;

class AssignPermissionsToRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:assign {role : O slug do papel (role)} {--permission=* : Slugs das permissões a serem atribuídas} {--module= : Atribuir todas as permissões de um módulo específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atribui permissões a um papel (role)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleSlug = $this->argument('role');
        $permissionSlugs = $this->option('permission');
        $module = $this->option('module');

        // Buscar o papel pelo slug
        $role = Role::where('slug', $roleSlug)->first();

        if (!$role) {
            $this->error("Papel '{$roleSlug}' não encontrado!");
            return 1;
        }

        // Se um módulo foi especificado, atribuir todas as permissões desse módulo
        if ($module) {
            $permissions = Permission::where('module', $module)->get();
            
            if ($permissions->isEmpty()) {
                $this->error("Nenhuma permissão encontrada para o módulo '{$module}'!");
                return 1;
            }
            
            $role->permissions()->syncWithoutDetaching($permissions);
            
            $this->info("Todas as permissões do módulo '{$module}' foram atribuídas ao papel '{$role->name}'!");
            
            // Listar as permissões atribuídas
            $this->table(
                ['ID', 'Nome', 'Slug', 'Módulo'],
                $permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'slug' => $permission->slug,
                        'module' => $permission->module,
                    ];
                })
            );
            
            return 0;
        }

        // Se permissões específicas foram fornecidas, atribuí-las
        if (!empty($permissionSlugs)) {
            $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
            
            if ($permissions->isEmpty()) {
                $this->error("Nenhuma das permissões especificadas foi encontrada!");
                return 1;
            }
            
            if ($permissions->count() !== count($permissionSlugs)) {
                $foundSlugs = $permissions->pluck('slug')->toArray();
                $notFound = array_diff($permissionSlugs, $foundSlugs);
                $this->warn("As seguintes permissões não foram encontradas: " . implode(', ', $notFound));
            }
            
            $role->permissions()->syncWithoutDetaching($permissions);
            
            $this->info("Permissões atribuídas com sucesso ao papel '{$role->name}'!");
            
            // Listar as permissões atribuídas
            $this->table(
                ['ID', 'Nome', 'Slug', 'Módulo'],
                $permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'slug' => $permission->slug,
                        'module' => $permission->module,
                    ];
                })
            );
            
            return 0;
        }

        // Se nenhuma permissão ou módulo foi especificado, listar todas as permissões disponíveis
        $this->info("Nenhuma permissão especificada. Aqui estão todas as permissões disponíveis:");
        
        $allPermissions = Permission::all();
        
        $this->table(
            ['ID', 'Nome', 'Slug', 'Módulo'],
            $allPermissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'slug' => $permission->slug,
                    'module' => $permission->module,
                ];
            })
        );
        
        $this->info("Para atribuir permissões, use: php artisan permissions:assign {$roleSlug} --permission=slug1 --permission=slug2");
        $this->info("Para atribuir todas as permissões de um módulo, use: php artisan permissions:assign {$roleSlug} --module=nome_do_modulo");
        
        return 0;
    }
} 