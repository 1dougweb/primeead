<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleDriveService;
use App\Models\GoogleDriveFile;
use Illuminate\Support\Facades\Log;

class SyncGoogleDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:sync {--folder= : ID da pasta específica para sincronizar} {--watch : Monitorar mudanças em tempo real}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza arquivos do Google Drive com o banco de dados';

    protected $driveService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->driveService = app(GoogleDriveService::class);
        
        if ($this->option('watch')) {
            $this->watchForChanges();
        } else {
            $this->syncFiles();
        }
    }

    /**
     * Sincroniza arquivos uma vez
     */
    protected function syncFiles()
    {
        $this->info('Iniciando sincronização do Google Drive...');
        
        try {
            $folderId = $this->option('folder') ?: config('services.google.root_folder_id');
            
            // Listar arquivos do Google Drive
            $driveFiles = $this->driveService->listFiles($folderId);
            
            $this->info('Arquivos encontrados no Google Drive: ' . $driveFiles->count());
            
            // Sincronizar com o banco de dados
            $synced = 0;
            $created = 0;
            $updated = 0;
            
            foreach ($driveFiles as $driveFile) {
                $dbFile = GoogleDriveFile::where('file_id', $driveFile->file_id)->first();
                
                if (!$dbFile) {
                    // Criar novo arquivo no banco
                    GoogleDriveFile::create([
                        'name' => $driveFile->name,
                        'file_id' => $driveFile->file_id,
                        'mime_type' => $driveFile->mime_type ?? 'application/octet-stream',
                        'web_view_link' => $driveFile->web_view_link,
                        'web_content_link' => $driveFile->web_content_link,
                        'thumbnail_link' => $driveFile->thumbnail_link,
                        'size' => $driveFile->size ?? 0,
                        'parent_id' => $driveFile->parent_id,
                        'created_by' => 1, // Usuário padrão
                        'is_folder' => $driveFile->is_folder ? 1 : 0,
                        'is_starred' => $driveFile->is_starred ? 1 : 0,
                        'is_trashed' => $driveFile->is_trashed ? 1 : 0,
                        'is_local' => $driveFile->is_local ? 1 : 0
                    ]);
                    $created++;
                } else {
                    // Verificar se precisa atualizar
                    $needsUpdate = false;
                    $updates = [];
                    
                    if ($dbFile->name !== $driveFile->name) {
                        $updates['name'] = $driveFile->name;
                        $needsUpdate = true;
                    }
                    
                    if ($dbFile->mime_type !== ($driveFile->mime_type ?? 'application/octet-stream')) {
                        $updates['mime_type'] = $driveFile->mime_type ?? 'application/octet-stream';
                        $needsUpdate = true;
                    }
                    
                    if ($dbFile->size !== ($driveFile->size ?? 0)) {
                        $updates['size'] = $driveFile->size ?? 0;
                        $needsUpdate = true;
                    }
                    
                    if ($needsUpdate) {
                        $dbFile->update($updates);
                        $updated++;
                    }
                }
                $synced++;
            }
            
            // Remover arquivos que não existem mais no Google Drive
            $driveFileIds = $driveFiles->pluck('file_id')->toArray();
            $deleted = GoogleDriveFile::whereNotIn('file_id', $driveFileIds)->delete();
            
            $this->info("✅ Sincronização concluída!");
            $this->info("📊 Estatísticas:");
            $this->info("   - Total sincronizado: {$synced}");
            $this->info("   - Novos arquivos: {$created}");
            $this->info("   - Arquivos atualizados: {$updated}");
            $this->info("   - Arquivos removidos: {$deleted}");
            
        } catch (\Exception $e) {
            $this->error('Erro na sincronização: ' . $e->getMessage());
            Log::error('Erro na sincronização do Google Drive: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    /**
     * Monitora mudanças em tempo real
     */
    protected function watchForChanges()
    {
        $this->info('Iniciando monitoramento em tempo real...');
        $this->info('Pressione Ctrl+C para parar');
        
        $lastSync = now();
        
        while (true) {
            try {
                // Verificar mudanças a cada 30 segundos
                sleep(30);
                
                $this->info('Verificando mudanças... (' . now()->format('H:i:s') . ')');
                
                // Fazer sincronização rápida
                $this->syncFiles();
                
                $lastSync = now();
                
            } catch (\Exception $e) {
                $this->error('Erro no monitoramento: ' . $e->getMessage());
                Log::error('Erro no monitoramento do Google Drive: ' . $e->getMessage());
                
                // Aguardar um pouco antes de tentar novamente
                sleep(60);
            }
        }
    }
} 