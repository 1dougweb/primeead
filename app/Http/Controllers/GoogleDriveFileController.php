<?php

namespace App\Http\Controllers;

use App\Models\GoogleDriveFile;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleDriveFileController extends Controller
{
    protected $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    /**
     * Exibir lista de arquivos
     */
    public function index(Request $request)
    {
        try {
            $parentId = $request->get('folder');
            $search = $request->get('search');
            
            // Carregar todas as pastas para o modal de mover
            $folders = GoogleDriveFile::where('is_folder', true)
                        ->where('is_trashed', false)
                        ->get();
            
            // Verificar se o serviço está configurado
            try {
                $files = $this->driveService->listFiles($parentId, $search);
                $currentFolder = $parentId ? GoogleDriveFile::find($parentId) : null;
                $configError = null;
            } catch (\Exception $e) {
                $files = collect([]);
                $currentFolder = null;
                $configError = $e->getMessage();
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'files' => $files,
                    'currentFolder' => $currentFolder,
                    'folders' => $folders,
                    'configError' => $configError
                ]);
            }

            return view('admin.files.index', compact('files', 'currentFolder', 'folders', 'configError'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return view('admin.files.index', [
                'files' => collect([]),
                'currentFolder' => null,
                'folders' => collect([]),
                'configError' => $e->getMessage()
            ]);
        }
    }

    /**
     * Upload de arquivo
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // max 100MB
            'folder_id' => 'nullable|exists:google_drive_files,id'
        ]);

        try {
            $file = $this->driveService->uploadFile(
                $request->file('file'),
                Auth::id(),
                $request->folder_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Arquivo enviado com sucesso!',
                'file' => $file
            ]);
        } catch (\Exception $e) {
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
            $folder = $this->driveService->createFolder(
                $request->name,
                Auth::id(),
                $request->parent_id
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
                            'name' => $fileInfo['name'],
                            'mime_type' => $fileInfo['mimeType'] ?? null,
                            'size' => $fileInfo['size'] ?? null,
                            'web_content_link' => $fileInfo['webContentLink'] ?? null,
                            'is_folder' => ($fileInfo['mimeType'] ?? '') === 'application/vnd.google-apps.folder',
                            'parent_id' => null,
                            'created_by' => auth()->id(),
                            'is_starred' => false,
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
            
            // Verificar se temos o link de download direto
            if ($file->web_content_link) {
                // Redirecionar para o link de download direto do Google Drive
                return redirect()->away($file->web_content_link);
            }
            
            // Se não tiver o link, tentar baixar pelo método tradicional
            $fileData = $this->driveService->download($file->file_id);

            if (!$fileData) {
                return back()->with('error', 'Erro ao baixar arquivo: Arquivo não encontrado no Google Drive');
            }

            // Se o serviço retornou um link direto, redirecionar
            if (isset($fileData['use_redirect']) && $fileData['use_redirect'] && isset($fileData['web_content_link'])) {
                // Atualizar o registro com o link de download, se necessário
                if (!$file->web_content_link) {
                    $file->update(['web_content_link' => $fileData['web_content_link']]);
                }
                
                // Redirecionar para o link de download
                return redirect()->away($fileData['web_content_link']);
            }

            // Se não tem link direto, retornar o conteúdo do arquivo
            return response($fileData['content'])
                ->header('Content-Type', $fileData['mime_type'])
                ->header('Content-Disposition', 'attachment; filename="' . $file->name . '"');
        } catch (\Exception $e) {
            \Log::error('Erro ao baixar arquivo: ' . $e->getMessage());
            return back()->with('error', 'Erro ao baixar arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Excluir arquivo/pasta
     */
    public function destroy($id)
    {
        $file = GoogleDriveFile::findOrFail($id);

        if ($this->driveService->delete($file->file_id)) {
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
    }

    /**
     * Mover para lixeira
     */
    public function trash($id)
    {
        $file = GoogleDriveFile::findOrFail($id);

        if ($this->driveService->trash($file->file_id)) {
            $file->update(['is_trashed' => true]);
            return response()->json([
                'success' => true,
                'message' => 'Arquivo movido para a lixeira!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao mover arquivo para a lixeira'
        ], 500);
    }

    /**
     * Restaurar da lixeira
     */
    public function restore($id)
    {
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
        $request->validate([
            'folder_id' => 'required|exists:google_drive_files,id'
        ]);

        $file = GoogleDriveFile::findOrFail($id);
        $targetFolder = GoogleDriveFile::findOrFail($request->folder_id);

        if ($this->driveService->move($file->file_id, $targetFolder->file_id)) {
            $file->update(['parent_id' => $request->folder_id]);
            return response()->json([
                'success' => true,
                'message' => 'Arquivo movido com sucesso!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao mover arquivo'
        ], 500);
    }

    /**
     * Marcar/desmarcar como favorito
     */
    public function toggleStar($id)
    {
        $file = GoogleDriveFile::findOrFail($id);
        $file->update(['is_starred' => !$file->is_starred]);

        return response()->json([
            'success' => true,
            'message' => $file->is_starred ? 'Arquivo marcado como favorito!' : 'Arquivo removido dos favoritos!',
            'is_starred' => $file->is_starred
        ]);
    }

    /**
     * Buscar arquivo pelo ID do Google Drive
     */
    public function findByFileId($fileId)
    {
        try {
            // Primeiro, tenta encontrar o arquivo no banco de dados
        $file = GoogleDriveFile::where('file_id', $fileId)->first();
        
            // Se não encontrar, tenta buscar do Google Drive e criar um registro local
        if (!$file) {
                $fileInfo = $this->driveService->getFileInfo($fileId);
                
                if ($fileInfo) {
                    // Cria um registro local para o arquivo
                    $file = GoogleDriveFile::create([
                        'file_id' => $fileId,
                        'name' => $fileInfo['name'],
                        'mime_type' => $fileInfo['mimeType'] ?? null,
                        'size' => $fileInfo['size'] ?? null,
                        'is_folder' => ($fileInfo['mimeType'] ?? '') === 'application/vnd.google-apps.folder',
                        'parent_id' => null, // Não temos essa informação facilmente
                        'created_by' => auth()->id(),
                        'is_starred' => false,
                        'is_trashed' => false,
                    ]);
                } else {
                    return response()->json(['error' => 'Arquivo não encontrado no Google Drive'], 404);
                }
        }
        
        return response()->json([
            'id' => $file->id,
            'file_id' => $file->file_id,
            'name' => $file->name
        ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar arquivo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Marcar/desmarcar como favorito usando o ID do Google Drive
     */
    public function toggleStarByFileId($fileId)
    {
        try {
            // Primeiro, tenta encontrar o arquivo no banco de dados
        $file = GoogleDriveFile::where('file_id', $fileId)->first();
        
            // Se não encontrar, tenta buscar do Google Drive e criar um registro local
        if (!$file) {
                $fileInfo = $this->driveService->getFileInfo($fileId);
                
                if ($fileInfo) {
                    // Cria um registro local para o arquivo
                    $file = GoogleDriveFile::create([
                        'file_id' => $fileId,
                        'name' => $fileInfo['name'],
                        'mime_type' => $fileInfo['mimeType'] ?? null,
                        'size' => $fileInfo['size'] ?? null,
                        'is_folder' => ($fileInfo['mimeType'] ?? '') === 'application/vnd.google-apps.folder',
                        'parent_id' => null, // Não temos essa informação facilmente
                        'created_by' => auth()->id(),
                        'is_starred' => true, // Já marca como favorito
                        'is_trashed' => false,
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Arquivo marcado como favorito!',
                        'is_starred' => true
                    ]);
                } else {
                    return response()->json(['error' => 'Arquivo não encontrado no Google Drive'], 404);
                }
        }
        
        $file->update(['is_starred' => !$file->is_starred]);

        return response()->json([
            'success' => true,
            'message' => $file->is_starred ? 'Arquivo marcado como favorito!' : 'Arquivo removido dos favoritos!',
            'is_starred' => $file->is_starred
        ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao marcar/desmarcar favorito: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Acessar arquivo ou pasta diretamente pelo ID do Google Drive
     */
    public function accessByFileId($fileId)
    {
        $file = GoogleDriveFile::where('file_id', $fileId)->first();
        
        if (!$file) {
            return back()->with('error', 'Arquivo não encontrado no banco de dados local');
        }
        
        // Se for uma pasta, redirecionar para a visualização da pasta
        if ($file->is_folder) {
            return redirect()->route('admin.files.index', ['folder' => $file->id]);
        }
        
        // Se for um arquivo, redirecionar para o download
        return redirect()->route('admin.files.download', ['id' => $file->id]);
    }
}
