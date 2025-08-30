@extends('layouts.admin')

@section('title', 'Detalhes do Parceiro')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-handshake text-primary me-2"></i>
                Detalhes do Parceiro
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.parceiros.index') }}">Parceiros</a></li>
                    <li class="breadcrumb-item active">{{ $parceiro->nome_completo }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.parceiros.edit', $parceiro) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Editar
            </a>
            <a href="{{ route('admin.parceiros.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Card Principal -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user me-2"></i>Informações do Parceiro
                    </h6>
                    <div>
                        {!! $parceiro->status_badge !!}
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Dados Pessoais -->
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-id-card me-2"></i>Dados Pessoais
                            </h6>
                            <div class="mb-3">
                                <label class="form-label text-muted">Nome Completo</label>
                                <p class="mb-1 fw-bold">{{ $parceiro->nome_completo }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Email</label>
                                <p class="mb-1">
                                    <a href="mailto:{{ $parceiro->email }}" class="text-decoration-none">
                                        {{ $parceiro->email }}
                                    </a>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Telefone</label>
                                <p class="mb-1">
                                    <a href="tel:{{ $parceiro->telefone }}" class="text-decoration-none">
                                        {{ $parceiro->telefone_formatado }}
                                    </a>
                                </p>
                            </div>
                            @if($parceiro->whatsapp)
                            <div class="mb-3">
                                <label class="form-label text-muted">WhatsApp</label>
                                <p class="mb-1">
                                    <a href="https://wa.me/55{{ $parceiro->whatsapp }}" target="_blank" class="text-decoration-none text-success">
                                        <i class="fab fa-whatsapp me-1"></i>{{ $parceiro->whatsapp_formatado }}
                                    </a>
                                </p>
                            </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label text-muted">Documento ({{ strtoupper($parceiro->tipo_documento) }})</label>
                                <p class="mb-1">{{ $parceiro->documento_formatado }}</p>
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i>Endereço
                            </h6>
                            <div class="mb-3">
                                <label class="form-label text-muted">CEP</label>
                                <p class="mb-1">{{ $parceiro->cep_formatado }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Endereço</label>
                                <p class="mb-1">
                                    {{ $parceiro->endereco }}, {{ $parceiro->numero }}
                                    @if($parceiro->complemento)
                                        - {{ $parceiro->complemento }}
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Bairro</label>
                                <p class="mb-1">{{ $parceiro->bairro }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Cidade/Estado</label>
                                <p class="mb-1">{{ $parceiro->cidade }}/{{ $parceiro->estado }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dados da Parceria -->
                    <hr class="my-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                        <i class="fas fa-handshake me-2"></i>Dados da Parceria
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Modalidade da Parceria</label>
                                <p class="mb-1">
                                    <span class="badge bg-info">{{ $parceiro->modalidade_parceria }}</span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Possui Estrutura?</label>
                                <p class="mb-1">
                                    @if($parceiro->possui_estrutura)
                                        <span class="badge bg-success">Sim</span>
                                    @else
                                        <span class="badge bg-warning">Não</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Tem Site?</label>
                                <p class="mb-1">
                                    @if($parceiro->tem_site)
                                        <span class="badge bg-success">Sim</span>
                                        @if($parceiro->site_url)
                                            <br><a href="{{ $parceiro->site_url }}" target="_blank" class="text-decoration-none small">
                                                {{ $parceiro->site_url }} <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                    @else
                                        <span class="badge bg-warning">Não</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Experiência Educacional</label>
                                <p class="mb-1">
                                    @if($parceiro->tem_experiencia_educacional)
                                        <span class="badge bg-success">Sim</span>
                                    @else
                                        <span class="badge bg-warning">Não</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Disponibilidade</label>
                                <p class="mb-1">{{ $parceiro->disponibilidade_formatada }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Comissão</label>
                                <p class="mb-1">{{ $parceiro->comissao_percentual }}%</p>
                            </div>
                        </div>
                    </div>

                    @if($parceiro->plano_negocio)
                    <div class="mb-3">
                        <label class="form-label text-muted">Plano de Negócio</label>
                        <div class="bg-light p-3 rounded">
                            {{ $parceiro->plano_negocio }}
                        </div>
                    </div>
                    @endif

                    @if($parceiro->experiencia_vendas)
                    <div class="mb-3">
                        <label class="form-label text-muted">Experiência em Vendas</label>
                        <div class="bg-light p-3 rounded">
                            {{ $parceiro->experiencia_vendas }}
                        </div>
                    </div>
                    @endif

                    @if($parceiro->motivacao)
                    <div class="mb-3">
                        <label class="form-label text-muted">Motivação</label>
                        <div class="bg-light p-3 rounded">
                            {{ $parceiro->motivacao }}
                        </div>
                    </div>
                    @endif

                    @if($parceiro->observacoes)
                    <div class="mb-3">
                        <label class="form-label text-muted">Observações</label>
                        <div class="bg-light p-3 rounded">
                            {{ $parceiro->observacoes }}
                        </div>
                    </div>
                    @endif

                    <!-- Dados Bancários -->
                    @if($parceiro->banco || $parceiro->pix)
                    <hr class="my-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                        <i class="fas fa-university me-2"></i>Dados Bancários
                    </h6>
                    <div class="row">
                        @if($parceiro->banco)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Banco</label>
                                <p class="mb-1">{{ $parceiro->banco }}</p>
                            </div>
                            @if($parceiro->agencia)
                            <div class="mb-3">
                                <label class="form-label text-muted">Agência</label>
                                <p class="mb-1">{{ $parceiro->agencia }}</p>
                            </div>
                            @endif
                            @if($parceiro->conta)
                            <div class="mb-3">
                                <label class="form-label text-muted">Conta</label>
                                <p class="mb-1">{{ $parceiro->conta }}</p>
                            </div>
                            @endif
                        </div>
                        @endif
                        @if($parceiro->pix)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">PIX</label>
                                <p class="mb-1">{{ $parceiro->pix }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar com Ações -->
        <div class="col-lg-4">
            <!-- Card de Ações -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks me-2"></i>Ações
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($parceiro->status === 'pendente')
                            <form action="{{ route('admin.parceiros.aprovar', $parceiro) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Confirma a aprovação deste parceiro?')">
                                    <i class="fas fa-check me-2"></i>Aprovar
                                </button>
                            </form>
                            <form action="{{ route('admin.parceiros.rejeitar', $parceiro) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Confirma a rejeição deste parceiro?')">
                                    <i class="fas fa-times me-2"></i>Rejeitar
                                </button>
                            </form>
                        @endif

                        @if($parceiro->status === 'aprovado')
                            <form action="{{ route('admin.parceiros.ativar', $parceiro) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Confirma a ativação deste parceiro?')">
                                    <i class="fas fa-play me-2"></i>Ativar
                                </button>
                            </form>
                        @endif

                        @if($parceiro->status === 'ativo')
                            <form action="{{ route('admin.parceiros.inativar', $parceiro) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Confirma a inativação deste parceiro?')">
                                    <i class="fas fa-pause me-2"></i>Inativar
                                </button>
                            </form>
                        @endif

                        @if($parceiro->whatsapp)
                            <a href="https://wa.me/55{{ preg_replace('/\D/', '', $parceiro->whatsapp) }}" target="_blank" class="btn btn-success w-100">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </a>
                        @endif

                        <a href="{{ route('admin.parceiros.edit', $parceiro) }}" class="btn btn-warning w-100">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>

                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-2"></i>Excluir
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card de Informações -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Informações
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                                Cadastrado em
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ $parceiro->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        @if($parceiro->data_aprovacao)
                        <div class="col-12 mb-3">
                            <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                                Aprovado em
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-success">
                                {{ $parceiro->data_aprovacao->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        @endif
                        @if($parceiro->ultimo_contato)
                        <div class="col-12">
                            <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                                Último Contato
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-info">
                                {{ $parceiro->ultimo_contato->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o parceiro <strong>{{ $parceiro->nome_completo }}</strong>?</p>
                <p class="text-danger">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('admin.parceiros.destroy', $parceiro) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection