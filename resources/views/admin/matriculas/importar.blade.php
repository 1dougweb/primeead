@extends('layouts.admin')

@section('title', 'Importar Matrículas')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mt-4 mb-0">
                <i class="fas fa-upload me-1"></i>
                Importar Matrículas
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.matriculas.index') }}">Matrículas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Importar</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.matriculas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Formulário de Importação -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-csv me-2"></i>
                        Upload do Arquivo
                    </h5>
                </div>
                <div class="card-body">
                    <form id="importForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Arquivo CSV -->
                        <div class="mb-3">
                            <label for="import_file" class="form-label">
                                <strong>Arquivo CSV</strong> <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('import_file') is-invalid @enderror" 
                                   id="import_file" 
                                   name="import_file" 
                                   accept=".csv,.txt"
                                   required>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Formato aceito: CSV. Tamanho máximo: 10MB.
                                <a href="{{ route('admin.matriculas.importar.template') }}" class="text-decoration-none">
                                    <i class="fas fa-download me-1"></i>Baixar template
                                </a>
                            </div>
                            @error('import_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Opções de Importação -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="batch_size" class="form-label">
                                        <strong>Tamanho do Lote</strong>
                                    </label>
                                    <select class="form-select" id="batch_size" name="batch_size">
                                        <option value="50">50 registros</option>
                                        <option value="100" selected>100 registros</option>
                                        <option value="200">200 registros</option>
                                        <option value="500">500 registros</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Processar registros em lotes para melhor performance
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dry_run" class="form-label">
                                        <strong>Modo de Teste</strong>
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="dry_run" name="dry_run">
                                        <label class="form-check-label" for="dry_run">
                                            Simulação (não salva no banco)
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Execute primeiro para verificar os dados
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comportamento com Duplicatas -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ignore_duplicates" class="form-label">
                                        <strong>Duplicatas</strong>
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="ignore_duplicates" name="ignore_duplicates" checked>
                                        <label class="form-check-label" for="ignore_duplicates">
                                            Ignorar matrículas existentes
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Evita criar registros duplicados
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="update_existing" class="form-label">
                                        <strong>Atualizar Existentes</strong>
                                    </label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="update_existing" name="update_existing">
                                        <label class="form-check-label" for="update_existing">
                                            Atualizar matrículas existentes
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Só funciona se "Ignorar duplicatas" estiver desmarcado
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mapeamento de Colunas (inicialmente oculto) -->
                        <div id="columnMappingSection" class="mt-4" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-columns me-2"></i>
                                        Mapeamento de Colunas
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="columnMappingContent">
                                        <!-- Conteúdo será preenchido dinamicamente -->
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-success" id="autoMapBtn">
                                            <i class="fas fa-magic me-1"></i>
                                            Mapeamento Automático
                                        </button>
                                        <button type="button" class="btn btn-outline-info" id="resetMappingBtn">
                                            <i class="fas fa-undo me-1"></i>
                                            Resetar Mapeamento
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                <i class="fas fa-upload me-1"></i>
                                <span id="submitText">Iniciar Importação</span>
                            </button>
                            
                            <button type="button" class="btn btn-outline-secondary" id="resetBtn">
                                <i class="fas fa-undo me-1"></i>
                                Limpar
                            </button>
                        </div>
                        
                        <!-- Campos ocultos para mapeamento de colunas -->
                        <div id="columnMappingFields" style="display: none;">
                            <!-- Serão preenchidos dinamicamente -->
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informações e Ajuda -->
        <div class="col-lg-4">
            <!-- Status da Importação -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Status da Importação
                    </h6>
                </div>
                <div class="card-body">
                    <div id="importStatus">
                        <p class="text-muted mb-0">
                            <i class="fas fa-clock me-1"></i>
                            Nenhuma importação em andamento
                        </p>
                    </div>
                </div>
            </div>

            <!-- Instruções -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Como Usar
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0 small">
                        <li>Baixe o template CSV</li>
                        <li>Preencha com os dados das matrículas</li>
                        <li>Salve como arquivo CSV</li>
                        <li>Faça upload e configure as opções</li>
                        <li>Execute primeiro em modo teste</li>
                        <li>Confirme a importação real</li>
                    </ol>
                </div>
            </div>

            <!-- Campos Obrigatórios -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Campos Obrigatórios
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        <li><strong>nome_completo</strong> - Nome completo do aluno</li>
                        <li><strong>cpf</strong> - CPF do aluno</li>
                        <li><strong>email</strong> - E-mail do aluno</li>
                        <li><strong>data_nascimento</strong> - Data de nascimento (YYYY-MM-DD)</li>
                        <li><strong>modalidade</strong> - Tipo de ensino</li>
                        <li><strong>curso</strong> - Nome do curso</li>
                    </ul>
                </div>
            </div>

            <!-- Dicas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Dicas Importantes
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        <li>Use o modo teste primeiro</li>
                        <li>Verifique o formato das datas</li>
                        <li>CPFs devem ser únicos</li>
                        <li>E-mails devem ser válidos</li>
                        <li>Arquivos grandes podem demorar</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Progresso -->
    <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="progressModalLabel">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        Importação em Andamento
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                    <p class="text-center mb-0">Processando arquivo... Por favor, aguarde.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Resultado -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalLabel">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Importação Concluída
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="resultContent">
                        <!-- Conteúdo será preenchido via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <a href="{{ route('admin.matriculas.index') }}" class="btn btn-primary" id="viewMatriculasBtn">
                        <i class="fas fa-list me-1"></i>
                        Ver Matrículas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const importForm = document.getElementById('importForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const resetBtn = document.getElementById('resetBtn');
    const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
    const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));

    // Validação do formulário
    importForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Validações adicionais
        if (!formData.get('import_file').name) {
            alert('Selecione um arquivo para importar');
            return;
        }

        // Mostrar modal de progresso
        progressModal.show();
        
        // Desabilitar botão
        submitBtn.disabled = true;
        submitText.textContent = 'Importando...';
        
        // Fazer requisição AJAX
        fetch('{{ route("admin.matriculas.importar.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            progressModal.hide();
            
            if (data.success) {
                showResult(data);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            progressModal.hide();
            showError('Erro na requisição: ' + error.message);
        })
        .finally(() => {
            // Reabilitar botão
            submitBtn.disabled = false;
            submitText.textContent = 'Iniciar Importação';
        });
    });

    // Análise automática do arquivo quando selecionado
    document.getElementById('import_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            analyzeFile(file);
        }
    });

    // Função para analisar arquivo
    function analyzeFile(file) {
        const formData = new FormData();
        formData.append('import_file', file);
        formData.append('_token', '{{ csrf_token() }}');

        // Mostrar indicador de análise
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        submitBtn.disabled = true;
        submitText.textContent = 'Analisando arquivo...';

        fetch('{{ route("admin.matriculas.importar.analyze") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayColumnAnalysis(data);
                submitBtn.disabled = false;
                submitText.textContent = 'Iniciar Importação';
            } else {
                showError(data.message || 'Erro ao analisar arquivo');
                submitBtn.disabled = true;
                submitText.textContent = 'Arquivo inválido';
            }
        })
        .catch(error => {
            console.error('Erro na análise:', error);
            showError('Erro ao analisar arquivo');
            submitBtn.disabled = true;
            submitText.textContent = 'Arquivo inválido';
        });
    }

    // Botão reset
    resetBtn.addEventListener('click', function() {
        importForm.reset();
        document.getElementById('ignore_duplicates').checked = true;
        document.getElementById('update_existing').checked = false;
        document.getElementById('batch_size').value = '100';
    });

    // Lógica para campos relacionados
    document.getElementById('ignore_duplicates').addEventListener('change', function() {
        const updateExisting = document.getElementById('update_existing');
        if (this.checked) {
            updateExisting.checked = false;
            updateExisting.disabled = true;
        } else {
            updateExisting.disabled = false;
        }
    });

    document.getElementById('update_existing').addEventListener('change', function() {
        const ignoreDuplicates = document.getElementById('ignore_duplicates');
        if (this.checked) {
            ignoreDuplicates.checked = false;
            ignoreDuplicates.disabled = true;
        } else {
            ignoreDuplicates.disabled = false;
        }
    });

    // Exibir análise de colunas
    function displayColumnAnalysis(data) {
        const analysis = data.analysis;
        const autoMapping = data.auto_mapping;
        const validation = data.validation;
        
        // Mostrar seção de mapeamento
        document.getElementById('columnMappingSection').style.display = 'block';
        
        // Gerar conteúdo do mapeamento
        let mappingHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Colunas Detectadas (${Object.keys(analysis.detected_columns).length})
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Coluna do Sistema</th>
                                    <th>Coluna do Arquivo</th>
                                    <th>Confiança</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        // Colunas detectadas
        Object.entries(analysis.detected_columns).forEach(([systemColumn, info]) => {
            const confidence = Math.round(info.confidence * 100);
            const confidenceClass = confidence >= 80 ? 'text-success' : confidence >= 60 ? 'text-warning' : 'text-danger';
            
            mappingHtml += `
                <tr>
                    <td><strong>${systemColumn}</strong></td>
                    <td>${info.header}</td>
                    <td><span class="${confidenceClass}">${confidence}%</span></td>
                </tr>
            `;
        });

        mappingHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-6">
        `;

        // Colunas faltantes
        if (analysis.missing_columns.length > 0) {
            mappingHtml += `
                <h6 class="text-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Colunas Faltantes (${analysis.missing_columns.length})
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Coluna</th>
                                <th>Descrição</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            analysis.missing_columns.forEach(missing => {
                mappingHtml += `
                    <tr>
                        <td><strong>${missing.column}</strong></td>
                        <td>${missing.description}</td>
                    </tr>
                `;
            });

            mappingHtml += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        // Colunas não mapeadas
        if (analysis.unmapped_columns.length > 0) {
            mappingHtml += `
                <h6 class="text-info">
                    <i class="fas fa-question-circle me-2"></i>
                    Colunas Não Mapeadas (${analysis.unmapped_columns.length})
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Coluna do Arquivo</th>
                                <th>Sugestões</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            analysis.unmapped_columns.forEach(unmapped => {
                let suggestionsHtml = '';
                if (unmapped.suggestions.length > 0) {
                    suggestionsHtml = unmapped.suggestions.map(suggestion => 
                        `<span class="badge bg-light text-dark me-1" title="${suggestion.description}">${suggestion.description}</span>`
                    ).join('');
                } else {
                    suggestionsHtml = '<span class="text-muted">Nenhuma sugestão</span>';
                }

                mappingHtml += `
                    <tr>
                        <td><strong>${unmapped.header}</strong></td>
                        <td>${suggestionsHtml}</td>
                    </tr>
                `;
            });

            mappingHtml += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        mappingHtml += `
                </div>
            </div>
            
            <div class="mt-3">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Total de registros detectados:</strong> ${analysis.total_rows}
                </div>
            </div>
        `;

        // Exibir validação
        if (!validation.valid) {
            mappingHtml += `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Problemas encontrados:</strong>
                    <ul class="mb-0 mt-2">
                        ${validation.errors.map(error => `<li>${error}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        if (validation.warnings.length > 0) {
            mappingHtml += `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Avisos:</strong>
                    <ul class="mb-0 mt-2">
                        ${validation.warnings.map(warning => `<li>${warning}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        document.getElementById('columnMappingContent').innerHTML = mappingHtml;
        
        // Gerar campos ocultos para mapeamento
        generateMappingFields(autoMapping);
        
        // Habilitar botão de importação se validação passar
        if (validation.valid) {
            document.getElementById('submitBtn').disabled = false;
        }
    }

    // Gerar campos ocultos para mapeamento
    function generateMappingFields(mapping) {
        const container = document.getElementById('columnMappingFields');
        container.innerHTML = '';
        
        Object.entries(mapping).forEach(([systemColumn, fileIndex]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `column_mapping[${systemColumn}]`;
            input.value = fileIndex;
            container.appendChild(input);
        });
        
        container.style.display = 'block';
    }

    // Mostrar resultado
    function showResult(data) {
        const resultContent = document.getElementById('resultContent');
        
        let summaryHtml = '';
        if (data.summary) {
            summaryHtml = `
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>${data.message}</h6>
                </div>
                
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="text-primary mb-1">${data.summary.total_processed}</h4>
                            <small class="text-muted">Total Processados</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="text-success mb-1">${data.summary.created}</h4>
                            <small class="text-muted">Criados</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="text-info mb-1">${data.summary.updated}</h4>
                            <small class="text-muted">Atualizados</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="text-warning mb-1">${data.summary.skipped}</h4>
                            <small class="text-muted">Ignorados</small>
                        </div>
                    </div>
                </div>
                
                ${data.summary.errors > 0 ? `
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>${data.summary.errors}</strong> registros com erros. Verifique os logs para detalhes.
                    </div>
                ` : ''}
            `;
        } else {
            summaryHtml = `
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>${data.message}</h6>
                </div>
            `;
        }
        
        resultContent.innerHTML = summaryHtml;
        resultModal.show();
    }

    // Mostrar erro
    function showError(message) {
        const resultContent = document.getElementById('resultContent');
        resultContent.innerHTML = `
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Erro na Importação</h6>
                <p class="mb-0">${message}</p>
            </div>
        `;
        resultModal.show();
    }

    // Verificar status da importação
    function checkImportStatus() {
        fetch('{{ route("admin.matriculas.importar.status") }}')
            .then(response => response.json())
            .then(data => {
                const statusDiv = document.getElementById('importStatus');
                
                if (data.has_imports) {
                    statusDiv.innerHTML = `
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-sync fa-spin me-2"></i>
                            <strong>${data.import_count}</strong> arquivo(s) de importação encontrado(s)
                            <br><small class="text-muted">Último: ${new Date(data.last_import.modified * 1000).toLocaleString()}</small>
                        </div>
                    `;
                } else {
                    statusDiv.innerHTML = `
                        <p class="text-muted mb-0">
                            <i class="fas fa-clock me-1"></i>
                            Nenhuma importação em andamento
                        </p>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro ao verificar status:', error);
            });
    }

    // Botão de mapeamento automático
    document.getElementById('autoMapBtn').addEventListener('click', function() {
        // Recarregar análise do arquivo
        const fileInput = document.getElementById('import_file');
        if (fileInput.files[0]) {
            analyzeFile(fileInput.files[0]);
        }
    });

    // Botão de resetar mapeamento
    document.getElementById('resetMappingBtn').addEventListener('click', function() {
        document.getElementById('columnMappingSection').style.display = 'none';
        document.getElementById('columnMappingFields').style.display = 'none';
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitText').textContent = 'Selecione um arquivo';
    });

    // Verificar status a cada 30 segundos
    checkImportStatus();
    setInterval(checkImportStatus, 30000);
});
</script>
@endpush
@endsection
