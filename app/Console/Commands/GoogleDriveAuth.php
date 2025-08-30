<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class GoogleDriveAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Autentica com o Google Drive usando OAuth 2.0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando autenticação com Google Drive...');

        try {
            $client = new Client();
            $credentialsPath = storage_path('app/google-credentials.json');
            
            if (!file_exists($credentialsPath)) {
                $this->error("Arquivo de credenciais não encontrado em: {$credentialsPath}");
                return 1;
            }

            $client->setAuthConfig($credentialsPath);
            $client->setAccessType('offline');
            $client->addScope(Drive::DRIVE);

            $tokenPath = storage_path('app/google-drive-token.json');

            // Se já existe um token, verificar se é válido
            if (file_exists($tokenPath)) {
                $accessToken = json_decode(file_get_contents($tokenPath), true);
                $client->setAccessToken($accessToken);

                if (!$client->isAccessTokenExpired()) {
                    $this->info('Token válido já existe. Autenticação já realizada!');
                    return 0;
                }

                // Token expirado, tentar renovar
                if ($client->getRefreshToken()) {
                    $this->info('Token expirado. Renovando...');
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
                    $this->info('Token renovado com sucesso!');
                    return 0;
                }
            }

            // Gerar URL de autorização
            $authUrl = $client->createAuthUrl();
            $this->info('Abra este link no seu navegador:');
            $this->line($authUrl);
            $this->newLine();

            // Solicitar código de autorização
            $authCode = $this->ask('Cole o código de autorização aqui');

            // Trocar código por token
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            
            if (array_key_exists('error', $accessToken)) {
                $this->error('Erro na autenticação: ' . $accessToken['error']);
                return 1;
            }

            // Salvar token
            file_put_contents($tokenPath, json_encode($accessToken));
            $this->info('Autenticação realizada com sucesso!');
            $this->info('Token salvo em: ' . $tokenPath);

            // Testar a conexão
            $this->info('Testando conexão...');
            $service = new Drive($client);
            $results = $service->files->listFiles(['pageSize' => 1]);
            $this->info('Conexão testada com sucesso!');

            return 0;

        } catch (\Exception $e) {
            $this->error('Erro durante a autenticação: ' . $e->getMessage());
            return 1;
        }
    }
} 