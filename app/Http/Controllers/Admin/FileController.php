<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriveFile;
use Illuminate\Http\Request;

/**
 * ATENÇÃO: Este controlador não é mais necessário.
 * O método move foi transferido para o GoogleDriveFileController.
 * As rotas foram atualizadas para usar o GoogleDriveFileController.
 * Este arquivo pode ser removido com segurança.
 */
class FileController extends Controller
{
    public function move(Request $request, $id)
    {
        try {
            $file = \App\Models\GoogleDriveFile::findOrFail($id);
            $file->parent_id = $request->input('folder_id');
            $file->save();

            return response()->json(['success' => true, 'message' => 'Arquivo movido com sucesso']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao mover arquivo: ' . $e->getMessage()], 500);
        }
    }
} 