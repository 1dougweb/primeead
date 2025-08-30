<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleDriveService;
use Illuminate\Http\UploadedFile;

class TestServiceAccountUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:test-service-account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa upload com Service Account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testando upload com Service Account...');
        
        try {
            $driveService = app(GoogleDriveService::class);
            
            // Criar arquivo de teste
            $testContent = 'Teste Service Account - ' . date('Y-m-d H:i:s');
            $testFile = storage_path('app/test-service-account.txt');
            file_put_contents($testFile, $testContent);
            
            // Criar UploadedFile
            $uploadedFile = new UploadedFile(
                $testFile,
                'test-service-account.txt',
                'text/plain',
                null,
                true
            );
            
            $this->info('Fazendo upload do arquivo de teste...');
            
            // Fazer upload (usando ID 1 como usuário padrão)
            $result = $driveService->uploadFile($uploadedFile, 1, null);
            
            if ($result) {
                $this->info('✅ Upload realizado com sucesso!');
                $this->info('ID do arquivo: ' . $result->file_id);
                $this->info('Nome: ' . $result->name);
                $this->info('Link: ' . $result->web_view_link);
            } else {
                $this->error('❌ Falha no upload');
            }
            
            // Limpar arquivo de teste
            unlink($testFile);
            
        } catch (\Exception $e) {
            $this->error('Erro: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 