<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ApplyGoogleCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:apply-credentials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aplica credenciais OAuth2 do arquivo temp_credentials.txt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Aplicando credenciais OAuth2...');
        
        $tempFile = base_path('temp_credentials.txt');
        
        if (!file_exists($tempFile)) {
            $this->error('Arquivo temp_credentials.txt não encontrado');
            return 1;
        }
        
        $content = file_get_contents($tempFile);
        
        // Extrair Client ID
        if (preg_match('/CLIENT_ID=(.+)/', $content, $matches)) {
            $clientId = trim($matches[1]);
        } else {
            $this->error('Client ID não encontrado no arquivo');
            return 1;
        }
        
        // Extrair Client Secret
        if (preg_match('/CLIENT_SECRET=(.+)/', $content, $matches)) {
            $clientSecret = trim($matches[1]);
        } else {
            $this->error('Client Secret não encontrado no arquivo');
            return 1;
        }
        
        if (empty($clientId) || empty($clientSecret)) {
            $this->error('Client ID ou Client Secret estão vazios');
            return 1;
        }
        
        $this->info('Client ID encontrado: ' . substr($clientId, 0, 30) . '...');
        $this->info('Client Secret encontrado: ' . substr($clientSecret, 0, 10) . '...');
        
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
        
        $this->info('🎉 Credenciais aplicadas com sucesso!');
        $this->info('Agora execute: php artisan google-drive:auth-url');
        
        // Remover arquivo temporário
        unlink($tempFile);
        $this->info('🗑️ Arquivo temporário removido');
        
        return 0;
    }
} 