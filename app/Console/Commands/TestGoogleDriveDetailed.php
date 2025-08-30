<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;

class TestGoogleDriveDetailed extends Command
{
    protected $signature = 'google-drive:test-detailed';
    protected $description = 'Test Google Drive connection with detailed debugging';

    public function handle()
    {
        $this->info('=== Teste Detalhado do Google Drive ===');
        
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
            
            // Teste 1: Listar arquivos da raiz (My Drive)
            $this->info("\n--- Teste 1: Listando arquivos da raiz ---");
            try {
                $results = $service->files->listFiles([
                    'pageSize' => 10,
                    'fields' => 'files(id, name, mimeType, parents)',
                    'q' => 'trashed = false'
                ]);
                
                $files = $results->getFiles();
                $this->info('Arquivos encontrados na raiz: ' . count($files));
                
                foreach ($files as $file) {
                    $type = $file->getMimeType() === 'application/vnd.google-apps.folder' ? 'PASTA' : 'ARQUIVO';
                    $this->line("- {$file->getName()} ({$type}) - ID: {$file->getId()}");
                }
                
            } catch (\Exception $e) {
                $this->error('Erro ao listar raiz: ' . $e->getMessage());
            }
            
            // Teste 2: Listar arquivos com supportsAllDrives
            $this->info("\n--- Teste 2: Listando com supportsAllDrives ---");
            try {
                $results = $service->files->listFiles([
                    'pageSize' => 10,
                    'fields' => 'files(id, name, mimeType, parents)',
                    'q' => 'trashed = false',
                    'supportsAllDrives' => true,
                    'includeItemsFromAllDrives' => true
                ]);
                
                $files = $results->getFiles();
                $this->info('Arquivos encontrados com supportsAllDrives: ' . count($files));
                
                foreach ($files as $file) {
                    $type = $file->getMimeType() === 'application/vnd.google-apps.folder' ? 'PASTA' : 'ARQUIVO';
                    $this->line("- {$file->getName()} ({$type}) - ID: {$file->getId()}");
                }
                
            } catch (\Exception $e) {
                $this->error('Erro ao listar com supportsAllDrives: ' . $e->getMessage());
            }
            
            // Teste 3: Tentar acessar pasta específica
            $folderId = config('services.google.root_folder_id');
            $this->info("\n--- Teste 3: Tentando acessar pasta específica ---");
            $this->info("Folder ID configurado: {$folderId}");
            
            try {
                // Primeiro, tentar obter informações da pasta
                $folder = $service->files->get($folderId, [
                    'fields' => 'id, name, mimeType, parents',
                    'supportsAllDrives' => true
                ]);
                
                $this->info("✓ Pasta encontrada: {$folder->getName()}");
                $this->info("Tipo: {$folder->getMimeType()}");
                
                // Agora listar conteúdo da pasta
                $results = $service->files->listFiles([
                    'pageSize' => 10,
                    'fields' => 'files(id, name, mimeType, parents)',
                    'q' => "'{$folderId}' in parents and trashed = false",
                    'supportsAllDrives' => true,
                    'includeItemsFromAllDrives' => true
                ]);
                
                $files = $results->getFiles();
                $this->info("Arquivos na pasta específica: " . count($files));
                
                foreach ($files as $file) {
                    $type = $file->getMimeType() === 'application/vnd.google-apps.folder' ? 'PASTA' : 'ARQUIVO';
                    $this->line("- {$file->getName()} ({$type}) - ID: {$file->getId()}");
                }
                
            } catch (\Exception $e) {
                $this->error('Erro ao acessar pasta específica: ' . $e->getMessage());
            }
            
            // Teste 4: Listar shared drives
            $this->info("\n--- Teste 4: Listando Shared Drives ---");
            try {
                $results = $service->drives->listDrives([
                    'pageSize' => 10
                ]);
                
                $drives = $results->getDrives();
                $this->info('Shared Drives encontrados: ' . count($drives));
                
                foreach ($drives as $drive) {
                    $this->line("- {$drive->getName()} - ID: {$drive->getId()}");
                }
                
            } catch (\Exception $e) {
                $this->error('Erro ao listar shared drives: ' . $e->getMessage());
            }
            
            $this->info("\n=== Teste concluído ===");
            
        } catch (\Exception $e) {
            $this->error('Erro geral: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
} 