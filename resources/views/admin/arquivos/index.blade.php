@extends('layouts.admin')

@section('title', 'Gerenciador de Arquivos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Gerenciador de Arquivos</h3>
        @if($canCreate)
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
        </div>
        @endif
    </div>

    <!-- Breadcrumb e Ações -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" id="folderBreadcrumb">
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0)" onclick="navigateToFolder(null, 'Início')" class="text-decoration-none droppable-breadcrumb" data-folder-id="root">
                                    <i class="fas fa-home"></i> Início
                                </a>
                            </li>
                            @if(isset($currentFolder) && $currentFolder)
                                @if(isset($currentFolder->ancestors))
                                    @foreach($currentFolder->ancestors as $ancestor)
                                        <li class="breadcrumb-item">
                                            <a href="javascript:void(0)" onclick="navigateToFolder('{{ $ancestor->id }}', '{{ $ancestor->name }}')" class="text-decoration-none droppable-breadcrumb" data-folder-id="{{ $ancestor->id }}">
                                                {{ $ancestor->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                @endif
                                <li class="breadcrumb-item active">{{ $currentFolder->name ?? 'Raiz' }}</li>
                            @endif
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-md-end">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary active" id="viewGrid" title="Visualização em grade">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="viewList" title="Visualização em lista">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="goBackBtn" title="Voltar (Alt + ←)" onclick="goBack()" disabled>
                                <i class="fas fa-arrow-left"></i>
                            </button>
                        </div>
                        <div class="btn-group me-2">
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
            @if(empty($files))
                <div class="text-center py-5">
                    <i class="fas fa-folder-open text-muted fa-4x mb-3"></i>
                    <h5>Nenhum arquivo encontrado</h5>
                    <p class="text-muted">Faça upload de arquivos ou crie uma pasta.</p>
                </div>
            @else
                <div class="row" id="filesGrid">
                    @foreach($files as $file)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4 file-item {{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'file-folder' : 'file-type-'.strtolower(explode('/', $file->mime_type)[0] ?? 'other') }}" 
                             data-id="{{ $file->file_id ?? '' }}" 
                             data-db-id="{{ $file->id ?? '' }}"
                             data-name="{{ $file->name ?? '' }}" 
                             data-is-folder="{{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'true' : 'false' }}">
                            <div class="card h-100 border-0 shadow-sm hover-shadow {{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'droppable-folder' : 'draggable-file' }}">
                                <div class="position-relative">
                                    <div class="file-preview text-center p-4 {{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'bg-folder' : getFilePreviewClass($file->mime_type) }}">
                                        <i class="{{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'fas fa-folder' : getCustomFileIcon($file->mime_type) }} fa-3x {{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'text-warning' : getFileIconColor($file->mime_type) }}"></i>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="file-name text-truncate">
                                            @if($file->mime_type === 'application/vnd.google-apps.folder')
                                                <a href="javascript:void(0)" onclick="navigateToFolder('{{ $file->id }}', '{{ $file->name }}')" class="text-decoration-none text-dark">
                                                    {{ $file->name }}
                                                </a>
                                            @else
                                                <span class="text-dark">{{ $file->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="file-info d-flex justify-content-between">
                                        <small class="text-muted">{{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'Pasta' : getFileTypeLabel($file->mime_type) }}</small>
                                        @if($file->mime_type !== 'application/vnd.google-apps.folder')
                                            <small class="text-muted">{{ formatFileSize($file->size ?? 0) }}</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 p-2">
                                    <div class="btn-group btn-group-sm w-100">
                                        @if($file->mime_type !== 'application/vnd.google-apps.folder')
                                            <a href="javascript:void(0)" onclick="downloadFile('{{ $file->id }}')" class="btn btn-light" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @else
                                            <a href="javascript:void(0)" onclick="navigateToFolder('{{ $file->id }}', '{{ $file->name }}')" class="btn btn-light" title="Abrir">
                                                <i class="fas fa-folder-open"></i>
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-light" onclick="showRenameModal('{{ $file->id }}', '{{ $file->name }}')" title="Renomear">
                                            <i class="fas fa-edit"></i>
                                        </button>
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
            @if(empty($files))
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
                                <tr class="file-item {{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'file-folder' : 'file-type-'.strtolower(explode('/', $file->mime_type)[0] ?? 'other') }}"
                                    data-id="{{ $file->file_id ?? '' }}" 
                                    data-db-id="{{ $file->id ?? '' }}"
                                    data-name="{{ $file->name ?? '' }}" 
                                    data-is-folder="{{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'true' : 'false' }}">
                                    <td>
                                        <i class="{{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'fas fa-folder' : getCustomFileIcon($file->mime_type) }} fa-lg {{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'text-warning' : getFileIconColor($file->mime_type) }}"></i>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($file->mime_type === 'application/vnd.google-apps.folder')
                                                <a href="javascript:void(0)" onclick="navigateToFolder('{{ $file->id }}', '{{ $file->name }}')" class="text-decoration-none text-dark">
                                                    {{ $file->name }}
                                                </a>
                                            @else
                                                <span>{{ $file->name }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $file->mime_type === 'application/vnd.google-apps.folder' ? 'Pasta' : getFileTypeLabel($file->mime_type) }}</td>
                                    <td>{{ $file->mime_type === 'application/vnd.google-apps.folder' ? '-' : formatFileSize($file->size ?? 0) }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($file->mime_type !== 'application/vnd.google-apps.folder')
                                                <a href="javascript:void(0)" onclick="downloadFile('{{ $file->id }}')" class="btn btn-light" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @else
                                                <a href="javascript:void(0)" onclick="navigateToFolder('{{ $file->id }}', '{{ $file->name }}')" class="btn btn-light" title="Abrir">
                                                    <i class="fas fa-folder-open"></i>
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-light" onclick="showRenameModal('{{ $file->id }}', '{{ $file->name }}')" title="Renomear">
                                                <i class="fas fa-edit"></i>
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
                    <i class="fas fa-exclamation-triangle"></i>
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

@endsection

@push('scripts')
<script src="{{ asset('js/google-drive-sync.js') }}"></script>
<script>
$(document).ready(function() {
    // Verificar se há preferência salva
    const viewMode = localStorage.getItem('fileViewMode') || 'grid';
    
    if (viewMode === 'list') {
        $('#gridView').addClass('d-none');
        $('#listView').removeClass('d-none');
        $('#viewList').addClass('active');
        $('#viewGrid').removeClass('active');
    } else {
        $('#gridView').removeClass('d-none');
        $('#listView').addClass('d-none');
        $('#viewGrid').addClass('active');
        $('#viewList').removeClass('active');
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
    
    // Busca em tempo real
    $('#searchInput').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.file-item').each(function() {
            const fileName = $(this).data('name').toLowerCase();
            if (fileName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Filtrar por tipo de arquivo
    $('#fileTypeFilter').change(function() {
        const fileType = $(this).val();
        if (fileType === '') {
            $('.file-item').show();
        } else {
            $('.file-item').hide();
            $('.file-' + fileType).show();
        }
    });
    
    // Botão de atualizar
    $('#refreshFiles').click(function() {
        refreshCurrentFolder();
    });
    
    // Inicializar drag and drop
    initDragAndDrop();
    
    // Inicializar estado do botão voltar
    updateBackButton();
    
    // Garantir que os ícones sejam preservados após carregamento
    setTimeout(function() {
        preserveIcons();
    }, 1000);
});

// Função para inicializar drag and drop
function initDragAndDrop() {
    // Configurar eventos para elementos arrastáveis (arquivos e pastas)
    const draggableElements = document.querySelectorAll('.draggable-file, .droppable-folder');
    draggableElements.forEach(element => {
        element.setAttribute('draggable', 'true');
        
        element.addEventListener('dragstart', function(e) {
            const fileItem = this.closest('.file-item');
            console.log('File item element:', fileItem);
            console.log('File item dataset:', fileItem.dataset);
            
            const fileData = {
                id: fileItem.dataset.id,
                name: fileItem.dataset.name,
                isFolder: fileItem.dataset.isFolder === 'true'
            };
            
            console.log('Drag start data:', fileData); // Debug
            console.log('Setting data transfer...');
            
            e.dataTransfer.setData('application/json', JSON.stringify(fileData));
            console.log('Data transfer set successfully');
            
            this.classList.add('dragging');
            
            // Adicionar efeito visual
            e.dataTransfer.effectAllowed = 'move';
        });
        
        element.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
    });
    
    // Configurar eventos para pastas (alvos de soltar)
    const droppableFolders = document.querySelectorAll('.droppable-folder');
    droppableFolders.forEach(folder => {
        folder.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            this.classList.add('drag-over');
        });
        
        folder.addEventListener('dragleave', function(e) {
            // Verificar se realmente saiu da área da pasta
            const rect = this.getBoundingClientRect();
            const x = e.clientX;
            const y = e.clientY;
            
            if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                this.classList.remove('drag-over');
            }
        });
        
        folder.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            try {
                console.log('Drop event triggered');
                console.log('Available data types:', e.dataTransfer.types);
                
                const jsonData = e.dataTransfer.getData('application/json');
                console.log('Raw JSON data:', jsonData);
                
                // Verificar se os dados existem
                if (!jsonData || jsonData.trim() === '') {
                    console.warn('Dados de drag and drop vazios');
                    console.log('Tentando outros formatos...');
                    
                    // Tentar outros formatos
                    const textData = e.dataTransfer.getData('text/plain');
                    console.log('Text data:', textData);
                    
                    const htmlData = e.dataTransfer.getData('text/html');
                    console.log('HTML data:', htmlData);
                    
                    return;
                }
                
                const fileData = JSON.parse(jsonData);
                console.log('Parsed file data:', fileData);
                
                const targetFolderId = this.closest('.file-item').dataset.id;
                console.log('Target folder ID:', targetFolderId);
                
                // Verificar se os dados necessários existem
                if (!fileData || !fileData.id || !fileData.name) {
                    console.warn('Dados de arquivo incompletos:', fileData);
                    return;
                }
                
                // Verificar se não está tentando mover uma pasta para dentro dela mesma
                if (fileData.id === targetFolderId) {
                    toastr.warning('Não é possível mover uma pasta para dentro dela mesma');
                    return;
                }
                
                // Verificar se não está tentando mover uma pasta para dentro de um arquivo
                if (fileData.isFolder && this.closest('.file-item').dataset.isFolder !== 'true') {
                    toastr.warning('Só é possível mover arquivos para pastas');
                    return;
                }
                
                const action = fileData.isFolder ? 'mover a pasta' : 'mover o arquivo';
                if (confirm(`Deseja ${action} "${fileData.name}" para esta pasta?`)) {
                    moveFile(fileData.id, targetFolderId);
                }
            } catch (error) {
                console.error('Erro ao processar o arquivo arrastado:', error);
                console.error('Dados recebidos:', e.dataTransfer.getData('application/json'));
                toastr.error('Erro ao processar o arquivo arrastado');
            }
        });
    });
    
    // Adicionar indicador visual para pastas durante o drag
    document.addEventListener('dragover', function(e) {
        const target = e.target.closest('.droppable-folder');
        if (target) {
            target.classList.add('drag-over');
        }
    });
    
    document.addEventListener('dragleave', function(e) {
        const target = e.target.closest('.droppable-folder');
        if (target) {
            const rect = target.getBoundingClientRect();
            const x = e.clientX;
            const y = e.clientY;
            
            if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                target.classList.remove('drag-over');
            }
        }
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

    axios.post('{{ route("admin.files.store") }}', formData)
        .then(response => {
            if (response.data.success) {
                toastr.success('Arquivo enviado com sucesso!');
                // Recarregar apenas os arquivos da pasta atual, não toda a página
                if (currentFolderId) {
                    navigateToFolder(currentFolderId, currentFolderName);
                } else {
                    location.reload();
                }
            } else {
                toastr.error(response.data.error || 'Erro ao enviar arquivo');
            }
        })
        .catch(error => {
            toastr.error(error.response?.data?.error || 'Erro ao enviar arquivo');
        });
}

function showCreateFolderModal() {
    $('#createFolderModal').modal('show');
}

function createFolder() {
    const name = $('#folderName').val();
    if (!name) return;

    axios.post('{{ route("admin.files.create-folder") }}', {
        name,
        parent_id: currentFolderId
    })
    .then(response => {
        if (response.data.success) {
            toastr.success('Pasta criada com sucesso!');
            // Recarregar apenas os arquivos da pasta atual, não toda a página
            if (currentFolderId) {
                navigateToFolder(currentFolderId, currentFolderName);
            } else {
                location.reload();
            }
        } else {
            toastr.error(response.data.error || 'Erro ao criar pasta');
        }
    })
    .catch(error => {
        toastr.error(error.response?.data?.error || 'Erro ao criar pasta');
    });
}

function showRenameModal(id, name) {
    $('#renameFileId').val(id);
    $('#newFileName').val(name);
    $('#renameModal').modal('show');
}

function renameFile() {
    const id = $('#renameFileId').val();
    const name = $('#newFileName').val();
    if (!name) return;

    axios.put('{{ route("admin.files.rename", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', id), { 
        name: name 
    })
    .then(response => {
        if (response.data.success) {
            toastr.success('Arquivo renomeado com sucesso!');
            location.reload();
        } else {
            toastr.error(response.data.error || 'Erro ao renomear');
        }
    })
    .catch(error => {
        toastr.error(error.response?.data?.error || 'Erro ao renomear');
    });
}

function showDeleteModal(id, name) {
    console.log('showDeleteModal called with id:', id, 'name:', name);
    $('#deleteFileId').val(id);
    $('#deleteFileName').text(name);
    
    // Verificar se é uma pasta baseado no nome do arquivo ou estrutura do DOM
    const fileItem = $(`[data-id="${id}"], [data-file-id="${id}"]`);
    let isFolder = false;
    
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
    
    $('#deleteFileIsFolder').val(isFolder);
    
    // Sempre mostrar o aviso de exclusão
    $('#recursiveWarning').show();
    
    $('#deleteModal').modal('show');
}

function deleteFileFromModal() {
    const id = $('#deleteFileId').val();
    const isFolder = $('#deleteFileIsFolder').val();

    // Mostrar indicador de carregamento
    toastr.info('Processando exclusão...');

    // Sempre usar exclusão recursiva (funciona para arquivos e pastas)
    const url = '{{ route("admin.files.delete-recursive", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', id);
    const method = 'post';
    const message = 'Item excluído com sucesso!';



    $.ajax({
        method: 'POST',
        url: url,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .then(response => {
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
        console.error('Erro na requisição AJAX:', error);
        toastr.error('Erro ao processar arquivo: ' + (error.responseJSON?.message || error.statusText));
        $('#deleteModal').modal('hide');
    });
}



function downloadFile(id) {
    // Redirecionar diretamente para o download
    window.location.href = '{{ route("admin.files.download", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', id);
}

function moveFile(fileId, folderId) {
    toastr.info('Movendo arquivo...');
    
    console.log('moveFile called with:', { fileId, folderId });
    
    // Tentar encontrar o elemento de várias formas
    let fileElement = document.querySelector(`[data-id="${fileId}"]`);
    let dbId = null;
    
    if (fileElement) {
        // Se encontrou pelo data-id (Google Drive ID), pegar o data-db-id
        dbId = fileElement.getAttribute('data-db-id');
        console.log('Found by data-id, dbId:', dbId);
    }
    
    if (!dbId) {
        // Tentar encontrar pelo data-db-id diretamente (caso fileId já seja o ID do banco)
        fileElement = document.querySelector(`[data-db-id="${fileId}"]`);
        if (fileElement) {
            dbId = fileId;
            console.log('Found by data-db-id, using fileId as dbId:', dbId);
        }
    }
    
    if (!dbId) {
        // Se ainda não encontrou, tentar buscar no banco de dados via API
        console.log('Element not found, trying to find in database...');
        
        // Fazer uma requisição para buscar o arquivo pelo Google Drive ID
        axios.get('{{ route("admin.files.find-by-file-id", ["fileId" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', fileId))
        .then(response => {
            if (response.data.success && response.data.file) {
                const dbFileId = response.data.file.id;
                console.log('Found file in database, ID:', dbFileId);
                
                // Agora fazer a movimentação
                performMove(dbFileId, folderId);
            } else {
                toastr.error('Arquivo não encontrado no banco de dados');
            }
        })
        .catch(error => {
            console.error('Error finding file:', error);
            toastr.error('Erro ao buscar arquivo no banco de dados');
        });
        
        return;
    }
    
    // Se encontrou o ID do banco, fazer a movimentação
    performMove(dbId, folderId);
}

function performMove(dbId, folderId) {
    console.log('Performing move with dbId:', dbId, 'folderId:', folderId);
    
    // Se folderId for null, undefined ou string vazia, enviar null
    const folderIdToSend = (folderId && folderId !== '' && folderId !== 'null') ? folderId : null;
    console.log('Folder ID to send:', folderIdToSend);
    
    axios.put('{{ route("admin.files.move", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', dbId), { 
        folder_id: folderIdToSend 
    })
    .then(response => {
        if (response.data.success) {
            toastr.success('Arquivo movido com sucesso!');
            // Recarregar a lista atual em vez de toda a página
            if (typeof refreshCurrentFolder === 'function') {
                refreshCurrentFolder();
            } else {
                location.reload();
            }
        } else {
            toastr.error(response.data.error || 'Erro ao mover arquivo');
        }
    })
    .catch(error => {
        console.error('Error moving file:', error);
        const errorMessage = error.response?.data?.error || 'Erro ao mover arquivo';
        toastr.error(errorMessage);
    });
}

function preserveIcons() {
    // Garantir que os ícones dos arquivos sejam preservados
    $('.file-item').each(function() {
        const fileItem = $(this);
        const isFolder = fileItem.data('is-folder') === true;
        
        // Preservar ícones de pasta
        if (isFolder) {
            const previewIcon = fileItem.find('.file-preview i');
            if (previewIcon.length && !previewIcon.hasClass('fa-folder')) {
                previewIcon.removeClass().addClass('fas fa-folder fa-3x text-warning');
            }
            
            const listIcon = fileItem.find('td:first i');
            if (listIcon.length && !listIcon.hasClass('fa-folder')) {
                listIcon.removeClass().addClass('fas fa-folder fa-lg text-warning');
            }
        }
        
        // Remover todos os <i class="fa-star ..."></i> e qualquer referência visual a favoritos
    });
}

// Variáveis globais para navegação
let currentFolderId = '{{ $currentFolderId ?? null }}';
let currentFolderName = '{{ $currentFolder?->name ?? "Início" }}';
let navigationHistory = [];

// Função para navegação fluida entre pastas
function navigateToFolder(folderId, folderName) {
    console.log('Navigating to folder:', folderId, folderName);
    
    // Mostrar loading
    showLoading();
    
    // Atualizar histórico de navegação
    if (folderId !== currentFolderId) {
        navigationHistory.push({
            id: currentFolderId,
            name: currentFolderName
        });
        currentFolderId = folderId;
        currentFolderName = folderName;
    }
    
    // Fazer requisição AJAX para carregar os arquivos da pasta
    const token = $('meta[name="csrf-token"]').attr('content');
    axios.get('{{ route("admin.files.index") }}', {
        params: {
            folder: folderId
        },
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.data.success) {
            updateFileList(response.data.files, response.data.breadcrumb, folderId, folderName);
            updateBreadcrumb(response.data.breadcrumb, folderName);
            updateBackButton();
            hideLoading();
        } else {
            toastr.error(response.data.error || 'Erro ao carregar pasta');
            hideLoading();
        }
    })
    .catch(error => {
        console.error('Error navigating to folder:', error);
        toastr.error('Erro ao navegar para a pasta');
        hideLoading();
    });
}

// Função para atualizar a lista de arquivos
function updateFileList(files, breadcrumb, folderId, folderName) {
    const gridContainer = $('#filesGrid');
    const listContainer = $('#listView tbody');
    
    // Limpar containers
    gridContainer.empty();
    listContainer.empty();
    
    if (files.length === 0) {
        // Mostrar mensagem de pasta vazia
        gridContainer.html(`
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-folder-open text-muted fa-4x mb-3"></i>
                    <h5>Pasta vazia</h5>
                    <p class="text-muted">Esta pasta não contém arquivos.</p>
                </div>
            </div>
        `);
        
        listContainer.html(`
            <tr>
                <td colspan="5" class="text-center py-5">
                    <i class="fas fa-folder-open text-muted fa-4x mb-3"></i>
                    <h5>Pasta vazia</h5>
                    <p class="text-muted">Esta pasta não contém arquivos.</p>
                </td>
            </tr>
        `);
        return;
    }
    
    // Gerar HTML para visualização em grade
    files.forEach(file => {
        console.log('Processing file:', {
            id: file.id,
            file_id: file.file_id,
            name: file.name,
            mime_type: file.mime_type
        });
        
        const isFolder = file.mime_type === 'application/vnd.google-apps.folder';
        const fileIcon = isFolder ? 'fas fa-folder' : getFileIcon(file.mime_type);
        const fileIconColor = isFolder ? 'text-warning' : getFileIconColor(file.mime_type);
        const fileType = isFolder ? 'Pasta' : getFileTypeLabel(file.mime_type);
        const fileSize = isFolder ? '-' : formatFileSize(file.size || 0);
        
        const gridItem = `
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4 file-item ${isFolder ? 'file-folder' : 'file-type-' + getFileType(file.mime_type)}" 
                 data-id="${file.file_id || file.id}" 
                 data-db-id="${file.id}"
                 data-name="${file.name}" 
                 data-is-folder="${isFolder}">
                <div class="card h-100 border-0 shadow-sm hover-shadow ${isFolder ? 'droppable-folder' : 'draggable-file'}">
                    <div class="position-relative">
                        <div class="file-preview text-center p-4 ${isFolder ? 'bg-folder' : getFilePreviewClass(file.mime_type)}">
                            <i class="${fileIcon} fa-3x ${fileIconColor}"></i>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="file-name text-truncate">
                                                            ${isFolder ? 
                                `<a href="javascript:void(0)" onclick="navigateToFolder('${file.file_id || file.id}', '${file.name}')" class="text-decoration-none text-dark">${file.name}</a>` :
                                `<span class="text-dark">${file.name}</span>`
                            }
                            </div>
                        </div>
                        <div class="file-info d-flex justify-content-between">
                            <small class="text-muted">${fileType}</small>
                            ${!isFolder ? `<small class="text-muted">${fileSize}</small>` : ''}
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 p-2">
                        <div class="btn-group btn-group-sm w-100">
                            ${isFolder ? 
                                `<a href="javascript:void(0)" onclick="navigateToFolder('${file.file_id || file.id}', '${file.name}')" class="btn btn-light" title="Abrir"><i class="fas fa-folder-open"></i></a>` :
                                `<a href="javascript:void(0)" onclick="downloadFile('${file.id}')" class="btn btn-light" title="Download"><i class="fas fa-download"></i></a>`
                            }
                            <button type="button" class="btn btn-light" onclick="showRenameModal('${file.id}', '${file.name}')" title="Renomear">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-light text-danger" onclick="showDeleteModal('${file.id}', '${file.name}')" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const listItem = `
            <tr class="file-item ${isFolder ? 'file-folder' : 'file-type-' + getFileType(file.mime_type)}"
                data-id="${file.file_id || file.id}" 
                data-db-id="${file.id}"
                data-name="${file.name}" 
                data-is-folder="${isFolder}">
                <td>
                    <i class="${fileIcon} fa-lg ${fileIconColor}"></i>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        ${isFolder ? 
                            `<a href="javascript:void(0)" onclick="navigateToFolder('${file.file_id || file.id}', '${file.name}')" class="text-decoration-none text-dark">${file.name}</a>` :
                            `<span>${file.name}</span>`
                        }
                    </div>
                </td>
                <td>${fileType}</td>
                <td>${fileSize}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        ${isFolder ? 
                            `<a href="javascript:void(0)" onclick="navigateToFolder('${file.file_id || file.id}', '${file.name}')" class="btn btn-light" title="Abrir"><i class="fas fa-folder-open"></i></a>` :
                            `<a href="javascript:void(0)" onclick="downloadFile('${file.id}')" class="btn btn-light" title="Download"><i class="fas fa-download"></i></a>`
                        }
                        <button type="button" class="btn btn-light" onclick="showRenameModal('${file.id}', '${file.name}')" title="Renomear">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-light text-danger" onclick="showDeleteModal('${file.id}', '${file.name}')" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        
        gridContainer.append(gridItem);
        listContainer.append(listItem);
    });
    
    // Reinicializar drag and drop
    initDragAndDrop();
}

// Função para atualizar o breadcrumb
function updateBreadcrumb(breadcrumb, currentName) {
    const breadcrumbContainer = $('#folderBreadcrumb');
    breadcrumbContainer.empty();
    
    // Adicionar link para início
    breadcrumbContainer.append(`
        <li class="breadcrumb-item">
            <a href="javascript:void(0)" onclick="navigateToFolder(null, 'Início')" class="text-decoration-none droppable-breadcrumb" data-folder-id="root">
                <i class="fas fa-home"></i> Início
            </a>
        </li>
    `);
    
    // Adicionar breadcrumb da pasta atual
    if (breadcrumb && breadcrumb.length > 0) {
        breadcrumb.forEach(item => {
            breadcrumbContainer.append(`
                <li class="breadcrumb-item">
                    <a href="javascript:void(0)" onclick="navigateToFolder('${item.id}', '${item.name}')" class="text-decoration-none droppable-breadcrumb" data-folder-id="${item.id}">
                        ${item.name}
                    </a>
                </li>
            `);
        });
    }
    
    // Adicionar pasta atual
    if (currentName && currentName !== 'Início') {
        breadcrumbContainer.append(`
            <li class="breadcrumb-item active">${currentName}</li>
        `);
    }
}

// Funções auxiliares para ícones e cores
function getFileIcon(mimeType) {
    if (!mimeType) return 'fas fa-file';
    
    const mainType = mimeType.split('/')[0];
    const subType = mimeType.split('/')[1] || '';
    
    switch (mainType) {
        case 'image': return 'fas fa-file-image';
        case 'video': return 'fas fa-file-video';
        case 'audio': return 'fas fa-file-audio';
        case 'text': return 'fas fa-file-alt';
        case 'application':
            if (subType.includes('pdf')) return 'fas fa-file-pdf';
            if (subType.includes('word') || subType.includes('document')) return 'fas fa-file-word';
            if (subType.includes('excel') || subType.includes('spreadsheet')) return 'fas fa-file-excel';
            if (subType.includes('powerpoint') || subType.includes('presentation')) return 'fas fa-file-powerpoint';
            if (subType.includes('zip') || subType.includes('rar') || subType.includes('tar')) return 'fas fa-file-archive';
            if (subType.includes('json') || subType.includes('xml') || subType.includes('javascript')) return 'fas fa-file-code';
            return 'fas fa-file';
        default: return 'fas fa-file';
    }
}

function getFileIconColor(mimeType) {
    if (!mimeType) return '';
    
    const mainType = mimeType.split('/')[0];
    const subType = mimeType.split('/')[1] || '';
    
    switch (mainType) {
        case 'image': return 'text-success';
        case 'video': return 'text-danger';
        case 'audio': return 'text-info';
        case 'text': return 'text-primary';
        case 'application':
            if (subType.includes('pdf')) return 'text-danger';
            if (subType.includes('word') || subType.includes('document')) return 'text-primary';
            if (subType.includes('excel') || subType.includes('spreadsheet')) return 'text-success';
            if (subType.includes('powerpoint') || subType.includes('presentation')) return 'text-warning';
            if (subType.includes('zip') || subType.includes('rar') || subType.includes('tar')) return 'text-secondary';
            if (subType.includes('json') || subType.includes('xml') || subType.includes('javascript')) return 'text-info';
            return '';
        default: return '';
    }
}

function getFileType(mimeType) {
    if (!mimeType) return 'other';
    
    const mainType = mimeType.split('/')[0];
    const subType = mimeType.split('/')[1] || '';
    
    switch (mainType) {
        case 'image': return 'image';
        case 'video': return 'video';
        case 'audio': return 'audio';
        case 'text': return 'document';
        case 'application':
            if (subType.includes('pdf')) return 'document';
            if (subType.includes('word') || subType.includes('document')) return 'document';
            if (subType.includes('excel') || subType.includes('spreadsheet')) return 'document';
            if (subType.includes('powerpoint') || subType.includes('presentation')) return 'document';
            if (subType.includes('zip') || subType.includes('rar') || subType.includes('tar')) return 'other';
            if (subType.includes('json') || subType.includes('xml') || subType.includes('javascript')) return 'document';
            return 'other';
        default: return 'other';
    }
}

function getFileTypeLabel(mimeType) {
    if (!mimeType) return 'Arquivo';
    
    const mainType = mimeType.split('/')[0];
    const subType = mimeType.split('/')[1] || '';
    
    switch (mainType) {
        case 'image': return 'Imagem';
        case 'video': return 'Vídeo';
        case 'audio': return 'Áudio';
        case 'text': return 'Texto';
        case 'application':
            if (subType.includes('pdf')) return 'PDF';
            if (subType.includes('word') || subType.includes('document')) return 'Documento';
            if (subType.includes('excel') || subType.includes('spreadsheet')) return 'Planilha';
            if (subType.includes('powerpoint') || subType.includes('presentation')) return 'Apresentação';
            if (subType.includes('zip') || subType.includes('rar') || subType.includes('tar')) return 'Arquivo compactado';
            if (subType.includes('json') || subType.includes('xml') || subType.includes('javascript')) return 'Código';
            return 'Arquivo';
        default: return 'Arquivo';
    }
}

function getFilePreviewClass(mimeType) {
    if (!mimeType) return '';
    
    const mainType = mimeType.split('/')[0];
    const subType = mimeType.split('/')[1] || '';
    
    switch (mainType) {
        case 'image': return 'bg-image';
        case 'video': return 'bg-video';
        case 'audio': return 'bg-audio';
        case 'text': return 'bg-code';
        case 'application':
            if (subType.includes('pdf')) return 'bg-pdf';
            if (subType.includes('word') || subType.includes('document')) return 'bg-document';
            if (subType.includes('excel') || subType.includes('spreadsheet')) return 'bg-spreadsheet';
            if (subType.includes('powerpoint') || subType.includes('presentation')) return 'bg-presentation';
            if (subType.includes('zip') || subType.includes('rar') || subType.includes('tar')) return 'bg-archive';
            if (subType.includes('json') || subType.includes('xml') || subType.includes('javascript')) return 'bg-code';
            return '';
        default: return '';
    }
}

function formatFileSize(bytes) {
    if (bytes == 0) return '0 B';
    
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + units[i];
}

// Funções de loading
function showLoading() {
    // Adicionar overlay de loading
    if (!$('#loadingOverlay').length) {
        $('body').append(`
            <div id="loadingOverlay" style="z-index:999;position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando pasta...</p>
                </div>
            </div>
        `);
    }
    $('#loadingOverlay').show();
}

function hideLoading() {
    $('#loadingOverlay').hide();
}

// Navegação com histórico (botões voltar/avançar)
function goBack() {
    if (navigationHistory.length > 0) {
        const previousFolder = navigationHistory.pop();
        navigateToFolder(previousFolder.id, previousFolder.name);
    }
}

// Função para atualizar estado do botão voltar
function updateBackButton() {
    const backBtn = $('#goBackBtn');
    if (navigationHistory.length > 0) {
        backBtn.prop('disabled', false).removeClass('disabled');
    } else {
        backBtn.prop('disabled', true).addClass('disabled');
    }
}

// Função para recarregar a pasta atual
function refreshCurrentFolder() {
    navigateToFolder(currentFolderId, currentFolderName);
}

// Adicionar suporte a teclas de atalho
$(document).keydown(function(e) {
    // Alt + Seta Esquerda = Voltar
    if (e.altKey && e.keyCode === 37) {
        e.preventDefault();
        goBack();
    }
    
    // F5 = Atualizar pasta atual
    if (e.keyCode === 116) {
        e.preventDefault();
        refreshCurrentFolder();
    }
});
</script>

<style>
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

.bg-archive {
    background-color: rgba(156, 39, 176, 0.1);
}

.bg-code {
    background-color: rgba(96, 125, 139, 0.1);
}

.bg-video {
    background-color: rgba(244, 67, 54, 0.1);
}

.bg-audio {
    background-color: rgba(0, 188, 212, 0.1);
}

/* Animações para navegação fluida */
.file-item {
    transition: all 0.3s ease;
}

.file-item:hover {
    transform: translateY(-2px);
}

/* Loading overlay */
#loadingOverlay {
    backdrop-filter: blur(2px);
}

/* Botão voltar desabilitado */
.btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Breadcrumb hover effects */
.breadcrumb-item a:hover {
    color: #007bff !important;
    text-decoration: underline !important;
}

/* Smooth transitions */
.card, .btn, .breadcrumb-item {
    transition: all 0.2s ease;
}

/* Drag and Drop styles */
.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.drag-over {
    background-color: rgba(0, 123, 255, 0.1) !important;
    border: 2px dashed #007bff !important;
    transform: scale(1.05);
}

.droppable-folder.drag-over {
    background-color: rgba(255, 193, 7, 0.2) !important;
    border: 2px dashed #ffc107 !important;
}

.droppable-folder.drag-over .file-preview {
    background-color: rgba(255, 193, 7, 0.3) !important;
}

/* Cursor styles */
.draggable-file {
    cursor: grab;
}

.draggable-file:active {
    cursor: grabbing;
}

.droppable-folder {
    cursor: pointer;
}

.droppable-folder.drag-over {
    cursor: copy;
}

/* List view styles */
#listView .table {
    margin-bottom: 0;
}

#listView .table th {
    border-top: none;
    font-weight: 600;
    color: #6c757d;
}

#listView .table td {
    vertical-align: middle;
    border-top: 1px solid #f8f9fa;
}

#listView .file-item:hover {
    background-color: #f8f9fa;
}

#listView .btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Ensure list view is properly displayed */
#listView.d-none {
    display: none !important;
}

#listView:not(.d-none) {
    display: block !important;
}

/* Botão de favorito na parte superior do card */
.btn-sm.position-absolute.top-0.end-0 {
    background-color: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(5px);
    transition: all 0.2s ease;
}

.btn-sm.position-absolute.top-0.end-0:hover {
    background-color: rgba(255, 255, 255, 1);
    transform: scale(1.1);
}

.btn-sm.position-absolute.top-0.end-0 .fa-star.text-warning {
    animation: pulse 0.3s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
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

.text-image {
    color: #4CAF50;
}

.text-pdf {
    color: #F44336;
}

.text-document {
    color: #2196F3;
}

.text-spreadsheet {
    color: #4CAF50;
}

.text-presentation {
    color: #FF9800;
}

.text-video {
    color: #9C27B0;
}

.text-audio {
    color: #00BCD4;
}

.text-archive {
    color: #795548;
}

.text-code {
    color: #607D8B;
}

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

.dragging {
    opacity: 0.5;
}
</style>
@endpush

@php
function getFileIconColor($mimeType) {
    if (!$mimeType) return '';
    
    $mainType = explode('/', $mimeType)[0];
    $subType = explode('/', $mimeType)[1] ?? '';
    
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
    
    $mainType = explode('/', $mimeType)[0];
    $subType = explode('/', $mimeType)[1] ?? '';
    
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

function getCustomFileIcon($mimeType) {
    if (!$mimeType) return 'fas fa-file';
    
    $mainType = explode('/', $mimeType)[0];
    $subType = explode('/', $mimeType)[1] ?? '';
    
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
    
    $mainType = explode('/', $mimeType)[0];
    $subType = explode('/', $mimeType)[1] ?? '';
    
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

function formatFileSize($bytes) {
    if ($bytes == 0) return '0 B';
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes, 1024));
    
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}
@endphp

<script>
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
</script> 