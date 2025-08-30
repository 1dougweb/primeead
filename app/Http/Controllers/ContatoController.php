<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContatoMail;

class ContatoController extends Controller
{
    /**
     * Exibir a página de contato
     */
    public function index()
    {
        // Obter configurações da landing page
        $landingSettings = \App\Models\SystemSetting::getLandingPageSettings();
        
        // Obter configurações do WhatsApp
        $whatsappSettings = \App\Models\SystemSetting::getWhatsAppSettings();
        
        return view('contato', compact('landingSettings', 'whatsappSettings'));
    }

    /**
     * Processar o formulário de contato
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefone' => 'required|string|max:20',
            'assunto' => 'required|string|max:255',
            'mensagem' => 'required|string|max:2000',
            'termos' => 'required|accepted'
        ], [
            'nome.required' => 'O campo nome é obrigatório.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'Por favor, insira um email válido.',
            'telefone.required' => 'O campo telefone é obrigatório.',
            'assunto.required' => 'O campo assunto é obrigatório.',
            'mensagem.required' => 'O campo mensagem é obrigatório.',
            'mensagem.max' => 'A mensagem não pode ter mais de 2000 caracteres.',
            'termos.required' => 'Você deve aceitar os termos e condições.',
            'termos.accepted' => 'Você deve aceitar os termos e condições.'
        ]);

        try {
            // Dados do contato
            $dadosContato = [
                'nome' => $request->nome,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'assunto' => $request->assunto,
                'mensagem' => $request->mensagem,
                'ip_address' => $request->ip(),
                'created_at' => now()
            ];

            // Enviar email para o administrador
            Mail::to('douglaseps@gmail.com')->send(new ContatoMail($dadosContato));

            return redirect()->route('contato')->with('success', 'Sua mensagem foi enviada com sucesso! Entraremos em contato em breve.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao enviar sua mensagem. Tente novamente.'])
                        ->withInput();
        }
    }
} 