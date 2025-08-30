<?php $__env->startSection('title', 'Matrículas'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mt-4 mb-0">
                <i class="fas fa-graduation-cap me-1"></i>
                Matrículas
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Matrículas</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.matriculas.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Nova Matrícula
            </a>
            <a href="<?php echo e(route('admin.matriculas.importar')); ?>" class="btn btn-success">
                <i class="fas fa-upload me-1"></i>
                Importar
            </a>
            <a href="<?php echo e(route('admin.matriculas.exportar')); ?>" class="btn btn-info">
                <i class="fas fa-download me-1"></i>
                Exportar
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <button class="btn btn-link text-decoration-none p-0 w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosMatriculas" aria-expanded="false">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtros de Busca
                    </h5>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </button>
        </div>
        <div class="collapse" id="filtrosMatriculas">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('admin.matriculas.index')); ?>" id="filterForm">
                    <div class="row g-3">
                        <!-- Status -->
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos os status</option>
                                <option value="pre_matricula" <?php echo e(request('status') == 'pre_matricula' ? 'selected' : ''); ?>>
                                    🟡 Pré-Matrícula
                                </option>
                                <option value="matricula_confirmada" <?php echo e(request('status') == 'matricula_confirmada' ? 'selected' : ''); ?>>
                                    🟢 Matrícula Confirmada
                                </option>
                                <option value="cancelada" <?php echo e(request('status') == 'cancelada' ? 'selected' : ''); ?>>
                                    🔴 Cancelada
                                </option>
                                <option value="trancada" <?php echo e(request('status') == 'trancada' ? 'selected' : ''); ?>>
                                    ⚫ Trancada
                                </option>
                                <option value="concluida" <?php echo e(request('status') == 'concluida' ? 'selected' : ''); ?>>
                                    ⭐ Concluída
                                </option>
                            </select>
                        </div>

                        <!-- Status de Pagamento -->
                        <div class="col-md-3">
                            <label for="status_pagamento" class="form-label">Status de Pagamento</label>
                            <select class="form-select" id="status_pagamento" name="status_pagamento">
                                <option value="">Todos os pagamentos</option>
                                <option value="avista" <?php echo e(request('status_pagamento') == 'avista' ? 'selected' : ''); ?>>
                                    💰 À Vista
                                </option>
                                <option value="pago" <?php echo e(request('status_pagamento') == 'pago' ? 'selected' : ''); ?>>
                                    🟢 Pago
                                </option>
                                <option value="pendente" <?php echo e(request('status_pagamento') == 'pendente' ? 'selected' : ''); ?>>
                                    🟡 Pendente
                                </option>
                                <option value="vencido" <?php echo e(request('status_pagamento') == 'vencido' ? 'selected' : ''); ?>>
                                    🔴 Vencido
                                </option>
                                <option value="sem_pagamento" <?php echo e(request('status_pagamento') == 'sem_pagamento' ? 'selected' : ''); ?>>
                                    ⚫ Sem pagamento
                                </option>
                            </select>
                        </div>

                        <!-- Tipo de Pagamento -->
                        <div class="col-md-3">
                            <label for="tipo_pagamento" class="form-label">Tipo de Pagamento</label>
                            <select class="form-select" id="tipo_pagamento" name="tipo_pagamento">
                                <option value="">Todos os tipos</option>
                                <option value="avista" <?php echo e(request('tipo_pagamento') == 'avista' ? 'selected' : ''); ?>>
                                    💰 À Vista
                                </option>
                                <option value="parcelado" <?php echo e(request('tipo_pagamento') == 'parcelado' ? 'selected' : ''); ?>>
                                    📅 Parcelado
                                </option>
                            </select>
                        </div>

                        <!-- Forma de Pagamento -->
                        <div class="col-md-3">
                            <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                            <select class="form-select" id="forma_pagamento" name="forma_pagamento">
                                <option value="">Todas as formas</option>
                                <option value="cartao_credito" <?php echo e(request('forma_pagamento') == 'cartao_credito' ? 'selected' : ''); ?>>
                                    💳 Cartão de Crédito
                                </option>
                                <option value="pix" <?php echo e(request('forma_pagamento') == 'pix' ? 'selected' : ''); ?>>
                                    📱 PIX
                                </option>
                                <option value="boleto" <?php echo e(request('forma_pagamento') == 'boleto' ? 'selected' : ''); ?>>
                                    🧾 Boleto
                                </option>
                            </select>
                        </div>

                        <!-- Modalidade -->
                        <div class="col-md-3">
                            <label for="modalidade" class="form-label">Modalidade</label>
                            <select class="form-select" id="modalidade" name="modalidade">
                                <option value="">Todas as modalidades</option>
                                <?php $__currentLoopData = $formSettings['available_modalities'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e(request('modalidade') == $value ? 'selected' : ''); ?>>
                                        <?php echo e($label); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Escola Parceira -->
                        <div class="col-md-3">
                            <label for="escola_parceira" class="form-label">Escola Parceira</label>
                            <select class="form-select" id="escola_parceira" name="escola_parceira">
                                <option value="">Todas</option>
                                <option value="1" <?php echo e(request('escola_parceira') === '1' ? 'selected' : ''); ?>>
                                    🏫 Sim (Vem de Parceiro)
                                </option>
                                <option value="0" <?php echo e(request('escola_parceira') === '0' ? 'selected' : ''); ?>>
                                    🎓 Não (Matrícula Direta)
                                </option>
                            </select>
                        </div>

                        <!-- Data início -->
                        <div class="col-md-3">
                            <label for="data_inicio" class="form-label">Data Início</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_inicio" 
                                   name="data_inicio" 
                                   value="<?php echo e(request('data_inicio')); ?>">
                        </div>
                        
                        <!-- Data fim -->
                        <div class="col-md-3">
                            <label for="data_fim" class="form-label">Data Fim</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="data_fim" 
                                   name="data_fim" 
                                   value="<?php echo e(request('data_fim')); ?>">
                        </div>

                        <!-- Busca -->
                        <div class="col-md-3">
                            <label for="busca" class="form-label">Buscar</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="busca" 
                                   name="busca" 
                                   value="<?php echo e(request('busca')); ?>"
                                   placeholder="Nome, CPF, email ou matrícula">
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>
                            Filtrar
                        </button>
                        <a href="<?php echo e(route('admin.matriculas.index')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Limpar
                        </a>
                    </div>
                </form>
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
                        Lista de Matrículas
                    </h5>
                </div>
                <div class="col-auto">
                    <span class="text-muted">
                        Total: <?php echo e($matriculas->total()); ?> matrículas
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Matrícula</th>
                            <th>Nome</th>
                            <th>Modalidade</th>
                            <th>Pagamentos</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $matriculas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $matricula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="<?php echo e($loop->even ? 'table-light' : ''); ?>">
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo e($matricula->numero_matricula); ?>

                                    </span>
                                </td>
                                <td class="fw-medium"><?php echo e($matricula->nome_completo); ?></td>
                                <td><?php echo e($matricula->modalidade_formatada); ?></td>
                                <td>
                                    <?php
                                        $totalParcelas = $matricula->numero_parcelas ?? 0;
                                        $parcelasPagas = $matricula->payments->where('status', 'paid')->count();
                                        $parcelasPendentes = $matricula->payments->where('status', 'pending')->count();
                                        $parcelasVencidas = $matricula->payments->where('status', 'pending')
                                            ->where('data_vencimento', '<', now())->count();
                                    ?>
                                    
                                    <div class="d-flex flex-column">
                                        <!-- Linha Superior: Forma de Pagamento (Etiquetas Pretas) -->
                                        <div class="mb-1">
                                            <?php
                                                $gateway = $matricula->payment_gateway ?? 'mercado_pago';
                                                // Verificar se há pagamento de matrícula (entrada)
                                                $pagamentoMatricula = $matricula->payments->where('numero_parcela', 0)->first();
                                                $matriculaHasEntrada = $matricula->valor_matricula > 0;
                                            ?>
                                            
                                            <?php if($gateway === 'mercado_pago'): ?>
                                                
                                                <?php if($matricula->forma_pagamento === 'cartao_credito'): ?>
                                                    <span class="badge bg-dark payment-type-badge">À Vista/Cartão</span>
                                                
                                                
                                                <?php elseif($matricula->forma_pagamento === 'pix'): ?>
                                                    <span class="badge bg-dark payment-type-badge">À Vista/PIX</span>
                                                
                                                
                                                <?php elseif($matriculaHasEntrada && $pagamentoMatricula && $pagamentoMatricula->status !== 'paid'): ?>
                                                    <span class="badge bg-dark payment-type-badge">Matrícula</span>
                                                
                                                
                                                <?php elseif($matricula->tipo_boleto === 'avista'): ?>
                                                    <span class="badge bg-dark payment-type-badge">À Vista</span>
                                                <?php elseif($totalParcelas == 1): ?>
                                                    <span class="badge bg-dark payment-type-badge">À Vista</span>
                                                <?php elseif($totalParcelas > 1): ?>
                                                    <span class="badge bg-dark payment-type-badge"><?php echo e($parcelasPagas); ?>/<?php echo e($totalParcelas); ?> Parcelas</span>
                                                <?php elseif($matricula->payments->count() == 1): ?>
                                                    <span class="badge bg-dark payment-type-badge">À Vista</span>
                                                <?php elseif($matricula->payments->count() > 1): ?>
                                                    <span class="badge bg-dark payment-type-badge"><?php echo e($parcelasPagas); ?>/<?php echo e($matricula->payments->count()); ?> Pagamentos</span>
                                                <?php else: ?>
                                                    <span class="badge bg-dark payment-type-badge">Sem Pagamento</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                
                                                <?php if($matriculaHasEntrada && $pagamentoMatricula && $pagamentoMatricula->status !== 'paid'): ?>
                                                    <span class="badge bg-dark payment-type-badge">Matrícula</span>
                                                <?php elseif($totalParcelas == 1 || $matricula->payments->count() <= 1): ?>
                                                    <span class="badge bg-dark payment-type-badge">À Vista/Manual</span>
                                                <?php elseif($totalParcelas > 1): ?>
                                                    <span class="badge bg-dark payment-type-badge"><?php echo e($parcelasPagas); ?>/<?php echo e($totalParcelas); ?> Parcelas</span>
                                                <?php elseif($matricula->payments->count() > 1): ?>
                                                    <span class="badge bg-dark payment-type-badge"><?php echo e($parcelasPagas); ?>/<?php echo e($matricula->payments->count()); ?> Pagamentos</span>
                                                <?php else: ?>
                                                    <span class="badge bg-dark payment-type-badge">Sem Pagamento</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Linha Inferior: Status (Cores de Status) -->
                                        <div>
                                            <?php
                                                // Verificar se há pagamento de matrícula (entrada)
                                                $pagamentoMatricula = $matricula->payments->where('numero_parcela', 0)->first();
                                                $mensalidades = $matricula->payments->where('numero_parcela', '>', 0);
                                                $hasBoletosPendentes = $matricula->payments->where('status', 'pending')
                                                    ->where('forma_pagamento', 'boleto')->count() > 0;
                                            ?>
                                            
                                            <?php if($gateway === 'mercado_pago'): ?>
                                                
                                                
                                                
                                                <?php if($matricula->forma_pagamento === 'cartao_credito' || $matricula->forma_pagamento === 'pix'): ?>
                                                    <?php if($matricula->payments->where('status', 'paid')->count() > 0): ?>
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    <?php endif; ?>
                                                
                                                
                                                <?php elseif($pagamentoMatricula): ?>
                                                    <?php if($pagamentoMatricula->status === 'paid'): ?>
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    <?php elseif($pagamentoMatricula->forma_pagamento === 'boleto'): ?>
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info payment-status-badge">Pendente</span>
                                                    <?php endif; ?>
                                                
                                                
                                                <?php elseif($matricula->tipo_boleto === 'avista' || $totalParcelas == 1): ?>
                                                    <?php if($matricula->payments->where('status', 'paid')->count() > 0): ?>
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    <?php elseif($hasBoletosPendentes): ?>
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info payment-status-badge">Pendente</span>
                                                    <?php endif; ?>
                                                
                                                
                                                <?php elseif($totalParcelas > 1): ?>
                                                    <?php if($parcelasPagas == $totalParcelas): ?>
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    <?php elseif($parcelasVencidas > 0): ?>
                                                        <span class="badge bg-danger payment-status-badge"><?php echo e($parcelasVencidas); ?> Vencida<?php echo e($parcelasVencidas > 1 ? 's' : ''); ?></span>
                                                    <?php elseif($parcelasPendentes > 0 && $hasBoletosPendentes): ?>
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    <?php elseif($parcelasPendentes > 0): ?>
                                                        <span class="badge bg-info payment-status-badge"><?php echo e($parcelasPendentes); ?> Pendente<?php echo e($parcelasPendentes > 1 ? 's' : ''); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary payment-status-badge">Aguardando</span>
                                                    <?php endif; ?>
                                                
                                                
                                                <?php elseif($matricula->payments->count() > 0): ?>
                                                    <?php
                                                        $parcelasPagas = $matricula->payments->where('status', 'paid')->count();
                                                        $totalPagamentos = $matricula->payments->count();
                                                    ?>
                                                    <?php if($parcelasPagas == $totalPagamentos): ?>
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    <?php elseif($hasBoletosPendentes): ?>
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info payment-status-badge"><?php echo e($totalPagamentos - $parcelasPagas); ?> Pendente<?php echo e(($totalPagamentos - $parcelasPagas) > 1 ? 's' : ''); ?></span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary payment-status-badge">Sem pagamentos</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                
                                                <?php if($pagamentoMatricula): ?>
                                                    <?php if($pagamentoMatricula->status === 'paid'): ?>
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    <?php endif; ?>
                                                <?php elseif($matricula->payments->count() > 0): ?>
                                                    <?php
                                                        $parcelasPagas = $matricula->payments->where('status', 'paid')->count();
                                                        $totalPagamentos = $matricula->payments->count();
                                                    ?>
                                                    <?php if($parcelasPagas == $totalPagamentos): ?>
                                                        <span class="badge bg-success payment-status-badge">Pago</span>
                                                    <?php elseif($hasBoletosPendentes): ?>
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    <?php elseif($parcelasPagas > 0): ?>
                                                        <span class="badge bg-info payment-status-badge"><?php echo e($totalPagamentos - $parcelasPagas); ?> Pendente<?php echo e(($totalPagamentos - $parcelasPagas) > 1 ? 's' : ''); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning payment-status-badge">Pendente</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary payment-status-badge">Sem pagamentos</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo e($matricula->status_formatado); ?></td>
                                <td><?php echo e($matricula->created_at->format('d/m/Y')); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('admin.matriculas.show', $matricula)); ?>" 
                                           class="btn btn-outline-secondary" 
                                           title="Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('admin.matriculas.edit', $matricula)); ?>" 
                                           class="btn btn-outline-secondary" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Excluir"
                                                onclick="confirmarExclusao('<?php echo e($matricula->id); ?>', '<?php echo e($matricula->nome_completo); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                    Nenhuma matrícula encontrada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($matriculas->hasPages()): ?>
                <div class="px-4 py-3 border-top">
                    <?php echo e($matriculas->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade centered-modal" id="modalExclusao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a matrícula do aluno <strong id="nomeAluno"></strong>?</p>
                <p class="text-danger mb-0">Esta ação não poderá ser desfeita!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formExclusao" method="POST" style="display: inline;">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit ao mudar os selects
    const selects = document.querySelectorAll('#filterForm select');
    selects.forEach(select => {
        select.addEventListener('change', () => {
            document.getElementById('filterForm').submit();
        });
    });

    // Função para confirmar exclusão
    window.confirmarExclusao = function(id, nome) {
        document.getElementById('nomeAluno').textContent = nome;
        document.getElementById('formExclusao').action = `/dashboard/matriculas/${id}`;
        const modal = new bootstrap.Modal(document.getElementById('modalExclusao'));
        modal.show();
    };
});
</script>

<style>
/* Estilos para linhas alternadas da tabela */
.table tbody tr:nth-child(even) {
    background-color: #f8f9fa !important;
}

.table tbody tr:nth-child(odd) {
    background-color: #ffffff !important;
}

.table tbody tr:hover {
    background-color: #e9ecef !important;
    transition: background-color 0.15s ease-in-out;
}

/* Melhorar contraste das bordas */
.table tbody tr {
    border-bottom: 1px solid #dee2e6;
}

.table tbody tr:last-child {
    border-bottom: none;
}

/* Estilos para as etiquetas de pagamento */
.payment-status-badge {
    min-width: 120px;
    text-align: center;
    display: inline-block;
}

.payment-type-badge {
    min-width: 120px;
    text-align: center;
    display: inline-block;
}

/* Responsividade para as etiquetas de pagamento */
@media (max-width: 768px) {
    .payment-status-badge,
    .payment-type-badge {
        min-width: 100px;
        font-size: 0.75rem;
    }
}

@media (max-width: 576px) {
    .payment-status-badge,
    .payment-type-badge {
        min-width: 80px;
        font-size: 0.7rem;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/douglas/Downloads/ec-complete-backup-20250728_105142/ec-complete-backup-20250813_144041/resources/views/admin/matriculas/index.blade.php ENDPATH**/ ?>