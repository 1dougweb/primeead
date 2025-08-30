<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleDriveService;

class TestGoogleDriveConnection extends Command
{
    protected $signature = 'google-drive:test';
    protected $description = 'Testar conexão com Google Drive';

    public function handle()
    {
        $this->info('Testando conexão com Google Drive...');

        try {
            // Tentar inicializar o serviço
            $driveService = new GoogleDriveService();
            $this->info('✓ Serviço GoogleDriveService inicializado com sucesso');

            // Verificar se o ID da pasta raiz está configurado
            $rootFolderId = config('services.google.root_folder_id');
            if (!$rootFolderId) {
                $this->warn('⚠ GOOGLE_DRIVE_ROOT_FOLDER_ID não configurado no .env');
                return Command::FAILURE;
            }

            $this->info("✓ Root Folder ID configurado: {$rootFolderId}");

            // Tentar listar arquivos da pasta raiz
            $this->info('Testando listagem de arquivos...');
            $files = $driveService->listFiles();
            
            $this->info("✓ Conexão estabelecida! Encontrados " . $files->count() . " arquivos/pastas");

            if ($files->count() > 0) {
                $this->info('Primeiros arquivos encontrados:');
                foreach ($files->take(5) as $file) {
                    $type = $file->is_folder ? '[PASTA]' : '[ARQUIVO]';
                    $this->line("  {$type} {$file->name}");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('✗ Erro ao conectar com Google Drive: ' . $e->getMessage());
            
            // Informações para debug
            $this->newLine();
            $this->info('Informações para debug:');
            $this->line('- Verifique se o arquivo storage/app/google-credentials.json existe');
            $this->line('- Verifique se a variável GOOGLE_DRIVE_ROOT_FOLDER_ID está configurada no .env');
            $this->line('- Verifique se a Service Account tem acesso à pasta do Google Drive');
            
            return Command::FAILURE;
        }
    }
} 