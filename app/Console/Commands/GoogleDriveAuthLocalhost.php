<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class GoogleDriveAuthLocalhost extends Command
{
    protected $signature = 'google-drive:auth-localhost';
    protected $description = 'Authenticate with Google Drive using localhost redirect';

    public function handle()
    {
        $this->info('Iniciando autenticação com Google Drive (localhost)...');
        
        try {
            $client = new Client();
            
            // Configurar credenciais
            $credentialsPath = storage_path('app/google-credentials.json');
            if (!file_exists($credentialsPath)) {
                $this->error("Arquivo de credenciais não encontrado: {$credentialsPath}");
                return 1;
            }
            
            $client->setAuthConfig($credentialsPath);
            $client->setAccessType('offline');
            $client->setPrompt('consent');
            $client->addScope(Drive::DRIVE);
            
            // Usar localhost como redirect URI
            $client->setRedirectUri('http://localhost:8080');
            
            // Gerar URL de autorização
            $authUrl = $client->createAuthUrl();
            
            $this->info('Abra este link no seu navegador:');
            $this->line($authUrl);
            $this->info('');
            $this->info('Após autorizar, você será redirecionado para localhost:8080');
            $this->info('Copie o código da URL (parâmetro "code") e cole aqui.');
            $this->info('');
            
            $authCode = $this->ask('Cole o código de autorização aqui');
            
            if (empty($authCode)) {
                $this->error('Código de autorização não fornecido.');
                return 1;
            }
            
            // Trocar código por token
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            
            if (array_key_exists('error', $accessToken)) {
                $this->error('Erro ao obter token: ' . $accessToken['error_description']);
                return 1;
            }
            
            // Salvar token
            $tokenPath = storage_path('app/google-drive-token.json');
            file_put_contents($tokenPath, json_encode($accessToken));
            
            $this->info('Token salvo com sucesso!');
            
            // Testar conexão
            $client->setAccessToken($accessToken);
            $service = new Drive($client);
            
            $this->info('Testando conexão...');
            $results = $service->files->listFiles([
                'pageSize' => 5,
                'fields' => 'files(id, name)'
            ]);
            
            $files = $results->getFiles();
            $this->info('Conexão bem-sucedida! Encontrados ' . count($files) . ' arquivos.');
            
            foreach ($files as $file) {
                $this->line('- ' . $file->getName());
            }
            
        } catch (\Exception $e) {
            $this->error('Erro durante a autenticação: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 