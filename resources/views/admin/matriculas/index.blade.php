@extends('layouts.admin')

@section('title', 'Matrículas')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mt-4 mb-0">
                <i class="fas fa-graduation-cap me-1"></i>
                Matrículas
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Matrículas</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.matriculas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Nova Matrícula
            </a>
            <a href="{{ route('admin.matriculas.importar') }}" class="btn btn-success">
                <i class="fas fa-upload me-1"></i>
                Importar
            </a>
            <a href="{{ route('admin.matriculas.exportar') }}" class="btn btn-info">
                <i class="fas fa-download me-1"></i>
                Exportar
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

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <button class="btn btn-link text-decoration-none p-0 w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosMatriculas" aria-expanded="false">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtros de Busca
                    </h5>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </button>
        </div>
        <div class="collapse" id="filtrosMatriculas">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.matriculas.index') }}" id="filterForm">
                    <div class="row g-3">
                        <!-- Status -->
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos os status</option>
                                <option value="pre_matricula" {{ request('status') == 'pre_matricula' ? 'selected' : '' }}>
                                    🟡 Pré-Matrícula
                                </option>
                                <option value="matricula_confirmada" {{ request('status') == 'matricula_confirmada' ? 'selected' : '' }}>
                                    🟢 Matrícula Confirmada
                                </option>
                                <option value="cancelada" {{ request('status') == 'cancelada' ? 'selected' : '' }}>
                                    🔴 Cancelada
                                </option>
                                <option value="trancada" {{ request('status') == 'trancada' ? 'selected' : '' }}>
                                    ⚫ Trancada
                                </option>
                                <option value="concluida" {{ request('status') == 'concluida' ? 'selected' : '' }}>
                                    ⭐ Concluída
                                </option>
                            </select>
                        </div>

                        <!-- Status de Pagamento -->
                        <div class="col-md-3">
                            <label for="status_pagamento" class="form-label">Status de Pagamento</label>
                            <select class="form-select" id="status_pagamento" name="status_pagamento">
                                <option value="">Todos os pagamentos</option>
                                <option value="avista" {{ request('status_pagamento') == 'avista' ? 'selected' : '' }}>
                                    💰 À Vista
                                </option>
                                <option value="pago" {{ request('status_pagamento') == 'pago' ? 'selected' : '' }}>
                                    🟢 Pago
                                </option>
                                <option value="pendente" {{ request('status_pagamento') == 'pendente' ? 'selected' : '' }}>
                                    🟡 Pendente
                                </option>
                                <option value="vencido" {{ request('status_pagamento') == 'vencido' ? 'selected' : '' }}>
                                    🔴 Vencido
                                </option>
                                <option value="sem_pagamento" {{ request('status_pagamento') == 'sem_pagamento' ? 'selected' : '' }}>
                                    ⚫ Sem pagamento
                                </option>
                            </select>
                        </div>

                        <!-- Tipo de Pagamento -->
                        <div class="col-md-3">
                            <label for="tipo_pagamento" class="form-label">Tipo de Pagamento</label>
                            <select class="form-select" id="tipo_pagamento" name="tipo_pagamento">
                                <option value="">Todos os tipos</option>
                                <option value="avista" {{ request('tipo_pagamento') == 'avista' ? 'selected' : '' }}>
                                    💰 À Vista
                                </option>
                                <option value="parcelado" {{ request('tipo_pagamento') == 'parcelado' ? 'selected' : '' }}>
                                    📅 Parcelado
                                </option>
                            </select>
                        </div>

                        <!-- Forma de Pagamento -->
                        <div class="col-md-3">
                            <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                            <select class="form-select" id="forma_pagamento" name="forma_pagamento">
                                <option value="">Todas as formas</option>
                                <option value="cartao_credito" {{ request('forma_pagamento') == 'cartao_credito' ? 'selected' : '' }}>
                                    💳 Cartão de Crédito
                                </option>
                                <option value="pix" {{ request('forma_pagamento') == 'pix' ? 'selected' : '' }}>
                                    📱 PIX
                                </option>
                                <option value="boleto" {{ request('forma_pagamento') == 'boleto' ? 'selected' : '' }}>
                                    🧾 Boleto
                                </option>
                            </select>
                        </div>

                        <!-- Modalidade -->
                        <div class="col-md-3">
                            <label for="modalidade" class="form-label">Modalidade</label>
                            <select class="form-select" id="modalidade" name="modalidade">
                                <option value="">Todas as modalidades</option>
                                @foreach($formSettings['available_modalities'] ?? [] as $value => $label)
                                    <option value="{{ $value }}" {{ request('modalidade') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Escola Parceira -->
                        <div class="col-md-3">
                            <label for="escola_parceira" class="form-label">Escola Parceira</label>
                            <select class="form-select" id="escola_parceira" name="escola_parceira">
                                <option value="">Todas</option>
                                <option value="1" {{ request('escola_parceira') === '1' ? 'selected' : '' }}>
                                    🏫 Sim (Vem de Parceiro)
                                </option>
                                <option value="0" {{ request('escola_parceira') === '0' ? 'selected' : '' }}>
                                    🎓 Não (Matrícula Direta)
                                </option>
                            </select>
                        </div>

                        <!-- Data início -->
                        <div class="col-md-3">
                            <label for="data_inicio" class="form-label">Data Início</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_inicio" 
                                   name="data_inicio" 
                                   value="{{ request('data_inicio') }}">
                        </div>
                        
                        <!-- Data fim -->
                        <div class="col-md-3">
                            <label for="data_fim" class="form-label">Data Fim</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_fim" 
                                   name="data_fim" 
                                   value="{{ request('data_fim') }}">
                        </div>

                        <!-- Busca -->
                        <div class="col-md-3">
                            <label for="busca" class="form-label">Buscar</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="busca" 
                                   name="busca" 
                                   value="{{ request('busca') }}"
                                   placeholder="Nome, CPF, email ou matrícula">
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>
                            Filtrar
                        </button>
                        <a href="{{ route('admin.matriculas.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-1"></i>
                        Lista de Matrículas
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="text-muted">
                        Total: {{ $matriculas->total() }} matrículas
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Matrícula</th>
                            <th>Nome</th>
                            <th>Modalidade</th>
                            <th>Pagamentos</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriculas as $matricula)
                            <tr class="{{ $loop->even ? 'table-light' : '' }}">
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $matricula->numero_matricula }}
                                    </span>
                                </td>
                                <td class="fw-medium">{{ $matricula->nome_completo }}</td>
                                <td>{{ $matricula->modalidade_formatada }}</td>
                                <td>
                                    @php
                                        $totalParcelas = $matricula->numero_parcelas ?? 0;
                                        $parcelasPagas = $matricula->payments->where('status', 'paid')->count();
                                        $parcelasPendentes = $matricula->payments->where('status', 'pending')->count();
                                        $parcelasVencidas = $matricula->payments->where('status', 'pending')
                                            ->where('data_vencimento', '<', now())->count();
                                    @endphp
                                    
                                    <div class="d-flex flex-column">
                                        <!-- Linha Superior: Forma de Pagamento (Etiquetas Pretas) -->
                                        <div class="mb-1">
                                            @php
                                                $gateway = $matricula->payment_gateway ?? 'mercado_pago';
                                                // Verificar se há pagamento de matrícula (entrada)
                                                $pagamentoMatricula = $matricula->payments->where('numero_parcela', 0)->first();
                                                $matriculaHasEntrada = $matricula->valor_matricula > 0;
                                            @endphp
                                            
                                            @if($gateway === 'mercado_pago')
                                                {{-- Cartão de crédito sempre é À Vista (parcelamento no checkout MP) --}}
                                                @if($matricula->forma_pagamento === 'cartao_credito')
                                                    <span class="badge bg-dark payment-type-badge">À Vista/Cartão</span>
                                                
                                                {{-- PIX sempre é À Vista --}}
                                                @elseif($matricula->forma_pagamento === 'pix')
                                                    <span class="badge bg-dark payment-type-badge">À Vista/PIX</span>
                                                
                                                {{-- Para boleto: verificar se há matrícula pendente --}}
                                                @elseif($matriculaHasEntrada && $pagamentoMatricula && $pagamentoMatricula->status !== 'paid')
                                                    <span class="badge bg-dark payment-type-badge">Matrícula</span>
                                                
                                                {{-- Boleto: mostrar parcelas se matrícula paga ou não há matrícula --}}
                                                @elseif($matricula->tipo_boleto === 'avista')
                                                    <span class="badge bg-dark payment-type-badge">À Vista</span>
                                                @elseif($totalParcelas == 1)
                                                    <span class="badge bg-dark payment-type-badge">À Vista</span>
                                                @elseif($totalParcelas > 1)
                                                    <span class="badge bg-dark payment-type-badge">{{ $parcelasPagas }}/{{ $totalParcelas }} Parcelas</span>
                                                @elseif($matricula->payments->count() == 1)
                                                    <span class="badge bg-dark payment-type-badge">À Vista</span>
                                                @elseif($matricula->payments->count() > 1)
                                                    <span class="badge bg-dark payment-type-badge">{{ $parcelasPagas }}/{{ $matricula->payments->count() }} Pagamentos</span>
                                                @else
                                                    <span class="badge bg-dark payment-type-badge">Sem Pagamento</span>
                                                @endif
                                            @else
                                                {{-- Lógica para outros bancos --}}
                                                @if($matriculaHasEntrada && $pagamentoMatricula && $pagamentoMatricula->status !== 'paid')
                                                    <span class="badge bg-dark payment-type-badge">Matrícula</span>
                                                @elseif($totalParcelas == 1 || $matricula->payments->count() <= 1)
                                                    <span class="badge bg-dark payment-type-badge">À Vista/Manual</span>
                                                @elseif($totalParcelas > 1)
                                                    <span class="badge bg-dark payment-type-badge">{{ $parcelasPagas }}/{{ $totalParcelas }} Parcelas</span>
                                                @elseif($matricula->payments->count() > 1)
                                                    <span class="badge bg-dark payment-type-badge">{{ $parcelasPagas }}/{{ $matricula->payments->count() }} Pagamentos</span>
                                                @else
                                                    <span class="badge bg-dark payment-type-badge">Sem Pagamento</span>
                                                @endif
                                            @endif
                                        </div>
                                        
                                        <!-- Linha Inferior: Status (Cores de Status) -->
                                        <div>
                                            @php
                                                // Verificar se há pagamento de matrícula (entrada)
                                                $pagamentoMatricula = $matricula->payments->where('numero_parcela', 0)->first();
                                                $mensalidades = $matricula->payments->where('numero_parcela', '>', 0);
                                                $hasBoletosPendentes = $matricula->payments->where('status', 'pending')
                                                    ->where('forma_pagamento', 'boleto')->count() > 0;
                                            @endphp
                                            
                                            @if($gateway === 'mercado_pago')
                                                {{-- Status para Mercado Pago --}}
                                                
                                                {{-- Cartão de crédito e PIX: sempre pagamento único --}}
                                                @if($matricula->forma_pagamento === 'cartao_credito' || $matricula->forma_pagamento === 'pix')
                                                    @if($matricula->payments->where('status', 'paid')->count() > 0)
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    @else
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    @endif
                                                
                                                {{-- Se há pagamento de matrícula (entrada) para boleto --}}
                                                @elseif($pagamentoMatricula)
                                                    @if($pagamentoMatricula->status === 'paid')
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    @elseif($pagamentoMatricula->forma_pagamento === 'boleto')
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    @else
                                                        <span class="badge bg-info payment-status-badge">Pendente</span>
                                                    @endif
                                                
                                                {{-- Pagamento único boleto (à vista) --}}
                                                @elseif($matricula->tipo_boleto === 'avista' || $totalParcelas == 1)
                                                    @if($matricula->payments->where('status', 'paid')->count() > 0)
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    @elseif($hasBoletosPendentes)
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    @else
                                                        <span class="badge bg-info payment-status-badge">Pendente</span>
                                                    @endif
                                                
                                                {{-- Pagamentos parcelados --}}
                                                @elseif($totalParcelas > 1)
                                                    @if($parcelasPagas == $totalParcelas)
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    @elseif($parcelasVencidas > 0)
                                                        <span class="badge bg-danger payment-status-badge">{{ $parcelasVencidas }} Vencida{{ $parcelasVencidas > 1 ? 's' : '' }}</span>
                                                    @elseif($parcelasPendentes > 0 && $hasBoletosPendentes)
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    @elseif($parcelasPendentes > 0)
                                                        <span class="badge bg-info payment-status-badge">{{ $parcelasPendentes }} Pendente{{ $parcelasPendentes > 1 ? 's' : '' }}</span>
                                                    @else
                                                        <span class="badge bg-secondary payment-status-badge">Aguardando</span>
                                                    @endif
                                                
                                                {{-- Outros casos --}}
                                                @elseif($matricula->payments->count() > 0)
                                                    @php
                                                        $parcelasPagas = $matricula->payments->where('status', 'paid')->count();
                                                        $totalPagamentos = $matricula->payments->count();
                                                    @endphp
                                                    @if($parcelasPagas == $totalPagamentos)
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    @elseif($hasBoletosPendentes)
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    @else
                                                        <span class="badge bg-info payment-status-badge">{{ $totalPagamentos - $parcelasPagas }} Pendente{{ ($totalPagamentos - $parcelasPagas) > 1 ? 's' : '' }}</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary payment-status-badge">Sem pagamentos</span>
                                                @endif
                                            @else
                                                {{-- Status para outros bancos --}}
                                                @if($pagamentoMatricula)
                                                    @if($pagamentoMatricula->status === 'paid')
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    @else
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    @endif
                                                @elseif($matricula->payments->count() > 0)
                                                    @php
                                                        $parcelasPagas = $matricula->payments->where('status', 'paid')->count();
                                                        $totalPagamentos = $matricula->payments->count();
                                                    @endphp
                                                    @if($parcelasPagas == $totalPagamentos)
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    @elseif($hasBoletosPendentes)
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    @elseif($parcelasPagas > 0)
                                                        <span class="badge bg-info payment-status-badge">{{ $totalPagamentos - $parcelasPagas }} Pendente{{ ($totalPagamentos - $parcelasPagas) > 1 ? 's' : '' }}</span>
                                                    @else
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary payment-status-badge">Sem pagamentos</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $matricula->status_formatado }}</td>
                                <td>{{ $matricula->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.matriculas.show', $matricula) }}" 
                                           class="btn btn-outline-secondary" 
                                           title="Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.matriculas.edit', $matricula) }}" 
                                           class="btn btn-outline-secondary" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Excluir"
                                                onclick="confirmarExclusao('{{ $matricula->id }}', '{{ $matricula->nome_completo }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                    Nenhuma matrícula encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($matriculas->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $matriculas->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade centered-modal" id="modalExclusao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a matrícula do aluno <strong id="nomeAluno"></strong>?</p>
                <p class="text-danger mb-0">Esta ação não poderá ser desfeita!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formExclusao" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit ao mudar os selects
    const selects = document.querySelectorAll('#filterForm select');
    selects.forEach(select => {
        select.addEventListener('change', () => {
            document.getElementById('filterForm').submit();
        });
    });

    // Função para confirmar exclusão
    window.confirmarExclusao = function(id, nome) {
        document.getElementById('nomeAluno').textContent = nome;
        document.getElementById('formExclusao').action = `/dashboard/matriculas/${id}`;
        const modal = new bootstrap.Modal(document.getElementById('modalExclusao'));
        modal.show();
    };
});
</script>

<style>
/* Estilos para linhas alternadas da tabela */
.table tbody tr:nth-child(even) {
    background-color: #f8f9fa !important;
}

.table tbody tr:nth-child(odd) {
    background-color: #ffffff !important;
}

.table tbody tr:hover {
    background-color: #e9ecef !important;
    transition: background-color 0.15s ease-in-out;
}

/* Melhorar contraste das bordas */
.table tbody tr {
    border-bottom: 1px solid #dee2e6;
}

.table tbody tr:last-child {
    border-bottom: none;
}

/* Estilos para as etiquetas de pagamento */
.payment-status-badge {
    min-width: 120px;
    text-align: center;
    display: inline-block;
}

.payment-type-badge {
    min-width: 120px;
    text-align: center;
    display: inline-block;
}

/* Responsividade para as etiquetas de pagamento */
@media (max-width: 768px) {
    .payment-status-badge,
    .payment-type-badge {
        min-width: 100px;
        font-size: 0.75rem;
    }
}

@media (max-width: 576px) {
    .payment-status-badge,
    .payment-type-badge {
        min-width: 80px;
        font-size: 0.7rem;
    }
}
</style>
@endpush

@endsection 