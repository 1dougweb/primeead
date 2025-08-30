<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleDriveService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TestGoogleDriveAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:test-access';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o acesso da Service Account ao Shared Drive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testando acesso da Service Account ao Google Drive...');
        
        try {
            $driveService = app(GoogleDriveService::class);
            
            // Teste 1: Listar arquivos
            $this->info('1. Testando listagem de arquivos...');
            $files = $driveService->listFiles();
            $this->info("   ✓ Listagem OK. Encontrados {$files->count()} arquivos.");
            
            // Teste 2: Criar pasta de teste
            $this->info('2. Testando criação de pasta...');
            $testFolderName = 'teste_' . date('Y-m-d_H-i-s');
            $folder = $driveService->createFolder($testFolderName, 1, null);
            $this->info("   ✓ Pasta criada: {$folder->name} (ID: {$folder->file_id})");
            
            // Teste 3: Criar arquivo de teste
            $this->info('3. Testando upload de arquivo...');
            $testContent = 'Teste de upload - ' . date('Y-m-d H:i:s');
            $tempFile = tempnam(sys_get_temp_dir(), 'test_');
            file_put_contents($tempFile, $testContent);
            
            $uploadedFile = new UploadedFile(
                $tempFile,
                'teste.txt',
                'text/plain',
                null,
                true
            );
            
            $file = $driveService->uploadFile($uploadedFile, 1, $folder->id);
            $this->info("   ✓ Arquivo enviado: {$file->name} (ID: {$file->file_id})");
            
            // Teste 4: Deletar arquivo de teste
            $this->info('4. Testando exclusão de arquivo...');
            $deleted = $driveService->delete($file->file_id);
            if ($deleted) {
                $this->info("   ✓ Arquivo deletado com sucesso.");
            } else {
                $this->warn("   ⚠ Arquivo não foi deletado.");
            }
            
            // Teste 5: Deletar pasta de teste
            $this->info('5. Testando exclusão de pasta...');
            $deleted = $driveService->delete($folder->file_id);
            if ($deleted) {
                $this->info("   ✓ Pasta deletada com sucesso.");
            } else {
                $this->warn("   ⚠ Pasta não foi deletada.");
            }
            
            // Limpar arquivo temporário
            unlink($tempFile);
            
            $this->info('✅ Todos os testes passaram! A Service Account tem acesso completo ao Shared Drive.');
            
        } catch (\Exception $e) {
            $this->error('❌ Erro no teste: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
} 