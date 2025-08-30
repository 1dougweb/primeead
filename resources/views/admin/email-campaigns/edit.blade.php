@extends('layouts.admin')

@section('title', 'Editar Campanha')

@section('page-title', 'Editar Campanha de Email')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.email-campaigns.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar para Campanhas
        </a>
        
        @if($campaign->status === 'draft')
            <button type="button" class="btn btn-info" onclick="showTestModal()">
                <i class="fas fa-paper-plane me-2"></i>
                Enviar Teste
            </button>
        @endif
    </div>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Editar Campanha</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.email-campaigns.update', $campaign->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nome da Campanha <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $campaign->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subject" class="form-label">Assunto do Email <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                           id="subject" name="subject" value="{{ old('subject', $campaign->subject) }}" required>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="content" class="form-label">Conteúdo do Email <span class="text-danger">*</span></label>
                            <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" 
                                      rows="20" required>{{ old('content', $campaign->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <strong>Variáveis disponíveis:</strong> 
                                <code>{{ '{' }}{{ '{' }}nome{{ '}' }}{{ '}' }}</code>, <code>{{ '{' }}{{ '{' }}email{{ '}' }}{{ '}' }}</code>, <code>{{ '{' }}{{ '{' }}telefone{{ '}' }}{{ '}' }}</code>, 
                                <code>{{ '{' }}{{ '{' }}curso{{ '}' }}{{ '}' }}</code>, <code>{{ '{' }}{{ '{' }}modalidade{{ '}' }}{{ '}' }}</code>
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="scheduled_at" class="form-label">Agendar Envio</label>
                            <input type="datetime-local" class="form-control @error('scheduled_at') is-invalid @enderror" 
                                   id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at', $campaign->scheduled_at ? $campaign->scheduled_at->format('Y-m-d\TH:i') : '') }}">
                            @error('scheduled_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Deixe em branco para salvar como rascunho. Defina uma data e hora para agendar o envio automático.
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.email-campaigns.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Atualizar Campanha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar com informações -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Informações da Campanha</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $campaign->status === 'draft' ? 'secondary' : ($campaign->status === 'scheduled' ? 'warning' : ($campaign->status === 'sending' ? 'info' : ($campaign->status === 'sent' ? 'success' : 'danger'))) }}">
                            {{ ucfirst($campaign->status) }}
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Criada em:</strong><br>
                        {{ $campaign->created_at->format('d/m/Y H:i') }}
                    </div>
                    
                    @if($campaign->scheduled_at)
                        <div class="mb-3">
                            <strong>Agendada para:</strong><br>
                            {{ $campaign->scheduled_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                    
                    @if($campaign->sent_at)
                        <div class="mb-3">
                            <strong>Enviada em:</strong><br>
                            {{ $campaign->sent_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                    
                    <div class="mb-0">
                        <strong>Total de Destinatários:</strong><br>
                        {{ $campaign->recipients()->count() }}
                    </div>
                </div>
            </div>
            
            <!-- Estatísticas -->
            @if($campaign->status === 'sent')
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Estatísticas</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Enviados:</strong>
                            <span class="text-success">{{ $campaign->recipients()->where('status', 'sent')->count() }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Abertos:</strong>
                            <span class="text-info">{{ $campaign->recipients()->where('opened_at', '!=', null)->count() }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Cliques:</strong>
                            <span class="text-primary">{{ $campaign->recipients()->where('clicked_at', '!=', null)->count() }}</span>
                        </div>
                        
                        <div class="mb-0">
                            <strong>Falhas:</strong>
                            <span class="text-danger">{{ $campaign->recipients()->where('status', 'failed')->count() }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Teste -->
<div class="modal fade" id="testModal" tabindex="-1" aria-labelledby="testModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testModalLabel">Enviar Email de Teste</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Envie um email de teste para verificar como a campanha ficará na caixa de entrada.</p>
                
                <div class="form-group">
                    <label for="test_email" class="form-label">Email de Destino</label>
                    <input type="email" class="form-control" id="test_email" placeholder="seu@email.com">
                </div>
                
                <div id="test-result" class="mt-3" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="send-test-button">
                    <i class="fas fa-paper-plane me-2"></i>
                    Enviar Teste
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    #content {
        font-family: 'Courier New', monospace;
        font-size: 14px;
        line-height: 1.4;
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Função para mostrar o modal de teste
        function showTestModal() {
            const testModal = new bootstrap.Modal($('#testModal')[0]);
            $('#test-result').hide();
            testModal.show();
        }
        
        // Função para enviar email de teste
        function sendTest() {
            const email = $('#test_email').val();
            const content = $('#content').val();
            const subject = $('#subject').val();
            const name = $('#name').val();
            const sendTestButton = $('#send-test-button');
            const testResult = $('#test-result');
            
            if (!email) {
                testResult.html('<div class="alert alert-danger">Por favor, informe um email válido.</div>');
                testResult.show();
                return;
            }
            
            if (!content || !subject || !name) {
                testResult.html('<div class="alert alert-danger">Por favor, preencha todos os campos da campanha antes de enviar o teste.</div>');
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
                    test_email: email,
                    content: content,
                    subject: subject,
                    name: name
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

        // Adicionar eventos aos botões
        $('.btn-info').on('click', showTestModal);
        $('#send-test-button').on('click', sendTest);
    });
</script>
@endpush
