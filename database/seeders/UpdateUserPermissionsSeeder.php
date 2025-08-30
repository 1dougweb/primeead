<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class UpdateUserPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Encontrar o usuário test
        $user = User::where('email', 'test@example.com')->first();
        
        if (!$user) {
            echo "Usuário test@example.com não encontrado!\n";
            return;
        }

        // Obter o papel do usuário
        $role = $user->roles->first();
        
        if (!$role) {
            echo "Usuário não tem papel definido!\n";
            return;
        }

        // Permissões adicionais necessárias
        $permissionsToAdd = [
            'view_kanban',
            'view_files',
            'view_contacts',
            'view_partners',
            'view_contracts'
        ];

        foreach ($permissionsToAdd as $permSlug) {
            $permission = Permission::where('slug', $permSlug)->first();
            
            if ($permission) {
                if (!$role->permissions->contains($permission)) {
                    $role->permissions()->attach($permission);
                    echo "Adicionada permissão: {$permission->name}\n";
                } else {
                    echo "Permissão já existe: {$permission->name}\n";
                }
            } else {
                echo "Permissão não encontrada: {$permSlug}\n";
            }
        }

        echo "Permissões atualizadas com sucesso!\n";
    }
} 