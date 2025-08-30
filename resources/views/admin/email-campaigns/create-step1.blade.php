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

    <div class="row">
        <!-- Templates Pré-definidos -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-templates me-2"></i>
                        Templates Prontos
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Escolha um template para começar rapidamente:</p>
                    
                    @foreach($templates as $template)
                        <div class="template-option mb-3 p-3 border rounded cursor-pointer" 
                             data-template-id="{{ $template['id'] }}"
                             data-name="{{ $template['name'] }}"
                             data-subject="{{ $template['subject'] }}"
                             data-content-raw="{{ e($template['content']) }}">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-file-alt text-primary me-3 mt-1"></i>
                                <div>
                                    <h6 class="mb-1">{{ $template['name'] }}</h6>
                                    <p class="text-muted small mb-0">{{ $template['description'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="template-option mb-3 p-3 border rounded cursor-pointer" 
                         data-template-id="blank"
                         data-name=""
                         data-subject=""
                         data-content-raw="">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-plus text-success me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Template em Branco</h6>
                                <p class="text-muted small mb-0">Comece do zero com um template personalizado</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gerador de Template com IA -->
                    <div class="ai-template-section mt-4 p-3 bg-light rounded">
                        <h6 class="mb-3">
                            <i class="fas fa-robot text-primary me-2"></i>
                            Gerar com IA
                        </h6>
                        <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#aiTemplateModal">
                            <i class="fas fa-magic me-2"></i>
                            Criar Template com ChatGPT
                        </button>
                        <small class="text-muted d-block mt-2">
                            Use inteligência artificial para criar templates profissionais
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulário Principal -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Passo 1: Conteúdo da Campanha</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.email-campaigns.create-step2') }}" method="POST">
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
                        
                        <div class="form-group mb-4">
                            <label for="content" class="form-label">Conteúdo do Email <span class="text-danger">*</span></label>
                            <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" 
                                      rows="20">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="content-error" class="invalid-feedback" style="display: none;">
                                O conteúdo do email é obrigatório.
                            </div>
                            <div class="form-text">
                                <strong>Variáveis disponíveis:</strong> 
                                <code>{{ '{' }}{{ '{' }}nome{{ '}' }}{{ '}' }}</code>, <code>{{ '{' }}{{ '{' }}email{{ '}' }}{{ '}' }}</code>, <code>{{ '{' }}{{ '{' }}telefone{{ '}' }}{{ '}' }}</code>, 
                                <code>{{ '{' }}{{ '{' }}curso{{ '}' }}{{ '}' }}</code>, <code>{{ '{' }}{{ '{' }}modalidade{{ '}' }}{{ '}' }}</code>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
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
</div>
<!-- Modal de Geração de Template com IA -->
<div class="modal fade" id="aiTemplateModal" tabindex="-1" aria-labelledby="aiTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aiTemplateModalLabel">
                    <i class="fas fa-robot text-primary me-2"></i>
                    Gerar Template com ChatGPT
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="aiTemplateForm">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Como funciona:</strong> O ChatGPT criará um template HTML profissional baseado nas suas especificações, incluindo todas as variáveis disponíveis ({{nome}}, {{email}}, {{curso}}, etc.).
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="template_type" class="form-label">Tipo de Template <span class="text-danger">*</span></label>
                            <select class="form-select" id="template_type" name="template_type" required>
                                <option value="">Selecione um tipo</option>
                                <option value="boas-vindas">Boas-vindas</option>
                                <option value="follow-up">Follow-up</option>
                                <option value="promocional">Promocional</option>
                                <option value="informativo">Informativo</option>
                                <option value="convite">Convite para evento</option>
                                <option value="lembrete">Lembrete</option>
                                <option value="agradecimento">Agradecimento</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="target_audience" class="form-label">Público-Alvo</label>
                            <input type="text" class="form-control" id="target_audience" name="target_audience" 
                                   value="estudantes" placeholder="Ex: estudantes, pais, profissionais">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="objective" class="form-label">Objetivo do Email <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="objective" name="objective" rows="3" required
                                  placeholder="Descreva o objetivo principal do email. Ex: 'Dar boas-vindas aos novos alunos e apresentar o curso de Excel Básico, mostrando os benefícios e próximos passos'"></textarea>
                        <div class="form-text">Seja específico sobre o que você quer alcançar com este email.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="additional_instructions" class="form-label">Instruções Adicionais</label>
                        <textarea class="form-control" id="additional_instructions" name="additional_instructions" rows="3"
                                  placeholder="Instruções específicas sobre tom, estilo, elementos visuais, cores, etc."></textarea>
                        <div class="form-text">Opcional: especifique detalhes sobre design, cores, tom de voz, elementos específicos a incluir.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="generateAiTemplateBtn">
                    <i class="fas fa-magic me-2"></i>
                    Gerar Template
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
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
        text-align: center;
    }
    
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }
    
    .step.active .step-circle {
        background-color: #007bff;
        color: white;
    }
    
    .step-label {
        font-size: 12px;
        color: #6c757d;
        white-space: nowrap;
    }
    
    .step.active .step-label {
        color: #007bff;
        font-weight: bold;
    }
    
    .step-line {
        flex: 1;
        height: 2px;
        background-color: #e9ecef;
        margin: 0 15px;
        margin-bottom: 20px;
    }
    
    .template-option {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .template-option:hover {
        background-color: #f8f9fa;
        border-color: #007bff !important;
    }
    
    .template-option.selected {
        background-color: #e7f3ff;
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manipular seleção de templates
        const templateOptions = document.querySelectorAll('.template-option');
        const nameInput = document.getElementById('name');
        const subjectInput = document.getElementById('subject');
        const contentTextarea = document.getElementById('content');
        const templateIdInput = document.getElementById('template_id');
        
        templateOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remover seleção anterior
                templateOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Adicionar seleção atual
                this.classList.add('selected');
                
                // Preencher campos
                const templateId = this.dataset.templateId;
                const templateName = this.dataset.name;
                const templateSubject = this.dataset.subject;
                let templateContent = '';
                if (templateId !== 'blank' && this.dataset.contentRaw) {
                    const textarea = document.createElement('textarea');
                    textarea.innerHTML = this.dataset.contentRaw;
                    templateContent = textarea.value;
                }
                
                templateIdInput.value = templateId;
                
                if (templateId !== 'blank') {
                    nameInput.value = 'Campanha ' + templateName;
                    subjectInput.value = templateSubject;
                    contentTextarea.value = templateContent;
                } else {
                    nameInput.value = '';
                    subjectInput.value = '';
                    contentTextarea.value = '';
                }
                
                // Focar no textarea
                contentTextarea.focus();
            });
        });
        
        // Validação do formulário
        const form = document.querySelector('form');
        const contentError = document.getElementById('content-error');
        
        form.addEventListener('submit', function(e) {
            const content = contentTextarea.value.trim();
            
            if (!content) {
                e.preventDefault();
                
                // Mostrar erro
                contentTextarea.classList.add('is-invalid');
                contentError.style.display = 'block';
                contentTextarea.focus();
                
                // Scroll para o campo
                contentTextarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                // Limpar erro se houver conteúdo
                contentTextarea.classList.remove('is-invalid');
                contentError.style.display = 'none';
            }
        });
        
        // Manipular geração de template com IA
        const generateAiTemplateBtn = document.getElementById('generateAiTemplateBtn');
        const aiTemplateForm = document.getElementById('aiTemplateForm');
        const aiTemplateModal = new bootstrap.Modal(document.getElementById('aiTemplateModal'));
        
        generateAiTemplateBtn.addEventListener('click', async function() {
            const formData = new FormData(aiTemplateForm);
            
            // Validar campos obrigatórios
            if (!formData.get('template_type') || !formData.get('objective')) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                return;
            }
            
            // Mostrar loading
            const originalText = generateAiTemplateBtn.innerHTML;
            generateAiTemplateBtn.disabled = true;
            generateAiTemplateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Gerando...';
            
            try {
                const response = await fetch('{{ route("admin.email-campaigns.generate-ai-template") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Preencher os campos do formulário principal
                    nameInput.value = result.suggested_name;
                    subjectInput.value = result.suggested_subject;
                    contentTextarea.value = result.html;
                    
                    // Fechar modal
                    aiTemplateModal.hide();
                    
                    // Mostrar sucesso
                    showAlert('success', result.message);
                    
                    // Limpar seleção de templates
                    templateOptions.forEach(opt => opt.classList.remove('selected'));
                    templateIdInput.value = 'ai-generated';
                    
                } else {
                    showAlert('error', result.message);
                }
                
            } catch (error) {
                console.error('Erro ao gerar template:', error);
                showAlert('error', 'Erro ao gerar template. Tente novamente.');
            } finally {
                // Restaurar botão
                generateAiTemplateBtn.disabled = false;
                generateAiTemplateBtn.innerHTML = originalText;
            }
        });
        
        // Função para mostrar alertas
        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
            
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${iconClass} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            // Adicionar alert no topo da página
            const container = document.querySelector('.container-fluid');
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Auto-remover após 5 segundos
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    });
</script>
@endsection 