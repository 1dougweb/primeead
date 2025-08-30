<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleDriveService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestGoogleDriveDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'googledrive:test-delete {file_id} {--user=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Google Drive delete functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fileId = $this->argument('file_id');
        $userId = $this->option('user');

        $this->info("🔍 Testing Google Drive Delete Function");
        $this->info("📁 File ID: {$fileId}");
        $this->info("👤 User ID: {$userId}");
        $this->newLine();

        // Test user authentication
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User not found with ID: {$userId}");
            return 1;
        }

        Auth::login($user);
        $this->info("✅ User authenticated: {$user->name} ({$user->email})");

        // Check permissions
        $hasDeletePermission = $user->hasPermissionTo('arquivos.delete');
        $this->info("🔑 Has 'arquivos.delete' permission: " . ($hasDeletePermission ? '✅ YES' : '❌ NO'));

        if (!$hasDeletePermission) {
            $this->warn("⚠️ User doesn't have delete permission, but continuing with test...");
        }

        // Test Google Drive Service
        $driveService = app(GoogleDriveService::class);
        $isConfigured = $driveService->isConfigured();
        $isReady = $driveService->isReady();

        $this->info("🔧 Google Drive configured: " . ($isConfigured ? '✅ YES' : '❌ NO'));
        $this->info("🚀 Google Drive ready: " . ($isReady ? '✅ YES' : '❌ NO'));

        if (!$isReady) {
            $this->error("❌ Cannot test delete - Google Drive service not ready");
            return 1;
        }

        $this->newLine();
        $this->info("🗂️ Testing delete for file ID: {$fileId}");

        try {
            $result = $driveService->deleteFile($fileId);
            
            $this->info("📤 Delete result:");
            $this->table(['Key', 'Value'], [
                ['Success', $result['success'] ? 'YES' : 'NO'],
                ['Message', $result['message'] ?? 'N/A'],
                ['Error', $result['error'] ?? 'N/A']
            ]);

            if ($result['success']) {
                $this->info("✅ File deleted successfully!");
                return 0;
            } else {
                $this->error("❌ Delete failed: " . ($result['error'] ?? 'Unknown error'));
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Exception occurred: " . $e->getMessage());
            $this->error("📍 File: " . $e->getFile());
            $this->error("📍 Line: " . $e->getLine());
            return 1;
        }
    }
} 