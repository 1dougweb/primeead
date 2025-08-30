<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleDriveService;
use App\Models\GoogleDriveFile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GoogleDriveSyncController extends Controller
{
    protected $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    /**
     * Sincroniza arquivos do Google Drive
     */
    public function sync(Request $request): JsonResponse
    {
        try {
            $folderId = $request->get('folder_id');
            if (empty($folderId) || $folderId === 'null') {
                $folderId = config('services.google.root_folder_id');
            }
            
            // Listar arquivos do Google Drive
            $driveFiles = $this->driveService->listFiles($folderId);
            
            // Sincronizar com o banco de dados
            $stats = $this->syncFilesWithDatabase($driveFiles, $folderId);
            
            return response()->json([
                'success' => true,
                'message' => 'Sincronização concluída',
                'stats' => $stats,
                'files' => $this->getFilesForFolder($folderId)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro na sincronização: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro na sincronização: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica mudanças em tempo real
     */
    public function checkChanges(Request $request): JsonResponse
    {
        try {
            $folderId = $request->get('folder_id');
            if (empty($folderId) || $folderId === 'null') {
                $folderId = config('services.google.root_folder_id');
            }
            $lastSync = $request->get('last_sync');
            
            // Listar arquivos do Google Drive
            $driveFiles = $this->driveService->listFiles($folderId);
            
            // Verificar se há mudanças
            $hasChanges = $this->checkForChanges($driveFiles, $lastSync);
            
            if ($hasChanges) {
                // Sincronizar mudanças
                $stats = $this->syncFilesWithDatabase($driveFiles, $folderId);
                
                return response()->json([
                    'success' => true,
                    'has_changes' => true,
                    'stats' => $stats,
                    'files' => $this->getFilesForFolder($folderId),
                    'last_sync' => now()->toISOString()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'has_changes' => false,
                'last_sync' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao verificar mudanças: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao verificar mudanças: ' . $e->getMessage()
            ], 500);
        }
    }

        /**
     * Sincroniza arquivos com o banco de dados
     */
    protected function syncFilesWithDatabase($driveFiles, $folderId): array
    {
        $created = 0;
        $updated = 0;
        $deleted = 0;
        
        foreach ($driveFiles as $driveFile) {
            $dbFile = GoogleDriveFile::where('file_id', $driveFile->file_id)->first();
            
            // Log para debug
            \Log::info('Processando arquivo do Google Drive:', [
                'file_id' => $driveFile->file_id,
                'name' => $driveFile->name,
                'parent_id' => $driveFile->parent_id ?? 'null',
                'parents' => $driveFile->parents ?? 'null'
            ]);
            
            // Converter parent_id do Google Drive para ID local
            $localParentId = null;
            if ($driveFile->parent_id) {
                $parentFile = GoogleDriveFile::where('file_id', $driveFile->parent_id)->first();
                $localParentId = $parentFile ? $parentFile->id : null;
            }
            
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
                    'parent_id' => $localParentId, // Usar ID local, não file_id do Google Drive
                    'created_by' => auth()->id() ?? 1, // Garantir que não seja null
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
        }
        
        // Remover arquivos que não existem mais no Google Drive
        $driveFileIds = $driveFiles->pluck('file_id')->toArray();
        $deleted = GoogleDriveFile::whereNotIn('file_id', $driveFileIds)->delete();
        
        return [
            'created' => $created,
            'updated' => $updated,
            'deleted' => $deleted,
            'total' => $driveFiles->count()
        ];
    }

    /**
     * Verifica se há mudanças desde a última sincronização
     */
    protected function checkForChanges($driveFiles, $lastSync): bool
    {
        if (!$lastSync) {
            return true; // Primeira verificação
        }
        
        $lastSyncTime = \Carbon\Carbon::parse($lastSync);
        
        // Verificar se algum arquivo foi modificado recentemente
        foreach ($driveFiles as $file) {
            // Usar modified_time da API do Google Drive se disponível
            if (isset($file->modified_time)) {
                $modifiedTime = \Carbon\Carbon::parse($file->modified_time);
                if ($modifiedTime->gt($lastSyncTime)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Obtém arquivos para uma pasta específica
     */
    protected function getFilesForFolder($folderId): array
    {
        $parentFile = GoogleDriveFile::where('file_id', $folderId)->first();
        $parentId = $parentFile ? $parentFile->id : null;
        
        $files = GoogleDriveFile::where('parent_id', $parentId)
            ->orderBy('name')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->file_id,
                    'name' => $file->name,
                    'mimeType' => $file->mime_type,
                    'size' => $file->size,
                    'webViewLink' => $file->web_view_link,
                    'webContentLink' => $file->web_content_link,
                    'thumbnailLink' => $file->thumbnail_link,
                    'created_at' => $file->created_at,
                    'updated_at' => $file->updated_at
                ];
            })
            ->toArray();
        
        return $files;
    }


} 