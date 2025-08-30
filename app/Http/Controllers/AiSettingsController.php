<?php

namespace App\Http\Controllers;

use App\Models\AiSetting;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiSettingsController extends Controller
{
    public function index()
    {
        $settings = AiSetting::first();
        
        // Se não existir AiSetting, criar um objeto vazio com dados do SystemSetting
        if (!$settings) {
            $settings = new AiSetting();
            $aiSettingsFromSystem = SystemSetting::getAiSettings();
            $settings->api_key = $aiSettingsFromSystem['api_key'];
            $settings->model = $aiSettingsFromSystem['model'];
            $settings->system_prompt = $aiSettingsFromSystem['system_prompt'];
            $settings->is_active = $aiSettingsFromSystem['is_active'];
            
            // Adicionar campos de prompt que não existem no AiSetting
            $settings->email_template_prompt = $aiSettingsFromSystem['email_template_prompt'];
            $settings->whatsapp_template_prompt = $aiSettingsFromSystem['whatsapp_template_prompt'];
            $settings->contract_template_prompt = $aiSettingsFromSystem['contract_template_prompt'];
            $settings->payment_template_prompt = $aiSettingsFromSystem['payment_template_prompt'];
            $settings->enrollment_template_prompt = $aiSettingsFromSystem['enrollment_template_prompt'];
            $settings->matriculation_template_prompt = $aiSettingsFromSystem['matriculation_template_prompt'];
            $settings->support_prompt = $aiSettingsFromSystem['support_prompt'];
        }
        
        return view('admin.settings.ai', compact('settings'));
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'ai_settings.api_key' => 'required|string',
            'ai_settings.model' => 'required|string',
            'ai_settings.system_prompt' => 'nullable|string',
            'ai_settings.email_template_prompt' => 'nullable|string',
            'ai_settings.whatsapp_template_prompt' => 'nullable|string',
            'ai_settings.contract_template_prompt' => 'nullable|string',
            'ai_settings.payment_template_prompt' => 'nullable|string',
            'ai_settings.enrollment_template_prompt' => 'nullable|string',
            'ai_settings.matriculation_template_prompt' => 'nullable|string',
            'ai_settings.support_prompt' => 'nullable|string',
            'ai_settings.is_active' => 'boolean'
        ]);
        
        $aiSettings = $request->ai_settings;
        
        // Testar a API key
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $aiSettings['api_key'],
                'Content-Type' => 'application/json',
            ])->get('https://api.openai.com/v1/models');
            
            if (!$response->successful()) {
                return back()->with('error', 'API Key inválida. Por favor, verifique suas credenciais.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao validar API Key: ' . $e->getMessage());
        }
        
        // Atualizar ou criar configurações no modelo AiSetting
        $settings = AiSetting::first();
        if (!$settings) {
            $settings = new AiSetting();
        }
        
        // Atualizar apenas os campos que existem no AiSetting
        $settings->api_key = $aiSettings['api_key'];
        $settings->model = $aiSettings['model'];
        $settings->system_prompt = $aiSettings['system_prompt'] ?? '';
        $settings->is_active = isset($aiSettings['is_active']);
        $settings->save();
        
        // Atualizar também no SystemSetting para manter sincronizado
        SystemSetting::set('ai_api_key', $aiSettings['api_key'], 'string', 'ai', 'API Key do ChatGPT');
        SystemSetting::set('ai_model', $aiSettings['model'], 'string', 'ai', 'Modelo do ChatGPT');
        SystemSetting::set('ai_system_prompt', $aiSettings['system_prompt'] ?? '', 'text', 'ai', 'Prompt do sistema para o ChatGPT');
        SystemSetting::set('ai_email_template_prompt', $aiSettings['email_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de email');
        SystemSetting::set('ai_whatsapp_template_prompt', $aiSettings['whatsapp_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de WhatsApp');
        SystemSetting::set('ai_contract_template_prompt', $aiSettings['contract_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de contratos');
        SystemSetting::set('ai_payment_template_prompt', $aiSettings['payment_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de pagamento');
        SystemSetting::set('ai_enrollment_template_prompt', $aiSettings['enrollment_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de inscrição');
        SystemSetting::set('ai_matriculation_template_prompt', $aiSettings['matriculation_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de matrícula');
        SystemSetting::set('ai_support_prompt', $aiSettings['support_prompt'] ?? '', 'text', 'ai', 'Prompt para suporte ao cliente');
        SystemSetting::set('ai_is_active', isset($aiSettings['is_active']), 'boolean', 'ai', 'Status de ativação do ChatGPT');
        
        // Limpar cache para garantir que as alterações sejam aplicadas imediatamente
        \Illuminate\Support\Facades\Cache::flush();
        
        return back()->with('success', 'Configurações do ChatGPT atualizadas com sucesso.');
    }
    
    public function testConnection(Request $request)
    {
        try {
            // Verificar se estamos recebendo API key e modelo no request (do formulário)
            if ($request->has('api_key') && !empty($request->api_key)) {
                $apiKey = $request->api_key;
            } else {
                // Caso contrário, usar as configurações salvas
                $settings = AiSetting::first();
                if (!$settings || !$settings->api_key) {
                    // Se não encontrar no AiSetting, tentar no SystemSetting
                    $apiKey = SystemSetting::get('ai_api_key', '');
                    if (empty($apiKey)) {
                        return response()->json(['success' => false, 'message' => 'API Key não configurada']);
                    }
                } else {
                    $apiKey = $settings->api_key;
                }
            }
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->get('https://api.openai.com/v1/models');
            
            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Conexão estabelecida com sucesso']);
            }
            
            return response()->json(['success' => false, 'message' => 'Erro ao conectar: ' . $response->json()['error']['message'] ?? 'Erro desconhecido']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao testar conexão: ' . $e->getMessage()]);
        }
    }
} 