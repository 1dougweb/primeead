<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class MigratePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:migrate {--force : Forçar migração mesmo se já foi executada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar permissões do sistema antigo para o Spatie Laravel Permission';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== MIGRAÇÃO DE PERMISSÕES PARA SPATIE ===');
        
        // Verificar se já foi migrado
        if (!$this->option('force') && $this->alreadyMigrated()) {
            $this->warn('A migração já foi executada anteriormente.');
            $this->warn('Use --force para executar novamente.');
            return 1;
        }

        try {
            DB::beginTransaction();

            $this->info('1. Verificando tabelas antigas...');
            $this->checkOldTables();

            $this->info('2. Criando permissões do Spatie...');
            $this->createSpatiePermissions();

            $this->info('3. Criando roles do Spatie...');
            $this->createSpatieRoles();

            $this->info('4. Migrando dados de usuários...');
            $this->migrateUserData();

            $this->info('5. Limpando cache...');
            $this->clearCache();

            DB::commit();

            $this->info('✅ Migração concluída com sucesso!');
            $this->info('Total de permissões: ' . Permission::count());
            $this->info('Total de roles: ' . Role::count());
            $this->info('Total de usuários: ' . User::count());

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Erro durante a migração: ' . $e->getMessage());
            $this->error('Rollback executado.');
            return 1;
        }
    }

    /**
     * Verificar se já foi migrado
     */
    private function alreadyMigrated(): bool
    {
        return Permission::count() > 0 || Role::count() > 0;
    }

    /**
     * Verificar tabelas antigas
     */
    private function checkOldTables(): void
    {
        $oldTables = ['old_permissions', 'old_roles', 'old_role_permissions', 'old_user_roles'];
        
        foreach ($oldTables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->line("  - Tabela {$table}: {$count} registros");
            } else {
                $this->line("  - Tabela {$table}: não encontrada");
            }
        }
    }

    /**
     * Criar permissões do Spatie
     */
    private function createSpatiePermissions(): void
    {
        // Executar seeder de permissões
        $this->call('db:seed', ['--class' => 'SpatiePermissionsMigrationSeeder', '--force' => true]);
        $this->info('  ✅ Permissões criadas: ' . Permission::count());
    }

    /**
     * Criar roles do Spatie
     */
    private function createSpatieRoles(): void
    {
        // Roles já são criados pelo seeder
        $this->info('  ✅ Roles criados: ' . Role::count());
    }

    /**
     * Migrar dados de usuários
     */
    private function migrateUserData(): void
    {
        $users = User::all();
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        
        if (!$superAdminRole) {
            $this->warn('  ⚠️ Role Super Admin não encontrado');
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
                    $this->line("  - Usuário {$user->name}: role {$roleName} atribuído");
                } else {
                    // Fallback para Super Admin
                    $user->assignRole($superAdminRole);
                    $this->line("  - Usuário {$user->name}: role Super Admin atribuído (fallback)");
                }
            }
        }

        $this->info('  ✅ Dados de usuários migrados');
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

    /**
     * Limpar cache
     */
    private function clearCache(): void
    {
        $this->call('permission:cache-reset');
        $this->call('config:clear');
        $this->call('cache:clear');
        $this->info('  ✅ Cache limpo');
    }
}
