@extends('layouts.admin')

@section('title', 'Criar Template')

@section('page-title', 'Criar Novo Template')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.email-campaigns.templates') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar para Templates
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Formulário -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i>
                        Novo Template
                    </h5>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#aiGenerateModal">
                        <i class="fas fa-robot me-2"></i>
                        Gerar com IA
                    </button>
                </div>
        <div class="card-body">
            <form action="{{ route('admin.email-campaigns.templates.store') }}" method="POST" id="templateForm">
                @csrf
                
                        <div class="row g-3">
                            <div class="col-md-8">
                            <label for="name" class="form-label">Nome do Template <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                            
                            <div class="col-md-4">
                                <label for="category" class="form-label">Categoria <span class="text-danger">*</span></label>
                                <select class="form-select @error('category') is-invalid @enderror" 
                                        id="category" name="category" required>
                                    <option value="">Selecione a categoria</option>
                                    <option value="welcome" {{ old('category') == 'welcome' ? 'selected' : '' }}>Boas-vindas</option>
                                    <option value="followup" {{ old('category') == 'followup' ? 'selected' : '' }}>Follow-up</option>
                                    <option value="promotional" {{ old('category') == 'promotional' ? 'selected' : '' }}>Promocional</option>
                                    <option value="informational" {{ old('category') == 'informational' ? 'selected' : '' }}>Informativo</option>
                                    <option value="invitation" {{ old('category') == 'invitation' ? 'selected' : '' }}>Convite</option>
                                    <option value="reminder" {{ old('category') == 'reminder' ? 'selected' : '' }}>Lembrete</option>
                                    <option value="thank_you" {{ old('category') == 'thank_you' ? 'selected' : '' }}>Agradecimento</option>
                                    <option value="custom" {{ old('category') == 'custom' ? 'selected' : '' }}>Personalizado</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                    </div>
                    
                    <div class="col-md-6">
                            <label for="type" class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" name="type" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="marketing" {{ old('type') == 'marketing' ? 'selected' : '' }}>Marketing</option>
                                    <option value="transactional" {{ old('type') == 'transactional' ? 'selected' : '' }}>Transacional</option>
                                    <option value="newsletter" {{ old('type') == 'newsletter' ? 'selected' : '' }}>Newsletter</option>
                                    <option value="automation" {{ old('type') == 'automation' ? 'selected' : '' }}>Automação</option>
                                    <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>Personalizado</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                            
                            <div class="col-md-6">
                                <label for="subject" class="form-label">Assunto do Email <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                       id="subject" name="subject" value="{{ old('subject') }}" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                </div>
                
                            <div class="col-12">
                                <label for="description" class="form-label">Descrição <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="2" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Breve descrição do propósito e uso do template.</div>
                </div>
                
                            <div class="col-12">
                            <label for="content" class="form-label">Conteúdo HTML <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('content') is-invalid @enderror" 
                                          id="content" name="content" rows="15" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <strong>Variáveis disponíveis:</strong> 
                                    <code>{{'{'}}{{'{'}}nome{{'}'}}{{'}'}}</code>, <code>{{'{'}}{{'{'}}email{{'}'}}{{'}'}}</code>, <code>{{'{'}}{{'{'}}telefone{{'}'}}{{'}'}}</code>, 
                                    <code>{{'{'}}{{'{'}}curso{{'}'}}{{'}'}}</code>, <code>{{'{'}}{{'{'}}modalidade{{'}'}}{{'}'}}</code>, <code>{{'{'}}{{'{'}}campanha{{'}'}}{{'}'}}</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_ai_generated" name="is_ai_generated" value="1">
                                <label class="form-check-label" for="is_ai_generated">
                                    <i class="fas fa-robot me-1"></i>
                                    Marcar como gerado por IA
                                </label>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-info" id="previewBtn">
                                    <i class="fas fa-eye me-2"></i>
                                    Visualizar
                                </button>
                                <a href="{{ route('admin.email-campaigns.templates') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Salvar Template
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Painel Lateral -->
        <div class="col-md-4">
            <!-- Dicas -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Dicas para Templates
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Use variáveis para personalizar emails
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Mantenha o design responsivo
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Teste em diferentes clientes de email
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Use CSS inline para melhor compatibilidade
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            Inclua texto alternativo para imagens
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Templates Base -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-code me-2"></i>
                        Templates Base
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm load-base-template" data-template="basic">
                            <i class="fas fa-file-alt me-2"></i>
                            HTML Básico
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm load-base-template" data-template="modern">
                            <i class="fas fa-magic me-2"></i>
                            Moderno
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm load-base-template" data-template="newsletter">
                            <i class="fas fa-newspaper me-2"></i>
                            Newsletter
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm load-base-template" data-template="promo">
                            <i class="fas fa-percentage me-2"></i>
                            Promocional
                        </button>
                    </div>
                </div>
            </div>
                            </div>
                        </div>
                    </div>
                    
<!-- Modal de Preview -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview do Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="bg-light p-3">
                    <div class="btn-group mb-3" role="group">
                                    <button type="button" class="btn btn-outline-secondary preview-size active" data-size="desktop">
                            <i class="fas fa-desktop me-2"></i>Desktop
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary preview-size" data-size="tablet">
                            <i class="fas fa-tablet-alt me-2"></i>Tablet
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary preview-size" data-size="mobile">
                            <i class="fas fa-mobile-alt me-2"></i>Mobile
                                    </button>
                                </div>
                    <div id="previewWrapper" class="mx-auto desktop">
                                    <iframe id="previewFrame" class="w-100 border-0" style="height: 600px;"></iframe>
                                </div>
                            </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Geração IA -->
<div class="modal fade" id="aiGenerateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-robot me-2"></i>
                    Gerar Template com IA
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="aiGenerateForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Template</label>
                            <select class="form-select" name="template_type" required>
                                <option value="">Selecione o tipo</option>
                                <option value="welcome">Boas-vindas</option>
                                <option value="followup">Follow-up</option>
                                <option value="promotional">Promocional</option>
                                <option value="informational">Informativo</option>
                                <option value="invitation">Convite</option>
                                <option value="reminder">Lembrete</option>
                                <option value="thank_you">Agradecimento</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Público-alvo</label>
                            <input type="text" class="form-control" name="target_audience" 
                                   placeholder="Ex: estudantes interessados em EJA" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Objetivo do Email</label>
                            <textarea class="form-control" name="objective" rows="3" 
                                      placeholder="Descreva qual é o objetivo principal deste email..." required></textarea>
                            </div>
                        <div class="col-12">
                            <label class="form-label">Instruções Adicionais (opcional)</label>
                            <textarea class="form-control" name="additional_instructions" rows="2" 
                                      placeholder="Instruções específicas, tom de voz, informações adicionais..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="generateAiBtn">
                        <i class="fas fa-robot me-2"></i>
                        Gerar Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #previewWrapper {
        transition: all 0.3s ease;
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
    
    .preview-size.active {
        background-color: var(--bs-primary) !important;
        color: white !important;
        border-color: var(--bs-primary) !important;
    }
    
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .form-control, .form-select {
        border-radius: 6px;
    }
    
    .btn {
        border-radius: 6px;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentTextarea = document.getElementById('content');
    const previewBtn = document.getElementById('previewBtn');
    const previewFrame = document.getElementById('previewFrame');
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    
    // Templates base
    const baseTemplates = {
        basic: `<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: white; padding: 40px; border-radius: 10px;">
                            <h1 style="color: #333; margin-bottom: 20px;">Olá, @{{nome}}!</h1>
            <p style="margin-bottom: 20px;">Seu conteúdo aqui...</p>
            <p style="margin-top: 30px; color: #666; font-size: 14px;">Atenciosamente,<br>Equipe</p>
        </div>
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 12px;">
            <p>Este email foi enviado para @{{email}}</p>
        </div>
    </div>
</body>
</html>`,
        
        modern: `<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Moderno</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 40px 20px;">
        <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; text-align: center;">
                <h1 style="color: white; margin: 0; font-size: 28px; font-weight: 300;">Olá, @{{nome}}!</h1>
            </div>
            <div style="padding: 40px;">
                <p style="font-size: 16px; line-height: 1.6; margin-bottom: 20px;">Seu conteúdo aqui...</p>
                <div style="text-align: center; margin: 30px 0;">
                    <a href="#" style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 25px; font-weight: bold;">
                        Botão de Ação
                    </a>
                </div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 20px; color: rgba(255,255,255,0.8); font-size: 12px;">
            <p>Este email foi enviado para @{{email}}</p>
        </div>
    </div>
</body>
</html>`,
        
        newsletter: `<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f8f9fa;">
    <div style="max-width: 600px; margin: 0 auto;">
        <!-- Header -->
        <div style="background-color: #343a40; color: white; padding: 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;">Newsletter</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.8;">Fique por dentro das novidades</p>
        </div>
        
        <!-- Content -->
        <div style="background-color: white; padding: 30px;">
            <h2 style="color: #343a40; margin-top: 0;">Olá, @{{nome}}!</h2>
            
            <!-- Article -->
            <div style="border-bottom: 1px solid #e9ecef; padding-bottom: 20px; margin-bottom: 20px;">
                <h3 style="color: #495057; margin-bottom: 10px;">Título do Artigo</h3>
                <p style="line-height: 1.6; margin-bottom: 15px;">Conteúdo do artigo aqui...</p>
                <a href="#" style="color: #007bff; text-decoration: none; font-weight: bold;">Leia mais →</a>
            </div>
            
            <!-- Footer -->
            <div style="text-align: center; padding-top: 20px; border-top: 1px solid #e9ecef;">
                <p style="color: #6c757d; font-size: 14px; margin: 0;">
                    Enviado para @{{email}} | <a href="#" style="color: #6c757d;">Descadastrar</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>`,
        
        promo: `<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oferta Especial</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #fff3cd;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: white; border-radius: 10px; overflow: hidden; border: 3px solid #ffc107;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); padding: 30px; text-align: center;">
                <h1 style="color: white; margin: 0; font-size: 32px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                    🎉 OFERTA ESPECIAL! 🎉
                </h1>
            </div>
            
            <!-- Content -->
            <div style="padding: 40px;">
                <h2 style="color: #343a40; text-align: center; margin-bottom: 20px;">Olá, @{{nome}}!</h2>
                
                <div style="background-color: #fff3cd; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0;">
                    <h3 style="color: #856404; margin: 0 0 10px 0; font-size: 28px;">50% OFF</h3>
                    <p style="color: #856404; margin: 0; font-size: 16px;">Por tempo limitado!</p>
                </div>
                
                <p style="text-align: center; margin: 30px 0;">
                    <a href="#" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; text-decoration: none; border-radius: 50px; font-weight: bold; font-size: 18px; text-transform: uppercase;">
                        Aproveitar Agora!
                    </a>
                </p>
                
                <p style="text-align: center; color: #6c757d; font-size: 14px; margin-top: 30px;">
                    Oferta válida até @{{data_limite}} ou enquanto durarem os estoques.
                </p>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px; color: #6c757d; font-size: 12px;">
            <p>Este email foi enviado para @{{email}}</p>
        </div>
    </div>
</body>
</html>`
    };
    
    // Carregar templates base
    document.querySelectorAll('.load-base-template').forEach(button => {
        button.addEventListener('click', function() {
            const templateType = this.dataset.template;
            if (baseTemplates[templateType]) {
                contentTextarea.value = baseTemplates[templateType];
                showAlert('success', 'Template base carregado com sucesso!');
            }
        });
    });
    
    // Preview do template
    previewBtn.addEventListener('click', function() {
        const content = contentTextarea.value;
        if (!content.trim()) {
            showAlert('warning', 'Por favor, adicione conteúdo ao template antes de visualizar.');
            return;
        }
        
        // Substituir variáveis por dados de exemplo
        const previewContent = content
            .replace(/\{\{nome\}\}/g, 'João Silva')
            .replace(/\{\{email\}\}/g, 'joao.silva@email.com')
            .replace(/\{\{telefone\}\}/g, '(11) 99999-9999')
            .replace(/\{\{curso\}\}/g, 'Ensino Médio')
            .replace(/\{\{modalidade\}\}/g, 'EJA')
            .replace(/\{\{campanha\}\}/g, 'Campanha de Teste')
            .replace(/\{\{data_limite\}\}/g, '31/12/2024');
        
        previewFrame.contentDocument.open();
        previewFrame.contentDocument.write(previewContent);
        previewFrame.contentDocument.close();
        
        previewModal.show();
    });
    
    // Visualização responsiva
    document.querySelectorAll('.preview-size').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.preview-size').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const size = this.dataset.size;
            document.getElementById('previewWrapper').className = 'mx-auto ' + size;
        });
    });
    
    // Geração de template com IA
    const aiGenerateForm = document.getElementById('aiGenerateForm');
    const generateAiBtn = document.getElementById('generateAiBtn');
    
    aiGenerateForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        generateAiBtn.disabled = true;
        generateAiBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Gerando...';
        
        try {
            const formData = new FormData(this);
            
            const response = await fetch('{{ route("admin.email-campaigns.templates.generate-ai") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Preencher formulário com dados gerados
                document.getElementById('name').value = result.template.name;
                document.getElementById('subject').value = result.template.subject;
                document.getElementById('content').value = result.template.content;
                document.getElementById('is_ai_generated').checked = true;
                
                // Fechar modal
                bootstrap.Modal.getInstance(document.getElementById('aiGenerateModal')).hide();
                
                showAlert('success', 'Template gerado com sucesso! Revise e salve quando estiver pronto.');
            } else {
                showAlert('danger', result.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            showAlert('danger', 'Erro ao gerar template com IA.');
        } finally {
            generateAiBtn.disabled = false;
            generateAiBtn.innerHTML = '<i class="fas fa-robot me-2"></i>Gerar Template';
        }
    });
    
    // Função para mostrar alertas
    function showAlert(type, message) {
        const alertsContainer = document.querySelector('.container-fluid');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertsContainer.insertBefore(alert, alertsContainer.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
});
</script>
@endpush 