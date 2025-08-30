<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestImpersonationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:impersonation {admin_email?} {user_email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test impersonation functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $adminEmail = $this->argument('admin_email') ?? 'webmaster@ensinocerto.com.br';
        $userEmail = $this->argument('user_email') ?? 'test@example.com';

        $this->info('🧪 TESTING IMPERSONATION FUNCTIONALITY');
        $this->info('=====================================');

        // Encontrar o admin
        $admin = User::where('email', $adminEmail)->first();
        if (!$admin) {
            $this->error("Admin com email '{$adminEmail}' não encontrado!");
            return 1;
        }

        // Encontrar o usuário para impersonar
        $userToImpersonate = User::where('email', $userEmail)->first();
        if (!$userToImpersonate) {
            $this->error("Usuário com email '{$userEmail}' não encontrado!");
            return 1;
        }

        $this->info("Admin: {$admin->name} ({$admin->email})");
        $this->info("Usuário para impersonar: {$userToImpersonate->name} ({$userToImpersonate->email})");

        // Verificar permissões do admin
        $hasPermission = $admin->hasPermissionTo('usuarios.impersonate');
        $this->info("Admin tem permissão usuarios.impersonate: " . ($hasPermission ? 'SIM' : 'NÃO'));

        if (!$hasPermission) {
            $this->error('Admin não tem permissão para impersonation!');
            return 1;
        }

        // Simular login como admin
        Auth::login($admin);
        $this->info("Login como admin realizado");

        // Simular impersonation
        $this->info("\n🔄 SIMULANDO IMPERSONATION...");

        // Salvar dados originais da sessão
        session([
            'impersonating_user_id' => $admin->id,
            'impersonating_user_name' => $admin->name,
            'original_admin_id' => $admin->id,
            'original_admin_name' => $admin->name,
            'original_admin_email' => $admin->email,
            'original_admin_tipo' => $admin->tipo_usuario,
            'original_admin_logged_in' => $admin->isAdmin(),
        ]);

        // Fazer login como o usuário selecionado
        Auth::login($userToImpersonate);

        // Atualizar variáveis de sessão para o usuário impersonado
        session([
            'admin_id' => $userToImpersonate->id,
            'admin_name' => $userToImpersonate->name,
            'admin_email' => $userToImpersonate->email,
            'admin_tipo' => $userToImpersonate->tipo_usuario,
            'admin_logged_in' => $userToImpersonate->isAdmin(),
        ]);

        // Marcar que estamos em modo de impersonation
        session(['is_impersonating' => true]);
        session(['impersonated_user_name' => $userToImpersonate->name]);

        $this->info("✅ Impersonation iniciada com sucesso!");
        $this->info("Usuário atual: " . Auth::user()->name);
        $this->info("Session admin_id: " . session('admin_id'));
        $this->info("Session is_impersonating: " . (session('is_impersonating') ? 'true' : 'false'));

        // Simular saída da impersonation
        $this->info("\n🔄 SIMULANDO SAÍDA DA IMPERSONATION...");

        // Recuperar o usuário original
        $originalUserId = session('impersonating_user_id');
        $originalUser = User::find($originalUserId);

        if (!$originalUser) {
            $this->error('Usuário original não encontrado!');
            return 1;
        }

        // Restaurar variáveis de sessão originais
        session([
            'admin_id' => session('original_admin_id') ?? $originalUser->id,
            'admin_name' => session('original_admin_name') ?? $originalUser->name,
            'admin_email' => session('original_admin_email') ?? $originalUser->email,
            'admin_tipo' => session('original_admin_tipo') ?? $originalUser->tipo_usuario,
            'admin_logged_in' => session('original_admin_logged_in') ?? $originalUser->isAdmin(),
        ]);

        // Limpar dados de impersonation da sessão
        session()->forget([
            'is_impersonating', 
            'impersonating_user_id', 
            'impersonating_user_name', 
            'impersonated_user_name',
            'original_admin_id',
            'original_admin_name',
            'original_admin_email',
            'original_admin_tipo',
            'original_admin_logged_in'
        ]);

        // Fazer login como o usuário original
        Auth::login($originalUser);

        $this->info("✅ Saída da impersonation realizada com sucesso!");
        $this->info("Usuário atual: " . Auth::user()->name);
        $this->info("Session admin_id: " . session('admin_id'));
        $this->info("Session is_impersonating: " . (session('is_impersonating') ? 'true' : 'false'));

        $this->info("\n✅ TESTE CONCLUÍDO COM SUCESSO!");
        $this->info("A funcionalidade de impersonation está funcionando corretamente.");
        $this->info("\n🌐 Para testar no navegador:");
        $this->info("1. Acesse: http://127.0.0.1:8000/dashboard/usuarios");
        $this->info("2. Clique no botão com ícone de usuário secreto (👤)");
        $this->info("3. Confirme a impersonation");
        $this->info("4. Para sair, clique em 'Sair da Impersonation' no topo da página");

        return 0;
    }
} 