<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Models\EmailTemplate;

class EmailTemplateController extends Controller
{
    /**
     * Diretório onde os templates de email estão armazenados
     */
    protected $templatesPath;

    /**
     * Templates disponíveis e suas descrições
     */
    protected $availableTemplates = [
        'confirmacao' => [
            'name' => 'Email de Confirmação',
            'description' => 'Enviado ao cliente após inscrição',
            'file' => 'emails/confirmacao.blade.php'
        ],
        'inscricao' => [
            'name' => 'Email de Notificação',
            'description' => 'Enviado ao admin após nova inscrição',
            'file' => 'emails/inscricao.blade.php'
        ],
        'followup' => [
            'name' => 'Email de Acompanhamento',
            'description' => 'Enviado após X dias da inscrição',
            'file' => 'emails/followup.blade.php'
        ]
    ];

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->templatesPath = resource_path('views');
    }

    /**
     * Exibir página de templates de email
     */
    public function index()
    {
        // Verificar se tem permissão para gerenciar templates
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            abort(403, 'Acesso negado. Você não tem permissão para gerenciar templates de email.');
        }

        $templates = $this->availableTemplates;
        
        // Verificar quais templates existem fisicamente
        foreach ($templates as $key => &$template) {
            $filePath = $this->templatesPath . '/' . $template['file'];
            $template['exists'] = File::exists($filePath);
            
            if ($template['exists']) {
                $template['content'] = File::get($filePath);
            } else {
                $template['content'] = '';
            }
        }

        return view('admin.emails.templates.index', compact('templates'));
    }

    /**
     * Exibir formulário para editar template
     */
    public function edit($templateKey)
    {
        // Verificar se tem permissão para editar templates
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            abort(403, 'Acesso negado. Você não tem permissão para editar templates de email.');
        }

        // Verificar se o template existe
        if (!array_key_exists($templateKey, $this->availableTemplates)) {
            return redirect()->route('admin.email-templates.index')
                ->with('error', 'Template não encontrado.');
        }

        $template = $this->availableTemplates[$templateKey];
        $filePath = $this->templatesPath . '/' . $template['file'];
        
        // Verificar se o arquivo existe
        if (File::exists($filePath)) {
            $template['content'] = File::get($filePath);
        } else {
            $template['content'] = '';
        }

        $template['key'] = $templateKey;
        
        // Obter variáveis disponíveis para o template
        $availableVariables = $this->getAvailableVariables($templateKey);
        
        return view('admin.emails.templates.edit', compact('template', 'availableVariables'));
    }

    /**
     * Atualizar template
     */
    public function update(Request $request, $id)
    {
        try {
            // Validar os dados
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'content' => 'required|string'
            ]);

            // Atualizar o template
            $template = EmailTemplate::findOrFail($id);
            $template->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Template atualizado com sucesso',
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Visualizar preview do template
     */
    public function preview(Request $request, $templateKey)
    {
        // Verificar se tem permissão para visualizar templates
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            abort(403, 'Acesso negado. Você não tem permissão para visualizar templates de email.');
        }

        // Verificar se o template existe
        if (!array_key_exists($templateKey, $this->availableTemplates)) {
            return response()->json([
                'success' => false,
                'message' => 'Template não encontrado.'
            ], 404);
        }

        // Obter conteúdo do template
        $content = $request->input('content');
        
        // Criar arquivo temporário para o template
        $tempFilePath = storage_path('app/temp_email_preview_' . time() . '.blade.php');
        File::put($tempFilePath, $content);
        
        // Renderizar template com dados de exemplo
        try {
            $data = $this->getSampleData($templateKey);
            $renderedView = view('temp_email_preview', $data)->render();
            
            // Remover arquivo temporário
            File::delete($tempFilePath);
            
            return response()->json([
                'success' => true,
                'html' => $renderedView
            ]);
        } catch (\Exception $e) {
            // Remover arquivo temporário
            if (File::exists($tempFilePath)) {
                File::delete($tempFilePath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao renderizar template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar email de teste
     */
    public function sendTest(Request $request, $templateKey)
    {
        // Verificar se tem permissão para enviar teste
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            abort(403, 'Acesso negado. Você não tem permissão para enviar testes de email.');
        }

        // Verificar se o template existe
        if (!array_key_exists($templateKey, $this->availableTemplates)) {
            return response()->json([
                'success' => false,
                'message' => 'Template não encontrado.'
            ], 404);
        }

        // Validar email de destino
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email inválido.'
            ], 422);
        }

        // Obter conteúdo do template
        $content = $request->input('content');
        $email = $request->input('email');
        
        // Criar arquivo temporário para o template
        $tempFilePath = storage_path('app/temp_email_test_' . time() . '.blade.php');
        File::put($tempFilePath, $content);
        
        // Enviar email de teste
        try {
            $data = $this->getSampleData($templateKey);
            
            // Enviar email usando o template temporário
            \Mail::send('temp_email_test', $data, function ($message) use ($email, $templateKey) {
                $message->to($email)
                        ->subject('Teste de Template - ' . $this->availableTemplates[$templateKey]['name']);
            });
            
            // Remover arquivo temporário
            File::delete($tempFilePath);
            
            return response()->json([
                'success' => true,
                'message' => 'Email de teste enviado com sucesso para ' . $email
            ]);
        } catch (\Exception $e) {
            // Remover arquivo temporário
            if (File::exists($tempFilePath)) {
                File::delete($tempFilePath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar template para o padrão
     */
    public function restore($templateKey)
    {
        // Verificar se tem permissão para restaurar templates
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            abort(403, 'Acesso negado. Você não tem permissão para restaurar templates de email.');
        }

        // Verificar se o template existe
        if (!array_key_exists($templateKey, $this->availableTemplates)) {
            return redirect()->route('admin.email-templates.index')
                ->with('error', 'Template não encontrado.');
        }

        // Obter template padrão
        $defaultTemplate = $this->getDefaultTemplate($templateKey);
        
        if (!$defaultTemplate) {
            return redirect()->route('admin.email-templates.index')
                ->with('error', 'Template padrão não encontrado.');
        }

        // Salvar template
        $template = $this->availableTemplates[$templateKey];
        $filePath = $this->templatesPath . '/' . $template['file'];
        
        // Criar diretório se não existir
        $directory = dirname($filePath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        
        // Salvar arquivo
        File::put($filePath, $defaultTemplate);

        return redirect()->route('admin.email-templates.edit', $templateKey)
            ->with('success', 'Template restaurado para o padrão com sucesso!');
    }

    /**
     * Obter template padrão
     */
    protected function getDefaultTemplate($templateKey)
    {
        switch ($templateKey) {
            case 'confirmacao':
                return $this->getDefaultConfirmacaoTemplate();
            case 'inscricao':
                return $this->getDefaultInscricaoTemplate();
            case 'followup':
                return $this->getDefaultFollowupTemplate();
            default:
                return null;
        }
    }

    /**
     * Obter dados de exemplo para o template
     */
    protected function getSampleData($templateKey)
    {
        switch ($templateKey) {
            case 'confirmacao':
                return [
                    'inscricao' => (object) [
                        'nome' => 'João da Silva',
                        'email' => 'joao@exemplo.com',
                        'telefone' => '(11) 98765-4321',
                        'curso' => 'Excel Básico',
                        'modalidade' => 'Ensino Médio',
                        'created_at' => now()
                    ],
                    'settings' => [
                        'contact_phone' => '(11) 1234-5678',
                        'contact_email' => 'contato@ensinocerto.com.br',
                        'contact_hours' => 'Seg-Sex: 8h às 18h'
                    ]
                ];
            case 'inscricao':
                return [
                    'inscricao' => (object) [
                        'id' => 123,
                        'nome' => 'João da Silva',
                        'email' => 'joao@exemplo.com',
                        'telefone' => '(11) 98765-4321',
                        'curso' => 'Excel Básico',
                        'modalidade' => 'Ensino Médio',
                        'created_at' => now()
                    ]
                ];
            case 'followup':
                return [
                    'inscricao' => (object) [
                        'nome' => 'João da Silva',
                        'email' => 'joao@exemplo.com',
                        'telefone' => '(11) 98765-4321',
                        'curso' => 'Excel Básico',
                        'modalidade' => 'Ensino Médio',
                        'created_at' => now()->subDays(3)
                    ],
                    'settings' => [
                        'contact_phone' => '(11) 1234-5678',
                        'contact_email' => 'contato@ensinocerto.com.br',
                        'contact_hours' => 'Seg-Sex: 8h às 18h'
                    ]
                ];
            default:
                return [];
        }
    }

    /**
     * Obter variáveis disponíveis para o template
     */
    protected function getAvailableVariables($templateKey)
    {
        $commonVariables = [
            'inscricao.nome' => 'Nome do cliente',
            'inscricao.email' => 'Email do cliente',
            'inscricao.telefone' => 'Telefone do cliente',
            'inscricao.curso' => 'Curso selecionado',
            'inscricao.modalidade' => 'Modalidade selecionada',
            'inscricao.created_at' => 'Data da inscrição'
        ];

        $specificVariables = [];

        switch ($templateKey) {
            case 'confirmacao':
                $specificVariables = [
                    'settings.contact_phone' => 'Telefone de contato',
                    'settings.contact_email' => 'Email de contato',
                    'settings.contact_hours' => 'Horário de atendimento'
                ];
                break;
            case 'inscricao':
                $specificVariables = [
                    'inscricao.id' => 'ID da inscrição'
                ];
                break;
            case 'followup':
                $specificVariables = [
                    'settings.contact_phone' => 'Telefone de contato',
                    'settings.contact_email' => 'Email de contato',
                    'settings.contact_hours' => 'Horário de atendimento'
                ];
                break;
        }

        return array_merge($commonVariables, $specificVariables);
    }

    /**
     * Template padrão para email de confirmação
     */
    protected function getDefaultConfirmacaoTemplate()
    {
        return '@extends(\'emails.layout\')

@section(\'content\')
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #3a5998; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">Inscrição Confirmada!</h1>
    </div>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px;">
        <p>Olá <strong>{{ $inscricao->nome }}</strong>,</p>
        
        <p>Sua inscrição foi realizada com sucesso! Agradecemos seu interesse em nossos cursos.</p>
        
        <div style="background-color: white; border-left: 4px solid #3a5998; padding: 15px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Detalhes da sua inscrição:</h3>
            <p><strong>Nome:</strong> {{ $inscricao->nome }}</p>
            <p><strong>Email:</strong> {{ $inscricao->email }}</p>
            <p><strong>Telefone:</strong> {{ $inscricao->telefone }}</p>
            <p><strong>Curso:</strong> {{ $inscricao->curso }}</p>
            <p><strong>Modalidade:</strong> {{ $inscricao->modalidade }}</p>
            <p><strong>Data:</strong> {{ $inscricao->created_at->format(\'d/m/Y H:i\') }}</p>
        </div>
        
        <p>Nossa equipe entrará em contato em breve para dar continuidade ao seu processo de matrícula.</p>
        
        <div style="margin: 30px 0; padding: 15px; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;">
            <h3>Próximos passos:</h3>
            <ol style="padding-left: 20px;">
                <li>Aguarde nosso contato telefônico</li>
                <li>Prepare seus documentos pessoais</li>
                <li>Escolha a melhor forma de pagamento</li>
            </ol>
        </div>
        
        <p>Se tiver qualquer dúvida, entre em contato conosco:</p>
        <p>
            <strong>Telefone:</strong> {{ $settings[\'contact_phone\'] }}<br>
            <strong>Email:</strong> {{ $settings[\'contact_email\'] }}<br>
            <strong>Horário de atendimento:</strong> {{ $settings[\'contact_hours\'] }}
        </p>
        
        <p style="margin-top: 30px;">Atenciosamente,<br>Equipe EJA Admin</p>
    </div>
    
    <div style="text-align: center; padding: 10px; font-size: 12px; color: #666;">
        <p>Este é um email automático. Por favor, não responda.</p>
    </div>
</div>
@endsection';
    }

    /**
     * Template padrão para email de notificação de inscrição
     */
    protected function getDefaultInscricaoTemplate()
    {
        return '@extends(\'emails.layout\')

@section(\'content\')
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">Nova Inscrição Recebida!</h1>
    </div>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px;">
        <p>Uma nova inscrição foi recebida no sistema.</p>
        
        <div style="background-color: white; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Detalhes da inscrição #{{ $inscricao->id }}:</h3>
            <p><strong>Nome:</strong> {{ $inscricao->nome }}</p>
            <p><strong>Email:</strong> {{ $inscricao->email }}</p>
            <p><strong>Telefone:</strong> {{ $inscricao->telefone }}</p>
            <p><strong>Curso:</strong> {{ $inscricao->curso }}</p>
            <p><strong>Modalidade:</strong> {{ $inscricao->modalidade }}</p>
            <p><strong>Data:</strong> {{ $inscricao->created_at->format(\'d/m/Y H:i\') }}</p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url(\'/admin/inscricoes/\' . $inscricao->id . \'/show\') }}" style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Visualizar Inscrição
            </a>
        </div>
        
        <p>Lembre-se de entrar em contato com o lead o mais rápido possível para aumentar as chances de conversão.</p>
        
        <p style="margin-top: 30px;">Atenciosamente,<br>Sistema EJA Admin</p>
    </div>
    
    <div style="text-align: center; padding: 10px; font-size: 12px; color: #666;">
        <p>Este é um email automático. Por favor, não responda.</p>
    </div>
</div>
@endsection';
    }

    /**
     * Template padrão para email de acompanhamento
     */
    protected function getDefaultFollowupTemplate()
    {
        return '@extends(\'emails.layout\')

@section(\'content\')
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #17a2b8; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">Tudo pronto para começar seus estudos?</h1>
    </div>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px;">
        <p>Olá <strong>{{ $inscricao->nome }}</strong>,</p>
        
        <p>Notamos que você se inscreveu em nosso curso <strong>{{ $inscricao->curso }}</strong> há alguns dias e gostaríamos de saber se podemos ajudar com alguma dúvida sobre o processo de matrícula.</p>
        
        <div style="background-color: white; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px;">
            <h3 style="color: #17a2b8; margin-top: 0;">Por que escolher nossos cursos?</h3>
            <ul style="padding-left: 20px;">
                <li>Material didático atualizado e de qualidade</li>
                <li>Professores experientes e qualificados</li>
                <li>Horários flexíveis que se adaptam à sua rotina</li>
                <li>Certificado reconhecido pelo MEC</li>
                <li>Excelente custo-benefício</li>
            </ul>
        </div>
        
        <p>Se você ainda tem interesse em iniciar seus estudos conosco, entre em contato para finalizarmos sua matrícula e garantir sua vaga.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="tel:{{ preg_replace(\'/[^0-9]/\', \'\', $settings[\'contact_phone\']) }}" style="background-color: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">
                Ligar Agora
            </a>
            <a href="mailto:{{ $settings[\'contact_email\'] }}" style="background-color: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Enviar Email
            </a>
        </div>
        
        <p>Estamos à disposição para atendê-lo:</p>
        <p>
            <strong>Telefone:</strong> {{ $settings[\'contact_phone\'] }}<br>
            <strong>Email:</strong> {{ $settings[\'contact_email\'] }}<br>
            <strong>Horário de atendimento:</strong> {{ $settings[\'contact_hours\'] }}
        </p>
        
        <p style="margin-top: 30px;">Atenciosamente,<br>Equipe EJA Admin</p>
    </div>
    
    <div style="text-align: center; padding: 10px; font-size: 12px; color: #666;">
        <p>Se não deseja receber mais emails como este, <a href="#" style="color: #17a2b8;">clique aqui</a>.</p>
    </div>
</div>
@endsection';
    }
}
