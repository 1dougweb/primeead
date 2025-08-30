<?php if($inscricoes->count() > 0): ?>
    <!-- Versão Desktop -->
    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Contato</th>
                        <th>Curso</th>
                        <th>Modalidade</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $inscricoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inscricao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="<?php echo e($inscricao->isLocked() ? 'table-warning' : ''); ?>">
                            <td>
                                <span class="badge bg-light text-dark"><?php echo e($inscricao->id); ?></span>
                                <?php if($inscricao->isLocked()): ?>
                                    <br><small class="text-warning"><i class="fas fa-lock me-1"></i>Travado</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-medium"><?php echo e($inscricao->nome); ?></div>
                                <?php if($inscricao->isLocked()): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo e($inscricao->lockedBy->name ?? 'N/A'); ?>

                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <a href="mailto:<?php echo e($inscricao->email); ?>" class="text-decoration-none mb-1">
                                        <i class="fas fa-envelope me-1 text-muted"></i>
                                        <?php echo e($inscricao->email); ?>

                                    </a>
                                    <div class="d-flex align-items-center">
                                        <a href="tel:<?php echo e($inscricao->telefone); ?>" class="text-decoration-none me-2">
                                            <i class="fas fa-phone me-1 text-muted"></i>
                                            <?php echo e($inscricao->telefone); ?>

                                        </a>
                                        <a href="https://wa.me/55<?php echo e(preg_replace('/[^0-9]/', '', $inscricao->telefone)); ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-success">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo e($inscricao->curso ?? 'N/A'); ?>

                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo e($inscricao->modalidade ?? 'N/A'); ?>

                                </span>
                            </td>
                            <td>
                                <?php switch($inscricao->etiqueta):
                                    case ('pendente'): ?>
                                        <span class="badge bg-warning">🟡 Pendente</span>
                                        <?php break; ?>
                                    <?php case ('contatado'): ?>
                                        <span class="badge bg-info">🔵 Contatado</span>
                                        <?php break; ?>
                                    <?php case ('interessado'): ?>
                                        <span class="badge bg-success">🟢 Interessado</span>
                                        <?php break; ?>
                                    <?php case ('nao_interessado'): ?>
                                        <span class="badge bg-danger">🔴 Não Interessado</span>
                                        <?php break; ?>
                                    <?php case ('matriculado'): ?>
                                        <span class="badge bg-primary">⭐ Matriculado</span>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <span class="badge bg-secondary"><?php echo e($inscricao->etiqueta ?? 'N/A'); ?></span>
                                <?php endswitch; ?>
                            </td>
                            <td>
                                <div><?php echo e($inscricao->created_at->format('d/m/Y')); ?></div>
                                <small class="text-muted"><?php echo e($inscricao->created_at->format('H:i')); ?></small>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <?php if($inscricao->isLocked() && $inscricao->locked_by == auth()->id()): ?>
                                        <button type="button" 
                                                class="btn btn-warning btn-sm" 
                                                onclick="destravar(<?php echo e($inscricao->id); ?>)"
                                                title="Liberar Lead">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" 
                                                class="btn btn-primary btn-sm" 
                                                onclick="pegarLead(<?php echo e($inscricao->id); ?>)"
                                                <?php echo e($inscricao->isLocked() ? 'disabled' : ''); ?>

                                                title="<?php echo e($inscricao->isLocked() ? 'Lead Indisponível' : 'Pegar Lead'); ?>">
                                            <i class="fas fa-hand-paper"></i>
                                        </button>
                                    <?php endif; ?>

                                    <button type="button" 
                                            class="btn btn-info btn-sm" 
                                            onclick="verDetalhes(<?php echo e($inscricao->id); ?>)"
                                            title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <a href="<?php echo e(route('admin.inscricoes.editar', $inscricao->id)); ?>" 
                                       class="btn btn-secondary btn-sm"
                                       <?php echo e($inscricao->isLockedByOther(auth()->id()) ? 'disabled' : ''); ?>

                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <?php if(auth()->user()->isAdmin()): ?>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm" 
                                                onclick="confirmarExclusao(<?php echo e($inscricao->id); ?>, '<?php echo e($inscricao->nome); ?>')"
                                                <?php echo e($inscricao->isLockedByOther(auth()->id()) ? 'disabled' : ''); ?>

                                                title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Versão Mobile -->
    <div class="d-md-none">
        <div class="inscricoes-grid">
            <?php $__currentLoopData = $inscricoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inscricao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="card mb-3 <?php echo e($inscricao->isLocked() ? 'border-warning' : ''); ?>">
                    <div class="card-body p-3">
                        <!-- Cabeçalho do Card -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-0 fw-bold"><?php echo e($inscricao->nome); ?></h6>
                                <small class="text-muted">ID #<?php echo e($inscricao->id); ?></small>
                            </div>
                            <?php if($inscricao->isLocked()): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-lock me-1"></i>Travado
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Status e Curso/Modalidade -->
                        <div class="mb-3">
                            <div class="d-flex gap-2 mb-2">
                                <?php switch($inscricao->etiqueta):
                                    case ('pendente'): ?>
                                        <span class="badge bg-warning">🟡 Pendente</span>
                                        <?php break; ?>
                                    <?php case ('contatado'): ?>
                                        <span class="badge bg-info">🔵 Contatado</span>
                                        <?php break; ?>
                                    <?php case ('interessado'): ?>
                                        <span class="badge bg-success">🟢 Interessado</span>
                                        <?php break; ?>
                                    <?php case ('nao_interessado'): ?>
                                        <span class="badge bg-danger">🔴 Não Interessado</span>
                                        <?php break; ?>
                                    <?php case ('matriculado'): ?>
                                        <span class="badge bg-primary">⭐ Matriculado</span>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <span class="badge bg-secondary"><?php echo e($inscricao->etiqueta ?? 'N/A'); ?></span>
                                <?php endswitch; ?>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-primary"><?php echo e($inscricao->curso ?? 'N/A'); ?></span>
                                <span class="badge bg-info"><?php echo e($inscricao->modalidade ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <!-- Status do Lead -->
                        <?php if($inscricao->isLocked()): ?>
                            <div class="alert alert-warning py-1 px-2 mb-2">
                                <small>
                                    <i class="fas fa-user me-1"></i>
                                    Em atendimento por: <?php echo e($inscricao->lockedBy->name ?? 'N/A'); ?>

                                </small>
                            </div>
                        <?php endif; ?>

                        <!-- Informações de Contato -->
                        <div class="contact-info mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="flex-grow-1">
                                    <a href="mailto:<?php echo e($inscricao->email); ?>" class="text-decoration-none d-block text-truncate">
                                        <i class="fas fa-envelope me-2 text-muted"></i>
                                        <?php echo e($inscricao->email); ?>

                                    </a>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <a href="tel:<?php echo e($inscricao->telefone); ?>" class="text-decoration-none">
                                        <i class="fas fa-phone me-2 text-muted"></i>
                                        <?php echo e($inscricao->telefone); ?>

                                    </a>
                                </div>
                                <a href="https://wa.me/55<?php echo e(preg_replace('/[^0-9]/', '', $inscricao->telefone)); ?>" 
                                   target="_blank" 
                                   class="btn btn-success btn-sm ms-2">
                                    <i class="fab fa-whatsapp"></i>
                                    WhatsApp
                                </a>
                            </div>
                        </div>

                        <!-- Data -->
                        <div class="text-muted mb-3">
                            <i class="fas fa-calendar me-2"></i>
                            <?php echo e($inscricao->created_at->format('d/m/Y H:i')); ?>

                        </div>

                        <!-- Botões de Ação -->
                        <div class="d-flex gap-2">
                            <?php if($inscricao->isLocked() && $inscricao->locked_by == auth()->id()): ?>
                                <button type="button" 
                                        class="btn btn-warning flex-grow-1" 
                                        onclick="destravar(<?php echo e($inscricao->id); ?>)">
                                    <i class="fas fa-unlock me-2"></i>
                                    Liberar Lead
                                </button>
                            <?php else: ?>
                                <button type="button" 
                                        class="btn btn-primary flex-grow-1" 
                                        onclick="pegarLead(<?php echo e($inscricao->id); ?>)"
                                        <?php echo e($inscricao->isLocked() ? 'disabled' : ''); ?>>
                                    <i class="fas fa-hand-paper me-2"></i>
                                    <?php echo e($inscricao->isLocked() ? 'Lead Indisponível' : 'Pegar Lead'); ?>

                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" 
                                    class="btn btn-info flex-grow-1" 
                                    onclick="verDetalhes(<?php echo e($inscricao->id); ?>)">
                                <i class="fas fa-eye me-2"></i>
                                Detalhes
                            </button>

                            <a href="<?php echo e(route('admin.inscricoes.editar', $inscricao->id)); ?>" 
                               class="btn btn-secondary flex-grow-1"
                               <?php echo e($inscricao->isLockedByOther(auth()->id()) ? 'disabled' : ''); ?>>
                                <i class="fas fa-edit me-2"></i>
                                Editar
                            </a>

                            <?php if(auth()->user()->isAdmin()): ?>
                                <button type="button" 
                                        class="btn btn-danger flex-grow-1" 
                                        onclick="confirmarExclusao(<?php echo e($inscricao->id); ?>, '<?php echo e($inscricao->nome); ?>')"
                                        <?php echo e($inscricao->isLockedByOther(auth()->id()) ? 'disabled' : ''); ?>>
                                    <i class="fas fa-trash me-2"></i>
                                    Excluir
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    
    <!-- Paginação -->
    <div class="card-footer bg-white border-0 pt-3">
        <?php echo e($inscricoes->links()); ?>

    </div>
<?php else: ?>
    <div class="text-center py-5">
        <img src="<?php echo e(asset('images/no-data.svg')); ?>" alt="Sem dados" class="img-fluid mb-3" style="max-width: 200px;">
        <h4 class="text-muted">Nenhuma inscrição encontrada</h4>
        <p class="text-muted">
            <?php if(request()->hasAny(['busca', 'curso', 'data_inicio', 'data_fim'])): ?>
                Tente remover alguns filtros para ver mais resultados.
            <?php else: ?>
                Não há inscrições cadastradas no sistema.
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?> <?php /**PATH /home/douglas/Downloads/ec-complete-backup-20250728_105142/ec-complete-backup-20250813_144041/resources/views/admin/inscricoes/_lista.blade.php ENDPATH**/ ?>