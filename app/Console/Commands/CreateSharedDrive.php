<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class CreateSharedDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:create-shared-drive {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um Shared Drive usando Service Account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name') ?: 'Ensino Certo - Documentos';
        
        $this->info('Criando Shared Drive: ' . $name);
        
        try {
            $client = new Client();
            $client->setAuthConfig(storage_path('app/google-credentials.json'));
            $client->setScopes([Drive::DRIVE]);
            
            $service = new Drive($client);
            
            // Criar Shared Drive
            $driveMetadata = new DriveFile([
                'name' => $name,
                'capabilities' => [
                    'canAddChildren' => true,
                    'canComment' => true,
                    'canCopy' => true,
                    'canDelete' => true,
                    'canDownload' => true,
                    'canEdit' => true,
                    'canListChildren' => true,
                    'canManageMembers' => true,
                    'canReadRevisions' => true,
                    'canRename' => true,
                    'canShare' => true,
                    'canTrashChildren' => true
                ]
            ]);
            
            $this->info('Criando Shared Drive...');
            $drive = $service->drives->create($driveMetadata);
            
            $this->info('✅ Shared Drive criado com sucesso!');
            $this->info('ID: ' . $drive->getId());
            $this->info('Nome: ' . $drive->getName());
            $this->info('Criado em: ' . $drive->getCreatedTime());
            
            // Adicionar usuário como membro
            $this->info('Adicionando usuário como membro...');
            
            $permission = new \Google\Service\Drive\Permission([
                'type' => 'user',
                'role' => 'writer',
                'emailAddress' => 'ensinocertodocumentos@gmail.com'
            ]);
            
            $service->permissions->create($drive->getId(), $permission, [
                'supportsAllDrives' => true
            ]);
            
            $this->info('✅ Usuário adicionado como membro');
            
            // Atualizar .env
            $this->info('Atualizando .env...');
            $envPath = base_path('.env');
            $envContent = file_get_contents($envPath);
            
            $envContent = preg_replace(
                '/GOOGLE_DRIVE_ROOT_FOLDER_ID=.*/',
                'GOOGLE_DRIVE_ROOT_FOLDER_ID=' . $drive->getId(),
                $envContent
            );
            
            file_put_contents($envPath, $envContent);
            
            $this->info('✅ .env atualizado');
            $this->info('🔄 Limpando cache...');
            
            $this->call('config:clear');
            
            $this->info('🎉 Shared Drive configurado com sucesso!');
            $this->info('Agora você pode fazer upload de arquivos normalmente.');
            
        } catch (\Exception $e) {
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 