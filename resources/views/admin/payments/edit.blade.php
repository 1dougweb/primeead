@extends('layouts.admin')

@section('title', 'Editar Pagamento')

@section('content')
<div class="container-fluid px-4">
    <h3 class="mt-4">
        <i class="fas fa-edit me-2"></i>
        Editar Pagamento #{{ $payment->id }}
    </h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Pagamentos</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.payments.show', $payment) }}">Pagamento #{{ $payment->id }}</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors && $errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.payments.update', $payment) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Dados do Pagamento -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Dados do Pagamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="matricula_id" class="form-label">Matrícula</label>
                                <select class="form-select @error('matricula_id') is-invalid @enderror" 
                                        id="matricula_id" 
                                        name="matricula_id" 
                                        disabled>
                                    <option value="{{ $payment->matricula->id }}" selected>
                                        {{ $payment->matricula->nome_completo }} - {{ $payment->matricula->cpf }}
                                    </option>
                                </select>
                                <input type="hidden" name="matricula_id" value="{{ $payment->matricula->id }}">
                                <small class="form-text text-muted">
                                    A matrícula não pode ser alterada após a criação do pagamento.
                                </small>
                                @error('matricula_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="valor" class="form-label">Valor</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    @php
                                        $gateway = $payment->matricula->payment_gateway ?? 'mercado_pago';
                                        $valorPadrao = $payment->valor;
                                        
                                        // Para outros bancos, usar valores da matrícula
                                        if ($gateway !== 'mercado_pago') {
                                            if ($payment->matricula->valor_pago && $payment->matricula->numero_parcelas > 1) {
                                                $valorPadrao = $payment->matricula->valor_pago / $payment->matricula->numero_parcelas;
                                            } elseif ($payment->matricula->valor_pago) {
                                                $valorPadrao = $payment->matricula->valor_pago;
                                            } elseif ($payment->matricula->numero_parcelas > 1) {
                                                $valorPadrao = $payment->matricula->valor_total_curso / $payment->matricula->numero_parcelas;
                                            } else {
                                                $valorPadrao = $payment->matricula->valor_total_curso;
                                            }
                                        }
                                    @endphp
                                    <input type="number" 
                                           class="form-control @error('valor') is-invalid @enderror" 
                                           id="valor" 
                                           name="valor" 
                                           value="{{ old('valor', $valorPadrao) }}" 
                                           step="0.01"
                                           min="0.01"
                                           required>
                                    @error('valor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if($payment->isOverdue() && $payment->status === 'pending')
                                <div class="alert alert-warning mt-2 p-2 small">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Pagamento vencido há {{ $payment->getFormattedDaysOverdue() }}.<br>
                                    Valor com juros: <strong>{{ $payment->getFormattedValorAtualizado() }}</strong><br>
                                    <small>(+ {{ $payment->getFormattedValorJurosMora() }} de juros)</small>
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-sm btn-warning" onclick="aplicarJuros()">
                                            Aplicar Juros
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="col-md-3">
                                <label for="payment_gateway" class="form-label">Gateway de Pagamento</label>
                                <div class="form-control-plaintext">
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
                                </div>
                                <small class="form-text text-muted">
                                    Gateway definido na matrícula
                                </small>
                            </div>

                            <div class="col-md-3">
                                <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                                @if(($payment->matricula->payment_gateway ?? 'mercado_pago') === 'mercado_pago')
                                    <select class="form-select @error('forma_pagamento') is-invalid @enderror" 
                                            id="forma_pagamento" 
                                            name="forma_pagamento" 
                                            required>
                                        <option value="">Selecione</option>
                                        <option value="pix" {{ old('forma_pagamento', $payment->forma_pagamento) == 'pix' ? 'selected' : '' }}>PIX</option>
                                        <option value="cartao_credito" {{ old('forma_pagamento', $payment->forma_pagamento) == 'cartao_credito' ? 'selected' : '' }}>Cartão de Crédito</option>
                                        <option value="boleto" {{ old('forma_pagamento', $payment->forma_pagamento) == 'boleto' ? 'selected' : '' }}>Boleto</option>
                                    </select>
                                @else
                                    <div class="form-control-plaintext">
                                        <span class="badge bg-secondary">Pagamento Manual</span>
                                    </div>
                                    <input type="hidden" name="forma_pagamento" value="{{ $payment->forma_pagamento ?? 'boleto' }}">
                                @endif
                                @error('forma_pagamento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="data_vencimento" class="form-label">Data de Vencimento</label>
                                <input type="date" 
                                       class="form-control @error('data_vencimento') is-invalid @enderror" 
                                       id="data_vencimento" 
                                       name="data_vencimento" 
                                       value="{{ old('data_vencimento', $payment->data_vencimento->format('Y-m-d')) }}" 
                                       required>
                                @error('data_vencimento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                @php
                                    $isManualGateway = $payment->matricula && 
                                        in_array($payment->matricula->payment_gateway ?? 'mercado_pago', ['asas', 'infiny_pay', 'cora']);
                                @endphp
                                
                                @if($isManualGateway)
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="pending" {{ old('status', $payment->status) == 'pending' ? 'selected' : '' }}>🟡 Pendente</option>
                                        <option value="processing" {{ old('status', $payment->status) == 'processing' ? 'selected' : '' }}>🔵 Processando</option>
                                        <option value="paid" {{ old('status', $payment->status) == 'paid' ? 'selected' : '' }}>🟢 Pago</option>
                                        <option value="failed" {{ old('status', $payment->status) == 'failed' ? 'selected' : '' }}>🔴 Falhou</option>
                                        <option value="cancelled" {{ old('status', $payment->status) == 'cancelled' ? 'selected' : '' }}>⚫ Cancelado</option>
                                    </select>
                                @else
                                    <input type="hidden" name="status" value="{{ $payment->status }}">
                                    <div class="form-control bg-light">
                                        @switch($payment->status)
                                            @case('pending')
                                                🟡 Pendente
                                                @break
                                            @case('processing')
                                                🔵 Processando
                                                @break
                                            @case('paid')
                                                🟢 Pago
                                                @break
                                            @case('failed')
                                                🔴 Falhou
                                                @break
                                            @case('cancelled')
                                                ⚫ Cancelado
                                                @break
                                            @default
                                                {{ $payment->status }}
                                        @endswitch
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-robot me-1"></i>
                                        Status controlado automaticamente pelo Mercado Pago via webhook.
                                    </small>
                                @endif
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações de Parcelamento (oculto para cartão de crédito) -->
            <div class="col-md-12 mb-4" id="parcelamento-section" style="display: {{ old('forma_pagamento', $payment->forma_pagamento) === 'cartao_credito' ? 'none' : 'block' }};">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Informações de Parcelamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="numero_parcela" class="form-label">Número da Parcela</label>
                                <input type="number" 
                                       class="form-control @error('numero_parcela') is-invalid @enderror" 
                                       id="numero_parcela" 
                                       name="numero_parcela" 
                                       value="{{ old('numero_parcela', $payment->numero_parcela) }}" 
                                       min="1"
                                       readonly>
                                @error('numero_parcela')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Número da parcela não pode ser alterado após a criação.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label for="total_parcelas" class="form-label">Total de Parcelas</label>
                                <input type="number" 
                                       class="form-control @error('total_parcelas') is-invalid @enderror" 
                                       id="total_parcelas" 
                                       name="total_parcelas" 
                                       value="{{ old('total_parcelas', $payment->total_parcelas) }}" 
                                       min="1"
                                       readonly>
                                @error('total_parcelas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Total de parcelas não pode ser alterado após a criação.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações do Gateway -->
            @if(($payment->matricula->payment_gateway ?? 'mercado_pago') !== 'mercado_pago')
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bank me-2"></i>
                                Informações do {{ 
                                    $payment->matricula->payment_gateway === 'asas' ? 'Banco Asas' : 
                                    ($payment->matricula->payment_gateway === 'infiny_pay' ? 'Infiny Pay' : 
                                    ($payment->matricula->payment_gateway === 'cora' ? 'Banco Cora' : 'Banco'))
                                }}
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($payment->matricula->bank_info)
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Informações:</strong> {{ $payment->matricula->bank_info }}
                                </div>
                            @endif
                            
                            @if($payment->matricula->valor_pago)
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Valor Pago:</strong></p>
                                        <p class="h5 text-success">R$ {{ number_format($payment->matricula->valor_pago, 2, ',', '.') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Valor Total:</strong></p>
                                        <p class="h5">R$ {{ number_format($payment->matricula->valor_total_curso, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Informações do Mercado Pago (apenas para gateway Mercado Pago) -->
            @if($payment->mercadopago_id && ($payment->matricula->payment_gateway ?? 'mercado_pago') === 'mercado_pago')
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>
                                Informações do Mercado Pago
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">ID Mercado Pago</label>
                                    <p class="mb-0"><code>{{ $payment->mercadopago_id }}</code></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Status Mercado Pago</label>
                                    <p class="mb-0">
                                        <span class="badge bg-light text-dark">{{ $payment->mercadopago_status ?? 'N/A' }}</span>
                                    </p>
                                </div>
                                @if($payment->mercadopago_updated_at)
                                    <div class="col-md-12">
                                        <label class="form-label text-muted">Última atualização</label>
                                        <p class="mb-0">{{ $payment->mercadopago_updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>
                Salvar Alterações
            </button>
            <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-outline-secondary">
                <i class="fas fa-times me-2"></i>
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Controlar visibilidade da seção de parcelamento
    const formaPagamentoSelect = document.getElementById('forma_pagamento');
    const parcelamentoSection = document.getElementById('parcelamento-section');
    
    function toggleParcelamento() {
        const formaPagamento = formaPagamentoSelect.value;
        if (formaPagamento === 'cartao_credito') {
            parcelamentoSection.style.display = 'none';
        } else {
            parcelamentoSection.style.display = 'block';
        }
    }
    
    // Aplicar lógica no carregamento e quando mudar
    toggleParcelamento();
    formaPagamentoSelect.addEventListener('change', toggleParcelamento);
    
    // Atualizar data de pagamento automaticamente quando status muda para 'paid'
    const statusSelect = document.getElementById('status');
    
    statusSelect.addEventListener('change', function() {
        if (this.value === 'paid') {
            // Adicionar campo oculto para data de pagamento
            let dataPagamentoInput = document.getElementById('data_pagamento_hidden');
            if (!dataPagamentoInput) {
                dataPagamentoInput = document.createElement('input');
                dataPagamentoInput.type = 'hidden';
                dataPagamentoInput.name = 'data_pagamento';
                dataPagamentoInput.id = 'data_pagamento_hidden';
                this.form.appendChild(dataPagamentoInput);
            }
            dataPagamentoInput.value = new Date().toISOString().slice(0, 19).replace('T', ' ');
        }
    });
});

function aplicarJuros() {
    // Obter o valor atualizado com juros
    const valorAtualizado = {{ $payment->getValorAtualizado() }};
    
    // Atualizar o campo de valor
    document.getElementById('valor').value = valorAtualizado.toFixed(2);
    
    // Exibir alerta
    alert('Valor atualizado com juros aplicado: R$ ' + valorAtualizado.toFixed(2).replace('.', ','));
}
</script>
@endsection 