@extends('layouts.admin')

@section('title', 'Nova Campanha - Passo 1')

@section('page-title', 'Nova Campanha de Email')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.email-campaigns.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar para Campanhas
        </a>
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
    
    <!-- Progress Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="step-progress">
                            <div class="step active">
                                <div class="step-circle">1</div>
                                <div class="step-label">Conteúdo</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step">
                                <div class="step-circle">2</div>
                                <div class="step-label">Destinatários</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step">
                                <div class="step-circle">3</div>
                                <div class="step-label">Confirmação</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário Principal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Passo 1: Conteúdo da Campanha</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.email-campaigns.create-step2') }}" method="POST" id="campaignForm">
                        @csrf
                        
                        <input type="hidden" id="template_id" name="template_id" value="">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nome da Campanha <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Nome interno para identificar esta campanha.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subject" class="form-label">Assunto do Email <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                           id="subject" name="subject" value="{{ old('subject') }}" required>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Este será o assunto do email que os destinatários verão.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group mb-4">
                                    <label for="content" class="form-label">Conteúdo do Email <span class="text-danger">*</span></label>
                                    <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" 
                                              rows="20" placeholder="Digite seu HTML aqui...">{{ old('content') }}</textarea>
                                    
                                    @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="content-error" class="invalid-feedback" style="display: none;">
                                        O conteúdo do email é obrigatório.
                                    </div>
                                    <div class="form-text">
                                        <strong>Variáveis disponíveis:</strong> 
                                        <code>@{{ nome }}</code>, <code>@{{ email }}</code>, <code>@{{ telefone }}</code>, 
                                        <code>@{{ curso }}</code>, <code>@{{ modalidade }}</code>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-5">
                                <div class="card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 flex justify-content-center">Preview do Email</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="previewWrapper" class="desktop">
                                            <iframe id="previewFrame" class="w-100 border-0" style="height: 600px;"></iframe>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light">
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100" id="updatePreview">
                                            <i class="fas fa-sync-alt me-2"></i>
                                            Atualizar Preview
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.email-campaigns.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-arrow-right me-2"></i>
                                Próximo: Selecionar Destinatários
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Galeria de Templates -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-palette me-2"></i>
                        Galeria de Templates
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">{{ count($templates) + (isset($savedTemplates) ? $savedTemplates->count() : 0) + 1 }} templates disponíveis</span>
                        <a href="{{ route('admin.email-campaigns.templates') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>
                            Gerenciar Templates
                        </a>
                    </div>
                </div>
                <div class="card-body rounded-3 border">
                    <p class="text-muted mb-4">Escolha um template para começar rapidamente. Clique em um template para visualizar e aplicar ao seu email.</p>
                    
                    <div class="row template-gallery">
                        <!-- Template em Branco -->
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card h-100 template-card" data-template-id="blank" data-name="" data-subject="" data-content="">
                                <div class="template-preview bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-plus fa-3x text-muted"></i>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">Template em Branco</h5>
                                    <p class="card-text text-muted">Comece do zero com um template personalizado</p>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100 select-template">
                                        <i class="fas fa-check me-2"></i>
                                        Selecionar
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Templates Salvos do Banco de Dados -->
                        @if(isset($savedTemplates) && $savedTemplates->count() > 0)
                            @foreach($savedTemplates as $template)
                                <div class="col-md-4 col-lg-3 mb-4">
                                    <div class="card h-100 template-card" 
                                         data-template-id="{{ $template->id }}"
                                         data-name="{{ htmlspecialchars($template->name, ENT_QUOTES) }}"
                                         data-subject="{{ htmlspecialchars($template->subject, ENT_QUOTES) }}"
                                         data-description="{{ htmlspecialchars($template->description, ENT_QUOTES) }}"
                                         data-content="{{ base64_encode($template->content) }}">
                                        <div class="template-preview" style="height: 200px; overflow: hidden; position: relative;">
                                            <div class="template-badge position-absolute" style="top: 10px; right: 10px;">
                                                @if($template->is_ai_generated)
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-robot"></i> IA
                                                    </span>
                                                @else
                                                    <span class="badge bg-primary">Personalizado</span>
                                                @endif
                                            </div>
                                            <iframe srcdoc="{{ htmlspecialchars($template->content, ENT_QUOTES, 'UTF-8') }}" frameborder="0" style="width: 100%; height: 400px; transform: scale(0.5); transform-origin: 0 0;"></iframe>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $template->name }}</h5>
                                            <p class="card-text text-muted small">{{ $template->description }}</p>
                                        </div>
                                        <div class="card-footer bg-white border-0">
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1 preview-template" data-bs-toggle="modal" data-bs-target="#previewModal">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Preview
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm flex-grow-1 select-template">
                                                    <i class="fas fa-check me-1"></i>
                                                    Selecionar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        
                        <!-- Templates Pré-definidos -->
                        @foreach($templates as $template)
                            <!-- Debug -->
                            <script>
                                console.log({{ Js::from([
                                    'message' => 'Template ' . $template['id'] . ':',
                                    'id' => $template['id'],
                                    'name' => $template['name'],
                                    'subject' => $template['subject'],
                                    'content_preview' => substr($template['content'], 0, 100) . '...'
                                ]) }});
                            </script>
                            
                            <div class="col-md-4 col-lg-3 mb-4">
                                <div class="card h-100 template-card" 
                                     data-template-id="{{ $template['id'] }}"
                                     data-name="{{ htmlspecialchars($template['name'], ENT_QUOTES) }}"
                                     data-subject="{{ htmlspecialchars($template['subject'], ENT_QUOTES) }}"
                                     data-description="{{ htmlspecialchars($template['description'], ENT_QUOTES) }}"
                                     data-content="{{ base64_encode($template['content']) }}">
                                    <div class="template-preview" style="height: 200px; overflow: hidden; position: relative;">
                                        <div class="template-badge badge bg-{{ $template['id'] == 'welcome' ? 'primary' : ($template['id'] == 'followup' ? 'success' : 'danger') }} position-absolute" style="top: 10px; right: 10px;">
                                            {{ $template['id'] == 'welcome' ? 'Boas-vindas' : ($template['id'] == 'followup' ? 'Follow-up' : 'Promocional') }}
                                        </div>
                                        <iframe srcdoc="{{ htmlspecialchars($template['content'], ENT_QUOTES, 'UTF-8') }}" frameborder="0" style="width: 100%; height: 400px; transform: scale(0.5); transform-origin: 0 0;"></iframe>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $template['name'] }}</h5>
                                        <p class="card-text text-muted small">{{ $template['description'] }}</p>
                                    </div>
                                    <div class="card-footer bg-white border-0">
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1 preview-template" data-bs-toggle="modal" data-bs-target="#previewModal">
                                                <i class="fas fa-eye me-1"></i>
                                                Preview
                                            </button>
                                            <button type="button" class="btn btn-primary btn-sm flex-grow-1 select-template">
                                                <i class="fas fa-check me-1"></i>
                                                Selecionar
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <button type="button" class="btn btn-outline-info btn-sm w-100 edit-template" data-bs-toggle="modal" data-bs-target="#editTemplateModal">
                                                <i class="fas fa-edit me-1"></i>
                                                Editar Template
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Preview -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Preview do Template</h5>
                
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-0">
                <div id="previewLoading" class="text-center p-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando preview...</p>
                </div>
                <div id="previewContainer" style="height: 70vh;"    >
                <div class="modal-content" style="height: 100%;">
                            <iframe id="previewFrame" srcdoc="" frameborder="0" style="width: 100%; height: 100%; background-color: white;"></iframe>
                        </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="applyTemplateBtn">Aplicar Template</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição de Template -->
<div class="modal fade" id="editTemplateModal" tabindex="-1" aria-labelledby="editTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTemplateModalLabel">Editar Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="editTemplateForm">
                    <input type="hidden" id="editTemplateId" name="id">
                    
                    <div class="mb-3">
                        <label for="editTemplateName" class="form-label">Nome do Template</label>
                        <input type="text" class="form-control" id="editTemplateName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editTemplateSubject" class="form-label">Assunto Padrão</label>
                        <input type="text" class="form-control" id="editTemplateSubject" name="subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editTemplateDescription" class="form-label">Descrição</label>
                        <textarea class="form-control" id="editTemplateDescription" name="description" rows="2" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editTemplateContent" class="form-label">Conteúdo HTML</label>
                        <div id="editTemplateContent" style="height: 400px;"></div>
                        <div class="form-text">
                            <strong>Variáveis disponíveis:</strong> 
                            <code>@{{ nome }}</code>, <code>@{{ email }}</code>, <code>@{{ telefone }}</code>, 
                            <code>@{{ curso }}</code>, <code>@{{ modalidade }}</code>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveTemplateBtn">
                    <i class="fas fa-save me-2"></i>
                    Salvar Template
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    .step-progress {
        display: flex;
        align-items: center;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }
    
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
    }
    
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #6c757d;
        margin-bottom: 8px;
    }
    
    .step.active .step-circle {
        background-color: var(--bs-primary);
        color: white;
    }
    
    .step-label {
        font-size: 14px;
        color: #6c757d;
    }
    
    .step.active .step-label {
        color: var(--bs-primary);
        font-weight: 600;
    }
    
    .step-line {
        flex: 1;
        height: 3px;
        background-color: #e9ecef;
        margin: 0 10px;
        margin-bottom: 25px;
    }
    
    .template-option {
        cursor: pointer;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        transition: all 0.2s;
    }
    
    .template-option:hover {
        border-color: var(--bs-primary);
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }
    
    .template-option.active {
        border-color: var(--bs-primary);
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }
    
    /* Novos estilos para a galeria de templates */
    .template-gallery {
        margin: 0 -10px;
    }
    
    .template-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    
    .template-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .template-card.selected {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.3);
    }
    
    .template-preview {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    #editTemplateContent {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }

    #previewWrapper {
        transition: all 0.3s ease;
        margin: 0 auto;
        max-width: 100%;
        overflow: hidden;
    }

    #previewWrapper.desktop {
        width: 100%;
    }

    #previewWrapper.tablet {
        width: 768px;
    }

    #previewWrapper.mobile {
        width: 375px;
    }

    .preview-size {
        padding: 0.5rem 1rem;
        border: 1px solid #dee2e6;
        background: white;
        cursor: pointer;
    }
    
    .preview-size:hover {
        background: #f8f9fa;
    }
    
    .preview-size.active {
        background-color: #0d6efd !important;
        color: white !important;
        border-color: #0d6efd !important;
    }

    /* Melhorias na visualização dos templates na galeria */
    .template-preview {
        background-color: #f8f9fa;
        position: relative;
        overflow: hidden;
        height: 200px;
    }

    .template-preview iframe {
        width: 1024px;
        height: 800px;
        border: none;
        transform: scale(0.2);
        transform-origin: 0 0;
        background-color: white;
    }

    .template-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1;
    }

    #content {
        font-family: monospace;
        font-size: 14px;
        line-height: 1.5;
        tab-size: 4;
    }

    #previewFrame {
        width: 100%;
        height: 600px;
        border: none;
        background: white;
    }

    .device-preview {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        background: white;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.43.0/min/vs/loader.min.js"></script>
<script>
    // Configuração do Monaco Editor
    require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.43.0/min/vs' } });

    let monacoEditor = null;

    require(['vs/editor/editor.main'], function() {
        // Criar o editor quando o modal for aberto
        document.getElementById('editTemplateModal').addEventListener('shown.bs.modal', function() {
            if (!monacoEditor) {
                monacoEditor = monaco.editor.create(document.getElementById('editTemplateContent'), {
                    language: 'html',
                    theme: 'vs-light',
                    minimap: { enabled: false },
                    automaticLayout: true,
                    wordWrap: 'on',
                    fontSize: 14
                });
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Debug para verificar se o Bootstrap está carregado
        console.log('Bootstrap disponível:', typeof bootstrap !== 'undefined');
        
        // Debug para os botões de preview
        const previewButtons = document.querySelectorAll('.preview-template');
        console.log('Botões de preview encontrados:', previewButtons.length);
        
        previewButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                console.log('Clique no botão de preview');
                // O modal deve abrir automaticamente pelo data-bs-toggle
            });
        });

        const templateCards = document.querySelectorAll('.template-card');
        const nameInput = document.getElementById('name');
        const subjectInput = document.getElementById('subject');
        const contentTextarea = document.getElementById('content');
        const templateIdInput = document.getElementById('template_id');
        const previewFrame = document.getElementById('previewFrame');
        const previewLoading = document.getElementById('previewLoading');
        const applyTemplateBtn = document.getElementById('applyTemplateBtn');
        const updatePreviewBtn = document.getElementById('updatePreview');
        const previewSizeButtons = document.querySelectorAll('.preview-size');
        const previewWrapper = document.getElementById('previewWrapper');

        // Função para decodificar conteúdo base64 com suporte a UTF-8
        function decodeTemplateContent(encodedContent) {
            try {
                return decodeURIComponent(escape(atob(encodedContent)));
            } catch (e) {
                console.error('Erro ao decodificar conteúdo:', e);
                return '';
            }
        }

        // Função para codificar conteúdo em base64 com suporte a UTF-8
        function encodeTemplateContent(content) {
            try {
                return btoa(unescape(encodeURIComponent(content)));
            } catch (e) {
                console.error('Erro ao codificar conteúdo:', e);
                return '';
            }
        }

        // Event listener para o botão de editar template
        document.querySelectorAll('.edit-template').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const templateCard = this.closest('.template-card');
                
                // Preencher o formulário de edição
                document.getElementById('editTemplateId').value = templateCard.dataset.templateId;
                document.getElementById('editTemplateName').value = templateCard.dataset.name;
                document.getElementById('editTemplateSubject').value = templateCard.dataset.subject;
                document.getElementById('editTemplateDescription').value = templateCard.dataset.description;

                // Aguardar o Monaco Editor estar pronto
                const waitForMonaco = setInterval(() => {
                    if (monacoEditor) {
                        const decodedContent = decodeTemplateContent(templateCard.dataset.content);
                        monacoEditor.setValue(decodedContent);
                        clearInterval(waitForMonaco);
                    }
                }, 100);
            });
        });

        // Event listener para salvar as alterações do template
        document.getElementById('saveTemplateBtn').addEventListener('click', async function() {
            try {
                const templateId = document.getElementById('editTemplateId').value;
                const formData = {
                    id: templateId,
                    name: document.getElementById('editTemplateName').value,
                    subject: document.getElementById('editTemplateSubject').value,
                    description: document.getElementById('editTemplateDescription').value,
                    content: monacoEditor.getValue()
                };

                const response = await fetch(`/dashboard/email-templates/${templateId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                
                // Atualizar o card do template
                const templateCard = document.querySelector(`.template-card[data-template-id="${result.id}"]`);
                if (templateCard) {
                    templateCard.dataset.name = result.name;
                    templateCard.dataset.subject = result.subject;
                    templateCard.dataset.description = result.description;
                    templateCard.dataset.content = encodeTemplateContent(result.content);
                    
                    templateCard.querySelector('.card-title').textContent = result.name;
                    templateCard.querySelector('.card-text').textContent = result.description;
                    templateCard.querySelector('iframe').srcdoc = result.content;
                }

                // Fechar o modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editTemplateModal'));
                modal.hide();

                // Mostrar mensagem de sucesso
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    Template atualizado com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                `;
                document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
            } catch (error) {
                console.error('Erro ao salvar template:', error);
                // Mostrar mensagem de erro
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.innerHTML = `
                    Erro ao salvar o template. Por favor, tente novamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                `;
                document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
            }
        });
        
        // Função para selecionar um template
        function selectTemplate(templateCard) {
            // Remover seleção anterior
            templateCards.forEach(card => card.classList.remove('selected'));
            
            // Adicionar seleção ao card atual
            templateCard.classList.add('selected');
            
            // Armazenar referência ao template selecionado
            currentSelectedTemplate = templateCard;
            
            // Preencher os campos do formulário
            const templateId = templateCard.dataset.templateId;
            const templateName = templateCard.dataset.name;
            const templateSubject = templateCard.dataset.subject;
            const templateContent = decodeTemplateContent(templateCard.dataset.content);
            
            if (templateId !== 'blank') {
                if (!nameInput.value) {
                    nameInput.value = templateName;
                }
                if (!subjectInput.value) {
                    subjectInput.value = templateSubject;
                }
                contentTextarea.value = templateContent;
            } else {
                // Se for template em branco, limpar o conteúdo apenas se não tiver sido editado
                if (!contentTextarea.value || contentTextarea.value === '') {
                    contentTextarea.value = '';
                }
            }
            
            templateIdInput.value = templateId;
        }
        
        // Event listeners para os botões de seleção de template
        document.querySelectorAll('.select-template').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const templateCard = this.closest('.template-card');
                selectTemplate(templateCard);
                
                // Rolar para o formulário
                document.querySelector('#campaignForm').scrollIntoView({ behavior: 'smooth' });
            });
        });
        
        // Event listeners para os cards de template
        templateCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Verificar se o clique foi no botão de preview ou selecionar
                if (!e.target.closest('.preview-template') && !e.target.closest('.select-template')) {
                    selectTemplate(this);
                }
            });
        });
        
        // Event listeners para os botões de preview
        document.querySelectorAll('.preview-template').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const templateCard = this.closest('.template-card');
                const templateContent = decodeTemplateContent(templateCard.dataset.content);
                
                // Mostrar loading
                previewLoading.style.display = 'block';
                document.getElementById('previewContainer').style.display = 'none';
                
                // Preparar o HTML com meta tags e estilos básicos
                const htmlContent = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <style>
                            body {
                                margin: 0;
                                padding: 20px;
                                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                                line-height: 1.5;
                            }
                            img {
                                max-width: 100%;
                                height: auto;
                            }
                            table {
                                width: 100%;
                                max-width: 100%;
                            }
                        </style>
                    </head>
                    <body>
                        ${templateContent}
                    </body>
                    </html>
                `;
                
                // Atualizar o iframe
                const previewFrame = document.getElementById('previewFrame');
                previewFrame.srcdoc = htmlContent;
                
                // Quando o iframe carregar
                previewFrame.onload = function() {
                    previewLoading.style.display = 'none';
                    document.getElementById('previewContainer').style.display = 'flex';
                    
                    // Ativar o modo desktop por padrão
                    document.querySelector('[data-size="desktop"]').click();
                };
                
                // Armazenar referência ao template para o botão "Aplicar Template"
                applyTemplateBtn.dataset.templateId = templateCard.dataset.templateId;
            });
        });
        
        // Controles de tamanho do preview
        document.querySelectorAll('.preview-size').forEach(button => {
            button.addEventListener('click', function() {
                // Remover classe active de todos os botões
                document.querySelectorAll('.preview-size').forEach(btn => btn.classList.remove('active'));
                // Adicionar classe active ao botão clicado
                this.classList.add('active');
                
                const size = this.dataset.size;
                const wrapper = document.getElementById('previewWrapper');
                
                // Remover classes anteriores
                wrapper.classList.remove('desktop', 'tablet', 'mobile');
                // Adicionar nova classe
                wrapper.classList.add(size);
            });
        });

        // Event listener para o botão "Aplicar Template" no modal
        applyTemplateBtn.addEventListener('click', function() {
            const templateId = this.dataset.templateId;
            const templateCard = document.querySelector(`.template-card[data-template-id="${templateId}"]`);
            
            if (templateCard) {
                selectTemplate(templateCard);
                
                // Fechar o modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('previewModal'));
                modal.hide();
                
                // Rolar para o formulário
                document.querySelector('#campaignForm').scrollIntoView({ behavior: 'smooth' });
            }
        });
        
        // Verificar se há um template selecionado via URL
        @if(isset($selectedTemplate))
        const selectedTemplateId = {{ Js::from($selectedTemplate->id ?? $selectedTemplate['id']) }};
        const selectedTemplateCard = document.querySelector(`.template-card[data-template-id="${selectedTemplateId}"]`);
        if (selectedTemplateCard) {
            // Selecionar o template automaticamente
            selectTemplate(selectedTemplateCard);
            
            // Rolar para o formulário
            document.querySelector('#campaignForm').scrollIntoView({ behavior: 'smooth' });
        }
        @endif
        
        // Limpar o iframe quando o modal for fechado
        const previewModal = document.getElementById('previewModal');
        previewModal.addEventListener('hidden.bs.modal', function() {
            previewFrame.srcdoc = '';
            previewLoading.style.display = 'none';
            document.getElementById('previewContainer').style.display = 'none';
        });

        // Função para atualizar o preview em tempo real
        function updatePreview() {
            const content = contentTextarea.value;
            
            // Se não houver conteúdo, mostrar mensagem
            if (!content.trim()) {
                previewFrame.contentDocument.open();
                previewFrame.contentDocument.write(`
                    <div style="padding: 20px; text-align: center; color: #6c757d;">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>Digite o conteúdo do email para ver o preview</p>
                    </div>
                `);
                previewFrame.contentDocument.close();
                return;
            }
            
            // Atualizar o preview com o conteúdo
            previewFrame.contentDocument.open();
            previewFrame.contentDocument.write(content);
            previewFrame.contentDocument.close();
        }
        
        // Atualizar preview ao carregar a página
        updatePreview();
        
        // Atualizar preview ao clicar no botão
        updatePreviewBtn.addEventListener('click', updatePreview);
        
        // Atualizar preview ao digitar (com debounce)
        let timeout;
        contentTextarea.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(updatePreview, 1000);
        });
        
        // Manipular visualização responsiva
        previewSizeButtons.forEach(button => {
            button.addEventListener('click', function() {
                previewSizeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                const size = this.dataset.size;
                previewWrapper.className = size;
            });
        });
        
        // Manipular seleção de template
        const useTemplateButtons = document.querySelectorAll('.use-template');
        useTemplateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.template-card');
                const templateId = card.dataset.templateId;
                const templateName = card.dataset.name;
                const templateSubject = card.dataset.subject;
                const templateContent = atob(card.dataset.content);
                
                document.getElementById('template_id').value = templateId;
                document.getElementById('name').value = templateName;
                document.getElementById('subject').value = templateSubject;
                document.getElementById('content').value = templateContent;
                
                updatePreview();
            });
        });
    });
</script>
@endpush 