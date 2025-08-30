@extends('layouts.admin')

@section('title', 'Importar Inscrições')

@section('content')
<div class="container-fluid px-4">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mt-4 mb-0">
                <i class="fas fa-upload me-2"></i>
                Importar Inscrições
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inscricoes') }}">Inscrições</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Importar</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.inscricoes') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Cards de Informação -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-3">
                        <i class="fas fa-file-csv fa-3x"></i>
                    </div>
                    <h5 class="card-title">Formato CSV</h5>
                    <p class="card-text text-muted">O arquivo deve estar no formato CSV com separador ponto e vírgula (;)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-3">
                        <i class="fas fa-shield-alt fa-3x"></i>
                    </div>
                    <h5 class="card-title">Sistema Kanban</h5>
                    <p class="card-text text-muted">Posições e etiquetas são respeitadas na importação, mantendo a organização do fluxo de trabalho</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-3">
                        <i class="fas fa-download fa-3x"></i>
                    </div>
                    <h5 class="card-title">Template Disponível</h5>
                    <p class="card-text text-muted">Baixe o template para ver o formato correto dos dados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário de Importação -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-upload me-2"></i>
                        Selecionar Arquivo
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.inscricoes.importar.processar') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="arquivo" class="form-label fw-bold">
                                <i class="fas fa-file me-2"></i>
                                Arquivo CSV
                            </label>
                            <input type="file" 
                                   class="form-control @error('arquivo') is-invalid @enderror" 
                                   id="arquivo" 
                                   name="arquivo" 
                                   accept=".csv,.txt"
                                   required>
                            @error('arquivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Tamanho máximo: 10MB. Formatos aceitos: CSV, TXT
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmacao" required>
                                <label class="form-check-label" for="confirmacao">
                                    Confirmo que o arquivo está no formato correto e contém dados válidos
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-outline-secondary me-md-2" onclick="limparFormulario()">
                                <i class="fas fa-eraser me-2"></i>Limpar
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnImportar" disabled>
                                <i class="fas fa-upload me-2"></i>Importar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Card de Ajuda -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Ajuda
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Campos Obrigatórios:</h6>
                    <ul class="list-unstyled mb-3">
                        <li><i class="fas fa-check text-success me-2"></i>Nome</li>
                        <li><i class="fas fa-check text-success me-2"></i>Email</li>
                        <li><i class="fas fa-check text-success me-2"></i>Telefone</li>
                    </ul>

                    <h6 class="fw-bold">Campos Opcionais:</h6>
                    <ul class="list-unstyled mb-3">
                        <li><i class="fas fa-info text-info me-2"></i>Curso (padrão: EJA)</li>
                        <li><i class="fas fa-info text-info me-2"></i>Modalidade (padrão: Online)</li>
                        <li><i class="fas fa-info text-info me-2"></i>Data/Hora (padrão: agora)</li>
                        <li><i class="fas fa-info text-info me-2"></i>Etiqueta (padrão: pendente)</li>
                        <li><i class="fas fa-info text-info me-2"></i>Posição Kanban (padrão: automática)</li>
                        <li><i class="fas fa-info text-info me-2"></i>Prioridade (padrão: média)</li>
                        <li><i class="fas fa-info text-info me-2"></i>Notas (padrão: vazio)</li>
                    </ul>

                    <div class="d-grid">
                        <a href="{{ route('admin.inscricoes.importar.template') }}" class="btn btn-outline-primary">
                            <i class="fas fa-download me-2"></i>Baixar Template
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card de Estatísticas -->
            @if(session('importados') || session('ignorados'))
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Resultado da Importação
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="text-success">
                                <h4 class="mb-0">{{ session('importados', 0) }}</h4>
                                <small>Importados</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-warning">
                                <h4 class="mb-0">{{ session('ignorados', 0) }}</h4>
                                <small>Ignorados</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Lista de Erros (se houver) -->
    @if(session('erros') && count(session('erros')) > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0 text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erros Encontrados ({{ count(session('erros')) }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Os seguintes registros não puderam ser importados. Verifique os dados e tente novamente.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Linha</th>
                                    <th>Erro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(session('erros') as $erro)
                                <tr>
                                    <td class="text-muted">{{ $erro }}</td>
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

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const arquivoInput = document.getElementById('arquivo');
    const confirmacaoCheckbox = document.getElementById('confirmacao');
    const btnImportar = document.getElementById('btnImportar');
    const form = document.getElementById('importForm');

    // Habilitar/desabilitar botão de importação
    function verificarFormulario() {
        const arquivoSelecionado = arquivoInput.files.length > 0;
        const confirmado = confirmacaoCheckbox.checked;
        btnImportar.disabled = !(arquivoSelecionado && confirmado);
    }

    arquivoInput.addEventListener('change', verificarFormulario);
    confirmacaoCheckbox.addEventListener('change', verificarFormulario);

    // Validação do formulário
    form.addEventListener('submit', function(e) {
        if (!arquivoInput.files.length) {
            e.preventDefault();
            alert('Por favor, selecione um arquivo para importar.');
            return;
        }

        if (!confirmacaoCheckbox.checked) {
            e.preventDefault();
            alert('Por favor, confirme que o arquivo está no formato correto.');
            return;
        }

        // Mostrar loading
        btnImportar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Importando...';
        btnImportar.disabled = true;
    });

    // Preview do arquivo selecionado
    arquivoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            console.log('Arquivo selecionado:', file.name, 'Tamanho:', file.size, 'bytes');
        }
    });
});

function limparFormulario() {
    document.getElementById('arquivo').value = '';
    document.getElementById('confirmacao').checked = false;
    document.getElementById('btnImportar').disabled = true;
}
</script>
@endpush
