<?php

namespace App\Console\Commands;

use Spatie\Permission\Models\Permission;
use Illuminate\Console\Command;

class ListPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:list {--module= : Filtrar por módulo} {--search= : Pesquisar por nome ou slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista todas as permissões disponíveis no sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = $this->option('module');
        $search = $this->option('search');

        $query = Permission::query();

        // Pesquisar por nome se especificado
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $permissions = $query->get();

        if ($permissions->isEmpty()) {
            $this->info('Nenhuma permissão encontrada com os critérios especificados.');
            return 0;
        }

        // Agrupar permissões por módulo (baseado no prefixo do nome)
        $permissionsByModule = $permissions->groupBy(function($permission) {
            $parts = explode('_', $permission->name);
            return $parts[0] ?? 'outros';
        });

        foreach ($permissionsByModule as $moduleName => $modulePermissions) {
            $this->info("\nMódulo: {$moduleName}");
            $this->table(
                ['ID', 'Nome', 'Guard'],
                $modulePermissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'guard' => $permission->guard_name,
                    ];
                })
            );
        }

        // Mostrar estatísticas
        $this->info("\nEstatísticas:");
        $this->info("Total de permissões: {$permissions->count()}");
        $this->info("Total de módulos: {$permissionsByModule->count()}");

        return 0;
    }
} 