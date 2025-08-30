<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class FixPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:fix {--user= : User email to fix} {--clear-sessions : Clear all sessions} {--clear-cache : Clear all caches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix permissions issues including impersonation problems';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 FIXING PERMISSIONS ISSUES');
        $this->info('============================');
        
        // Clear sessions if requested
        if ($this->option('clear-sessions')) {
            $this->clearSessions();
        }
        
        // Clear caches if requested
        if ($this->option('clear-cache')) {
            $this->clearCaches();
        }
        
        // Fix specific user or all admin users
        $userEmail = $this->option('user');
        if ($userEmail) {
            $this->fixUserPermissions($userEmail);
        } else {
            $this->fixAllAdminUsers();
        }
        
        $this->info('');
        $this->info('✅ PERMISSIONS FIX COMPLETE');
        $this->info('');
        $this->info('🌐 NEXT STEPS:');
        $this->info('1. Clear browser cache and cookies');
        $this->info('2. Close and reopen browser');
        $this->info('3. Login again');
        $this->info('4. Access: http://127.0.0.1:8000/dashboard/usuarios/stop-impersonation');
    }
    
    private function clearSessions()
    {
        $this->info('1. Clearing session files...');
        $sessionPath = storage_path('framework/sessions');
        $cleared = 0;
        
        if (is_dir($sessionPath)) {
            $files = glob($sessionPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $cleared++;
                }
            }
        }
        
        $this->info("   ✅ Cleared {$cleared} session files");
    }
    
    private function clearCaches()
    {
        $this->info('2. Clearing caches...');
        
        $caches = [
            'cache:clear' => 'Application cache',
            'config:clear' => 'Config cache',
            'route:clear' => 'Route cache',
            'view:clear' => 'View cache'
        ];
        
        foreach ($caches as $command => $description) {
            try {
                Artisan::call($command);
                $this->info("   ✅ {$description} cleared");
            } catch (\Exception $e) {
                $this->error("   ❌ {$description} failed: " . $e->getMessage());
            }
        }
    }
    
    private function fixUserPermissions($email)
    {
        $this->info("3. Fixing permissions for user: {$email}");
        
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("   ❌ User not found: {$email}");
            return;
        }
        
        $this->info("   👤 User: {$user->name}");
        $this->info("   🔧 Type: {$user->tipo_usuario}");
        $this->info("   🔑 Is Admin: " . ($user->isAdmin() ? 'YES' : 'NO'));
        
        // Check if user has roles
        if ($user->roles->count() === 0) {
            $this->warn("   ⚠️  User has no roles assigned");
            
            // Try to assign appropriate role based on tipo_usuario
            $roleSlug = $this->getRoleSlugByUserType($user->tipo_usuario);
            $role = Role::where('slug', $roleSlug)->first();
            
            if ($role) {
                $user->roles()->attach($role->id);
                $this->info("   ✅ Assigned role: {$role->name}");
            } else {
                $this->error("   ❌ Role not found: {$roleSlug}");
            }
        } else {
            $this->info("   ✅ User has " . $user->roles->count() . " role(s)");
            foreach ($user->roles as $role) {
                $this->info("     - {$role->name} ({$role->permissions->count()} permissions)");
            }
        }
    }
    
    private function fixAllAdminUsers()
    {
        $this->info('3. Fixing permissions for all admin users...');
        
        $adminUsers = User::where('tipo_usuario', 'admin')->get();
        
        foreach ($adminUsers as $user) {
            $this->info("   👤 {$user->name} ({$user->email})");
            
            if ($user->roles->count() === 0) {
                $adminRole = Role::where('slug', 'admin')->orWhere('slug', 'super-admin')->first();
                if ($adminRole) {
                    $user->roles()->attach($adminRole->id);
                    $this->info("     ✅ Assigned role: {$adminRole->name}");
                } else {
                    $this->error("     ❌ Admin role not found");
                }
            } else {
                $this->info("     ✅ Has " . $user->roles->count() . " role(s)");
            }
        }
    }
    
    private function getRoleSlugByUserType($userType)
    {
        $mapping = [
            'admin' => 'admin',
            'vendedor' => 'vendedor',
            'colaborador' => 'colaborador',
            'midia' => 'midia',
            'parceiro' => 'parceiro'
        ];
        
        return $mapping[$userType] ?? 'user';
    }
} 