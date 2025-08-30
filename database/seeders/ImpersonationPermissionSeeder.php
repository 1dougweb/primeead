<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ImpersonationPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar permissão de impersonation
        $permission = Permission::create([
            'name' => 'Fazer Login como Usuário',
            'slug' => 'impersonate_users',
            'module' => 'usuarios',
            'description' => 'Permite fazer login como outro usuário para fins de suporte e depuração',
            'is_active' => true
        ]);

        // Atribuir permissão apenas ao papel de Admin
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->attach($permission->id);
            $this->command->info("Permissão 'impersonate_users' atribuída ao papel Admin");
        }

        $this->command->info("Permissão de impersonation criada com sucesso!");
    }
}
