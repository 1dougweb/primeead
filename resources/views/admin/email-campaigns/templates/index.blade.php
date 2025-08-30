@extends('layouts.admin')

@section('title', 'Galeria de Templates')

@section('page-title', 'Galeria de Templates')

@section('page-actions')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#aiGenerateModal">
            <i class="fas fa-robot me-2"></i>
            Gerar com IA
        </button>
        <a href="{{ route('admin.email-campaigns.templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Novo Template
        </a>
        <a href="{{ route('admin.email-campaigns.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar para Campanhas
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

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Categoria</label>
                            <select class="form-select" id="filterCategory">
                                <option value="">Todas as categorias</option>
                                <option value="welcome">Boas-vindas</option>
                                <option value="followup">Follow-up</option>
                                <option value="promotional">Promocional</option>
                                <option value="informational">Informativo</option>
                                <option value="invitation">Convite</option>
                                <option value="reminder">Lembrete</option>
                                <option value="thank_you">Agradecimento</option>
                                <option value="custom">Personalizado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" id="filterType">
                                <option value="">Todos os tipos</option>
                                <option value="marketing">Marketing</option>
                                <option value="transactional">Transacional</option>
                                <option value="newsletter">Newsletter</option>
                                <option value="automation">Automação</option>
                                <option value="custom">Personalizado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Origem</label>
                            <select class="form-select" id="filterOrigin">
                                <option value="">Todas as origens</option>
                                <option value="saved">Templates Salvos</option>
                                <option value="predefined">Pré-definidos</option>
                                <option value="ai">Gerado por IA</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pesquisar</label>
                            <input type="text" class="form-control" id="searchTemplates" placeholder="Nome do template...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Salvos -->
    @if($savedTemplates->count() > 0)
    <div class="row mb-4" id="savedTemplatesSection">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="fas fa-save me-2 text-primary"></i>
                Templates Salvos
                <span class="badge bg-primary ms-2">{{ $savedTemplates->count() }}</span>
            </h5>
        </div>
        <div class="row g-4" id="savedTemplatesContainer">
            @foreach($savedTemplates as $template)
                <div class="col-md-4 template-card" 
                     data-category="{{ $template->category }}" 
                     data-type="{{ $template->type }}" 
                     data-origin="saved"
                     data-name="{{ strtolower($template->name) }}">
                    <div class="card h-100 template-item">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $template->name }}</h6>
                            <div class="d-flex align-items-center gap-2">
                                @if($template->is_ai_generated)
                                    <span class="badge bg-info" title="Gerado por IA">
                                        <i class="fas fa-robot"></i>
                                    </span>
                                @endif
                                {!! $template->category_badge !!}
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <p class="text-muted mb-2">{{ $template->description }}</p>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="{{ $template->type_icon }} me-1"></i>
                                    {{ ucfirst($template->type) }}
                                </small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-primary btn-sm preview-template" 
                                        data-template-id="{{ $template->id }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#previewModal">
                                    <i class="fas fa-eye me-2"></i>
                                    Visualizar
                                </button>
                                <div class="btn-group">
                                    <button class="btn btn-outline-success btn-sm use-template"
                                            data-template-id="{{ $template->id }}">
                                        <i class="fas fa-check me-1"></i>
                                        Usar
                                    </button>
                                    <a href="{{ route('admin.email-campaigns.templates.edit', $template->id) }}" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm delete-template" 
                                            data-template-id="{{ $template->id }}"
                                            data-template-name="{{ $template->name }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                Criado em {{ $template->created_at->format('d/m/Y H:i') }}
                                @if($template->creator)
                                    por {{ $template->creator->name }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Templates Pré-definidos -->
    <div class="row" id="predefinedTemplatesSection">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="fas fa-palette me-2 text-secondary"></i>
                Templates Pré-definidos
                <span class="badge bg-secondary ms-2">{{ count($predefinedTemplates) }}</span>
            </h5>
        </div>
        <div class="row g-4" id="predefinedTemplatesContainer">
            @foreach($predefinedTemplates as $template)
                <div class="col-md-4 template-card" 
                     data-category="{{ $template['category'] }}" 
                     data-type="{{ $template['type'] }}" 
                     data-origin="predefined"
                     data-name="{{ strtolower($template['name']) }}">
                    <div class="card h-100 template-item">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $template['name'] }}</h6>
                            <span class="badge bg-secondary">Pré-definido</span>
                        </div>
                        
                        <!-- Thumbnail -->
                        <div class="template-preview position-relative">
                            <img src="{{ asset($template['thumbnail']) }}" 
                                 class="img-fluid w-100 h-100 object-fit-cover" 
                                 alt="{{ $template['name'] }}"
                                 onerror="this.src='{{ asset('assets/images/no-data.svg') }}'">
                        </div>
                        
                        <div class="card-body">
                            <p class="text-muted mb-3">{{ $template['description'] }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-primary btn-sm preview-template" 
                                        data-template-id="{{ $template['id'] }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#previewModal">
                                    <i class="fas fa-eye me-2"></i>
                                    Visualizar
                                </button>
                                <div class="btn-group">
                                    <button class="btn btn-outline-success btn-sm use-template"
                                            data-template-id="{{ $template['id'] }}">
                                        <i class="fas fa-check me-1"></i>
                                        Usar
                                    </button>
                                    <a href="{{ route('admin.email-campaigns.templates.edit', $template['id']) }}" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Mensagem quando não há resultados -->
    <div class="row" id="noResultsMessage" style="display: none;">
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum template encontrado</h5>
                <p class="text-muted">Tente ajustar os filtros ou criar um novo template.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Preview -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview do Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="bg-light p-3">
                    <div id="previewWrapper" class="mx-auto desktop">
                        <iframe id="previewFrame" class="w-100 border-0" style="height: 600px;"></iframe>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary use-template-from-preview">
                    <i class="fas fa-check me-2"></i>
                    Usar Este Template
                </button>
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

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o template <strong id="deleteTemplateName"></strong>?</p>
                <p class="text-danger">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>
                    Excluir Template
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .template-card {
        transition: all 0.3s ease;
    }
    
    .template-item {
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }
    
    .template-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: #007bff;
    }
    
    .template-preview {
        height: 200px;
        overflow: hidden;
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    
    .template-preview img {
        object-fit: cover;
        width: 100%;
        height: 100%;
    }
    
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
    // Variáveis globais
    let currentTemplateId = null;
    
    // Elementos
    const previewFrame = document.getElementById('previewFrame');
    const filterCategory = document.getElementById('filterCategory');
    const filterType = document.getElementById('filterType');
    const filterOrigin = document.getElementById('filterOrigin');
    const searchInput = document.getElementById('searchTemplates');
    
    // Filtros e busca
    function applyFilters() {
        const category = filterCategory.value.toLowerCase();
        const type = filterType.value.toLowerCase();
        const origin = filterOrigin.value.toLowerCase();
        const search = searchInput.value.toLowerCase();
        
        const cards = document.querySelectorAll('.template-card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            const cardCategory = card.dataset.category.toLowerCase();
            const cardType = card.dataset.type.toLowerCase();
            const cardOrigin = card.dataset.origin.toLowerCase();
            const cardName = card.dataset.name.toLowerCase();
            
            const categoryMatch = !category || cardCategory === category;
            const typeMatch = !type || cardType === type;
            const originMatch = !origin || cardOrigin === origin;
            const searchMatch = !search || cardName.includes(search);
            
            if (categoryMatch && typeMatch && originMatch && searchMatch) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Mostrar/ocultar seções vazias
        toggleSectionVisibility('savedTemplatesSection', 'savedTemplatesContainer');
        toggleSectionVisibility('predefinedTemplatesSection', 'predefinedTemplatesContainer');
        
        // Mostrar mensagem se não há resultados
        document.getElementById('noResultsMessage').style.display = visibleCount === 0 ? 'block' : 'none';
    }
    
    function toggleSectionVisibility(sectionId, containerId) {
        const section = document.getElementById(sectionId);
        const container = document.getElementById(containerId);
        if (section && container) {
            const visibleCards = container.querySelectorAll('.template-card:not([style*="display: none"])');
            section.style.display = visibleCards.length > 0 ? 'block' : 'none';
        }
    }
    
    // Event listeners para filtros
    filterCategory.addEventListener('change', applyFilters);
    filterType.addEventListener('change', applyFilters);
    filterOrigin.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);
    
    // Manipular visualização responsiva
    const previewSizeButtons = document.querySelectorAll('.preview-size');
    const previewWrapper = document.getElementById('previewWrapper');
    
    previewSizeButtons.forEach(button => {
        button.addEventListener('click', function() {
            previewSizeButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const size = this.dataset.size;
            previewWrapper.className = 'mx-auto ' + size;
        });
    });
    
    // Manipular preview do template
    const previewButtons = document.querySelectorAll('.preview-template');
    
    previewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const templateId = this.dataset.templateId;
            currentTemplateId = templateId;
            
            const url = "{{ route('admin.email-campaigns.templates.get', ['id' => ':id']) }}".replace(':id', templateId);
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    previewFrame.contentDocument.open();
                    previewFrame.contentDocument.write(data.content);
                    previewFrame.contentDocument.close();
                })
                .catch(error => {
                    console.error('Erro ao carregar preview:', error);
                    previewFrame.contentDocument.open();
                    previewFrame.contentDocument.write('<p>Erro ao carregar preview do template.</p>');
                    previewFrame.contentDocument.close();
                });
        });
    });
    
    // Manipular seleção do template
    const useTemplateButtons = document.querySelectorAll('.use-template, .use-template-from-preview');
    
    useTemplateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const templateId = this.classList.contains('use-template-from-preview') 
                ? currentTemplateId 
                : this.dataset.templateId;
            
            window.location.href = `{{ route('admin.email-campaigns.create') }}?template=${templateId}`;
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
                // Fechar modal
                bootstrap.Modal.getInstance(document.getElementById('aiGenerateModal')).hide();
                
                // Mostrar mensagem de sucesso
                showAlert('success', result.message);
                
                // Recarregar página para mostrar novo template
                setTimeout(() => window.location.reload(), 1500);
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
    
    // Exclusão de templates
    const deleteButtons = document.querySelectorAll('.delete-template');
    const deleteModal = document.getElementById('deleteModal');
    const deleteTemplateName = document.getElementById('deleteTemplateName');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const templateId = this.dataset.templateId;
            const templateName = this.dataset.templateName;
            
            deleteTemplateName.textContent = templateName;
            confirmDeleteBtn.dataset.templateId = templateId;
            
            new bootstrap.Modal(deleteModal).show();
        });
    });
    
    confirmDeleteBtn.addEventListener('click', async function() {
        const templateId = this.dataset.templateId;
        
        try {
            const response = await fetch(`{{ route('admin.email-campaigns.templates.destroy', ['id' => ':id']) }}`.replace(':id', templateId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                bootstrap.Modal.getInstance(deleteModal).hide();
                showAlert('success', 'Template excluído com sucesso!');
                
                // Remover card do template
                document.querySelector(`[data-template-id="${templateId}"]`).closest('.template-card').remove();
                applyFilters(); // Reaplica filtros
            } else {
                showAlert('danger', 'Erro ao excluir template.');
            }
        } catch (error) {
            console.error('Erro:', error);
            showAlert('danger', 'Erro ao excluir template.');
        }
    });
    
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