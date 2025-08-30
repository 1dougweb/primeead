<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class GoogleDriveOAuthSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:oauth-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configura autenticação OAuth2 para o Google Drive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Configurando autenticação OAuth2 para Google Drive...');
        
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
            
            $this->info('URL de autorização gerada:');
            $this->line($authUrl);
            $this->newLine();
            
            $this->info('Instruções:');
            $this->line('1. Acesse a URL acima no seu navegador');
            $this->line('2. Faça login com sua conta Google');
            $this->line('3. Autorize o acesso ao Google Drive');
            $this->line('4. Copie o código de autorização da URL de retorno');
            $this->newLine();
            
            $authCode = $this->ask('Cole o código de autorização aqui:');
            
            if (empty($authCode)) {
                $this->error('Código de autorização não fornecido');
                return 1;
            }
            
            // Trocar código por token
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            
            if (isset($accessToken['error'])) {
                $this->error('Erro ao obter token: ' . $accessToken['error_description'] ?? $accessToken['error']);
                return 1;
            }
            
            // Salvar token
            $tokenPath = storage_path('app/google-oauth-token.json');
            file_put_contents($tokenPath, json_encode($accessToken));
            
            $this->info('✅ Token salvo com sucesso em: ' . $tokenPath);
            $this->info('✅ Autenticação OAuth2 configurada!');
            
            // Testar acesso
            $this->info('Testando acesso...');
            $client->setAccessToken($accessToken);
            $service = new Drive($client);
            
            $rootFolderId = config('services.google.root_folder_id');
            $file = $service->files->get($rootFolderId);
            
            $this->info('✅ Acesso confirmado!');
            $this->info('Pasta: ' . $file->getName());
            $this->info('ID: ' . $file->getId());
            
        } catch (\Exception $e) {
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 