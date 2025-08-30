@extends('layouts.admin')

@section('title', 'Galeria de Templates')

@section('page-title', 'Galeria de Templates de Email')

@section('page-actions')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
            <i class="fas fa-plus me-2"></i>
            Novo Template
        </button>
        <a href="{{ route('admin.email-campaigns.create') }}" class="btn btn-outline-primary">
            <i class="fas fa-envelope me-2"></i>
            Nova Campanha
        </a>
        <a href="{{ route('admin.email-campaigns.index') }}" class="btn btn-outline-secondary">
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
    
    <!-- Filtros e Pesquisa -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchTemplate" placeholder="Pesquisar templates...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="categoryFilter">
                        <option value="all">Todas as Categorias</option>
                        <option value="welcome">Boas-vindas</option>
                        <option value="followup">Follow-up</option>
                        <option value="promotional">Promocional</option>
                        <option value="notification">Notificações</option>
                        <option value="custom">Personalizados</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="sortTemplates">
                        <option value="newest">Mais recentes</option>
                        <option value="oldest">Mais antigos</option>
                        <option value="name_asc">Nome (A-Z)</option>
                        <option value="name_desc">Nome (Z-A)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Galeria de Templates -->
    <div class="row" id="templateGallery">
        <!-- Template em Branco (Sempre presente) -->
        <div class="col-md-4 col-lg-3 mb-4 template-item" data-category="custom">
            <div class="card h-100 template-card">
                <div class="template-preview bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="fas fa-plus fa-3x text-muted"></i>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Template em Branco</h5>
                    <p class="card-text text-muted">Comece do zero com um template personalizado</p>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.email-campaigns.create') }}?template=blank" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="fas fa-plus me-1"></i>
                            Usar Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Template de Boas-vindas -->
        <div class="col-md-4 col-lg-3 mb-4 template-item" data-category="welcome">
            <div class="card h-100 template-card">
                <div class="template-preview" style="height: 200px; overflow: hidden; position: relative;">
                    <div class="template-badge badge bg-primary position-absolute" style="top: 10px; right: 10px;">
                        Boas-vindas
                    </div>
                    <img src="{{ asset('assets/images/templates/welcome-template.jpg') }}" class="card-img-top" alt="Template de Boas-vindas">
                </div>
                <div class="card-body">
                    <h5 class="card-title">Boas-vindas</h5>
                    <p class="card-text text-muted small">Template de boas-vindas para novos leads</p>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1 preview-template" data-bs-toggle="modal" data-bs-target="#previewModal" data-template-id="welcome">
                            <i class="fas fa-eye me-1"></i>
                            Preview
                        </button>
                        <a href="{{ route('admin.email-campaigns.create') }}?template=welcome" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="fas fa-plus me-1"></i>
                            Usar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Template de Follow-up -->
        <div class="col-md-4 col-lg-3 mb-4 template-item" data-category="followup">
            <div class="card h-100 template-card">
                <div class="template-preview" style="height: 200px; overflow: hidden; position: relative;">
                    <div class="template-badge badge bg-success position-absolute" style="top: 10px; right: 10px;">
                        Follow-up
                    </div>
                    <img src="{{ asset('assets/images/templates/followup-template.jpg') }}" class="card-img-top" alt="Template de Follow-up">
                </div>
                <div class="card-body">
                    <h5 class="card-title">Follow-up</h5>
                    <p class="card-text text-muted small">Template para acompanhamento de leads</p>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1 preview-template" data-bs-toggle="modal" data-bs-target="#previewModal" data-template-id="followup">
                            <i class="fas fa-eye me-1"></i>
                            Preview
                        </button>
                        <a href="{{ route('admin.email-campaigns.create') }}?template=followup" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="fas fa-plus me-1"></i>
                            Usar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Template Promocional -->
        <div class="col-md-4 col-lg-3 mb-4 template-item" data-category="promotional">
            <div class="card h-100 template-card">
                <div class="template-preview" style="height: 200px; overflow: hidden; position: relative;">
                    <div class="template-badge badge bg-danger position-absolute" style="top: 10px; right: 10px;">
                        Promocional
                    </div>
                    <img src="{{ asset('assets/images/templates/promo-template.jpg') }}" class="card-img-top" alt="Template Promocional">
                </div>
                <div class="card-body">
                    <h5 class="card-title">Promocional</h5>
                    <p class="card-text text-muted small">Template para ofertas e promoções</p>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1 preview-template" data-bs-toggle="modal" data-bs-target="#previewModal" data-template-id="promotional">
                            <i class="fas fa-eye me-1"></i>
                            Preview
                        </button>
                        <a href="{{ route('admin.email-campaigns.create') }}?template=promotional" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="fas fa-plus me-1"></i>
                            Usar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Template Simples -->
        <div class="col-md-4 col-lg-3 mb-4 template-item" data-category="notification">
            <div class="card h-100 template-card">
                <div class="template-preview" style="height: 200px; overflow: hidden; position: relative;">
                    <div class="template-badge badge bg-info position-absolute" style="top: 10px; right: 10px;">
                        Notificação
                    </div>
                    <img src="{{ asset('assets/images/templates/simple-template.jpg') }}" class="card-img-top" alt="Template Simples">
                </div>
                <div class="card-body">
                    <h5 class="card-title">Simples</h5>
                    <p class="card-text text-muted small">Template simples e limpo</p>
                </div>
                <div class="card-footer bg-white border-0">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-grow-1 preview-template" data-bs-toggle="modal" data-bs-target="#previewModal" data-template-id="simple">
                            <i class="fas fa-eye me-1"></i>
                            Preview
                        </button>
                        <a href="{{ route('admin.email-campaigns.create') }}?template=simple" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="fas fa-plus me-1"></i>
                            Usar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mensagem de Nenhum Template -->
    <div id="noTemplatesMessage" class="text-center py-5 d-none">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <h3 class="text-muted">Nenhum template encontrado</h3>
        <p class="mb-4">Tente ajustar seus filtros ou criar um novo template.</p>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
            <i class="fas fa-plus me-2"></i>
            Criar Novo Template
        </button>
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
                <!-- Loading Spinner -->
                <div id="previewLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando template...</p>
                </div>
                
                <!-- Preview Frame -->
                <iframe id="previewFrame" srcdoc="" frameborder="0" style="width: 100%; height: 600px; display: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" id="useTemplateBtn" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Usar este Template
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Adicionar Template -->
<div class="modal fade" id="addTemplateModal" tabindex="-1" aria-labelledby="addTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTemplateModalLabel">Adicionar Novo Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="addTemplateForm">
                    <div class="mb-3">
                        <label for="templateName" class="form-label">Nome do Template</label>
                        <input type="text" class="form-control" id="templateName" required>
                    </div>
                    <div class="mb-3">
                        <label for="templateDescription" class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="templateDescription" required>
                    </div>
                    <div class="mb-3">
                        <label for="templateCategory" class="form-label">Categoria</label>
                        <select class="form-select" id="templateCategory" required>
                            <option value="welcome">Boas-vindas</option>
                            <option value="followup">Follow-up</option>
                            <option value="promotional">Promocional</option>
                            <option value="notification">Notificação</option>
                            <option value="custom">Personalizado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="templateSubject" class="form-label">Assunto do Email</label>
                        <input type="text" class="form-control" id="templateSubject" required>
                    </div>
                    <div class="mb-3">
                        <label for="templateContent" class="form-label">Conteúdo HTML</label>
                        <textarea class="form-control" id="templateContent" rows="10" required></textarea>
                        <div class="form-text">
                            <strong>Variáveis disponíveis:</strong> 
                            <code>{{ '{' }}{{ '{' }}nome{{ '}' }}{{ '}' }}</code>, <code>{{ '{' }}{{ '{' }}email{{ '}' }}{{ '}' }}</code>, <code>{{ '{' }}{{ '{' }}telefone{{ '}' }}{{ '}' }}</code>, 
                            <code>{{ '{' }}{{ '{' }}curso{{ '}' }}{{ '}' }}</code>, <code>{{ '{' }}{{ '{' }}modalidade{{ '}' }}{{ '}' }}</code>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="templateThumbnail" class="form-label">Imagem de Thumbnail</label>
                        <input type="file" class="form-control" id="templateThumbnail" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveTemplateBtn">Salvar Template</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    .template-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    
    .template-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .template-preview {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    
    .template-badge {
        z-index: 10;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos
        const searchInput = document.getElementById('searchTemplate');
        const categoryFilter = document.getElementById('categoryFilter');
        const sortSelect = document.getElementById('sortTemplates');
        const templateItems = document.querySelectorAll('.template-item');
        const noTemplatesMessage = document.getElementById('noTemplatesMessage');
        const previewFrame = document.getElementById('previewFrame');
        const previewLoading = document.getElementById('previewLoading');
        const useTemplateBtn = document.getElementById('useTemplateBtn');
        const previewModal = document.getElementById('previewModal');
        
        // Limpar preview quando o modal for fechado
        previewModal.addEventListener('hidden.bs.modal', function () {
            previewFrame.srcdoc = '';
            previewFrame.style.display = 'none';
            previewLoading.style.display = 'block';
            document.getElementById('previewModalLabel').textContent = 'Preview do Template';
        });
        
        // Função para filtrar templates
        function filterTemplates() {
            const searchTerm = searchInput.value.toLowerCase();
            const category = categoryFilter.value;
            let visibleCount = 0;
            
            templateItems.forEach(item => {
                const templateCategory = item.dataset.category;
                const templateTitle = item.querySelector('.card-title').textContent.toLowerCase();
                const templateDesc = item.querySelector('.card-text').textContent.toLowerCase();
                
                const matchesSearch = searchTerm === '' || 
                                     templateTitle.includes(searchTerm) || 
                                     templateDesc.includes(searchTerm);
                                     
                const matchesCategory = category === 'all' || templateCategory === category;
                
                if (matchesSearch && matchesCategory) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });
            
            // Mostrar mensagem se não houver templates
            if (visibleCount === 0) {
                noTemplatesMessage.classList.remove('d-none');
            } else {
                noTemplatesMessage.classList.add('d-none');
            }
        }
        
        // Event listeners para filtros
        searchInput.addEventListener('input', filterTemplates);
        categoryFilter.addEventListener('change', filterTemplates);
        
        // Event listener para ordenação
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            const templateList = Array.from(document.querySelectorAll('.template-item'));
            const templateGallery = document.getElementById('templateGallery');
            
            templateList.sort((a, b) => {
                const titleA = a.querySelector('.card-title').textContent;
                const titleB = b.querySelector('.card-title').textContent;
                
                if (sortValue === 'name_asc') {
                    return titleA.localeCompare(titleB);
                } else if (sortValue === 'name_desc') {
                    return titleB.localeCompare(titleA);
                }
                // Outras opções de ordenação seriam implementadas aqui
                return 0;
            });
            
            // Remover todos os templates
            templateList.forEach(item => item.remove());
            
            // Adicionar de volta na nova ordem
            templateList.forEach(item => {
                templateGallery.appendChild(item);
            });
        });
        
        // Event listeners para botões de preview
        document.querySelectorAll('.preview-template').forEach(button => {
            button.addEventListener('click', async function() {
                const templateId = this.dataset.templateId;
                
                // Mostrar loading e esconder iframe
                previewLoading.style.display = 'block';
                previewFrame.style.display = 'none';
                
                try {
                    // Buscar o template do servidor
                    const response = await fetch(`/dashboard/email-campaigns/templates/${templateId}`);
                    
                    if (!response.ok) {
                        throw new Error('Erro ao carregar o template');
                    }
                    
                    const template = await response.json();
                    
                    // Atualizar o iframe com o conteúdo do template
                    previewFrame.srcdoc = template.content;
                    
                    // Atualizar o botão "Usar este Template"
                    useTemplateBtn.href = `{{ route('admin.email-campaigns.create') }}?template=${templateId}`;
                    
                    // Atualizar o título do modal
                    document.getElementById('previewModalLabel').textContent = `Preview: ${template.name}`;
                    
                    // Quando o iframe carregar, mostrar ele e esconder o loading
                    previewFrame.onload = function() {
                        previewLoading.style.display = 'none';
                        previewFrame.style.display = 'block';
                    };
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro ao carregar o template. Por favor, tente novamente.');
                    
                    // Em caso de erro, esconder loading e iframe
                    previewLoading.style.display = 'none';
                    previewFrame.style.display = 'none';
                }
            });
        });
        
        // Event listener para salvar novo template
        document.getElementById('saveTemplateBtn').addEventListener('click', function() {
            // Aqui implementaríamos a lógica para salvar o template
            alert('Funcionalidade de salvar template será implementada em breve!');
            
            // Fechar o modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addTemplateModal'));
            modal.hide();
        });
    });
</script>
@endsection 