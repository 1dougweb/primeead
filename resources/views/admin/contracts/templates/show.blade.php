@extends('layouts.admin')

@section('title', 'Visualizar Template de Contrato')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-eye me-2"></i>
                        Visualizar Template
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.contracts.templates.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Voltar
                        </a>
                        <a href="{{ route('admin.contracts.templates.edit', $template) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            Editar
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Informações Básicas -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Informações Básicas
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Nome:</strong>
                                                <p class="mb-0">{{ $template->name }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Dias de Validade:</strong>
                                                <p class="mb-0">{{ $template->validity_days }} dias</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($template->description)
                                        <div class="mb-3">
                                            <strong>Descrição:</strong>
                                            <p class="mb-0">{{ $template->description }}</p>
                                        </div>
                                    @endif
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Status:</strong>
                                                <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                                    {{ $template->is_active ? 'Ativo' : 'Inativo' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Tipo:</strong>
                                                @if($template->is_default)
                                                    <span class="badge bg-warning">Template Padrão</span>
                                                @else
                                                    <span class="badge bg-info">Template Personalizado</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Conteúdo do Template -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-file-alt me-2"></i>
                                        Conteúdo do Template
                                    </h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="generatePreview()">
                                        <i class="fas fa-eye me-1"></i>
                                        Preview
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Conteúdo HTML</label>
                                        <div class="border rounded p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                                            <pre class="mb-0"><code>{{ $template->content }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview -->
                            <div class="card mt-4" id="previewCard" style="display: none;">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-eye me-2"></i>
                                        Preview do Template
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div id="previewContent" class="border rounded p-4" style="background-color: white;">
                                        <!-- Preview será inserido aqui -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Estatísticas -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        Estatísticas
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Contratos Gerados:</strong>
                                        <span class="badge bg-info fs-6">{{ $template->contracts_count }}</span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Contratos Assinados:</strong>
                                        <span class="badge bg-success fs-6">{{ $template->contracts->where('status', 'signed')->count() }}</span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Contratos Pendentes:</strong>
                                        <span class="badge bg-warning fs-6">{{ $template->contracts->whereIn('status', ['draft', 'sent'])->count() }}</span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>Contratos Expirados:</strong>
                                        <span class="badge bg-danger fs-6">{{ $template->contracts->where('status', 'expired')->count() }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Informações do Sistema -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info me-2"></i>
                                        Informações do Sistema
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Criado em:</strong>
                                        <br>
                                        <small class="text-muted">{{ $template->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    
                                    @if($template->creator)
                                        <div class="mb-3">
                                            <strong>Criado por:</strong>
                                            <br>
                                            <small class="text-muted">{{ $template->creator->name }}</small>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-3">
                                        <strong>Última atualização:</strong>
                                        <br>
                                        <small class="text-muted">{{ $template->updated_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong>ID do Template:</strong>
                                        <br>
                                        <small class="text-muted">{{ $template->id }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Ações -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-cogs me-2"></i>
                                        Ações
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.contracts.templates.edit', $template) }}" class="btn btn-primary">
                                            <i class="fas fa-edit me-1"></i>
                                            Editar Template
                                        </a>
                                        
                                        @if(!$template->is_default)
                                            <button type="button" class="btn btn-warning" onclick="setAsDefault()">
                                                <i class="fas fa-star me-1"></i>
                                                Definir como Padrão
                                            </button>
                                        @endif
                                        
                                        <button type="button" class="btn btn-{{ $template->is_active ? 'secondary' : 'success' }}" onclick="toggleActive()">
                                            <i class="fas fa-{{ $template->is_active ? 'pause' : 'play' }} me-1"></i>
                                            {{ $template->is_active ? 'Desativar' : 'Ativar' }}
                                        </button>
                                        
                                        <button type="button" class="btn btn-info" onclick="duplicateTemplate()">
                                            <i class="fas fa-copy me-1"></i>
                                            Duplicar
                                        </button>
                                        
                                        @if($template->contracts_count == 0)
                                            <button type="button" class="btn btn-danger" onclick="deleteTemplate()">
                                                <i class="fas fa-trash me-1"></i>
                                                Excluir
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contratos Recentes -->
                    @if($template->contracts->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-file-contract me-2"></i>
                                            Contratos Recentes
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Número</th>
                                                        <th>Aluno</th>
                                                        <th>Status</th>
                                                        <th>Criado em</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($template->contracts as $contract)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $contract->contract_number }}</strong>
                                                            </td>
                                                            <td>
                                                                @if($contract->matricula)
                                                                    {{ $contract->matricula->nome_completo }}
                                                                @else
                                                                    {{ $contract->student_email }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @switch($contract->status)
                                                                    @case('draft')
                                                                        <span class="badge bg-secondary">Rascunho</span>
                                                                        @break
                                                                    @case('sent')
                                                                        <span class="badge bg-info">Enviado</span>
                                                                        @break
                                                                    @case('signed')
                                                                        <span class="badge bg-success">Assinado</span>
                                                                        @break
                                                                    @case('expired')
                                                                        <span class="badge bg-danger">Expirado</span>
                                                                        @break
                                                                    @case('cancelled')
                                                                        <span class="badge bg-warning">Cancelado</span>
                                                                        @break
                                                                    @default
                                                                        <span class="badge bg-secondary">{{ $contract->status }}</span>
                                                                @endswitch
                                                            </td>
                                                            <td>
                                                                <small class="text-muted">{{ $contract->created_at->format('d/m/Y H:i') }}</small>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="{{ route('admin.contracts.view-signed', $contract) }}" 
                                                                       class="btn btn-outline-primary" title="Visualizar">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                    @if($contract->status === 'signed')
                                                                        <a href="{{ route('admin.contracts.download-pdf', $contract) }}" 
                                                                           class="btn btn-outline-success" title="Download PDF">
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generatePreview() {
    const content = `{!! addslashes($template->content) !!}`;
    const previewDiv = document.getElementById('previewContent');
    const previewCard = document.getElementById('previewCard');
    
    // Dados de exemplo para preview
    const sampleData = {
        'student_name': 'João da Silva Santos',
        'student_email': 'joao@exemplo.com',
        'student_cpf': '123.456.789-00',
        'student_rg': '12.345.678-9',
        'student_phone': '(11) 99999-9999',
        'student_address': 'Rua das Flores, 123, Centro, São Paulo - SP',
        'student_birth_date': '01/01/1990',
        'student_nationality': 'Brasileira',
        'student_civil_status': 'Solteiro(a)',
        'student_mother_name': 'Maria da Silva',
        'student_father_name': 'José Santos',
        'course_name': 'Ensino Médio EJA',
        'course_modality': 'Presencial',
        'course_shift': 'Noturno',
        'tuition_value': 'R$ 250,00',
        'enrollment_value': 'R$ 100,00',
        'enrollment_number': '2024001',
        'enrollment_date': '15/01/2024',
        'due_date': '10',
        'payment_method': 'Boleto Bancário',
        'school_name': 'EJA Supletivo',
        'current_date': new Date().toLocaleDateString('pt-BR'),
        'current_year': new Date().getFullYear(),
        'contract_date': new Date().toLocaleDateString('pt-BR'),
    };
    
    let previewContent = content;
    
    // Substituir variáveis
    Object.keys(sampleData).forEach(key => {
        const regex = new RegExp('{{' + key + '}}', 'g');
        previewContent = previewContent.replace(regex, sampleData[key]);
    });
    
    previewDiv.innerHTML = previewContent;
    previewCard.style.display = 'block';
    
    // Scroll para o preview
    previewCard.scrollIntoView({ behavior: 'smooth' });
}

function setAsDefault() {
    if (confirm('Deseja definir este template como padrão?')) {
        fetch('{{ route("admin.contracts.templates.set-default", $template) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao definir template como padrão');
        });
    }
}

function toggleActive() {
    const action = '{{ $template->is_active ? "desativar" : "ativar" }}';
    if (confirm(`Deseja ${action} este template?`)) {
        fetch('{{ route("admin.contracts.templates.toggle-active", $template) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao alterar status do template');
        });
    }
}

function duplicateTemplate() {
    if (confirm('Deseja duplicar este template?')) {
        fetch('{{ route("admin.contracts.templates.duplicate", $template) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao duplicar template');
        });
    }
}

function deleteTemplate() {
    if (confirm('Tem certeza que deseja excluir este template? Esta ação não pode ser desfeita.')) {
        fetch('{{ route("admin.contracts.templates.destroy", $template) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => {
            if (response.ok) {
                window.location.href = '{{ route("admin.contracts.templates.index") }}';
            } else {
                alert('Erro ao excluir template');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao excluir template');
        });
    }
}
</script>
@endsection 