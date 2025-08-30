<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\Inscricao;
use App\Models\Parceiro;
use App\Models\SystemSetting;
use App\Models\Payment;
use App\Services\WhatsAppService;
use App\Services\PaymentNotificationService;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MatriculaController extends Controller
{
    protected $driveService;

    /**
     * Construtor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:matriculas.index');
        
        // Tentar resolver o GoogleDriveService de forma segura
        try {
            $this->driveService = app(\App\Services\GoogleDriveService::class);
        } catch (\Exception $e) {
            \Log::warning('GoogleDriveService não pôde ser inicializado: ' . $e->getMessage());
            $this->driveService = null;
        }
    }

    /**
     * Exibir lista de matrículas
     */
    public function index(Request $request)
    {
        $query = Matricula::with(['inscricao', 'createdBy', 'payments']);

        // Aplicar filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('modalidade')) {
            $query->where('modalidade', $request->modalidade);
        }

        if ($request->filled('escola_parceira')) {
            if ($request->escola_parceira === '1') {
                $query->where('escola_parceira', true);
            } elseif ($request->escola_parceira === '0') {
                $query->where(function($q) {
                    $q->where('escola_parceira', false)
                      ->orWhereNull('escola_parceira');
                });
            }
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        if ($request->filled('busca')) {
            $busca = trim($request->busca);
            
            // Log para debug
            Log::info('Filtro de busca aplicado', [
                'termo_busca' => $busca,
                'user_id' => auth()->id()
            ]);
            
            // Busca com tratamento especial para CPF
            $query->where(function($q) use ($busca) {
                $q->where('nome_completo', 'like', "%{$busca}%")
                  ->orWhere('cpf', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('numero_matricula', 'like', "%{$busca}%")
                  ->orWhere('rg', 'like', "%{$busca}%")
                  ->orWhere('telefone_celular', 'like', "%{$busca}%")
                  ->orWhere('telefone_fixo', 'like', "%{$busca}%");
                
                // Busca por CPF sem máscara (remove pontos e traços)
                if (strlen($busca) >= 11) {
                    $cpfLimpo = preg_replace('/[^0-9]/', '', $busca);
                    if (strlen($cpfLimpo) >= 11) {
                        // Busca por CPF limpo (apenas números)
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') LIKE ?", ["%{$cpfLimpo}%"]);
                        
                        // Busca por CPF com máscara (formato: 123.456.789-00)
                        $cpfMascarado = substr($cpfLimpo, 0, 3) . '.' . substr($cpfLimpo, 3, 3) . '.' . substr($cpfLimpo, 6, 3) . '-' . substr($cpfLimpo, 9, 2);
                        $q->orWhere('cpf', 'like', "%{$cpfMascarado}%");
                    }
                }
            });
            
            // Log da query SQL para debug
            Log::info('Query SQL gerada', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
        }

        // Filtro por status de pagamento
        if ($request->filled('status_pagamento')) {
            $statusPagamento = $request->status_pagamento;
            
            switch ($statusPagamento) {
                case 'avista':
                    // Buscar matrículas com pagamento à vista (boleto à vista, cartão único, PIX único)
                    $query->where(function($q) {
                        $q->where('tipo_boleto', 'avista')
                          ->orWhere('numero_parcelas', 1)
                          ->orWhereDoesntHave('payments', function($subQ) {
                              $subQ->where('numero_parcela', '>', 1);
                          });
                    });
                    break;
                    
                case 'pago':
                    // Buscar matrículas onde todos os pagamentos estão pagos
                    $query->whereDoesntHave('payments', function($q) {
                        $q->where('status', 'pending');
                    })->whereHas('payments', function($q) {
                        $q->where('status', 'paid');
                    });
                    break;
                    
                case 'pendente':
                    // Buscar matrículas com pelo menos um pagamento pendente
                    $query->whereHas('payments', function($q) {
                        $q->where('status', 'pending');
                    });
                    break;
                    
                case 'vencido':
                    // Buscar matrículas com pagamentos vencidos
                    $query->whereHas('payments', function($q) {
                        $q->where('status', 'pending')
                          ->where('data_vencimento', '<', now());
                    });
                    break;
                    
                case 'sem_pagamento':
                    // Buscar matrículas sem pagamentos
                    $query->whereDoesntHave('payments');
                    break;
            }
        }

        // Filtro por tipo de pagamento
        if ($request->filled('tipo_pagamento')) {
            $tipoPagamento = $request->tipo_pagamento;
            
            switch ($tipoPagamento) {
                case 'avista':
                    // Buscar matrículas com pagamento à vista
                    $query->where(function($q) {
                        $q->where('tipo_boleto', 'avista')
                          ->orWhere('numero_parcelas', 1)
                          ->orWhere('forma_pagamento', 'pix')
                          ->orWhere('forma_pagamento', 'cartao_credito');
                    });
                    break;
                    
                case 'parcelado':
                    // Buscar matrículas com pagamento parcelado
                    $query->where(function($q) {
                        $q->where('tipo_boleto', 'parcelado')
                          ->where('numero_parcelas', '>', 1);
                    });
                    break;
            }
        }

        // Filtro por forma de pagamento
        if ($request->filled('forma_pagamento')) {
            $query->where('forma_pagamento', $request->forma_pagamento);
        }

        $matriculas = $query->orderBy('created_at', 'desc')
                           ->paginate(20)
                           ->withQueryString();

        $formSettings = SystemSetting::getFormSettings();

        return view('admin.matriculas.index', compact('matriculas', 'formSettings'));
    }

    /**
     * Formulário para nova matrícula
     */
    public function create(Request $request)
    {
        $inscricaoId = $request->get('inscricao_id');
        $inscricao = null;
        
        if ($inscricaoId) {
            $inscricao = Inscricao::findOrFail($inscricaoId);
        } else {
            // Não limpar dados da sessão para evitar conflitos de CSRF
            // $request->session()->forget([...]);
        }
        
        $formSettings = SystemSetting::getFormSettings();
        
        // Buscar parceiros aprovados e ativos para o select
        $parceiros = Parceiro::aprovados()
            ->orderBy('nome_fantasia')
            ->orderBy('razao_social')
            ->orderBy('nome_completo')
            ->get()
            ->filter(function($parceiro) {
                // Filtra apenas parceiros que tenham um nome válido para exibição
                return !empty($parceiro->nome_exibicao);
            });
        
        return view('admin.matriculas.create', compact('inscricao', 'formSettings', 'parceiros'));
    }

    /**
     * Salvar nova matrícula
     */
    public function store(Request $request)
    {
        try {
            Log::info('Iniciando processo de matrícula', [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token', 'doc_rg_cpf', 'doc_comprovante', 'doc_historico', 'doc_certificado', 'doc_outros'])
            ]);

            // Validação básica primeiro
            $baseRules = [
                'inscricao_id' => 'nullable|exists:inscricaos,id',
                'nome_completo' => 'required|string|max:255',
                'data_nascimento' => 'required|date',
                'cpf' => 'required|string|max:14|unique:matriculas,cpf,NULL,id,deleted_at,NULL',
                'rg' => 'nullable|string|max:255',
                'orgao_emissor' => 'nullable|string|max:255',
                'sexo' => 'required|in:M,F,O',
                'estado_civil' => 'required|in:solteiro,casado,divorciado,viuvo,outro',
                'nacionalidade' => 'required|string|max:255',
                'naturalidade' => 'required|string|max:255',
                'cep' => 'required|string|max:9',
                'logradouro' => 'required|string|max:255',
                'numero' => 'required|string|max:255',
                'complemento' => 'nullable|string|max:255',
                'bairro' => 'required|string|max:255',
                'cidade' => 'required|string|max:255',
                'estado' => 'required|string|size:2',
                'telefone_fixo' => 'nullable|string|max:255',
                'telefone_celular' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'nome_pai' => 'nullable|string|max:255',
                'nome_mae' => 'required|string|max:255',
                'modalidade' => 'required|string|max:255',
                'curso' => 'required|string|max:255',
                'ultima_serie' => 'nullable|string|max:255',
                'ano_conclusao' => 'nullable|integer|min:1950|max:' . date('Y'),
                'escola_origem' => 'nullable|string|max:255',
                'status' => 'required|in:pre_matricula,matricula_confirmada,cancelada,trancada,concluida',
                
                // Campos da calculadora de pagamento
                'payment_gateway' => 'required|in:mercado_pago,asas,infiny_pay,cora',
                'forma_pagamento' => 'nullable|in:pix,cartao_credito,boleto',
                'bank_info' => 'nullable|string|max:2000',
                'valor_pago' => 'nullable|numeric|min:0',
                'tipo_boleto' => 'nullable|in:avista,parcelado',
                'valor_total_curso' => 'required|numeric|min:0',
                'valor_matricula' => 'nullable|numeric|min:0',
                'dia_vencimento' => 'required|integer|min:1|max:31',
                'percentual_juros' => 'nullable|numeric|min:0|max:100',
                'desconto' => 'nullable|numeric|min:0|max:100',
            ];

            // Validação condicional baseada no gateway de pagamento
            $paymentGateway = $request->input('payment_gateway');
            $formaPagamento = $request->input('forma_pagamento');
            $tipoBoleto = $request->input('tipo_boleto');
            
            if ($paymentGateway === 'mercado_pago') {
                // Para Mercado Pago, forma_pagamento é obrigatória
                $baseRules['forma_pagamento'] = 'required|in:pix,cartao_credito,boleto';
                
                // numero_parcelas é obrigatório apenas para boleto parcelado
                if ($formaPagamento === 'boleto' && $tipoBoleto === 'parcelado') {
                    $baseRules['numero_parcelas'] = 'required|integer|min:2|max:6';
                } else {
                    $baseRules['numero_parcelas'] = 'nullable|integer|min:1|max:12';
                }
            } else {
                // Para outros bancos, apenas bank_info é obrigatório
                $baseRules['bank_info'] = 'required|string|max:2000';
                $baseRules['numero_parcelas'] = 'nullable|integer|min:1|max:12';
                
                // Definir forma de pagamento padrão para outros bancos
                if (empty($formaPagamento)) {
                    $baseRules['forma_pagamento'] = 'nullable|in:pix,cartao_credito,boleto';
                }
            }

            // Adicionar campos de documentos
            $baseRules = array_merge($baseRules, [
                // Campos de documentos
                'doc_rg_cpf' => 'nullable|array',
                'doc_rg_cpf.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'doc_comprovante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'doc_historico' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'doc_certificado' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'doc_outros' => 'nullable|array',
                'doc_outros.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                
                'observacoes' => 'nullable|string',
                'escola_parceira' => 'nullable|boolean',
                'parceiro_id' => 'nullable|required_if:escola_parceira,1|exists:parceiros,id',
                'google_drive_folder_id' => 'nullable|string',
            ]);

            $validated = $request->validate($baseRules, [
                'nome_completo.required' => 'O nome completo é obrigatório',
                'data_nascimento.required' => 'A data de nascimento é obrigatória',
                'cpf.required' => 'O CPF é obrigatório',
                'sexo.required' => 'O sexo é obrigatório',
                'estado_civil.required' => 'O estado civil é obrigatório',
                'nacionalidade.required' => 'A nacionalidade é obrigatória',
                'naturalidade.required' => 'A naturalidade é obrigatória',
                'cep.required' => 'O CEP é obrigatório',
                'logradouro.required' => 'O logradouro é obrigatório',
                'numero.required' => 'O número é obrigatório',
                'bairro.required' => 'O bairro é obrigatório',
                'cidade.required' => 'A cidade é obrigatória',
                'estado.required' => 'O estado é obrigatório',
                'telefone_celular.required' => 'O telefone celular é obrigatório',
                'email.required' => 'O email é obrigatório',
                'nome_mae.required' => 'O nome da mãe é obrigatório',
                'modalidade.required' => 'A modalidade é obrigatória',
                'curso.required' => 'O curso é obrigatório',
                'forma_pagamento.required' => 'A forma de pagamento é obrigatória',
                'valor_total_curso.required' => 'O valor total do curso é obrigatório',
                'dia_vencimento.required' => 'O dia de vencimento é obrigatório',
            ]);

            // Ajustes baseados no gateway de pagamento
            if ($validated['payment_gateway'] === 'mercado_pago') {
                // Para cartão de crédito do Mercado Pago, definir número de parcelas como 1 se não informado
                // (o parcelamento real será feito no checkout do Mercado Pago)
                if ($validated['forma_pagamento'] === 'cartao_credito' && empty($validated['numero_parcelas'])) {
                    $validated['numero_parcelas'] = 1;
                }
            } else {
                // Para outros bancos, definir valores padrão
                if (empty($validated['forma_pagamento'])) {
                    $validated['forma_pagamento'] = 'boleto'; // Padrão para outros bancos
                }
                if (empty($validated['numero_parcelas'])) {
                    $validated['numero_parcelas'] = 1;
                }
            }

            Log::info('Dados validados com sucesso', ['validated_count' => count($validated)]);

            DB::beginTransaction();
            
            // Processar uploads de documentos
            $documentos = [];
            
            if ($request->hasFile('doc_rg_cpf')) {
                foreach ($request->file('doc_rg_cpf') as $file) {
                    $path = $file->store('documentos/matriculas', 'public');
                    $documentos['doc_rg_cpf'][] = $path;
                }
                Log::info('Documentos RG/CPF processados', ['count' => count($documentos['doc_rg_cpf'])]);
            }
            
            if ($request->hasFile('doc_comprovante')) {
                $path = $request->file('doc_comprovante')->store('documentos/matriculas', 'public');
                $documentos['doc_comprovante'] = $path;
                Log::info('Documento comprovante processado', ['path' => $path]);
            }
            
            if ($request->hasFile('doc_historico')) {
                $path = $request->file('doc_historico')->store('documentos/matriculas', 'public');
                $documentos['doc_historico'] = $path;
                Log::info('Documento histórico processado', ['path' => $path]);
            }
            
            if ($request->hasFile('doc_certificado')) {
                $path = $request->file('doc_certificado')->store('documentos/matriculas', 'public');
                $documentos['doc_certificado'] = $path;
                Log::info('Documento certificado processado', ['path' => $path]);
            }
            
            if ($request->hasFile('doc_outros')) {
                foreach ($request->file('doc_outros') as $file) {
                    $path = $file->store('documentos/matriculas', 'public');
                    $documentos['doc_outros'][] = $path;
                }
                Log::info('Outros documentos processados', ['count' => count($documentos['doc_outros'])]);
            }
            
            // Mesclar dados validados com documentos
            $dadosMatricula = array_merge($validated, $documentos);
            
            // Adicionar valores padrão para campos obrigatórios no banco
            $dadosMatricula['valor_matricula'] = $dadosMatricula['valor_matricula'] ?? 0;
            
            // Calcular valor_mensalidade se não foi informado e for parcelado
            if ((!isset($dadosMatricula['valor_mensalidade']) || $dadosMatricula['valor_mensalidade'] == 0) && 
                isset($dadosMatricula['numero_parcelas']) && $dadosMatricula['numero_parcelas'] > 1 &&
                isset($dadosMatricula['valor_total_curso']) && $dadosMatricula['valor_total_curso'] > 0) {
                
                $valorParaParcelar = $dadosMatricula['valor_total_curso'] - $dadosMatricula['valor_matricula'];
                $dadosMatricula['valor_mensalidade'] = $valorParaParcelar / $dadosMatricula['numero_parcelas'];
            } else {
                $dadosMatricula['valor_mensalidade'] = $dadosMatricula['valor_mensalidade'] ?? 0;
            }
            
            // Adicionar dados do usuário logado
            $dadosMatricula['created_by'] = Auth::id();
            $dadosMatricula['updated_by'] = Auth::id();
            
            Log::info('Criando matrícula no banco de dados', [
                'nome_completo' => $dadosMatricula['nome_completo'],
                'cpf' => $dadosMatricula['cpf']
            ]);

            // Tentar criar a matrícula com retry em caso de conflito de número
            $maxRetries = 3;
            $retryCount = 0;
            
            while ($retryCount < $maxRetries) {
                try {
                    $matricula = Matricula::create($dadosMatricula);
                    break; // Sucesso, sair do loop
                } catch (\Illuminate\Database\QueryException $e) {
                    // Se for erro de duplicata no numero_matricula, tentar novamente
                    if ($e->getCode() == 23000 && strpos($e->getMessage(), 'numero_matricula_unique') !== false) {
                        $retryCount++;
                        Log::warning('Conflito de número de matrícula, tentativa ' . $retryCount, [
                            'error' => $e->getMessage()
                        ]);
                        
                        if ($retryCount >= $maxRetries) {
                            throw new \Exception('Erro ao gerar número único de matrícula após ' . $maxRetries . ' tentativas. Tente novamente.');
                        }
                        
                        // Pequena pausa antes de tentar novamente
                        usleep(100000); // 100ms
                    } else {
                        // Outro tipo de erro, relançar
                        throw $e;
                    }
                }
            }

            Log::info('Matrícula criada com sucesso', ['matricula_id' => $matricula->id]);

            // Se veio de uma inscrição, atualizar o status da inscrição
            if ($matricula->inscricao_id) {
                $matricula->inscricao->update([
                    'status' => 'matriculado',
                    'updated_by' => Auth::id()
                ]);
                Log::info('Status da inscrição atualizado', ['inscricao_id' => $matricula->inscricao_id]);
            }

            // Criar pagamentos baseados na matrícula
            // 🚨 PROTEÇÃO: Para outros gateways, apenas criar pagamento único
            $gateway = $matricula->payment_gateway ?? 'mercado_pago';
            
            if ($gateway === 'mercado_pago' && $matricula->numero_parcelas > 1) {
                $this->createPaymentsForMatricula($matricula, $request);
            } else {
                $this->createSinglePayment($matricula, $request);
            }

            // Tentar enviar mensagem de WhatsApp
            try {
                $whatsappService = app(WhatsAppService::class);
                if ($whatsappService->hasValidSettings()) {
                    $whatsappService->sendMatriculaConfirmation($matricula);
                    Log::info('Mensagem de confirmação WhatsApp enviada para matrícula: ' . $matricula->id);
                }
            } catch (\Exception $e) {
                Log::error('Erro ao enviar mensagem WhatsApp para matrícula ' . $matricula->id . ': ' . $e->getMessage());
                // Não impedir o fluxo se o WhatsApp falhar
            }

            DB::commit();

            Log::info('Processo de matrícula concluído com sucesso', ['matricula_id' => $matricula->id]);

            // Não limpar dados da sessão para evitar conflitos de CSRF
            // $request->session()->forget([...]);

            // Se for requisição AJAX, retornar JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Matrícula realizada com sucesso!',
                    'redirect_url' => route('admin.matriculas.show', $matricula),
                    'matricula_id' => $matricula->id
                ]);
            }

            return redirect()
                ->route('admin.matriculas.show', $matricula)
                ->with('success', 'Matrícula realizada com sucesso!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação na matrícula', [
                'errors' => $e->errors(),
                'user_id' => Auth::id()
            ]);
            
            // Se for requisição AJAX, retornar JSON com erros
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erros de validação encontrados',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()
                ->withInput()
                ->withErrors($e->errors());
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro geral ao realizar matrícula', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            // Se for requisição AJAX, retornar JSON com erro
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao realizar matrícula: ' . $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao realizar matrícula: ' . $e->getMessage()]);
        }
    }

    /**
     * Exibir detalhes da matrícula
     */
    public function show(Matricula $matricula)
    {
        $matricula->load(['inscricao', 'createdBy', 'updatedBy', 'parceiro', 'payments']);
        return view('admin.matriculas.show', compact('matricula'));
    }

    /**
     * Exibir formulário de edição
     */
    public function edit(Matricula $matricula)
    {
        $formSettings = SystemSetting::getFormSettings();
        
        // Buscar parceiros aprovados e ativos para o select
        $parceiros = Parceiro::aprovados()
            ->orderBy('nome_fantasia')
            ->orderBy('razao_social')
            ->orderBy('nome_completo')
            ->get()
            ->filter(function($parceiro) {
                return !empty($parceiro->nome_exibicao);
            });
        
        return view('admin.matriculas.edit', compact('matricula', 'formSettings', 'parceiros'));
    }

    /**
     * Atualizar matrícula
     */
    public function update(Request $request, Matricula $matricula)
    {
        Log::info('Iniciando atualização de matrícula', [
            'matricula_id' => $matricula->id,
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        try {
        $validated = $request->validate([
            'nome_completo' => 'nullable|string|max:255',
            'data_nascimento' => 'nullable|date',
            'cpf' => 'nullable|string|max:14|unique:matriculas,cpf,' . $matricula->id,
            'rg' => 'nullable|string|max:255',
            'orgao_emissor' => 'nullable|string|max:255',
            'sexo' => 'nullable|in:M,F,O',
            'estado_civil' => 'nullable|in:solteiro,casado,divorciado,viuvo,outro',
            'nacionalidade' => 'nullable|string|max:255',
            'naturalidade' => 'nullable|string|max:255',
            'cep' => 'nullable|string|max:9',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:255',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|size:2',
            'telefone_fixo' => 'nullable|string|max:255',
            'telefone_celular' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'nome_pai' => 'nullable|string|max:255',
            'nome_mae' => 'nullable|string|max:255',
            'modalidade' => 'nullable|string|max:255',
            'curso' => 'nullable|string|max:255',
            'ultima_serie' => 'nullable|string|max:255',
            'ano_conclusao' => 'nullable|integer|min:1950|max:' . date('Y'),
            'escola_origem' => 'nullable|string|max:255',
            'status' => 'required|in:pre_matricula,matricula_confirmada,cancelada,trancada,concluida',
            
            // Campos de pagamento
            'forma_pagamento' => 'nullable|in:pix,cartao_credito,boleto',
            'payment_gateway' => 'nullable|in:mercado_pago,asas,infiny_pay,cora',
            'bank_info' => 'nullable|string|max:2000',
            'valor_pago' => 'nullable|numeric|min:0',
            'tipo_boleto' => 'nullable|in:avista,parcelado',
            'valor_total_curso' => 'nullable|numeric|min:0',
            'valor_matricula' => 'nullable|numeric|min:0',
            'numero_parcelas' => 'nullable|integer|min:1|max:12',
            'dia_vencimento' => 'nullable|integer|min:1|max:31',
            'desconto' => 'nullable|numeric|min:0|max:100',
            'percentual_juros' => 'nullable|numeric|min:0|max:100',
            
            'observacoes' => 'nullable|string',
            'escola_parceira' => 'nullable|boolean',
            'parceiro_id' => 'nullable|required_if:escola_parceira,1|exists:parceiros,id',
            'google_drive_folder_id' => 'nullable|string',
        ]);

            // Ajustes baseados no gateway de pagamento para update
            if (isset($validated['payment_gateway'])) {
                if ($validated['payment_gateway'] === 'mercado_pago') {
                    // Para cartão de crédito do Mercado Pago, definir número de parcelas como 1 se não informado
                    if (isset($validated['forma_pagamento']) && $validated['forma_pagamento'] === 'cartao_credito' && empty($validated['numero_parcelas'])) {
                        $validated['numero_parcelas'] = 1;
                    }
                } else {
                    // Para outros bancos, definir valores padrão se não informados
                    if (!isset($validated['forma_pagamento']) || empty($validated['forma_pagamento'])) {
                        $validated['forma_pagamento'] = 'boleto';
                    }
                    if (!isset($validated['numero_parcelas']) || empty($validated['numero_parcelas'])) {
                        $validated['numero_parcelas'] = 1;
                    }
                }
            }

            Log::info('Validação concluída com sucesso', [
                'matricula_id' => $matricula->id,
                'validated_fields' => array_keys($validated)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação na atualização de matrícula', [
                'matricula_id' => $matricula->id,
                'errors' => $e->errors(),
                'user_id' => Auth::id()
            ]);
            
            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Erro de validação. Verifique os campos obrigatórios.');
        }

        DB::beginTransaction();
        try {
            // Adicionar dados de auditoria
            $validated['updated_by'] = Auth::id();
            
            Log::info('Atualizando dados da matrícula', [
                'matricula_id' => $matricula->id,
                'changes' => array_diff_assoc($validated, $matricula->getAttributes())
            ]);

            $matricula->update($validated);

            // Se o status mudou para cancelada, atualizar a inscrição também
            if ($matricula->isDirty('status') && $matricula->status === 'cancelada' && $matricula->inscricao) {
                Log::info('Atualizando status da inscrição para disponível', [
                    'matricula_id' => $matricula->id,
                    'inscricao_id' => $matricula->inscricao->id
                ]);
                
                $matricula->inscricao->update([
                    'status' => 'disponivel',
                    'updated_by' => Auth::id()
                ]);
            }

            DB::commit();

            Log::info('Matrícula atualizada com sucesso', [
                'matricula_id' => $matricula->id,
                'user_id' => Auth::id()
            ]);

            return redirect()
                ->route('admin.matriculas.show', $matricula)
                ->with('success', 'Matrícula atualizada com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao atualizar matrícula', [
                'matricula_id' => $matricula->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao atualizar matrícula: ' . $e->getMessage()])
                ->with('error', 'Erro interno. Verifique os logs para mais detalhes.');
        }
    }

    /**
     * Regenerar pagamentos da matrícula
     */
    public function regeneratePayments(Request $request, Matricula $matricula)
    {
        try {
            Log::info('Iniciando regeneração de pagamentos', [
                'matricula_id' => $matricula->id,
                'user_id' => Auth::id()
            ]);

            // Verificar se a matrícula tem dados de pagamento válidos
            if (!$matricula->forma_pagamento || !$matricula->valor_total_curso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Matrícula não possui dados de pagamento válidos para regenerar.'
                ], 400);
            }

            // Corrigir valor_mensalidade se necessário
            if ((!$matricula->valor_mensalidade || $matricula->valor_mensalidade == 0) && 
                $matricula->numero_parcelas > 1 && $matricula->valor_total_curso > 0) {
                
                $valorParaParcelar = $matricula->valor_total_curso - ($matricula->valor_matricula ?? 0);
                $matricula->valor_mensalidade = $valorParaParcelar / $matricula->numero_parcelas;
                $matricula->save();
                
                Log::info('Valor da mensalidade corrigido durante regeneração', [
                    'matricula_id' => $matricula->id,
                    'novo_valor_mensalidade' => $matricula->valor_mensalidade
                ]);
            }

            DB::beginTransaction();

            // Excluir pagamentos existentes (apenas os pendentes)
            $existingPayments = $matricula->payments()->where('status', 'pending')->get();
            
            Log::info('Excluindo pagamentos existentes', [
                'matricula_id' => $matricula->id,
                'payments_count' => $existingPayments->count()
            ]);

            foreach ($existingPayments as $payment) {
                // Se o pagamento tem dados do Mercado Pago, cancelar lá também
                if ($payment->mercadopago_id) {
                    try {
                        $mercadoPagoService = app(MercadoPagoService::class);
                        $mercadoPagoService->cancelPayment($payment->mercadopago_id);
                        Log::info('Pagamento cancelado no Mercado Pago', [
                            'payment_id' => $payment->id,
                            'mercadopago_id' => $payment->mercadopago_id
                        ]);
                    } catch (\Exception $e) {
                        Log::warning('Erro ao cancelar pagamento no Mercado Pago', [
                            'payment_id' => $payment->id,
                            'mercadopago_id' => $payment->mercadopago_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                $payment->delete();
            }

            // Criar novos pagamentos baseados nos dados da matrícula
            $paymentSettings = SystemSetting::getPaymentSettings();
            
            // 🚨 PROTEÇÃO: Para outros gateways, apenas criar pagamento único
            $gateway = $matricula->payment_gateway ?? 'mercado_pago';
            
            if ($gateway === 'mercado_pago' && $matricula->tipo_boleto === 'parcelado' && $matricula->numero_parcelas > 1) {
                // Criar pagamentos parcelados (APENAS para Mercado Pago)
                $this->createPaymentsForMatricula($matricula, $request);
            } else {
                // Criar pagamento único (outros gateways ou pagamento à vista)
                $this->createSinglePayment($matricula, $request);
            }

            DB::commit();

            Log::info('Pagamentos regenerados com sucesso', [
                'matricula_id' => $matricula->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pagamentos regenerados com sucesso!',
                'redirect_url' => route('admin.matriculas.show', $matricula)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao regenerar pagamentos', [
                'matricula_id' => $matricula->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao regenerar pagamentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar pasta no Google Drive para a matrícula
     */
    public function createDriveFolder(Request $request, Matricula $matricula)
    {
        try {
            $request->validate([
                'name' => 'nullable|string|max:255',
                'parent_id' => 'nullable|string',
                'parent_folder_id' => 'nullable|string'
            ]);
            
            // Usar o nome fornecido ou gerar um padrão
            $folderName = $request->name ?: ($matricula->nome_completo . ' - CPF ' . $matricula->cpf . ' - ' . date('d/m/Y') . ' - Documentos');
            
            // Verificar se a pasta já foi criada
            if ($matricula->google_drive_folder_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasta já foi criada para esta matrícula.'
                ], 400);
            }
            
            // Converter o parent_id local para o file_id do Google Drive
            $googleDriveParentId = null;
            $parentId = $request->parent_id ?: $request->parent_folder_id;
            
            if ($parentId) {
                $parentFile = \App\Models\GoogleDriveFile::find($parentId);
                if ($parentFile) {
                    $googleDriveParentId = $parentFile->file_id;
                } else {
                    // Se não encontrou no banco local, pode ser um ID direto do Google Drive
                    $googleDriveParentId = $parentId;
                }
            }
            
            if (!$this->driveService) {
                return response()->json([
                    'success' => false,
                    'message' => 'Serviço do Google Drive não está disponível no momento.'
                ], 503);
            }
            
            $folder = $this->driveService->createFolder(
                $folderName,
                Auth::id(),
                $googleDriveParentId
            );

            // Atualizar a matrícula com o ID da pasta
            $matricula->update([
                'google_drive_folder_id' => $folder->file_id,
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pasta criada com sucesso!',
                'folder' => $folder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar pasta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar arquivos da pasta do Google Drive da matrícula
     */
    public function listDriveFiles(Request $request, Matricula $matricula)
    {
        try {
            \Log::info('MatriculaController::listDriveFiles - Iniciando listagem para matrícula: ' . $matricula->id);
            
            if (!$matricula->google_drive_folder_id) {
                \Log::warning('MatriculaController::listDriveFiles - Nenhuma pasta do Google Drive vinculada à matrícula: ' . $matricula->id);
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma pasta do Google Drive vinculada a esta matrícula.',
                    'files' => []
                ]);
            }

            // Obter a pasta atual (pode ser a pasta da matrícula ou uma subpasta)
            $currentFolderId = $request->get('folder', $matricula->google_drive_folder_id);
            \Log::info('MatriculaController::listDriveFiles - Pasta atual: ' . $currentFolderId);
            \Log::info('MatriculaController::listDriveFiles - Pasta da matrícula: ' . $matricula->google_drive_folder_id);
            
            // Verificar se a pasta atual pertence à hierarquia da matrícula
            if ($currentFolderId !== $matricula->google_drive_folder_id) {
                // Verificar se a pasta atual é filha da pasta da matrícula
                $currentFolder = \App\Models\GoogleDriveFile::where('file_id', $currentFolderId)->first();
                if (!$currentFolder || !$this->isChildOfMatriculaFolder($currentFolder, $matricula->google_drive_folder_id)) {
                    \Log::warning('MatriculaController::listDriveFiles - Acesso negado à pasta: ' . $currentFolderId);
                    return response()->json([
                        'success' => false,
                        'message' => 'Acesso negado a esta pasta.',
                        'files' => []
                    ]);
                }
            }
            
            // Buscar a pasta atual no banco de dados
            $currentFolder = \App\Models\GoogleDriveFile::where('file_id', $currentFolderId)->first();
            \Log::info('MatriculaController::listDriveFiles - Pasta encontrada no banco: ' . ($currentFolder ? 'Sim' : 'Não'));
            
            // Se a pasta não existe no banco, tentar sincronizar primeiro
            if (!$currentFolder) {
                \Log::info('MatriculaController::listDriveFiles - Pasta não encontrada no banco, sincronizando primeiro');
                if ($this->driveService) {
                    try {
                        // Sincronizar a pasta raiz para garantir que todas as pastas sejam criadas no banco
                        $this->driveService->listFiles();
                        $currentFolder = \App\Models\GoogleDriveFile::where('file_id', $currentFolderId)->first();
                        \Log::info('MatriculaController::listDriveFiles - Após sincronização, pasta encontrada: ' . ($currentFolder ? 'Sim' : 'Não'));
                    } catch (\Exception $e) {
                        \Log::warning('Erro ao sincronizar pasta: ' . $e->getMessage());
                    }
                }
            }
            
            // Primeiro, sincronizar com o Google Drive para garantir dados atualizados
            $driveFiles = collect([]);
            if ($this->driveService) {
                try {
                    \Log::info('MatriculaController::listDriveFiles - Sincronizando arquivos do Google Drive para pasta: ' . $currentFolderId);
                    $driveFiles = $this->driveService->listFiles($currentFolderId);
                    \Log::info('MatriculaController::listDriveFiles - Arquivos sincronizados: ' . count($driveFiles));
                } catch (\Exception $e) {
                    \Log::warning('Erro ao sincronizar com Google Drive: ' . $e->getMessage());
                }
            }
            
            // Buscar arquivos do banco de dados local
            if ($currentFolder) {
                // Se encontramos a pasta no banco, buscar seus filhos
                \Log::info('MatriculaController::listDriveFiles - Buscando arquivos da pasta: ' . $currentFolder->id);
                $files = \App\Models\GoogleDriveFile::where('parent_id', $currentFolder->id)
                    ->where('is_trashed', false)
                    ->get();
                \Log::info('MatriculaController::listDriveFiles - Arquivos encontrados no banco: ' . $files->count());
            } else {
                // Se não encontramos a pasta, buscar arquivos que têm como parent a pasta da matrícula
                \Log::info('MatriculaController::listDriveFiles - Pasta não encontrada, buscando arquivos da pasta da matrícula');
                $matriculaFolder = \App\Models\GoogleDriveFile::where('file_id', $matricula->google_drive_folder_id)->first();
                if ($matriculaFolder) {
                    $files = \App\Models\GoogleDriveFile::where('parent_id', $matriculaFolder->id)
                        ->where('is_trashed', false)
                        ->get();
                } else {
                    $files = collect([]);
                }
                \Log::info('MatriculaController::listDriveFiles - Arquivos encontrados no banco: ' . $files->count());
            }
            
            // Verificar se algum arquivo do Google Drive não está no resultado
            if ($driveFiles && $driveFiles->count() > 0) {
                $googleDriveIds = $files->pluck('file_id')->toArray();
                foreach ($driveFiles as $driveFile) {
                    if (!in_array($driveFile->file_id, $googleDriveIds)) {
                        // Este arquivo do Google Drive não está no resultado, adicionar
                        $files->push($driveFile);
                        \Log::info('MatriculaController::listDriveFiles - Arquivo adicionado do Google Drive: ' . $driveFile->name);
                    }
                }
            }
            
            // Se não encontramos arquivos no banco, mas temos arquivos do Google Drive, usar apenas os do Google Drive
            if ($files->count() === 0 && $driveFiles && $driveFiles->count() > 0) {
                \Log::info('MatriculaController::listDriveFiles - Nenhum arquivo no banco, usando arquivos do Google Drive');
                $files = $driveFiles;
            }
            
            \Log::info('MatriculaController::listDriveFiles - Total final de arquivos: ' . $files->count());
            
            // Log dos arquivos finais para debug
            foreach ($files as $file) {
                \Log::info('MatriculaController::listDriveFiles - Arquivo final: ' . $file->name . ' (ID: ' . $file->id . ', File ID: ' . $file->file_id . ', Parent ID: ' . $file->parent_id . ')');
            }
            
            return response()->json([
                'success' => true,
                'files' => $files,
                'current_folder' => $currentFolder,
                'folder_name' => $currentFolder ? $currentFolder->name : 'Pasta da Matrícula',
                'folder_link' => 'https://drive.google.com/drive/folders/' . $currentFolderId
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao listar arquivos da matrícula: ' . $e->getMessage(), [
                'matricula_id' => $matricula->id,
                'google_drive_folder_id' => $matricula->google_drive_folder_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar arquivos: ' . $e->getMessage(),
                'files' => []
            ], 500);
        }
    }

    /**
     * Verificar se uma pasta é filha da pasta da matrícula
     */
    private function isChildOfMatriculaFolder($folder, $matriculaFolderId)
    {
        // Se a pasta atual é a pasta da matrícula, retorna true
        if ($folder->file_id === $matriculaFolderId) {
            return true;
        }
        
        // Verificar recursivamente se é filha da pasta da matrícula
        $parent = $folder;
        $maxDepth = 10; // Evitar loop infinito
        $depth = 0;
        
        while ($parent && $depth < $maxDepth) {
            $parent = \App\Models\GoogleDriveFile::where('file_id', $parent->parent_id)->first();
            if ($parent && $parent->file_id === $matriculaFolderId) {
                return true;
            }
            $depth++;
        }
        
        return false;
    }

    /**
     * Excluir matrícula
     */
    public function destroy(Matricula $matricula)
    {
        DB::beginTransaction();
        try {
            // Registrar a exclusão para auditoria
            Log::info('Iniciando exclusão de matrícula', [
                'matricula_id' => $matricula->id,
                'numero_matricula' => $matricula->numero_matricula,
                'nome_completo' => $matricula->nome_completo,
                'user_id' => Auth::id()
            ]);
            
            // Excluir todos os pagamentos associados permanentemente
            $matricula->payments()->forceDelete();
            Log::info('Pagamentos excluídos permanentemente', [
                'matricula_id' => $matricula->id
            ]);
            
            // Excluir todos os contratos associados permanentemente
            $matricula->contracts()->forceDelete();
            Log::info('Contratos excluídos permanentemente', [
                'matricula_id' => $matricula->id
            ]);
            
            // Se tem inscrição vinculada, atualizar o status
            if ($matricula->inscricao) {
                $matricula->inscricao->update([
                    'status' => 'disponivel',
                    'updated_by' => Auth::id()
                ]);
                Log::info('Status da inscrição atualizado para disponível', [
                    'inscricao_id' => $matricula->inscricao_id
                ]);
            }

            // Excluir a matrícula permanentemente
            $matricula->forceDelete();
            Log::info('Matrícula excluída permanentemente', [
                'matricula_id' => $matricula->id
            ]);

            DB::commit();

            return redirect()
                ->route('admin.matriculas.index')
                ->with('success', 'Matrícula excluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir matrícula', [
                'matricula_id' => $matricula->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Erro ao excluir matrícula: ' . $e->getMessage()]);
        }
    }



    /**
     * Criar payment inicial (matrícula ou primeira mensalidade)
     * As demais mensalidades são criadas pelo cron job
     */
    protected function createPaymentsForMatricula(Matricula $matricula, Request $request)
    {
        $paymentSettings = SystemSetting::getPaymentSettings();
        
        // CENÁRIO 1: COM valor de matrícula (entrada)
        if ($matricula->valor_matricula > 0) {
            // Criar payment da MATRÍCULA (entrada)
            $dataVencimentoMatricula = now()->addDays(1);
                
            $paymentMatricula = Payment::create([
                'matricula_id' => $matricula->id,
                'valor' => $matricula->valor_matricula,
                'forma_pagamento' => $matricula->forma_pagamento,
                'data_vencimento' => $dataVencimentoMatricula,
                'descricao' => 'Matrícula - ' . ($matricula->inscricao->curso->nome ?? 'Curso'),
                'numero_parcela' => 0, // Matrícula usa número 0
                'total_parcelas' => $matricula->numero_parcelas + 1,
                'status' => 'pending',
            ]);

            Log::info('Payment da matrícula criado', [
                'payment_id' => $paymentMatricula->id,
                'matricula_id' => $matricula->id,
                'valor' => $matricula->valor_matricula
            ]);

            // Integrar com Mercado Pago se configurado
            if ($paymentSettings['mercadopago_enabled']) {
                try {
                    $mercadoPagoService = app(MercadoPagoService::class);
                    $mercadoPagoPayment = $mercadoPagoService->createPayment($paymentMatricula);
                    
                    $paymentMatricula->update([
                        'mercadopago_id' => $mercadoPagoPayment['id'],
                        'mercadopago_status' => $mercadoPagoPayment['status'],
                        'mercadopago_data' => $mercadoPagoPayment['full_response']
                    ]);

                    // Processar resposta para boletos (download PDF, etc.)
                    if ($paymentMatricula->forma_pagamento === 'boleto') {
                        $mercadoPagoService->processPaymentResponse($paymentMatricula, $mercadoPagoPayment['full_response']);
                    }

                    Log::info('Pagamento de matrícula criado no Mercado Pago', [
                        'payment_id' => $paymentMatricula->id,
                        'mercadopago_id' => $mercadoPagoPayment['id']
                    ]);

                } catch (\Exception $e) {
                    Log::error('Erro ao criar pagamento de matrícula no Mercado Pago', [
                        'payment_id' => $paymentMatricula->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Enviar notificações de pagamento criado
            try {
                $notificationService = app(PaymentNotificationService::class);
                $notificationService->sendPaymentCreatedNotifications($paymentMatricula);
            } catch (\Exception $e) {
                Log::error('Erro ao enviar notificações de pagamento de matrícula', [
                    'payment_id' => $paymentMatricula->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Marcar que há parcelas ativas para o cron job processar
            $matricula->parcelas_ativas = true;
            $matricula->saveQuietly(); // Evita disparar hooks updating/updated
        }
        
        // CENÁRIO 2: SEM valor de matrícula
        else if ($matricula->numero_parcelas > 0) {
            // Criar primeira mensalidade como pendente
            $dataVencimento = now()->addMonths(1)->day((int) $matricula->dia_vencimento);
            
            $valorMensalidade = $matricula->valor_mensalidade;
            if (!$valorMensalidade || $valorMensalidade == 0) {
                $valorMensalidade = $matricula->valor_total_curso / $matricula->numero_parcelas;
            }
            
            $paymentPrimeiro = Payment::create([
                'matricula_id' => $matricula->id,
                'valor' => $valorMensalidade,
                'forma_pagamento' => $matricula->forma_pagamento,
                'data_vencimento' => $dataVencimento,
                'descricao' => 'Mensalidade 1/' . $matricula->numero_parcelas . ' - ' . ($matricula->inscricao->curso->nome ?? 'Curso'),
                'numero_parcela' => 1,
                'total_parcelas' => $matricula->numero_parcelas,
                'status' => 'pending',
            ]);

            Log::info('Primeira mensalidade criada (sem matrícula)', [
                'payment_id' => $paymentPrimeiro->id,
                'matricula_id' => $matricula->id
            ]);

            // Integrar com Mercado Pago se configurado
            if ($paymentSettings['mercadopago_enabled']) {
                try {
                    $mercadoPagoService = app(MercadoPagoService::class);
                    $mercadoPagoPayment = $mercadoPagoService->createPayment($paymentPrimeiro);
                    
                    $paymentPrimeiro->update([
                        'mercadopago_id' => $mercadoPagoPayment['id'],
                        'mercadopago_status' => $mercadoPagoPayment['status'],
                        'mercadopago_data' => $mercadoPagoPayment['full_response']
                    ]);

                    // Processar resposta para boletos (download PDF, etc.)
                    if ($paymentPrimeiro->forma_pagamento === 'boleto') {
                        $mercadoPagoService->processPaymentResponse($paymentPrimeiro, $mercadoPagoPayment['full_response']);
                    }

                    Log::info('Primeira mensalidade criada no Mercado Pago', [
                        'payment_id' => $paymentPrimeiro->id,
                        'mercadopago_id' => $mercadoPagoPayment['id']
                    ]);

                } catch (\Exception $e) {
                    Log::error('Erro ao criar primeira mensalidade no Mercado Pago', [
                        'payment_id' => $paymentPrimeiro->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Enviar notificações de pagamento criado
            try {
                $notificationService = app(PaymentNotificationService::class);
                $notificationService->sendPaymentCreatedNotifications($paymentPrimeiro);
            } catch (\Exception $e) {
                Log::error('Erro ao enviar notificações da primeira mensalidade', [
                    'payment_id' => $paymentPrimeiro->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Marcar que há parcelas ativas para o cron job processar
            $matricula->parcelas_ativas = true;
            $matricula->saveQuietly(); // Evita disparar hooks updating/updated
        }
        
        // As demais mensalidades serão criadas pelo cron job automaticamente
        Log::info('Payment inicial criado. Cron job criará as demais mensalidades.', [
            'matricula_id' => $matricula->id,
            'valor_matricula' => $matricula->valor_matricula,
            'numero_parcelas' => $matricula->numero_parcelas
        ]);
    }



    /**
     * Criar pagamento único
     */
    protected function createSinglePayment(Matricula $matricula, Request $request)
    {
        // Para pagamento único, usar o dia de vencimento especificado ou 7 dias
        $dataVencimento = $request->dia_vencimento 
            ? now()->addMonth()->day((int) $request->dia_vencimento)
            : now()->addDays(7);
            
        $payment = Payment::create([
            'matricula_id' => $matricula->id,
            'valor' => $matricula->valor_total_curso, // Usar dados da matrícula
            'forma_pagamento' => $matricula->forma_pagamento, // Usar dados da matrícula
            'data_vencimento' => $dataVencimento,
            'descricao' => 'Pagamento à vista - ' . ($matricula->inscricao->curso->nome ?? 'Curso'),
            'numero_parcela' => 1,
            'total_parcelas' => 1,
            'status' => 'pending',
        ]);

        // Integrar com Mercado Pago se configurado
        $paymentSettings = SystemSetting::getPaymentSettings();
        if ($paymentSettings['mercadopago_enabled']) {
            try {
                $mercadoPagoService = app(MercadoPagoService::class);
                $mercadoPagoPayment = $mercadoPagoService->createPayment($payment);
                
                $payment->update([
                    'mercadopago_id' => $mercadoPagoPayment['id'],
                    'mercadopago_status' => $mercadoPagoPayment['status'],
                    'mercadopago_data' => $mercadoPagoPayment['full_response']
                ]);

                // Processar resposta para boletos (download PDF, etc.)
                if ($payment->forma_pagamento === 'boleto') {
                    $mercadoPagoService->processPaymentResponse($payment, $mercadoPagoPayment['full_response']);
                }

                Log::info('Pagamento criado no Mercado Pago', [
                    'payment_id' => $payment->id,
                    'mercadopago_id' => $mercadoPagoPayment['id']
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao criar pagamento no Mercado Pago', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Pagamento único criado para matrícula', [
            'matricula_id' => $matricula->id,
            'payment_id' => $payment->id,
            'valor' => $request->valor_total_curso
        ]);

        // Enviar notificações de pagamento criado
        try {
            $notificationService = app(PaymentNotificationService::class);
            $notificationService->sendPaymentCreatedNotifications($payment);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificações de pagamento único', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
} 