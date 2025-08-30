<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class SetupGoogleOAuthToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:setup-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configura token OAuth2 usando refresh token existente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Configurando token OAuth2...');
        
        try {
            $client = new Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setScopes([Drive::DRIVE]);
            
            // Usar refresh token existente
            $refreshToken = env('GOOGLE_DRIVE_REFRESH_TOKEN');
            
            if (empty($refreshToken)) {
                $this->error('Refresh token não encontrado no .env');
                return 1;
            }
            
            $this->info('Usando refresh token existente...');
            
            // Obter novo access token
            $accessToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            
            if (isset($accessToken['error'])) {
                $this->error('Erro ao obter token: ' . $accessToken['error_description'] ?? $accessToken['error']);
                return 1;
            }
            
            // Salvar token
            $tokenPath = storage_path('app/google-oauth-token.json');
            file_put_contents($tokenPath, json_encode($accessToken));
            
            $this->info('✅ Token salvo com sucesso em: ' . $tokenPath);
            
            // Testar acesso
            $this->info('Testando acesso...');
            $client->setAccessToken($accessToken);
            $service = new Drive($client);
            
            $rootFolderId = config('services.google.root_folder_id');
            $file = $service->files->get($rootFolderId);
            
            $this->info('✅ Acesso confirmado!');
            $this->info('Pasta: ' . $file->getName());
            $this->info('ID: ' . $file->getId());
            
            // Testar upload
            $this->info('Testando upload...');
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
            
        } catch (\Exception $e) {
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 