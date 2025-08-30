<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleDriveService;

class TestGoogleDrive extends Command
{
    protected $signature = 'google-drive:test';
    protected $description = 'Test Google Drive connection and list files';

    public function handle()
    {
        $this->info('Testing Google Drive connection...');
        
        try {
            $driveService = new GoogleDriveService();
            
            $this->info('Google Drive service initialized successfully.');
            
            // Test listing files
            $this->info('Attempting to list files...');
            $files = $driveService->listFiles();
            
            $this->info('Files found: ' . $files->count());
            
            foreach ($files->take(10) as $file) {
                $this->line('- ' . $file->name . ' (' . $file->mime_type . ')');
            }
            
            $this->info('Google Drive test completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Error testing Google Drive: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
} 