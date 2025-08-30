<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class SetupOAuthToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:setup-oauth';

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
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
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
                $this->error('Erro ao obter token: ' . ($accessToken['error_description'] ?? $accessToken['error']));
                return 1;
            }
            
            // Salvar token
            $tokenPath = storage_path('app/google-drive-token.json');
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
            
        } catch (\Exception $e) {
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 