<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleDriveService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TestGoogleDriveOAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:test-oauth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa upload com OAuth2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testando upload com OAuth2...');
        
        try {
            $driveService = app(GoogleDriveService::class);
            
            // Criar arquivo de teste
            $testContent = 'Teste OAuth2 - ' . date('Y-m-d H:i:s');
            $testFile = storage_path('app/test-oauth.txt');
            file_put_contents($testFile, $testContent);
            
            // Criar UploadedFile
            $uploadedFile = new UploadedFile(
                $testFile,
                'test-oauth.txt',
                'text/plain',
                null,
                true
            );
            
            $this->info('Fazendo upload do arquivo de teste...');
            
            // Fazer upload
            $result = $driveService->uploadFile($uploadedFile);
            
            if ($result) {
                $this->info('✅ Upload realizado com sucesso!');
                $this->info('ID do arquivo: ' . $result['id']);
                $this->info('Nome: ' . $result['name']);
                $this->info('Link: ' . $result['webViewLink']);
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