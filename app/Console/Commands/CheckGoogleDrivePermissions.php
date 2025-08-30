<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class CheckGoogleDrivePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:check-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica as permissões da Service Account no Google Drive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando permissões da Service Account...');
        
        try {
            $client = new Client();
            $client->setAuthConfig(storage_path('app/google-credentials.json'));
            $client->addScope(Drive::DRIVE);
            $client->useApplicationDefaultCredentials();
            
            $service = new Drive($client);
            
            // Pegar o ID da pasta raiz
            $rootFolderId = config('services.google.root_folder_id');
            $this->info("ID da pasta raiz: {$rootFolderId}");
            
            // Verificar se a pasta existe e obter informações
            try {
                $file = $service->files->get($rootFolderId, [
                    'fields' => 'id, name, mimeType, parents, permissions, owners'
                ]);
                
                $this->info("Nome da pasta: {$file->getName()}");
                $this->info("Tipo MIME: {$file->getMimeType()}");
                
                // Verificar se é um Shared Drive
                if ($file->getMimeType() === 'application/vnd.google-apps.folder') {
                    $this->info("✓ É uma pasta");
                    
                    // Verificar se tem parents (se não tem, é a raiz)
                    $parents = $file->getParents();
                    if (empty($parents)) {
                        $this->info("✓ É uma pasta raiz (não tem parents)");
                    } else {
                        $this->info("⚠ Tem parents: " . implode(', ', $parents));
                    }
                    
                    // Verificar permissões
                    $permissions = $file->getPermissions();
                    if ($permissions) {
                        $this->info("Permissões encontradas:");
                        foreach ($permissions as $permission) {
                            $this->info("  - {$permission->getEmailAddress()} ({$permission->getRole()})");
                        }
                    } else {
                        $this->warn("⚠ Nenhuma permissão encontrada");
                    }
                    
                    // Verificar proprietários
                    $owners = $file->getOwners();
                    if ($owners) {
                        $this->info("Proprietários:");
                        foreach ($owners as $owner) {
                            $this->info("  - {$owner->getEmailAddress()}");
                        }
                    }
                    
                } else {
                    $this->error("❌ Não é uma pasta");
                }
                
            } catch (\Exception $e) {
                $this->error("❌ Erro ao obter informações da pasta: " . $e->getMessage());
            }
            
            // Tentar listar arquivos na pasta
            $this->info("\nTentando listar arquivos na pasta...");
            try {
                $results = $service->files->listFiles([
                    'q' => "'{$rootFolderId}' in parents and trashed = false",
                    'fields' => 'files(id, name, mimeType)',
                    'pageSize' => 10
                ]);
                
                $files = $results->getFiles();
                $this->info("✓ Listagem OK. Encontrados " . count($files) . " arquivos.");
                
                foreach ($files as $file) {
                    $this->info("  - {$file->getName()} ({$file->getMimeType()})");
                }
                
            } catch (\Exception $e) {
                $this->error("❌ Erro ao listar arquivos: " . $e->getMessage());
            }
            
            // Verificar se é um Shared Drive
            $this->info("\nVerificando se é um Shared Drive...");
            try {
                $about = $service->about->get([
                    'fields' => 'user, storageQuota'
                ]);
                
                $this->info("Usuário autenticado: " . $about->getUser()->getEmailAddress());
                
                $storageQuota = $about->getStorageQuota();
                if ($storageQuota) {
                    $this->info("Quota total: " . $storageQuota->getLimit());
                    $this->info("Quota usada: " . $storageQuota->getUsage());
                    $this->info("Quota disponível: " . ($storageQuota->getLimit() - $storageQuota->getUsage()));
                }
                
            } catch (\Exception $e) {
                $this->error("❌ Erro ao obter informações do usuário: " . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Erro geral: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 