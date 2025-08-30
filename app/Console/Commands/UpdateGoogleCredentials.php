<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateGoogleCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:update-credentials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza credenciais OAuth2 do Google Drive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Atualizando credenciais OAuth2 do Google Drive...');
        $this->newLine();
        
        $this->info('📋 Instruções para criar novo cliente OAuth2:');
        $this->line('1. Acesse: https://console.cloud.google.com/');
        $this->line('2. Faça login com ensinocertodocumentos@gmail.com');
        $this->line('3. Selecione o projeto "ensino-certo"');
        $this->line('4. Vá em "APIs & Services" > "Credentials"');
        $this->line('5. Clique em "+ CREATE CREDENTIALS" > "OAuth 2.0 Client IDs"');
        $this->line('6. Configure:');
        $this->line('   - Application type: Web application');
        $this->line('   - Name: Ensino Certo - Google Drive');
        $this->line('   - Authorized redirect URIs:');
        $this->line('     * http://127.0.0.1:8000/auth/google/callback');
        $this->line('     * http://localhost:8000/auth/google/callback');
        $this->newLine();
        
        $clientId = $this->ask('Cole o novo Client ID:');
        $clientSecret = $this->ask('Cole o novo Client Secret:');
        
        if (empty($clientId) || empty($clientSecret)) {
            $this->error('Client ID e Client Secret são obrigatórios');
            return 1;
        }
        
        // Atualizar .env
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        
        // Atualizar GOOGLE_OAUTH_CLIENT_ID
        $envContent = preg_replace(
            '/GOOGLE_OAUTH_CLIENT_ID=.*/',
            'GOOGLE_OAUTH_CLIENT_ID=' . $clientId,
            $envContent
        );
        
        // Atualizar GOOGLE_OAUTH_CLIENT_SECRET
        $envContent = preg_replace(
            '/GOOGLE_OAUTH_CLIENT_SECRET=.*/',
            'GOOGLE_OAUTH_CLIENT_SECRET=' . $clientSecret,
            $envContent
        );
        
        file_put_contents($envPath, $envContent);
        
        $this->info('✅ Credenciais atualizadas no .env');
        $this->info('🔄 Limpando cache de configuração...');
        
        // Limpar cache
        $this->call('config:clear');
        
        $this->info('✅ Cache limpo');
        $this->newLine();
        
        $this->info('🎉 Credenciais atualizadas com sucesso!');
        $this->info('Agora execute: php artisan google-drive:auth-url');
        
        return 0;
    }
} 