<?php

namespace App\Http\Controllers;

use App\Models\Parceiro;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Mail\ParceiroNovoMail;
use Illuminate\Support\Facades\Mail;
use App\Models\SystemSetting;

class ParceiroController extends Controller
{
    /**
     * Página pública de cadastro de parceiros
     */
    public function create()
    {
        // Obter configurações da landing page
        $landingSettings = SystemSetting::getLandingPageSettings();
        
        // Obter configurações do WhatsApp
        $whatsappSettings = SystemSetting::getWhatsAppSettings();
        
        return view('parceiros.cadastro', compact('landingSettings', 'whatsappSettings'));
    }

    /**
     * Processar cadastro público de parceiros
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:parceiros,email',
            'whatsapp' => 'required|string|max:20',
            'cep' => 'nullable|string|size:9',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|size:2',
            'modalidade_parceria' => 'required|string|in:Polo Presencial,EaD,Híbrido,Representante',
            'possui_estrutura' => 'required|boolean',
            'plano_negocio' => 'nullable|string|max:2000',
            'tem_site' => 'required|boolean',
            'site_url' => 'nullable|url|max:255',
            'tem_experiencia_educacional' => 'required|boolean',
        ], [
            'nome_completo.required' => 'O nome completo é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'Digite um email válido.',
            'email.unique' => 'Este email já está cadastrado.',
            'whatsapp.required' => 'O WhatsApp é obrigatório.',
            'cep.size' => 'O CEP deve ter 9 caracteres.',
            'estado.size' => 'Estado inválido.',
            'modalidade_parceria.required' => 'A modalidade da parceria é obrigatória.',
            'modalidade_parceria.in' => 'Modalidade de parceria inválida.',
            'possui_estrutura.required' => 'Informe se possui estrutura.',
            'tem_site.required' => 'Informe se tem site.',
            'site_url.url' => 'Digite uma URL válida.',
            'tem_experiencia_educacional.required' => 'Informe se tem experiência na área educacional.',
        ]);

        // Limpar dados
        if (isset($validated['whatsapp'])) {
            $validated['whatsapp'] = preg_replace('/\D/', '', $validated['whatsapp']);
        }
        
        if (isset($validated['cep'])) {
            $validated['cep'] = preg_replace('/\D/', '', $validated['cep']);
        }

        // Se não tem site, limpar URL
        if (!$validated['tem_site']) {
            $validated['site_url'] = null;
        }

        // Definir status inicial
        $validated['status'] = 'pendente';

        $parceiro = Parceiro::create($validated);

        // Enviar email de notificação para admin
        try {
            Mail::to(config('mail.admin_email', 'admin@ensinocerto.com.br'))
                ->send(new ParceiroNovoMail($parceiro));
        } catch (\Exception $e) {
            // Log do erro mas não impede o cadastro
            \Log::error('Erro ao enviar email de novo parceiro: ' . $e->getMessage());
        }

        // Enviar mensagem de boas-vindas via WhatsApp
        try {
            $whatsappService = app(\App\Services\WhatsAppService::class);
            if ($whatsappService->hasValidSettings()) {
                $whatsappService->sendParceiroBoasVindas($parceiro);
                \Log::info('Mensagem de boas-vindas WhatsApp enviada para parceiro: ' . $parceiro->id);
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar mensagem WhatsApp para parceiro ' . $parceiro->id . ': ' . $e->getMessage());
            // Não impedir o fluxo se o WhatsApp falhar
        }

        return redirect()->route('parceiros.sucesso');
    }

    /**
     * Página de sucesso após cadastro
     */
    public function sucesso()
    {
        return view('parceiros.sucesso');
    }

    /**
     * Buscar CEP via API
     */
    public function buscarCep(string $cep): JsonResponse
    {
        $cep = preg_replace('/\D/', '', $cep);
        
        if (strlen($cep) !== 8) {
            return response()->json(['error' => 'CEP inválido'], 400);
        }

        try {
            $response = file_get_contents("https://viacep.com.br/ws/{$cep}/json/");
            $data = json_decode($response, true);
            
            if (isset($data['erro'])) {
                return response()->json(['error' => 'CEP não encontrado'], 404);
            }
            
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar CEP'], 500);
        }
    }

    /**
     * Exibe o dashboard do parceiro
     */
    public function dashboard()
    {
        return view('parceiros.dashboard');
    }
}
