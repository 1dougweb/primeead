<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AddParceirosPermissionToVendedor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:add-parceiros-to-vendedor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add parceiros.index permission to Vendedor role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Adding parceiros.index permission to Vendedor role...');

        $role = Role::where('name', 'Vendedor')->first();
        $permission = Permission::where('name', 'parceiros.index')->first();

        if (!$role) {
            $this->error('Role Vendedor not found!');
            return 1;
        }

        if (!$permission) {
            $this->error('Permission parceiros.index not found!');
            return 1;
        }

        // Adicionar permissão ao role
        $role->givePermissionTo($permission);

        // Limpar cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('Permission parceiros.index added to Vendedor role successfully!');
        $this->info('Cache cleared!');

        return 0;
    }
}
