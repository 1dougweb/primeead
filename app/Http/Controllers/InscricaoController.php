<?php

namespace App\Http\Controllers;

use App\Http\Requests\InscricaoRequest;
use App\Models\Inscricao;
use App\Mail\InscricaoMail;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class InscricaoController extends Controller
{
    /**
     * Exibir a landing page
     */
    public function index()
    {
        // Obter configurações de formulário
        $formSettings = \App\Models\SystemSetting::getFormSettings();
        
        // Usar os valores diretamente, pois já estão decodificados pelo método castValue
        $availableCourses = $formSettings['available_courses'] ?? [];
        $availableModalities = $formSettings['available_modalities'] ?? [];
        $defaultCourse = $formSettings['default_course'];
        $defaultModality = $formSettings['default_modality'];
        
        // Obter configurações do WhatsApp
        $whatsappSettings = \App\Models\SystemSetting::getWhatsAppSettings();
        
        // Obter configurações do countdown
        $countdownSettings = \App\Models\SystemSetting::getCountdownSettings();
        
        // Obter configurações da landing page
        $landingSettings = \App\Models\SystemSetting::getLandingPageSettings();
        
        // Verificar e renovar countdown se necessário
        \App\Models\SystemSetting::checkAndRenewCountdown();
        
        return view('welcome', compact(
            'availableCourses',
            'availableModalities',
            'defaultCourse',
            'defaultModality',
            'whatsappSettings',
            'countdownSettings',
            'landingSettings'
        ));
    }

    /**
     * Processar o formulário de inscrição
     */
    public function store(InscricaoRequest $request)
    {
        try {
            // Criar a inscrição no banco de dados
            $inscricao = Inscricao::create([
                'nome' => $request->nome,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'curso' => $request->curso,
                'modalidade' => $request->modalidade ?? 'online',
                'termos' => $request->has('termos'),
                'ip_address' => $request->ip(),
            ]);

            // Obter configurações de formulário para os labels
            $formSettings = \App\Models\SystemSetting::getFormSettings();
            $availableCourses = $formSettings['available_courses'] ?? [];
            $availableModalities = $formSettings['available_modalities'] ?? [];
            
            // Adicionar os labels ao objeto de inscrição para o email
            $inscricao->curso_label = $availableCourses[$inscricao->curso] ?? $inscricao->curso;
            $inscricao->modalidade_label = $availableModalities[$inscricao->modalidade] ?? $inscricao->modalidade;

            // Enviar email para o administrador
            Mail::to('douglaseps@gmail.com')->send(new InscricaoMail($inscricao, 'admin'));
            
            // Enviar email para o aluno
            Mail::to($inscricao->email)->send(new InscricaoMail($inscricao, 'aluno'));

            // Tentar enviar mensagem de WhatsApp
            try {
                $whatsappService = app(WhatsAppService::class);
                if ($whatsappService->hasValidSettings()) {
                    $whatsappService->sendInscricaoConfirmation($inscricao);
                    Log::info('Mensagem de confirmação WhatsApp enviada para inscrição: ' . $inscricao->id);
                }
            } catch (\Exception $e) {
                Log::error('Erro ao enviar mensagem WhatsApp para inscrição ' . $inscricao->id . ': ' . $e->getMessage());
                // Não impedir o fluxo se o WhatsApp falhar
            }

            // Redirecionar para página de agradecimento
            return redirect()->route('obrigado')->with([
                'success' => 'Inscrição realizada com sucesso! Entraremos em contato em breve.',
                'inscricao' => $inscricao
            ]);

        } catch (\Exception $e) {
            // Em caso de erro, voltar com mensagem de erro
            return back()->withErrors(['error' => 'Erro ao processar sua inscrição. Tente novamente.'])
                        ->withInput();
        }
    }

    /**
     * Exibir página de agradecimento
     */
    public function obrigado()
    {
        // Obter dados da sessão
        $inscricao = session('inscricao');
        
        // Obter configurações de formulário se a inscrição não tiver os labels
        if ($inscricao && (!isset($inscricao->curso_label) || !isset($inscricao->modalidade_label))) {
            $formSettings = \App\Models\SystemSetting::getFormSettings();
            $availableCourses = $formSettings['available_courses'] ?? [];
            $availableModalities = $formSettings['available_modalities'] ?? [];
            
            $inscricao->curso_label = $availableCourses[$inscricao->curso] ?? $inscricao->curso;
            $inscricao->modalidade_label = $availableModalities[$inscricao->modalidade] ?? $inscricao->modalidade;
        }
        
        // Limpar o cache para garantir que as configurações mais recentes sejam carregadas
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_page_title");
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_page_subtitle");
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_header_color");
        
        // Obter configurações da página de agradecimento
        $pageSettings = \App\Models\SystemSetting::getThankYouPageSettings();
        
        return view('obrigado', compact('inscricao', 'pageSettings'));
    }
}
