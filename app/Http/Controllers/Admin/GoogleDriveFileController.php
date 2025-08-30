<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleDriveFile;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleDriveFileController extends Controller
{
    protected $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->middleware('auth');
        $this->middleware('permission:google-drive.index'); // Habilitar verificação de permissão
        $this->driveService = $driveService;
    }   

    /**
     * Exibir lista de arquivos
     */
    public function index(Request $request)
    {
        try {
            \Log::info('GoogleDriveFileController::index - REQUISIÇÃO RECEBIDA', [
                'method' => $request->method(),
                'url' => $request->url(),
                'folder' => $request->get('folder'),
                'search' => $request->get('search'),
                'ajax' => $request->ajax(),
                'user_id' => auth()->id()
            ]);
            $parentId = $request->get('folder');
            $search = $request->get('search');
            
            \Log::info('Parâmetros da requisição:', ['folder' => $parentId, 'search' => $search]);
            
            // Obter o ID da pasta raiz
            $rootFolderId = config('services.google.root_folder_id');
            if (empty($rootFolderId)) {
                throw new \Exception("ID da pasta raiz do Google Drive não configurado");
            }
            
            // Carregar todas as pastas para o modal de mover
            $folders = GoogleDriveFile::where('is_folder', true)
                        ->where('is_trashed', false)
                        ->get();
            
            \Log::info('Total de pastas encontradas no banco de dados: ' . $folders->count());
            
            // Converter parentId local para file_id do Google Drive se necessário
            $googleDriveParentId = $parentId;
            \Log::info('GoogleDriveFileController::index - Iniciando conversão de ID', [
                'parentId' => $parentId,
                'parentId_type' => gettype($parentId)
            ]);
            
            if ($parentId) {
                $folder = GoogleDriveFile::find($parentId);
                \Log::info('GoogleDriveFileController::index - Buscando pasta no banco', [
                    'parentId' => $parentId,
                    'folder_found' => $folder ? 'sim' : 'não',
                    'folder_file_id' => $folder ? $folder->file_id : 'n/a'
                ]);
                
                if ($folder && $folder->file_id) {
                    $googleDriveParentId = $folder->file_id;
                    \Log::info('GoogleDriveFileController::index - Convertendo ID local para file_id', [
                        'local_id' => $parentId,
                        'google_drive_id' => $googleDriveParentId
                    ]);
                } else {
                    \Log::warning('GoogleDriveFileController::index - Pasta não encontrada ou sem file_id', [
                        'parentId' => $parentId
                    ]);
                }
            }
            
            // Verificar se o serviço está configurado
            try {
                // Primeiro, sincronizar com o Google Drive para garantir dados atualizados
                try {
                    \Log::info('Sincronizando arquivos do Google Drive');
                    $driveFiles = $this->driveService->listFiles($googleDriveParentId, $search);
                    \Log::info('Arquivos sincronizados com o Google Drive: ' . $driveFiles->count());
                } catch (\Exception $e) {
                    \Log::warning('Erro ao sincronizar com Google Drive: ' . $e->getMessage());
                    $driveFiles = collect([]);
                }
                
                // Usar diretamente os dados da API do Google Drive
                if ($parentId) {
                    // Se uma pasta específica foi solicitada, buscar arquivos dessa pasta
                    \Log::info('Buscando arquivos da pasta: ' . $parentId);
                    \Log::info('Google Drive Parent ID usado: ' . $googleDriveParentId);
                    
                    // Usar os dados da API do Google Drive diretamente
                    $files = $driveFiles;
                    
                    // Encontrar a pasta atual para navegação
                    $currentFolder = GoogleDriveFile::where('id', $parentId)
                        ->orWhere('file_id', $parentId)
                        ->first();
                    
                    \Log::info('Total de arquivos encontrados na pasta via API: ' . $files->count());
                    \Log::info('Arquivos encontrados:', [
                        'count' => $files->count(),
                        'sample_files' => $files->take(3)->map(function($file) {
                            return [
                                'id' => $file->id ?? $file->file_id,
                                'name' => $file->name,
                                'mime_type' => $file->mime_type
                            ];
                        })->toArray()
                    ]);
                } else {
                    // Se nenhuma pasta foi especificada, usar os dados da API do Google Drive
                    \Log::info('Buscando arquivos da pasta raiz via API');
                    
                    // Usar os dados da API do Google Drive diretamente
                    $files = $driveFiles;
                    $currentFolder = null; // Não mostramos a pasta raiz como pasta atual
                    
                    \Log::info('Total de arquivos encontrados na raiz via API: ' . $files->count());
                }
                
                // Garantir que a pasta raiz não apareça na listagem
                $files = $files->filter(function($file) use ($rootFolderId) {
                    return $file->file_id !== $rootFolderId;
                });
                
                // Adicionar informações de ancestrais para navegação
                if ($currentFolder) {
                    $ancestors = collect();
                    $parent = $currentFolder;
                    
                    while ($parent && $parent->parent_id) {
                        $parent = GoogleDriveFile::find($parent->parent_id);
                        if ($parent) {
                            // Não adicionar a pasta raiz aos ancestrais
                            if ($parent->file_id === $rootFolderId) {
                                continue;
                            }
                            $ancestors->prepend($parent);
                        }
                    }
                    
                    $currentFolder->ancestors = $ancestors;
                }
                
                $configError = null;
            } catch (\Exception $e) {
                \Log::error('Erro ao listar arquivos do Google Drive: ' . $e->getMessage());
                $files = collect([]);
                $currentFolder = null;
                $configError = $e->getMessage();
            }
            
            if ($request->ajax()) {
                try {
                    // Preparar breadcrumb para navegação
                    $breadcrumb = collect();
                    if ($currentFolder) {
                        \Log::info('Current folder found:', ['id' => $currentFolder->id, 'name' => $currentFolder->name]);
                        
                        if (isset($currentFolder->ancestors)) {
                            $breadcrumb = $currentFolder->ancestors;
                            \Log::info('Breadcrumb prepared:', ['count' => $breadcrumb->count()]);
                        } else {
                            \Log::warning('Ancestors property not found on current folder');
                        }
                    } else {
                        \Log::info('No current folder set');
                    }
                    
                    \Log::info('Preparing AJAX response', [
                        'files_count' => $files->count(),
                        'breadcrumb_count' => $breadcrumb->count(),
                        'has_current_folder' => $currentFolder ? true : false
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'files' => $files,
                        'currentFolder' => $currentFolder,
                        'folders' => $folders,
                        'breadcrumb' => $breadcrumb,
                        'configError' => $configError
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error preparing AJAX response: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'error' => 'Erro interno do servidor: ' . $e->getMessage()
                    ], 500);
                }
            }

            $canCreate = auth()->check();
            
            \Log::info('GoogleDriveFileController::index - Passando dados para view', [
                'current_folder' => $currentFolder ? [
                    'id' => $currentFolder->id,
                    'name' => $currentFolder->name,
                    'file_id' => $currentFolder->file_id
                ] : null,
                'files_count' => $files->count(),
                'folders_count' => $folders->count()
            ]);
            
            return view('admin.arquivos.index', compact('files', 'currentFolder', 'folders', 'configError', 'canCreate'));
        } catch (\Exception $e) {
            \Log::error('Erro geral ao processar a requisição: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }
            
            $canCreate = auth()->check();
            return view('admin.arquivos.index', [
                'files' => collect([]),
                'currentFolder' => null,
                'folders' => collect([]),
                'configError' => $e->getMessage(),
                'canCreate' => $canCreate
            ]);
        }
    }

    /**
     * Upload de arquivo
     */
    public function store(Request $request)
    {
        \Log::info('GoogleDriveFileController::store - Iniciando upload de arquivo');
        
        try {
            $request->validate([
                'file' => 'required|file|max:102400', // max 100MB
                'folder_id' => 'nullable'
            ]);

            // Validar folder_id manualmente
            $validFolderId = null;
            if ($request->folder_id && !empty(trim($request->folder_id))) {
                \Log::info('GoogleDriveFileController::store - Validando folder_id', [
                    'folder_id' => $request->folder_id,
                    'folder_id_type' => gettype($request->folder_id),
                    'folder_id_trimmed' => trim($request->folder_id)
                ]);
                
                // Tentar encontrar por ID local primeiro
                $folder = GoogleDriveFile::where('id', $request->folder_id)
                    ->where('is_folder', true)
                    ->first();
                
                if (!$folder) {
                    // Se não encontrar por ID local, tentar por file_id do Google Drive
                    $folder = GoogleDriveFile::where('file_id', $request->folder_id)
                        ->where('is_folder', true)
                        ->first();
                }
                
                if ($folder) {
                    $validFolderId = $folder->id; // Usar sempre o ID local
                    \Log::info('GoogleDriveFileController::store - Pasta encontrada', [
                        'original_id' => $request->folder_id,
                        'valid_id' => $validFolderId,
                        'folder_name' => $folder->name
                    ]);
                } else {
                    \Log::warning('GoogleDriveFileController::store - Pasta não encontrada', [
                        'folder_id' => $request->folder_id
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Pasta de destino não encontrada'
                    ], 400);
                }
            } else {
                \Log::info('GoogleDriveFileController::store - Nenhum folder_id fornecido ou vazio');
            }

            \Log::info('GoogleDriveFileController::store - Validação passou', [
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_size' => $request->file('file')->getSize(),
                'folder_id' => $request->folder_id,
                'valid_folder_id' => $validFolderId,
                'folder_id_type' => gettype($request->folder_id),
                'folder_id_empty' => empty($request->folder_id),
                'all_request_data' => $request->all()
            ]);

            // Check file size before upload
            $fileSize = $request->file('file')->getSize();
            if ($fileSize > 100 * 1024 * 1024) { // 100MB limit
                \Log::warning('GoogleDriveFileController::store - Arquivo muito grande', ['size' => $fileSize]);
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo muito grande. Tamanho máximo permitido: 100MB'
                ], 400);
            }

            \Log::info('GoogleDriveFileController::store - Chamando driveService->uploadFile');
            $file = $this->driveService->uploadFile(
                $request->file('file'),
                Auth::id(),
                $validFolderId
            );

            \Log::info('GoogleDriveFileController::store - Upload concluído com sucesso', [
                'file_id' => $file->id,
                'file_name' => $file->name,
                'parent_id' => $file->parent_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Arquivo enviado com sucesso!',
                'file' => $file
            ]);
        } catch (\Exception $e) {
            \Log::error('GoogleDriveFileController::store - Erro no upload', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar pasta
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:google_drive_files,id'
        ]);

        try {
            // Converter o parent_id local para o file_id do Google Drive
            $googleDriveParentId = null;
            if ($request->parent_id) {
                $parentFile = GoogleDriveFile::find($request->parent_id);
                if ($parentFile) {
                    $googleDriveParentId = $parentFile->file_id;
                }
            }

            $folder = $this->driveService->createFolder(
                $request->name,
                Auth::id(),
                $googleDriveParentId
            );

            return response()->json([
                'success' => true,
                'message' => 'Pasta criada com sucesso!',
                'folder' => $folder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar pasta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download de arquivo
     */
    public function download($id)
    {
        try {
            // Verificar se o ID parece ser um ID do Google Drive (contém letras)
            if (preg_match('/[a-zA-Z]/', $id)) {
                // Buscar pelo campo file_id
                $file = GoogleDriveFile::where('file_id', $id)->first();
                
                // Se não encontrar, tentar criar um registro local
                if (!$file) {
                    $fileInfo = $this->driveService->getFileInfo($id);
                    
                    if ($fileInfo) {
                        // Criar um registro local para o arquivo
                        $file = GoogleDriveFile::create([
                            'file_id' => $id,
                            'name' => $fileInfo->getName(),
                            'mime_type' => $fileInfo->getMimeType() ?? null,
                            'size' => $fileInfo->getSize() ?? null,
                            'web_content_link' => $fileInfo->getWebContentLink() ?? null,
                            'is_folder' => ($fileInfo->getMimeType() ?? '') === 'application/vnd.google-apps.folder',
                            'parent_id' => null,
                            'created_by' => auth()->id(),
                            'is_trashed' => false,
                        ]);
                    } else {
                        return back()->with('error', 'Erro ao baixar arquivo: Arquivo não encontrado no Google Drive');
                    }
                }
            } else {
                // Buscar pelo ID local
                $file = GoogleDriveFile::findOrFail($id);
            }
            
            // If it's a folder, redirect to the folder view
            if ($file->is_folder) {
                return redirect()->route('admin.files.index', ['folder' => $file->id]);
            }
            
            // Download do arquivo
            $download = $this->driveService->download($file->file_id);
            
            if (!$download) {
                return back()->with('error', 'Erro ao baixar arquivo');
            }
            
            return response($download['content'])
                ->header('Content-Type', $download['mime_type'])
                ->header('Content-Disposition', 'attachment; filename="' . $download['filename'] . '"');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao baixar arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Excluir arquivo/pasta
     */
    public function destroy(Request $request, $id)
    {
        \Log::info('GoogleDriveFileController::destroy - Início', [
            'id' => $id,
            'recursive' => $request->get('recursive', false)
        ]);
        
        try {
            $recursive = $request->get('recursive', false);
            
            \Log::info('Iniciando exclusão de arquivo', [
                'id' => $id,
                'id_type' => is_numeric($id) ? 'numeric' : 'string',
                'id_length' => strlen($id),
                'recursive' => $recursive
            ]);
            
            // Verificar se o ID é um ID do Google Drive (contém letras)
            if (preg_match('/[a-zA-Z]/', $id)) {
                \Log::info('ID é um file_id do Google Drive, buscando no banco de dados');
                $file = GoogleDriveFile::withTrashed()->where('file_id', $id)->first();
                
                if (!$file) {
                    \Log::warning('Arquivo não encontrado no banco de dados pelo file_id', ['file_id' => $id]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Arquivo não encontrado no banco de dados'
                    ], 404);
                }
                
                // Se o arquivo foi soft deleted, restaurá-lo primeiro
                if ($file->deleted_at) {
                    \Log::info('Arquivo estava soft deleted, restaurando', ['id' => $file->id]);
                    $file->restore();
                }
            } else {
                // Se é um ID numérico, usar findOrFail
                $file = GoogleDriveFile::findOrFail($id);
            }
            
            \Log::info('Arquivo encontrado no banco de dados', [
                'id' => $file->id,
                'file_id' => $file->file_id,
                'name' => $file->name,
                'is_folder' => $file->is_folder
            ]);
            
            try {
                // Excluir no Google Drive (com opção recursiva)
                $result = $this->driveService->delete($file->file_id, $recursive);
                
                if ($result) {
                    // Excluir do banco de dados local
                    $file->delete();
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Arquivo excluído com sucesso!'
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao excluir arquivo'
                ], 500);
            } catch (\Google\Service\Exception $e) {
                $errorCode = $e->getCode();
                $errorMessage = $e->getMessage();
                
                \Log::error('GoogleDriveFileController::destroy - Erro da API do Google Drive', [
                    'file_id' => $file->file_id,
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'errors' => $e->getErrors()
                ]);
                
                // Tratar especificamente o erro 403 (permissões insuficientes)
                if ($errorCode === 403) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você não tem permissão para excluir este arquivo/pasta.',
                        'code' => $errorCode
                    ], 403);
                }
                
                // Para outros erros da API do Google
                return response()->json([
                    'success' => false,
                    'message' => 'Erro do Google Drive: ' . $errorMessage,
                    'code' => $errorCode
                ], 500);
            } catch (\Exception $e) {
                \Log::error('GoogleDriveFileController::destroy - Erro geral ao excluir arquivo', [
                    'file_id' => $file->file_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir arquivo (catch externo)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro externo: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Excluir arquivo ou pasta (recursivamente para pastas)
     */
    public function destroyRecursive(Request $request, $id)
    {
        \Log::info('GoogleDriveFileController::destroyRecursive - Início', ['id' => $id]);
        
        try {
            // Verificar se o ID é um ID do Google Drive (contém letras)
            if (preg_match('/[a-zA-Z]/', $id)) {
                $file = GoogleDriveFile::withTrashed()->where('file_id', $id)->first();
                
                if (!$file) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Arquivo não encontrado no banco de dados'
                    ], 404);
                }
                
                if ($file->deleted_at) {
                    $file->restore();
                }
            } else {
                $file = GoogleDriveFile::findOrFail($id);
            }
            
            \Log::info('Item encontrado para exclusão', [
                'id' => $file->id,
                'file_id' => $file->file_id,
                'name' => $file->name,
                'is_folder' => $file->is_folder
            ]);
            
            // Para arquivos, usar exclusão simples; para pastas, usar exclusão recursiva
            if ($file->is_folder) {
                // É uma pasta - usar exclusão recursiva
                try {
                    $result = $this->driveService->deleteRecursive($file->file_id);
                    
                    if ($result) {
                        // Excluir do banco de dados local
                        $file->delete();
                        
                        return response()->json([
                            'success' => true,
                            'message' => 'Pasta e todo seu conteúdo foram excluídos com sucesso!'
                        ]);
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao excluir pasta recursivamente'
                    ], 500);
                    
                } catch (\Exception $e) {
                    \Log::error('GoogleDriveFileController::destroyRecursive - Erro ao excluir pasta recursivamente', [
                        'file_id' => $file->file_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 500);
                }
            } else {
                // É um arquivo - usar exclusão simples
                try {
                    $result = $this->driveService->delete($file->file_id);
                    
                    if ($result) {
                        // Excluir do banco de dados local
                        $file->delete();
                        
                        return response()->json([
                            'success' => true,
                            'message' => 'Arquivo excluído com sucesso!'
                        ]);
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao excluir arquivo'
                    ], 500);
                    
                } catch (\Exception $e) {
                    \Log::error('GoogleDriveFileController::destroyRecursive - Erro ao excluir arquivo', [
                        'file_id' => $file->file_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 500);
                }
            }
            

            
        } catch (\Exception $e) {
            \Log::error('GoogleDriveFileController::destroyRecursive - Erro externo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro externo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronização completa de todos os arquivos do Google Drive
     */
    public function syncAll(Request $request)
    {
        try {
            \Log::info('GoogleDriveFileController::syncAll - Iniciando sincronização completa');
            
            $maxDepth = $request->get('max_depth', 10);
            $folderId = $request->get('folder_id');
            
            \Log::info('GoogleDriveFileController::syncAll - Parâmetros', [
                'maxDepth' => $maxDepth,
                'folderId' => $folderId
            ]);
            
            // Executar sincronização recursiva
            $allFiles = $this->driveService->syncAllFiles($folderId, $maxDepth);
            
            \Log::info('GoogleDriveFileController::syncAll - Sincronização concluída', [
                'totalFiles' => $allFiles->count()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Sincronização completa realizada com sucesso!',
                'total_files' => $allFiles->count(),
                'files' => $allFiles
            ]);
            
        } catch (\Exception $e) {
            \Log::error('GoogleDriveFileController::syncAll - Erro na sincronização: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro na sincronização: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mover para lixeira
     */
    public function trash($id)
    {
        try {
            $file = GoogleDriveFile::findOrFail($id);
            
            if ($this->driveService->trash($file->file_id)) {
                $file->update(['is_trashed' => true]);
                return response()->json([
                    'success' => true,
                    'message' => 'Arquivo movido para lixeira!'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao mover arquivo para lixeira'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao mover arquivo para lixeira: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar da lixeira
     */
    public function restore($id)
    {
        try {
            $file = GoogleDriveFile::findOrFail($id);
            
            if ($this->driveService->untrash($file->file_id)) {
                $file->update(['is_trashed' => false]);
                return response()->json([
                    'success' => true,
                    'message' => 'Arquivo restaurado com sucesso!'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar arquivo'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renomear arquivo/pasta
     */
    public function rename(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $file = GoogleDriveFile::findOrFail($id);

        if ($this->driveService->rename($file->file_id, $request->name)) {
            $file->update(['name' => $request->name]);
            return response()->json([
                'success' => true,
                'message' => 'Arquivo renomeado com sucesso!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao renomear arquivo'
        ], 500);
    }

    /**
     * Mover arquivo/pasta
     */
    public function move(Request $request, $id)
    {
        \Log::info('GoogleDriveFileController::move - Iniciando movimentação', [
            'id' => $id,
            'request_data' => $request->all(),
            'folder_id' => $request->folder_id,
            'folder_id_type' => gettype($request->folder_id),
            'folder_id_empty' => empty($request->folder_id)
        ]);
        
        try {
            // Normalizar folder_id antes da validação
            $folderId = $request->folder_id;
            if ($folderId === '' || $folderId === 'null' || $folderId === null || $folderId === 'undefined') {
                $folderId = null;
            }
            
            // Se for uma string vazia após trim, também considerar null
            if (is_string($folderId) && empty(trim($folderId))) {
                $folderId = null;
            }
            
            $request->merge(['folder_id' => $folderId]);
            
            \Log::info('GoogleDriveFileController::move - Folder ID normalizado', [
                'original_folder_id' => $request->folder_id,
                'normalized_folder_id' => $folderId
            ]);
            
            // Se folder_id não for null, verificar se existe no banco de dados
            if ($folderId !== null) {
                \Log::info('GoogleDriveFileController::move - Buscando pasta de destino', [
                    'folder_id' => $folderId,
                    'folder_id_type' => gettype($folderId),
                    'folder_id_trimmed' => is_string($folderId) ? trim($folderId) : $folderId
                ]);
                
                // Primeiro tentar buscar pelo ID local
                $targetFolder = GoogleDriveFile::where('id', $folderId)
                    ->where('is_folder', true)
                    ->first();
                
                if ($targetFolder) {
                    \Log::info('GoogleDriveFileController::move - Pasta encontrada pelo ID local', [
                        'id' => $targetFolder->id,
                        'name' => $targetFolder->name,
                        'file_id' => $targetFolder->file_id
                    ]);
                } else {
                    \Log::info('GoogleDriveFileController::move - Pasta não encontrada pelo ID local, tentando pelo file_id');
                    
                    // Se não encontrar pelo ID local, tentar buscar pelo file_id (Google Drive ID)
                    $targetFolder = GoogleDriveFile::where('file_id', $folderId)
                        ->where('is_folder', true)
                        ->first();
                    
                    if ($targetFolder) {
                        \Log::info('GoogleDriveFileController::move - Pasta encontrada pelo file_id', [
                            'id' => $targetFolder->id,
                            'name' => $targetFolder->name,
                            'file_id' => $targetFolder->file_id
                        ]);
                        // Se encontrou pelo file_id, usar o ID local
                        $request->merge(['folder_id' => $targetFolder->id]);
                    } else {
                        \Log::warning('GoogleDriveFileController::move - Pasta não encontrada nem pelo ID local nem pelo file_id', [
                            'folder_id' => $folderId,
                            'folder_id_type' => gettype($folderId)
                        ]);
                        
                        // Tentar buscar a pasta diretamente no Google Drive e criar no banco se existir
                        try {
                            \Log::info('GoogleDriveFileController::move - Tentando buscar pasta no Google Drive', [
                                'folder_id' => $folderId
                            ]);
                            
                            $folderInfo = $this->driveService->getFileInfo($folderId);
                            
                            if ($folderInfo && $folderInfo->getMimeType() === 'application/vnd.google-apps.folder') {
                                \Log::info('GoogleDriveFileController::move - Pasta encontrada no Google Drive, criando no banco', [
                                    'folder_name' => $folderInfo->getName(),
                                    'folder_id' => $folderInfo->getId()
                                ]);
                                
                                // Verificar se já existe um registro (incluindo soft deleted)
                                $existingFolder = GoogleDriveFile::withTrashed()
                                    ->where('file_id', $folderInfo->getId())
                                    ->first();
                                
                                if ($existingFolder) {
                                    if ($existingFolder->trashed()) {
                                        // Se está soft deleted, restaurar
                                        $existingFolder->restore();
                                        $existingFolder->update([
                                            'name' => $folderInfo->getName(),
                                            'mime_type' => $folderInfo->getMimeType(),
                                            'is_folder' => true,
                                            'is_trashed' => false,
                                        ]);
                                        $targetFolder = $existingFolder;
                                        
                                        \Log::info('GoogleDriveFileController::move - Pasta restaurada do soft delete', [
                                            'local_id' => $targetFolder->id,
                                            'google_id' => $targetFolder->file_id,
                                            'name' => $targetFolder->name
                                        ]);
                                    } else {
                                        // Se já existe e não está soft deleted, usar o existente
                                        $targetFolder = $existingFolder;
                                        
                                        \Log::info('GoogleDriveFileController::move - Pasta já existe no banco', [
                                            'local_id' => $targetFolder->id,
                                            'google_id' => $targetFolder->file_id,
                                            'name' => $targetFolder->name
                                        ]);
                                    }
                                } else {
                                    // Criar novo registro no banco de dados
                                    $targetFolder = GoogleDriveFile::create([
                                        'file_id' => $folderInfo->getId(),
                                        'name' => $folderInfo->getName(),
                                        'mime_type' => $folderInfo->getMimeType(),
                                        'is_folder' => true,
                                        'parent_id' => null, // Assumimos que é raiz por enquanto
                                        'created_by' => auth()->id() ?? 1,
                                        'is_trashed' => false,
                                    ]);
                                    
                                    \Log::info('GoogleDriveFileController::move - Pasta criada no banco de dados', [
                                        'local_id' => $targetFolder->id,
                                        'google_id' => $targetFolder->file_id,
                                        'name' => $targetFolder->name
                                    ]);
                                }
                            } else {
                                \Log::warning('GoogleDriveFileController::move - Pasta não encontrada no Google Drive ou não é uma pasta', [
                                    'folder_id' => $folderId,
                                    'folder_info' => $folderInfo ? [
                                        'name' => $folderInfo->getName(),
                                        'mime_type' => $folderInfo->getMimeType()
                                    ] : null
                                ]);
                            }
                        } catch (\Exception $e) {
                            \Log::error('GoogleDriveFileController::move - Erro ao buscar pasta no Google Drive', [
                                'folder_id' => $folderId,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
                
                // Se ainda não encontrou, retornar erro
                if (!$targetFolder) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pasta de destino não encontrada'
                    ], 422);
                }
            } else {
                \Log::info('GoogleDriveFileController::move - Movendo para pasta raiz (folder_id é null)');
            }
        } catch (\Exception $e) {
            \Log::error('GoogleDriveFileController::move - Erro ao validar pasta de destino', [
                'folder_id' => $request->folder_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao validar pasta de destino: ' . $e->getMessage()
            ], 422);
        }

        try {
            // Usar o folder_id já normalizado
            $folderId = $request->folder_id;
            
            \Log::info('Movendo arquivo/pasta', [
                'id' => $id,
                'folder_id' => $folderId,
                'original_folder_id' => $request->folder_id
            ]);
            
            // O ID passado agora é o file_id do Google Drive
            $fileId = $id;
            
            // Determinar o ID do Google Drive para a pasta de destino
            if ($folderId) {
                // Buscar a pasta de destino no banco de dados
                $targetFolder = GoogleDriveFile::where('id', $folderId)
                    ->orWhere('file_id', $folderId)
                    ->first();
                
                if (!$targetFolder) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pasta de destino não encontrada'
                    ], 404);
                }
                
                $targetFolderId = $targetFolder->file_id;
            } else {
                // Se folder_id for null, mover para a pasta raiz
                $targetFolderId = config('services.google.root_folder_id');
                if (empty($targetFolderId)) {
                    throw new \Exception("ID da pasta raiz do Google Drive não configurado");
                }
            }
            
            // Verificar permissões antes de mover
            \Log::info('GoogleDriveFileController::move - Verificando permissões', [
                'file_id' => $fileId,
                'target_folder_id' => $targetFolderId
            ]);
            
            // Verificar se o arquivo existe e se temos permissão
            try {
                $fileInfo = $this->driveService->getFileInfo($fileId);
                \Log::info('GoogleDriveFileController::move - Informações do arquivo', [
                    'file_name' => $fileInfo->getName(),
                    'file_owners' => $fileInfo->getOwners(),
                    'file_permissions' => $fileInfo->getPermissions()
                ]);
            } catch (\Exception $e) {
                \Log::warning('GoogleDriveFileController::move - Erro ao obter informações do arquivo', [
                    'error' => $e->getMessage()
                ]);
            }
            
            // Verificar se a pasta de destino existe e se temos permissão
            try {
                $folderInfo = $this->driveService->getFileInfo($targetFolderId);
                \Log::info('GoogleDriveFileController::move - Informações da pasta de destino', [
                    'folder_name' => $folderInfo->getName(),
                    'folder_owners' => $folderInfo->getOwners(),
                    'folder_permissions' => $folderInfo->getPermissions()
                ]);
            } catch (\Exception $e) {
                \Log::warning('GoogleDriveFileController::move - Erro ao obter informações da pasta de destino', [
                    'error' => $e->getMessage()
                ]);
            }
            
            // Mover o arquivo no Google Drive
            $result = $this->driveService->move($fileId, $targetFolderId);
            
            if ($result) {
                // Atualizar o registro no banco de dados se existir
                $fileRecord = GoogleDriveFile::where('file_id', $fileId)->first();
                if ($fileRecord) {
                    // Usar o ID local da pasta de destino, não o file_id
                    $localParentId = null;
                    if ($targetFolder) {
                        $localParentId = $targetFolder->id;
                    }
                    
                    $fileRecord->parent_id = $localParentId;
                    $fileRecord->save();
                    
                    \Log::info('GoogleDriveFileController::move - Registro atualizado no banco', [
                        'file_id' => $fileId,
                        'local_parent_id' => $localParentId,
                        'google_parent_id' => $targetFolderId
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Arquivo movido com sucesso!',
                    'refresh' => true
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao mover arquivo'
            ], 500);
        } catch (\Google\Service\Exception $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            \Log::error('GoogleDriveFileController::move - Erro da API do Google Drive', [
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'file_id' => $id,
                'folder_id' => $folderId
            ]);
            
            // Tratar especificamente o erro 403 (permissões insuficientes)
            if ($errorCode === 403) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para mover este arquivo. O arquivo pode não pertencer à sua conta ou você pode ter apenas permissão de visualização.',
                    'code' => $errorCode,
                    'errors' => $e->getErrors()
                ], 403);
            }
            
            // Para outros erros da API do Google
            return response()->json([
                'success' => false,
                'message' => 'Erro do Google Drive: ' . $errorMessage,
                'code' => $errorCode
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Erro ao mover arquivo/pasta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao mover arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    

    /**
     * Buscar arquivo pelo ID do Google Drive
     */
    public function findByFileId($fileId)
    {
        try {
            \Log::info('GoogleDriveFileController::findByFileId - Início', ['fileId' => $fileId]);
            
            // Primeiro, tenta encontrar o arquivo no banco de dados
            $file = GoogleDriveFile::where('file_id', $fileId)->first();
            
            if ($file) {
                \Log::info('GoogleDriveFileController::findByFileId - Arquivo encontrado no banco', ['id' => $file->id]);
                return response()->json([
                    'success' => true,
                    'file' => [
                        'id' => $file->id,
                        'file_id' => $file->file_id,
                        'name' => $file->name,
                        'mime_type' => $file->mime_type,
                        'size' => $file->size,
                        'is_folder' => $file->is_folder
                    ]
                ]);
            }
            
            \Log::info('GoogleDriveFileController::findByFileId - Arquivo não encontrado no banco, buscando no Google Drive');
            
            // Se não encontrar, tenta buscar do Google Drive e criar um registro local
            $fileInfo = $this->driveService->getFileInfo($fileId);
            
            if ($fileInfo) {
                \Log::info('GoogleDriveFileController::findByFileId - Arquivo encontrado no Google Drive', [
                    'name' => $fileInfo->getName(),
                    'mimeType' => $fileInfo->getMimeType()
                ]);
                
                // Cria um registro local para o arquivo
                $file = GoogleDriveFile::create([
                    'file_id' => $fileId,
                    'name' => $fileInfo->getName(),
                    'mime_type' => $fileInfo->getMimeType() ?? null,
                    'size' => $fileInfo->getSize() ?? null,
                    'is_folder' => ($fileInfo->getMimeType() ?? '') === 'application/vnd.google-apps.folder',
                    'parent_id' => null, // Não temos essa informação facilmente
                    'created_by' => auth()->id(),
                    'is_trashed' => false,
                ]);
                
                \Log::info('GoogleDriveFileController::findByFileId - Registro criado no banco', ['id' => $file->id]);
                
                return response()->json([
                    'success' => true,
                    'file' => [
                        'id' => $file->id,
                        'file_id' => $file->file_id,
                        'name' => $file->name,
                        'mime_type' => $file->mime_type,
                        'size' => $file->size,
                        'is_folder' => $file->is_folder
                    ]
                ]);
            } else {
                \Log::warning('GoogleDriveFileController::findByFileId - Arquivo não encontrado no Google Drive', ['fileId' => $fileId]);
                return response()->json(['error' => 'Arquivo não encontrado no Google Drive'], 404);
            }
        } catch (\Exception $e) {
            \Log::error('GoogleDriveFileController::findByFileId - Erro', [
                'fileId' => $fileId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Erro ao buscar arquivo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Marcar/desmarcar como favorito usando o ID do Google Drive
     */


    /**
     * Acessar arquivo pelo ID do Google Drive
     */
    public function accessByFileId($fileId)
    {
        try {
            // Verificar se o arquivo já existe no banco de dados
            $file = GoogleDriveFile::where('file_id', $fileId)->first();
            
            // Se não existir, criar um registro local
            if (!$file) {
                $fileInfo = $this->driveService->getFileInfo($fileId);
                
                if (!$fileInfo) {
                    return back()->with('error', 'Arquivo não encontrado no Google Drive');
                }
                
                // Criar registro local
                $file = GoogleDriveFile::create([
                    'file_id' => $fileId,
                    'name' => $fileInfo->getName(),
                    'mime_type' => $fileInfo->getMimeType() ?? null,
                    'size' => $fileInfo->getSize() ?? null,
                    'is_folder' => ($fileInfo->getMimeType() ?? '') === 'application/vnd.google-apps.folder',
                    'parent_id' => null,
                    'created_by' => auth()->id(),
                    'is_trashed' => false,
                ]);
            }
            
            // Se for uma pasta, redirecionar para a visualização da pasta
            if ($file->is_folder) {
                return redirect()->route('admin.files.index', ['folder' => $file->id]);
            }
            
            // Se for um arquivo, redirecionar para download
            return redirect()->route('admin.files.download', ['id' => $file->id]);
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao acessar arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Limpar registros de arquivos que não existem mais no Google Drive
     */
    public function cleanupRecords()
    {
        try {
            \Log::info('Iniciando limpeza de registros');
            
            // Obter o ID da pasta raiz
            $rootFolderId = config('services.google.root_folder_id');
            if (empty($rootFolderId)) {
                throw new \Exception("ID da pasta raiz do Google Drive não configurado");
            }
            
            // Remover qualquer registro que tenha o mesmo ID da pasta raiz
            $rootRecords = GoogleDriveFile::where('file_id', $rootFolderId)->get();
            foreach ($rootRecords as $record) {
                $record->delete();
                \Log::info('Registro de pasta raiz excluído: ' . $record->name . ' (ID: ' . $record->id . ')');
            }
            
            // Remover qualquer pasta chamada "site" que seja a raiz
            $siteRecords = GoogleDriveFile::where('name', 'site')
                ->where('file_id', $rootFolderId)
                ->get();
            foreach ($siteRecords as $record) {
                $record->delete();
                \Log::info('Registro de pasta "site" excluído: ' . $record->name . ' (ID: ' . $record->id . ')');
            }
            
            // Obter todos os arquivos do banco de dados
            $dbFiles = GoogleDriveFile::all();
            $deletedCount = 0;
            $updatedCount = 0;
            
            foreach ($dbFiles as $dbFile) {
                try {
                    // Verificar se o arquivo ainda existe no Google Drive
                    $fileInfo = $this->driveService->getFileInfo($dbFile->file_id);
                    
                    if (!$fileInfo) {
                        // Se o arquivo não existe mais no Google Drive, excluir do banco de dados
                        $dbFile->delete();
                        $deletedCount++;
                        \Log::info('Arquivo excluído do banco de dados: ' . $dbFile->name . ' (ID: ' . $dbFile->id . ')');
                    } else {
                        // Se o arquivo existe, verificar se é a pasta raiz
                        if ($dbFile->file_id === $rootFolderId) {
                            // Se for a pasta raiz, excluir do banco de dados
                            $dbFile->delete();
                            $deletedCount++;
                            \Log::info('Pasta raiz excluída do banco de dados: ' . $dbFile->name . ' (ID: ' . $dbFile->id . ')');
                        } else {
                            // Atualizar informações do arquivo
                            $dbFile->name = $fileInfo->getName();
                            $dbFile->mime_type = $fileInfo->getMimeType();
                            $dbFile->size = $fileInfo->getSize() ?? 0;
                            $dbFile->is_folder = $fileInfo->getMimeType() === 'application/vnd.google-apps.folder';
                            
                            // Verificar se o parent_id está correto
                            $parents = $fileInfo->getParents();
                            if ($parents && count($parents) > 0) {
                                $parentFileId = $parents[0];
                                $correctParentId = null;
                                
                                // Se o parent não for a pasta raiz, buscar no banco de dados
                                if ($parentFileId !== $rootFolderId) {
                                    $parentFile = GoogleDriveFile::where('file_id', $parentFileId)->first();
                                    if ($parentFile) {
                                        $correctParentId = $parentFile->id;
                                    }
                                }
                                
                                // Atualizar o parent_id se necessário
                                if ($dbFile->parent_id !== $correctParentId) {
                                    $dbFile->parent_id = $correctParentId;
                                    $updatedCount++;
                                }
                            }
                            
                            $dbFile->save();
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Erro ao verificar arquivo: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Limpeza concluída. {$deletedCount} arquivos excluídos e {$updatedCount} atualizados."
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao limpar registros: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar registros: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Executar uma limpeza completa no banco de dados
     * Remove qualquer registro da pasta "site" e qualquer registro com o ID da pasta raiz
     */
    public function forceCleanup()
    {
        try {
            \Log::info('Iniciando limpeza forçada de registros');
            
            // Obter o ID da pasta raiz
            $rootFolderId = config('services.google.root_folder_id');
            if (empty($rootFolderId)) {
                throw new \Exception("ID da pasta raiz do Google Drive não configurado");
            }
            
            // Remover qualquer registro com o nome "site"
            $siteRecords = GoogleDriveFile::where('name', 'site')->get();
            $siteCount = $siteRecords->count();
            foreach ($siteRecords as $record) {
                $record->delete();
                \Log::info('Registro de pasta "site" excluído: ' . $record->name . ' (ID: ' . $record->id . ')');
            }
            
            // Remover qualquer registro com o ID da pasta raiz
            $rootRecords = GoogleDriveFile::where('file_id', $rootFolderId)->get();
            $rootCount = $rootRecords->count();
            foreach ($rootRecords as $record) {
                $record->delete();
                \Log::info('Registro de pasta raiz excluído: ' . $record->name . ' (ID: ' . $record->id . ')');
            }
            
            // Sincronizar com o Google Drive
            try {
                $this->driveService->listFiles(null);
                \Log::info('Sincronização com Google Drive concluída');
            } catch (\Exception $e) {
                \Log::warning('Erro ao sincronizar com Google Drive: ' . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => "Sinconização forçada concluída. {$siteCount} registros 'site' e {$rootCount} registros raiz excluídos."
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao executar limpeza forçada: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar limpeza forçada: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar apenas pastas para seleção como pasta pai
     */
    public function listFolders(Request $request)
    {
        try {
            // Buscar apenas pastas no banco de dados
            $folders = GoogleDriveFile::where('is_folder', true)
                        ->where('is_trashed', false)
                        ->orderBy('name')
                        ->get(['id', 'file_id', 'name', 'parent_id']);
            
            return response()->json([
                'success' => true,
                'folders' => $folders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar pastas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar pasta pai (para organização)
     */
    public function createParentFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $folder = $this->driveService->createFolder(
                $request->name,
                Auth::id(),
                null // Criar na raiz
            );

            return response()->json([
                'success' => true,
                'message' => 'Pasta criada com sucesso!',
                'folder' => $folder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar pasta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar permissões de um arquivo/pasta
     */
    public function getPermissions($id)
    {
        try {
            $file = GoogleDriveFile::findOrFail($id);
            $permissions = $this->driveService->listPermissions($file->file_id);

            return response()->json([
                'success' => true,
                'permissions' => $permissions,
                'file' => $file
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar permissões: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compartilhar com usuário específico
     */
    public function shareWithUser(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:reader,writer,commenter',
            'notify' => 'boolean'
        ]);

        try {
            $file = GoogleDriveFile::findOrFail($id);
            
            $permission = $this->driveService->shareWithUser(
                $file->file_id,
                $request->email,
                $request->role,
                $request->boolean('notify', true)
            );

            return response()->json([
                'success' => true,
                'message' => 'Arquivo compartilhado com sucesso!',
                'permission' => $permission
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao compartilhar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar link público
     */
    public function createPublicLink(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:reader,writer,commenter'
        ]);

        try {
            $file = GoogleDriveFile::findOrFail($id);
            
            $linkInfo = $this->driveService->createPublicLink(
                $file->file_id,
                $request->role
            );

            return response()->json([
                'success' => true,
                'message' => 'Link público criado com sucesso!',
                'link_info' => $linkInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar link público: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar código de incorporação
     */
    public function generateEmbed(Request $request, $id)
    {
        $request->validate([
            'width' => 'nullable|string',
            'height' => 'nullable|string'
        ]);

        try {
            $file = GoogleDriveFile::findOrFail($id);
            
            $embedInfo = $this->driveService->generateEmbedLink(
                $file->file_id,
                $request->input('width', '100%'),
                $request->input('height', '600px')
            );

            return response()->json([
                'success' => true,
                'embed_info' => $embedInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar código de incorporação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover permissão
     */
    public function removePermission(Request $request, $id)
    {
        $request->validate([
            'permission_id' => 'required|string'
        ]);

        try {
            $file = GoogleDriveFile::findOrFail($id);
            
            $success = $this->driveService->removePermission(
                $file->file_id,
                $request->permission_id
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permissão removida com sucesso!'
                ]);
            } else {
                throw new \Exception('Falha ao remover permissão');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover permissão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar permissão
     */
    public function updatePermission(Request $request, $id)
    {
        $request->validate([
            'permission_id' => 'required|string',
            'role' => 'required|in:reader,writer,commenter'
        ]);

        try {
            $file = GoogleDriveFile::findOrFail($id);
            
            $permission = $this->driveService->updatePermission(
                $file->file_id,
                $request->permission_id,
                $request->role
            );

            return response()->json([
                'success' => true,
                'message' => 'Permissão atualizada com sucesso!',
                'permission' => $permission
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar permissão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar permissões de um arquivo
     */
    public function checkPermissions($id)
    {
        try {
            \Log::info('GoogleDriveFileController::checkPermissions - Verificando permissões', ['id' => $id]);
            
            // Verificar se o ID é um file_id do Google Drive ou ID local
            $file = null;
            if (preg_match('/[a-zA-Z]/', $id)) {
                // É um file_id do Google Drive
                $file = GoogleDriveFile::where('file_id', $id)->first();
            } else {
                // É um ID local
                $file = GoogleDriveFile::find($id);
            }
            
            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo não encontrado no banco de dados'
                ], 404);
            }
            
            // Verificar permissões no Google Drive
            $permissions = $this->driveService->checkFilePermissions($file->file_id);
            
            return response()->json([
                'success' => true,
                'file' => [
                    'id' => $file->id,
                    'file_id' => $file->file_id,
                    'name' => $file->name,
                    'is_folder' => $file->is_folder
                ],
                'permissions' => $permissions
            ]);
            
        } catch (\Exception $e) {
            \Log::error('GoogleDriveFileController::checkPermissions - Erro ao verificar permissões', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar permissões: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mover arquivo/pasta para lixeira
     */
    public function moveToTrash($id)
    {
        try {
            \Log::info('GoogleDriveFileController::moveToTrash - Início', ['id' => $id]);
            
            // Verificar se o ID é um file_id do Google Drive ou ID local
            $file = null;
            if (preg_match('/[a-zA-Z]/', $id)) {
                // É um file_id do Google Drive
                $file = GoogleDriveFile::withTrashed()->where('file_id', $id)->first();
            } else {
                // É um ID local
                $file = GoogleDriveFile::withTrashed()->find($id);
            }
            
            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo não encontrado no banco de dados'
                ], 404);
            }
            
            // Se o arquivo já está na lixeira, restaurar
            if ($file->trashed()) {
                $result = $this->driveService->untrash($file->file_id);
                
                if ($result) {
                    $file->restore();
                    return response()->json([
                        'success' => true,
                        'message' => 'Arquivo restaurado da lixeira com sucesso!'
                    ]);
                }
            } else {
                // Mover para lixeira
                $result = $this->driveService->trash($file->file_id);
                
                if ($result) {
                    $file->delete(); // Soft delete no banco local
                    return response()->json([
                        'success' => true,
                        'message' => 'Arquivo movido para lixeira com sucesso!'
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao mover arquivo para lixeira'
            ], 500);
            
        } catch (\Google\Service\Exception $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            \Log::error('GoogleDriveFileController::moveToTrash - Erro da API do Google Drive', [
                'id' => $id,
                'error_code' => $errorCode,
                'error_message' => $errorMessage
            ]);
            
            if ($errorCode === 403) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para mover este arquivo/pasta para lixeira.',
                    'code' => $errorCode
                ], 403);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro do Google Drive: ' . $errorMessage,
                'code' => $errorCode
            ], 500);
        } catch (\Exception $e) {
            \Log::error('GoogleDriveFileController::moveToTrash - Erro geral', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao mover arquivo para lixeira: ' . $e->getMessage()
            ], 500);
        }
    }
}
