@extends('layouts.admin')

@section('title', 'Editar Template')

@section('page-title', 'Editar Template')

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
                        <i class="fas fa-edit me-2"></i>
                        {{ is_object($template) ? $template->name : $template['name'] }}
                        
                        @if(isset($template->is_predefined) && $template->is_predefined)
                            <span class="badge bg-secondary ms-2">Pré-definido</span>
                        @elseif(isset($template->is_ai_generated) && $template->is_ai_generated)
                            <span class="badge bg-info ms-2">
                                <i class="fas fa-robot"></i> IA
                            </span>
                        @elseif(is_object($template))
                            <span class="badge bg-primary ms-2">Personalizado</span>
                        @else
                            <span class="badge bg-secondary ms-2">Pré-definido</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($template->is_predefined) && $template->is_predefined)
                        <!-- Template pré-definido - somente visualização -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Este é um template pré-definido e não pode ser editado. Você pode visualizar seu conteúdo abaixo.
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nome do Template</label>
                                <input type="text" class="form-control" value="{{ $template->name }}" readonly>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Categoria</label>
                                <input type="text" class="form-control" value="{{ ucfirst($template->category ?? 'N/A') }}" readonly>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Tipo</label>
                                <input type="text" class="form-control" value="{{ ucfirst($template->type ?? 'N/A') }}" readonly>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Assunto do Email</label>
                                <input type="text" class="form-control" value="{{ $template->subject }}" readonly>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Descrição</label>
                                <textarea class="form-control" rows="2" readonly>{{ $template->description }}</textarea>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Conteúdo HTML</label>
                                <textarea class="form-control" id="content" rows="15" readonly>{{ $template->content }}</textarea>
                            </div>
                        </div>
                    @else
                        <!-- Template editável -->
                        <form action="{{ route('admin.email-campaigns.templates.update', is_object($template) ? $template->id : $template['id']) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="name" class="form-label">Nome do Template <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', is_object($template) ? $template->name : $template['name']) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="category" class="form-label">Categoria <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category') is-invalid @enderror" 
                                            id="category" name="category" required>
                                        <option value="">Selecione a categoria</option>
                                        <option value="welcome" {{ old('category', is_object($template) ? $template->category : 'welcome') == 'welcome' ? 'selected' : '' }}>Boas-vindas</option>
                                        <option value="followup" {{ old('category', is_object($template) ? $template->category : 'followup') == 'followup' ? 'selected' : '' }}>Follow-up</option>
                                        <option value="promotional" {{ old('category', is_object($template) ? $template->category : 'promotional') == 'promotional' ? 'selected' : '' }}>Promocional</option>
                                        <option value="informational" {{ old('category', is_object($template) ? $template->category : 'informational') == 'informational' ? 'selected' : '' }}>Informativo</option>
                                        <option value="invitation" {{ old('category', is_object($template) ? $template->category : 'invitation') == 'invitation' ? 'selected' : '' }}>Convite</option>
                                        <option value="reminder" {{ old('category', is_object($template) ? $template->category : 'reminder') == 'reminder' ? 'selected' : '' }}>Lembrete</option>
                                        <option value="thank_you" {{ old('category', is_object($template) ? $template->category : 'thank_you') == 'thank_you' ? 'selected' : '' }}>Agradecimento</option>
                                        <option value="custom" {{ old('category', is_object($template) ? $template->category : 'custom') == 'custom' ? 'selected' : '' }}>Personalizado</option>
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
                                        <option value="marketing" {{ old('type', is_object($template) ? $template->type : 'marketing') == 'marketing' ? 'selected' : '' }}>Marketing</option>
                                        <option value="transactional" {{ old('type', is_object($template) ? $template->type : 'transactional') == 'transactional' ? 'selected' : '' }}>Transacional</option>
                                        <option value="newsletter" {{ old('type', is_object($template) ? $template->type : 'newsletter') == 'newsletter' ? 'selected' : '' }}>Newsletter</option>
                                        <option value="automation" {{ old('type', is_object($template) ? $template->type : 'automation') == 'automation' ? 'selected' : '' }}>Automação</option>
                                        <option value="custom" {{ old('type', is_object($template) ? $template->type : 'custom') == 'custom' ? 'selected' : '' }}>Personalizado</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                            </div>
                            
                            <div class="col-md-6">
                                    <label for="subject" class="form-label">Assunto do Email <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                           id="subject" name="subject" value="{{ old('subject', is_object($template) ? $template->subject : $template['subject']) }}" required>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                        </div>
                        
                                <div class="col-12">
                            <label for="description" class="form-label">Descrição <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="2" required>{{ old('description', is_object($template) ? $template->description : $template['description']) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                                <div class="col-12">
                            <label for="content" class="form-label">Conteúdo HTML <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                              id="content" name="content" rows="15" required>{{ old('content', is_object($template) ? $template->content : $template['content']) }}</textarea>
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
                                @if(is_object($template) && isset($template->is_ai_generated) && $template->is_ai_generated)
                                    <div class="text-muted">
                                        <i class="fas fa-robot me-1"></i>
                                        Template gerado por IA
                                    </div>
                                @elseif(is_object($template) && isset($template->created_at))
                                    <div class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Criado em {{ $template->created_at->format('d/m/Y H:i') }}
                                        @if($template->creator)
                                            por {{ $template->creator->name }}
                                        @endif
                                    </div>
                                @else
                                    <div></div>
                                @endif
                                
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
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Painel Lateral -->
        <div class="col-md-4">
            <!-- Informações do Template -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informações do Template
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Categoria:</dt>
                        <dd class="col-sm-8">
                            @if(is_object($template) && isset($template->category))
                                {!! $template->category_badge !!}
                            @else
                                <span class="badge bg-secondary">N/A</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Tipo:</dt>
                        <dd class="col-sm-8">
                            @if(is_object($template) && isset($template->type))
                                <i class="{{ $template->type_icon }} me-1"></i>
                                {{ ucfirst($template->type) }}
                            @else
                                <i class="fas fa-envelope me-1"></i>
                                N/A
                            @endif
                        </dd>
                        
                        @if(is_object($template) && isset($template->updated_at))
                            <dt class="col-sm-4">Atualizado:</dt>
                            <dd class="col-sm-8">{{ $template->updated_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
            
            <!-- Dicas -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Dicas de Edição
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Use o preview para testar suas alterações
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Variáveis são substituídas automaticamente
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            CSS inline funciona melhor em emails
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            Teste em diferentes dispositivos
                        </li>
                    </ul>
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
                @if(!(isset($template->is_predefined) && $template->is_predefined))
                    <button type="button" class="btn btn-success" id="useTemplateBtn">
                        <i class="fas fa-paper-plane me-2"></i>
                        Usar em Nova Campanha
                    </button>
                @endif
            </div>
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
    
    .badge.bg-purple {
        background-color: #6f42c1 !important;
    }

    .badge.bg-orange {
        background-color: #fd7e14 !important;
    }

    .badge.bg-pink {
        background-color: #d63384 !important;
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
    
    // Preview do template
    if (previewBtn) {
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
                .replace(/\{\{campanha\}\}/g, 'Campanha de Teste');
            
            previewFrame.contentDocument.open();
            previewFrame.contentDocument.write(previewContent);
            previewFrame.contentDocument.close();
        
            previewModal.show();
        });
    }
    
    // Visualização responsiva
    document.querySelectorAll('.preview-size').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.preview-size').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const size = this.dataset.size;
            document.getElementById('previewWrapper').className = 'mx-auto ' + size;
        });
    });
    
    // Usar template em nova campanha
    const useTemplateBtn = document.getElementById('useTemplateBtn');
    if (useTemplateBtn) {
        useTemplateBtn.addEventListener('click', function() {
            const templateId = '{{ is_object($template) ? $template->id : $template["id"] }}';
            window.location.href = `{{ route('admin.email-campaigns.create') }}?template=${templateId}`;
        });
    }
    
    // Função para mostrar alertas
    function showAlert(type, message) {
        const alertsContainer = document.querySelector('.container-fluid');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
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