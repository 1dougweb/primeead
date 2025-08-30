<?php

namespace App\Http\Controllers;

use App\Models\Inscricao;
use App\Models\KanbanColumn;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class KanbanController extends Controller
{
    /**
     * Construtor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:kanban.index');
    }

    /**
     * Exibir o board Kanban
     */
    public function index(Request $request)
    {
        // Filtros
        $query = Inscricao::with('lockedBy');
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();
        
        // Se não for admin, mostrar leads livres (locked_by = null) e leads atribuídos ao próprio usuário
        if (!$isAdmin) {
            $query->where(function($q) use ($userId) {
                $q->whereNull('locked_by')  // Leads livres
                  ->orWhere('locked_by', $userId);  // Leads do próprio usuário
            });
        }
        
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('telefone', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        if ($request->filled('responsavel') && $isAdmin) {
            $query->where('locked_by', $request->responsavel);
        }

        // Carregar leads
        $leads = $query->orderBy('kanban_order')->orderBy('created_at', 'desc')->get();
        
        // Obter colunas do usuário atual
        $columns = KanbanColumn::forUser($userId)->orderBy('order')->get();

        // Agrupar leads por coluna
        $kanbanData = [];
        foreach ($columns as $column) {
            $kanbanData[$column->slug] = $leads->where('etiqueta', $column->slug);
        }

        // Dados para filtros
        $usuarios = \App\Models\User::where('ativo', true)->get();
        $prioridades = [
            'baixa' => 'Baixa',
            'media' => 'Média', 
            'alta' => 'Alta',
            'urgente' => 'Urgente'
        ];

        return view('admin.kanban.index', compact('kanbanData', 'usuarios', 'prioridades', 'columns'));
    }



    /**
     * Listar colunas do Kanban
     */
    public function listColumns()
    {
        $userId = auth()->id();
        $columns = KanbanColumn::forUser($userId)->orderBy('order')->get();
        return response()->json($columns);
    }

    /**
     * Criar nova coluna
     */
    public function createColumn(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|in:primary,secondary,success,danger,warning,info',
            'icon' => 'nullable|string|max:10'
        ]);

        $userId = auth()->id();

        // Gerar slug único para o usuário
        $slug = Str::slug($request->name);
        $baseSlug = $slug;
        $counter = 1;
        while (KanbanColumn::where('slug', $slug)->where('user_id', $userId)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Obter última ordem para o usuário
        $lastOrder = KanbanColumn::forUser($userId)->max('order') ?? -1;

        $column = KanbanColumn::create([
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color,
            'icon' => $request->icon,
            'order' => $lastOrder + 1,
            'is_system' => false,
            'is_active' => true,
            'user_id' => $userId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coluna criada com sucesso!',
            'column' => $column
        ]);
    }

    /**
     * Atualizar coluna
     */
    public function updateColumn(Request $request, $id)
    {
        $userId = auth()->id();
        $column = KanbanColumn::forUser($userId)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|in:primary,secondary,success,danger,warning,info',
            'icon' => 'nullable|string|max:10',
            'is_active' => 'boolean'
        ]);

        $column->update([
            'name' => $request->name,
            'color' => $request->color,
            'icon' => $request->icon,
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coluna atualizada com sucesso!',
            'column' => $column
        ]);
    }

    /**
     * Reordenar colunas
     */
    public function reorderColumns(Request $request)
    {
        $request->validate([
            'columns' => 'required|array',
            'columns.*' => 'exists:kanban_columns,id'
        ]);

        $userId = auth()->id();

        foreach ($request->columns as $index => $columnId) {
            KanbanColumn::forUser($userId)->where('id', $columnId)->update(['order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Colunas reordenadas com sucesso!'
        ]);
    }

    /**
     * Excluir coluna
     */
    public function deleteColumn($id)
    {
        $userId = auth()->id();
        $column = KanbanColumn::forUser($userId)->findOrFail($id);

        // Verificar se a coluna tem leads
        $leadsCount = Inscricao::where('etiqueta', $column->slug)->count();
        if ($leadsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Não é possível excluir esta coluna pois ela possui {$leadsCount} lead(s). Mova os leads para outra coluna antes de excluir."
            ], 403);
        }

        $column->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coluna excluída com sucesso!'
        ]);
    }

    /**
     * Mover card no Kanban (drag & drop)
     */
    public function moveCard(Request $request)
    {
        try {
            $request->validate([
                'lead_id' => 'required|exists:inscricaos,id',
                'new_status' => 'required|exists:kanban_columns,slug',
                'new_position' => 'required|integer|min:0'
            ]);

            $inscricao = Inscricao::findOrFail($request->lead_id);
            $userId = auth()->id();
            $isAdmin = auth()->user()->isAdmin();

            // Verificar se o usuário pode mover este lead
            if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este lead está sendo atendido por ' . $inscricao->lockedBy->name . '.'
                ], 403);
            }

            // Para usuários não-admin, só podem mover leads que já são seus
            if (!$isAdmin && !$inscricao->locked_by) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você só pode mover leads que estão sob sua responsabilidade.'
                ], 403);
            }

            DB::transaction(function() use ($inscricao, $request, $userId, $isAdmin) {
                $statusAnterior = $inscricao->etiqueta;
                $statusNovo = $request->new_status;
                
                // Atualizar posição dos outros cards na mesma coluna
                Inscricao::where('etiqueta', $statusNovo)
                        ->where('kanban_order', '>=', $request->new_position)
                        ->where('id', '!=', $inscricao->id)
                        ->increment('kanban_order');

                // Travar o lead se mudou de status e não estava travado
                if ($statusAnterior !== $statusNovo) {
                    // Se não estava travado, travar para o usuário atual
                    if (!$inscricao->locked_by) {
                        $inscricao->lock($userId);
                    }
                    
                    // Registrar histórico
                    \App\Models\StatusHistory::create([
                        'inscricao_id' => $inscricao->id,
                        'status_anterior' => $statusAnterior,
                        'status_novo' => $statusNovo,
                        'alterado_por' => auth()->user()->name,
                        'tipo_usuario' => auth()->user()->isAdmin() ? 'admin' : 'user',
                        'observacoes' => 'Movido via Kanban',
                        'data_alteracao' => now()
                    ]);
                }

                // Atualizar o lead - Usando o método update do modelo para garantir que os valores sejam tratados corretamente
                $inscricao->update([
                    'etiqueta' => $statusNovo,
                    'kanban_order' => $request->new_position
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Lead movido com sucesso!'
            ]);
        } catch (\Exception $e) {
            // Log do erro
            \Log::error('Erro ao mover card no Kanban: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            // Retornar erro em formato JSON
            return response()->json([
                'success' => false,
                'message' => 'Erro ao mover lead: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar informações do lead
     */
    public function updateLead(Request $request, $id)
    {
        $request->validate([
            'notas' => 'nullable|string',
            'prioridade' => 'required|in:baixa,media,alta,urgente',
            'todolist' => 'nullable|array',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'proximo_followup' => 'nullable|date'
        ]);

        // Validar todolist apenas se existir
        if ($request->has('todolist') && is_array($request->todolist)) {
            foreach ($request->todolist as $index => $item) {
                if (!is_array($item) || !isset($item['text']) || empty($item['text'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Todos os itens da lista de tarefas devem ter texto.'
                    ], 422);
                }
            }
        }

        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();

        // Verificar permissões
        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por outro usuário.'
            ], 403);
        }

        // Para usuários não-admin, só podem editar leads que são seus
        if (!$isAdmin && !$inscricao->locked_by) {
            return response()->json([
                'success' => false,
                'message' => 'Você só pode editar leads que estão sob sua responsabilidade.'
            ], 403);
        }

        // Processar todolist
        $todolist = [];
        if ($request->has('todolist') && is_array($request->todolist)) {
            foreach ($request->todolist as $item) {
                if (is_array($item) && isset($item['text']) && !empty($item['text'])) {
                    $todolist[] = [
                        'id' => $item['id'] ?? uniqid(),
                        'text' => $item['text'],
                        'completed' => isset($item['completed']) ? (bool)$item['completed'] : false,
                        'created_at' => $item['created_at'] ?? now()->toISOString()
                    ];
                }
            }
        }

        // Processar upload de fotos
        $photos = $inscricao->photos ?? [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs('leads/photos', $filename, 'public');
                
                $photos[] = [
                    'id' => uniqid(),
                    'filename' => $filename,
                    'path' => $path,
                    'original_name' => $photo->getClientOriginalName(),
                    'uploaded_at' => now()->toISOString(),
                    'uploaded_by' => auth()->user()->name
                ];
            }
        }

        $inscricao->update([
            'notas' => $request->notas,
            'prioridade' => $request->prioridade,
            'todolist' => $todolist,
            'photos' => $photos,
            'proximo_followup' => $request->proximo_followup ? 
                Carbon::parse($request->proximo_followup) : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Informações atualizadas com sucesso!',
            'lead' => $inscricao->fresh()
        ]);
    }

    /**
     * Adicionar nota rápida
     */
    public function addNote(Request $request, $id)
    {
        $request->validate([
            'nota' => 'required|string|max:1000'
        ]);

        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();

        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por outro usuário.'
            ], 403);
        }

        if (!$isAdmin && !$inscricao->locked_by) {
            return response()->json([
                'success' => false,
                'message' => 'Você só pode adicionar notas em leads que estão sob sua responsabilidade.'
            ], 403);
        }

        $notaAtual = $inscricao->notas ?? '';
        $novaNota = "[" . now()->format('d/m/Y H:i') . " - " . auth()->user()->name . "]\n" . $request->nota . "\n\n" . $notaAtual;

        $inscricao->update([
            'notas' => $novaNota,
            'ultimo_contato' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nota adicionada com sucesso!'
        ]);
    }

    /**
     * Marcar último contato
     */
    public function markContact(Request $request, $id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();

        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por outro usuário.'
            ], 403);
        }

        if (!$isAdmin && !$inscricao->locked_by) {
            return response()->json([
                'success' => false,
                'message' => 'Você só pode marcar contato em leads que estão sob sua responsabilidade.'
            ], 403);
        }

        $inscricao->update([
            'ultimo_contato' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Último contato marcado!'
        ]);
    }

    /**
     * Obter dados do lead para o modal
     */
    public function getLeadData($id)
    {
        $inscricao = Inscricao::with(['lockedBy', 'statusHistories'])->findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();
        
        // Verificar permissões - usuários só podem ver leads próprios ou leads livres
        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para visualizar este lead.'
            ], 403);
        }

        // Usuários não-admin podem visualizar leads livres (sem locked_by) ou seus próprios leads
        
        return response()->json([
            'success' => true,
            'lead' => $inscricao
        ]);
    }

    /**
     * Filtrar leads por data de follow-up
     */
    public function getFollowUps(Request $request)
    {
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();
        
        $query = Inscricao::whereNotNull('proximo_followup')
                          ->with('lockedBy');

        // Se não for admin, mostrar leads livres (locked_by = null) e leads atribuídos ao próprio usuário
        if (!$isAdmin) {
            $query->where(function($q) use ($userId) {
                $q->whereNull('locked_by')  // Leads livres
                  ->orWhere('locked_by', $userId);  // Leads do próprio usuário
            });
        }

        if ($request->filled('date')) {
            $date = $request->date;
            $query->whereDate('proximo_followup', $date);
        } else {
            // Próximos 7 dias por padrão
            $query->whereBetween('proximo_followup', [
                now()->startOfDay(),
                now()->addDays(7)->endOfDay()
            ]);
        }

        $followUps = $query->orderBy('proximo_followup')->get();

        return response()->json([
            'success' => true,
            'followups' => $followUps
        ]);
    }

    /**
     * Deletar foto do lead
     */
    public function deletePhoto(Request $request, $id)
    {
        $request->validate([
            'photo_id' => 'required|string'
        ]);

        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();

        // Verificar permissões
        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para deletar fotos deste lead.'
            ], 403);
        }

        if (!$isAdmin && !$inscricao->locked_by) {
            return response()->json([
                'success' => false,
                'message' => 'Você só pode deletar fotos de leads que estão sob sua responsabilidade.'
            ], 403);
        }

        $photos = $inscricao->photos ?? [];
        $photoToDelete = null;
        $newPhotos = [];

        foreach ($photos as $photo) {
            if ($photo['id'] === $request->photo_id) {
                $photoToDelete = $photo;
            } else {
                $newPhotos[] = $photo;
            }
        }

        if ($photoToDelete) {
            // Deletar arquivo físico
            if (file_exists(storage_path('app/public/' . $photoToDelete['path']))) {
                unlink(storage_path('app/public/' . $photoToDelete['path']));
            }

            // Atualizar banco de dados
            $inscricao->update(['photos' => $newPhotos]);

            return response()->json([
                'success' => true,
                'message' => 'Foto deletada com sucesso!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Foto não encontrada.'
        ], 404);
    }

    /**
     * Filtrar leads via AJAX
     */
    public function filter(Request $request)
    {
        // Filtros
        $query = Inscricao::with('lockedBy');
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();
        
        // Se não for admin, mostrar leads livres (locked_by = null) e leads atribuídos ao próprio usuário
        if (!$isAdmin) {
            $query->where(function($q) use ($userId) {
                $q->whereNull('locked_by')  // Leads livres
                  ->orWhere('locked_by', $userId);  // Leads do próprio usuário
            });
        }
        
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('telefone', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        if ($request->filled('responsavel') && $isAdmin) {
            $query->where('locked_by', $request->responsavel);
        }

        // Agrupar por status (colunas do Kanban)
        $leads = $query->orderBy('kanban_order')->orderBy('created_at', 'desc')->get();
        
        // Obter colunas ativas do usuário
        $columns = KanbanColumn::forUser($userId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Agrupar leads por coluna
        $kanbanData = [];
        foreach ($columns as $column) {
            $kanbanData[$column->slug] = $leads->where('etiqueta', $column->slug);
        }

        return response()->json([
            'success' => true,
            'kanbanData' => $kanbanData
        ]);
    }

    /**
     * Obter tarefas do lead
     */
    public function getTasks($id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();

        // Verificar permissões
        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por outro usuário.'
            ], 403);
        }

        // Obter tarefas do lead
        $tasks = $inscricao->todolist ?? [];
        
        // Formatar para o formato esperado pelo frontend
        $formattedTasks = [];
        foreach ($tasks as $task) {
            $formattedTasks[] = [
                'id' => $task['id'] ?? uniqid(),
                'title' => $task['text'] ?? $task['title'] ?? '',
                'description' => $task['description'] ?? '',
                'status' => $task['completed'] ? 'completed' : 'pending',
                'priority' => $task['priority'] ?? 'media',
                'due_date' => $task['due_date'] ?? null,
                'created_at' => $task['created_at'] ?? now()->toISOString(),
                'updated_at' => $task['updated_at'] ?? now()->toISOString()
            ];
        }

        return response()->json([
            'success' => true,
            'tasks' => $formattedTasks
        ]);
    }

    /**
     * Criar nova tarefa para o lead
     */
    public function createTask(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:baixa,media,alta,urgente',
            'due_date' => 'nullable|date'
        ]);

        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();

        // Verificar permissões
        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por outro usuário.'
            ], 403);
        }

        // Criar nova tarefa
        $taskId = uniqid();
        $newTask = [
            'id' => $taskId,
            'title' => $request->title,
            'text' => $request->title, // Para compatibilidade com o formato antigo
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'status' => 'pending',
            'completed' => false, // Para compatibilidade com o formato antigo
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
            'created_by' => auth()->user()->name
        ];

        // Adicionar à lista de tarefas
        $todolist = $inscricao->todolist ?? [];
        $todolist[] = $newTask;
        
        $inscricao->update(['todolist' => $todolist]);

        // Registrar no histórico
        \App\Models\StatusHistory::create([
            'inscricao_id' => $inscricao->id,
            'status_anterior' => $inscricao->etiqueta,
            'status_novo' => $inscricao->etiqueta,
            'alterado_por' => auth()->user()->name,
            'tipo_usuario' => auth()->user()->isAdmin() ? 'admin' : 'user',
            'observacoes' => 'Tarefa adicionada: ' . $request->title,
            'data_alteracao' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tarefa adicionada com sucesso!',
            'task' => $newTask
        ]);
    }

    /**
     * Atualizar tarefa do lead
     */
    public function updateTask(Request $request, $id, $taskId)
    {
        $request->validate([
            'status' => 'required|in:pending,completed',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:baixa,media,alta,urgente',
            'due_date' => 'nullable|date'
        ]);

        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();

        // Verificar permissões
        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por outro usuário.'
            ], 403);
        }

        // Atualizar tarefa
        $todolist = $inscricao->todolist ?? [];
        $updated = false;
        $taskTitle = '';

        foreach ($todolist as $key => $task) {
            if (($task['id'] ?? '') === $taskId) {
                $taskTitle = $task['title'] ?? $task['text'] ?? 'Tarefa';
                
                // Atualizar campos
                if ($request->has('title')) {
                    $todolist[$key]['title'] = $request->title;
                    $todolist[$key]['text'] = $request->title; // Para compatibilidade
                }
                
                if ($request->has('description')) {
                    $todolist[$key]['description'] = $request->description;
                }
                
                if ($request->has('priority')) {
                    $todolist[$key]['priority'] = $request->priority;
                }
                
                if ($request->has('due_date')) {
                    $todolist[$key]['due_date'] = $request->due_date;
                }
                
                // Status
                $todolist[$key]['status'] = $request->status;
                $todolist[$key]['completed'] = $request->status === 'completed';
                $todolist[$key]['updated_at'] = now()->toISOString();
                
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Tarefa não encontrada.'
            ], 404);
        }

        $inscricao->update(['todolist' => $todolist]);

        // Registrar no histórico
        \App\Models\StatusHistory::create([
            'inscricao_id' => $inscricao->id,
            'status_anterior' => $inscricao->etiqueta,
            'status_novo' => $inscricao->etiqueta,
            'alterado_por' => auth()->user()->name,
            'tipo_usuario' => auth()->user()->isAdmin() ? 'admin' : 'user',
            'observacoes' => 'Tarefa "' . $taskTitle . '" ' . 
                ($request->status === 'completed' ? 'concluída' : 'atualizada'),
            'data_alteracao' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tarefa atualizada com sucesso!'
        ]);
    }

    /**
     * Excluir tarefa do lead
     */
    public function deleteTask($id, $taskId)
    {
        $inscricao = Inscricao::findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();

        // Verificar permissões
        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por outro usuário.'
            ], 403);
        }

        // Excluir tarefa
        $todolist = $inscricao->todolist ?? [];
        $deleted = false;
        $taskTitle = '';

        foreach ($todolist as $key => $task) {
            // Converter para string para garantir comparação correta
            $taskIdString = (string)($task['id'] ?? '');
            $searchIdString = (string)$taskId;
            
            if ($taskIdString === $searchIdString) {
                $taskTitle = $task['title'] ?? $task['text'] ?? 'Tarefa';
                unset($todolist[$key]);
                $deleted = true;
                break;
            }
        }

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Tarefa não encontrada.'
            ], 404);
        }

        // Reindexar array
        $todolist = array_values($todolist);
        $inscricao->update(['todolist' => $todolist]);

        // Registrar no histórico
        \App\Models\StatusHistory::create([
            'inscricao_id' => $inscricao->id,
            'status_anterior' => $inscricao->etiqueta,
            'status_novo' => $inscricao->etiqueta,
            'alterado_por' => auth()->user()->name,
            'tipo_usuario' => auth()->user()->isAdmin() ? 'admin' : 'user',
            'observacoes' => 'Tarefa "' . $taskTitle . '" removida',
            'data_alteracao' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tarefa excluída com sucesso!'
        ]);
    }

    /**
     * Obter histórico do lead
     */
    public function getHistory($id)
    {
        $inscricao = Inscricao::with('statusHistories')->findOrFail($id);
        $userId = auth()->id();
        $isAdmin = auth()->user()->isAdmin();

        // Verificar permissões
        if (!$isAdmin && $inscricao->locked_by && $inscricao->locked_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Este lead está sendo atendido por outro usuário.'
            ], 403);
        }

        // Formatar histórico para o frontend
        $history = $inscricao->statusHistories->map(function($item) {
            return [
                'id' => $item->id,
                'action' => $this->formatHistoryAction($item->status_anterior, $item->status_novo),
                'description' => $item->observacoes,
                'user_name' => $item->alterado_por,
                'created_at' => $item->data_alteracao->toISOString(),
                'status_anterior' => $item->status_anterior,
                'status_novo' => $item->status_novo
            ];
        })->sortByDesc('created_at')->values();

        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }

    /**
     * Formatar ação do histórico para exibição
     */
    private function formatHistoryAction($statusAnterior, $statusNovo)
    {
        $statusLabels = [
            'pendente' => 'Pendente',
            'contatado' => 'Contatado',
            'interessado' => 'Interessado',
            'nao_interessado' => 'Não Interessado',
            'matriculado' => 'Matriculado'
        ];

        return 'Moveu de ' . 
            ($statusLabels[$statusAnterior] ?? $statusAnterior) . 
            ' para ' . 
            ($statusLabels[$statusNovo] ?? $statusNovo);
    }
}
