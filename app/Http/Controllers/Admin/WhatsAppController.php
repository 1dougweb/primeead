<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
            $this->middleware('auth');
        $this->middleware('permission:whatsapp.index');
        $this->whatsappService = $whatsappService;
    }

    /**
     * Exibir configurações do WhatsApp
     */
    public function index()
    {
        $settings = [
            'base_url' => SystemSetting::get('evolution_api_base_url', ''),
            'api_key' => SystemSetting::get('evolution_api_key', ''),
            'instance' => SystemSetting::get('evolution_api_instance', 'default'),
            'number' => SystemSetting::get('evolution_api_number', ''),
            'connected' => SystemSetting::get('evolution_api_connected', false),
            'last_connection' => SystemSetting::get('evolution_api_last_connection', '')
        ];
        
        // Verificar se instância existe
        $instanceExists = false;
        $connectionStatus = ['connected' => false, 'message' => 'Configurações incompletas'];
        
        if (!empty($settings['base_url']) && !empty($settings['api_key']) && !empty($settings['instance'])) {
            try {
                $instanceExists = $this->whatsappService->instanceExists();
                $connectionStatus = $this->whatsappService->getConnectionStatus();
            } catch (\Exception $e) {
                $connectionStatus = ['connected' => false, 'message' => 'Erro de conexão'];
            }
        }
        
        return view('admin.settings.whatsapp', compact('settings', 'connectionStatus', 'instanceExists'));
    }

    /**
     * Verificar se instância existe
     */
    public function checkInstance()
    {
        try {
            $exists = $this->whatsappService->instanceExists();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'exists' => $exists
                ],
                'message' => $exists ? 'Instância encontrada' : 'Instância não encontrada'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar instância: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'data' => [
                    'exists' => false
                ],
                'error' => $e->getMessage(),
                'message' => 'Erro ao verificar instância: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar configurações
     */
    public function update(Request $request)
    {
        $request->validate([
            'base_url' => 'required|url',
            'api_key' => 'required|string|max:255',
            'instance' => 'required|string|max:100|regex:/^[a-zA-Z0-9_-]+$/',
            'number' => 'nullable|string|regex:/^[0-9]+$/'
        ], [
            'base_url.required' => 'A URL da API é obrigatória',
            'base_url.url' => 'A URL da API deve ser um endereço válido',
            'api_key.required' => 'A chave da API é obrigatória',
            'instance.required' => 'O nome da instância é obrigatório',
            'instance.regex' => 'O nome da instância deve conter apenas letras, números, hífen e underscore',
            'number.regex' => 'O número deve conter apenas dígitos'
        ]);

        try {
            // Atualizar configurações através do serviço
            $this->whatsappService->updateSettings(
                $request->base_url,
                $request->api_key,
                $request->instance,
                $request->number
            );

            return redirect()
                ->route('admin.settings.whatsapp')
                ->with('success', 'Configurações do WhatsApp atualizadas com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar configurações WhatsApp: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao salvar configurações: ' . $e->getMessage());
        }
    }

    /**
     * Gerar QR Code para conexão
     */
    public function qrCode()
    {
        try {
            $result = $this->whatsappService->getQrCode();
            
            // Log para debug
            Log::info('Resultado do getQrCode', ['result' => $result]);
            
            return response()->json([
                'success' => $result['success'] ?? true,
                'data' => $result,
                'message' => $result['message'] ?? 'QR Code gerado com sucesso!'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar QR Code: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Erro ao gerar QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar nova instância
     */
    public function createInstance()
    {
        try {
            $result = $this->whatsappService->createInstance();
            
            return response()->json([
                'success' => true,
                'message' => 'Instância criada com sucesso!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar instância WhatsApp: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar status da conexão
     */
    public function status()
    {
        try {
            $status = $this->whatsappService->getConnectionStatus();
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status WhatsApp: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desconectar instância
     */
    public function disconnect()
    {
        try {
            $result = $this->whatsappService->disconnect();
            
            return response()->json([
                'success' => true,
                'message' => 'Instância desconectada com sucesso!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao desconectar WhatsApp: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar instância
     */
    public function deleteInstance()
    {
        try {
            $result = $this->whatsappService->deleteInstance();
            
            return response()->json([
                'success' => true,
                'message' => 'Instância deletada com sucesso!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar instância WhatsApp: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar envio de mensagem
     */
    public function testMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:1000'
        ], [
            'phone.required' => 'O número de telefone é obrigatório',
            'message.required' => 'A mensagem é obrigatória'
        ]);

        try {
            $result = $this->whatsappService->sendMessage(
                $request->phone,
                $request->message
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Mensagem enviada com sucesso!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem de teste: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reconectar instância (útil quando QR Code expira)
     */
    public function reconnect()
    {
        try {
            $result = $this->whatsappService->reconnectInstance();
            
            return response()->json([
                'success' => true,
                'message' => 'Tentativa de reconexão realizada!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao reconectar WhatsApp: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar mudanças no status de conexão
     */
    public function checkConnectionChange()
    {
        try {
            $currentStatus = $this->whatsappService->getConnectionStatus();
            $lastKnownStatus = SystemSetting::get('evolution_api_connected', false, 'boolean');
            
            $response = [
                'success' => true,
                'current_status' => $currentStatus,
                'status_changed' => false,
                'message' => null
            ];
            
            // Verificar se houve mudança de status
            if ($lastKnownStatus !== $currentStatus['connected']) {
                $response['status_changed'] = true;
                
                if (!$currentStatus['connected'] && $lastKnownStatus) {
                    // Foi desconectado
                    $response['message'] = 'WhatsApp foi desconectado. Clique em "Gerar QR Code" para reconectar.';
                    Log::info('WhatsApp desconectado automaticamente');
                } elseif ($currentStatus['connected'] && !$lastKnownStatus) {
                    // Foi conectado
                    $response['message'] = 'WhatsApp conectado com sucesso!';
                    Log::info('WhatsApp conectado com sucesso');
                }
                
                // Atualizar status conhecido
                SystemSetting::set('evolution_api_connected', $currentStatus['connected'], 'boolean', 'evolution_api', 'Status da conexão');
            }
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar mudança de status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Monitorar conexão e reconectar automaticamente se necessário
     */
    public function monitor()
    {
        try {
            $result = $this->whatsappService->checkAndReconnectIfNeeded();
            
            if ($result === true) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conexão WhatsApp está funcionando normalmente',
                    'connected' => true
                ]);
            } else if (is_array($result)) {
                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'connected' => isset($result['connected']) ? $result['connected'] : false,
                    'qrcode' => isset($result['qrcode']) ? $result['qrcode'] : null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Conexão WhatsApp apresenta problemas',
                    'connected' => false
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro no monitoramento WhatsApp: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'connected' => false
            ], 500);
        }
    }
} 