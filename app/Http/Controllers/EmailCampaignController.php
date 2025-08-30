<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\Inscricao;
use App\Models\User;
use App\Jobs\SendCampaignEmail;
use App\Services\ChatGptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log; // Added Log facade
use App\Models\EmailTemplate;

class EmailCampaignController extends Controller
{
    /**
     * Construtor do controlador.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin_or_media');
    }

    /**
     * Exibir lista de campanhas de email.
     */
    public function index()
    {
        // Obter todas as campanhas ordenadas por data de criação decrescente
        $campaigns = EmailCampaign::orderBy('created_at', 'desc')->get();
        
        return view('admin.email-campaigns.index', compact('campaigns'));
    }

    /**
     * Exibir formulário para criar uma nova campanha (Passo 1).
     */
    public function create(Request $request)
    {
        // Verificar se foi passado um template específico
        $selectedTemplate = null;
        if ($request->has('template')) {
            $templateId = $request->get('template');
            
            // Verificar se é template do banco de dados
            if (is_numeric($templateId)) {
                $selectedTemplate = EmailTemplate::find($templateId);
            }
        }
        
        // Obter apenas templates salvos no banco de dados (sem templates pré-definidos)
        $templates = EmailTemplate::orderBy('created_at', 'desc')->get()->map(function($template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'subject' => $template->subject,
                'content' => $template->content
            ];
        })->toArray();
        
        // Obter templates salvos do banco de dados
        $savedTemplates = EmailTemplate::orderBy('created_at', 'desc')->get();
        
        return view('admin.email-campaigns.create', compact('templates', 'savedTemplates', 'selectedTemplate'));
    }

    /**
     * Processar Passo 1 e ir para Passo 2 (Seleção de Leads).
     */
    public function createStep2(Request $request)
    {
        // Log do conteúdo recebido do formulário
        Log::info('Conteúdo recebido do formulário:', [
            'content' => substr($request->content, 0, 500)
        ]);

        // Validar dados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.email-campaigns.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Armazenar dados na sessão
        session([
            'campaign_data' => [
                'name' => $request->name,
                'subject' => $request->subject,
                'content' => $request->content,
            ]
        ]);

        // Log do conteúdo após armazenar na sessão
        Log::info('Conteúdo armazenado na sessão:', [
            'content' => substr(session('campaign_data.content'), 0, 500)
        ]);

        // Obter contagem de leads por etiqueta
        $leadsByStatus = Inscricao::select('etiqueta', DB::raw('count(*) as total'))
            ->groupBy('etiqueta')
            ->pluck('total', 'etiqueta')
            ->toArray();

        // Garantir que todas as etiquetas possíveis existam no array
        $allStatus = [
            'pendente' => 0,
            'contatado' => 0,
            'interessado' => 0,
            'nao_interessado' => 0,
            'matriculado' => 0
        ];

        // Mesclar as contagens reais com os valores padrão
        $leadsByStatus = array_merge($allStatus, $leadsByStatus);

        // Obter contagem de leads por curso
        $leadsByCourse = Inscricao::select('curso', DB::raw('count(*) as total'))
            ->whereNotNull('curso')
            ->groupBy('curso')
            ->pluck('total', 'curso')
            ->toArray();

        // Obter contagem de leads por modalidade
        $leadsByModality = Inscricao::select('modalidade', DB::raw('count(*) as total'))
            ->whereNotNull('modalidade')
            ->groupBy('modalidade')
            ->pluck('total', 'modalidade')
            ->toArray();

        return view('admin.email-campaigns.create-step2', [
            'cursos' => Inscricao::distinct('curso')->pluck('curso')->filter()->values(),
            'modalidades' => Inscricao::distinct('modalidade')->pluck('modalidade')->filter()->values(),
            'leadStats' => [
                'total' => Inscricao::count(),
                'by_status' => $leadsByStatus,
                'by_course' => $leadsByCourse,
                'by_modality' => $leadsByModality
            ]
        ]);
    }

    /**
     * Processar Passo 2 e ir para Passo 3 (Preview e Confirmação).
     */
    public function createStep3(Request $request)
    {
        // Validar seleção de destinatários
        $validator = Validator::make($request->all(), [
            'selection_type' => 'required|in:all,status,course,modality,custom',
            'status' => 'required_if:selection_type,status',
            'course' => 'required_if:selection_type,course',
            'modality' => 'required_if:selection_type,modality',
            'custom_filters' => 'required_if:selection_type,custom|array'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.email-campaigns.create-step2')
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar se os dados da campanha existem na sessão
        if (!session()->has('campaign_data')) {
            return redirect()
                ->route('admin.email-campaigns.create')
                ->with('error', 'Dados da campanha não encontrados. Por favor, comece novamente.');
        }

        // Obter leads baseado na seleção
        $query = Inscricao::query();

        switch ($request->selection_type) {
            case 'all':
                // Não aplicar filtros
                break;
            case 'status':
                $query->where('etiqueta', $request->status);
                break;
            case 'course':
                $query->where('curso', $request->course);
                break;
            case 'modality':
                $query->where('modalidade', $request->modality);
                break;
            case 'custom':
                if (!empty($request->custom_filters['status'])) {
                    $query->where('etiqueta', $request->custom_filters['status']);
                }
                if (!empty($request->custom_filters['curso'])) {
                    $query->where('curso', $request->custom_filters['curso']);
                }
                if (!empty($request->custom_filters['modalidade'])) {
                    $query->where('modalidade', $request->custom_filters['modalidade']);
                }
                if (!empty($request->custom_filters['created_from'])) {
                    $query->whereDate('created_at', '>=', $request->custom_filters['created_from']);
                }
                break;
        }

        $selectedLeads = $query->get();

        // Armazenar leads selecionados na sessão
        session(['selected_leads' => $selectedLeads]);

        // Obter dados da campanha
        $campaignData = session('campaign_data');

        // Preparar dados de seleção
        $selectionData = [
            'selection_type' => $request->selection_type,
            'status' => $request->status,
            'course' => $request->course,
            'modality' => $request->modality,
            'custom_filters' => $request->custom_filters
        ];

        return view('admin.email-campaigns.create-step3', [
            'campaignData' => $campaignData,
            'selectedLeads' => $selectedLeads,
            'totalLeads' => $selectedLeads->count(),
            'selectionData' => $selectionData
        ]);
    }

    /**
     * Finalizar criação da campanha.
     */
    public function createFinish(Request $request)
    {
        // Verificar se todos os dados necessários estão na sessão
        if (!session()->has('campaign_data') || !session()->has('selected_leads')) {
            return redirect()
                ->route('admin.email-campaigns.create')
                ->with('error', 'Dados da campanha não encontrados. Por favor, comece novamente.');
        }

        $campaignData = session('campaign_data');
        $selectedLeads = session('selected_leads');

        try {
            DB::beginTransaction();

            // Log do conteúdo antes de salvar
            Log::info('Conteúdo do template antes de salvar:', [
                'content' => substr($campaignData['content'], 0, 500)
            ]);

            // Criar a campanha
            $campaign = EmailCampaign::create([
                'name' => $campaignData['name'],
                'subject' => $campaignData['subject'],
                'content' => $campaignData['content'], // O conteúdo já está decodificado desde o createStep2
                'status' => 'draft',
                'total_recipients' => count($selectedLeads),
                'sent_count' => 0,
                'opened_count' => 0,
                'clicked_count' => 0,
                'failed_count' => 0,
                'created_by' => auth()->id()
            ]);

            // Log do conteúdo após salvar
            Log::info('Conteúdo do template após salvar:', [
                'content' => substr($campaign->content, 0, 500)
            ]);

            // Adicionar destinatários
            foreach ($selectedLeads as $lead) {
                $customFields = [
                    'telefone' => $lead->telefone,
                    'curso' => $lead->curso,
                    'modalidade' => $lead->modalidade
                ];

                EmailCampaignRecipient::create([
                    'campaign_id' => $campaign->id,
                    'email' => $lead->email,
                    'name' => $lead->nome,
                    'custom_fields' => $customFields,
                    'status' => 'pending',
                    'tracking_code' => Str::random(32)
                ]);
            }

            DB::commit();

            // Limpar dados da sessão
            session()->forget(['campaign_data', 'selected_leads']);

            return redirect()
                ->route('admin.email-campaigns.show', $campaign->id)
                ->with('success', 'Campanha criada com sucesso! Você pode enviar emails de teste antes de iniciar o envio.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.email-campaigns.create')
                ->with('error', 'Erro ao criar campanha: ' . $e->getMessage());
        }
    }

    /**
     * Armazenar uma nova campanha (método legado para compatibilidade).
     */
    public function store(Request $request)
    {
        // Redirecionar para o novo processo de passos
        return redirect()->route('admin.email-campaigns.create');
    }

    /**
     * Exibir detalhes da campanha.
     */
    public function show($id, Request $request)
    {
        $campaign = EmailCampaign::with(['recipients' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        // Se for uma requisição AJAX, retornar apenas os dados de progresso
        if ($request->ajax()) {
            return response()->json([
                'status' => $campaign->status,
                'total_recipients' => $campaign->total_recipients,
                'sent_count' => $campaign->sent_count,
                'failed_count' => $campaign->failed_count,
                'pending_count' => $campaign->pending_count,
                'opened_count' => $campaign->opened_count,
                'clicked_count' => $campaign->clicked_count
            ]);
        }

        // Obter destinatários paginados
        $recipients = $campaign->recipients()->paginate(50);
        
        return view('admin.email-campaigns.show', compact('campaign', 'recipients'));
    }

    /**
     * Exibir formulário para editar uma campanha.
     */
    public function edit($id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        
        // Verificar se a campanha pode ser editada
        if (!$campaign->canEdit()) {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'Esta campanha não pode ser editada porque já foi enviada ou está em andamento.');
        }
        
        $recipients = $campaign->recipients()->paginate(10);
        
        return view('admin.email-campaigns.edit', compact('campaign', 'recipients'));
    }

    /**
     * Atualizar uma campanha.
     */
    public function update(Request $request, $id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        
        // Verificar se a campanha pode ser editada
        if (!$campaign->canEdit()) {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'Esta campanha não pode ser editada porque já foi enviada ou está em andamento.');
        }
        
        // Validar dados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Atualizar campanha
        $campaign->name = $request->name;
        $campaign->subject = $request->subject;
        $campaign->content = $request->content;
        
        // Se tiver uma data agendada
        if ($request->has('scheduled_at') && !empty($request->scheduled_at)) {
            $campaign->status = 'scheduled';
            $campaign->scheduled_at = $request->scheduled_at;
        }
        
        $campaign->save();

        return redirect()->back()
            ->with('success', 'Campanha atualizada com sucesso!');
    }

    /**
     * Remover uma campanha.
     */
    public function destroy($id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        
        // Verificar se a campanha pode ser excluída (apenas rascunhos ou agendadas)
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'Apenas campanhas em rascunho ou agendadas podem ser excluídas.');
        }
        
        // Excluir campanha e destinatários (cascade)
        $campaign->delete();

        return redirect()->route('admin.email-campaigns.index')
            ->with('success', 'Campanha excluída com sucesso!');
    }
    
    /**
     * Adicionar destinatários à campanha.
     */
    public function addRecipients(Request $request, $id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        
        // Verificar se a campanha pode ser editada
        if (!$campaign->canEdit()) {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'Esta campanha não pode ser editada porque já foi enviada ou está em andamento.');
        }
        
        // Validar dados
        $validator = Validator::make($request->all(), [
            'recipient_type' => 'required|in:manual,leads,all_leads',
            'emails' => 'required_if:recipient_type,manual',
            'lead_status' => 'required_if:recipient_type,leads',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();
            
            $recipientCount = 0;
            
            // Adicionar destinatários com base no tipo
            if ($request->recipient_type === 'manual') {
                // Adicionar emails manualmente
                $emails = explode(',', $request->emails);
                
                foreach ($emails as $email) {
                    $email = trim($email);
                    
                    // Verificar se o email é válido
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        // Verificar se o email já existe na campanha
                        $exists = EmailCampaignRecipient::where('campaign_id', $campaign->id)
                            ->where('email', $email)
                            ->exists();
                            
                        if (!$exists) {
                            EmailCampaignRecipient::create([
                                'campaign_id' => $campaign->id,
                                'email' => $email,
                                'status' => 'pending',
                                'tracking_code' => EmailCampaignRecipient::generateTrackingCode()
                            ]);
                            
                            $recipientCount++;
                        }
                    }
                }
            } elseif ($request->recipient_type === 'leads') {
                // Adicionar leads com base na etiqueta
                $leads = Inscricao::where('etiqueta', $request->lead_status)->get();
                
                foreach ($leads as $lead) {
                    // Verificar se o email já existe na campanha
                    $exists = EmailCampaignRecipient::where('campaign_id', $campaign->id)
                        ->where('email', $lead->email)
                        ->exists();
                        
                    if (!$exists) {
                        EmailCampaignRecipient::create([
                            'campaign_id' => $campaign->id,
                            'email' => $lead->email,
                            'name' => $lead->nome,
                            'custom_fields' => [
                                'lead_id' => $lead->id,
                                'telefone' => $lead->telefone,
                                'curso' => $lead->curso,
                                'modalidade' => $lead->modalidade
                            ],
                            'status' => 'pending',
                            'tracking_code' => EmailCampaignRecipient::generateTrackingCode()
                        ]);
                        
                        $recipientCount++;
                    }
                }
            } elseif ($request->recipient_type === 'all_leads') {
                // Adicionar todos os leads
                $leads = Inscricao::all();
                
                foreach ($leads as $lead) {
                    // Verificar se o email já existe na campanha
                    $exists = EmailCampaignRecipient::where('campaign_id', $campaign->id)
                        ->where('email', $lead->email)
                        ->exists();
                        
                    if (!$exists) {
                        EmailCampaignRecipient::create([
                            'campaign_id' => $campaign->id,
                            'email' => $lead->email,
                            'name' => $lead->nome,
                            'custom_fields' => [
                                'lead_id' => $lead->id,
                                'telefone' => $lead->telefone,
                                'curso' => $lead->curso,
                                'modalidade' => $lead->modalidade
                            ],
                            'status' => 'pending',
                            'tracking_code' => EmailCampaignRecipient::generateTrackingCode()
                        ]);
                        
                        $recipientCount++;
                    }
                }
            }
            
            // Atualizar contagem de destinatários
            $campaign->updateCounts();
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', $recipientCount . ' destinatários adicionados com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Erro ao adicionar destinatários: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Remover um destinatário da campanha.
     */
    public function removeRecipient($campaignId, $recipientId)
    {
        $campaign = EmailCampaign::findOrFail($campaignId);
        
        // Verificar se a campanha pode ser editada
        if (!$campaign->canEdit()) {
            return redirect()->route('admin.email-campaigns.index')
                ->with('error', 'Esta campanha não pode ser editada porque já foi enviada ou está em andamento.');
        }
        
        // Remover destinatário
        $recipient = EmailCampaignRecipient::where('campaign_id', $campaignId)
            ->where('id', $recipientId)
            ->firstOrFail();
            
        $recipient->delete();
        
        // Atualizar contagem de destinatários
        $campaign->updateCounts();
        
        return redirect()->back()
            ->with('success', 'Destinatário removido com sucesso!');
    }
    
    /**
     * Enviar campanha de teste.
     */
    public function sendTest(Request $request, $id)
    {
        // Validar email de teste
        $validator = Validator::make($request->all(), [
            'test_email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email inválido'
            ]);
        }

        try {
            // Buscar campanha
            $campaign = EmailCampaign::findOrFail($id);

            // Dados de teste
            $testData = [
                'nome' => 'Usuário Teste',
                'email' => $request->test_email,
                'telefone' => '(00) 00000-0000',
                'curso' => 'Curso Teste',
                'modalidade' => 'Modalidade Teste',
                'campanha' => $campaign->name,
                'tracking_code' => 'test_' . Str::random(32)
            ];

            // Processar conteúdo do email
            $content = $this->processEmailContent($campaign->content, $testData);

            // Configurar e enviar email
            $this->configureMailSettings();

            Mail::html($content, function ($message) use ($campaign, $request) {
                $message->to($request->test_email)
                    ->subject($campaign->subject);
            });

            return response()->json([
                'success' => true,
                'message' => 'Email de teste enviado com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar email de teste: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Enviar campanha.
     */
    public function send($id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        // Verificar se a campanha pode ser enviada
        if (!$campaign->canSend()) {
            return redirect()->back()->with('error', 'Esta campanha não pode ser enviada.');
        }

        try {
            DB::beginTransaction();

            // Obter destinatários pendentes
            $recipients = $campaign->recipients()
                ->where('status', 'pending')
                ->get();

            // Atualizar status da campanha e contadores iniciais
            $campaign->update([
                'status' => 'sending',
                'started_at' => now(),
                'total_recipients' => $recipients->count(),
                'sent_count' => 0,
                'failed_count' => 0,
                'pending_count' => $recipients->count(),
                'opened_count' => 0,
                'clicked_count' => 0
            ]);

            // Processar em lotes de 10
            $batchSize = 10;
            foreach ($recipients->chunk($batchSize) as $batch) {
                foreach ($batch as $recipient) {
                    SendCampaignEmail::dispatch($campaign, $recipient);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.email-campaigns.show', $campaign->id)
                ->with('success', 'Campanha iniciada com sucesso! Os emails serão enviados em lotes.');

        } catch (\Exception $e) {
            DB::rollback();
            
            // Registrar erro
            \Log::error('Erro ao enviar campanha: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Erro ao enviar campanha: ' . $e->getMessage());
        }
    }
    
    /**
     * Cancelar campanha agendada.
     */
    public function cancel($id)
    {
        $campaign = EmailCampaign::findOrFail($id);

        if (!$campaign->canCancel()) {
            return redirect()->back()->with('error', 'Esta campanha não pode ser cancelada.');
        }

        try {
            DB::beginTransaction();
            
            // Atualizar status da campanha
            $campaign->update([
                'status' => 'canceled',
                'completed_at' => now()
            ]);
            
            DB::commit();
            
            return redirect()
                ->route('admin.email-campaigns.show', $campaign->id)
                ->with('success', 'Campanha cancelada com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->back()
                ->with('error', 'Erro ao cancelar campanha: ' . $e->getMessage());
        }
    }
    
    /**
     * Exibir galeria de templates de email.
     */
    public function templateGallery()
    {
        // Templates pré-definidos (mesmos do método create)
        $templates = [
            [
                'id' => 'welcome',
                'name' => 'Boas-vindas',
                'description' => 'Template de boas-vindas para novos leads',
                'subject' => 'Bem-vindo(a) à nossa plataforma!',
                'category' => 'welcome',
                'thumbnail' => 'welcome-template.jpg'
            ],
            [
                'id' => 'followup',
                'name' => 'Follow-up',
                'description' => 'Template para acompanhamento de leads',
                'subject' => 'Não perca essa oportunidade!',
                'category' => 'followup',
                'thumbnail' => 'followup-template.jpg'
            ],
            [
                'id' => 'promotional',
                'name' => 'Promocional',
                'description' => 'Template para ofertas e promoções',
                'subject' => 'Oferta especial só para você!',
                'category' => 'promotional',
                'thumbnail' => 'promo-template.jpg'
            ],
            [
                'id' => 'simple',
                'name' => 'Simples',
                'description' => 'Template simples e limpo',
                'subject' => 'Notificação importante',
                'category' => 'notification',
                'thumbnail' => 'simple-template.jpg'
            ]
        ];
        
        return view('admin.email-campaigns.template-gallery', compact('templates'));
    }
    
    /**
     * Exibir galeria de templates
     */
    public function templates()
    {
        // Obter apenas templates do banco de dados
        $savedTemplates = EmailTemplate::orderBy('created_at', 'desc')->get();
        
        // Array vazio para manter compatibilidade com a view
        $predefinedTemplates = [];
        
        return view('admin.email-campaigns.templates.index', compact('savedTemplates', 'predefinedTemplates'));
    }

    /**
     * Exibir formulário para criar novo template
     */
    public function createTemplate()
    {
        return view('admin.email-campaigns.templates.create');
    }

    /**
     * Armazenar novo template
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string|in:welcome,followup,promotional,informational,invitation,reminder,thank_you,custom',
            'type' => 'required|string|in:marketing,transactional,newsletter,automation,custom'
        ]);

        $template = EmailTemplate::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'description' => $request->description,
            'content' => $request->content,
            'category' => $request->category,
            'type' => $request->type,
            'is_ai_generated' => $request->boolean('is_ai_generated', false),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id()
        ]);

            return redirect()->route('admin.email-campaigns.templates')
            ->with('success', 'Template criado com sucesso!');
        }

    /**
     * Exibir formulário de edição do template
     */
    public function editTemplate($id)
    {
        // Verificar se é template do banco de dados
        if (is_numeric($id)) {
            $template = EmailTemplate::findOrFail($id);
        return view('admin.email-campaigns.templates.edit', compact('template'));
        }

        // Se não for numérico, é um template que não existe mais
        return redirect()->route('admin.email-campaigns.templates')
            ->with('error', 'Template não encontrado.');
    }

    /**
     * Atualizar um template
     */
    public function updateTemplate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'content' => 'required|string',
            'category' => 'required|string|in:welcome,followup,promotional,informational,invitation,reminder,thank_you,custom',
            'type' => 'required|string|in:marketing,transactional,newsletter,automation,custom'
        ]);

        // Só atualizar templates do banco de dados
        if (!is_numeric($id)) {
            return redirect()->route('admin.email-campaigns.templates')
                ->with('error', 'Template não encontrado.');
        }

        $template = EmailTemplate::findOrFail($id);
        
        $template->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'description' => $request->description,
            'content' => $request->content,
            'category' => $request->category,
            'type' => $request->type,
            'updated_by' => auth()->id()
        ]);
        
        return redirect()->route('admin.email-campaigns.templates')
            ->with('success', 'Template atualizado com sucesso!');
    }

    /**
     * Excluir template
     */
    public function destroyTemplate($id)
    {
        // Só permitir exclusão de templates do banco de dados
        if (!is_numeric($id)) {
            return redirect()->route('admin.email-campaigns.templates')
                ->with('error', 'Template não encontrado.');
        }

        $template = EmailTemplate::findOrFail($id);
        $template->delete();
        
        return redirect()->route('admin.email-campaigns.templates')
            ->with('success', 'Template excluído com sucesso!');
    }

    /**
     * Gerar template com IA
     */
    public function generateTemplateWithAi(Request $request)
    {
        $request->validate([
            'template_type' => 'required|string|in:welcome,followup,promotional,informational,invitation,reminder,thank_you',
            'objective' => 'required|string|max:500',
            'target_audience' => 'required|string|max:300',
            'additional_instructions' => 'nullable|string|max:1000'
        ]);

        try {
            $chatGptService = new \App\Services\ChatGptService();
            
            $result = $chatGptService->generateEmailMarketingTemplate(
                $request->template_type,
                $request->objective,
                $request->target_audience,
                $request->additional_instructions
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

            // Criar template no banco de dados
            $template = EmailTemplate::create([
                'name' => 'Template IA - ' . ucfirst($request->template_type) . ' - ' . date('d/m/Y H:i'),
                'subject' => $result['subject'],
                'description' => 'Template gerado automaticamente pela IA baseado em: ' . $request->objective,
                'content' => $result['content'],
                'category' => $request->template_type,
                'type' => 'marketing',
                'is_ai_generated' => true,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template gerado com sucesso!',
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'subject' => $template->subject,
                    'content' => $template->content
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao gerar template com IA: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar thumbnail para o template
     */
    protected function generateThumbnail($templateId)
    {
        // Caminho base para as thumbnails
        $thumbnailPath = '/assets/images/templates/';
        
        // Verificar se existe uma thumbnail específica
        $specificThumbnail = $thumbnailPath . $templateId . '-thumb.png';
        if (file_exists(public_path($specificThumbnail))) {
            return $specificThumbnail;
        }
        
        // Retornar placeholder se não houver thumbnail
        return $thumbnailPath . 'placeholder.svg';
    }

    /**
     * Template de boas-vindas
     */
    protected function getWelcomeTemplate()
    {
        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap");
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Inter", Arial, sans-serif; line-height: 1.6; color: #333; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h1 style="color: #333; margin-bottom: 20px;">Olá, {{nome}}!</h1>
            <p style="margin-bottom: 20px;">Seja bem-vindo(a) ao nosso curso {{curso}} na modalidade {{modalidade}}.</p>
            <p style="margin-bottom: 20px;">Estamos muito felizes em ter você conosco!</p>
            <p style="margin-top: 30px; color: #666; font-size: 14px;">Atenciosamente,<br>Equipe de Suporte</p>
        </div>
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 12px;">
            <p>Este email foi enviado para {{email}}</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Template de follow-up
     */
    protected function getFollowupTemplate()
    {
        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Follow-up</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap");
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Inter", Arial, sans-serif; line-height: 1.6; color: #333; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h1 style="color: #333; margin-bottom: 20px;">Olá, {{nome}}!</h1>
            <p style="margin-bottom: 20px;">Notamos que você demonstrou interesse no curso {{curso}}.</p>
            <p style="margin-bottom: 20px;">Que tal aproveitar nossas condições especiais?</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="#" style="display: inline-block; padding: 12px 24px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    Saiba Mais
                </a>
            </div>
            <p style="margin-top: 30px; color: #666; font-size: 14px;">Atenciosamente,<br>Equipe de Vendas</p>
        </div>
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 12px;">
            <p>Este email foi enviado para {{email}}</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Template promocional
     */
    protected function getPromotionalTemplate()
    {
        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oferta Especial</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap");
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Inter", Arial, sans-serif; line-height: 1.6; color: #333; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h1 style="color: #333; margin-bottom: 20px;">Oferta Especial!</h1>
            <p style="margin-bottom: 20px;">Olá, {{nome}}!</p>
            <p style="margin-bottom: 20px;">Preparamos uma oferta imperdível para você:</p>
            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h2 style="color: #e63946; margin-bottom: 10px;">50% OFF</h2>
                <p>No curso {{curso}} na modalidade {{modalidade}}</p>
            </div>
            <div style="text-align: center; margin: 30px 0;">
                <a href="#" style="display: inline-block; padding: 12px 24px; background-color: #e63946; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    Aproveitar Agora
                </a>
            </div>
            <p style="margin-top: 30px; color: #666; font-size: 14px;">Atenciosamente,<br>Equipe Comercial</p>
        </div>
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 12px;">
            <p>Este email foi enviado para {{email}}</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Template simples
     */
    protected function getSimpleTemplate()
    {
        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificação</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap");
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Inter", Arial, sans-serif; line-height: 1.6; color: #333; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h1 style="color: #333; margin-bottom: 20px;">Olá, {{nome}}!</h1>
            <p style="margin-bottom: 20px;">Temos uma atualização importante sobre o curso {{curso}} na modalidade {{modalidade}}.</p>
            <p style="margin-bottom: 20px;">Para mais informações, entre em contato conosco pelo telefone {{telefone}}.</p>
            <p style="margin-top: 30px; color: #666; font-size: 14px;">Atenciosamente,<br>Equipe de Suporte</p>
        </div>
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 12px;">
            <p>Este email foi enviado para {{email}}</p>
        </div>
    </div>
</body>
</html>';
    }
    
    /**
     * Retornar o conteúdo de um template específico.
     */
    public function getTemplate($id)
    {
        // Verificar se é template do banco de dados
        if (is_numeric($id)) {
            $template = EmailTemplate::find($id);
            if (!$template) {
            return response()->json(['error' => 'Template não encontrado'], 404);
        }

            return response()->json([
                'id' => $template->id,
                'name' => $template->name,
                'subject' => $template->subject,
                'description' => $template->description,
                'content' => $template->content,
                'category' => $template->category,
                'type' => $template->type,
                'is_ai_generated' => $template->is_ai_generated
            ]);
        }

        // Se não for numérico, template não existe
        return response()->json(['error' => 'Template não encontrado'], 404);
    }
    
    /**
     * Rastrear abertura de email.
     */
    public function trackOpen($trackingCode)
    {
        $recipient = EmailCampaignRecipient::where('tracking_code', $trackingCode)->first();
        
        if ($recipient) {
            $recipient->markOpened();
            $recipient->campaign->updateCounts();
        }
        
        // Retornar uma imagem transparente de 1x1 pixel
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($pixel, 200)->header('Content-Type', 'image/gif');
    }
    
    /**
     * Rastrear cliques em links.
     */
    public function trackClick($trackingCode, $url)
    {
        $recipient = EmailCampaignRecipient::where('tracking_code', $trackingCode)->first();
        
        if ($recipient) {
            $recipient->markClicked();
            $recipient->campaign->updateCounts();
        }
        
        // Redirecionar para a URL original
        return redirect()->away(base64_decode($url));
    }
    
    /**
     * Cancelar inscrição (unsubscribe).
     */
    public function unsubscribe($trackingCode)
    {
        $recipient = EmailCampaignRecipient::where('tracking_code', $trackingCode)->first();
        
        if (!$recipient) {
            return view('admin.email-campaigns.unsubscribe', [
                'success' => false,
                'message' => 'Código de rastreamento inválido.'
            ]);
        }
        
        // Marcar como cancelado
        $recipient->status = 'unsubscribed';
        $recipient->save();
        
        return view('admin.email-campaigns.unsubscribe', [
            'success' => true,
            'email' => $recipient->email,
            'message' => 'Você foi removido da nossa lista de emails com sucesso.'
        ]);
    }

    /**
     * Configurar as configurações de email a partir do banco de dados
     */
    private function configureMailSettings()
    {
        try {
            $settings = \App\Models\SystemSetting::whereIn('key', [
                'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 
                'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'
            ])->pluck('value', 'key');

            if ($settings->isEmpty()) {
                return false;
            }

            // Configurar o mailer temporariamente
            config([
                'mail.default' => $settings->get('mail_mailer', 'smtp'),
                'mail.mailers.smtp.host' => $settings->get('mail_host'),
                'mail.mailers.smtp.port' => $settings->get('mail_port'),
                'mail.mailers.smtp.encryption' => $settings->get('mail_encryption'),
                'mail.mailers.smtp.username' => $settings->get('mail_username'),
                'mail.mailers.smtp.password' => $settings->get('mail_password'),
                'mail.from.address' => $settings->get('mail_from_address'),
                'mail.from.name' => $settings->get('mail_from_name'),
            ]);

            \Log::info('Configurações de email carregadas do banco de dados', [
                'host' => $settings->get('mail_host'),
                'port' => $settings->get('mail_port'),
                'from' => $settings->get('mail_from_address')
            ]);

            return $settings;
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar configurações de email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Processar conteúdo do email substituindo variáveis
     */
    private function processEmailContent($content, $data)
    {
        // Garantir que o conteúdo não está com entidades HTML
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Substituir variáveis
        $content = str_replace('{{nome}}', $data['nome'], $content);
        $content = str_replace('{{email}}', $data['email'], $content);
        $content = str_replace('{{telefone}}', $data['telefone'], $content);
        $content = str_replace('{{curso}}', $data['curso'], $content);
        $content = str_replace('{{modalidade}}', $data['modalidade'], $content);
        $content = str_replace('{{campanha}}', $data['campanha'], $content);

        // Adicionar pixel de rastreamento e link de cancelamento de inscrição
        $trackingPixel = '<img src="' . route('admin.email-campaigns.track-open', ['trackingCode' => $data['tracking_code']]) . '" alt="" style="display:none" />';
        $unsubscribeLink = '<div style="text-align: center; margin-top: 20px; font-size: 12px; color: #666;">Se não deseja mais receber nossos emails, <a href="' . route('admin.email-campaigns.unsubscribe', ['trackingCode' => $data['tracking_code']]) . '" style="color: #666; text-decoration: underline;">clique aqui</a>.</div>';

        // Inserir pixel de rastreamento antes do </body>
        $content = str_replace('</body>', $trackingPixel . "\n" . $unsubscribeLink . "\n</body>", $content);

        return $content;
    }

    /**
     * Gerar template de email usando ChatGPT
     */
    public function generateAiTemplate(Request $request)
    {
        try {
            // Validar entrada
            $request->validate([
                'template_type' => 'required|string|max:100',
                'objective' => 'required|string|max:500',
                'target_audience' => 'nullable|string|max:100',
                'additional_instructions' => 'nullable|string|max:1000'
            ]);

            // Verificar se as configurações de AI estão ativas
            $aiSettings = \App\Models\SystemSetting::getAiSettings();
            if (!$aiSettings['is_active'] || empty($aiSettings['api_key'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'ChatGPT não está configurado ou ativo. Configure nas configurações do sistema.'
                ], 400);
            }

            // Instanciar o serviço ChatGPT
            $chatGptService = new ChatGptService();

            // Gerar template
            $htmlTemplate = $chatGptService->generateEmailMarketingTemplate(
                $request->template_type,
                $request->objective,
                $request->target_audience ?? 'estudantes',
                $request->additional_instructions ?? ''
            );

            if (!$htmlTemplate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível gerar o template. Verifique sua conexão com o ChatGPT e tente novamente.'
                ], 500);
            }

            Log::info('Template de email gerado via AI', [
                'type' => $request->template_type,
                'objective' => $request->objective,
                'user' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template gerado com sucesso!',
                'html' => $htmlTemplate,
                'suggested_name' => 'Template AI - ' . ucfirst($request->template_type),
                'suggested_subject' => $this->generateSubjectFromObjective($request->objective)
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar template com AI', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar sugestão de assunto baseado no objetivo
     */
    private function generateSubjectFromObjective($objective)
    {
        $suggestions = [
            'boas-vindas' => 'Bem-vindo(a)! Sua jornada educacional começa agora',
            'follow-up' => 'Não perca essa oportunidade educacional!',
            'promocional' => 'Oferta especial só para você!',
            'informativo' => 'Informações importantes sobre seu curso',
            'convite' => 'Você está convidado(a)!',
            'lembrete' => 'Lembrete importante sobre sua inscrição',
            'agradecimento' => 'Obrigado por escolher nosso curso!'
        ];

        // Tentar identificar o tipo baseado no objetivo
        $objectiveLower = strtolower($objective);
        
        foreach ($suggestions as $type => $subject) {
            if (str_contains($objectiveLower, $type) || str_contains($objectiveLower, str_replace('-', ' ', $type))) {
                return $subject;
            }
        }

        // Fallback genérico
        return 'Nova mensagem sobre seus estudos';
    }
}
