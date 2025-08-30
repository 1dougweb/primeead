@extends('layouts.admin')

@section('title', 'Gerenciador de Arquivos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Gerenciador de Arquivos</h3>
        @if(!isset($configError))
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-primary me-2" onclick="document.getElementById('fileInput').click()">
                <i class="fas fa-upload me-2"></i>Upload
            </button>
            <button type="button" class="btn btn-outline-primary me-2" onclick="showCreateFolderModal()">
                <i class="fas fa-folder-plus me-2"></i>Nova Pasta
            </button>
            <button type="button" class="btn btn-outline-success me-2" onclick="syncAllFiles()" id="syncAllBtn">
                <i class="fas fa-sync-alt me-2"></i>Sincronização Completa
            </button>
            <button type="button" class="btn btn-outline-warning me-2" onclick="testModal()" title="Testar Modal">
                <i class="fas fa-bug me-2"></i>Testar Modal
            </button>
            <!-- <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-sync me-2"></i>Sincronizar
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="cleanupRecords()"><i class="fas fa-sync me-2"></i>Sincronização Normal</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="forceCleanup()"><i class="fas fa-broom me-2"></i>Limpeza Forçada</a></li>
                </ul>   
            </div> -->
        </div>
        @endif
    </div>

    @if(isset($configError))
    <div class="alert alert-warning">
        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Configuração necessária</h4>
        <p>{{ $configError }}</p>
        <hr>
        <div class="mb-0">
            <p>Para configurar a integração com o Google Drive, siga os passos abaixo:</p>
            <ol>
                <li>Acesse o <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
                <li>Crie um novo projeto</li>
                <li>Habilite a API do Google Drive para o projeto</li>
                <li>Crie credenciais OAuth 2.0 para aplicativo Web</li>
                <li>Configure as URIs de redirecionamento autorizadas (ex: http://localhost:8000/oauth/callback)</li>
                <li>Baixe o arquivo JSON de credenciais</li>
                <li>Renomeie o arquivo para <code>google-credentials.json</code></li>
                <li>Mova o arquivo para <code>storage/app/google-credentials.json</code></li>
                <li>Adicione a variável <code>GOOGLE_DRIVE_ROOT_FOLDER_ID</code> ao seu arquivo <code>.env</code> com o ID da pasta raiz do Google Drive</li>
            </ol>
        </div>
    </div>
    @else
    <!-- Breadcrumb e Ações -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.files.index') }}" class="text-decoration-none droppable-breadcrumb" data-folder-id="root">
                        <i class="fas fa-home"></i>
                </a>
            </li>
            @if($currentFolder)
                                @if($currentFolder->name !== 'site' || $currentFolder->file_id !== config('services.google.root_folder_id'))
                @foreach($currentFolder->ancestors as $ancestor)
                    <li class="breadcrumb-item">
                                            <a href="{{ route('admin.files.index', ['folder' => $ancestor->id]) }}" class="text-decoration-none droppable-breadcrumb" data-folder-id="{{ $ancestor->id }}">
                            {{ $ancestor->name }}
                        </a>
                    </li>
                @endforeach
                <li class="breadcrumb-item active">{{ $currentFolder->name }}</li>
                                @endif
            @endif
        </ol>
    </nav>
                </div>
        <div class="col-md-6">
                    <div class="d-flex justify-content-md-end">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="viewGrid" title="Visualização em grade">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="viewList" title="Visualização em lista">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshFiles" title="Atualizar">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de pesquisa e filtros -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
            <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Buscar arquivos...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="fileTypeFilter">
                        <option value="">Todos os tipos</option>
                        <option value="folder">Pastas</option>
                        <option value="image">Imagens</option>
                        <option value="document">Documentos</option>
                        <option value="video">Vídeos</option>
                        <option value="audio">Áudios</option>
                        <option value="other">Outros</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Visualização em Grade (padrão) -->
    <div class="card border-0 shadow-sm" id="gridView">
        <div class="card-body">
        @if(count($files) == 0)
                <div class="text-center py-5">
                    <i class="fas fa-folder-open text-muted fa-4x mb-3"></i>
                    <h5>Nenhum arquivo encontrado</h5>
                    <p class="text-muted">Faça upload de arquivos ou crie uma pasta.</p>
            </div>
        @else
                <div class="row" id="filesGrid">
            @foreach($files as $file)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4 file-item {{ $file->is_folder ? 'file-folder' : 'file-type-'.strtolower(explode('/', $file->mime_type)[0] ?? 'other') }} {{ $file->is_starred ? 'file-starred' : '' }}" 
                             data-id="{{ $file->id }}" 
                             data-file-id="{{ $file->file_id }}" 
                             data-name="{{ $file->name }}" 
                             data-is-folder="{{ $file->is_folder ? 'true' : 'false' }}">
                            <div class="card h-100 border-0 shadow-sm hover-shadow {{ $file->is_folder ? 'droppable-folder' : 'draggable-file' }}">
                                <div class="position-relative">
                                    <div class="file-preview text-center p-4 {{ $file->is_folder ? 'bg-folder' : getFilePreviewClass($file->mime_type) }}">
                                        <i class="{{ $file->is_folder ? 'fas fa-folder' : getCustomFileIcon($file->mime_type) }} fa-3x {{ $file->is_folder ? 'text-warning' : getFileIconColor($file->mime_type) }}"></i>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="file-name text-truncate">
                                    @if($file->is_folder)
                                                <a href="{{ route('admin.files.index', ['folder' => $file->id]) }}" class="text-decoration-none text-dark">
                                            {{ $file->name }}
                                        </a>
                                    @else
                                                <span class="text-dark">{{ $file->name }}</span>
                                    @endif
                                </div>
                                        <!-- @if($file->is_folder)
                                            <a href="{{ route('admin.files.index', ['folder' => $file->id]) }}" class="btn btn-sm btn-light ms-2" title="Abrir pasta">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        @endif -->
                            </div>
                                    <div class="file-info d-flex justify-content-between">
                                        <small class="text-muted">{{ $file->is_folder ? 'Pasta' : getFileTypeLabel($file->mime_type) }}</small>
                            @if(!$file->is_folder)
                                            <small class="text-muted">{{ $file->formatted_size }}</small>
                            @endif
                        </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 p-2">
                                    <div class="btn-group btn-group-sm w-100">
                                @if(!$file->is_folder)
                                            <a href="javascript:void(0)" onclick="downloadFile('{{ $file->id }}', '{{ $file->file_id }}')" class="btn btn-light" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                        @else
                                            <a href="{{ route('admin.files.index', ['folder' => $file->id]) }}" class="btn btn-light" title="Abrir">
                                                <i class="fas fa-folder-open"></i>
                                            </a>
                                @endif
                                        <button type="button" class="btn btn-light" onclick="showRenameModal('{{ $file->id }}', '{{ $file->name }}')" title="Renomear">
                                    <i class="fas fa-edit"></i>
                                </button>
                                        <!-- <button type="button" class="btn btn-light" onclick="showMoveModal('{{ $file->id }}')" title="Mover">
                                    <i class="fas fa-arrows-alt"></i>
                                        </button> -->
                                        <button type="button" class="btn btn-light text-danger" onclick="showDeleteModal('{{ $file->id }}', '{{ $file->name }}')" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
                </div>
        @endif
        </div>
    </div>

    <!-- Visualização em Lista (inicialmente oculta) -->
    <div class="card border-0 shadow-sm d-none" id="listView">
        <div class="card-body p-0">
            @if(count($files) == 0)
                <div class="text-center py-5">
                    <i class="fas fa-folder-open text-muted fa-4x mb-3"></i>
                    <h5>Nenhum arquivo encontrado</h5>
                    <p class="text-muted">Faça upload de arquivos ou crie uma pasta.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" width="40px"></th>
                                <th scope="col">Nome</th>
                                <th scope="col" width="120px">Tipo</th>
                                <th scope="col" width="120px">Tamanho</th>
                                <th scope="col" width="180px">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($files as $file)
                                <tr class="file-item {{ $file->is_folder ? 'file-folder' : 'file-type-'.strtolower(explode('/', $file->mime_type)[0] ?? 'other') }} {{ $file->is_starred ? 'file-starred' : '' }}"
                                    data-id="{{ $file->id }}" 
                                    data-file-id="{{ $file->file_id }}" 
                                    data-name="{{ $file->name }}" 
                                    data-is-folder="{{ $file->is_folder ? 'true' : 'false' }}">
                                    <td>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="{{ $file->is_folder ? 'fas fa-folder' : getCustomFileIcon($file->mime_type) }} fa-lg me-2 {{ $file->is_folder ? 'text-warning' : getFileIconColor($file->mime_type) }}"></i>
                                            @if($file->is_folder)
                                                <a href="{{ route('admin.files.index', ['folder' => $file->id]) }}" class="text-decoration-none text-dark">
                                                    {{ $file->name }}
                                                </a>
                                            @else
                                                <span>{{ $file->name }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $file->is_folder ? 'Pasta' : getFileTypeLabel($file->mime_type) }}</td>
                                    <td>{{ $file->is_folder ? '-' : $file->formatted_size }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if(!$file->is_folder)
                                                <a href="javascript:void(0)" onclick="downloadFile('{{ $file->id }}', '{{ $file->file_id }}')" class="btn btn-light" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('admin.files.index', ['folder' => $file->id]) }}" class="btn btn-light" title="Abrir">
                                                    <i class="fas fa-folder-open"></i>
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-light" onclick="showRenameModal('{{ $file->id }}', '{{ $file->name }}')" title="Renomear">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-light" onclick="showMoveModal('{{ $file->id }}')" title="Mover">
                                                <i class="fas fa-arrows-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-light text-danger" onclick="showDeleteModal('{{ $file->id }}', '{{ $file->name }}')" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Hidden File Input -->
    <input type="file" id="fileInput" class="d-none" onchange="uploadFile(this)">
</div>

<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Pasta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="folderName" class="form-label">Nome da Pasta</label>
                    <input type="text" class="form-control" id="folderName">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="createFolder()">Criar</button>
            </div>
        </div>
    </div>
</div>

<!-- Rename Modal -->
<div class="modal fade" id="renameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Renomear</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="renameFileId">
                <div class="mb-3">
                    <label for="newFileName" class="form-label">Novo Nome</label>
                    <input type="text" class="form-control" id="newFileName">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="renameFile()">Renomear</button>
            </div>
        </div>
    </div>
</div>

<!-- Move Modal -->
<div class="modal fade" id="moveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mover para</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="moveFileId">
                <div class="mb-3">
                    <label for="targetFolder" class="form-label">Pasta de Destino</label>
                    <select class="form-select" id="targetFolder">
                        <option value="">Raiz</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="moveFile()">Mover</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="deleteFileId">
                <input type="hidden" id="deleteFileIsFolder">
                <p>Tem certeza que deseja excluir <strong id="deleteFileName"></strong>?</p>
                
                <p class="text-muted">Esta ação excluirá o item permanentemente do Google Drive.</p>
                
                <!-- Aviso de exclusão -->
                <div id="recursiveWarning" class="alert alert-danger">
                    <strong>Atenção:</strong> Esta operação excluirá o item permanentemente!
                </div>
                
                <p class="text-danger mt-3"><i class="fas fa-exclamation-triangle"></i> Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="deleteFileFromModal()">Excluir</button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
@if(!isset($configError))
<script>
// Variáveis globais para navegação
let currentFolderId = '{{ $currentFolder?->id ?? null }}';
let currentFolderName = '{{ $currentFolder?->name ?? "Início" }}';

// Adicionar no início do script para depuração
$(document).ready(function() {
    // Verificar se há preferência salva
    const viewMode = localStorage.getItem('fileViewMode') || 'grid';
    
    if (viewMode === 'list') {
        $('#gridView').addClass('d-none');
        $('#listView').removeClass('d-none');
        $('#viewList').addClass('active');
    } else {
        $('#gridView').removeClass('d-none');
        $('#listView').addClass('d-none');
        $('#viewGrid').addClass('active');
    }
    
    // Alternar para visualização em grade
    $('#viewGrid').click(function() {
        $('#gridView').removeClass('d-none');
        $('#listView').addClass('d-none');
        $('#viewGrid').addClass('active');
        $('#viewList').removeClass('active');
        localStorage.setItem('fileViewMode', 'grid');
    });
    
    // Alternar para visualização em lista
    $('#viewList').click(function() {
        $('#gridView').addClass('d-none');
        $('#listView').removeClass('d-none');
        $('#viewList').addClass('active');
        $('#viewGrid').removeClass('active');
        localStorage.setItem('fileViewMode', 'list');
    });
    
    // Filtrar por tipo de arquivo
    $('#fileTypeFilter').change(function() {
        const fileType = $(this).val();
        
        // Primeiro, mostrar ou esconder com base em favoritos
        $('.file-item').show(); // Remover filtro de favoritos
        
        // Em seguida, aplicar filtro de tipo se necessário
        if (fileType !== '') {
            $('.file-item').not('.file-' + fileType).hide();
        }
    });
    
    // Botão de atualizar
    $('#refreshFiles').click(function() {
        location.reload();
    });
});

/**
 * Inicializa a funcionalidade de drag and drop para arquivos e pastas
 */
function initDragAndDrop() {
    // Elemento raiz para indicador visual
    const fileManager = document.body;
    
    // Função para ativar o modo de arrastar
    function activateDragMode() {
        fileManager.classList.add('file-drag-active');
    }
    
    // Função para desativar o modo de arrastar
    function deactivateDragMode() {
        fileManager.classList.remove('file-drag-active');
    }
    
    // Configurar eventos para elementos arrastáveis (arquivos na visualização em grade)
    const draggableFiles = document.querySelectorAll('.draggable-file');
    draggableFiles.forEach(file => {
        file.setAttribute('draggable', 'true');
        
        file.addEventListener('dragstart', function(e) {
            // Armazenar dados do arquivo sendo arrastado
            const fileData = {
                id: this.closest('.file-item').dataset.id,
                fileId: this.closest('.file-item').dataset.fileId,
                name: this.closest('.file-item').dataset.name
            };
            
            e.dataTransfer.setData('application/json', JSON.stringify(fileData));
            this.classList.add('dragging');
            activateDragMode();
        });
        
        file.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            deactivateDragMode();
        });
    });
    
    // Configurar eventos para linhas de tabela arrastáveis (arquivos na visualização em lista)
    const draggableRows = document.querySelectorAll('tr[data-is-folder="false"]');
    draggableRows.forEach(row => {
        row.setAttribute('draggable', 'true');
        row.classList.add('draggable-row');
        
        row.addEventListener('dragstart', function(e) {
            // Armazenar dados do arquivo sendo arrastado
            const fileData = {
                id: this.dataset.id,
                fileId: this.dataset.fileId,
                name: this.dataset.name
            };
            
            e.dataTransfer.setData('application/json', JSON.stringify(fileData));
            this.classList.add('dragging');
            activateDragMode();
        });
        
        row.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            deactivateDragMode();
        });
    });
    
    // Configurar eventos para pastas (alvos de soltar na visualização em grade)
    const droppableFolders = document.querySelectorAll('.droppable-folder');
    droppableFolders.forEach(folder => {
        folder.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        
        folder.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });
        
        folder.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            try {
                // Obter dados do arquivo arrastado
                const fileData = JSON.parse(e.dataTransfer.getData('application/json'));
                const targetFolderId = this.closest('.file-item').dataset.id;
                
                // Mostrar confirmação
                if (confirm(`Deseja mover "${fileData.name}" para esta pasta?`)) {
                    moveFile(fileData.id, targetFolderId);
                }
            } catch (error) {
                console.error('Erro ao processar o arquivo arrastado:', error);
            }
        });
    });
    
    // Configurar eventos para linhas de tabela onde podemos soltar (pastas na visualização em lista)
    const droppableRows = document.querySelectorAll('tr[data-is-folder="true"]');
    droppableRows.forEach(row => {
        row.classList.add('droppable-row');
        
        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        
        row.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });
        
        row.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            try {
                // Obter dados do arquivo arrastado
                const fileData = JSON.parse(e.dataTransfer.getData('application/json'));
                const targetFolderId = this.dataset.id;
                
                // Mostrar confirmação
                if (confirm(`Deseja mover "${fileData.name}" para esta pasta?`)) {
                    moveFile(fileData.id, targetFolderId);
                }
            } catch (error) {
                console.error('Erro ao processar o arquivo arrastado:', error);
            }
        });
    });
    
    // Configurar eventos para breadcrumbs (alvos de soltar para navegação para cima)
    const droppableBreadcrumbs = document.querySelectorAll('.droppable-breadcrumb');
    droppableBreadcrumbs.forEach(breadcrumb => {
        breadcrumb.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        
        breadcrumb.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });
        
        breadcrumb.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            try {
                // Obter dados do arquivo arrastado
                const fileData = JSON.parse(e.dataTransfer.getData('application/json'));
                const targetFolderId = this.dataset.folderId === 'root' ? null : this.dataset.folderId;
                
                // Mostrar confirmação
                if (confirm(`Deseja mover "${fileData.name}" para esta pasta?`)) {
                    moveFile(fileData.id, targetFolderId);
                }
            } catch (error) {
                console.error('Erro ao processar o arquivo arrastado:', error);
            }
        });
    });
}

/**
 * Move um arquivo para uma pasta específica
 */
function moveFile(fileId, folderId) {
    // Mostrar indicador de carregamento
    toastr.info('Movendo arquivo...');
    
    // Enviar solicitação para mover o arquivo
    axios.put(window.location.origin + '/dashboard/files/' + fileId + '/move', { 
        folder_id: folderId === '' || folderId === null || folderId === undefined ? null : folderId 
    })
    .then(response => {
        if (response.data.success) {
            toastr.success(response.data.message);
            // Recarregar a página após o movimento bem-sucedido
            location.reload();
        }
    })
    .catch(error => {
        toastr.error(error.response?.data?.message || 'Erro ao mover arquivo');
    });
}

/**
 * Função para download de arquivos
 * Verifica se o ID é do Google Drive e busca o ID local antes de fazer o download
 */
function downloadFile(id, fileId) {
    // Mostrar indicador de carregamento
    toastr.info('Preparando download...');
    
    // Se o ID parece ser um ID do Google Drive (contém letras)
    if (fileId && fileId.match(/[a-zA-Z]/)) {
        // Usar diretamente o ID do Google Drive na rota de download
        // O controlador já foi modificado para lidar com isso
        window.location.href = window.location.origin + '/dashboard/files/' + fileId + '/download';
    } else {
        // Se não contém letras ou não tem fileId, usar o ID local diretamente
        window.location.href = window.location.origin + '/dashboard/files/' + id + '/download';
    }
}

function cleanupRecords() {
    // Mostrar confirmação
    if (!confirm('Esta ação irá sincronizar os arquivos com o Google Drive e remover registros inconsistentes. Deseja continuar?')) {
        return;
    }

    // Mostrar indicador de carregamento
    toastr.info('Sincronizando arquivos...');

    // Enviar solicitação para limpar registros
    axios.post(window.location.origin + '/dashboard/files/cleanup')
        .then(response => {
            if (response.data.success) {
                toastr.success(response.data.message);
                // Recarregar a página após um pequeno atraso
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                toastr.error(response.data.message || 'Erro ao sincronizar arquivos');
            }
        })
        .catch(error => {
            toastr.error(error.response?.data?.message || 'Erro ao sincronizar arquivos');
        });
}

function forceCleanup() {
    // Mostrar confirmação
    if (!confirm('ATENÇÃO: Esta ação irá remover todos os registros da pasta "site" e da pasta raiz. Deseja continuar?')) {
        return;
    }

    // Mostrar indicador de carregamento
    toastr.info('Executando limpeza forçada...');

    // Enviar solicitação para limpar registros
    axios.post(window.location.origin + '/dashboard/files/force-cleanup')
        .then(response => {
            if (response.data.success) {
                toastr.success(response.data.message);
                // Recarregar a página após um pequeno atraso
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                toastr.error(response.data.message || 'Erro ao executar limpeza forçada');
            }
        })
        .catch(error => {
            toastr.error(error.response?.data?.message || 'Erro ao executar limpeza forçada');
        });
}

function starFile(id) {
    console.log('starFile chamado com ID:', id);
    
    axios.post(window.location.origin + '/dashboard/files/' + id + '/star')
        .then(response => {
            if (response.data.success) {
                toastr.success(response.data.message);
                console.log('Resposta do servidor:', response.data);
                location.reload();
            }
        })
        .catch(error => {
            console.error('Erro ao favoritar:', error);
            toastr.error(error.response?.data?.message || 'Erro ao marcar/desmarcar favorito');
        });
}

// Funções para upload e criação de pastas
function uploadFile(input) {
    if (!input.files.length) return;

    const formData = new FormData();
    formData.append('file', input.files[0]);
    // Usar a variável global que é atualizada durante a navegação
    console.log('Upload - Current folder ID:', currentFolderId);
    console.log('Upload - Current folder name:', currentFolderName);
    formData.append('folder_id', currentFolderId);

    axios.post('{{ route('admin.files.store') }}', formData)
        .then(response => {
            if (response.data.success) {
                toastr.success(response.data.message);
                // Recarregar apenas os arquivos da pasta atual, não toda a página
                if (currentFolderId) {
                    navigateToFolder(currentFolderId, currentFolderName);
                } else {
                    location.reload();
                }
            }
        })
        .catch(error => {
            toastr.error(error.response?.data?.message || 'Erro ao enviar arquivo');
        });
}

function showCreateFolderModal() {
    $('#createFolderModal').modal('show');
}

function createFolder() {
    const name = $('#folderName').val();
    if (!name) return;

    axios.post('{{ route('admin.files.create-folder') }}', {
        name,
        parent_id: currentFolderId
    })
    .then(response => {
        if (response.data.success) {
            toastr.success(response.data.message);
            // Recarregar apenas os arquivos da pasta atual, não toda a página
            if (currentFolderId) {
                navigateToFolder(currentFolderId, currentFolderName);
            } else {
                location.reload();
            }
        }
    })
    .catch(error => {
        toastr.error(error.response?.data?.message || 'Erro ao criar pasta');
    });
}

// Funções para modais
function showRenameModal(id, name) {
    // Verificar se o ID é um ID do Google Drive
    if (id.match(/[a-zA-Z]/)) {
        // Buscar o ID do banco de dados pelo file_id do Google Drive
        axios.get(window.location.origin + '/dashboard/files/find-by-file-id/' + id)
            .then(response => {
                if (response.data.id) {
                    $('#renameFileId').val(response.data.id);
                    $('#newFileName').val(name);
                    $('#renameModal').modal('show');
                } else {
                    toastr.error('Arquivo não encontrado no banco de dados local');
                }
            })
            .catch(error => {
                toastr.error('Erro ao buscar informações do arquivo');
            });
    } else {
        // Se não contém letras, assumimos que é o ID do banco de dados local
        $('#renameFileId').val(id);
        $('#newFileName').val(name);
        $('#renameModal').modal('show');
    }
}

function renameFile() {
    const id = $('#renameFileId').val();
    const name = $('#newFileName').val();
    if (!name) return;

    axios.put(window.location.origin + '/dashboard/files/' + id + '/rename', { name })
        .then(response => {
            if (response.data.success) {
                toastr.success(response.data.message);
                location.reload();
            }
        })
        .catch(error => {
            toastr.error(error.response?.data?.message || 'Erro ao renomear');
        });
}

function showMoveModal(id) {
    // Verificar se o ID é um ID do Google Drive
    if (id.match(/[a-zA-Z]/)) {
        // Buscar o ID do banco de dados pelo file_id do Google Drive
        axios.get(window.location.origin + '/dashboard/files/find-by-file-id/' + id)
            .then(response => {
                if (response.data.id) {
                    $('#moveFileId').val(response.data.id);
                    $('#moveModal').modal('show');
                } else {
                    toastr.error('Arquivo não encontrado no banco de dados local');
                }
            })
            .catch(error => {
                toastr.error('Erro ao buscar informações do arquivo');
            });
    } else {
        // Se não contém letras, assumimos que é o ID do banco de dados local
        $('#moveFileId').val(id);
        $('#moveModal').modal('show');
    }
}

function moveFile() {
    const id = $('#moveFileId').val();
    const folderId = $('#targetFolder').val();

    axios.put(window.location.origin + '/dashboard/files/' + id + '/move', { folder_id: folderId })
        .then(response => {
            if (response.data.success) {
                toastr.success(response.data.message);
                location.reload();
            }
        })
        .catch(error => {
            toastr.error(error.response?.data?.message || 'Erro ao mover');
        });
}

function showDeleteModal(id, name) {
    console.log('showDeleteModal called with id:', id, 'name:', name);
    
    try {
        // Verificar se o modal existe
        const modal = $('#deleteModal');
        if (modal.length === 0) {
            console.error('Modal não encontrado!');
            toastr.error('Erro: Modal de exclusão não encontrado');
            return;
        }
        
        console.log('Modal encontrado, configurando...');
        
        // Configurar campos do modal
        $('#deleteFileId').val(id);
        $('#deleteFileName').text(name);
        
        // Verificar se é uma pasta baseado no nome do arquivo ou estrutura do DOM
        const fileItem = $(`[data-id="${id}"], [data-file-id="${id}"]`);
        let isFolder = false;
        
        // Verificar se é uma pasta de várias formas
        if (fileItem.length > 0) {
            isFolder = fileItem.data('is-folder') === 'true' || 
                      fileItem.hasClass('file-folder') ||
                      fileItem.closest('tr').hasClass('file-folder');
        }
        
        // Fallback: verificar pelo nome (se contém extensão, provavelmente é arquivo)
        if (!isFolder && name) {
            const hasExtension = name.includes('.');
            isFolder = !hasExtension;
        }
        
        console.log('showDeleteModal - isFolder:', isFolder);
        console.log('showDeleteModal - fileItem:', fileItem);
        console.log('showDeleteModal - fileItem classes:', fileItem.attr('class'));
        console.log('showDeleteModal - deleteRecursive element exists:', $('#deleteRecursive').length > 0);
        
        $('#deleteFileIsFolder').val(isFolder);
        
        // Sempre mostrar o aviso de exclusão
        $('#recursiveWarning').show();
        
        console.log('Tentando exibir modal...');
        
        // Tentar exibir o modal usando jQuery (mais compatível)
        try {
            console.log('Tentando jQuery modal...');
            modal.modal('show');
            console.log('Modal exibido com jQuery!');
        } catch (jqueryError) {
            console.log('jQuery modal failed, trying Bootstrap 5...', jqueryError);
            
            try {
                // Fallback para Bootstrap 5
                console.log('Tentando Bootstrap 5 modal...');
                const modalInstance = new bootstrap.Modal(modal[0]);
                modalInstance.show();
                console.log('Modal exibido com Bootstrap 5!');
            } catch (bootstrapError) {
                console.log('Bootstrap 5 também falhou, tentando fallback direto...', bootstrapError);
                
                // Último recurso: mostrar o modal diretamente
                try {
                    modal.show();
                    modal.addClass('show');
                    modal.attr('style', 'display: block !important; background-color: rgba(0,0,0,0.5) !important;');
                    modal.find('.modal-dialog').addClass('show');
                    console.log('Modal exibido com fallback direto!');
                } catch (fallbackError) {
                    console.error('Todos os métodos falharam:', fallbackError);
                    toastr.error('Erro ao exibir modal. Tente recarregar a página.');
                }
            }
        }
        
        console.log('Modal exibido com sucesso!');
        

        
            } catch (error) {
            console.error('Erro ao exibir modal:', error);
            toastr.error('Erro ao exibir modal de exclusão: ' + error.message);
            
            // Fallback: mostrar confirmação inline
            console.log('Tentando fallback inline...');
            showInlineDeleteConfirmation(id, name, isFolder);
        }
    }
    
    // Função de fallback para quando o modal não funciona
    function showInlineDeleteConfirmation(id, name, isFolder) {
        console.log('Mostrando confirmação inline para:', name, 'isFolder:', isFolder);
        
        let message = `Tem certeza que deseja excluir "${name}"?\n\n`;
        
        if (isFolder) {
            message += `ATENÇÃO: Esta é uma pasta!\n`;
            message += `- Para pasta vazia: Use "Exclusão permanente"\n`;
            message += `- Para pasta com conteúdo: Use "Exclusão recursiva"\n\n`;
            message += `Escolha uma opção:\n`;
            message += `1. Exclusão permanente\n`;
            message += `2. Mover para lixeira\n`;
            message += `3. Exclusão recursiva (recomendado para pastas com conteúdo)\n`;
            message += `4. Cancelar`;
            
            const choice = prompt(message, '3');
            
            switch(choice) {
                case '1':
                    deleteFileDirectly(id, 'permanent');
                    break;
                case '2':
                    deleteFileDirectly(id, 'trash');
                    break;
                case '3':
                    deleteFileDirectly(id, 'recursive');
                    break;
                case '4':
                default:
                    console.log('Operação cancelada pelo usuário');
                    break;
            }
        } else {
            message += `Escolha uma opção:\n`;
            message += `1. Exclusão permanente\n`;
            message += `2. Mover para lixeira\n`;
            message += `3. Cancelar`;
            
            const choice = prompt(message, '1');
            
            switch(choice) {
                case '1':
                    deleteFileDirectly(id, 'permanent');
                    break;
                case '2':
                    deleteFileDirectly(id, 'trash');
                    break;
                case '3':
                default:
                    console.log('Operação cancelada pelo usuário');
                    break;
            }
        }
    }
    
    // Função para deletar arquivo diretamente
    function deleteFileDirectly(id, option) {
        console.log('Deletando arquivo diretamente:', id, 'opção:', option);
        
        let url;
        let method = 'post';
        
        if (option === 'permanent') {
            url = window.location.origin + '/dashboard/files/' + id;
            method = 'delete';
        } else if (option === 'recursive') {
            url = window.location.origin + '/dashboard/files/delete-recursive/' + id;
            method = 'post';
        } else if (option === 'trash') {
            url = window.location.origin + '/dashboard/files/' + id + '/move-to-trash';
            method = 'post';
        }
        
        console.log('Enviando requisição para:', url, 'método:', method);
        
        $.ajax({
            method: method,
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .then(response => {
            if (response.success) {
                toastr.success(response.message || 'Arquivo processado com sucesso!');
                location.reload();
            } else {
                toastr.error(response.message || 'Erro ao processar arquivo');
            }
        })
        .catch(error => {
            console.error('Erro ao processar arquivo:', error);
            toastr.error('Erro ao processar arquivo: ' + (error.responseJSON?.message || error.statusText));
        });
    }

function deleteFileFromModal() {
    const id = $('#deleteFileId').val();
    const isFolder = $('#deleteFileIsFolder').val();

    // Mostrar indicador de carregamento
    toastr.info('Processando exclusão...');

    // Sempre usar exclusão recursiva (funciona para arquivos e pastas)
    const url = window.location.origin + '/dashboard/files/delete-recursive/' + id;
    const method = 'post';
    const message = 'Item excluído com sucesso!';

    console.log('Modal delete - Enviando requisição:', { url, method, isFolder });

    $.ajax({
        method: method,
        url: url,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .then(response => {
        console.log('Modal delete - Resposta:', response);
        if (response.success) {
            toastr.success(response.message || message);
            $('#deleteModal').modal('hide');
            location.reload();
        } else {
            toastr.error(response.message || 'Erro ao processar arquivo');
            $('#deleteModal').modal('hide');
        }
    })
    .catch(error => {
        console.error('Modal delete - Erro:', error);
        toastr.error('Erro ao processar arquivo: ' + (error.responseJSON?.message || error.statusText));
        $('#deleteModal').modal('hide');
    });
}
</script>

<!-- Incluir o script de drag and drop -->
<script src="{{ asset('js/file-manager.js') }}"></script>

<style>
/* Impedir seleção de texto em elementos clicáveis */
.card, .btn, button, a, .star-btn, .file-preview, .card-body, .card-footer {
    user-select: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    cursor: pointer !important;
}

/* Garantir que o cursor seja sempre um ponteiro nos elementos clicáveis */
.btn, button, a, .star-btn, .botao-estrela {
    cursor: pointer !important;
}

.botao-estrela {
    z-index: 10;
    opacity: 1 !important;
    background-color: rgba(255, 255, 255, 0.8);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: block !important;
    visibility: visible !important;
    width: 35px;
    height: 35px;
}

.hover-shadow:hover {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    transform: translateY(-2px);
    transition: all .2s;
}

.file-preview {
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-top-left-radius: 0.25rem;
    border-top-right-radius: 0.25rem;
}

.bg-folder {
    background-color: rgba(255, 193, 7, 0.1);
}

.bg-image {
    background-color: rgba(76, 175, 80, 0.1);
}

.bg-pdf {
    background-color: rgba(244, 67, 54, 0.1);
}

.bg-document {
    background-color: rgba(33, 150, 243, 0.1);
}

.bg-spreadsheet {
    background-color: rgba(76, 175, 80, 0.1);
}

.bg-presentation {
    background-color: rgba(255, 152, 0, 0.1);
}

.bg-video {
    background-color: rgba(156, 39, 176, 0.1);
}

.bg-audio {
    background-color: rgba(0, 188, 212, 0.1);
}

.bg-archive {
    background-color: rgba(121, 85, 72, 0.1);
}

.bg-code {
    background-color: rgba(96, 125, 139, 0.1);
}

.file-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-actions {
    opacity: 0.7;
}

.card:hover .file-actions {
    opacity: 1;
}

.btn-group-sm > .btn {
    padding: .25rem .5rem;
}

.table > :not(caption) > * > * {
    padding: .75rem 1rem;
}

/* Cores para tipos de arquivo */
.text-image {
    color: #4CAF50; /* Verde para imagens */
}

.text-pdf {
    color: #F44336; /* Vermelho para PDFs */
}

.text-document {
    color: #2196F3; /* Azul para documentos */
}

.text-spreadsheet {
    color: #4CAF50; /* Verde para planilhas */
}

.text-presentation {
    color: #FF9800; /* Laranja para apresentações */
}

.text-video {
    color: #9C27B0; /* Roxo para vídeos */
}

.text-audio {
    color: #00BCD4; /* Ciano para áudios */
}

.text-archive {
    color: #795548; /* Marrom para arquivos compactados */
}

.text-code {
    color: #607D8B; /* Azul acinzentado para código */
}

/* Fundo colorido para os cards */
.card-folder {
    border-left: 4px solid #FFC107 !important;
}

.card-image {
    border-left: 4px solid #4CAF50 !important;
}

.card-pdf {
    border-left: 4px solid #F44336 !important;
}

.card-document {
    border-left: 4px solid #2196F3 !important;
}

.card-spreadsheet {
    border-left: 4px solid #4CAF50 !important;
}

.card-presentation {
    border-left: 4px solid #FF9800 !important;
}

.card-video {
    border-left: 4px solid #9C27B0 !important;
}

.card-audio {
    border-left: 4px solid #00BCD4 !important;
}

.card-archive {
    border-left: 4px solid #795548 !important;
}

.card-code {
    border-left: 4px solid #607D8B !important;
}

/* Cores para as linhas da tabela */
.table .file-folder td:first-child {
    border-left: 4px solid #FFC107;
}

.table .file-type-image td:first-child {
    border-left: 4px solid #4CAF50;
}

.table .file-type-application td:first-child {
    border-left: 4px solid #2196F3;
}

.table .file-type-video td:first-child {
    border-left: 4px solid #9C27B0;
}

.table .file-type-audio td:first-child {
    border-left: 4px solid #00BCD4;
}

.table .file-type-text td:first-child {
    border-left: 4px solid #607D8B;
}

.star-btn {
    z-index: 10;
    opacity: 1 !important;
    background-color: rgba(255, 255, 255, 0.8);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: block !important;
    visibility: visible !important;
}

.star-btn:hover {
    background-color: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.text-warning {
    color: #ffc107 !important;
}

.file-starred {
    /* Classe vazia, usada apenas para seleção */
}

.always-visible {
    opacity: 1 !important;
    visibility: visible !important;
    display: inline-block !important;
}

/* Estilos para drag and drop */
.draggable-file {
    cursor: grab;
}

.draggable-file:active {
    cursor: grabbing;
}

.droppable-folder {
    transition: all 0.2s ease;
    position: relative;
    z-index: 10;
}

.droppable-folder.drag-over {
    border: 2px dashed #4CAF50 !important;
    box-shadow: 0 0 10px rgba(76, 175, 80, 0.5) !important;
    transform: scale(1.02);
}

.droppable-breadcrumb {
    transition: all 0.2s ease;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}

.droppable-breadcrumb.drag-over {
    background-color: rgba(76, 175, 80, 0.2);
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
}

.dragging {
    opacity: 0.5;
}
</style>
@endif
@endpush 

@php
function getFileIconColor($mimeType) {
    if (!$mimeType) return '';
    
    // Extrair o tipo principal do MIME
    $mainType = explode('/', $mimeType)[0];
    $subType = explode('/', $mimeType)[1] ?? '';
    
    // Definir cores baseadas no tipo de arquivo
    switch ($mainType) {
        case 'image':
            return 'text-image';
        case 'video':
            return 'text-video';
        case 'audio':
            return 'text-audio';
        case 'text':
            return 'text-code';
        case 'application':
            // Subtipos de application
            if (strpos($subType, 'pdf') !== false) {
                return 'text-pdf';
            } elseif (strpos($subType, 'word') !== false || strpos($subType, 'document') !== false || strpos($subType, 'rtf') !== false) {
                return 'text-document';
            } elseif (strpos($subType, 'excel') !== false || strpos($subType, 'spreadsheet') !== false) {
                return 'text-spreadsheet';
            } elseif (strpos($subType, 'powerpoint') !== false || strpos($subType, 'presentation') !== false) {
                return 'text-presentation';
            } elseif (strpos($subType, 'zip') !== false || strpos($subType, 'rar') !== false || strpos($subType, 'tar') !== false || strpos($subType, 'compressed') !== false) {
                return 'text-archive';
            } elseif (strpos($subType, 'json') !== false || strpos($subType, 'xml') !== false || strpos($subType, 'javascript') !== false || strpos($subType, 'html') !== false) {
                return 'text-code';
            }
            return '';
        default:
            return '';
    }
}

function getFilePreviewClass($mimeType) {
    if (!$mimeType) return '';
    
    // Extrair o tipo principal do MIME
    $mainType = explode('/', $mimeType)[0];
    $subType = explode('/', $mimeType)[1] ?? '';
    
    // Definir classes baseadas no tipo de arquivo
    switch ($mainType) {
        case 'image':
            return 'bg-image';
        case 'video':
            return 'bg-video';
        case 'audio':
            return 'bg-audio';
        case 'text':
            return 'bg-code';
        case 'application':
            // Subtipos de application
            if (strpos($subType, 'pdf') !== false) {
                return 'bg-pdf';
            } elseif (strpos($subType, 'word') !== false || strpos($subType, 'document') !== false || strpos($subType, 'rtf') !== false) {
                return 'bg-document';
            } elseif (strpos($subType, 'excel') !== false || strpos($subType, 'spreadsheet') !== false) {
                return 'bg-spreadsheet';
            } elseif (strpos($subType, 'powerpoint') !== false || strpos($subType, 'presentation') !== false) {
                return 'bg-presentation';
            } elseif (strpos($subType, 'zip') !== false || strpos($subType, 'rar') !== false || strpos($subType, 'tar') !== false || strpos($subType, 'compressed') !== false) {
                return 'bg-archive';
            } elseif (strpos($subType, 'json') !== false || strpos($subType, 'xml') !== false || strpos($subType, 'javascript') !== false || strpos($subType, 'html') !== false) {
                return 'bg-code';
            }
            return '';
        default:
            return '';
    }
}

function getFileCardClass($mimeType) {
    if (!$mimeType) return '';
    
    // Extrair o tipo principal do MIME
    $mainType = explode('/', $mimeType)[0];
    $subType = explode('/', $mimeType)[1] ?? '';
    
    // Definir classes baseadas no tipo de arquivo
    switch ($mainType) {
        case 'image':
            return 'card-image';
        case 'video':
            return 'card-video';
        case 'audio':
            return 'card-audio';
        case 'text':
            return 'card-code';
        case 'application':
            // Subtipos de application
            if (strpos($subType, 'pdf') !== false) {
                return 'card-pdf';
            } elseif (strpos($subType, 'word') !== false || strpos($subType, 'document') !== false || strpos($subType, 'rtf') !== false) {
                return 'card-document';
            } elseif (strpos($subType, 'excel') !== false || strpos($subType, 'spreadsheet') !== false) {
                return 'card-spreadsheet';
            } elseif (strpos($subType, 'powerpoint') !== false || strpos($subType, 'presentation') !== false) {
                return 'card-presentation';
            } elseif (strpos($subType, 'zip') !== false || strpos($subType, 'rar') !== false || strpos($subType, 'tar') !== false || strpos($subType, 'compressed') !== false) {
                return 'card-archive';
            } elseif (strpos($subType, 'json') !== false || strpos($subType, 'xml') !== false || strpos($subType, 'javascript') !== false || strpos($subType, 'html') !== false) {
                return 'card-code';
            }
            return '';
        default:
            return '';
    }
}

function getCustomFileIcon($mimeType) {
    if (!$mimeType) return 'fas fa-file';
    
    // Extrair o tipo principal do MIME
    $mainType = explode('/', $mimeType)[0];
    $subType = explode('/', $mimeType)[1] ?? '';
    
    // Definir ícones baseados no tipo de arquivo
    switch ($mainType) {
        case 'image':
            return 'fas fa-file-image';
        case 'video':
            return 'fas fa-file-video';
        case 'audio':
            return 'fas fa-file-audio';
        case 'text':
            return 'fas fa-file-alt';
        case 'application':
            // Subtipos de application
            if (strpos($subType, 'pdf') !== false) {
                return 'fas fa-file-pdf';
            } elseif (strpos($subType, 'word') !== false || strpos($subType, 'document') !== false || strpos($subType, 'rtf') !== false) {
                return 'fas fa-file-word';
            } elseif (strpos($subType, 'excel') !== false || strpos($subType, 'spreadsheet') !== false) {
                return 'fas fa-file-excel';
            } elseif (strpos($subType, 'powerpoint') !== false || strpos($subType, 'presentation') !== false) {
                return 'fas fa-file-powerpoint';
            } elseif (strpos($subType, 'zip') !== false || strpos($subType, 'rar') !== false || strpos($subType, 'tar') !== false || strpos($subType, 'compressed') !== false) {
                return 'fas fa-file-archive';
            } elseif (strpos($subType, 'json') !== false || strpos($subType, 'xml') !== false || strpos($subType, 'javascript') !== false || strpos($subType, 'html') !== false) {
                return 'fas fa-file-code';
            }
            return 'fas fa-file';
        default:
            return 'fas fa-file';
    }
}

function getFileTypeLabel($mimeType) {
    if (!$mimeType) return 'Arquivo';
    
    // Extrair o tipo principal do MIME
    $mainType = explode('/', $mimeType)[0];
    $subType = explode('/', $mimeType)[1] ?? '';
    
    // Definir rótulos baseados no tipo de arquivo
    switch ($mainType) {
        case 'image':
            return 'Imagem';
        case 'video':
            return 'Vídeo';
        case 'audio':
            return 'Áudio';
        case 'text':
            return 'Texto';
        case 'application':
            // Subtipos de application
            if (strpos($subType, 'pdf') !== false) {
                return 'PDF';
            } elseif (strpos($subType, 'word') !== false || strpos($subType, 'document') !== false || strpos($subType, 'rtf') !== false) {
                return 'Documento';
            } elseif (strpos($subType, 'excel') !== false || strpos($subType, 'spreadsheet') !== false) {
                return 'Planilha';
            } elseif (strpos($subType, 'powerpoint') !== false || strpos($subType, 'presentation') !== false) {
                return 'Apresentação';
            } elseif (strpos($subType, 'zip') !== false || strpos($subType, 'rar') !== false || strpos($subType, 'tar') !== false || strpos($subType, 'compressed') !== false) {
                return 'Arquivo compactado';
            } elseif (strpos($subType, 'json') !== false || strpos($subType, 'xml') !== false || strpos($subType, 'javascript') !== false || strpos($subType, 'html') !== false) {
                return 'Código';
            }
            return 'Arquivo';
        default:
            return 'Arquivo';
    }
}
@endphp

@push('scripts')
<script src="{{ asset('js/google-drive-sync.js') }}"></script>

<script>
// Função para navegação entre pastas (simples reload para este arquivo)
function navigateToFolder(folderId, folderName) {
    console.log('Navigating to folder:', folderId, folderName);
    
    // Para este arquivo, vamos fazer um reload simples com o parâmetro folder
    if (folderId) {
        window.location.href = '{{ route("admin.files.index") }}?folder=' + folderId;
    } else {
        window.location.href = '{{ route("admin.files.index") }}';
    }
}
function syncAllFiles() {
    var button = document.getElementById('syncAllBtn');
    var originalText = button.innerHTML;
    
    // Desabilitar botão e mostrar loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sincronizando...';
    
    // Mostrar notificação
    toastr.info('Iniciando sincronização completa do Google Drive...', 'Sincronização');
    
    axios.post('{{ route("admin.files.sync-all") }}', {
        max_depth: 10,
        folder_id: null
    }, {
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .then(function(response) {
        if (response.data.success) {
            toastr.success('Sincronização concluída! ' + response.data.total_files + ' arquivos processados.', 'Sucesso');
            
            // Recarregar a página para mostrar os novos arquivos
            setTimeout(function() {
                location.reload();
            }, 2000);
        } else {
            toastr.error(response.data.message || 'Erro na sincronização', 'Erro');
        }
    })
    .catch(function(error) {
        console.error('Erro na sincronização:', error);
        toastr.error(error.response && error.response.data && error.response.data.message ? error.response.data.message : 'Erro na sincronização', 'Erro');
    })
    .finally(function() {
        // Reabilitar botão
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Test function for modal
window.testModal = function() {
    console.log('Testing modal...');
    showDeleteModal('test-123', 'Arquivo de Teste');
};
</script>
@endpush 