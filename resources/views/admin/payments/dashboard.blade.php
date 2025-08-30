@extends('layouts.admin')

@section('title', 'Dashboard de Pagamentos')

@section('page-title', 'Dashboard de Pagamentos')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Novo Pagamento
        </a>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-2"></i>
            Listar Pagamentos
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0">R$ {{ number_format($stats['total_amount'] ?? 0, 2, ',', '.') }}</div>
                            <div class="small">Total Arrecadado</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0">R$ {{ number_format($stats['pending_amount'] ?? 0, 2, ',', '.') }}</div>
                            <div class="small">Pendentes ({{ $stats['pending_payments'] ?? 0 }})</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0">R$ {{ number_format($stats['overdue_amount'] ?? 0, 2, ',', '.') }}</div>
                            <div class="small">Em Atraso ({{ $stats['overdue_payments'] ?? 0 }})</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0">{{ $stats['paid_payments'] ?? 0 }}</div>
                            <div class="small">Pagamentos Realizados</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparação Mensal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Comparação Mensal
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Mês Atual:</span>
                                <span class="fw-bold text-primary">R$ {{ number_format($stats['monthly_comparison']['current'] ?? 0, 2, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Mês Anterior:</span>
                                <span class="fw-bold text-secondary">R$ {{ number_format($stats['monthly_comparison']['previous'] ?? 0, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $current = $stats['monthly_comparison']['current'] ?? 0;
                        $previous = $stats['monthly_comparison']['previous'] ?? 0;
                        $growth = $previous > 0 
                            ? (($current - $previous) / $previous) * 100
                            : 0;
                    @endphp
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Variação:</span>
                            <span class="fw-bold {{ $growth >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                                <i class="fas fa-arrow-{{ $growth >= 0 ? 'up' : 'down' }} ms-1"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagamentos Recentes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Pagamentos Recentes
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentPayments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Aluno</th>
                                        <th>Curso</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Vencimento</th>
                                        <th>Forma de Pagamento</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPayments as $payment)
                                        <tr>
                                            <td>
                                                @if($payment->matricula)
                                                <div class="fw-bold">{{ $payment->matricula->nome_completo }}</div>
                                                <small class="text-muted">{{ $payment->matricula->email }}</small>
                                                @else
                                                    <div class="fw-bold text-danger">Matrícula não encontrada</div>
                                                    <small class="text-muted">ID: {{ $payment->matricula_id }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->matricula)
                                                <div>{{ $payment->matricula->curso }}</div>
                                                <small class="text-muted">{{ $payment->matricula->modalidade }}</small>
                                                @else
                                                    <div class="text-muted">N/A</div>
                                                    <small class="text-muted">N/A</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold">{{ $payment->getFormattedAmount() }}</span>
                                                @if($payment->total_parcelas > 1)
                                                    <br><small class="text-muted">{{ $payment->getInstallmentLabel() }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $payment->getStatusColor() }}">{{ $payment->getStatusLabel() }}</span>
                                                @if($payment->isOverdue())
                                                    <br><small class="text-danger">{{ $payment->getFormattedDaysOverdue() }} em atraso</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $payment->getFormattedDueDate() }}
                                                @if($payment->isDueSoon())
                                                    <br><small class="text-warning">Vence em {{ $payment->getDaysUntilDue() }} dias</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $payment->getFormaPagamentoLabel() }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-outline-primary" title="Visualizar">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-outline-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>
                                Ver Todos os Pagamentos
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum pagamento encontrado</h5>
                            <p class="text-muted">Quando houver pagamentos, eles aparecerão aqui.</p>
                            <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Criar Primeiro Pagamento
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 