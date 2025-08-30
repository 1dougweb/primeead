<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleDriveController extends Controller
{
    private $drive;

    public function __construct()
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/google-credentials.json'));
        $client->addScope(Google_Service_Drive::DRIVE);
        $client->setAccessType('offline');
        
        $this->drive = new Google_Service_Drive($client);
    }

    public function createStudentFolder(Request $request)
    {
        try {
            $request->validate([
                'student_name' => 'required|string'
            ]);

            // Create folder metadata
            $folderMetadata = new Google_Service_Drive_DriveFile([
                'name' => $request->student_name . ' - Documentos',
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            // Create the folder
            $folder = $this->drive->files->create($folderMetadata, [
                'fields' => 'id'
            ]);

            return response()->json([
                'status' => 'success',
                'folder_id' => $folder->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating student folder: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create folder'
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file',
                'folder_id' => 'required|string'
            ]);

            $file = $request->file('file');
            
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $file->getClientOriginalName(),
                'parents' => [$request->folder_id]
            ]);

            $content = file_get_contents($file->getRealPath());
            
            $file = $this->drive->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);

            return response()->json([
                'status' => 'success',
                'file_id' => $file->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload file'
            ], 500);
        }
    }

    public function listFiles(Request $request)
    {
        try {
            $request->validate([
                'folder_id' => 'required|string'
            ]);

            $files = $this->drive->files->listFiles([
                'q' => "'{$request->folder_id}' in parents",
                'fields' => 'files(id, name, mimeType, webViewLink)'
            ]);

            return response()->json([
                'status' => 'success',
                'files' => $files->getFiles()
            ]);

        } catch (\Exception $e) {
            Log::error('Error listing files: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to list files'
            ], 500);
        }
    }

    public function deleteFile($fileId)
    {
        try {
            $this->drive->files->delete($fileId);

            return response()->json([
                'status' => 'success',
                'message' => 'File deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting file: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete file'
            ], 500);
        }
    }
} 