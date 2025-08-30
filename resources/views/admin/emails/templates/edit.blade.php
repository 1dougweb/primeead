@extends('layouts.admin')

@section('title', 'Editar Template')

@section('page-title', 'Editar Template: ' . $template['name'])

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar para Templates
        </a>
        
        <button type="button" class="btn btn-info" onclick="previewTemplate()">
            <i class="fas fa-eye me-2"></i>
            Visualizar
        </button>
        
        <button type="button" class="btn btn-success" onclick="showSendTestModal()">
            <i class="fas fa-paper-plane me-2"></i>
            Enviar Teste
        </button>
        
        <button type="button" class="btn btn-warning" onclick="confirmRestore()">
            <i class="fas fa-undo me-2"></i>
            Restaurar Padrão
        </button>
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
                    <h5 class="mb-0">Editor de Template</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.email-templates.update', $template['key']) }}" method="POST" id="templateForm" onsubmit="return prepareSubmit();">
                        @csrf
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="form-group">
                            <textarea name="content" id="templateEditor" class="form-control" rows="25">{{ old('content', $template['content']) }}</textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="button" class="btn btn-info me-2" onclick="previewTemplate()">
                                    <i class="fas fa-eye me-2"></i>
                                    Visualizar
                                </button>
                                
                                <button type="button" class="btn btn-success me-2" onclick="showSendTestModal()">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Enviar Teste
                                </button>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Salvar Template
                            </button>
                        </div>
                    </form>
                    
                    <form id="restoreForm" action="{{ route('admin.email-templates.restore', $template['key']) }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Variáveis Disponíveis</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Use essas variáveis no seu template:</p>
                    
                    @foreach($availableVariables as $variable => $description)
                        <div class="mb-2">
                            <code class="text-primary">{{ '{' }}{{ '{' }} {{ $variable }} {{ '}' }}{{ '}' }}</code>
                            <small class="text-muted d-block">{{ $description }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Dicas de HTML</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Estrutura básica:</strong>
                            <code>&lt;div style="..."&gt;</code>
                        </li>
                        <li class="mb-2">
                            <strong>Cores:</strong>
                            <code>color: #333;</code>
                        </li>
                        <li class="mb-2">
                            <strong>Fontes:</strong>
                            <code>font-family: Arial;</code>
                        </li>
                        <li class="mb-2">
                            <strong>Tamanhos:</strong>
                            <code>font-size: 16px;</code>
                        </li>
                        <li class="mb-0">
                            <strong>Espaçamento:</strong>
                            <code>padding: 20px;</code>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Preview -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Preview do Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="previewLoading" class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Gerando preview...</p>
                </div>
                
                <div id="previewContent" style="display: none;"></div>
                
                <div id="previewError" class="alert alert-danger" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Envio de Teste -->
<div class="modal fade" id="sendTestModal" tabindex="-1" aria-labelledby="sendTestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendTestModalLabel">Enviar Email de Teste</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Envie um email de teste para verificar como o template ficará na caixa de entrada.</p>
                
                <div class="form-group">
                    <label for="testEmail" class="form-label">Email de Destino</label>
                    <input type="email" class="form-control" id="testEmail" placeholder="seu@email.com">
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    O email será enviado com dados de exemplo.
                </div>
                
                <div id="sendTestResult" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="sendTestButton" onclick="sendTestEmail()">
                    <i class="fas fa-paper-plane me-2"></i>
                    Enviar Teste
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Preview do template
    function previewTemplate() {
        const content = document.getElementById('templateEditor').value;
        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        
        // Mostrar modal e loading
        previewModal.show();
        document.getElementById('previewLoading').style.display = 'block';
        document.getElementById('previewContent').style.display = 'none';
        document.getElementById('previewError').style.display = 'none';
        
        // Enviar requisição AJAX para preview
        fetch('{{ route('admin.email-templates.preview', $template['key']) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ content: content })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('previewLoading').style.display = 'none';
            
            if (data.success) {
                document.getElementById('previewContent').innerHTML = data.html;
                document.getElementById('previewContent').style.display = 'block';
            } else {
                document.getElementById('previewError').textContent = data.message;
                document.getElementById('previewError').style.display = 'block';
            }
        })
        .catch(error => {
            document.getElementById('previewLoading').style.display = 'none';
            document.getElementById('previewError').textContent = 'Erro ao gerar preview: ' + error.message;
            document.getElementById('previewError').style.display = 'block';
        });
    }
    
    // Mostrar modal de envio de teste
    function showSendTestModal() {
        const sendTestModal = new bootstrap.Modal(document.getElementById('sendTestModal'));
        document.getElementById('sendTestResult').style.display = 'none';
        sendTestModal.show();
    }
    
    // Enviar email de teste
    function sendTestEmail() {
        const email = document.getElementById('testEmail').value;
        const content = document.getElementById('templateEditor').value;
        const sendTestButton = document.getElementById('sendTestButton');
        const sendTestResult = document.getElementById('sendTestResult');
        
        if (!email) {
            sendTestResult.innerHTML = '<div class="alert alert-danger">Por favor, informe um email válido.</div>';
            sendTestResult.style.display = 'block';
            return;
        }
        
        // Desabilitar botão e mostrar loading
        sendTestButton.disabled = true;
        sendTestButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';
        sendTestResult.style.display = 'none';
        
        // Enviar requisição AJAX
        fetch('{{ route('admin.email-templates.send-test', $template['key']) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                email: email,
                content: content
            })
        })
        .then(response => response.json())
        .then(data => {
            sendTestButton.disabled = false;
            sendTestButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Enviar Teste';
            
            if (data.success) {
                sendTestResult.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
            } else {
                sendTestResult.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
            
            sendTestResult.style.display = 'block';
        })
        .catch(error => {
            sendTestButton.disabled = false;
            sendTestButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Enviar Teste';
            sendTestResult.innerHTML = '<div class="alert alert-danger">Erro ao enviar email: ' + error.message + '</div>';
            sendTestResult.style.display = 'block';
        });
    }
    
    // Confirmar restauração do template
    function confirmRestore() {
        if (confirm('Tem certeza que deseja restaurar este template para o padrão? Todas as alterações serão perdidas.')) {
            document.getElementById('restoreForm').submit();
        }
    }
    
    // Preparar o formulário para envio
    function prepareSubmit() {
        return true;
    }
</script>
@endsection

@section('styles')
<style>
    .form-group {
        margin-bottom: 1rem;
        position: relative;
    }
    
    .modal-xl {
        max-width: 90%;
    }
    
    #previewContent {
        max-height: 80vh;
        overflow-y: auto;
    }
    
    #templateEditor {
        font-family: 'Courier New', monospace;
        font-size: 14px;
        line-height: 1.4;
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    /* Corrigir problemas de layout */
    .card-body {
        overflow: visible;
    }
</style>
@endsection