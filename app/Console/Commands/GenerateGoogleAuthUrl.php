<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class GenerateGoogleAuthUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:auth-url';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera URL de autorização OAuth2 para Google Drive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Gerando URL de autorização OAuth2...');
        
        try {
            $client = new Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect_uri'));
            $client->setScopes([Drive::DRIVE]);
            $client->setAccessType('offline');
            $client->setPrompt('consent');
            
            // Gerar URL de autorização
            $authUrl = $client->createAuthUrl();
            
            $this->info('✅ URL de autorização gerada:');
            $this->newLine();
            $this->line($authUrl);
            $this->newLine();
            
            $this->info('📋 Instruções:');
            $this->line('1. Copie a URL acima e cole no seu navegador');
            $this->line('2. Faça login com sua conta Google (ensinocertodocumentos@gmail.com)');
            $this->line('3. Clique em "Permitir" para autorizar o acesso');
            $this->line('4. Você será redirecionado para uma URL que contém o código');
            $this->line('5. Copie o código da URL (parte após "code=")');
            $this->newLine();
            
            $this->info('💡 Exemplo de URL de retorno:');
            $this->line('http://127.0.0.1:8000/auth/google/callback?code=4/0AfJohXn...');
            $this->line('O código é: 4/0AfJohXn...');
            $this->newLine();
            
            $this->info('🔧 Depois de obter o código, execute:');
            $this->line('php artisan google-drive:exchange-code [SEU_CODIGO]');
            
        } catch (\Exception $e) {
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 