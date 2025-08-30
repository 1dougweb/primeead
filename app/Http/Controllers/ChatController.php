<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Processar mensagem do usuário
     */
    public function processMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'required|string',
            'user_email' => 'nullable|email',
            'user_name' => 'nullable|string|max:255'
        ]);

        try {
            $result = $this->chatService->processMessage(
                $request->session_id,
                $request->message,
                $request->user_email,
                $request->user_name
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Erro no controller do chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor. Tente novamente em alguns instantes.'
            ], 500);
        }
    }

    /**
     * Obter histórico de conversa
     */
    public function getHistory(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        try {
            $history = $this->chatService->getConversationHistory($request->session_id);
            
            return response()->json([
                'success' => true,
                'history' => $history
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter histórico do chat', [
                'error' => $e->getMessage(),
                'session_id' => $request->session_id
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao carregar histórico da conversa.'
            ], 500);
        }
    }

    /**
     * Encerrar conversa
     */
    public function closeConversation(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        try {
            $success = $this->chatService->closeConversation($request->session_id);
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Conversa encerrada com sucesso.' : 'Conversa não encontrada.'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao encerrar conversa', [
                'error' => $e->getMessage(),
                'session_id' => $request->session_id
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao encerrar conversa.'
            ], 500);
        }
    }

    /**
     * Gerar ID de sessão único
     */
    public function generateSessionId()
    {
        return response()->json([
            'success' => true,
            'session_id' => Str::uuid()->toString()
        ]);
    }

    /**
     * Testar conexão do chat
     */
    public function testConnection()
    {
        try {
            // Verificar se o ChatGPT está configurado
            $aiSettings = \App\Models\SystemSetting::getAiSettings();
            
            if (empty($aiSettings['api_key'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'ChatGPT não está configurado. Configure a API key nas configurações do sistema.'
                ]);
            }

            // Testar com uma mensagem simples
            $sessionId = Str::uuid()->toString();
            $result = $this->chatService->processMessage(
                $sessionId,
                'Olá, teste de conexão.',
                'teste@exemplo.com',
                'Usuário Teste'
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chat funcionando corretamente!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao processar mensagem: ' . ($result['error'] ?? 'Erro desconhecido')
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão do chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao testar conexão: ' . $e->getMessage()
            ], 500);
        }
    }
}
