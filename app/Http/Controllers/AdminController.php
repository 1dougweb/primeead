<?php

namespace App\Http\Controllers;

use App\Models\Inscricao;
use App\Models\User;
use App\Models\SystemSetting;
use App\Models\StatusHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Construtor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:inscricoes.index')->only(['inscricoes', 'exportarCSV', 'importarInscricoes', 'processarImportacaoInscricoes', 'downloadTemplateInscricoes', 'countInscricoes', 'apagarTodasInscricoes', 'detalhesModal', 'editarInscricao', 'detalhes', 'atualizarInscricao', 'deletarInscricao', 'atualizarEtiqueta', 'show', 'unlock', 'editModal', 'updateStatus', 'addNote', 'markContact']);
    }

    /**
     * Dashboard principal
     */
    public function dashboard(Request $request)
    {
        // Regenerate session token to prevent CSRF issues
        $request->session()->regenerateToken();
        
        $user = auth()->user();

        // Se for parceiro, redirecionar para o dashboard de parceiro
        if ($user->isParceiro()) {
            return redirect()->route('parceiros.dashboard');
        }

        // Para admin e outros tipos de staff, mostrar o dashboard administrativo
        $totalInscricoes = Inscricao::count();
        $inscricoesHoje = Inscricao::whereDate('created_at', Carbon::today())->count();
        $inscricoesUltimos7Dias = Inscricao::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $inscricoesUltimos30Dias = Inscricao::where('created_at', '>=', Carbon::now()->subDays(30))->count();

        // Estatísticas por curso
        $estatisticasCursos = Inscricao::selectRaw('curso, COUNT(*) as total')
            ->groupBy('curso')
            ->get();
            
        // Estatísticas por modalidade
        $estatisticasModalidade = Inscricao::selectRaw('modalidade, COUNT(*) as total')
            ->groupBy('modalidade')
            ->get();
            
        // Estatísticas de conversão
        $conversaoPorCurso = Inscricao::selectRaw('curso, modalidade, COUNT(*) as total')
            ->groupBy('curso', 'modalidade')
            ->get();

        // Inscrições recentes
        $inscricoesRecentes = Inscricao::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Obter configurações de formulário para labels dinâmicos
        $formSettings = SystemSetting::getFormSettings();
        $availableCourses = $formSettings['available_courses'] ?? [];
        $availableModalities = $formSettings['available_modalities'] ?? [];
        
        // Gerar dados para o gráfico de conversão em linha
        $chartData = $this->getDashboardChartData($request);

        // Se for requisição AJAX (filtro de gráfico), retornar apenas dados JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'chartData' => $chartData,
                'message' => 'Dados do gráfico atualizados com sucesso'
            ]);
        }

        return view('admin.dashboard', compact(
            'totalInscricoes',
            'inscricoesHoje',
            'inscricoesUltimos7Dias',
            'inscricoesUltimos30Dias',
            'estatisticasCursos',
            'estatisticasModalidade',
            'conversaoPorCurso',
            'inscricoesRecentes',
            'availableCourses',
            'availableModalities',
            'chartData'
        ));
    }

    /**
     * Gera dados para o gráfico de conversão do dashboard
     */
    private function getDashboardChartData(Request $request)
    {
        // Definir período (últimos 30 dias por padrão)
        $startDate = $request->filled('chart_start_date') 
            ? Carbon::parse($request->chart_start_date) 
            : Carbon::now()->subDays(30)->startOfDay();
        
        $endDate = $request->filled('chart_end_date')
            ? Carbon::parse($request->chart_end_date)->endOfDay()
            : Carbon::now()->endOfDay();
        
        // Array para armazenar os dados
        $labels = [];
        $leadsData = [];
        $contatadosData = [];
        $matriculadosData = [];
        
        // Gerar dados para cada dia no período
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $formattedDate = $currentDate->format('d/m');
            
            // Adicionar label da data
            $labels[] = $formattedDate;
            
            // Contar leads recebidos neste dia
            $leadsData[] = Inscricao::whereDate('created_at', $dateString)->count();
            
            // Contar leads contatados neste dia (com status alterado para 'contatado')
            $contatadosData[] = StatusHistory::where('status_novo', 'contatado')
                ->whereDate('data_alteracao', $dateString)
                ->count();
            
            // Contar leads matriculados neste dia
            $matriculadosData[] = StatusHistory::where('status_novo', 'matriculado')
                ->whereDate('data_alteracao', $dateString)
                ->count();
            
            // Avançar para o próximo dia
            $currentDate->addDay();
        }
        
        return [
            'labels' => $labels,
            'leads' => $leadsData,
            'contatados' => $contatadosData,
            'matriculados' => $matriculadosData
        ];
    }

    /**
     * Listar todas as inscrições
     */
    public function inscricoes(Request $request)
    {
        // Construir query base
        $query = Inscricao::with(['lockedBy', 'statusHistories']);

        // Verificar permissões do usuário
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();

        // Se não for admin, mostrar leads livres (locked_by = null) e leads atribuídos ao próprio usuário
        if (!$isAdmin) {
            $query->where(function($q) use ($userId) {
                $q->whereNull('locked_by')  // Leads livres
                  ->orWhere('locked_by', $userId);  // Leads do próprio usuário
            });
        }

        // Filtrar por busca
        if ($request->has('busca') && !empty($request->busca)) {
            $query->where(function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->busca . '%')
                  ->orWhere('email', 'like', '%' . $request->busca . '%')
                  ->orWhere('telefone', 'like', '%' . $request->busca . '%');
            });
        }

        // Filtrar por data
        if ($request->has('data_inicio') && $request->has('data_fim')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->data_inicio)->startOfDay(),
                Carbon::parse($request->data_fim)->endOfDay()
            ]);
        }

        // Ordenar
        $query->orderBy('created_at', 'desc');

        // Paginar resultados
        $inscricoes = $query->paginate(20)->withQueryString();

        // Se for uma requisição AJAX, retornar apenas o HTML da lista
        if ($request->ajax()) {
            return view('admin.inscricoes._lista', compact('inscricoes'))->render();
        }

        // Retornar view completa
        return view('admin.inscricoes.index', compact('inscricoes'));
    }

    /**
     * Mostrar formulário de edição
     */
    public function editarInscricao($id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();

        // Verificar se o lead está travado por outro usuário
        if ($inscricao->isLockedByOther($userId)) {
            return redirect()->back()->with('error', 'Este lead está sendo atendido por ' . $inscricao->lockedBy->name . '. Não é possível editar.');
        }

        // Se o lead não está travado, travar para o usuário atual
        if (!$inscricao->isLocked()) {
            $inscricao->lock($userId);
        }

        // Obter configurações de formulário para labels dinâmicos
        $formSettings = SystemSetting::getFormSettings();

        return view('admin.inscricoes.edit', compact('inscricao', 'formSettings'));
    }

    /**
     * Retornar formulário de edição para o modal
     */
    public function editModal($id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();
        
        // Verificar se o usuário não-admin tem acesso a este lead
        if (!$isAdmin && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para editar este lead.'
            ], 403);
        }

        // Verificar se o lead está travado por outro usuário
        if ($inscricao->isLockedByOther($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por ' . $inscricao->lockedBy->name . '. Não é possível editar.'
            ], 403);
        }

        // Se o lead não está travado, travar para o usuário atual
        if (!$inscricao->isLocked()) {
            $inscricao->lock($userId);
        }

        // Obter configurações de formulário para labels dinâmicos
        $formSettings = SystemSetting::getFormSettings();
        $cursos = $formSettings['available_courses'] ?? [];
        $modalidades = $formSettings['available_modalities'] ?? [];

        return view('admin.inscricoes._modal_form', compact('inscricao', 'cursos', 'modalidades'))->render();
    }

    /**
     * Atualizar inscrição
     */
    public function atualizarInscricao(Request $request, $id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();

        // Verificar se o lead está travado por outro usuário
        if ($inscricao->isLockedByOther($userId)) {
            return redirect()->back()->with('error', 'Este lead está sendo atendido por ' . $inscricao->lockedBy->name . '. Não é possível atualizar.');
        }

        // Validar dados
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefone' => 'required|string|max:20',
            'curso' => 'required|string|max:100',
            'modalidade' => 'required|string|max:50',
            'etiqueta' => 'required|in:pendente,contatado,interessado,nao_interessado,matriculado',
            'notas' => 'nullable|string|max:1000'
        ]);

        // Registrar histórico de status se houver mudança
        if ($inscricao->etiqueta !== $request->etiqueta) {
            // Obter dados do usuário atual
            $usuario = User::find($userId);
            $nomeUsuario = $usuario ? $usuario->name : 'Sistema';
            $tipoUsuario = session('admin_tipo', 'admin');

            StatusHistory::create([
                'inscricao_id' => $inscricao->id,
                'status_anterior' => $inscricao->etiqueta,
                'status_novo' => $request->etiqueta,
                'alterado_por' => $nomeUsuario,
                'tipo_usuario' => $tipoUsuario,
                'observacoes' => $request->notas,
                'data_alteracao' => now()
            ]);
        }

        // Atualizar inscrição
        $inscricao->update([
            'nome' => $request->nome,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'curso' => $request->curso,
            'modalidade' => $request->modalidade,
            'etiqueta' => $request->etiqueta,
            'notas' => $request->notas
        ]);

        // Se for requisição AJAX, retornar JSON
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead atualizado com sucesso!'
            ]);
        }

        return redirect()->route('admin.inscricoes')->with('success', 'Inscrição atualizada com sucesso!');
    }

    /**
     * Atualizar status via AJAX
     */
    public function updateStatus(Request $request, $id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $user = auth()->user();

        // Verificar se o lead está travado por outro usuário
        if ($inscricao->isLockedByOther($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por ' . $inscricao->lockedBy->name . '. Não é possível alterar.'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pendente,contatado,interessado,nao_interessado,matriculado',
            'observacao' => 'nullable|string|max:500'
        ]);

        $statusAnterior = $inscricao->etiqueta;
        $novoStatus = $request->status;

        // Atualizar status
        $inscricao->etiqueta = $novoStatus;
        $inscricao->save();

        // Criar histórico
        StatusHistory::create([
            'inscricao_id' => $inscricao->id,
            'status_anterior' => $statusAnterior,
            'status_novo' => $novoStatus,
            'alterado_por' => $user->name,
            'tipo_usuario' => $user->user_type ?? 'admin',
            'data_alteracao' => now(),
            'observacoes' => $request->observacao
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status alterado com sucesso!'
        ]);
    }

    /**
     * Adicionar nota rápida via AJAX
     */
    public function addNote(Request $request, $id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $user = auth()->user();

        $request->validate([
            'nota' => 'required|string|max:500'
        ]);

        // Adicionar nota às notas existentes
        $notasAtual = $inscricao->notas ?? '';
        $novaNota = "[" . now()->format('d/m/Y H:i') . " - " . $user->name . "] " . $request->nota;
        
        if (!empty($notasAtual)) {
            $inscricao->notas = $notasAtual . "\n\n" . $novaNota;
        } else {
            $inscricao->notas = $novaNota;
        }
        
        $inscricao->save();

        // Criar histórico
        StatusHistory::create([
            'inscricao_id' => $inscricao->id,
            'status_anterior' => $inscricao->etiqueta,
            'status_novo' => $inscricao->etiqueta,
            'alterado_por' => $user->name,
            'tipo_usuario' => $user->user_type ?? 'admin',
            'data_alteracao' => now(),
            'observacoes' => "Nota adicionada: " . $request->nota
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nota adicionada com sucesso!'
        ]);
    }

    /**
     * Marcar contato via AJAX
     */
    public function markContact(Request $request, $id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $user = auth()->user();

        // Verificar se o lead está travado por outro usuário
        if ($inscricao->isLockedByOther($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por ' . $inscricao->lockedBy->name . '. Não é possível alterar.'
            ], 403);
        }

        $statusAnterior = $inscricao->etiqueta;

        // Se já está como contatado, não alterar
        if ($statusAnterior === 'contatado') {
            return response()->json([
                'success' => false,
                'message' => 'Este lead já está marcado como contatado.'
            ]);
        }

        // Atualizar para contatado
        $inscricao->etiqueta = 'contatado';
        $inscricao->save();

        // Criar histórico
        StatusHistory::create([
            'inscricao_id' => $inscricao->id,
            'status_anterior' => $statusAnterior,
            'status_novo' => 'contatado',
            'alterado_por' => $user->name,
            'tipo_usuario' => $user->user_type ?? 'admin',
            'data_alteracao' => now(),
            'observacoes' => 'Lead marcado como contatado via Kanban'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead marcado como contatado!'
        ]);
    }

    /**
     * Exportar inscrições para CSV
     */
    public function exportarCSV(Request $request)
    {
        $query = Inscricao::query();

        // Aplicar os mesmos filtros da listagem
        if ($request->filled('curso')) {
            $query->where('curso', $request->curso);
        }

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('created_at', [
                $request->data_inicio . ' 00:00:00',
                $request->data_fim . ' 23:59:59'
            ]);
        }

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('telefone', 'like', "%{$busca}%");
            });
        }

        $inscricoes = $query->orderBy('created_at', 'desc')->get();
        
        // Obter configurações de formulário para labels dinâmicos
        $formSettings = SystemSetting::getFormSettings();
        $cursos = $formSettings['available_courses'] ?? [];
        $modalidades = $formSettings['available_modalities'] ?? [];

        $filename = 'inscricoes_eja_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($inscricoes, $cursos, $modalidades) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, [
                'ID',
                'Nome',
                'Email',
                'Telefone',
                'Curso',
                'Modalidade',
                'Aceita Termos',
                'IP',
                'Data/Hora',
                'Etiqueta',
                'Posição Kanban',
                'Prioridade',
                'Notas'
            ], ';');

            // Dados
            foreach ($inscricoes as $inscricao) {
                $cursoLabel = $cursos[$inscricao->curso] ?? $inscricao->curso;
                $modalidadeLabel = $modalidades[$inscricao->modalidade] ?? $inscricao->modalidade;

                fputcsv($file, [
                    $inscricao->id,
                    $inscricao->nome,
                    $inscricao->email,
                    $inscricao->telefone,
                    $cursoLabel,
                    $modalidadeLabel,
                    $inscricao->termos ? 'Sim' : 'Não',
                    $inscricao->ip_address,
                    $inscricao->created_at->format('d/m/Y H:i:s'),
                    $inscricao->etiqueta ?? 'pendente',
                    $inscricao->kanban_order ?? 0,
                    $inscricao->prioridade ?? 'media',
                    $inscricao->notas ?? ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Deletar inscrição
     */
    public function deletarInscricao($id)
    {
        // Verificar se o usuário tem permissão para excluir
        if (!auth()->user()->hasPermission('inscricoes.delete')) {
            return redirect()->back()->with('error', 'Você não tem permissão para excluir inscrições.');
        }

        $inscricao = Inscricao::findOrFail($id);

        // Verificar se o lead está travado por outro usuário
        if ($inscricao->isLockedByOther(auth()->id())) {
            return redirect()->back()->with('error', 'Este lead está sendo atendido por ' . $inscricao->lockedBy->name . '. Não é possível excluir.');
        }

        // Excluir histórico de status
        StatusHistory::where('inscricao_id', $id)->delete();

        // Excluir inscrição
        $inscricao->delete();

        return redirect()->back()->with('success', 'Inscrição excluída com sucesso!');
    }

    /**
     * Atualizar etiqueta da inscrição
     */
    public function atualizarEtiqueta(Request $request, $id)
    {
        $request->validate([
            'etiqueta' => 'required|in:pendente,contatado,interessado,nao_interessado,matriculado',
            'observacoes' => 'nullable|string|max:500'
        ]);

        $inscricao = Inscricao::findOrFail($id);
        $userId = session('admin_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão expirada. Por favor, faça login novamente.'
            ], 401);
        }
        
        // Verificar se o lead está travado por outro usuário
        if ($inscricao->isLockedByOther($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por ' . $inscricao->lockedBy->name . '. Não é possível alterar o status.'
            ], 403);
        }

        // Verificar cooldown e limite apenas se o lead não está travado pelo usuário atual
        if (!$inscricao->isLockedBy($userId)) {
            // Se o lead está livre ou travado por outro usuário, aplicar validações
            if (!$inscricao->isLocked()) {
                // Lead está livre - verificar cooldown e limite
                if (SystemSetting::isUserInCooldown($userId)) {
                    $remainingSeconds = SystemSetting::getCooldownRemainingSeconds($userId);
                    $minutes = floor($remainingSeconds / 60);
                    $seconds = $remainingSeconds % 60;
                    
                    $timeFormatted = sprintf(
                        $minutes > 0 ? "%02d:%02d" : "00:%02d",
                        $minutes,
                        $seconds
                    );
                    
                    return response()->json([
                        'success' => false,
                        'message' => "Você deve aguardar {$timeFormatted} antes de pegar outro lead.",
                        'cooldown' => true,
                        'remaining_seconds' => $remainingSeconds
                    ], 429);
                }

                // Verificar limite máximo de leads por usuário
                $maxLeads = SystemSetting::get('max_leads_per_user', 10);
                $currentLeads = Inscricao::where('locked_by', $userId)->count();
                
                if ($currentLeads >= $maxLeads) {
                    return response()->json([
                        'success' => false,
                        'message' => "Você já atingiu o limite máximo de {$maxLeads} leads simultâneos."
                    ], 429);
                }
            }
        }

        // Se o lead não está travado, verificar se o usuário tem colunas e travar
        if (!$inscricao->isLocked()) {
            // Verificar se o usuário tem colunas configuradas
            $userColumns = \App\Models\KanbanColumn::forUser($userId)->where('is_active', true)->count();
            if ($userColumns === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você precisa configurar pelo menos uma coluna no Kanban antes de pegar leads. Acesse o Kanban e crie suas colunas.'
                ], 403);
            }
            
            $inscricao->lock($userId);
        }

        // Registrar histórico de status
        $statusAnterior = $inscricao->etiqueta;
        
        // Atualizar status
        $inscricao->update([
            'etiqueta' => $request->etiqueta
        ]);

        // Obter dados do usuário atual
        $usuario = User::find($userId);
        $nomeUsuario = $usuario ? $usuario->name : 'Sistema';
        $tipoUsuario = session('admin_tipo', 'admin');

        // Criar histórico
        StatusHistory::create([
            'inscricao_id' => $inscricao->id,
            'status_anterior' => $statusAnterior,
            'status_novo' => $request->etiqueta,
            'alterado_por' => $nomeUsuario,
            'tipo_usuario' => $tipoUsuario,
            'observacoes' => $request->observacoes,
            'data_alteracao' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso!'
        ]);
    }

    /**
     * Destravar lead
     */
    public function unlock($id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = session('admin_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Sessão expirada. Por favor, faça login novamente.'
            ], 401);
        }

        // Verificar se o lead está travado pelo usuário atual
        if (!$inscricao->isLockedBy($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode destravar este lead pois ele está travado por outro usuário.'
            ], 403);
        }

        // Destravar o lead
        $inscricao->unlock();

        return response()->json([
            'success' => true,
            'message' => 'Lead destravado com sucesso!'
        ]);
    }

    /**
     * Monitoramento de atendimento - apenas para admins
     */
    public function monitoramento(Request $request)
    {
        // Verificar se tem permissão para monitoramento
        if (!auth()->user()->hasPermission('monitoramento.index')) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }
        
        // Obter filtros
        $status = $request->get('status');
        $usuario = $request->get('usuario');
        $lockedStatus = $request->get('locked_status');
        $dataInicio = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');
        
        // Consultar leads com filtros
        $query = Inscricao::query();
        
        if ($status) {
            $query->where('etiqueta', $status);
        }
        
        if ($usuario) {
            $query->where('locked_by', $usuario);
        }
        
        if ($lockedStatus === 'locked') {
            $query->whereNotNull('locked_by');
        } elseif ($lockedStatus === 'unlocked') {
            $query->whereNull('locked_by');
        }
        
        if ($dataInicio) {
            $query->whereDate('created_at', '>=', $dataInicio);
        }
        
        if ($dataFim) {
            $query->whereDate('created_at', '<=', $dataFim);
        }
        
        // Ordenar por data de criação (mais recentes primeiro)
        $query->orderBy('created_at', 'desc');
        
        // Obter resultados paginados
        $inscricoes = $query->paginate(15);
        
        // Carregar relacionamentos
        $inscricoes->load('lockedBy');
        
        // Obter estatísticas
        $stats = [
            'total_leads' => Inscricao::count(),
            'leads_travados' => Inscricao::whereNotNull('locked_by')->count(),
            'leads_livres' => Inscricao::whereNull('locked_by')->count(),
            'leads_hoje' => Inscricao::whereDate('created_at', today())->count(),
        ];
        
        // Opções de status para o filtro
        $statusOptions = [
            'pendente' => 'Pendente',
            'contatado' => 'Contatado',
            'interessado' => 'Interessado',
            'nao_interessado' => 'Não Interessado',
            'matriculado' => 'Matriculado',
        ];
        
        // Obter lista de usuários para o filtro
        $usuarios = User::orderBy('name')->get();

        // Gerar dados para o gráfico de conversão
        $chartData = $this->getConversionChartData($request);
        
        // Se for requisição AJAX (filtro de gráfico), retornar apenas dados JSON
        if ($request->ajax() && $request->has(['chart_start_date', 'chart_end_date'])) {
            return response()->json([
                'success' => true,
                'chartData' => $chartData,
                'message' => 'Dados do gráfico atualizados com sucesso'
            ]);
        }
        
        return view('admin.monitoramento', compact(
            'inscricoes', 
            'stats', 
            'statusOptions', 
            'usuarios',
            'chartData'
        ));
    }

    /**
     * Gera dados para o gráfico de conversão
     */
    private function getConversionChartData(Request $request = null)
    {
        // Se não há request, criar um fake
        if (!$request) {
            $request = request();
        }
        
        // Definir período (últimos 7 dias por padrão)
        $startDate = $request->filled('chart_start_date') 
            ? Carbon::parse($request->chart_start_date) 
            : Carbon::now()->subDays(6)->startOfDay();
        
        $endDate = $request->filled('chart_end_date')
            ? Carbon::parse($request->chart_end_date)->endOfDay()
            : Carbon::now()->endOfDay();
        
        // Array para armazenar os dados
        $labels = [];
        $leadsData = [];
        $contatadosData = [];
        $matriculadosData = [];
        
        // Gerar dados para cada dia no período
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $formattedDate = $currentDate->format('d/m');
            
            // Adicionar label da data
            $labels[] = $formattedDate;
            
            // Contar leads recebidos neste dia
            $leadsData[] = Inscricao::whereDate('created_at', $dateString)->count();
            
            // Contar leads contatados neste dia (com status alterado para 'contatado')
            $contatadosData[] = StatusHistory::where('status_novo', 'contatado')
                ->whereDate('data_alteracao', $dateString)
                ->count();
            
            // Contar leads matriculados neste dia
            $matriculadosData[] = StatusHistory::where('status_novo', 'matriculado')
                ->whereDate('data_alteracao', $dateString)
                ->count();
            
            // Avançar para o próximo dia
            $currentDate->addDay();
        }
        
        return [
            'labels' => $labels,
            'leads' => $leadsData,
            'contatados' => $contatadosData,
            'matriculados' => $matriculadosData
        ];
    }

    /**
     * Mostrar detalhes de uma inscrição
     */
    public function show($id)
    {
        $inscricao = Inscricao::with(['lockedBy', 'statusHistories'])->findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();
        
        // Verificar se o usuário não-admin tem acesso a este lead
        // Usuários não-admin podem ver leads livres (locked_by = null) ou seus próprios leads
        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para visualizar este lead.'
            ], 403);
        }

        // Verificar se o lead está travado por outro usuário
        if ($inscricao->isLockedByOther($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por ' . $inscricao->lockedBy->name . '.'
            ]);
        }

        // Se o lead não está travado, travar para o usuário atual
        if (!$inscricao->isLocked()) {
            $inscricao->lock($userId);
        }

        // Obter configurações de formulário para labels dinâmicos
        $formSettings = SystemSetting::getFormSettings();
        $availableCourses = $formSettings['available_courses'] ?? [];
        $availableModalities = $formSettings['available_modalities'] ?? [];

        // Adicionar labels ao objeto de inscrição
        $inscricao->curso_label = $availableCourses[$inscricao->curso] ?? $inscricao->curso;
        $inscricao->modalidade_label = $availableModalities[$inscricao->modalidade] ?? $inscricao->modalidade;

        // Formatar datas para exibição
        $inscricao->data_inscricao = $inscricao->created_at->format('d/m/Y H:i');
        $inscricao->ultimo_contato_formatado = $inscricao->ultimo_contato ? $inscricao->ultimo_contato->format('d/m/Y H:i') : null;

        // Obter histórico de status
        $historico = $inscricao->statusHistories()->orderBy('data_alteracao', 'desc')->get();

        // Formatar histórico para exibição
        $historicoFormatado = [];
        foreach ($historico as $h) {
            $historicoFormatado[] = [
                'data' => $h->data_alteracao->format('d/m/Y H:i'),
                'status_anterior' => $h->status_anterior,
                'status_novo' => $h->status_novo,
                'alterado_por' => $h->alterado_por,
                'observacoes' => $h->observacoes
            ];
        }

        // Adicionar histórico formatado ao objeto de inscrição
        $inscricao->historico = $historicoFormatado;

        // Retornar dados
        return response()->json([
            'success' => true,
            'inscricao' => $inscricao
        ]);
    }

    /**
     * Mostrar página de detalhes de uma inscrição
     */
    public function detalhes($id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();
        
        // Verificar se o usuário não-admin tem acesso a este lead
        // Usuários não-admin podem ver leads livres (locked_by = null) ou seus próprios leads
        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return redirect()->back()->with('error', 'Você não tem permissão para visualizar este lead.');
        }

        // Verificar se o lead está travado por outro usuário
        if ($inscricao->isLockedByOther($userId)) {
            return redirect()->back()->with('error', 'Este lead está sendo atendido por ' . $inscricao->lockedBy->name . '. Não é possível visualizar.');
        }

        // Se o lead não está travado, travar para o usuário atual
        if (!$inscricao->isLocked()) {
            $inscricao->lock($userId);
        }

        // Obter configurações de formulário para labels dinâmicos
        $formSettings = SystemSetting::getFormSettings();
        $cursos = $formSettings['available_courses'] ?? [];
        $modalidades = $formSettings['available_modalities'] ?? [];

        return view('admin.inscricoes.detalhes', compact('inscricao', 'cursos', 'modalidades'));
    }

    /**
     * Retornar detalhes da inscrição para modal (sem layout)
     */
    public function detalhesModal($id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();
        
        // Verificar se o usuário não-admin tem acesso a este lead
        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para visualizar este lead.'
            ], 403);
        }

        // Obter configurações de formulário para labels dinâmicos
        $formSettings = SystemSetting::getFormSettings();
        $cursos = $formSettings['available_courses'] ?? [];
        $modalidades = $formSettings['available_modalities'] ?? [];

        // Retornar apenas o partial sem layout
        return view('admin.inscricoes._detalhes', compact('inscricao', 'cursos', 'modalidades'));
    }

    private function checkAccess()
    {
        if (!auth()->check()) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }

        if (!auth()->user()->hasPermission('inscricoes.index')) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }

        return null;
    }

    /**
     * Mostrar página de importação de inscrições
     */
    public function importarInscricoes()
    {
        return view('admin.inscricoes.importar');
    }

    /**
     * Processar arquivo de importação de inscrições
     */
    public function processarImportacaoInscricoes(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $arquivo = $request->file('arquivo');
            $caminho = $arquivo->getRealPath();
            
            // Ler o arquivo CSV
            $dados = $this->lerArquivoCSV($caminho);
            
            if (empty($dados)) {
                return redirect()->back()->with('error', 'Arquivo vazio ou formato inválido.');
            }

            // Validar cabeçalhos
            $cabecalhosEsperados = ['ID', 'Nome', 'Email', 'Telefone', 'Curso', 'Modalidade', 'Aceita Termos', 'IP', 'Data/Hora', 'Etiqueta', 'Posição Kanban', 'Prioridade', 'Notas'];
            $cabecalhosEncontrados = array_keys($dados[0]);
            
            $cabecalhosFaltando = array_diff($cabecalhosEsperados, $cabecalhosEncontrados);
            if (count($cabecalhosFaltando) > 0) {
                return redirect()->back()->with('error', 'Formato de arquivo inválido. Cabeçalhos faltando: ' . implode(', ', $cabecalhosFaltando));
            }

            // Obter configurações de formulário para validação
            $formSettings = SystemSetting::getFormSettings();
            $cursosValidos = array_keys($formSettings['available_courses'] ?? []);
            $modalidadesValidas = array_keys($formSettings['available_modalities'] ?? []);

            $importados = 0;
            $ignorados = 0;
            $erros = [];

            foreach ($dados as $linha => $dado) {
                try {
                    // Validar se é uma linha válida
                    if (empty($dado['Nome']) && empty($dado['Email']) && empty($dado['Telefone'])) {
                        continue; // Pular linhas vazias
                    }

                    // Verificar se já existe (por email)
                    $inscricaoExistente = Inscricao::where('email', $dado['Email'])->first();
                    if ($inscricaoExistente) {
                        $ignorados++;
                        continue;
                    }

                    // Validar dados obrigatórios
                    if (empty($dado['Nome']) || empty($dado['Email']) || empty($dado['Telefone'])) {
                        $erros[] = "Linha " . ($linha + 1) . ": Nome, Email e Telefone são obrigatórios";
                        continue;
                    }

                    // Validar email
                    if (!filter_var($dado['Email'], FILTER_VALIDATE_EMAIL)) {
                        $erros[] = "Linha " . ($linha + 1) . ": Email inválido";
                        continue;
                    }

                    // Validar curso
                    $curso = $dado['Curso'];
                    if (!empty($curso)) {
                        if (!in_array($curso, $cursosValidos)) {
                            // Tentar encontrar por label
                            $cursoEncontrado = array_search($curso, $formSettings['available_courses'] ?? []);
                            if ($cursoEncontrado === false) {
                                $erros[] = "Linha " . ($linha + 1) . ": Curso inválido: " . $curso;
                                continue;
                            }
                            $curso = $cursoEncontrado;
                        }
                    }

                    // Validar modalidade
                    $modalidade = $dado['Modalidade'];
                    if (!empty($modalidade)) {
                        if (!in_array($modalidade, $modalidadesValidas)) {
                            // Tentar encontrar por label
                            $modalidadeEncontrada = array_search($modalidade, $formSettings['available_modalities'] ?? []);
                            if ($modalidadeEncontrada === false) {
                                $erros[] = "Linha " . ($linha + 1) . ": Modalidade inválida: " . $modalidade;
                                continue;
                            }
                            $modalidade = $modalidadeEncontrada;
                        }
                    }

                    // Converter termos
                    $termos = in_array(strtolower($dado['Aceita Termos']), ['sim', 'yes', '1', 'true']);

                    // Converter data
                    $dataInscricao = null;
                    if (!empty($dado['Data/Hora'])) {
                        try {
                            $dataInscricao = Carbon::createFromFormat('d/m/Y H:i:s', $dado['Data/Hora']);
                        } catch (\Exception $e) {
                            try {
                                $dataInscricao = Carbon::parse($dado['Data/Hora']);
                            } catch (\Exception $e2) {
                                $dataInscricao = now();
                            }
                        }
                    }

                    // Processar campos do Kanban
                    $etiqueta = $dado['Etiqueta'] ?: 'pendente';
                    $posicaoKanban = intval($dado['Posição Kanban']) ?: 0;
                    $prioridade = $dado['Prioridade'] ?: 'media';
                    $notas = $dado['Notas'] ?: '';

                    // Validar etiqueta
                    $etiquetasValidas = ['pendente', 'contatado', 'interessado', 'nao_interessado', 'matriculado'];
                    if (!in_array($etiqueta, $etiquetasValidas)) {
                        $etiqueta = 'pendente';
                    }

                    // Validar prioridade
                    $prioridadesValidas = ['baixa', 'media', 'alta', 'urgente'];
                    if (!in_array($prioridade, $prioridadesValidas)) {
                        $prioridade = 'media';
                    }

                    // Calcular posição Kanban se não fornecida
                    if ($posicaoKanban <= 0) {
                        $posicaoKanban = Inscricao::where('etiqueta', $etiqueta)->max('kanban_order') + 1;
                    }

                    // Criar inscrição
                    $inscricao = Inscricao::create([
                        'nome' => $dado['Nome'],
                        'email' => $dado['Email'],
                        'telefone' => $dado['Telefone'],
                        'curso' => $curso ?: ($formSettings['default_course'] ?? 'eja'),
                        'modalidade' => $modalidade ?: ($formSettings['default_modality'] ?? 'online'),
                        'termos' => $termos,
                        'ip_address' => $dado['IP'] ?: $request->ip(),
                        'etiqueta' => $etiqueta,
                        'kanban_order' => $posicaoKanban,
                        'prioridade' => $prioridade,
                        'notas' => $notas,
                        'created_at' => $dataInscricao ?: now(),
                        'updated_at' => $dataInscricao ?: now(),
                    ]);

                    $importados++;

                } catch (\Exception $e) {
                    $erros[] = "Linha " . ($linha + 1) . ": " . $e->getMessage();
                }
            }

            $mensagem = "Importação concluída: {$importados} inscrições importadas, {$ignorados} ignoradas (já existiam)";
            if (!empty($erros)) {
                $mensagem .= ". " . count($erros) . " erros encontrados.";
            }

            return redirect()->route('admin.inscricoes')->with([
                'success' => $mensagem,
                'importados' => $importados,
                'ignorados' => $ignorados,
                'erros' => $erros
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao processar arquivo: ' . $e->getMessage());
        }
    }

    /**
     * Download do template de importação
     */
    public function downloadTemplateInscricoes()
    {
        $filename = 'template_importacao_inscricoes.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, [
                'ID',
                'Nome',
                'Email',
                'Telefone',
                'Curso',
                'Modalidade',
                'Aceita Termos',
                'IP',
                'Data/Hora',
                'Etiqueta',
                'Posição Kanban',
                'Prioridade',
                'Notas'
            ], ';');

            // Exemplo de dados
            fputcsv($file, [
                '1',
                'João Silva',
                'joao@email.com',
                '(11) 99999-9999',
                'eja',
                'online',
                'Sim',
                '192.168.1.1',
                '01/01/2024 10:00:00',
                'pendente',
                '1',
                'media',
                'Lead interessado no curso EJA'
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Ler arquivo CSV e retornar array de dados
     */
    private function lerArquivoCSV($caminho)
    {
        $dados = [];
        $handle = fopen($caminho, 'r');
        
        if ($handle === false) {
            return [];
        }

        // Detectar encoding
        $conteudo = file_get_contents($caminho);
        $encoding = mb_detect_encoding($conteudo, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        
        if ($encoding !== 'UTF-8') {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', $encoding);
            file_put_contents($caminho, $conteudo);
        }

        // Ler arquivo
        $linha = 0;
        $cabecalhos = [];
        
        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            if ($linha === 0) {
                // Cabeçalho
                $cabecalhos = $row;
            } else {
                // Dados
                $dado = [];
                foreach ($cabecalhos as $i => $cabecalho) {
                    $dado[$cabecalho] = $row[$i] ?? '';
                }
                $dados[] = $dado;
            }
            $linha++;
        }

        fclose($handle);
        return $dados;
    }

    /**
     * Contar inscrições (total e filtradas)
     */
    public function countInscricoes(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Query base
            $query = Inscricao::query();

            // Aplicar filtros se fornecidos
            if ($request->filled('curso')) {
                $query->where('curso', $request->curso);
            }

            if ($request->filled('data_inicio') && $request->filled('data_fim')) {
                $query->whereBetween('created_at', [
                    $request->data_inicio . ' 00:00:00',
                    $request->data_fim . ' 23:59:59'
                ]);
            }

            if ($request->filled('busca')) {
                $busca = $request->busca;
                $query->where(function($q) use ($busca) {
                    $q->where('nome', 'like', "%{$busca}%")
                      ->orWhere('email', 'like', "%{$busca}%")
                      ->orWhere('telefone', 'like', "%{$busca}%");
                });
            }

            // LÓGICA DE DOMÍNIO: Usuários só podem ver inscrições do seu domínio
            if (!$user->isAdmin()) {
                if ($user->isParceiro()) {
                    // Parceiros só podem ver inscrições relacionadas ao seu domínio
                    // Por enquanto, vamos limitar a inscrições que eles travaram
                    $query->where('locked_by', $user->id);
                } else {
                    // Usuários normais (vendedor, colaborador, mídia) só podem ver inscrições que eles travaram
                    $query->where('locked_by', $user->id);
                }
            }

            $total = $query->count();
            $totalGeral = Inscricao::count();

            return response()->json([
                'success' => true,
                'total' => $total,
                'total_geral' => $totalGeral,
                'scope' => $user->isAdmin() ? 'admin_full_access' : 'user_limited_access',
                'user_type' => $user->tipo_usuario
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao contar inscrições: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apagar todas as inscrições (com filtros opcionais)
     */
    public function apagarTodasInscricoes(Request $request)
    {
        try {
            // Verificar permissão para exclusão
            if (!auth()->user()->hasPermission('inscricoes.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para excluir inscrições.'
                ], 403);
            }

            $user = auth()->user();
            $query = Inscricao::query();

            // Aplicar filtros se fornecidos
            $filtros = $request->input('filtros', []);
            
            if (!empty($filtros['curso'])) {
                $query->where('curso', $filtros['curso']);
            }

            if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                $query->whereBetween('created_at', [
                    $filtros['data_inicio'] . ' 00:00:00',
                    $filtros['data_fim'] . ' 23:59:59'
                ]);
            }

            if (!empty($filtros['busca'])) {
                $busca = $filtros['busca'];
                $query->where(function($q) use ($busca) {
                    $q->where('nome', 'like', "%{$busca}%")
                      ->orWhere('email', 'like', "%{$busca}%")
                      ->orWhere('telefone', 'like', "%{$busca}%");
                });
            }

            // LÓGICA DE DOMÍNIO: Usuários só podem apagar inscrições do seu domínio
            if (!$user->isAdmin()) {
                if ($user->isParceiro()) {
                    // Parceiros só podem apagar inscrições relacionadas ao seu domínio
                    // Por enquanto, vamos limitar a inscrições que eles travaram
                    $query->where('locked_by', $user->id);
                } else {
                    // Usuários normais (vendedor, colaborador, mídia) só podem apagar inscrições que eles travaram
                    $query->where('locked_by', $user->id);
                }
            }

            // Contar antes de apagar
            $totalParaApagar = $query->count();
            $totalGeral = Inscricao::count();

            if ($totalParaApagar === 0) {
                $mensagem = $user->isAdmin() 
                    ? 'Nenhuma inscrição encontrada para apagar.'
                    : 'Você só pode apagar inscrições que você travou. Nenhuma inscrição encontrada para apagar.';
                
                return response()->json([
                    'success' => false,
                    'message' => $mensagem
                ], 404);
            }

            // Apagar em lote
            $query->delete();

            // Log da ação
            \Log::info('Exclusão em massa de inscrições', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_type' => $user->tipo_usuario,
                'parceiro_id' => $user->parceiro_id,
                'total_apagadas' => $totalParaApagar,
                'total_geral' => $totalGeral,
                'filtros_aplicados' => $filtros,
                'ip_address' => $request->ip(),
                'scope' => $user->isAdmin() ? 'admin_full_access' : 'user_limited_access'
            ]);

            $mensagem = $user->isAdmin() 
                ? "Sucesso! {$totalParaApagar} inscrições foram removidas."
                : "Sucesso! {$totalParaApagar} inscrições do seu domínio foram removidas.";

            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'total_apagadas' => $totalParaApagar,
                'total_geral' => $totalGeral,
                'scope' => $user->isAdmin() ? 'admin_full_access' : 'user_limited_access'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao apagar inscrições em massa: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao apagar inscrições: ' . $e->getMessage()
            ], 500);
        }
    }
}
