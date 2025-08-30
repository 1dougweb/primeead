@extends('layouts.admin')

@section('title', 'Boletos Armazenados')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mt-4 mb-0">
                <i class="fas fa-file-pdf me-2"></i>
                Boletos Armazenados
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Pagamentos</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Boletos</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Voltar para Pagamentos
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                    <h4 class="mb-1">{{ $totalBoletos }}</h4>
                    <small class="text-muted">Total de Boletos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h4 class="mb-1">{{ $boletosPendentes }}</h4>
                    <small class="text-muted">Pendentes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h4 class="mb-1">{{ $boletosPagos }}</h4>
                    <small class="text-muted">Pagos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                    <h4 class="mb-1">{{ $boletosVencidos }}</h4>
                    <small class="text-muted">Vencidos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Boletos -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-1"></i>
                        Lista de Boletos
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="text-muted">
                        {{ $boletos->count() }} boletos encontrados
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pagamento</th>
                            <th>Aluno</th>
                            <th>Arquivo</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boletos as $payment)
                            <tr>
                                <td>
                                    <div>
                                        <strong>#{{ $payment->id }}</strong>
                                        @if($payment->numero_parcela > 1)
                                            <br><small class="text-muted">{{ $payment->numero_parcela }}/{{ $payment->total_parcelas }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($payment->matricula)
                                        <div>
                                            <strong>{{ $payment->matricula->nome_completo }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $payment->matricula->cpf }}</small>
                                        </div>
                                    @else
                                        <div>
                                            <strong class="text-muted">Pagamento de Teste</strong>
                                            <br>
                                            <small class="text-muted">Sem matrícula associada</small>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <code class="small">{{ $payment->arquivo_boleto }}</code>
                                        @if(file_exists(public_path('storage/boletos/' . $payment->arquivo_boleto)))
                                            <br><small class="text-success">
                                                <i class="fas fa-check-circle"></i> 
                                                {{ number_format(filesize(public_path('storage/boletos/' . $payment->arquivo_boleto)) / 1024, 1) }} KB
                                            </small>
                                        @else
                                            <br><small class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Arquivo não encontrado
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong>R$ {{ number_format($payment->valor, 2, ',', '.') }}</strong>
                                </td>
                                <td>
                                    <div>
                                        {{ $payment->data_vencimento->format('d/m/Y') }}
                                        @if($payment->data_vencimento->isPast() && $payment->status === 'pending')
                                            <br><small class="text-danger">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                {{ $payment->data_vencimento->diffForHumans() }}
                                            </small>
                                        @elseif($payment->data_vencimento->isFuture())
                                            <br><small class="text-info">
                                                Vence {{ $payment->data_vencimento->diffForHumans() }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @switch($payment->status)
                                        @case('pending')
                                            <span class="badge bg-warning">🟡 Pendente</span>
                                            @break
                                        @case('paid')
                                            <span class="badge bg-success">🟢 Pago</span>
                                            @break
                                        @case('processing')
                                            <span class="badge bg-info">🔄 Processando</span>
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
                                        {{ $payment->created_at->format('d/m/Y') }}
                                        <br><small class="text-muted">{{ $payment->created_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.payments.download-boleto', $payment) }}" 
                                           class="btn btn-outline-success btn-sm" 
                                           title="Download PDF">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        
                                        <a href="{{ $payment->getBoletoDirectUrl() }}" 
                                           target="_blank"
                                           class="btn btn-outline-primary btn-sm" 
                                           title="Abrir PDF">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        
                                        <a href="{{ route('admin.payments.show', $payment) }}" 
                                           class="btn btn-outline-info btn-sm" 
                                           title="Ver Pagamento">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($payment->matricula)
                                            <a href="{{ route('admin.matriculas.show', $payment->matricula) }}" 
                                               class="btn btn-outline-secondary btn-sm" 
                                               title="Ver Matrícula">
                                                <i class="fas fa-user-graduate"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-file-pdf fa-2x mb-3"></i>
                                        <p>Nenhum boleto encontrado.</p>
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
    </div>

    @if(count($arquivosOrfaos) > 0)
        <!-- Arquivos Órfãos -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Arquivos Órfãos ({{ count($arquivosOrfaos) }})
                </h5>
                <small>Arquivos PDF que existem no diretório mas não estão vinculados a nenhum pagamento</small>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($arquivosOrfaos as $arquivo)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                    <h6 class="card-title">{{ $arquivo['name'] }}</h6>
                                    <p class="card-text small text-muted">
                                        {{ $arquivo['size'] }} KB<br>
                                        {{ $arquivo['date'] }}
                                    </p>
                                    <a href="{{ asset('boletos/' . $arquivo['name']) }}" 
                                       target="_blank" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-external-link-alt me-1"></i>
                                        Abrir
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection 