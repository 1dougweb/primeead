@extends('layouts.admin')

@section('title', 'Contratos Digitais')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-contract me-2"></i>
                        Contratos Digitais
                    </h3>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" onclick="showBulkActions()">
                            <i class="fas fa-tasks me-1"></i>
                            Ações em Lote
                        </button>
                        <a href="{{ route('admin.contracts.templates.index') }}" class="btn btn-info">
                            <i class="fas fa-file-alt me-1"></i>
                            Templates
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" onchange="filterContracts()">
                                <option value="">Todos os Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>📝 Rascunho</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>📤 Enviado</option>
                                <option value="viewed" {{ request('status') == 'viewed' ? 'selected' : '' }}>👁️ Visualizado</option>
                                <option value="signed" {{ request('status') == 'signed' ? 'selected' : '' }}>✅ Assinado</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>⏰ Expirado</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período</label>
                            <select class="form-select" name="period" onchange="filterContracts()">
                                <option value="">Todos os Períodos</option>
                                <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Hoje</option>
                                <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Esta Semana</option>
                                <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Este Mês</option>
                                <option value="quarter" {{ request('period') == 'quarter' ? 'selected' : '' }}>Este Trimestre</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pesquisar</label>
                            <input type="text" class="form-control" name="search" placeholder="Número do contrato, nome do aluno, email..." value="{{ request('search') }}" onkeyup="delayedSearch(this.value)">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                <i class="fas fa-times me-1"></i>
                                Limpar
                            </button>
                        </div>
                    </div>

                    <!-- Estatísticas Rápidas -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-light">
                                <div class="card-body text-center p-3">
                                    <h5 class="mb-1">{{ $contracts->total() }}</h5>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center p-3">
                                    <h5 class="mb-1">{{ $contracts->where('status', 'signed')->count() }}</h5>
                                    <small>Assinados</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center p-3">
                                    <h5 class="mb-1">{{ $contracts->whereIn('status', ['sent', 'viewed'])->count() }}</h5>
                                    <small>Pendentes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center p-3">
                                    <h5 class="mb-1">{{ $contracts->where('status', 'expired')->count() }}</h5>
                                    <small>Expirados</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center p-3">
                                    <h5 class="mb-1">{{ $contracts->where('status', 'draft')->count() }}</h5>
                                    <small>Rascunhos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-dark text-white">
                                <div class="card-body text-center p-3">
                                    <h5 class="mb-1">{{ $contracts->where('status', 'cancelled')->count() }}</h5>
                                    <small>Cancelados</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Contratos -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" id="selectAll" onchange="toggleAllContracts()">
                                    </th>
                                    <th>Contrato</th>
                                    <th>Aluno</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Criado</th>
                                    <th>Expiração</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contracts as $contract)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="contract-checkbox" value="{{ $contract->id }}">
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $contract->contract_number }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $contract->title }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $contract->matricula->nome_completo }}</strong>
                                            <br>
                                            <small class="text-muted">Mat: {{ $contract->matricula->numero_matricula }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $contract->student_email }}</td>
                                    <td>
                                        <span class="badge bg-{{ $contract->status_color }}">
                                            {{ $contract->status_formatted }}
                                        </span>
                                        @if($contract->signed_at)
                                            <br>
                                            <small class="text-muted">
                                                Assinado em {{ $contract->signed_at->format('d/m/Y H:i') }}
                                            </small>
                                        @elseif($contract->viewed_at)
                                            <br>
                                            <small class="text-muted">
                                                Visualizado em {{ $contract->viewed_at->format('d/m/Y H:i') }}
                                            </small>
                                        @elseif($contract->sent_at)
                                            <br>
                                            <small class="text-muted">
                                                Enviado em {{ $contract->sent_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            {{ $contract->created_at->format('d/m/Y H:i') }}
                                            <br>
                                            <small class="text-muted">
                                                por {{ $contract->creator->name ?? 'Sistema' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($contract->access_expires_at)
                                            <div class="{{ $contract->isExpired() ? 'text-danger' : ($contract->access_expires_at->diffInDays() <= 3 ? 'text-warning' : 'text-success') }}">
                                                {{ $contract->access_expires_at->format('d/m/Y H:i') }}
                                                <br>
                                                <small>
                                                    {{ $contract->access_expires_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        @else
                                            <span class="text-muted">Sem expiração</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if($contract->status === 'signed')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.contracts.view-signed', $contract) }}">
                                                            <i class="fas fa-eye me-2"></i>
                                                            Visualizar Assinado
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.contracts.download-pdf', $contract) }}">
                                                            <i class="fas fa-download me-2"></i>
                                                            Download PDF
                                                        </a>
                                                    </li>
                                                @else
                                                    <li>
                                                        <a class="dropdown-item" href="{{ $contract->getAccessLink() }}" target="_blank">
                                                            <i class="fas fa-external-link-alt me-2"></i>
                                                            Abrir Link Público
                                                        </a>
                                                    </li>
                                                    @if(in_array($contract->status, ['draft', 'expired']))
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="sendContract({{ $contract->id }})">
                                                                <i class="fas fa-paper-plane me-2"></i>
                                                                Enviar por Email
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if(in_array($contract->status, ['draft', 'sent', 'viewed', 'expired']))
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="sendContractWhatsApp({{ $contract->id }})">
                                                                <i class="fab fa-whatsapp me-2 text-success"></i>
                                                                Enviar via WhatsApp
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if($contract->status !== 'cancelled')
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="regenerateLink({{ $contract->id }})">
                                                                <i class="fas fa-refresh me-2"></i>
                                                                Gerar Novo Link
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endif
                                                
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.matriculas.show', $contract->matricula) }}">
                                                        <i class="fas fa-user me-2"></i>
                                                        Ver Matrícula
                                                    </a>
                                                </li>
                                                
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="updateContractVariables({{ $contract->id }})">
                                                        <i class="fas fa-sync me-2"></i>
                                                        Atualizar Variáveis
                                                    </a>
                                                </li>
                                                
                                                @if($contract->status !== 'signed')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="cancelContract({{ $contract->id }})">
                                                            <i class="fas fa-times me-2"></i>
                                                            Cancelar Contrato
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-file-contract fa-3x mb-3"></i>
                                            <p>Nenhum contrato encontrado.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    @if($contracts->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $contracts->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ações em Lote -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ações em Lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Selecione uma ação para aplicar aos contratos selecionados:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-success" onclick="bulkSendContracts()">
                        <i class="fas fa-paper-plane me-2"></i>
                        Enviar Contratos Selecionados
                    </button>
                    <button class="btn btn-info" onclick="bulkRegenerateLinks()">
                        <i class="fas fa-refresh me-2"></i>
                        Gerar Novos Links
                    </button>
                    <button class="btn btn-warning" onclick="bulkExtendExpiration()">
                        <i class="fas fa-clock me-2"></i>
                        Estender Expiração
                    </button>
                    <button class="btn btn-danger" onclick="bulkCancelContracts()">
                        <i class="fas fa-times me-2"></i>
                        Cancelar Contratos
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let searchTimeout;

function delayedSearch(query) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        filterContracts();
    }, 500);
}

function filterContracts() {
    const status = document.querySelector('[name="status"]').value;
    const period = document.querySelector('[name="period"]').value;
    const search = document.querySelector('[name="search"]').value;
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (period) params.append('period', period);
    if (search) params.append('search', search);
    
    window.location.href = '{{ route("admin.contracts.index") }}?' + params.toString();
}

function clearFilters() {
    window.location.href = '{{ route("admin.contracts.index") }}';
}

function toggleAllContracts() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.contract-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function getSelectedContracts() {
    const checkboxes = document.querySelectorAll('.contract-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function showBulkActions() {
    const selected = getSelectedContracts();
    if (selected.length === 0) {
        alert('Selecione pelo menos um contrato.');
        return;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('bulkActionsModal'));
    modal.show();
}

function sendContract(contractId) {
    if (!confirm('Tem certeza que deseja enviar este contrato por email para o aluno?')) {
        return;
    }
    
    fetch(`/admin/contracts/${contractId}/send`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Contrato enviado por email com sucesso!');
            window.location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao enviar contrato.');
    });
}

function sendContractWhatsApp(contractId) {
    if (!confirm('Tem certeza que deseja enviar este contrato via WhatsApp para o aluno?')) {
        return;
    }
    
    fetch(`/admin/contracts/${contractId}/send-whatsapp`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Contrato enviado via WhatsApp com sucesso!');
            window.location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao enviar contrato via WhatsApp.');
    });
}

function regenerateLink(contractId) {
    if (!confirm('Tem certeza que deseja gerar um novo link? O link anterior será invalidado.')) {
        return;
    }
    
    fetch(`/admin/contracts/${contractId}/regenerate-link`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Novo link gerado com sucesso!');
            window.location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao gerar novo link.');
    });
}

function cancelContract(contractId) {
    if (!confirm('Tem certeza que deseja cancelar este contrato? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    fetch(`/admin/contracts/${contractId}/cancel`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Contrato cancelado com sucesso!');
            window.location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao cancelar contrato.');
    });
}

function updateContractVariables(contractId) {
    if (!confirm('Tem certeza que deseja atualizar as variáveis deste contrato com os dados atuais da matrícula?')) {
        return;
    }
    
    fetch(`/admin/contracts/${contractId}/update-variables`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = 'Variáveis do contrato atualizadas com sucesso!\n\n';
            message += `Valor Matrícula: ${data.variables.enrollment_value}\n`;
            message += `Valor Mensalidade: ${data.variables.tuition_value}\n`;
            message += `Forma de Pagamento: ${data.variables.payment_method}\n`;
            message += `Aluno: ${data.variables.student_name}`;
            
            alert(message);
            window.location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar variáveis do contrato.');
    });
}

// Funções de ações em lote
function bulkSendContracts() {
    const selected = getSelectedContracts();
    if (selected.length === 0) return;
    
    if (!confirm(`Tem certeza que deseja enviar ${selected.length} contratos?`)) {
        return;
    }
    
    // Implementar requisição para envio em lote
    alert('Funcionalidade em desenvolvimento.');
}

function bulkRegenerateLinks() {
    const selected = getSelectedContracts();
    if (selected.length === 0) return;
    
    if (!confirm(`Tem certeza que deseja gerar novos links para ${selected.length} contratos?`)) {
        return;
    }
    
    // Implementar requisição para regeneração em lote
    alert('Funcionalidade em desenvolvimento.');
}

function bulkExtendExpiration() {
    const selected = getSelectedContracts();
    if (selected.length === 0) return;
    
    const days = prompt('Por quantos dias deseja estender a expiração?', '30');
    if (!days || isNaN(days)) return;
    
    // Implementar requisição para extensão em lote
    alert('Funcionalidade em desenvolvimento.');
}

function bulkCancelContracts() {
    const selected = getSelectedContracts();
    if (selected.length === 0) return;
    
    if (!confirm(`Tem certeza que deseja cancelar ${selected.length} contratos? Esta ação não pode ser desfeita.`)) {
        return;
    }
    
    // Implementar requisição para cancelamento em lote
    alert('Funcionalidade em desenvolvimento.');
}
</script>
@endsection 