<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionMigrationController extends Controller
{
    /**
     * Mostrar página de migração de permissões
     */
    public function index()
    {
        $stats = $this->getMigrationStats();
        
        return view('admin.permissions.migration', compact('stats'));
    }

    /**
     * Executar migração de permissões
     */
    public function migrate(Request $request)
    {
        try {
            $force = $request->boolean('force', false);
            
            \Log::info('Iniciando migração via controller', [
                'force' => $force,
                'user_id' => auth()->id()
            ]);
            
            // Teste simples primeiro
            $testExitCode = Artisan::call('list');
            \Log::info('Teste Artisan::call', ['exit_code' => $testExitCode]);
            
            // Executar comando de migração
            $exitCode = Artisan::call('permissions:migrate', [
                '--force' => $force
            ]);

            $output = Artisan::output();
            
            \Log::info('Migração executada', [
                'exit_code' => $exitCode,
                'output_length' => strlen($output)
            ]);

            // Se exit code é 1 mas não há erro real (já migrado), tratar como sucesso
            if ($exitCode === 0 || (str_contains($output, 'já foi executada') && !$force)) {
                return response()->json([
                    'success' => true,
                    'message' => $exitCode === 0 ? 'Migração executada com sucesso!' : 'Sistema já migrado anteriormente.',
                    'output' => $output,
                    'stats' => $this->getMigrationStats()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao executar migração',
                    'output' => $output
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Erro na migração via controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro durante a migração: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Verificar status da migração
     */
    public function status()
    {
        return response()->json([
            'stats' => $this->getMigrationStats()
        ]);
    }

    /**
     * Limpar cache de permissões
     */
    public function clearCache()
    {
        try {
            Artisan::call('permission:cache-reset');
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            return response()->json([
                'success' => true,
                'message' => 'Cache limpo com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas da migração
     */
    private function getMigrationStats(): array
    {
        $stats = [
            'permissions' => [
                'current' => Permission::count(),
                'old_tables' => $this->getOldTablesInfo()
            ],
            'roles' => [
                'current' => Role::count(),
                'list' => Role::withCount('permissions')->get()->map(function($role) {
                    return [
                        'name' => $role->name,
                        'permissions_count' => $role->permissions_count
                    ];
                })
            ],
            'users' => [
                'total' => User::count(),
                'with_roles' => User::whereHas('roles')->count(),
                'without_roles' => User::whereDoesntHave('roles')->count()
            ],
            'migration_status' => $this->getMigrationStatus()
        ];

        return $stats;
    }

    /**
     * Obter informações das tabelas antigas
     */
    private function getOldTablesInfo(): array
    {
        $oldTables = ['old_permissions', 'old_roles', 'old_role_permissions', 'old_user_roles'];
        $info = [];

        foreach ($oldTables as $table) {
            if (Schema::hasTable($table)) {
                $info[$table] = DB::table($table)->count();
            } else {
                $info[$table] = 0;
            }
        }

        return $info;
    }

    /**
     * Obter status da migração
     */
    private function getMigrationStatus(): array
    {
        $hasPermissions = Permission::count() > 0;
        $hasRoles = Role::count() > 0;
        $hasOldTables = $this->hasOldTables();

        return [
            'migrated' => $hasPermissions && $hasRoles,
            'has_old_data' => $hasOldTables,
            'can_migrate' => $hasOldTables || !$hasPermissions,
            'needs_force' => $hasPermissions && $hasOldTables
        ];
    }

    /**
     * Verificar se existem tabelas antigas
     */
    private function hasOldTables(): bool
    {
        $oldTables = ['old_permissions', 'old_roles', 'old_role_permissions', 'old_user_roles'];
        
        foreach ($oldTables as $table) {
            if (Schema::hasTable($table) && DB::table($table)->count() > 0) {
                return true;
            }
        }

        return false;
    }
}
