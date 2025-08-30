@extends('layouts.admin')

@section('title', 'Parceiros')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Parceiros</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header com Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['total'] }}</h4>
                            <p class="mb-0">Total de Parceiros</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-handshake fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['pendentes'] }}</h4>
                            <p class="mb-0">Pendentes</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['ativos'] }}</h4>
                            <p class="mb-0">Ativos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $stats['aprovados'] }}</h4>
                            <p class="mb-0">Aprovados</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros e Busca -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nome, email...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                        <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="rejeitado" {{ request('status') == 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                        <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="modalidade" class="form-label">Modalidade</label>
                    <select class="form-select" id="modalidade" name="modalidade">
                        <option value="">Todas</option>
                        <option value="Polo Presencial" {{ request('modalidade') == 'Polo Presencial' ? 'selected' : '' }}>Polo Presencial</option>
                        <option value="EaD" {{ request('modalidade') == 'EaD' ? 'selected' : '' }}>EaD</option>
                        <option value="Híbrido" {{ request('modalidade') == 'Híbrido' ? 'selected' : '' }}>Híbrido</option>
                        <option value="Representante" {{ request('modalidade') == 'Representante' ? 'selected' : '' }}>Representante</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="estrutura" class="form-label">Possui Estrutura</label>
                    <select class="form-select" id="estrutura" name="estrutura">
                        <option value="">Todos</option>
                        <option value="1" {{ request('estrutura') == '1' ? 'selected' : '' }}>Sim</option>
                        <option value="0" {{ request('estrutura') == '0' ? 'selected' : '' }}>Não</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.parceiros.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                        <a href="{{ route('admin.parceiros.exportar') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-success">
                            <i class="fas fa-download"></i> Exportar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Parceiros -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Lista de Parceiros ({{ $parceiros->total() }})</h5>
            <a href="{{ route('admin.parceiros.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Novo Parceiro
            </a>
        </div>
        <div class="card-body">
            @if($parceiros->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>WhatsApp</th>
                                <th>Modalidade</th>
                                <th>Estrutura</th>
                                <th>Status</th>
                                <th>Data Cadastro</th>
                                <th width="200">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parceiros as $parceiro)
                                <tr>
                                    <td>
                                        <strong>{{ $parceiro->nome_completo }}</strong>
                                        @if($parceiro->nome_fantasia)
                                            <br><small class="text-muted">{{ $parceiro->nome_fantasia }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $parceiro->email }}</td>
                                    <td>
                                        @if($parceiro->whatsapp)
                                            <a href="https://wa.me/55{{ preg_replace('/\D/', '', $parceiro->whatsapp) }}" target="_blank" class="text-success">
                                                {{ $parceiro->whatsapp_formatado }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @switch($parceiro->modalidade_parceria)
                                            @case('Polo Presencial')
                                                <span class="badge bg-primary">Polo Presencial</span>
                                                @break
                                            @case('EaD')
                                                <span class="badge bg-info">EaD</span>
                                                @break
                                            @case('Híbrido')
                                                <span class="badge bg-warning">Híbrido</span>
                                                @break
                                            @case('Representante')
                                                <span class="badge bg-secondary">Representante</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ $parceiro->modalidade_parceria ?? '-' }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($parceiro->possui_estrutura)
                                            <span class="badge bg-success">Sim</span>
                                        @else
                                            <span class="badge bg-warning">Não</span>
                                        @endif
                                    </td>
                                    <td>{!! $parceiro->status_badge !!}</td>
                                    <td>{{ $parceiro->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.parceiros.show', $parceiro) }}" class="btn btn-sm btn-outline-primary" title="Ver Detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                                                        @if($parceiro->status === 'pendente')
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="showApproveModal({{ $parceiro->id }}, '{{ $parceiro->nome_completo }}')" title="Aprovar">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="showRejectModal({{ $parceiro->id }}, '{{ $parceiro->nome_completo }}')" title="Rejeitar">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                            
                            @if($parceiro->status === 'aprovado')
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="showActivateModal({{ $parceiro->id }}, '{{ $parceiro->nome_completo }}')" title="Ativar">
                                    <i class="fas fa-play"></i>
                                </button>
                            @endif
                            
                            @if($parceiro->status === 'ativo')
                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="showDeactivateModal({{ $parceiro->id }}, '{{ $parceiro->nome_completo }}')" title="Inativar">
                                    <i class="fas fa-pause"></i>
                                </button>
                            @endif
                                            
                                                                        @if($parceiro->whatsapp)
                                <a href="https://wa.me/55{{ preg_replace('/\D/', '', $parceiro->whatsapp) }}" target="_blank" class="btn btn-sm btn-outline-success" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            @endif
                                            
                                            <a href="{{ route('admin.parceiros.edit', $parceiro) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div class="d-flex justify-content-center">
                    {{ $parceiros->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Nenhum parceiro encontrado.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modais de Ação -->
<!-- Modal de Aprovação -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Aprovar Parceiro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center">Tem certeza que deseja aprovar o parceiro:</p>
                <p class="text-center"><strong id="approvePartnerName"></strong></p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Ao aprovar, o parceiro receberá um email de confirmação e poderá ser ativado posteriormente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="confirmApprove()">
                    <i class="fas fa-check me-1"></i>Confirmar Aprovação
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Rejeição -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Rejeitar Parceiro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-times-circle text-danger" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center">Tem certeza que deseja rejeitar o parceiro:</p>
                <p class="text-center"><strong id="rejectPartnerName"></strong></p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Ao rejeitar, o parceiro receberá um email informando sobre a decisão. Esta ação pode ser revertida posteriormente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">
                    <i class="fas fa-times me-1"></i>Confirmar Rejeição
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Ativação -->
<div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="activateModalLabel">
                    <i class="fas fa-play-circle me-2"></i>Ativar Parceiro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-play-circle text-primary" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center">Tem certeza que deseja ativar o parceiro:</p>
                <p class="text-center"><strong id="activatePartnerName"></strong></p>
                <div class="alert alert-success">
                    <i class="fas fa-info-circle me-2"></i>
                    Ao ativar, o parceiro estará apto a receber leads e comissões conforme configurado.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmActivate()">
                    <i class="fas fa-play me-1"></i>Confirmar Ativação
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Inativação -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="deactivateModalLabel">
                    <i class="fas fa-pause-circle me-2"></i>Inativar Parceiro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-pause-circle text-warning" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center">Tem certeza que deseja inativar o parceiro:</p>
                <p class="text-center"><strong id="deactivatePartnerName"></strong></p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Ao inativar, o parceiro não receberá mais leads nem comissões. Esta ação pode ser revertida posteriormente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-warning" onclick="confirmDeactivate()">
                    <i class="fas fa-pause me-1"></i>Confirmar Inativação
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para ações -->
<script>
// Variáveis globais para armazenar dados do modal
let currentPartnerId = null;
let currentPartnerName = null;

// Funções para mostrar modais
function showApproveModal(id, name) {
    currentPartnerId = id;
    currentPartnerName = name;
    document.getElementById('approvePartnerName').textContent = name;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function showRejectModal(id, name) {
    currentPartnerId = id;
    currentPartnerName = name;
    document.getElementById('rejectPartnerName').textContent = name;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showActivateModal(id, name) {
    currentPartnerId = id;
    currentPartnerName = name;
    document.getElementById('activatePartnerName').textContent = name;
    new bootstrap.Modal(document.getElementById('activateModal')).show();
}

function showDeactivateModal(id, name) {
    currentPartnerId = id;
    currentPartnerName = name;
    document.getElementById('deactivatePartnerName').textContent = name;
    new bootstrap.Modal(document.getElementById('deactivateModal')).show();
}

// Funções de confirmação
function confirmApprove() {
    executeAction(`/dashboard/parceiros/${currentPartnerId}/aprovar`, 'approveModal');
}

function confirmReject() {
    executeAction(`/dashboard/parceiros/${currentPartnerId}/rejeitar`, 'rejectModal');
}

function confirmActivate() {
    executeAction(`/dashboard/parceiros/${currentPartnerId}/ativar`, 'activateModal');
}

function confirmDeactivate() {
    executeAction(`/dashboard/parceiros/${currentPartnerId}/inativar`, 'deactivateModal');
}

// Função genérica para executar ações
function executeAction(url, modalId) {
    // Mostrar loading no botão
    const modal = document.getElementById(modalId);
    const confirmBtn = modal.querySelector('.btn:not(.btn-secondary)');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processando...';
    confirmBtn.disabled = true;

    // Obter token CSRF
    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || `Erro ${response.status}: ${response.statusText}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Fechar modal
            bootstrap.Modal.getInstance(modal).hide();
            
            // Mostrar toast de sucesso (se disponível) ou recarregar página
            if (typeof showToast === 'function') {
                showToast(data.message || 'Ação executada com sucesso!', 'success');
                // Recarregar após um pequeno delay para mostrar o toast
                setTimeout(() => location.reload(), 1000);
            } else {
                location.reload();
            }
        } else {
            throw new Error(data.message || 'Erro na operação');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Restaurar botão
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
        
        // Mostrar erro
        if (typeof showToast === 'function') {
            showToast(error.message || 'Erro ao executar ação', 'error');
        } else {
            alert('Erro: ' + (error.message || 'Erro ao executar ação'));
        }
    });
}

// Função para mostrar toast (opcional)
function showToast(message, type = 'success') {
    // Criar toast dinamicamente se não existir
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        document.body.appendChild(toastContainer);
    }

    const toastId = 'toast-' + Date.now();
    const toastColor = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
    const toastIcon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    
    const toastHtml = `
        <div id="${toastId}" class="toast ${toastColor}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${toastColor}">
                <i class="fas ${toastIcon} me-2"></i>
                <strong class="me-auto">${type === 'success' ? 'Sucesso' : 'Erro'}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    // Remover toast após ser ocultado
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}
</script>
@endsection 