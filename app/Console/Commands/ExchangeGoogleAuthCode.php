<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class ExchangeGoogleAuthCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:exchange-code {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Troca código de autorização por token OAuth2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $authCode = $this->argument('code');
        
        if (empty($authCode)) {
            $this->error('Código de autorização não fornecido');
            return 1;
        }
        
        $this->info('Trocando código por token OAuth2...');
        
        try {
            $client = new Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect_uri'));
            $client->setScopes([Drive::DRIVE]);
            
            // Trocar código por token
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            
            if (isset($accessToken['error'])) {
                $this->error('Erro ao obter token: ' . ($accessToken['error_description'] ?? $accessToken['error']));
                return 1;
            }
            
            // Salvar token
            $tokenPath = storage_path('app/google-oauth-token.json');
            file_put_contents($tokenPath, json_encode($accessToken));
            
            $this->info('✅ Token salvo com sucesso em: ' . $tokenPath);
            
            // Mostrar informações do token
            $this->info('📋 Informações do token:');
            $this->line('Access Token: ' . substr($accessToken['access_token'], 0, 50) . '...');
            $this->line('Expira em: ' . date('Y-m-d H:i:s', time() + $accessToken['expires_in']));
            
            if (isset($accessToken['refresh_token'])) {
                $this->line('Refresh Token: ' . substr($accessToken['refresh_token'], 0, 50) . '...');
                
                // Salvar refresh token no .env
                $this->info('💾 Salvando refresh token no .env...');
                $envContent = file_get_contents('.env');
                $envContent = preg_replace(
                    '/GOOGLE_DRIVE_REFRESH_TOKEN=.*/',
                    'GOOGLE_DRIVE_REFRESH_TOKEN=' . $accessToken['refresh_token'],
                    $envContent
                );
                file_put_contents('.env', $envContent);
                $this->info('✅ Refresh token salvo no .env');
            }
            
            // Testar acesso
            $this->info('🧪 Testando acesso...');
            $client->setAccessToken($accessToken);
            $service = new Drive($client);
            
            $rootFolderId = config('services.google.root_folder_id');
            $file = $service->files->get($rootFolderId);
            
            $this->info('✅ Acesso confirmado!');
            $this->info('Pasta: ' . $file->getName());
            $this->info('ID: ' . $file->getId());
            
            // Testar upload
            $this->info('🧪 Testando upload...');
            $testContent = 'Teste OAuth2 - ' . date('Y-m-d H:i:s');
            $testFile = storage_path('app/test-oauth.txt');
            file_put_contents($testFile, $testContent);
            
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => 'test-oauth.txt',
                'parents' => [$rootFolderId]
            ]);
            
            $content = file_get_contents($testFile);
            $uploadedFile = $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'text/plain',
                'uploadType' => 'multipart'
            ]);
            
            $this->info('✅ Upload de teste realizado com sucesso!');
            $this->info('ID do arquivo: ' . $uploadedFile->getId());
            
            // Limpar arquivo de teste
            unlink($testFile);
            
            $this->info('🎉 Configuração OAuth2 concluída com sucesso!');
            $this->info('Agora você pode usar o upload de arquivos normalmente.');
            
        } catch (\Exception $e) {
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 