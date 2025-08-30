<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primeiro, renomear as tabelas antigas
        $this->renameOldTables();
        
        // Depois, criar as tabelas do Spatie
        $this->createSpatiePermissionTables();
        
        // Migrar os dados existentes
        $this->migrateExistingData();
        
        // Remover tabelas antigas
        $this->dropOldTables();
    }

    /**
     * Renomear tabelas antigas
     */
    private function renameOldTables(): void
    {
        if (Schema::hasTable('permissions')) {
            Schema::rename('permissions', 'old_permissions');
        }
        
        if (Schema::hasTable('roles')) {
            Schema::rename('roles', 'old_roles');
        }
        
        if (Schema::hasTable('role_permissions')) {
            Schema::rename('role_permissions', 'old_role_permissions');
        }
        
        if (Schema::hasTable('user_roles')) {
            Schema::rename('user_roles', 'old_user_roles');
        }
    }

    /**
     * Criar as tabelas do Spatie Laravel Permission
     */
    private function createSpatiePermissionTables(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        // Criar tabela de permissões do Spatie
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // Criar tabela de roles do Spatie
        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // Criar tabela model_has_permissions
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                'model_has_permissions_permission_model_type_primary');
        });

        // Criar tabela model_has_roles
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole) {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                'model_has_roles_role_model_type_primary');
        });

        // Criar tabela role_has_permissions
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });
    }

    /**
     * Migrar dados existentes para o Spatie
     */
    private function migrateExistingData(): void
    {
        $tableNames = config('permission.table_names');
        
        // Migrar permissões se existirem
        if (Schema::hasTable('old_permissions')) {
            $oldPermissions = DB::table('old_permissions')->get();
            foreach ($oldPermissions as $permission) {
                DB::table($tableNames['permissions'])->insert([
                    'name' => $permission->slug ?? $permission->name,
                    'guard_name' => 'web',
                    'created_at' => $permission->created_at,
                    'updated_at' => $permission->updated_at,
                ]);
            }
        }

        // Migrar roles se existirem
        if (Schema::hasTable('old_roles')) {
            $oldRoles = DB::table('old_roles')->get();
            foreach ($oldRoles as $role) {
                DB::table($tableNames['roles'])->insert([
                    'name' => $role->slug ?? $role->name,
                    'guard_name' => 'web',
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ]);
            }
        }

        // Migrar relacionamentos role_permissions se existirem
        if (Schema::hasTable('old_role_permissions')) {
            $oldRolePermissions = DB::table('old_role_permissions')->get();
            foreach ($oldRolePermissions as $rolePermission) {
                // Buscar os nomes correspondentes
                $oldRole = DB::table('old_roles')->where('id', $rolePermission->role_id)->first();
                $oldPermission = DB::table('old_permissions')->where('id', $rolePermission->permission_id)->first();
                
                if ($oldRole && $oldPermission) {
                    $newRoleId = DB::table($tableNames['roles'])
                        ->where('name', $oldRole->slug ?? $oldRole->name)
                        ->value('id');
                        
                    $newPermissionId = DB::table($tableNames['permissions'])
                        ->where('name', $oldPermission->slug ?? $oldPermission->name)
                        ->value('id');

                    if ($newRoleId && $newPermissionId) {
                        DB::table($tableNames['role_has_permissions'])->insert([
                            'role_id' => $newRoleId,
                            'permission_id' => $newPermissionId,
                        ]);
                    }
                }
            }
        }

        // Migrar relacionamentos user_roles se existirem
        if (Schema::hasTable('old_user_roles')) {
            $oldUserRoles = DB::table('old_user_roles')->get();
            foreach ($oldUserRoles as $userRole) {
                // Buscar o nome do role correspondente
                $oldRole = DB::table('old_roles')->where('id', $userRole->role_id)->first();
                
                if ($oldRole) {
                    $newRoleId = DB::table($tableNames['roles'])
                        ->where('name', $oldRole->slug ?? $oldRole->name)
                        ->value('id');

                    if ($newRoleId) {
                        DB::table($tableNames['model_has_roles'])->insert([
                            'role_id' => $newRoleId,
                            'model_type' => 'App\\Models\\User',
                            'model_id' => $userRole->user_id,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Remover tabelas antigas
     */
    private function dropOldTables(): void
    {
        Schema::dropIfExists('old_user_roles');
        Schema::dropIfExists('old_role_permissions');
        Schema::dropIfExists('old_permissions');
        Schema::dropIfExists('old_roles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
