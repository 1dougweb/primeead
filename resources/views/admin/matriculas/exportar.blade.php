@extends('layouts.admin')

@section('title', 'Exportar Matrículas')

@section('page-title', 'Exportar Matrículas')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.matriculas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar
        </a>
    </div>
@endsection

@section('content')
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.matriculas.index') }}">Matrículas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Exportar</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>
                        Configurações de Exportação
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form id="exportForm" action="{{ route('admin.matriculas.exportar.store') }}" method="POST">
                        @csrf
                        
                        <!-- Formato de Exportação -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="format" class="form-label">
                                        <strong>Formato de Exportação</strong> <span class="text-danger">*</span>
                                    </label>
                                    <select name="format" id="format" class="form-select" required>
                                        <option value="">Selecione o formato</option>
                                        <option value="csv">CSV</option>
                                        <option value="excel">Excel</option>
                                        <option value="json">JSON</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="limit" class="form-label">
                                        <strong>Limite de Registros</strong>
                                    </label>
                                    <input type="number" name="limit" id="limit" class="form-control" 
                                           value="1000" min="1" max="10000" 
                                           placeholder="Máximo: 10.000">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Deixe em branco para exportar todos
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="card mt-3 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-filter me-2"></i> Filtros (Opcional)
                                </h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">
                                                <strong>Status</strong>
                                            </label>
                                            <select name="filters[status]" id="status" class="form-select">
                                                <option value="">Todos</option>
                                                @foreach($statusOptions as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="parceiro_id" class="form-label">
                                                <strong>Parceiro</strong>
                                            </label>
                                            <select name="filters[parceiro_id]" id="parceiro_id" class="form-select">
                                                <option value="">Todos</option>
                                                @foreach($parceiros as $parceiro)
                                                    <option value="{{ $parceiro->id }}">
                                                        {{ $parceiro->nome_exibicao }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="modalidade" class="form-label">
                                                <strong>Modalidade</strong>
                                            </label>
                                            <select name="filters[modalidade]" id="modalidade" class="form-select">
                                                <option value="">Todas</option>
                                                @foreach($modalidadeOptions as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="curso" class="form-label">
                                                <strong>Curso</strong>
                                            </label>
                                            <input type="text" name="filters[curso]" id="curso" class="form-control" 
                                                   placeholder="Buscar por curso">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="data_inicio" class="form-label">
                                                <strong>Data Início</strong>
                                            </label>
                                            <input type="date" name="filters[data_inicio]" id="data_inicio" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="data_fim" class="form-label">
                                                <strong>Data Fim</strong>
                                            </label>
                                            <input type="date" name="filters[data_fim]" id="data_fim" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="valor_min" class="form-label">
                                                <strong>Valor Mínimo</strong>
                                            </label>
                                            <input type="number" name="filters[valor_min]" id="valor_min" class="form-control" 
                                                   step="0.01" min="0" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="valor_max" class="form-label">
                                                <strong>Valor Máximo</strong>
                                            </label>
                                            <input type="number" name="filters[valor_max]" id="valor_max" class="form-control" 
                                                   step="0.01" min="0" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Colunas e Ordenação -->
                        <div class="card mt-3 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-columns me-2"></i> Colunas e Ordenação
                                </h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <strong>Colunas para Exportar</strong>
                                            </label>
                                            <div class="row">
                                                @foreach($columnOptions as $value => $label)
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" 
                                                                   id="col_{{ $value }}" name="columns[]" 
                                                                   value="{{ $value }}" 
                                                                   {{ in_array($value, ['nome_completo', 'cpf', 'email', 'curso', 'status']) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="col_{{ $value }}">
                                                                {{ $label }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sort_by" class="form-label">
                                                        <strong>Ordenar por</strong>
                                                    </label>
                                                    <select name="sort_by" id="sort_by" class="form-select">
                                                        <option value="">Padrão (Data de Criação)</option>
                                                        <option value="nome_completo">Nome</option>
                                                        <option value="cpf">CPF</option>
                                                        <option value="valor_total_curso">Valor</option>
                                                        <option value="status">Status</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="sort_direction" class="form-label">
                                                        <strong>Direção</strong>
                                                    </label>
                                                    <select name="sort_direction" id="sort_direction" class="form-select">
                                                        <option value="asc">Crescente</option>
                                                        <option value="desc">Decrescente</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Opções Adicionais -->
                        <div class="card mt-3 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-cog me-2"></i> Opções Adicionais
                                </h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" 
                                                   id="include_headers" name="include_headers" value="1" checked>
                                            <label class="form-check-label" for="include_headers">
                                                Incluir cabeçalhos
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="notification_email">E-mail para notificação (Opcional)</label>
                                            <input type="email" name="notification_email" id="notification_email" 
                                                   class="form-control" placeholder="seu@email.com">
                                            <small class="form-text text-muted">
                                                Receba uma notificação quando a exportação for concluída
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="row mt-4">
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-download me-2"></i> Iniciar Exportação
                                </button>
                                <button type="button" class="btn btn-secondary btn-lg ms-2" onclick="resetForm()">
                                    <i class="fas fa-undo me-2"></i> Limpar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Status da Exportação -->
    <div class="row mt-4" id="exportStatus" style="display: none;">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i> Status da Exportação
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div id="statusContent"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Progresso -->
<div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="progressModalLabel">Processando Exportação</h5>
            </div>
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Processando...</span>
                </div>
                <p class="mb-0" id="progressMessage">Iniciando exportação...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Validação do formulário
    $('#exportForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return false;
        }
        
        // Mostrar modal de progresso
        const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
        progressModal.show();
        
        // Enviar formulário via AJAX
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess(response.message);
                    if (response.download_url) {
                        showDownloadLink(response.download_url, response.file_name);
                    }
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr) {
                let message = 'Erro interno do servidor';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showError(message);
            },
            complete: function() {
                progressModal.hide();
            }
        });
    });

    // Validação de datas
    $('#data_inicio').on('change', function() {
        $('#data_fim').attr('min', $(this).val());
    });

    $('#data_fim').on('change', function() {
        $('#data_inicio').attr('max', $(this).val());
    });

    // Validação de valores
    $('#valor_min').on('change', function() {
        $('#valor_max').attr('min', $(this).val());
    });

    $('#valor_max').on('change', function() {
        $('#valor_min').attr('max', $(this).val());
    });
});

function validateForm() {
    const format = $('#format').val();
    if (!format) {
        showError('Selecione o formato de exportação');
        return false;
    }

    const columns = $('input[name="columns[]"]:checked');
    if (columns.length === 0) {
        showError('Selecione pelo menos uma coluna para exportar');
        return false;
    }

    return true;
}

function showSuccess(message) {
    toastr.success(message);
}

function showError(message) {
    toastr.error(message);
}

function showDownloadLink(url, fileName) {
    // Mostrar mensagem de sucesso
    toastr.success(`${fileName} foi gerado com sucesso!`);
    
    // Criar modal de download
    const modalHtml = `
        <div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="downloadModalLabel">Download Disponível</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <p class="mb-3">${fileName} foi gerado com sucesso!</p>
                        <a href="${url}" class="btn btn-primary btn-lg">
                            <i class="fas fa-download me-2"></i> Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal anterior se existir
    const existingModal = document.getElementById('downloadModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Adicionar novo modal ao body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Mostrar modal
    const downloadModal = new bootstrap.Modal(document.getElementById('downloadModal'));
    downloadModal.show();
}

function resetForm() {
    $('#exportForm')[0].reset();
    $('input[name="columns[]"]').prop('checked', false);
    $('#col_nome_completo, #col_cpf, #col_email, #col_curso, #col_status').prop('checked', true);
    $('#include_headers').prop('checked', true);
}
</script>
@endpush
