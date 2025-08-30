@extends('layouts.admin')

@section('title', 'Detalhes da Campanha')

@section('page-title', 'Campanha: ' . $campaign->name)

@section('page-actions')
    <div class="d-flex gap-2">
        @if($campaign->canEdit())
            <a href="{{ route('admin.email-campaigns.edit', $campaign->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>
                Editar Campanha
            </a>
        @endif
        
        <a href="{{ route('admin.email-campaigns.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar para Campanhas
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">{{ $campaign->name }}</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.email-campaigns.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                    @if($campaign->canEdit())
                        <a href="{{ route('admin.email-campaigns.edit', $campaign->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Status e Progresso -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Status da Campanha</h5>
                        <span class="badge bg-{{ $campaign->status === 'sending' ? 'warning' : ($campaign->status === 'completed' ? 'success' : 'secondary') }} fs-6">
                            @switch($campaign->status)
                                @case('sending')
                                    Enviando
                                    @break
                                @case('completed')
                                    Concluída
                                    @break
                                @case('draft')
                                    Rascunho
                                    @break
                                @case('scheduled')
                                    Agendada
                                    @break
                                @default
                                    {{ ucfirst($campaign->status) }}
                            @endswitch
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if($campaign->status === 'sending' || $campaign->status === 'completed')
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Progresso do Envio</span>
                                <span class="text-muted" id="progress-text">
                                    {{ $campaign->sent_count + $campaign->failed_count }} de {{ $campaign->total_recipients }}
                                </span>
                            </div>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $campaign->total_recipients > 0 ? ($campaign->sent_count / $campaign->total_recipients) * 100 : 0 }}%"
                                     id="progress-sent">
                                    <span class="progress-text">{{ $campaign->sent_count }} enviados</span>
                                </div>
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: {{ $campaign->total_recipients > 0 ? ($campaign->failed_count / $campaign->total_recipients) * 100 : 0 }}%"
                                     id="progress-failed">
                                    <span class="progress-text">{{ $campaign->failed_count }} falhas</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between text-sm">
                                <span class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Enviados: <span id="sent-count">{{ $campaign->sent_count }}</span>
                                </span>
                                <span class="text-warning">
                                    <i class="fas fa-clock me-1"></i>
                                    Pendentes: <span id="pending-count">{{ $campaign->pending_count ?? 0 }}</span>
                                </span>
                                <span class="text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Falhas: <span id="failed-count">{{ $campaign->failed_count }}</span>
                                </span>
                            </div>
                        </div>

                        @if($campaign->status === 'sending')
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-info-circle me-2"></i>
                                        <span id="status-message">Enviando emails em lotes de 10 (aguardando 10 segundos entre os lotes)</span>
                                    </div>
                                    <div class="spinner-border spinner-border-sm text-info" role="status">
                                        <span class="visually-hidden">Enviando...</span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
                                             role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Estatísticas Detalhadas -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-primary" id="total-recipients">{{ $campaign->total_recipients }}</h4>
                                    <p class="text-muted mb-0">Total de Destinatários</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-success" id="opened-count">{{ $campaign->opened_count }}</h4>
                                    <p class="text-muted mb-0">Aberturas</p>
                                    <small class="text-muted">{{ $campaign->openRate() }}%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-info" id="clicked-count">{{ $campaign->clicked_count }}</h4>
                                    <p class="text-muted mb-0">Cliques</p>
                                    <small class="text-muted">{{ $campaign->clickRate() }}%</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h4 class="text-warning">{{ $campaign->failureRate() }}%</h4>
                                    <p class="text-muted mb-0">Taxa de Falha</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalhes da Campanha -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Conteúdo do Email</h5>
                </div>
                <div class="card-body">
                    <h6><strong>Assunto:</strong> {{ $campaign->subject }}</h6>
                    <hr>
                    <div class="border p-3 bg-light">
                        {!! $campaign->content !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informações</h5>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $campaign->status === 'sending' ? 'warning' : ($campaign->status === 'completed' ? 'success' : 'secondary') }}">
                            @switch($campaign->status)
                                @case('sending')
                                    Enviando
                                    @break
                                @case('completed')
                                    Concluída
                                    @break
                                @case('draft')
                                    Rascunho
                                    @break
                                @case('scheduled')
                                    Agendada
                                    @break
                                @default
                                    {{ ucfirst($campaign->status) }}
                            @endswitch
                        </span>
                    </p>
                    <p><strong>Criado por:</strong> {{ $campaign->creator->name ?? 'Sistema' }}</p>
                    <p><strong>Criado em:</strong> {{ $campaign->created_at->format('d/m/Y H:i') }}</p>
                    
                    @if($campaign->started_at)
                        <p><strong>Iniciado em:</strong> {{ $campaign->started_at->format('d/m/Y H:i') }}</p>
                    @endif
                    
                    @if($campaign->completed_at)
                        <p><strong>Concluído em:</strong> {{ $campaign->completed_at->format('d/m/Y H:i') }}</p>
                    @endif
                    
                    @if($campaign->scheduled_at)
                        <p><strong>Agendado para:</strong> {{ $campaign->scheduled_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>

            <!-- Ações -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Ações</h5>
                </div>
                <div class="card-body">
                    @if($campaign->canSend())
                        <form action="{{ route('admin.email-campaigns.send', $campaign->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100 mb-2" 
                                    onclick="return confirm('Tem certeza que deseja enviar esta campanha?')">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Campanha
                            </button>
                        </form>
                    @endif
                    
                    @if($campaign->canCancel())
                        <form action="{{ route('admin.email-campaigns.cancel', $campaign->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm w-100 mb-2"
                                    onclick="return confirm('Tem certeza que deseja cancelar esta campanha?')">
                                <i class="fas fa-stop me-2"></i>Cancelar Campanha
                            </button>
                        </form>
                    @endif
                    
                    <button type="button" class="btn btn-info btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                        <i class="fas fa-vial me-2"></i>Enviar Teste
                    </button>
                    
                    <a href="{{ route('admin.email-campaigns.edit', $campaign->id) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Destinatários -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Destinatários ({{ $recipients->total() }})</h5>
                </div>
                <div class="card-body">
                    @if($recipients->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>Nome</th>
                                        <th>Status</th>
                                        <th>Enviado em</th>
                                        <th>Aberto</th>
                                        <th>Clicado</th>
                                        <th>Erro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recipients as $recipient)
                                        <tr>
                                            <td>{{ $recipient->email }}</td>
                                            <td>{{ $recipient->name ?: '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $recipient->status === 'sent' ? 'success' : 
                                                    ($recipient->status === 'failed' ? 'danger' : 
                                                    ($recipient->status === 'sending' ? 'warning' : 'secondary')) 
                                                }}">
                                                    @switch($recipient->status)
                                                        @case('sent')
                                                            Enviado
                                                            @break
                                                        @case('failed')
                                                            Falhou
                                                            @break
                                                        @case('sending')
                                                            Enviando
                                                            @break
                                                        @case('pending')
                                                            Pendente
                                                            @break
                                                        @default
                                                            {{ ucfirst($recipient->status) }}
                                                    @endswitch
                                                </span>
                                            </td>
                                            <td>{{ $recipient->sent_at ? $recipient->sent_at->format('d/m/Y H:i') : '-' }}</td>
                                            <td>
                                                @if($recipient->opened_at)
                                                    <i class="fas fa-check text-success"></i>
                                                    {{ $recipient->opened_at->format('d/m H:i') }}
                                                    @if($recipient->open_count > 1)
                                                        <small class="text-muted">({{ $recipient->open_count }}x)</small>
                                                    @endif
                                                @else
                                                    <i class="fas fa-times text-muted"></i>
                                                @endif
                                            </td>
                                            <td>
                                                @if($recipient->clicked_at)
                                                    <i class="fas fa-check text-success"></i>
                                                    {{ $recipient->clicked_at->format('d/m H:i') }}
                                                    @if($recipient->click_count > 1)
                                                        <small class="text-muted">({{ $recipient->click_count }}x)</small>
                                                    @endif
                                                @else
                                                    <i class="fas fa-times text-muted"></i>
                                                @endif
                                            </td>
                                            <td>
                                                @if($recipient->error_message)
                                                    <span class="text-danger" title="{{ $recipient->error_message }}">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{ $recipients->links() }}
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum destinatário encontrado.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Teste de Email -->
<div class="modal fade" id="testEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enviar Email de Teste</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="test_email" class="form-label">Email de Destino</label>
                    <input type="email" class="form-control" id="test_email" placeholder="seu@email.com">
                </div>
                <div id="test-result" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="send-test-button">
                    <i class="fas fa-paper-plane me-2"></i>Enviar Teste
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .progress {
        background-color: #f8f9fa;
        box-shadow: inset 0 1px 3px rgba(0,0,0,.1);
    }
    
    .progress-bar {
        transition: width 0.5s ease;
        position: relative;
    }
    
    .progress-text {
        position: absolute;
        right: 5px;
        color: white;
        font-size: 12px;
        line-height: 20px;
        text-shadow: 0 0 2px rgba(0,0,0,0.5);
    }
    
    .progress-bar-animated {
        animation: progress-bar-stripes 1s linear infinite;
    }
    
    @keyframes progress-bar-stripes {
        0% { background-position-x: 1rem; }
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Função para enviar email de teste
        function sendTest() {
            const email = $('#test_email').val();
            const sendTestButton = $('#send-test-button');
            const testResult = $('#test-result');
            
            if (!email) {
                testResult.html('<div class="alert alert-danger">Por favor, informe um email válido.</div>');
                testResult.show();
                return;
            }
            
            // Desabilitar botão e mostrar loading
            sendTestButton.prop('disabled', true);
            sendTestButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...');
            testResult.hide();
            
            // Enviar requisição AJAX
            $.ajax({
                url: '{{ route('admin.email-campaigns.test', $campaign->id) }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify({ 
                    test_email: email
                }),
                contentType: 'application/json',
                success: function(data) {
                    sendTestButton.prop('disabled', false);
                    sendTestButton.html('<i class="fas fa-paper-plane me-2"></i>Enviar Teste');
                    
                    if (data.success) {
                        testResult.html('<div class="alert alert-success">' + data.message + '</div>');
                    } else {
                        testResult.html('<div class="alert alert-danger">' + data.message + '</div>');
                    }
                    
                    testResult.show();
                },
                error: function(xhr, status, error) {
                    sendTestButton.prop('disabled', false);
                    sendTestButton.html('<i class="fas fa-paper-plane me-2"></i>Enviar Teste');
                    testResult.html('<div class="alert alert-danger">Erro ao enviar teste: ' + error + '</div>');
                    testResult.show();
                }
            });
        }

        // Adicionar evento de click ao botão
        $('#send-test-button').on('click', sendTest);

        // Atualizar progresso em tempo real
        function updateProgress() {
            if ('{{ $campaign->status }}' === 'sending') {
                $.ajax({
                    url: '{{ route('admin.email-campaigns.show', $campaign->id) }}',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(data) {
                        // Atualizar contadores
                        $('#progress-text').text(
                            `${data.sent_count + data.failed_count} de ${data.total_recipients}`
                        );
                        
                        // Atualizar barras de progresso
                        const sentPercentage = (data.sent_count / data.total_recipients) * 100;
                        const failedPercentage = (data.failed_count / data.total_recipients) * 100;
                        
                        $('#progress-sent')
                            .css('width', `${sentPercentage}%`)
                            .find('.progress-text')
                            .text(`${data.sent_count} enviados`);
                            
                        $('#progress-failed')
                            .css('width', `${failedPercentage}%`)
                            .find('.progress-text')
                            .text(`${data.failed_count} falhas`);
                        
                        // Atualizar contadores detalhados
                        $('#sent-count').text(data.sent_count);
                        $('#pending-count').text(data.pending_count);
                        $('#failed-count').text(data.failed_count);
                        
                        // Atualizar estatísticas
                        $('#total-recipients').text(data.total_recipients);
                        $('#opened-count').text(data.opened_count);
                        $('#clicked-count').text(data.clicked_count);
                        
                        // Continuar atualizando se ainda estiver enviando
                        if (data.status === 'sending') {
                            setTimeout(updateProgress, 2000);
                        } else {
                            window.location.reload(); // Recarregar página quando concluir
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro ao atualizar progresso:', error);
                    }
                });
            }
        }

        // Iniciar atualização se estiver enviando
        if ('{{ $campaign->status }}' === 'sending') {
            updateProgress();
        }
    });
</script>
@endpush 