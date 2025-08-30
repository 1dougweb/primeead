<?php

namespace App\Http\Controllers;

use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OAuthController extends Controller
{
    protected $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->middleware('auth');
        $this->driveService = $driveService;
    }

    /**
     * Handle OAuth callback from Google
     */
    public function callback(Request $request)
    {
        try {
            $code = $request->get('code');
            
            if (!$code) {
                return redirect()->route('admin.settings.index')
                    ->with('error', 'Código de autorização não fornecido.');
            }

            // Process OAuth callback here if needed
            // This would typically exchange the code for tokens
            
            return redirect()->route('admin.settings.index')
                ->with('success', 'Autorização concluída com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('OAuth callback error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('admin.settings.index')
                ->with('error', 'Erro durante a autorização.');
        }
    }

    /**
     * Check OAuth status
     */
    public function status()
    {
        try {
            $isConfigured = $this->driveService->isConfigured();
            
            return response()->json([
                'success' => true,
                'configured' => $isConfigured,
                'message' => $isConfigured ? 'Google Drive configurado' : 'Google Drive não configurado'
            ]);
            
        } catch (\Exception $e) {
            Log::error('OAuth status error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'configured' => false,
                'message' => 'Erro ao verificar status'
            ], 500);
        }
    }

    /**
     * Revoke OAuth tokens
     */
    public function revoke(Request $request)
    {
        try {
            // Logic to revoke tokens would go here
            
            return response()->json([
                'success' => true,
                'message' => 'Autorização revogada com sucesso'
            ]);
            
        } catch (\Exception $e) {
            Log::error('OAuth revoke error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao revogar autorização'
            ], 500);
        }
    }

    /**
     * Get OAuth authorization URL
     */
    public function getAuthUrl()
    {
        try {
            // Logic to generate auth URL would go here
            $authUrl = 'https://accounts.google.com/oauth2/auth'; // Placeholder
            
            return response()->json([
                'success' => true,
                'auth_url' => $authUrl
            ]);
            
        } catch (\Exception $e) {
            Log::error('OAuth auth URL error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar URL de autorização'
            ], 500);
        }
    }
} 