<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class TestGoogleDriveAccount extends Command
{
    protected $signature = 'google-drive:test-account';
    protected $description = 'Test which Google account the service account is accessing';

    public function handle()
    {
        $this->info('=== Teste de Conta do Google Drive ===');
        
        try {
            // Inicializar cliente
            $client = new Client();
            $serviceAccountPath = storage_path('app/service-account.json');
            
            if (!file_exists($serviceAccountPath)) {
                $this->error("Service account file not found: {$serviceAccountPath}");
                return 1;
            }
            
            $this->info('✓ Service account file found');
            
            // Configurar client
            $client->setAuthConfig($serviceAccountPath);
            $client->useApplicationDefaultCredentials();
            $client->addScope(Drive::DRIVE);
            
            $service = new Drive($client);
            $this->info('✓ Google Drive service initialized');
            
            // Obter informações da conta
            $this->info("\n--- Informações da Conta ---");
            try {
                $about = $service->about->get(['fields' => 'user']);
                $user = $about->getUser();
                
                if ($user) {
                    $this->info('Email: ' . ($user->getEmailAddress() ?? 'N/A'));
                    $this->info('Nome: ' . ($user->getDisplayName() ?? 'N/A'));
                    $this->info('Foto: ' . ($user->getPhotoLink() ?? 'N/A'));
                } else {
                    $this->info('Informações do usuário não disponíveis (Service Account)');
                }
            } catch (\Exception $e) {
                $this->error('Erro ao obter informações da conta: ' . $e->getMessage());
            }
            
            // Listar arquivos com mais detalhes
            $this->info("\n--- Arquivos na Raiz com Detalhes ---");
            try {
                $results = $service->files->listFiles([
                    'pageSize' => 10,
                    'fields' => 'files(id, name, mimeType, owners, createdTime, modifiedTime)',
                    'q' => 'trashed = false',
                    'orderBy' => 'name'
                ]);
                
                $files = $results->getFiles();
                $this->info('Total de arquivos: ' . count($files));
                
                foreach ($files as $file) {
                    $type = $file->getMimeType() === 'application/vnd.google-apps.folder' ? 'PASTA' : 'ARQUIVO';
                    $this->line("\n📁 {$file->getName()} ({$type})");
                    $this->line("   ID: {$file->getId()}");
                    $this->line("   Criado: {$file->getCreatedTime()}");
                    $this->line("   Modificado: {$file->getModifiedTime()}");
                    
                    $owners = $file->getOwners();
                    if ($owners && count($owners) > 0) {
                        $owner = $owners[0];
                        $this->line("   Proprietário: {$owner->getDisplayName()} ({$owner->getEmailAddress()})");
                    }
                }
                
            } catch (\Exception $e) {
                $this->error('Erro ao listar arquivos: ' . $e->getMessage());
            }
            
            $this->info("\n=== Teste concluído ===");
            
        } catch (\Exception $e) {
            $this->error('Erro geral: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
} 