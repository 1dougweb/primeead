@extends('layouts.admin')

@section('title', 'Kanban - Gerenciamento de Leads')

@push('styles')
<style>
    /* Estilos para limitar texto nos cards do Kanban */
    .kanban-card .card-title {
        font-size: 0.9rem;
        font-weight: 600;
        line-height: 1.2;
        margin-bottom: 0.5rem;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    .kanban-card .text-muted {
        font-size: 0.75rem;
        line-height: 1.2;
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        display: block;
    }
    
    .kanban-card .card-body {
        overflow: hidden;
    }
    
    .kanban-card .flex-grow-1 {
        min-width: 0;
        overflow: hidden;
    }
    
    /* Scrollbar personalizado para o container horizontal das colunas */
    .kanban-container::-webkit-scrollbar {
        height: 8px !important;
    }
    
    .kanban-container::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05) !important;
        border-radius: 10px !important;
        margin: 0 10px !important;
    }
    
    .kanban-container::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border-radius: 10px !important;
        border: 2px solid rgba(255, 255, 255, 0.8) !important;
        transition: all 0.3s ease !important;
    }
    
    .kanban-container::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%) !important;
        transform: scale(1.05) !important;
    }
    
    .kanban-container::-webkit-scrollbar-corner {
        background: transparent !important;
    }
    
    /* Scrollbar vertical para o conteúdo das colunas */
    .kanban-cards::-webkit-scrollbar {
        width: 6px !important;
    }
    
    .kanban-cards::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.03) !important;
        border-radius: 8px !important;
        margin: 2px 0 !important;
    }
    
    .kanban-cards::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #9ca3af 0%, #6b7280 100%) !important;
        border-radius: 8px !important;
        border: 1px solid rgba(255, 255, 255, 0.9) !important;
        transition: all 0.3s ease !important;
    }
    
    .kanban-cards::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #6b7280 0%, #4b5563 100%) !important;
        transform: scaleX(1.2) !important;
    }
    
    /* Scrollbar para Firefox */
    .kanban-container {
        scrollbar-width: thin !important;
        scrollbar-color: #667eea rgba(0, 0, 0, 0.05) !important;
    }
    
    .kanban-cards {
        scrollbar-width: thin !important;
        scrollbar-color: #9ca3af rgba(0, 0, 0, 0.03) !important;
    }
    
    /* Animações suaves para os scrollbars */
    .kanban-container::-webkit-scrollbar-thumb,
    .kanban-cards::-webkit-scrollbar-thumb {
        animation: scrollbarGlow 2s ease-in-out infinite alternate !important;
    }
    
    @keyframes scrollbarGlow {
        0% {
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        100% {
            box-shadow: 0 0 15px rgba(102, 126, 234, 0.6);
        }
    }
    
    /* Scrollbar para dispositivos móveis */
    @media (max-width: 768px) {
        .kanban-container::-webkit-scrollbar {
            height: 6px !important;
        }
        
        .kanban-cards::-webkit-scrollbar {
            width: 4px !important;
        }
        
        .kanban-container::-webkit-scrollbar-thumb,
        .kanban-cards::-webkit-scrollbar-thumb {
            border-width: 1px !important;
        }
    }
</style>
@endpush

@section('page-title', 'Kanban Board')

@section('page-actions')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-success" onclick="showColumnModal()">
            <i class="fas fa-columns me-2"></i>Gerenciar Colunas
        </button>
        <button type="button" class="btn btn-outline-primary" onclick="toggleFilters()">
            <i class="fas fa-filter me-2"></i>Filtros
        </button>
        <a href="{{ route('admin.inscricoes') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list me-2"></i>Visão Lista
        </a>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        
        <!-- Informações sobre Colunas Personalizadas -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <div>
                            <strong>Kanban Personalizado:</strong> Estas são suas colunas exclusivas. Você pode criar, editar e excluir colunas (apenas se não houver leads nelas). 
                            Cada usuário tem seu próprio conjunto de colunas no Kanban.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="row mb-4 d-none" id="filtersCard">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <form id="kanbanFilterForm" class="row g-0">
                            <!-- Barra de Busca Principal -->
                            <div class="col-12 bg-light border-bottom p-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-start-0 ps-0" 
                                           id="filterSearch" 
                                           placeholder="Buscar por nome, email ou telefone...">
                                </div>
                            </div>
                            
                            <!-- Filtros Avançados -->
                            <div class="col-12 p-3">
                                <div class="row g-3">
                                    <!-- Status -->
                                    <div class="col-md-3">
                                        <label class="form-label d-flex align-items-center">
                                            <i class="fas fa-tag me-2 text-muted"></i>
                                            Status
                                        </label>
                                        <select class="form-select" id="filterStatus">
                                            <option value="">Todos os Status</option>
                                            <option value="pendente">🟡 Pendente</option>
                                            <option value="contatado">🔵 Contatado</option>
                                            <option value="interessado">🟢 Interessado</option>
                                            <option value="nao_interessado">🔴 Não Interessado</option>
                                            <option value="matriculado">⭐ Matriculado</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Prioridade -->
                                    <div class="col-md-3">
                                        <label class="form-label d-flex align-items-center">
                                            <i class="fas fa-exclamation-circle me-2 text-muted"></i>
                                            Prioridade
                                        </label>
                                        <select class="form-select" id="filterPrioridade">
                                            <option value="">Todas as Prioridades</option>
                                            <option value="baixa">Baixa</option>
                                            <option value="media">Média</option>
                                            <option value="alta">Alta</option>
                                            <option value="urgente">Urgente</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Período -->
                                    <div class="col-md-4">
                                        <label class="form-label d-flex align-items-center">
                                            <i class="fas fa-calendar me-2 text-muted"></i>
                                            Período
                                        </label>
                                        <div class="input-group">
                                            <input type="date" class="form-control" id="filterDataInicio" placeholder="Data Início">
                                            <span class="input-group-text bg-light">até</span>
                                            <input type="date" class="form-control" id="filterDataFim" placeholder="Data Fim">
                                        </div>
                                    </div>
                                    
                                    <!-- Botões de Ação -->
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="d-flex gap-2 w-100">
                                            <button type="button" class="btn btn-light flex-grow-1" onclick="clearFilters()">
                                                <i class="fas fa-eraser me-1"></i>
                                                Limpar
                                            </button>
                                            <button type="button" class="btn btn-primary flex-grow-1" onclick="applyFilters()">
                                                <i class="fas fa-filter me-1"></i>
                                                Filtrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <!-- Kanban Board -->
        <div class="kanban-board">
        <div class="kanban-container">
            @foreach($columns as $column)
                <div class="kanban-column">
                    <div class="card h-100">
                        <div class="card-header bg-{{ $column->color }} text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">{{ $column->name }}</h6>
                                <span class="badge bg-light text-dark">
                                    {{ $kanbanData[$column->slug]->count() }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-2 kanban-cards scroll-container" data-status="{{ $column->slug }}">
                            @foreach($kanbanData[$column->slug] as $lead)
                                <div class="kanban-card" data-id="{{ $lead->id }}" data-status="{{ $lead->etiqueta }}" ondblclick="openEditModal({{ $lead->id }})">
                                    <div class="card mb-2">
                                        <div class="card-body p-3 rounded-3 shadow-lg border" >
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title mb-1" title="{{ $lead->nome }}">
                                                        @if(strlen($lead->nome) > 30)
                                                            {{ substr($lead->nome, 0, 27) }}...
                                                        @else
                                                            {{ $lead->nome }}
                                                        @endif
                                                    </h6>
                                                    <small class="text-muted" title="{{ $lead->email }}">
                                                        @if(strlen($lead->email) > 25)
                                                            {{ substr($lead->email, 0, 22) }}...
                                                        @else
                                                            {{ $lead->email }}
                                                        @endif
                                                    </small>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                            type="button" 
                                                            id="dropdownMenuButton{{ $lead->id }}"
                                                            data-bs-toggle="dropdown"
                                                            data-bs-auto-close="true"
                                                            aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $lead->id }}">
                                                        <li>
                                                            <button class="dropdown-item" type="button" onclick="event.stopPropagation(); openEditModal({{ $lead->id }});">
                                                                <i class="fas fa-edit me-2"></i>Editar Lead
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" type="button" onclick="event.stopPropagation(); addQuickNote({{ $lead->id }});">
                                                            <i class="fas fa-sticky-note me-2"></i>Nota Rápida
                                                            </button>
                                                        {{-- </li>
                                                        <li>
                                                            <button class="dropdown-item" type="button" onclick="event.stopPropagation(); markContact({{ $lead->id }});">
                                                                <i class="fas fa-phone-alt me-2"></i>Marcar Contato
                                                            </button>
                                                        </li> --}}
                                                        <li>
                                                            <button class="dropdown-item" type="button" onclick="event.stopPropagation(); changeStatus({{ $lead->id }});">
                                                                <i class="fas fa-exchange-alt me-2"></i>Alterar Status
                                                            </button>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="event.stopPropagation(); window.open('https://wa.me/55{{ preg_replace('/[^0-9]/', '', $lead->telefone) }}', '_blank');">
                                                                <i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Web
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="event.stopPropagation(); window.open('mailto:{{ $lead->email }}', '_blank');">
                                                                <i class="fas fa-envelope me-2 text-primary"></i>Enviar Email
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="event.stopPropagation(); window.open('tel:{{ $lead->telefone }}', '_self');">
                                                                <i class="fas fa-phone me-2 text-info"></i>Ligar
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-phone me-1"></i>{{ $lead->telefone }}
                                                </small>
                                            </div>

                                            <div class="mb-2">
                                                <span class="badge bg-{{ match($lead->prioridade ?? 'media') { 'baixa' => 'success', 'media' => 'warning', 'alta' => 'orange', 'urgente' => 'danger', default => 'secondary' } }}">
                                                    {{ ucfirst($lead->prioridade ?? 'média') }}
                                                </span>
                                            </div>

                                            <!-- Botões de ação rápida -->
                                                <div class="mb-2">
                                                <div class="d-flex gap-1">
                                                    <a href="https://wa.me/55{{ preg_replace('/[^0-9]/', '', $lead->telefone) }}" 
                                                       target="_blank" 
                                                       class="btn btn-success btn-sm"
                                                       onclick="event.stopPropagation();"
                                                       title="WhatsApp">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>
                                                    <a href="mailto:{{ $lead->email }}" 
                                                       class="btn btn-primary btn-sm"
                                                       onclick="event.stopPropagation();"
                                                       title="Enviar Email">
                                                        <i class="fas fa-envelope"></i>
                                                    </a>
                                                    <a href="tel:{{ $lead->telefone }}" 
                                                       class="btn btn-info btn-sm"
                                                       onclick="event.stopPropagation();"
                                                       title="Ligar">
                                                        <i class="fas fa-phone"></i>
                                                    </a>
                                                </div>
                                                </div>

                                            @if($lead->isLocked())
                                                <div class="mb-2">
                                                    <small class="text-warning">
                                                        <i class="fas fa-lock me-1"></i>
                                                        {{ $lead->locked_by == session('admin_id') ? 'Seu' : ($lead->lockedBy->name ?? 'Usuário') }}
                                                    </small>
                                                </div>
                                            @endif

                                            <div class="mt-2 pt-2 border-top">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-plus me-1"></i>
                                                    {{ $lead->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal para editar lead -->
    <div class="modal fade" id="leadModal" tabindex="-1" aria-labelledby="leadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="leadModalLabel">
                        <i class="fas fa-edit me-2"></i>Editar Lead
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Sistema de abas -->
                    <ul class="nav nav-tabs nav-fill" id="leadModalTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-tab-pane" type="button" role="tab" aria-controls="info-tab-pane" aria-selected="true">
                                <i class="fas fa-user-edit me-2"></i>Informações do Lead
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks-tab-pane" type="button" role="tab" aria-controls="tasks-tab-pane" aria-selected="false">
                                <i class="fas fa-tasks me-2"></i>Lista de Tarefas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-tab-pane" type="button" role="tab" aria-controls="history-tab-pane" aria-selected="false">
                                <i class="fas fa-history me-2"></i>Histórico
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Conteúdo das abas -->
                    <div class="tab-content" id="leadModalTabsContent">
                        <!-- Aba de informações -->
                        <div class="tab-pane fade show active p-4" id="info-tab-pane" role="tabpanel" aria-labelledby="info-tab" tabindex="0">
                            <div id="leadModalContent">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <p class="mt-3 text-muted">Carregando dados do lead...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Aba de tarefas -->
                        <div class="tab-pane fade p-4" id="tasks-tab-pane" role="tabpanel" aria-labelledby="tasks-tab" tabindex="0">
                            <div id="leadTasksContent">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clipboard-list me-2"></i>Lista de Tarefas
                                    </h5>
                                    <button type="button" class="btn btn-sm btn-primary" id="addTaskBtn">
                                        <i class="fas fa-plus me-2"></i>Nova Tarefa
                                    </button>
                                </div>
                                
                                <div id="tasksList" class="mb-4">
                                    <div class="text-center py-4" id="tasksLoading">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Carregando...</span>
                                        </div>
                                        <p class="mt-3 text-muted">Carregando tarefas...</p>
                                    </div>
                                    
                                    <div id="tasksContainer" class="d-none">
                                        <!-- As tarefas serão carregadas aqui via JavaScript -->
                                        <div class="alert alert-info" id="noTasksMessage">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Nenhuma tarefa cadastrada para este lead.
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Formulário para adicionar nova tarefa -->
                                <div class="card border mb-3 d-none" id="newTaskForm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Nova Tarefa</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="taskTitle" class="form-label">Título</label>
                                            <input type="text" class="form-control" id="taskTitle" placeholder="Título da tarefa">
                                        </div>
                                        <div class="mb-3">
                                            <label for="taskDescription" class="form-label">Descrição</label>
                                            <textarea class="form-control" id="taskDescription" rows="2" placeholder="Descrição da tarefa"></textarea>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="taskDueDate" class="form-label">Data de Vencimento</label>
                                                    <input type="date" class="form-control" id="taskDueDate">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="taskPriority" class="form-label">Prioridade</label>
                                                    <select class="form-select" id="taskPriority">
                                                        <option value="baixa">Baixa</option>
                                                        <option value="media" selected>Média</option>
                                                        <option value="alta">Alta</option>
                                                        <option value="urgente">Urgente</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-secondary" id="cancelTaskBtn">
                                                <i class="fas fa-times me-2"></i>Cancelar
                                            </button>
                                            <button type="button" class="btn btn-primary" id="saveTaskBtn">
                                                <i class="fas fa-save me-2"></i>Salvar Tarefa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Aba de histórico -->
                        <div class="tab-pane fade p-4" id="history-tab-pane" role="tabpanel" aria-labelledby="history-tab" tabindex="0">
                            <div id="leadHistoryContent">
                                <h5 class="mb-4">
                                    <i class="fas fa-history me-2"></i>Histórico de Atividades
                                </h5>
                                
                                <div class="text-center py-4" id="historyLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <p class="mt-3 text-muted">Carregando histórico...</p>
                                </div>
                                
                                <div id="historyContainer" class="d-none">
                                    <!-- O histórico será carregado aqui via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-none" id="leadModalFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveLeadChanges()">
                        <i class="fas fa-save me-2"></i>Salvar Alterações
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para alterar status -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">
                        <i class="fas fa-exchange-alt me-2"></i>Alterar Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="statusForm">
                        <div class="mb-3">
                            <label class="form-label">Novo Status:</label>
                            <select class="form-select" name="status" required>
                                <option value="">Selecione um status</option>
                                <option value="pendente">🟡 Pendente</option>
                                <option value="contatado">🔵 Contatado</option>
                                <option value="interessado">🟢 Interessado</option>
                                <option value="nao_interessado">🔴 Não Interessado</option>
                                <option value="matriculado">⭐ Matriculado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observação (opcional):</label>
                            <textarea class="form-control" name="observacao" rows="3" placeholder="Adicione uma observação sobre a mudança de status..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveStatusChange()">
                        <i class="fas fa-check me-2"></i>Alterar Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Follow-ups -->
    <div class="modal fade" id="followUpsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Follow-ups Programados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="followUpsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Nota Rápida -->
    <div class="modal fade" id="quickNoteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Nota Rápida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickNoteForm">
                        <div class="mb-3">
                            <label class="form-label">Nota</label>
                            <textarea class="form-control" name="nota" rows="4" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveQuickNote()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Gerenciar Colunas -->
    <div class="modal fade" id="columnModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gerenciar Colunas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                        <div class="alert alert-warning mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                            <div>
                                <strong>Importante:</strong> Estas são suas colunas pessoais do Kanban. Você só pode excluir colunas que não possuem leads. 
                                Quando você pega um lead, ele é automaticamente colocado na sua primeira coluna.
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-success mb-3" onclick="showNewColumnForm()">
                        <i class="fas fa-plus me-2"></i>Nova Coluna
                    </button>

                    <div id="columnForm" style="display: none;" class="card mb-3">
                        <div class="card-body">
                            <form onsubmit="saveColumn(event)">
                                <input type="hidden" name="id">
                                <div class="mb-3">
                                    <label class="form-label">Nome da Coluna</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cor</label>
                                    <select class="form-select" name="color" required>
                                        <option value="primary">Azul</option>
                                        <option value="secondary">Cinza</option>
                                        <option value="success">Verde</option>
                                        <option value="danger">Vermelho</option>
                                        <option value="warning">Amarelo</option>
                                        <option value="info">Azul Claro</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ícone</label>
                                    <input type="text" class="form-control" name="icon" placeholder="Ícone (opcional)">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                    <button type="button" class="btn btn-light" onclick="hideColumnForm()">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Cor</th>
                                    <th>Ícone</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="columnsTable">
                                <!-- Preenchido via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
/* KANBAN BOARD - LAYOUT FUNCIONAL */
body { margin: 0; padding: 0; overflow-x: hidden; }

.main-content { 
    padding: 0 !important; 
    margin: 0 !important;
    overflow: hidden;
}

.container-fluid { 
    padding: 20px !important;
    margin: 0;
    margin-bottom: 20px!important;
}

/* KANBAN PRINCIPAL */
.kanban-board {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    height: calc(100vh - 40px);
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    cursor: grab;
    user-select: none;
    scroll-behavior: smooth;
    transition: all 0.2s ease;
}

.kanban-board:active {
    cursor: grabbing;
}

.kanban-board.dragging {
    cursor: grabbing !important;
    user-select: none;
    scroll-behavior: auto; /* Desativar smooth durante drag para melhor responsividade */
}

.kanban-board.can-drag {
    cursor: grab;
}

.kanban-board.can-drag:active,
.kanban-board.is-dragging {
    cursor: grabbing !important;
}

.kanban-container {
    display: flex !important;
    flex-direction: row !important;
    gap: 20px;

}

/* COLUNAS */
.kanban-column {
    flex: 0 0 320px;
    width: 320px;
    height: 100%;
}

.kanban-column .card {
    height: 100%;
    display: flex;
    flex-direction: column;
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.card-header {
    flex-shrink: 0;
    padding: 16px;
    border-radius: 12px 12px 0 0 !important;
    font-weight: 600;
}

/* ÁREA DOS CARDS */
.kanban-cards {
    flex: 1;
    overflow-y: scroll !important; /* FORÇAR SCROLLBAR */
    overflow-x: hidden !important;
    padding: 12px;
    height: 0; /* Força o flex a funcionar */
    scrollbar-width: auto !important; /* Firefox */
    position: relative;
}

/* BACKGROUND QUANDO VAZIO */
.kanban-cards.empty-column::before {
    content: "📥\A Nenhum lead";
    white-space: pre;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #6c757d;
    font-size: 1.1rem;
    text-align: center;
    line-height: 1.8;
    pointer-events: none;
    user-select: none;
    opacity: 0.7;
    z-index: 1;
}

/* SCROLL CONTAINER - Forçar scrollbar vertical */
.scroll-container {
    overflow-y: scroll !important; /* Sempre mostrar scrollbar */
    overflow-x: hidden !important;
    max-height: calc(100vh - 200px) !important;
    min-height: 400px !important;
    scrollbar-width: auto !important; /* Firefox */
    -ms-overflow-style: scrollbar !important; /* IE */
}

/* CARDS DOS LEADS */
.kanban-card {
    margin-bottom: 12px;
    cursor: grab;
    transition: transform 0.2s ease;
}

.kanban-card:hover {
    transform: translateY(-2px);
}

.kanban-card:active { cursor: grabbing; }

.kanban-card .card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.kanban-card:hover .card {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #ced4da;
}

.kanban-card .card-body {
    padding: 16px;
}

.kanban-card .card-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
    font-size: 0.95rem;
}

/* BOTÕES DE AÇÃO */
.kanban-card .btn-sm {
    padding: 6px 10px;
    border-radius: 8px;
    min-width: 36px;
    height: 32px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
}

.btn-success { background: #25d366 !important; }
.btn-primary { background: #007bff !important; }
.btn-info { background: #17a2b8 !important; }

.kanban-card .btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

/* DROPDOWN - MELHORADO */
.kanban-card .dropdown {
    position: relative !important;
    z-index: 1050 !important;
}

.kanban-card .dropdown-toggle {
    border: 1px solid #dee2e6 !important;
    background: white !important;
    z-index: 1051 !important;
    position: relative !important;
    padding: 4px 8px !important;
    font-size: 12px !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
}

.kanban-card .dropdown-toggle:hover {
    background: #f8f9fa !important;
    border-color: #adb5bd !important;
    transform: scale(1.05) !important;
}

.kanban-card .dropdown-toggle:focus,
.kanban-card .dropdown-toggle:active,
.kanban-card .dropdown-toggle.show {
    background: #e9ecef !important;
    border-color: #6c757d !important;
    box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25) !important;
}

.kanban-card .dropdown-menu {
    z-index: 1060 !important;
    border: none !important;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    border-radius: 10px !important;
    min-width: 220px !important;
    padding: 8px 0 !important;
    margin-top: 5px !important;
    transform: translateY(0) !important;
    animation: dropdownFadeIn 0.15s ease-out !important;
}

@keyframes dropdownFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.kanban-card .dropdown-item {
    padding: 12px 16px !important;
    font-size: 0.875rem !important;
    transition: all 0.2s ease !important;
    border: none !important;
    background: transparent !important;
    width: 100% !important;
    text-align: left !important;
    color: #495057 !important;
    display: flex !important;
    align-items: center !important;
}

.kanban-card .dropdown-item:hover {
    background: #f8f9fa !important;
    transform: translateX(4px) !important;
    color: #212529 !important;
}

.kanban-card .dropdown-item:active {
    background: #e9ecef !important;
    color: #212529 !important;
}

.kanban-card .dropdown-item i {
    width: 16px !important;
    text-align: center !important;
    margin-right: 8px !important;
}

.kanban-card .dropdown-divider {
    margin: 4px 0 !important;
    border-color: #e9ecef !important;
}

/* SCROLLBARS - MUITO VISÍVEIS */
.kanban-cards::-webkit-scrollbar,
.scroll-container::-webkit-scrollbar { 
    width: 12px !important; /* MAIS LARGO */
    background: #e0e0e0 !important;
}

.kanban-cards::-webkit-scrollbar-track,
.scroll-container::-webkit-scrollbar-track { 
    background: #f0f0f0 !important;
    border-radius: 6px !important;
    border: 1px solid #ddd !important;
}

.kanban-cards::-webkit-scrollbar-thumb,
.scroll-container::-webkit-scrollbar-thumb { 
    background: #666 !important; /* ESCURO E VISÍVEL */
    border-radius: 6px !important;
    border: 2px solid #f0f0f0 !important;
    min-height: 30px !important;
}

.kanban-cards::-webkit-scrollbar-thumb:hover,
.scroll-container::-webkit-scrollbar-thumb:hover { 
    background: #333 !important; /* AINDA MAIS ESCURO */
}

.kanban-cards::-webkit-scrollbar-corner,
.scroll-container::-webkit-scrollbar-corner {
    background: #f0f0f0 !important;
}

.kanban-board::-webkit-scrollbar { height: 8px; }
.kanban-board::-webkit-scrollbar-track { background: transparent; }
.kanban-board::-webkit-scrollbar-thumb { 
    background: rgba(0,0,0,0.3); 
    border-radius: 4px; 
}

/* ESTILOS DO MODAL COM ABAS */
.modal-xl .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 50px rgba(0,0,0,0.1);
}

.modal-header.bg-light {
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef;
    padding: 15px 20px;
}

.modal-header .modal-title {
    font-weight: 600;
    font-size: 1.2rem;
}

.nav-tabs {
    border-bottom: none;
    background-color: #f8f9fa;
    padding: 0 10px;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    font-weight: 500;
    padding: 12px 20px;
    border-radius: 0;
    transition: all 0.2s ease;
}

.nav-tabs .nav-link:hover {
    color: #495057;
    background-color: rgba(0,0,0,0.02);
}

.nav-tabs .nav-link.active {
    color: #007bff;
    background-color: #fff;
    border-bottom: 2px solid #007bff;
    font-weight: 600;
}

.tab-content {
    background-color: #fff;
    min-height: 400px;
}

.tab-pane {
    padding: 20px;
}

/* Estilos para tarefas */
.list-group-item {
    border-radius: 8px !important;
    margin-bottom: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.list-group-item .form-check-input {
    margin-top: 3px;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.list-group-item .form-check-label {
    padding-left: 8px;
    font-weight: 500;
    cursor: pointer;
}

/* Estilos para o timeline do histórico */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-badge {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    text-align: center;
    line-height: 40px;
    font-size: 14px;
    color: white;
    font-weight: bold;
    z-index: 1;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* RESPONSIVO */
@media (max-width: 768px) {
    .kanban-column { flex: 0 0 280px; width: 280px; }
    .container-fluid { padding: 10px !important; }
    .kanban-board { padding: 10px; }
}
</style>
@endsection

@push('scripts')
<script>
// Função para inicializar o Kanban quando o SortableJS estiver carregado
function initKanban() {
    if (typeof Sortable === 'undefined') {
        console.error('SortableJS não foi carregado. Tentando novamente em 500ms...');
        setTimeout(initKanban, 500);
        return;
    }
    
    
    // Forçar layout horizontal com JavaScript adicional
    const kanbanBoard = document.querySelector('.kanban-board');
    const kanbanContainer = document.querySelector('.kanban-container');
    const kanbanColumns = document.querySelectorAll('.kanban-column');
    
    // Aplicar estilos inline para garantir layout horizontal
    if (kanbanBoard) {
        kanbanBoard.style.cssText = `
            display: block !important;
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
        `;
    }
    
    if (kanbanContainer) {
        kanbanContainer.style.cssText = `
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            gap: 20px !important;
            width: max-content !important;
            min-width: 100% !important;
        `;
    }
    
    kanbanColumns.forEach((column, index) => {
        column.style.cssText = `
            flex: 0 0 300px !important;
            min-width: 300px !important;
            max-width: 300px !important;
            display: flex !important;
            flex-direction: column !important;
        `;
        
        const card = column.querySelector('.card');
        if (card) {
            card.style.cssText = `
                height: auto !important;
                min-height: 500px !important;
                display: flex !important;
                flex-direction: column !important;
            `;
        }
    });
    
    // Verificar se SortableJS foi carregado
    if (typeof Sortable === 'undefined') {
        console.error('SortableJS não foi carregado!');
        return;
    }
    
    const columns = document.querySelectorAll('.kanban-cards');
    
    
    columns.forEach((column, index) => {
        new Sortable(column, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            filter: '.btn-success, .btn-primary, .btn-info, .dropdown, .dropdown-menu, .dropdown-item, .dropdown-toggle, .empty-column-message',
            preventOnFilter: false,
            onStart: function(evt) {
                // Fechar todos os dropdowns abertos
                const dropdowns = document.querySelectorAll('.dropdown-menu.show');
                dropdowns.forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            },
            onEnd: function(evt) {
                const leadId = evt.item.dataset.id;
                const newStatus = evt.to.dataset.status;
                const newPosition = evt.newIndex;
                const oldStatus = evt.from.dataset.status;
                
                // Atualizar mensagens imediatamente
                setTimeout(() => updateEmptyColumnClasses(), 100);
                
                moveCard(leadId, newStatus, newPosition);
            }
        });
    });
}

// Função para mover card
function moveCard(leadId, newStatus, newPosition) {
    
    fetch('/dashboard/kanban/move', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            lead_id: leadId,
            new_status: newStatus,
            new_position: newPosition
        })
    })
    .then(response => {
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Atualizar o data-status do card
            const card = document.querySelector(`[data-id="${leadId}"]`);
            if (card) {
                card.dataset.status = newStatus;
            }
            
            // Controlar classes de colunas vazias
            updateEmptyColumnClasses();
            
            // Mostrar notificação de sucesso
            if (typeof toastr !== 'undefined') {
                toastr.success(data.message);
            } else {
                console.log('Sucesso:', data.message);
            }
        } else {
            console.error('Erro no servidor:', data.message);
            if (typeof toastr !== 'undefined') {
                toastr.error(data.message || 'Erro ao mover lead');
            }
            // NÃO recarregar página - melhor UX
            console.log('Erro na movimentação - card permanece na posição atual');
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('Erro ao mover lead - verifique sua conexão');
        }
        // NÃO recarregar página - melhor UX
        console.log('Erro de conexão - card permanece na posição atual');
    });
}

// Função para abrir modal de edição (duplo clique e menu de contexto)
function openEditModal(leadId) {
    
    // Abrir o modal
    const modal = new bootstrap.Modal(document.getElementById('leadModal'));
    modal.show();
    
    // Resetar conteúdo do modal
    document.getElementById('leadModalContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-3 text-muted">Carregando dados do lead...</p>
        </div>
    `;
    document.getElementById('leadModalFooter').classList.add('d-none');
    
    // Carregar dados do lead via AJAX
    fetch(`/dashboard/inscricoes/${leadId}/edit`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro ao carregar dados do lead');
        }
        return response.text();
    })
    .then(html => {
        document.getElementById('leadModalContent').innerHTML = html;
        document.getElementById('leadModalFooter').classList.remove('d-none');
        
        // Armazenar ID do lead no modal
        document.getElementById('leadModal').setAttribute('data-lead-id', leadId);
    })
    .catch(error => {
        console.error('Erro ao carregar dados do lead:', error);
        document.getElementById('leadModalContent').innerHTML = `
            <div class="text-center py-5">
                <div class="text-danger">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h5>Erro ao carregar dados</h5>
                    <p>Não foi possível carregar os dados do lead. Tente novamente.</p>
                    <button class="btn btn-primary" onclick="openEditModal(${leadId})">
                        <i class="fas fa-redo me-2"></i>Tentar Novamente
                    </button>
                </div>
            </div>
        `;
    });
}

// Função para salvar alterações do modal de edição
function saveLeadChanges() {
    const modal = document.getElementById('leadModal');
    const leadId = modal.getAttribute('data-lead-id');
    const form = modal.querySelector('form');
    
    if (!form) {
        console.error('Formulário não encontrado no modal');
        return;
    }
    
    // Mostrar loading no botão
    const saveButton = modal.querySelector('.btn-primary');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
    saveButton.disabled = true;
    
    // Preparar dados do formulário
    const formData = new FormData(form);
    formData.append('_method', 'PUT'); // Simular método PUT
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    fetch(`/dashboard/inscricoes/${leadId}`, {
        method: 'POST', // Usar POST com _method=PUT
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Erro ao salvar alterações');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Fechar modal com sucesso (sem reload)
            closeModalWithSuccess('leadModal', data.message || 'Lead atualizado com sucesso!');
            
            // Atualizar o card na view se necessário
            const card = document.querySelector(`.kanban-card[data-id="${leadId}"]`);
            if (card) {
                // Atualizar informações básicas do card
                const nome = form.querySelector('[name="nome"]').value;
                const email = form.querySelector('[name="email"]').value;
                const telefone = form.querySelector('[name="telefone"]').value;
                const etiqueta = form.querySelector('[name="etiqueta"]').value;
                
                // Atualizar textos do card
                const cardTitle = card.querySelector('.card-title');
                if (cardTitle) cardTitle.textContent = nome;
                
                // Atualizar email e telefone usando textContent
                const smallElements = card.querySelectorAll('small');
                smallElements.forEach(element => {
                    if (element.textContent.includes('@')) {
                        element.textContent = email;
                    } else if (element.textContent.includes('+') || element.textContent.match(/\d{8,}/)) {
                        element.textContent = telefone;
                    }
                });
                
                // Se o status mudou, mover o card para a coluna correta
                if (card.dataset.status !== etiqueta) {
                    const novaColuna = document.querySelector(`.kanban-cards[data-status="${etiqueta}"]`);
                    if (novaColuna) {
                        novaColuna.appendChild(card);
                        card.dataset.status = etiqueta;
                        updateColumnCounts();
                        updateEmptyColumnClasses();
                    }
                }
            }
        } else {
            throw new Error(data.message || 'Erro ao salvar alterações');
        }
    })
    .catch(error => {
        console.error('Erro ao salvar:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('Erro ao salvar: ' + error.message);
        } else {
            alert('Erro ao salvar: ' + error.message);
        }
    })
    .finally(() => {
        // Restaurar botão
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
    });
}

// Função para alterar status
function changeStatus(leadId) {
    
    // Armazenar ID do lead no modal
    document.getElementById('statusModal').setAttribute('data-lead-id', leadId);
    
    // Limpar formulário
    document.getElementById('statusForm').reset();
    
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

// Função para salvar mudança de status
function saveStatusChange() {
    const modal = document.getElementById('statusModal');
    const leadId = modal.getAttribute('data-lead-id');
    const formData = new FormData(document.getElementById('statusForm'));
    
    const status = formData.get('status');
    const observacao = formData.get('observacao');
    
    if (!status) {
        alert('Por favor, selecione um status.');
        return;
    }
    
    // Mostrar loading
    const saveButton = modal.querySelector('.btn-primary');
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Alterando...';
    saveButton.disabled = true;
    
    fetch(`/dashboard/inscricoes/${leadId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            status: status,
            observacao: observacao
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal com sucesso (sem reload)
            closeModalWithSuccess('statusModal', data.message || 'Status alterado com sucesso!');
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error(data.message || 'Erro ao alterar status');
            } else {
                alert(data.message || 'Erro ao alterar status');
            }
        }
    })
    .catch(error => {
        console.error('Erro ao alterar status:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('Erro ao alterar status');
        } else {
            alert('Erro ao alterar status');
        }
    })
    .finally(() => {
        // Restaurar botão
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
    });
}

// INICIALIZAR DROPDOWNS CORRETAMENTE
setTimeout(() => {
        
    // Verificar se Bootstrap está disponível
    if (typeof bootstrap === 'undefined') {
        console.error('❌ Bootstrap não encontrado!');
        return;
    }
    
    // Inicializar todos os dropdowns manualmente
    const dropdownButtons = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    
    
    dropdownButtons.forEach((button, index) => {
        try {
            // Garantir que o botão tem um ID único
            if (!button.id) {
                button.id = `dropdown-btn-${index}`;
            }
            
            // Inicializar dropdown
            const dropdown = new bootstrap.Dropdown(button, {
                autoClose: true,
                boundary: 'viewport'
            });
            
            // Adicionar eventos para debug
            button.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            // Prevenir problemas com cards
            const card = button.closest('.kanban-card');
            if (card) {
                card.addEventListener('click', function(e) {
                    if (e.target.closest('.dropdown')) {
                        e.stopPropagation();
                    }
                });
            }
            
        } catch (error) {
            console.error(`❌ Erro ao inicializar dropdown ${index}:`, error);
        }
    });
    
    // CONFIGURAR SCROLLBAR
    const scrollContainers = document.querySelectorAll('.scroll-container');
    scrollContainers.forEach(container => {
        container.style.overflowY = 'scroll';
        container.style.overflowX = 'hidden';
        container.style.height = 'calc(100vh - 200px)';
        container.style.maxHeight = 'calc(100vh - 200px)';
        container.style.minHeight = '400px';
    });
    
    // INICIALIZAR DRAG HORIZONTAL DO KANBAN
    initKanbanHorizontalDrag();
    
    // FUNÇÃO DE DEBUG PARA DROPDOWNS
    window.debugDropdowns = function() {
        console.log('🔍 DIAGNOSTICO DE DROPDOWNS:');
        const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        console.log(`📊 Total de dropdowns: ${dropdowns.length}`);
        
        dropdowns.forEach((btn, i) => {
            const instance = bootstrap.Dropdown.getInstance(btn);
            console.log(`Dropdown ${i + 1}:`, {
                button: btn.id || `sem-id-${i}`,
                hasInstance: !!instance,
                isVisible: btn.getAttribute('aria-expanded') === 'true'
            });
        });
        
        const openMenus = document.querySelectorAll('.dropdown-menu.show');
        console.log(`📋 Menus abertos: ${openMenus.length}`);
    };
    
    // Adicionar função global para teste manual
    window.testDropdown = function(index = 0) {
        const dropdowns = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        if (dropdowns[index]) {
            console.log('🧪 Testando dropdown:', index);
            dropdowns[index].click();
        }
    };
    
}, 500);

// Fechar dropdowns quando iniciar drag
document.addEventListener('dragstart', function(e) {
    const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
    openDropdowns.forEach(dropdown => {
        dropdown.classList.remove('show');
    });
});

// SISTEMA DE FILTROS DO KANBAN (seguindo padrão da página de inscrições)
function toggleFilters() {
    const filtersCard = document.getElementById('filtersCard');
    filtersCard.classList.toggle('d-none');
    
    // Animar entrada se estiver sendo mostrado
    if (!filtersCard.classList.contains('d-none')) {
        filtersCard.style.opacity = '0';
        filtersCard.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            filtersCard.style.transition = 'all 0.3s ease';
            filtersCard.style.opacity = '1';
            filtersCard.style.transform = 'translateY(0)';
        }, 10);
    }
}

function applyFilters() {
    const search = document.getElementById('filterSearch').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const prioridade = document.getElementById('filterPrioridade').value;
    const dataInicio = document.getElementById('filterDataInicio').value;
    const dataFim = document.getElementById('filterDataFim').value;
    
    const cards = document.querySelectorAll('.kanban-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        let shouldShow = true;
        
        // Filtro por busca de texto
        if (search) {
            const cardText = card.textContent.toLowerCase();
            if (!cardText.includes(search)) {
                shouldShow = false;
            }
        }
        
        // Filtro por status
        if (status && card.dataset.status !== status) {
            shouldShow = false;
        }
        
        // Filtro por prioridade
        if (prioridade) {
            const prioridadeBadge = card.querySelector('.badge');
            if (prioridadeBadge) {
                const cardPrioridade = prioridadeBadge.textContent.toLowerCase();
                if (!cardPrioridade.includes(prioridade.toLowerCase())) {
                    shouldShow = false;
                }
            }
        }
        
        // Filtro por período
        if (dataInicio || dataFim) {
            const dateElement = card.querySelector('small:last-child');
            if (dateElement) {
                const cardDateText = dateElement.textContent;
                const match = cardDateText.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                if (match) {
                    const cardDate = new Date(match[3], match[2] - 1, match[1]);
                    
                    if (dataInicio) {
                        const startDate = new Date(dataInicio);
                        if (cardDate < startDate) {
                            shouldShow = false;
                        }
                    }
                    
                    if (dataFim) {
                        const endDate = new Date(dataFim);
                        endDate.setHours(23, 59, 59, 999); // Incluir o dia todo
                        if (cardDate > endDate) {
                            shouldShow = false;
                        }
                    }
                }
            }
        }
        
        // Aplicar visibilidade com animação
        if (shouldShow) {
            card.style.display = 'block';
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
            visibleCount++;
        } else {
            card.style.transition = 'all 0.2s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            setTimeout(() => {
                if (card.style.opacity === '0') {
                    card.style.display = 'none';
                }
            }, 200);
        }
    });
    
    // Atualizar contadores das colunas
    updateColumnCounts();
    
    // Atualizar classes de colunas vazias
    setTimeout(() => updateEmptyColumnClasses(), 300);
    
    // Mostrar resultado
    if (typeof toastr !== 'undefined') {
        toastr.info(`Filtros aplicados. ${visibleCount} leads encontrados.`);
    }
    
    console.log(`✅ Filtros aplicados - ${visibleCount} leads visíveis`);
}

function clearFilters() {
    // Limpar campos
    document.getElementById('filterSearch').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterPrioridade').value = '';
    document.getElementById('filterDataInicio').value = '';
    document.getElementById('filterDataFim').value = '';
    
    // Mostrar todos os cards
    const cards = document.querySelectorAll('.kanban-card');
    cards.forEach(card => {
        card.style.display = 'block';
        card.style.opacity = '1';
        card.style.transform = 'scale(1)';
        card.style.transition = 'all 0.2s ease';
    });
    
    // Atualizar contadores
    updateColumnCounts();
    
    // Atualizar classes de colunas vazias
    setTimeout(() => updateEmptyColumnClasses(), 100);
    
    if (typeof toastr !== 'undefined') {
        toastr.success('Filtros limpos. Todos os leads estão visíveis.');
    }
}

function updateColumnCounts() {
    const columns = document.querySelectorAll('.kanban-column');
    
    columns.forEach(column => {
        const visibleCards = column.querySelectorAll('.kanban-card[style*="display: block"], .kanban-card:not([style*="display: none"])');
        const badge = column.querySelector('.badge');
        if (badge) {
            badge.textContent = visibleCards.length;
        }
    });
}

function showFollowUps() {
    const modal = new bootstrap.Modal(document.getElementById('followUpsModal'));
    modal.show();
    
    // Simular conteúdo de follow-ups por enquanto
    document.getElementById('followUpsContent').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
            <h5>Follow-ups Programados</h5>
            <p class="text-muted">Esta funcionalidade será implementada em breve.</p>
            <p class="text-muted">Aqui você poderá visualizar e gerenciar todos os follow-ups agendados para seus leads.</p>
        </div>
    `;
}

function addQuickNote(leadId) {
    // Armazenar o ID do lead no modal
    document.getElementById('quickNoteModal').setAttribute('data-lead-id', leadId);
    
    // Limpar o textarea
    document.querySelector('#quickNoteForm textarea[name="nota"]').value = '';
    
    // Abrir o modal
    const modal = new bootstrap.Modal(document.getElementById('quickNoteModal'));
    modal.show();
}

function saveQuickNote() {
    const modal = document.getElementById('quickNoteModal');
    const leadId = modal.getAttribute('data-lead-id');
    const nota = document.querySelector('#quickNoteForm textarea[name="nota"]').value;
    
    if (!nota.trim()) {
        alert('Por favor, digite uma nota.');
        return;
    }
    
    // Enviar nota via AJAX
    fetch(`/dashboard/inscricoes/${leadId}/nota`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            nota: nota
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            const modalInstance = bootstrap.Modal.getInstance(modal);
            modalInstance.hide();
            
            // Mostrar sucesso
            if (typeof toastr !== 'undefined') {
                toastr.success(data.message || 'Nota adicionada com sucesso!');
            }
            
            // Atualizar sem reload da página
            if (typeof toastr === 'undefined') {
                console.log('Nota salva com sucesso!');
            }
        } else {
            alert(data.message || 'Erro ao salvar nota');
        }
    })
    .catch(error => {
        console.error('Erro ao salvar nota:', error);
        alert('Erro ao salvar nota');
    });
}

function markContact(leadId) {
    if (confirm('Marcar este lead como contatado?')) {
        fetch(`/dashboard/inscricoes/${leadId}/marcar-contato`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
                if (typeof toastr !== 'undefined') {
                    toastr.success(data.message || 'Lead marcado como contatado!');
                }
                location.reload();
            } else {
                alert(data.message || 'Erro ao marcar contato');
            }
        })
        .catch(error => {
            console.error('Erro ao marcar contato:', error);
            alert('Erro ao marcar contato');
        });
    }
}

function saveNotes(leadId) {
    const notes = document.querySelector('textarea').value;
    // Implementar salvamento de anotações
}

// ANIMAÇÕES E EFEITOS VISUAIS
document.addEventListener('DOMContentLoaded', function() {
    // Feedback visual nos botões de ação rápida
    document.querySelectorAll('.kanban-card .btn-sm[href]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.add('loading');
            setTimeout(() => this.classList.remove('loading'), 1500);
        });
    });
    
    // Animações nos cards
    document.querySelectorAll('.kanban-card .card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});

// FUNÇÃO PARA DRAG HORIZONTAL DO KANBAN
function initKanbanHorizontalDrag() {
    const kanbanBoard = document.querySelector('.kanban-board');
    if (!kanbanBoard) return;
    
    let isDown = false;
    let startX;
    let scrollLeft;
    let isDragging = false;
    let animationFrame;
    let momentum = 0;
    let lastMoveTime = 0;
    let lastMoveX = 0;
    
    // Função para detectar se pode fazer drag
    function canDrag(target) {
        return !target.closest('.kanban-card') && 
               !target.closest('.btn') && 
               !target.closest('.dropdown') &&
               !target.closest('input') &&
               !target.closest('textarea');
    }
    
    // Atualizar cursor baseado na posição do mouse
    kanbanBoard.addEventListener('mousemove', (e) => {
        if (!isDown) {
            if (canDrag(e.target)) {
                kanbanBoard.classList.add('can-drag');
            } else {
                kanbanBoard.classList.remove('can-drag');
            }
        }
    });
    
    kanbanBoard.addEventListener('mouseleave', () => {
        kanbanBoard.classList.remove('can-drag');
    });
    
    // Eventos de mouse
    kanbanBoard.addEventListener('mousedown', (e) => {
        if (!canDrag(e.target)) return;
        
        isDown = true;
        isDragging = false;
        kanbanBoard.classList.add('is-dragging');
        kanbanBoard.classList.remove('can-drag');
        
        startX = e.pageX - kanbanBoard.offsetLeft;
        scrollLeft = kanbanBoard.scrollLeft;
        lastMoveTime = Date.now();
        lastMoveX = e.pageX;
        momentum = 0;
        
        // Desativar smooth scroll durante o drag
        kanbanBoard.style.scrollBehavior = 'auto';
        
        e.preventDefault();
        e.stopPropagation();
    });
    
    kanbanBoard.addEventListener('mouseleave', () => {
        if (isDown) {
            isDown = false;
            isDragging = false;
            kanbanBoard.classList.remove('is-dragging');
            kanbanBoard.style.scrollBehavior = 'smooth';
        }
    });
    
    kanbanBoard.addEventListener('mouseup', (e) => {
        if (isDown) {
            isDown = false;
            kanbanBoard.classList.remove('is-dragging');
            
            // Aplicar momentum se houve movimento significativo
            if (isDragging && Math.abs(momentum) > 0.5) {
                applyMomentum();
            }
            
            // Reativar smooth scroll
            setTimeout(() => {
                kanbanBoard.style.scrollBehavior = 'smooth';
            }, 100);
        }
    });
    
    kanbanBoard.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const x = e.pageX - kanbanBoard.offsetLeft;
        const walk = (x - startX) * 1.5; // Multiplicador suavizado
        const newScrollLeft = scrollLeft - walk;
        
        // Calcular momentum para inércia
        const currentTime = Date.now();
        const timeDiff = currentTime - lastMoveTime;
        if (timeDiff > 0) {
            momentum = (e.pageX - lastMoveX) / timeDiff * -1.5;
        }
        
        lastMoveTime = currentTime;
        lastMoveX = e.pageX;
        
        // Aplicar scroll suavizado
        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
        }
        
        animationFrame = requestAnimationFrame(() => {
            kanbanBoard.scrollLeft = newScrollLeft;
        });
        
        isDragging = true;
    });
    
    // Função para aplicar momentum (inércia)
    function applyMomentum() {
        if (Math.abs(momentum) < 0.1) return;
        
        const currentScroll = kanbanBoard.scrollLeft;
        const targetScroll = currentScroll + (momentum * 50);
        
        // Limitar dentro dos bounds
        const maxScroll = kanbanBoard.scrollWidth - kanbanBoard.clientWidth;
        const finalScroll = Math.max(0, Math.min(maxScroll, targetScroll));
        
        // Animar para a posição final
        kanbanBoard.style.scrollBehavior = 'smooth';
        kanbanBoard.scrollLeft = finalScroll;
        
        // Reduzir momentum
        momentum *= 0.8;
        
        if (Math.abs(momentum) > 0.1) {
            setTimeout(applyMomentum, 16);
        }
    }
    
    // Eventos de toque (mobile)
    let touchStartX = 0;
    let touchScrollLeft = 0;
    let touchMomentum = 0;
    let lastTouchTime = 0;
    let lastTouchX = 0;
    
    kanbanBoard.addEventListener('touchstart', (e) => {
        if (!canDrag(e.target)) return;
        
        touchStartX = e.touches[0].clientX;
        touchScrollLeft = kanbanBoard.scrollLeft;
        lastTouchTime = Date.now();
        lastTouchX = e.touches[0].clientX;
        touchMomentum = 0;
        
        kanbanBoard.classList.add('is-dragging');
        kanbanBoard.style.scrollBehavior = 'auto';
        
        e.preventDefault();
    });
    
    kanbanBoard.addEventListener('touchmove', (e) => {
        if (!touchStartX) return;
        
        e.preventDefault();
        
        const touchX = e.touches[0].clientX;
        const walk = (touchStartX - touchX) * 1.2;
        
        // Calcular momentum
        const currentTime = Date.now();
        const timeDiff = currentTime - lastTouchTime;
        if (timeDiff > 0) {
            touchMomentum = (lastTouchX - touchX) / timeDiff * 1.2;
        }
        
        lastTouchTime = currentTime;
        lastTouchX = touchX;
        
        kanbanBoard.scrollLeft = touchScrollLeft + walk;
    });
    
    kanbanBoard.addEventListener('touchend', () => {
        kanbanBoard.classList.remove('is-dragging');
        
        // Aplicar momentum no touch
        if (Math.abs(touchMomentum) > 0.5) {
            setTimeout(() => {
                kanbanBoard.style.scrollBehavior = 'smooth';
                const currentScroll = kanbanBoard.scrollLeft;
                const targetScroll = currentScroll + (touchMomentum * 100);
                const maxScroll = kanbanBoard.scrollWidth - kanbanBoard.clientWidth;
                const finalScroll = Math.max(0, Math.min(maxScroll, targetScroll));
                kanbanBoard.scrollLeft = finalScroll;
            }, 50);
        } else {
            kanbanBoard.style.scrollBehavior = 'smooth';
        }
        
        touchStartX = 0;
        touchMomentum = 0;
    });
}

// FUNÇÃO PARA CONTROLAR BACKGROUND DE COLUNA VAZIA
function updateEmptyColumnClasses() {
    const columns = document.querySelectorAll('.kanban-cards');
    
    columns.forEach(column => {
        const status = column.dataset.status;
        // Contar apenas cards reais (não fantasmas do sortable)
        const cards = column.querySelectorAll('.kanban-card:not(.sortable-ghost):not(.sortable-chosen)');
        
        if (cards.length === 0) {
            column.classList.add('empty-column');
        } else {
            column.classList.remove('empty-column');
        }
    });
}

// FUNÇÃO PARA FECHAR MODAL E MOSTRAR SUCESSO SEM RELOAD
function closeModalWithSuccess(modalId, message) {
    const modal = document.getElementById(modalId);
    const modalInstance = bootstrap.Modal.getInstance(modal);
    modalInstance.hide();
    
    if (typeof toastr !== 'undefined') {
        toastr.success(message || 'Operação realizada com sucesso!');
    }
    
    // Atualizar classes de colunas vazias
    setTimeout(() => updateEmptyColumnClasses(), 100);
}

// INICIALIZAR CONTROLE DE COLUNAS VAZIAS AO CARREGAR
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Kanban
    initKanban();
    
    // Aguardar um pouco para garantir que o DOM está completamente carregado
    setTimeout(() => {
        updateEmptyColumnClasses();
        
        // Configurar busca em tempo real
        const searchInput = document.getElementById('filterSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                // Debounce para evitar muitas chamadas
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    if (this.value.length >= 2 || this.value.length === 0) {
                        applyFilters();
                    }
                }, 300);
            });
            
            // Aplicar filtros ao pressionar Enter
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyFilters();
                }
            });
        }
        
        // Configurar outros filtros para aplicação automática
        ['filterStatus', 'filterPrioridade', 'filterDataInicio', 'filterDataFim'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', function() {
                    applyFilters();
                });
            }
        });
    }, 500);
});

// SISTEMA DE TAREFAS (TODO LIST) E HISTÓRICO
// Armazenar tarefas do lead atual
let currentLeadTasks = [];

// Função para carregar tarefas quando a aba for aberta
document.addEventListener('shown.bs.tab', function (event) {
    if (event.target.id === 'tasks-tab') {
        loadLeadTasks();
    } else if (event.target.id === 'history-tab') {
        loadLeadHistory();
    }
});

// Função para carregar tarefas do lead atual
function loadLeadTasks() {
    const modal = document.getElementById('leadModal');
    const leadId = modal.getAttribute('data-lead-id');
    
    if (!leadId) {
        console.error('ID do lead não encontrado');
        return;
    }
    
    // Mostrar loading
    document.getElementById('tasksLoading').classList.remove('d-none');
    document.getElementById('tasksContainer').classList.add('d-none');
    
    // Carregar tarefas via AJAX
    fetch(`/dashboard/inscricoes/${leadId}/tasks`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao carregar tarefas');
            }
            return response.json();
        })
        .then(data => {
            // Armazenar tarefas
            currentLeadTasks = data.tasks || [];
            
            // Renderizar tarefas
            renderTasks();
            
            // Esconder loading
            document.getElementById('tasksLoading').classList.add('d-none');
            document.getElementById('tasksContainer').classList.remove('d-none');
        })
        .catch(error => {
            console.error('Erro ao carregar tarefas:', error);
            
            // Mostrar mensagem de erro
            document.getElementById('tasksContainer').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Erro ao carregar tarefas. Tente novamente.
                </div>
            `;
            
            // Esconder loading
            document.getElementById('tasksLoading').classList.add('d-none');
            document.getElementById('tasksContainer').classList.remove('d-none');
        });
}

// Função para renderizar tarefas
function renderTasks() {
    const container = document.getElementById('tasksContainer');
    
    // Verificar se há tarefas
    if (!currentLeadTasks || currentLeadTasks.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Nenhuma tarefa cadastrada para este lead.
            </div>
        `;
        return;
    }
    
    // Renderizar tarefas
    let html = '<div class="list-group">';
    
    currentLeadTasks.forEach((task, index) => {
        const priorityClass = {
            'baixa': 'text-success',
            'media': 'text-warning',
            'alta': 'text-danger',
            'urgente': 'text-danger fw-bold'
        }[task.priority] || 'text-secondary';
        
        const isCompleted = task.status === 'completed';
        const titleClass = isCompleted ? 'text-decoration-line-through text-muted' : '';
        const taskIdString = String(task.id); // Converter para string
        
        html += `
            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <div class="form-check flex-grow-1">
                    <input class="form-check-input" type="checkbox" id="task-${taskIdString}" 
                           ${isCompleted ? 'checked' : ''} 
                           onchange="toggleTaskStatus('${taskIdString}', this.checked)">
                    <label class="form-check-label ${titleClass}" for="task-${taskIdString}">
                        ${task.title}
                    </label>
                    <div class="small text-muted mt-1">
                        ${task.description ? `<p class="mb-1">${task.description}</p>` : ''}
                        <div class="d-flex align-items-center gap-3">
                            <span class="${priorityClass}">
                                <i class="fas fa-flag me-1"></i>
                                ${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}
                            </span>
                            ${task.due_date ? `
                                <span>
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    ${formatDate(task.due_date)}
                                </span>
                            ` : ''}
                        </div>
                    </div>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTask('${taskIdString}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Função para formatar data
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

// Função para alternar status da tarefa
function toggleTaskStatus(taskId, isCompleted) {
    const modal = document.getElementById('leadModal');
    const leadId = modal.getAttribute('data-lead-id');
    
    if (!leadId || !taskId) {
        console.error('ID do lead ou da tarefa não encontrado');
        return;
    }
    
    // Converter taskId para string para garantir consistência
    taskId = String(taskId);
    
    // Atualizar status da tarefa via AJAX
    fetch(`/dashboard/inscricoes/${leadId}/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: isCompleted ? 'completed' : 'pending'
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Erro ao atualizar tarefa');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Atualizar tarefa na lista
            const taskIndex = currentLeadTasks.findIndex(task => String(task.id) === taskId);
            if (taskIndex !== -1) {
                currentLeadTasks[taskIndex].status = isCompleted ? 'completed' : 'pending';
                
                // Atualizar visual
                const label = document.querySelector(`label[for="task-${taskId}"]`);
                if (label) {
                    if (isCompleted) {
                        label.classList.add('text-decoration-line-through', 'text-muted');
                    } else {
                        label.classList.remove('text-decoration-line-through', 'text-muted');
                    }
                }
            }
            
            // Mostrar notificação
            if (typeof toastr !== 'undefined') {
                toastr.success('Tarefa atualizada com sucesso');
            }
        } else {
            throw new Error(data.message || 'Erro ao atualizar tarefa');
        }
    })
    .catch(error => {
        console.error('Erro ao atualizar tarefa:', error);
        
        // Reverter checkbox
        const checkbox = document.getElementById(`task-${taskId}`);
        if (checkbox) {
            checkbox.checked = !isCompleted;
        }
        
        // Mostrar notificação
        if (typeof toastr !== 'undefined') {
            toastr.error(error.message || 'Erro ao atualizar tarefa');
        } else {
            alert(error.message || 'Erro ao atualizar tarefa');
        }
    });
}

// Função para excluir tarefa
function deleteTask(taskId) {
    if (!confirm('Tem certeza que deseja excluir esta tarefa?')) {
        return;
    }
    
    const modal = document.getElementById('leadModal');
    const leadId = modal.getAttribute('data-lead-id');
    
    if (!leadId || !taskId) {
        console.error('ID do lead ou da tarefa não encontrado');
        return;
    }
    
    // Converter taskId para string para garantir consistência
    taskId = String(taskId);
    
    // Excluir tarefa via AJAX
    fetch(`/dashboard/inscricoes/${leadId}/tasks/${taskId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }

    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Erro ao excluir tarefa');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Remover tarefa da lista
            currentLeadTasks = currentLeadTasks.filter(task => String(task.id) !== taskId);
            
            // Renderizar tarefas
            renderTasks();
            
            // Mostrar notificação
            if (typeof toastr !== 'undefined') {
                toastr.success('Tarefa excluída com sucesso');
            }
        } else {
            throw new Error(data.message || 'Erro ao excluir tarefa');
        }
    })
    .catch(error => {
        console.error('Erro ao excluir tarefa:', error);
        
        // Mostrar notificação
        if (typeof toastr !== 'undefined') {
            toastr.error(error.message || 'Erro ao excluir tarefa');
        } else {
            alert(error.message || 'Erro ao excluir tarefa');
        }
    });
}

// Função para carregar histórico do lead
function loadLeadHistory() {
    const modal = document.getElementById('leadModal');
    const leadId = modal.getAttribute('data-lead-id');
    
    if (!leadId) {
        console.error('ID do lead não encontrado');
        return;
    }
    
    // Mostrar loading
    document.getElementById('historyLoading').classList.remove('d-none');
    document.getElementById('historyContainer').classList.add('d-none');
    
    // Carregar histórico via AJAX
    fetch(`/dashboard/inscricoes/${leadId}/history`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao carregar histórico');
            }
            return response.json();
        })
        .then(data => {
            // Renderizar histórico
            renderHistory(data.history || []);
            
            // Esconder loading
            document.getElementById('historyLoading').classList.add('d-none');
            document.getElementById('historyContainer').classList.remove('d-none');
        })
        .catch(error => {
            console.error('Erro ao carregar histórico:', error);
            
            // Mostrar mensagem de erro
            document.getElementById('historyContainer').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Erro ao carregar histórico. Tente novamente.
                </div>
            `;
            
            // Esconder loading
            document.getElementById('historyLoading').classList.add('d-none');
            document.getElementById('historyContainer').classList.remove('d-none');
        });
}

// Função para renderizar histórico
function renderHistory(history) {
    const container = document.getElementById('historyContainer');
    
    // Verificar se há histórico
    if (!history || history.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Nenhum registro de histórico encontrado.
            </div>
        `;
        return;
    }
    
    // Renderizar histórico
    let html = '<div class="timeline">';
    
    history.forEach((item, index) => {
        const date = new Date(item.created_at);
        
        html += `
            <div class="timeline-item mb-4">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="timeline-badge bg-primary">
                            ${date.toLocaleDateString('pt-BR', {day: '2-digit', month: '2-digit'})}
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">${item.action}</h6>
                            <small class="text-muted">${date.toLocaleTimeString('pt-BR')}</small>
                        </div>
                        <p class="mb-1">
                            ${item.description}
                        </p>
                        <small class="text-muted">
                            Por: ${item.user_name}
                        </small>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Configurar eventos para o formulário de nova tarefa
document.addEventListener('DOMContentLoaded', function() {
    // Botão para adicionar nova tarefa
    const addTaskBtn = document.getElementById('addTaskBtn');
    if (addTaskBtn) {
        addTaskBtn.addEventListener('click', function() {
            document.getElementById('newTaskForm').classList.remove('d-none');
            document.getElementById('taskTitle').focus();
        });
    }
    
    // Botão para cancelar nova tarefa
    const cancelTaskBtn = document.getElementById('cancelTaskBtn');
    if (cancelTaskBtn) {
        cancelTaskBtn.addEventListener('click', function() {
            document.getElementById('newTaskForm').classList.add('d-none');
            document.getElementById('taskTitle').value = '';
            document.getElementById('taskDescription').value = '';
            document.getElementById('taskDueDate').value = '';
            document.getElementById('taskPriority').value = 'media';
        });
    }
    
    // Botão para salvar nova tarefa
    const saveTaskBtn = document.getElementById('saveTaskBtn');
    if (saveTaskBtn) {
        saveTaskBtn.addEventListener('click', function() {
            saveNewTask();
        });
    }
});

// Função para salvar nova tarefa
function saveNewTask() {
    const modal = document.getElementById('leadModal');
    const leadId = modal.getAttribute('data-lead-id');
    
    if (!leadId) {
        console.error('ID do lead não encontrado');
        return;
    }
    
    const title = document.getElementById('taskTitle').value.trim();
    const description = document.getElementById('taskDescription').value.trim();
    const dueDate = document.getElementById('taskDueDate').value;
    const priority = document.getElementById('taskPriority').value;
    
    // Validar campos
    if (!title) {
        alert('Por favor, informe o título da tarefa');
        document.getElementById('taskTitle').focus();
        return;
    }
    
    // Mostrar loading
    const saveBtn = document.getElementById('saveTaskBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
    saveBtn.disabled = true;
    
    // Salvar tarefa via AJAX
    fetch(`/dashboard/inscricoes/${leadId}/tasks`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            title,
            description,
            due_date: dueDate,
            priority
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro ao salvar tarefa');
        }
        return response.json();
    })
    .then(data => {
        // Adicionar tarefa à lista
        currentLeadTasks.push(data.task);
        
        // Renderizar tarefas
        renderTasks();
        
        // Limpar formulário
        document.getElementById('taskTitle').value = '';
        document.getElementById('taskDescription').value = '';
        document.getElementById('taskDueDate').value = '';
        document.getElementById('taskPriority').value = 'media';
        
        // Esconder formulário
        document.getElementById('newTaskForm').classList.add('d-none');
        
        // Mostrar notificação
        if (typeof toastr !== 'undefined') {
            toastr.success('Tarefa adicionada com sucesso');
        }
    })
    .catch(error => {
        console.error('Erro ao salvar tarefa:', error);
        
        // Mostrar notificação
        if (typeof toastr !== 'undefined') {
            toastr.error('Erro ao salvar tarefa');
        } else {
            alert('Erro ao salvar tarefa');
        }
    })
    .finally(() => {
        // Restaurar botão
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Adicionar estilos para o timeline
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .timeline {
            position: relative;
            padding-left: 1.5rem;
        }
        
        .timeline:before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .timeline-badge {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            text-align: center;
            line-height: 2.5rem;
            font-size: 0.75rem;
            color: white;
            font-weight: bold;
            z-index: 1;
        }
    `;
    document.head.appendChild(style);
});

// Funções do Modal de Colunas
function showColumnModal() {
    const modal = new bootstrap.Modal(document.getElementById('columnModal'));
    modal.show();
    loadColumns();
}

function showNewColumnForm() {
    const form = document.getElementById('columnForm');
    form.style.display = 'block';
    form.querySelector('form').reset();
    form.querySelector('[name="id"]').value = '';
}

function hideColumnForm() {
    document.getElementById('columnForm').style.display = 'none';
    document.querySelector('#columnForm form').reset();
}

function editColumn(column) {
    const form = document.getElementById('columnForm');
    form.style.display = 'block';
    form.querySelector('[name="id"]').value = column.id;
    form.querySelector('[name="name"]').value = column.name;
    form.querySelector('[name="color"]').value = column.color;
    form.querySelector('[name="icon"]').value = column.icon || '';
}

function loadColumns() {
    fetch('/dashboard/kanban/columns')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('columnsTable');
            tbody.innerHTML = data.map(column => `
                <tr>
                    <td>${column.name}</td>
                    <td><span class="badge bg-${column.color}">${column.color}</span></td>
                    <td>${column.icon || '-'}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary" onclick='editColumn(${JSON.stringify(column)})'>
                                <i class="fas fa-edit"></i>
                            </button>
                            ${!column.is_system ? `
                                <button class="btn btn-sm btn-danger" onclick="deleteColumn('${column.id}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
        });
}

function saveColumn(event) {
    event.preventDefault();
    const form = event.target;
    const id = form.querySelector('[name="id"]').value;
    const data = {
        name: form.name.value,
        color: form.color.value,
        icon: form.icon.value
    };

    const url = id ? `/dashboard/kanban/columns/${id}` : '/dashboard/kanban/columns';
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideColumnForm();
            loadColumns();
            location.reload();
        } else {
            alert(data.message || 'Erro ao salvar coluna');
        }
    });
}

function deleteColumn(id) {
    if (!confirm('Tem certeza que deseja excluir esta coluna?')) return;

    fetch(`/dashboard/kanban/columns/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadColumns();
            location.reload();
        } else {
            alert(data.message || 'Erro ao excluir coluna');
        }
    });
}
</script>
@endpush