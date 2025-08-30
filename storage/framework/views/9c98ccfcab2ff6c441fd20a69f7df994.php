<?php $__env->startSection('title', 'Inscrições'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mt-4 mb-0">
                <i class="fas fa-clipboard-list me-2"></i>
                Gerenciar Inscrições
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Inscrições</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.inscricoes.importar')); ?>" class="btn btn-primary">
                <i class="fas fa-upload me-2"></i>Importar CSV
            </a>
            <a href="<?php echo e(route('admin.inscricoes.exportar', request()->query())); ?>" class="btn btn-success">
                <i class="fas fa-download me-2"></i>Exportar CSV
            </a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('inscricoes.delete')): ?>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalApagarTodas">
                    <i class="fas fa-trash-alt me-2"></i>
                    <?php if(auth()->user()->isAdmin()): ?>
                        Apagar Todas
                    <?php else: ?>
                        Apagar Minhas
                    <?php endif; ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Indicador de Cooldown -->
    <div id="cooldownAlert" class="alert alert-warning alert-dismissible fade show d-none shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-clock me-2"></i>
            <div>
                <strong>Cooldown Ativo!</strong>
                <span id="cooldownMessage">Aguarde antes de pegar outro lead.</span>
            </div>
        </div>
        <div class="progress mt-2" style="height: 6px;">
            <div id="cooldownProgress" class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" style="width: 0%"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <form method="GET" action="<?php echo e(route('admin.inscricoes')); ?>" class="row g-0" id="filterForm">
                        <!-- Barra de Busca Principal -->
                        <div class="col-12 bg-light border-bottom p-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 ps-0" 
                                       id="busca" 
                                       name="busca" 
                                       value="<?php echo e(request('busca')); ?>" 
                                       placeholder="Buscar por nome, email ou telefone...">
                            </div>
                        </div>
                        
                        <!-- Filtros Avançados -->
                        <div class="col-12 p-3">
                            <div class="row g-3">
                                <!-- Período -->
                                <div class="col-md-8">
                                    <label class="form-label d-flex align-items-center fw-bold">
                                        <i class="fas fa-calendar me-2 text-muted"></i>
                                        Período
                                    </label>
                                    <div class="input-group">
                                        <input type="date" 
                                               class="form-control" 
                                               id="data_inicio" 
                                               name="data_inicio" 
                                               value="<?php echo e(request('data_inicio')); ?>"
                                               placeholder="Data Início"
                                               max="<?php echo e(now()->format('Y-m-d')); ?>">
                                        <span class="input-group-text bg-light">até</span>
                                        <input type="date" 
                                               class="form-control" 
                                               id="data_fim" 
                                               name="data_fim" 
                                               value="<?php echo e(request('data_fim')); ?>"
                                               placeholder="Data Fim"
                                               max="<?php echo e(now()->format('Y-m-d')); ?>">
                                    </div>
                                </div>
                                
                                <!-- Botões de Ação -->
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="d-flex gap-2 w-100">
                                        <a href="<?php echo e(route('admin.inscricoes')); ?>" class="btn btn-light flex-grow-1" id="clearFilters">
                                            <i class="fas fa-eraser me-1"></i>
                                            Limpar
                                        </a>
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-filter me-1"></i>
                                            Filtrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-1"></i>
                        Lista de Inscrições
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="text-muted">
                        Total: <?php echo e($inscricoes->total()); ?> inscrições
                    </span>
                    <?php if(request()->hasAny(['busca', 'data_inicio', 'data_fim'])): ?>
                        <span class="badge bg-primary ms-2">Filtros aplicados</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0" id="listaInscricoes">
            <?php echo $__env->make('admin.inscricoes._lista', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    <?php echo $__env->make('admin.inscricoes._modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Função para pegar lead
    function pegarLead(id) {
        fetch(`/dashboard/inscricoes/${id}/etiqueta`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                etiqueta: 'contatado',
                observacoes: 'Lead atribuído automaticamente'
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                toastr.success('Lead atribuído com sucesso!');
                // Atualizar a lista
                atualizarLista();
            } else if (data.cooldown) {
                // Mostrar alerta de cooldown
                document.getElementById('cooldownAlert').classList.remove('d-none');
                document.getElementById('cooldownMessage').textContent = data.message;
                
                // Iniciar contagem regressiva
                let remainingSeconds = data.remaining_seconds;
                const progressBar = document.getElementById('cooldownProgress');
                const totalSeconds = remainingSeconds;
                
                const interval = setInterval(() => {
                    remainingSeconds--;
                    const progress = ((totalSeconds - remainingSeconds) / totalSeconds) * 100;
                    progressBar.style.width = `${progress}%`;
                    
                    const minutes = Math.floor(remainingSeconds / 60);
                    const seconds = remainingSeconds % 60;
                    const timeFormatted = minutes > 0 
                        ? `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
                        : `00:${String(seconds).padStart(2, '0')}`;
                    document.getElementById('cooldownMessage').textContent = 
                        `Aguarde ${timeFormatted} antes de pegar outro lead.`;
                    
                    if (remainingSeconds <= 0) {
                        clearInterval(interval);
                        document.getElementById('cooldownAlert').classList.add('d-none');
                    }
                }, 1000);
            } else {
                toastr.error(data.message || 'Erro ao atribuir lead');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            toastr.error('Erro ao atribuir lead');
        });
    }

    // Função para destravar
    function destravar(id) {
        if (confirm('Tem certeza que deseja liberar este lead?')) {
            fetch(`/dashboard/inscricoes/${id}/unlock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    toastr.success('Lead liberado com sucesso!');
                    // Atualizar a lista
                    atualizarLista();
                } else {
                    toastr.error(data.message || 'Erro ao liberar lead');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                toastr.error('Erro ao liberar lead');
            });
        }
    }

    // Função para ver detalhes
    function verDetalhes(id) {
        const modal = new bootstrap.Modal(document.getElementById('modalDetalhes'));
        modal.show();
                
        fetch(`/dashboard/inscricoes/${id}/detalhes-modal`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao carregar detalhes');
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('detalhesConteudo').innerHTML = html;
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('detalhesConteudo').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Erro ao carregar os detalhes. Por favor, tente novamente.
                    </div>
                `;
            });
    }

    // Função para confirmar exclusão
    function confirmarExclusao(id, nome) {
        document.getElementById('nomeInscricao').textContent = nome;
        document.getElementById('formExclusao').action = `/dashboard/inscricoes/${id}`;
        const modal = new bootstrap.Modal(document.getElementById('modalExclusao'));
        modal.show();
    }

    // Função para atualizar a lista
    function atualizarLista() {
        const url = new URL(window.location.href);
        return fetch(url.pathname + url.search, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('listaInscricoes').innerHTML = html;
                return html;
            })
            .catch(error => {
                console.error('Erro:', error);
                toastr.error('Erro ao atualizar a lista');
                throw error;
            });
    }

    // Atualizar lista a cada 30 segundos
    setInterval(atualizarLista, 30000);

    // Limpar filtros
    document.getElementById('clearFilters').addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = this.href;
    });

    // Manipular cliques na paginação para usar AJAX
    function initializePaginationAjax() {
        const paginationLinks = document.querySelectorAll('#listaInscricoes .pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                if (url && url !== '#') {
                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(html => {
                        document.getElementById('listaInscricoes').innerHTML = html;
                        // Atualizar URL sem recarregar a página
                        window.history.pushState({}, '', url);
                        // Re-inicializar eventos de paginação para os novos links
                        initializePaginationAjax();
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        toastr.error('Erro ao navegar');
                    });
                }
            });
        });
    }

    // Inicializar paginação AJAX na carga da página
    initializePaginationAjax();

    // Re-inicializar paginação AJAX após atualização da lista
    const originalAtualizarLista = atualizarLista;
    atualizarLista = function() {
        return originalAtualizarLista().then(() => {
            initializePaginationAjax();
        }).catch(() => {
            // Fallback se houver erro
        });
    };

    // Manipular navegação do browser (botão voltar/avançar)
    window.addEventListener('popstate', function(event) {
        atualizarLista();
    });
</script>

<style>
/* Estilos para Desktop */
@media (min-width: 768px) {
    .table-responsive {
        margin-bottom: 0;
        border: 0;
    }

    .table {
        margin-bottom: 0;
    }

    .table td {
        vertical-align: middle;
    }
}

/* Estilos para Mobile */
@media (max-width: 767.98px) {
    .inscricoes-grid {
        padding: 1rem;
    }
    
    .card {
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid rgba(0,0,0,0.1);
    }
    
    .card.border-warning {
        border-width: 2px;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .contact-info a {
        color: inherit;
    }
    
    .contact-info .text-truncate {
        max-width: 200px;
    }
    
    .btn {
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.75rem;
    }
    
    .alert {
        border-radius: 8px;
    }
}

/* Ajustes para os filtros em mobile */
@media (max-width: 767.98px) {
    .container-fluid {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    
    .card {
        margin-bottom: 1rem;
    }
    
    .input-group {
        margin-bottom: 1rem;
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border-right: 0;
    }
    
    .form-control {
        border-left: 0;
    }
    
    .form-control:focus {
        border-color: #dee2e6;
        box-shadow: none;
    }
    
    .input-group .form-control:focus + .input-group-text {
        border-color: #dee2e6;
    }
    
    .btn-group {
        width: 100%;
        gap: 0.5rem;
    }
    
    .btn-group .btn {
        flex: 1;
    }
}

/* Ajustes para o modal em mobile */
@media (max-width: 767.98px) {
    .modal-content {
        border-radius: 16px;
        margin: 0.5rem;
    }
    
    .modal-header {
        padding: 1rem;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .modal-footer {
        padding: 1rem;
        border-bottom-left-radius: 16px;
        border-bottom-right-radius: 16px;
    }
}

/* Ajustes para a paginação */
.pagination {
    justify-content: center;
    margin-top: 1rem;
}

.page-link {
    border-radius: 8px;
    margin: 0 0.1rem;
    border: 1px solid #dee2e6;
    color: #6f42c1;
    padding: 0.5rem 0.75rem;
    transition: all 0.2s ease;
}

.page-link:hover {
    color: #ffffff;
    background-color: #6f42c1;
    border-color: #6f42c1;
    transform: translateY(-1px);
}

.page-item.active .page-link {
    background-color: #6f42c1;
    border-color: #6f42c1;
    color: white;
    font-weight: 600;
}

.page-item.disabled .page-link {
    color: #6c757d;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

/* Ajustes para a paginação em mobile */
@media (max-width: 767.98px) {
    .pagination {
        justify-content: center;
        margin-top: 1rem;
        flex-wrap: wrap;
    }
    
    .page-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
        margin: 0.1rem;
    }
    
    .page-link i {
        font-size: 0.8rem;
    }
}

// Modal de Apagar Todas as Inscrições - Versão Simplificada
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado, inicializando modal...');
    
    // Função para carregar estatísticas
    function carregarEstatisticas() {
        const totalInscricoes = document.getElementById('totalInscricoes');
        const inscricoesFiltradas = document.getElementById('inscricoesFiltradas');
        
        if (!totalInscricoes || !inscricoesFiltradas) {
            console.error('Elementos de estatísticas não encontrados');
            return;
        }

        // Total de inscrições
        fetch('/dashboard/inscricoes/count', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            totalInscricoes.textContent = data.total_geral || 0;
            console.log('Total carregado:', data.total_geral);
        })
        .catch(error => {
            console.error('Erro ao carregar total:', error);
            totalInscricoes.textContent = 'Erro';
        });

        // Inscrições filtradas
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        
        fetch('/dashboard/inscricoes/count?' + params.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            inscricoesFiltradas.textContent = data.total || 0;
            console.log('Filtradas carregadas:', data.total);
        })
        .catch(error => {
            console.error('Erro ao carregar filtradas:', error);
            inscricoesFiltradas.textContent = 'Erro';
        });
    }

    // Função para apagar todas as inscrições
    function apagarTodasInscricoes() {
        const btnConfirmarApagar = document.getElementById('btnConfirmarApagar');
        const loadingText = '<i class="fas fa-spinner fa-spin me-2"></i>Apagando...';
        const originalText = btnConfirmarApagar.innerHTML;
        
        btnConfirmarApagar.innerHTML = loadingText;
        btnConfirmarApagar.disabled = true;

        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        
        fetch('/dashboard/inscricoes/apagar-todas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                filtros: Object.fromEntries(params)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const mensagem = data.scope === 'admin_full_access' 
                    ? `Sucesso! ${data.total_apagadas} inscrições foram removidas.`
                    : `Sucesso! ${data.total_apagadas} inscrições do seu domínio foram removidas.`;
                
                toastr.success(mensagem);
                
                const modal = document.getElementById('modalApagarTodas');
                const modalInstance = bootstrap.Modal.getInstance(modal);
                modalInstance.hide();
                
                if (data.total_apagadas > 0) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                toastr.error(data.message || 'Erro ao apagar inscrições');
                btnConfirmarApagar.innerHTML = originalText;
                btnConfirmarApagar.disabled = false;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            toastr.error('Erro ao apagar inscrições');
            btnConfirmarApagar.innerHTML = originalText;
            btnConfirmarApagar.disabled = false;
        });
    }

    // Configurar o modal quando ele for aberto
    document.addEventListener('show.bs.modal', function(event) {
        if (event.target.id === 'modalApagarTodas') {
            console.log('Modal de apagar todas aberto');
            
            setTimeout(() => {
                const confirmacaoTexto = document.getElementById('confirmacaoTexto');
                const btnConfirmarApagar = document.getElementById('btnConfirmarApagar');
                
                if (confirmacaoTexto && btnConfirmarApagar) {
                    console.log('Elementos encontrados, configurando...');
                    
                    // Limpar campo e desabilitar botão
                    confirmacaoTexto.value = '';
                    btnConfirmarApagar.disabled = true;
                    btnConfirmarApagar.classList.remove('btn-danger');
                    btnConfirmarApagar.classList.add('btn-secondary');
                    
                    // Event listener para o campo de texto
                    confirmacaoTexto.addEventListener('input', function() {
                        const textoDigitado = this.value.trim().toUpperCase();
                        console.log('Texto digitado:', textoDigitado);
                        
                        const deveHabilitar = textoDigitado === 'CONFIRMAR';
                        btnConfirmarApagar.disabled = !deveHabilitar;
                        
                        if (deveHabilitar) {
                            btnConfirmarApagar.classList.remove('btn-secondary');
                            btnConfirmarApagar.classList.add('btn-danger');
                            console.log('Botão habilitado!');
                        } else {
                            btnConfirmarApagar.classList.remove('btn-danger');
                            btnConfirmarApagar.classList.add('btn-secondary');
                            console.log('Botão desabilitado');
                        }
                    });
                    
                    // Event listener para o botão
                    btnConfirmarApagar.addEventListener('click', function() {
                        if (confirmacaoTexto.value.trim().toUpperCase() === 'CONFIRMAR') {
                            apagarTodasInscricoes();
                        }
                    });
                    
                    // Carregar estatísticas
                    carregarEstatisticas();
                    
                } else {
                    console.error('Elementos não encontrados no modal');
                }
            }, 200);
        }
    });
});

// Função de teste para o botão (definida globalmente)
window.testarBotao = function() {
    const btnConfirmarApagar = document.getElementById('btnConfirmarApagar');
    const confirmacaoTexto = document.getElementById('confirmacaoTexto');
    
    if (btnConfirmarApagar && confirmacaoTexto) {
        console.log('=== TESTE DO BOTÃO ===');
        console.log('Botão encontrado:', btnConfirmarApagar);
        console.log('Campo de texto encontrado:', confirmacaoTexto);
        console.log('Valor do campo:', confirmacaoTexto.value);
        console.log('Botão disabled:', btnConfirmarApagar.disabled);
        console.log('Classes do botão:', btnConfirmarApagar.className);
        
        // Simular digitação de CONFIRMAR
        confirmacaoTexto.value = 'CONFIRMAR';
        confirmacaoTexto.dispatchEvent(new Event('input'));
        
        console.log('Após simulação:');
        console.log('Botão disabled:', btnConfirmarApagar.disabled);
        console.log('Classes do botão:', btnConfirmarApagar.className);
    } else {
        console.error('Elementos não encontrados para teste');
    }
};

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    inicializarModalApagarTodas();
});

// Também inicializar quando o modal for aberto (para garantir que os elementos existam)
document.addEventListener('show.bs.modal', function(event) {
    if (event.target.id === 'modalApagarTodas') {
        // Aguardar um pouco para garantir que o modal esteja completamente renderizado
        setTimeout(() => {
            inicializarModalApagarTodas();
        }, 100);
    }
});
</style>
<?php $__env->stopPush(); ?>



<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Douglas\Documents\Projetos\ensinocerto\resources\views/admin/inscricoes/index.blade.php ENDPATH**/ ?>