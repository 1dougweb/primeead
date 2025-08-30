@extends('layouts.admin')

@section('title', 'Pagamentos')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mt-4 mb-0">
                <i class="fas fa-credit-card me-1"></i>
                Pagamentos
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pagamentos</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.payments.dashboard') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-chart-bar me-1"></i>
                Dashboard
            </a>
            <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Novo Pagamento
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors && $errors->any())
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
            <button class="btn btn-link text-decoration-none p-0 w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosPagamentos" aria-expanded="false">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtros de Busca
                    </h5>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </button>
        </div>
        <div class="collapse" id="filtrosPagamentos">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.payments.index') }}" id="filterForm">
                    <div class="row g-3">
                        <!-- Busca geral -->
                        <div class="col-md-6">
                            <label for="search" class="form-label">Busca geral</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Nome do aluno, CPF...">
                        </div>
                        
                        <!-- Status -->
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos os status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                    🟡 Pendente
                                </option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>
                                    🔄 Processando
                                </option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>
                                    🟢 Pago
                                </option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>
                                    🔴 Falhou
                                </option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                    ⚫ Cancelado
                                </option>
                            </select>
                        </div>

                        <!-- Gateway de Pagamento -->
                        <div class="col-md-3">
                            <label for="gateway" class="form-label">Gateway</label>
                            <select class="form-select" id="gateway" name="gateway">
                                <option value="">Todos os Gateways</option>
                                <option value="mercado_pago" {{ request('gateway') == 'mercado_pago' ? 'selected' : '' }}>
                                    Mercado Pago
                                </option>
                                <option value="asas" {{ request('gateway') == 'asas' ? 'selected' : '' }}>
                                    Banco Asas
                                </option>
                                <option value="infiny_pay" {{ request('gateway') == 'infiny_pay' ? 'selected' : '' }}>
                                    Infiny Pay
                                </option>
                                <option value="cora" {{ request('gateway') == 'cora' ? 'selected' : '' }}>
                                    Banco Cora
                                </option>
                            </select>
                        </div>

                        <!-- Forma de Pagamento -->
                        <div class="col-md-3">
                            <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                            <select class="form-select" id="forma_pagamento" name="forma_pagamento">
                                <option value="">Todas</option>
                                <option value="pix" {{ request('forma_pagamento') == 'pix' ? 'selected' : '' }}>
                                    PIX
                                </option>
                                <option value="cartao_credito" {{ request('forma_pagamento') == 'cartao_credito' ? 'selected' : '' }}>
                                    Cartão de Crédito
                                </option>
                                <option value="boleto" {{ request('forma_pagamento') == 'boleto' ? 'selected' : '' }}>
                                    Boleto
                                </option>
                                <option value="manual" {{ request('forma_pagamento') == 'manual' ? 'selected' : '' }}>
                                    Pagamento Manual
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

                        <!-- Vencidos -->
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="overdue" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }}>
                                <label class="form-check-label" for="overdue">
                                    Apenas vencidos
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>
                            Filtrar
                        </button>
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
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
                        Lista de Pagamentos
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="text-muted">
                        Total: {{ $payments->total() }} pagamentos
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Aluno</th>
                            <th>Valor</th>
                            <th>Gateway</th>
                            <th>Forma</th>
                            <th>Status</th>
                            <th>Vencimento</th>
                            <th>Parcela</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>
                                    <small class="text-muted">#{{ $payment->id }}</small>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $payment->matricula->nome_completo }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $payment->matricula->cpf }}</small>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $matricula = $payment->matricula;
                                        $gateway = $matricula->payment_gateway ?? 'mercado_pago';
                                        $valorExibir = $payment->valor;
                                        
                                        // Para outros bancos, priorizar valor_pago se disponível
                                        if ($gateway !== 'mercado_pago') {
                                            if ($matricula->valor_pago && $matricula->valor_pago > 0) {
                                                $valorExibir = $matricula->valor_pago;
                                            } elseif ($matricula->valor_total_curso && $matricula->valor_total_curso > 0) {
                                                $valorExibir = $matricula->valor_total_curso;
                                            }
                                        }
                                    @endphp
                                    <strong>R$ {{ number_format($valorExibir, 2, ',', '.') }}</strong>
                                </td>
                                <td>
                                    @php
                                        $gateway = $payment->matricula->payment_gateway ?? 'mercado_pago';
                                    @endphp
                                    @switch($gateway)
                                        @case('mercado_pago')
                                            <span class="badge bg-primary">Mercado Pago</span>
                                            @break
                                        @case('asas')
                                            <span class="badge bg-info">Banco Asas</span>
                                            @break
                                        @case('infiny_pay')
                                            <span class="badge bg-warning">Infiny Pay</span>
                                            @break
                                        @case('cora')
                                            <span class="badge bg-success">Banco Cora</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $gateway }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @php
                                        $gateway = $payment->matricula->payment_gateway ?? 'mercado_pago';
                                    @endphp
                                    @if($gateway === 'mercado_pago')
                                        @switch($payment->forma_pagamento)
                                            @case('pix')
                                                <span class="badge bg-info">PIX</span>
                                                @break
                                            @case('cartao_credito')
                                                <span class="badge bg-primary">Cartão</span>
                                                @break
                                            @case('boleto')
                                                <span class="badge bg-secondary">Boleto</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ $payment->forma_pagamento }}</span>
                                        @endswitch
                                    @else
                                        <span class="badge bg-secondary">Pagamento Manual</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($payment->status)
                                        @case('pending')
                                            <span class="badge bg-warning">🟡 Pendente</span>
                                            @break
                                        @case('processing')
                                            <span class="badge bg-info">🔄 Processando</span>
                                            @break
                                        @case('paid')
                                            <span class="badge bg-success">🟢 Pago</span>
                                            @break
                                        @case('failed')
                                            <span class="badge bg-danger">🔴 Falhou</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-dark">⚫ Cancelado</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ $payment->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div>
                                        {{ $payment->data_vencimento->format('d/m/Y') }}
                                        @if($payment->data_vencimento->isPast() && in_array($payment->status, ['pending', 'processing']))
                                            <br><small class="text-danger">Vencido</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($payment->total_parcelas > 1)
                                        <small>{{ $payment->numero_parcela }}/{{ $payment->total_parcelas }}</small>
                                    @else
                                        <small class="text-muted">À vista</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.payments.show', $payment) }}" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($payment->hasBoleto())
                                            <a href="{{ route('admin.payments.download-boleto', $payment) }}" 
                                               class="btn btn-outline-success btn-sm" 
                                               title="Download Boleto">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                        
                                        <a href="{{ route('admin.payments.edit', $payment) }}" 
                                           class="btn btn-outline-warning btn-sm" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" 
                                              action="{{ route('admin.payments.destroy', $payment) }}" 
                                              class="d-inline"
                                              onsubmit="return confirm('Tem certeza que deseja excluir este pagamento?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-outline-danger btn-sm" 
                                                    title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-credit-card fa-2x mb-3"></i>
                                        <p>Nenhum pagamento encontrado.</p>
                                        <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>
                                            Criar Primeiro Pagamento
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
            <div class="card-footer">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection 