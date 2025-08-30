<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class GenerateGoogleDriveToken extends Command
{
    protected $signature = 'google-drive:auth';
    protected $description = 'Gerar token de autenticação para Google Drive';

    public function handle()
    {
        $this->info('Iniciando processo de autenticação do Google Drive...');

        try {
            // Verificar se o arquivo de credenciais existe
            $credentialsPath = storage_path('app/google-credentials.json');
            if (!file_exists($credentialsPath)) {
                $this->error('Arquivo de credenciais não encontrado: ' . $credentialsPath);
                return Command::FAILURE;
            }

            // Configurar o cliente Google
            $client = new Client();
            $client->setAuthConfig($credentialsPath);
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');
            $client->addScope(Drive::DRIVE);

            // Verificar se já existe um token
            $tokenPath = storage_path('app/google-drive-token.json');
            if (file_exists($tokenPath)) {
                $accessToken = json_decode(file_get_contents($tokenPath), true);
                $client->setAccessToken($accessToken);

                if ($client->isAccessTokenExpired()) {
                    if ($client->getRefreshToken()) {
                        $this->info('Renovando token existente...');
                        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
                        $this->info('Token renovado com sucesso!');
                        return Command::SUCCESS;
                    } else {
                        $this->warn('Token expirado e sem refresh token. Gerando novo token...');
                    }
                } else {
                    $this->info('Token válido encontrado. Testando conexão...');
                    $service = new Drive($client);
                    try {
                        $service->about->get(['fields' => 'user']);
                        $this->info('Conexão com Google Drive estabelecida com sucesso!');
                        return Command::SUCCESS;
                    } catch (\Exception $e) {
                        $this->error('Erro ao testar conexão: ' . $e->getMessage());
                    }
                }
            }

            // Gerar novo token
            $authUrl = $client->createAuthUrl();
            $this->info('Acesse a URL abaixo para autorizar a aplicação:');
            $this->line($authUrl);
            $this->newLine();

            $authCode = $this->ask('Digite o código de autorização obtido da URL:');

            if (!$authCode) {
                $this->error('Código de autorização é obrigatório.');
                return Command::FAILURE;
            }

            // Trocar o código pelo token
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            if (array_key_exists('error', $accessToken)) {
                $this->error('Erro ao obter token: ' . $accessToken['error_description']);
                return Command::FAILURE;
            }

            // Salvar o token
            file_put_contents($tokenPath, json_encode($accessToken));
            chmod($tokenPath, 0600);

            $this->info('Token gerado e salvo com sucesso!');

            // Testar a conexão
            $client->setAccessToken($accessToken);
            $service = new Drive($client);
            try {
                $about = $service->about->get(['fields' => 'user']);
                $this->info('Conexão estabelecida! Usuário: ' . $about->getUser()->getDisplayName());

                // Verificar se o root folder ID está configurado
                $rootFolderId = config('services.google.root_folder_id');
                if ($rootFolderId) {
                    try {
                        $folder = $service->files->get($rootFolderId);
                        $this->info('Pasta raiz acessível: ' . $folder->getName());
                    } catch (\Exception $e) {
                        $this->warn('Pasta raiz configurada mas não acessível: ' . $e->getMessage());
                    }
                } else {
                    $this->warn('GOOGLE_DRIVE_ROOT_FOLDER_ID não configurado no .env');
                }

                return Command::SUCCESS;
            } catch (\Exception $e) {
                $this->error('Erro ao testar conexão: ' . $e->getMessage());
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('Erro no processo de autenticação: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 