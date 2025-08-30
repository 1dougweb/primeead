<?php $__env->startSection('title', 'Usuários'); ?>

<?php $__env->startSection('page-title', 'Gerenciar Usuários'); ?>

<?php $__env->startSection('page-actions'); ?>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('admin.usuarios.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Novo Usuário
        </a>
        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar ao Dashboard
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-users me-2"></i>
                Lista de Usuários (<?php echo e($usuarios->total()); ?>)
            </h5>
        </div>
        
        <div class="card-body p-0">
            <?php if($usuarios->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Papéis</th>
                                <th>Status</th>
                                <th>Último Acesso</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="<?php echo e(!$usuario->ativo ? 'table-secondary' : ''); ?>">
                                    <td>
                                        <span class="badge bg-light text-dark"><?php echo e($usuario->id); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo e($usuario->name); ?></strong>
                                        <?php if($usuario->id == session('admin_id')): ?>
                                            <span class="badge bg-info text-white ms-1">Você</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo e($usuario->email); ?>" class="text-decoration-none">
                                            <?php echo e($usuario->email); ?>

                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo e($usuario->tipo_usuario === 'admin' ? 'bg-warning text-dark' : 'bg-primary'); ?>">
                                            <?php echo e($usuario->tipo_usuario_formatado); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($usuario->roles->isEmpty()): ?>
                                            <span class="text-muted small">Nenhum papel atribuído</span>
                                        <?php else: ?>
                                            <div>
                                                <?php $__currentLoopData = $usuario->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <span class="badge <?php echo e($role->is_active ? 'bg-info' : 'bg-secondary'); ?> mb-1">
                                                        <?php echo e($role->name); ?>

                                                    </span>
                                                    <?php if(!$loop->last): ?> <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-toggle" 
                                                   type="checkbox" 
                                                   data-id="<?php echo e($usuario->id); ?>"
                                                   <?php echo e($usuario->ativo ? 'checked' : ''); ?>

                                                   <?php echo e($usuario->id == session('admin_id') ? 'disabled' : ''); ?>>
                                            <label class="form-check-label">
                                                <span class="badge <?php echo e($usuario->ativo ? 'bg-success' : 'bg-danger'); ?>">
                                                    <?php echo e($usuario->ativo ? 'Ativo' : 'Inativo'); ?>

                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($usuario->ultimo_acesso): ?>
                                            <small>
                                                <?php echo e($usuario->ultimo_acesso->format('d/m/Y')); ?><br>
                                                <span class="text-muted"><?php echo e($usuario->ultimo_acesso->format('H:i:s')); ?></span>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Nunca acessou</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="<?php echo e(route('admin.usuarios.show', $usuario->id)); ?>" 
                                               class="btn btn-sm btn-outline-info"
                                               title="Ver Detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <a href="<?php echo e(route('admin.usuarios.edit', $usuario->id)); ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if($usuario->id != session('admin_id')): ?>
                                                <?php if($userMenuPermissions['admin.usuarios.impersonate'] ?? false): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success" 
                                                            onclick="confirmarImpersonation(<?php echo e($usuario->id); ?>, '<?php echo e(addslashes($usuario->name)); ?>')"
                                                            title="Fazer Login como <?php echo e($usuario->name); ?>">
                                                        <i class="fas fa-user-secret"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmarExclusao(<?php echo e($usuario->id); ?>, '<?php echo e(addslashes($usuario->name)); ?>')"
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
                
                <!-- Paginação -->
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando <?php echo e($usuarios->firstItem()); ?> a <?php echo e($usuarios->lastItem()); ?> 
                            de <?php echo e($usuarios->total()); ?> registros
                        </div>
                        
                        <?php echo e($usuarios->links()); ?>

                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum usuário encontrado</h5>
                    <p class="text-muted">Clique no botão "Novo Usuário" para adicionar o primeiro usuário.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalExclusao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o usuário <strong id="nomeUsuario"></strong>?</p>
                    <p class="text-danger small">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formExclusao" method="POST" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function confirmarExclusao(id, nome) {
    document.getElementById('nomeUsuario').textContent = nome;
    document.getElementById('formExclusao').action = `/dashboard/usuarios/${id}`;
    new bootstrap.Modal(document.getElementById('modalExclusao')).show();
}

// Função para confirmar impersonation
function confirmarImpersonation(id, nome) {
    if (confirm(`Tem certeza que deseja fazer login como "${nome}"?\n\nVocê será redirecionado para o dashboard como este usuário. Para voltar ao seu usuário original, clique no botão "Sair da Impersonation" no topo da página.`)) {
        // Criar formulário dinâmico para impersonation
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/dashboard/usuarios/${id}/impersonate`;
        
        // Adicionar token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Adicionar ao body e submeter
        document.body.appendChild(form);
        form.submit();
    }
}

// Toggle status do usuário
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const id = this.dataset.id;
            const isChecked = this.checked;
            
            fetch(`/dashboard/usuarios/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar badge
                    const badge = this.nextElementSibling.querySelector('.badge');
                    if (data.novo_status) {
                        badge.className = 'badge bg-success';
                        badge.textContent = 'Ativo';
                        this.closest('tr').classList.remove('table-secondary');
                    } else {
                        badge.className = 'badge bg-danger';
                        badge.textContent = 'Inativo';
                        this.closest('tr').classList.add('table-secondary');
                    }
                } else {
                    alert(data.message);
                    // Reverter toggle
                    this.checked = !isChecked;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao alterar status do usuário');
                // Reverter toggle
                this.checked = !isChecked;
            });
        });
    });
});
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/douglas/Downloads/ec-complete-backup-20250728_105142/ec-complete-backup-20250813_144041/resources/views/admin/usuarios/index.blade.php ENDPATH**/ ?>