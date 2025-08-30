/**
 * Google Drive Sync - Sincronização em tempo real
 */
class GoogleDriveSync {
    constructor() {
        this.lastSync = null;
        this.syncInterval = null;
        this.isSyncing = false;
        this.currentFolderId = null;
        this.init();
    }

    init() {
        // Inicializar sincronização
        this.startAutoSync();
        
        // Adicionar botão de sincronização manual
        this.addSyncButton();
        
        // Sincronizar após upload/operações
        this.bindToOperations();
    }

    /**
     * Inicia sincronização automática
     */
    startAutoSync() {
        // Sincronizar a cada 30 segundos
        this.syncInterval = setInterval(() => {
            this.checkForChanges();
        }, 30000);

        // Primeira sincronização
        this.sync();
    }

    /**
     * Para sincronização automática
     */
    stopAutoSync() {
        if (this.syncInterval) {
            clearInterval(this.syncInterval);
            this.syncInterval = null;
        }
    }

    /**
     * Sincronização manual
     */
    sync() {
        if (this.isSyncing) return;

        this.isSyncing = true;
        this.showSyncIndicator();

        const folderId = this.getCurrentFolderId();

        axios.post('/dashboard/files/sync', {
            folder_id: folderId
        })
        .then(response => {
            if (response.data.success) {
                this.handleSyncSuccess(response.data);
            } else {
                this.handleSyncError(response.data.error);
            }
        })
        .catch(error => {
            this.handleSyncError('Erro na sincronização: ' + error.message);
        })
        .finally(() => {
            this.isSyncing = false;
            this.hideSyncIndicator();
        });
    }

    /**
     * Verifica mudanças
     */
    checkForChanges() {
        if (this.isSyncing) return;

        const folderId = this.getCurrentFolderId();

        axios.get('/dashboard/files/check-changes', {
            params: {
                folder_id: folderId,
                last_sync: this.lastSync
            }
        })
        .then(response => {
            if (response.data.success && response.data.has_changes) {
                this.handleSyncSuccess(response.data);
                this.showNotification('Arquivos atualizados automaticamente', 'success');
            }
        })
        .catch(error => {
            console.error('Erro ao verificar mudanças:', error);
        });
    }

    /**
     * Manipula sucesso da sincronização
     */
    handleSyncSuccess(data) {
        this.lastSync = data.last_sync || new Date().toISOString();
        
        if (data.files) {
            this.updateFileList(data.files);
        }

        if (data.stats) {
            this.updateSyncStats(data.stats);
        }
    }

    /**
     * Manipula erro da sincronização
     */
    handleSyncError(error) {
        console.error('Erro na sincronização:', error);
        this.showNotification('Erro na sincronização: ' + error, 'error');
    }

    /**
     * Atualiza lista de arquivos
     */
    updateFileList(files) {
        const gridContainer = $('#filesGrid');
        const listContainer = $('#listView tbody');
        
        if (!gridContainer.length) return;

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
            const isFolder = file.mimeType === 'application/vnd.google-apps.folder';
            const fileIcon = isFolder ? 'fas fa-folder' : this.getFileIcon(file.mimeType);
            const fileIconColor = isFolder ? 'text-warning' : this.getFileIconColor(file.mimeType);
            const fileType = isFolder ? 'Pasta' : this.getFileTypeLabel(file.mimeType);
            const fileSize = isFolder ? '-' : this.formatFileSize(file.size || 0);
            
            const gridItem = `
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4 file-item ${isFolder ? 'file-folder' : 'file-type-' + this.getFileType(file.mimeType)}" 
                     data-id="${file.id}" 
                     data-name="${file.name}" 
                     data-is-folder="${isFolder}">
                    <div class="card h-100 border-0 shadow-sm hover-shadow ${isFolder ? 'droppable-folder' : 'draggable-file'}">
                        <div class="position-relative">
                            <div class="file-preview text-center p-4 ${isFolder ? 'bg-folder' : this.getFilePreviewClass(file.mimeType)}">
                                <i class="${fileIcon} fa-3x ${fileIconColor}"></i>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="file-name text-truncate">
                                    ${isFolder ? 
                                        `<a href="javascript:void(0)" onclick="navigateToFolder('${file.id}', '${file.name}')" class="text-decoration-none text-dark">${file.name}</a>` :
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
                                    `<a href="javascript:void(0)" onclick="navigateToFolder('${file.id}', '${file.name}')" class="btn btn-light" title="Abrir"><i class="fas fa-folder-open"></i></a>` :
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
                <tr class="file-item ${isFolder ? 'file-folder' : 'file-type-' + this.getFileType(file.mimeType)}"
                    data-id="${file.id}" 
                    data-name="${file.name}" 
                    data-is-folder="${isFolder}">
                    <td>
                        <i class="${fileIcon} fa-lg ${fileIconColor}"></i>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            ${isFolder ? 
                                `<a href="javascript:void(0)" onclick="navigateToFolder('${file.id}', '${file.name}')" class="text-decoration-none text-dark">${file.name}</a>` :
                                `<span>${file.name}</span>`
                            }
                        </div>
                    </td>
                    <td>${fileType}</td>
                    <td>${fileSize}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            ${isFolder ? 
                                `<a href="javascript:void(0)" onclick="navigateToFolder('${file.id}', '${file.name}')" class="btn btn-light" title="Abrir"><i class="fas fa-folder-open"></i></a>` :
                                `<a href="javascript:void(0)" onclick="downloadFile('${file.id}')" class="btn btn-light" title="Download"><i class="fas fa-download"></i></a>`
                            }
                            <button type="button" class="btn btn-light" onclick="toggleFavorite('${file.id}')" title="Favoritar">
                                <i class="fas fa-star ${file.is_starred ? 'text-warning' : 'text-muted'}"></i>
                            </button>
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
        
        // Reinicializar drag and drop se existir
        if (typeof initDragAndDrop === 'function') {
            initDragAndDrop();
        }
        
        // Preservar ícones após atualização
        if (typeof preserveIcons === 'function') {
            setTimeout(() => {
                preserveIcons();
            }, 100);
        }
    }

    /**
     * Atualiza estatísticas de sincronização
     */
    updateSyncStats(stats) {
        // Atualizar contador de arquivos se existir
        const fileCount = document.querySelector('.file-count');
        if (fileCount) {
            fileCount.textContent = stats.total;
        }

        // Mostrar notificação se houve mudanças
        if (stats.created > 0 || stats.updated > 0 || stats.deleted > 0) {
            let message = '';
            if (stats.created > 0) message += `${stats.created} novo(s) arquivo(s) `;
            if (stats.updated > 0) message += `${stats.updated} arquivo(s) atualizado(s) `;
            if (stats.deleted > 0) message += `${stats.deleted} arquivo(s) removido(s)`;
            
            this.showNotification(message, 'info');
        }
    }

    /**
     * Adiciona botão de sincronização manual
     */
    addSyncButton() {
        const syncButton = `
            <button type="button" class="btn btn-outline-secondary" id="manualSyncBtn" title="Sincronizar manualmente">
                <i class="fas fa-sync-alt"></i>
                <span class="sync-indicator d-none">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </button>
        `;

        // Adicionar ao grupo de botões existente
        const buttonGroup = document.querySelector('.btn-group');
        if (buttonGroup) {
            buttonGroup.insertAdjacentHTML('beforeend', syncButton);
        }

        // Adicionar evento de clique
        document.getElementById('manualSyncBtn')?.addEventListener('click', () => {
            this.sync();
        });
    }

    /**
     * Vincula sincronização às operações
     */
    bindToOperations() {
        // Sincronizar após upload
        const originalUploadFile = window.uploadFile;
        window.uploadFile = function(input) {
            originalUploadFile(input);
            setTimeout(() => {
                window.googleDriveSync?.sync();
            }, 2000);
        };

        // Sincronizar após outras operações
        const operations = ['createFolder', 'renameFile', 'deleteFile', 'moveFile'];
        operations.forEach(operation => {
            const original = window[operation];
            if (original) {
                window[operation] = function(...args) {
                    const result = original.apply(this, args);
                    setTimeout(() => {
                        window.googleDriveSync?.sync();
                    }, 2000);
                    return result;
                };
            }
        });
    }

    /**
     * Mostra indicador de sincronização
     */
    showSyncIndicator() {
        const indicator = document.querySelector('.sync-indicator');
        if (indicator) {
            indicator.classList.remove('d-none');
        }
    }

    /**
     * Esconde indicador de sincronização
     */
    hideSyncIndicator() {
        const indicator = document.querySelector('.sync-indicator');
        if (indicator) {
            indicator.classList.add('d-none');
        }
    }

    /**
     * Mostra notificação
     */
    showNotification(message, type = 'info') {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    /**
     * Obtém ID da pasta atual
     */
    getCurrentFolderId() {
        // Tentar obter do breadcrumb ou variável global
        const breadcrumbItem = document.querySelector('.breadcrumb-item.active');
        if (breadcrumbItem) {
            const folderId = breadcrumbItem.dataset.folderId;
            if (folderId && folderId !== 'root') {
                return folderId;
            }
        }
        
        return null; // Pasta raiz
    }

    // Funções auxiliares para ícones e formatação
    getFileIcon(mimeType) {
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

    getFileIconColor(mimeType) {
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

    getFileType(mimeType) {
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

    getFileTypeLabel(mimeType) {
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

    getFilePreviewClass(mimeType) {
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

    formatFileSize(bytes) {
        if (bytes == 0) return '0 B';
        
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + units[i];
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos na página de arquivos
    if (window.location.pathname.includes('/files') || document.querySelector('#filesGrid')) {
        window.googleDriveSync = new GoogleDriveSync();
    }
}); 