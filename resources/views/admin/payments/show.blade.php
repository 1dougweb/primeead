@extends('layouts.admin')

@section('title', 'Detalhes do Pagamento')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mt-4 mb-0">
                <i class="fas fa-credit-card me-2"></i>
                Detalhes do Pagamento #{{ $payment->id }}
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Pagamentos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-outline-warning me-2">
                <i class="fas fa-edit me-1"></i>
                Editar
            </a>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
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

    <div class="row">
        <!-- Informações do Pagamento -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Informações do Pagamento
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">ID do Pagamento</label>
                            <p class="mb-0"><strong>#{{ $payment->id }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <p class="mb-0">
                                @switch($payment->status)
                                    @case('pending')
                                        <span class="badge bg-warning fs-6">🟡 Pendente</span>
                                        @break
                                    @case('processing')
                                        <span class="badge bg-info fs-6">🔄 Processando</span>
                                        @break
                                    @case('paid')
                                        <span class="badge bg-success fs-6">🟢 Pago</span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-danger fs-6">🔴 Falhou</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-dark fs-6">⚫ Cancelado</span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark fs-6">{{ $payment->status }}</span>
                                @endswitch
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Valor</label>
                            @php
                                $gateway = $payment->matricula->payment_gateway ?? 'mercado_pago';
                                $valorExibir = $payment->valor;
                                
                                // Para outros bancos, usar valores da matrícula
                                if ($gateway !== 'mercado_pago') {
                                    if ($payment->matricula->valor_pago && $payment->matricula->numero_parcelas > 1) {
                                        $valorExibir = $payment->matricula->valor_pago / $payment->matricula->numero_parcelas;
                                    } elseif ($payment->matricula->valor_pago) {
                                        $valorExibir = $payment->matricula->valor_pago;
                                    } elseif ($payment->matricula->numero_parcelas > 1) {
                                        $valorExibir = $payment->matricula->valor_total_curso / $payment->matricula->numero_parcelas;
                                    } else {
                                        $valorExibir = $payment->matricula->valor_total_curso;
                                    }
                                }
                            @endphp
                            <p class="mb-0"><strong class="text-success">R$ {{ number_format($valorExibir, 2, ',', '.') }}</strong></p>
                        </div>
                        @if($payment->isOverdue() && $payment->status === 'pending')
                        <div class="col-md-6">
                            <label class="form-label text-muted">Valor Atualizado (com juros)</label>
                            <p class="mb-0">
                                <strong class="text-danger">{{ $payment->getFormattedValorAtualizado() }}</strong>
                                <small class="text-muted ms-2">(+ {{ $payment->getFormattedValorJurosMora() }} de juros)</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Dias em Atraso</label>
                            <p class="mb-0"><span class="badge bg-danger">{{ $payment->getFormattedDaysOverdue() }}</span></p>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label text-muted">Gateway de Pagamento</label>
                            <p class="mb-0">
                                @switch($payment->matricula->payment_gateway ?? 'mercado_pago')
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
                                        <span class="badge bg-secondary">{{ $payment->matricula->payment_gateway }}</span>
                                @endswitch
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Forma de Pagamento</label>
                            <p class="mb-0">
                                @if(($payment->matricula->payment_gateway ?? 'mercado_pago') === 'mercado_pago')
                                    @switch($payment->forma_pagamento)
                                        @case('pix')
                                            <span class="badge bg-info">PIX</span>
                                            @break
                                        @case('cartao_credito')
                                            <span class="badge bg-primary">Cartão de Crédito</span>
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
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Data de Vencimento</label>
                            <p class="mb-0">
                                {{ $payment->data_vencimento->format('d/m/Y') }}
                                @if($payment->data_vencimento->isPast() && in_array($payment->status, ['pending', 'processing']))
                                    <span class="badge bg-danger ms-2">Vencido</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Data de Pagamento</label>
                            <p class="mb-0">
                                @if($payment->data_pagamento)
                                    {{ $payment->data_pagamento->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">Não pago</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Parcela</label>
                            <p class="mb-0">
                                @if($payment->total_parcelas > 1)
                                    {{ $payment->numero_parcela }}/{{ $payment->total_parcelas }}
                                @else
                                    À vista
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Criado em</label>
                            <p class="mb-0">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações da Matrícula -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        Informações da Matrícula
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($payment->matricula)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Nome Completo</label>
                            <p class="mb-0"><strong>{{ $payment->matricula->nome_completo }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">CPF</label>
                            <p class="mb-0">{{ $payment->matricula->cpf }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">E-mail</label>
                            <p class="mb-0">{{ $payment->matricula->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Telefone</label>
                                <p class="mb-0">{{ $payment->matricula->telefone_celular ?? 'Não informado' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Modalidade</label>
                            <p class="mb-0">{{ $payment->matricula->modalidade ?? 'Não informado' }}</p>
                        </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Curso</label>
                                <p class="mb-0">{{ $payment->matricula->curso ?? 'Não informado' }}</p>
                            </div>
                        @else
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Matrícula não encontrada</strong><br>
                                    Este pagamento está vinculado à matrícula ID: {{ $payment->matricula_id }}, mas ela não foi encontrada no sistema.
                                    Isso pode ter ocorrido se a matrícula foi excluída.
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status da Matrícula</label>
                            <p class="mb-0">
                                @switch($payment->matricula->status)
                                    @case('pre_matricula')
                                        <span class="badge bg-warning">🟡 Pré-Matrícula</span>
                                        @break
                                    @case('matricula_confirmada')
                                        <span class="badge bg-success">🟢 Matrícula Confirmada</span>
                                        @break
                                    @case('cancelada')
                                        <span class="badge bg-danger">🔴 Cancelada</span>
                                        @break
                                    @case('trancada')
                                        <span class="badge bg-dark">⚫ Trancada</span>
                                        @break
                                    @case('concluida')
                                        <span class="badge bg-info">⭐ Concluída</span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark">{{ $payment->matricula->status }}</span>
                                @endswitch
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel Lateral -->
        <div class="col-md-4">
            <!-- Ações Rápidas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-outline-warning">
                            <i class="fas fa-edit me-2"></i>
                            Editar Pagamento
                        </a>
                        @if($payment->status === 'pending')
                            @php
                                $isManualGateway = $payment->matricula && 
                                    in_array($payment->matricula->payment_gateway ?? 'mercado_pago', ['asas', 'infiny_pay', 'cora']);
                            @endphp
                            
                            @if($isManualGateway)
                                <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="paid">
                                    <input type="hidden" name="valor" value="{{ $payment->valor }}">
                                    <input type="hidden" name="forma_pagamento" value="{{ $payment->forma_pagamento }}">
                                    <input type="hidden" name="data_vencimento" value="{{ $payment->data_vencimento->format('Y-m-d') }}">
                                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Marcar como pago?')">
                                        <i class="fas fa-check me-2"></i>
                                        Marcar como Pago
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-robot me-2"></i>
                                    <strong>Mercado Pago</strong><br>
                                    O status será atualizado automaticamente via webhook.
                                </div>
                            @endif
                        @endif
                        @if($payment->matricula)
                        <a href="{{ route('admin.matriculas.show', $payment->matricula) }}" class="btn btn-outline-info">
                            <i class="fas fa-user-graduate me-2"></i>
                            Ver Matrícula
                        </a>
                        @else
                            <button class="btn btn-outline-secondary" disabled>
                                <i class="fas fa-user-graduate me-2"></i>
                                Matrícula não encontrada
                            </button>
                        @endif
                        
                        @if($payment->hasBoleto())
                            <a href="{{ route('admin.payments.download-boleto', $payment) }}" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-download me-2"></i>
                                Download Boleto
                            </a>
                        @endif
                        
                        @if($payment->hasPixCode())
                            <button type="button" class="btn btn-outline-info w-100 mb-2" onclick="showPixCode('{{ $payment->codigo_pix }}')">
                                <i class="fas fa-qrcode me-2"></i>
                                Ver Código PIX
                            </button>
                        @endif
                        
                        <form method="POST" action="{{ route('admin.payments.resend-notifications', $payment) }}" class="d-inline mt-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100" onclick="return confirm('Reenviar notificações de pagamento?')">
                                <i class="fas fa-paper-plane me-2"></i>
                                Reenviar Notificações
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Informações do Gateway -->
            @if(($payment->matricula->payment_gateway ?? 'mercado_pago') !== 'mercado_pago')
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bank me-2"></i>
                            {{ 
                                $payment->matricula->payment_gateway === 'asas' ? 'Banco Asas' : 
                                ($payment->matricula->payment_gateway === 'infiny_pay' ? 'Infiny Pay' : 
                                ($payment->matricula->payment_gateway === 'cora' ? 'Banco Cora' : 'Banco'))
                            }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($payment->matricula->bank_info)
                            <div class="mb-3">
                                <label class="form-label text-muted">Informações do Banco</label>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ $payment->matricula->bank_info }}
                                </div>
                            </div>
                        @endif
                        
                        @if($payment->matricula->valor_pago)
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Valor Pago</label>
                                    <p class="mb-0"><strong class="text-success">R$ {{ number_format($payment->matricula->valor_pago, 2, ',', '.') }}</strong></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Valor Total</label>
                                    <p class="mb-0"><strong>R$ {{ number_format($payment->matricula->valor_total_curso, 2, ',', '.') }}</strong></p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Informações do Mercado Pago (apenas para gateway Mercado Pago) -->
            @if($payment->mercadopago_id && ($payment->matricula->payment_gateway ?? 'mercado_pago') === 'mercado_pago')
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Mercado Pago
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">ID Mercado Pago</label>
                            <p class="mb-0"><code>{{ $payment->mercadopago_id }}</code></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Status Mercado Pago</label>
                            <p class="mb-0">
                                <span class="badge bg-light text-dark">{{ $payment->mercadopago_status ?? 'N/A' }}</span>
                            </p>
                        </div>
                        @if($payment->mercadopago_updated_at)
                            <div class="mb-0">
                                <label class="form-label text-muted">Última atualização</label>
                                <p class="mb-0">{{ $payment->mercadopago_updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function showPixCode(pixCode) {
        document.getElementById('pixCodeContent').textContent = pixCode;
        var modal = new bootstrap.Modal(document.getElementById('pixCodeModal'));
        modal.show();
    }

    function copyPixCode() {
        var pixCode = document.getElementById('pixCodeContent').textContent;
        navigator.clipboard.writeText(pixCode).then(function() {
            var btn = document.getElementById('copyPixBtn');
            var originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            
            setTimeout(function() {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }, 2000);
        });
    }
</script>

<!-- Modal PIX -->
<div class="modal fade" id="pixCodeModal" tabindex="-1" aria-labelledby="pixCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pixCodeModalLabel">
                    <i class="fas fa-qrcode me-2"></i>
                    Código PIX
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Copie o código PIX abaixo para realizar o pagamento:</p>
                <div class="bg-light p-3 rounded border">
                    <code id="pixCodeContent" class="text-break"></code>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="copyPixBtn" onclick="copyPixCode()">
                    <i class="fas fa-copy me-1"></i>
                    Copiar Código
                </button>
            </div>
        </div>
    </div>
</div>

@endsection 