/**
 * File Manager - Google Drive Integration
 * Gerencia operações de drag and drop e navegação em pastas
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar drag and drop
    initDragAndDrop();
    
    // Inicializar navegação por clique duplo
    initDoubleClickNavigation();
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
                    moveFileToFolder(fileData.id, targetFolderId);
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
                    moveFileToFolder(fileData.id, targetFolderId);
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
                    moveFileToFolder(fileData.id, targetFolderId);
                }
            } catch (error) {
                console.error('Erro ao processar o arquivo arrastado:', error);
            }
        });
    });
}

/**
 * Inicializa a funcionalidade de navegação por clique duplo
 */
function initDoubleClickNavigation() {
    // Selecionar todos os cards de pasta
    const folderItems = document.querySelectorAll('.file-item[data-is-folder="true"]');
    
    folderItems.forEach(folder => {
        folder.addEventListener('dblclick', function(e) {
            // Prevenir comportamento padrão se o clique não foi em um link ou botão
            if (!e.target.closest('a') && !e.target.closest('button')) {
                e.preventDefault();
                const folderId = this.dataset.id;
                navigateToFolder(folderId);
            }
        });
    });
}

/**
 * Move um arquivo para uma pasta
 */
function moveFileToFolder(fileId, folderId) {
    // Mostrar indicador de carregamento
    toastr.info('Movendo arquivo...');
    
    // Enviar solicitação para mover o arquivo
    axios.put(`${window.location.origin}/dashboard/files/${fileId}/move`, { 
        folder_id: folderId === '' || folderId === null || folderId === undefined ? null : folderId 
    })
    .then(response => {
        if (response.data.success) {
            toastr.success(response.data.message);
            
            // Recarregar a página após o movimento bem-sucedido
            // Adicionando um pequeno atraso para garantir que a sincronização seja concluída
            setTimeout(() => {
                // Forçar um recarregamento completo da página para garantir que os dados sejam atualizados
                window.location.href = window.location.href.split('?')[0] + (window.location.search || '');
            }, 500);
        } else {
            toastr.error(response.data.message || 'Erro ao mover arquivo');
        }
    })
    .catch(error => {
        console.error('Erro ao mover arquivo:', error);
        toastr.error(error.response?.data?.message || 'Erro ao mover arquivo');
    });
}

/**
 * Navega para uma pasta específica
 */
function navigateToFolder(folderId) {
    window.location.href = `${window.location.origin}/dashboard/files?folder=${folderId}`;
}

/**
 * Adiciona estilos CSS para drag and drop
 */
function addDragDropStyles() {
    const style = document.createElement('style');
    style.textContent = `
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
        
        tr.drag-over td {
            border-top: 2px dashed #4CAF50 !important;
            border-bottom: 2px dashed #4CAF50 !important;
            background-color: rgba(76, 175, 80, 0.1);
        }
        
        tr.drag-over td:first-child {
            border-left: 2px dashed #4CAF50 !important;
        }
        
        tr.drag-over td:last-child {
            border-right: 2px dashed #4CAF50 !important;
        }
    `;
    document.head.appendChild(style);
}

// Adicionar estilos CSS quando o documento estiver carregado
document.addEventListener('DOMContentLoaded', addDragDropStyles); 