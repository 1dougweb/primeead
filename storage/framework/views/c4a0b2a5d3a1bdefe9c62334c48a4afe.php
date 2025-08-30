<?php $__env->startSection('title', 'Configurações do WhatsApp'); ?>

<?php $__env->startSection('page-title', 'WhatsApp'); ?>

<?php $__env->startSection('page-actions'); ?>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-primary" id="btn-refresh-status">
            <i class="fas fa-sync-alt me-2"></i>
            Atualizar Status
        </button>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <!-- Configurações da API -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fab fa-whatsapp me-2 text-success"></i>
                        Configurações da Evolution API
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.settings.whatsapp.update')); ?>" method="POST" id="whatsapp-config-form">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="mb-3">
                            <label for="base_url" class="form-label">
                                <i class="fas fa-server me-1"></i>
                                URL da API
                            </label>
                            <input type="url" 
                                   class="form-control <?php $__errorArgs = ['base_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="base_url" 
                                   name="base_url"
                                   value="<?php echo e(old('base_url', $settings['base_url'] ?? '')); ?>"
                                   placeholder="http://localhost:8080"
                                   required>
                            <div class="form-text">
                                Ex: http://localhost:8080 ou https://api.evolution.com
                            </div>
                            <?php $__errorArgs = ['base_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="api_key" class="form-label">
                                <i class="fas fa-key me-1"></i>
                                Chave da API
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control <?php $__errorArgs = ['api_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="api_key" 
                                       name="api_key"
                                       value="<?php echo e(old('api_key', $settings['api_key'] ?? '')); ?>"
                                       placeholder="Sua chave da API"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="toggle-api-key">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Chave de autenticação da Evolution API
                            </div>
                            <?php $__errorArgs = ['api_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="instance" class="form-label">
                                <i class="fas fa-mobile-alt me-1"></i>
                                Nome da Instância
                            </label>
                            <input type="text" 
                                   class="form-control <?php $__errorArgs = ['instance'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="instance" 
                                   name="instance"
                                   value="<?php echo e(old('instance', $settings['instance'] ?? 'default')); ?>"
                                   placeholder="default"
                                   pattern="[a-zA-Z0-9_-]+"
                                   required>
                            <div class="form-text">
                                Apenas letras, números, hífen e underscore
                            </div>
                            <?php $__errorArgs = ['instance'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Salvar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Status da Conexão e QR Code -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        Conexão WhatsApp
                    </h5>
                    <div id="connection-status-badge">
                        <?php if($connectionStatus['connected']): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Conectado
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger">
                                <i class="fas fa-times-circle me-1"></i>
                                Desconectado
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status da Conexão -->
                    <div class="alert alert-info mb-3" id="status-message">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo e($connectionStatus['message']); ?>

                    </div>

                    <!-- Container do QR Code -->
                    <div class="text-center mb-3" id="qrcode-container">
                        <div class="qr-placeholder">
                            <i class="fas fa-qrcode display-4 text-muted"></i>
                            <p class="text-muted mt-2">
                                Configure a API e clique em "Gerar QR Code" para conectar
                            </p>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" id="btn-create-instance" 
                                <?php if(empty($settings['base_url']) || empty($settings['api_key'])): ?> disabled <?php endif; ?>>
                            <i class="fas fa-plus me-2"></i>
                            Criar Nova Instância
                        </button>
                        
                        <button type="button" class="btn btn-primary" id="btn-generate-qr"
                                <?php if(empty($settings['base_url']) || empty($settings['api_key'])): ?> disabled <?php endif; ?>>
                            <i class="fas fa-qrcode me-2"></i>
                            Gerar QR Code
                        </button>

                        <button type="button" class="btn btn-outline-info" id="btn-refresh-status">
                            <i class="fas fa-sync me-2"></i>
                            Atualizar Status
                        </button>

                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-warning" id="btn-disconnect"
                                    <?php if(!$connectionStatus['connected']): ?> disabled <?php endif; ?>>
                                <i class="fas fa-unlink me-2"></i>
                                Desconectar
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="btn-delete-instance">
                                <i class="fas fa-trash me-2"></i>
                                Deletar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teste de Mensagem -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-paper-plane me-2"></i>
                        Teste de Mensagem
                    </h5>
                </div>
                <div class="card-body">
                    <form id="test-message-form">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="test_phone" class="form-label">Número de Telefone</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="test_phone" 
                                       name="phone"
                                       placeholder="(11) 99999-9999"
                                       required>
                                <div class="form-text">
                                    Formato: (DD) 9XXXX-XXXX
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="test_message" class="form-label">Mensagem</label>
                                <textarea class="form-control" 
                                          id="test_message" 
                                          name="message"
                                          rows="3" 
                                          placeholder="Digite sua mensagem de teste..."
                                          maxlength="1000"
                                          required>Olá! Esta é uma mensagem de teste do sistema EJA Supletivo.</textarea>
                                <div class="form-text">
                                    Máximo 1000 caracteres
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100"
                                        <?php if(!$connectionStatus['connected']): ?> disabled <?php endif; ?>>
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Enviar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos DOM
    const qrcodeContainer = document.getElementById('qrcode-container');
    const statusMessage = document.getElementById('status-message');
    const connectionBadge = document.getElementById('connection-status-badge');
    const testMessageForm = document.getElementById('test-message-form');
    
    // Botões
    const btnCreateInstance = document.getElementById('btn-create-instance');
    const btnGenerateQr = document.getElementById('btn-generate-qr');
    const btnRefreshStatus = document.getElementById('btn-refresh-status');
    const btnDisconnect = document.getElementById('btn-disconnect');
    const btnDeleteInstance = document.getElementById('btn-delete-instance');
    const btnToggleApiKey = document.getElementById('toggle-api-key');

    // URLs das rotas
    const routes = {
        createInstance: '<?php echo e(route("admin.settings.whatsapp.create-instance")); ?>',
        qrCode: '<?php echo e(route("admin.settings.whatsapp.qr-code")); ?>',
        status: '<?php echo e(route("admin.settings.whatsapp.status")); ?>',
        disconnect: '<?php echo e(route("admin.settings.whatsapp.disconnect")); ?>',
        deleteInstance: '<?php echo e(route("admin.settings.whatsapp.delete-instance")); ?>',
        testMessage: '<?php echo e(route("admin.settings.whatsapp.test-message")); ?>'
    };

    // Salvar textos originais dos botões
    [btnCreateInstance, btnGenerateQr, btnRefreshStatus, btnDisconnect, btnDeleteInstance].forEach(btn => {
        if (btn && !btn.dataset.originalText) {
            btn.dataset.originalText = btn.innerHTML;
        }
    });

    // Toggle visibilidade da API Key
    btnToggleApiKey.addEventListener('click', function() {
        const apiKeyInput = document.getElementById('api_key');
        const icon = this.querySelector('i');
        
        if (apiKeyInput.type === 'password') {
            apiKeyInput.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            apiKeyInput.type = 'password';
            icon.className = 'fas fa-eye';
        }
    });

    // Criar instância
    btnCreateInstance.addEventListener('click', async function() {
        await executeAction(this, async () => {
            const response = await fetch(routes.createInstance, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });
            return await response.json();
        }, 'Instância criada com sucesso!');
    });

    // Gerar QR Code
    btnGenerateQr.addEventListener('click', async function() {
        await executeAction(this, async () => {
            qrcodeContainer.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2 text-muted">Gerando QR Code...</p>
                </div>
            `;
            
            const response = await fetch(routes.qrCode);
            const data = await response.json();
            
            console.log('Resposta QR Code:', data); // Debug
            
            if (data.success) {
                if (data.data.connected) {
                    qrcodeContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Dispositivo já conectado!
                        </div>
                    `;
                } else if (data.data.qrcode) {
                    // Verificar se qrcode é um objeto ou string
                    let qrcodeUrl = data.data.qrcode;
                    if (typeof qrcodeUrl === 'object' && qrcodeUrl.base64) {
                        qrcodeUrl = qrcodeUrl.base64;
                    }
                    
                    if (qrcodeUrl && qrcodeUrl !== '[object Object]') {
                        qrcodeContainer.innerHTML = `
                            <div class="qr-code-display">
                                <img src="${qrcodeUrl}" class="img-fluid border rounded" alt="QR Code" style="max-width: 300px;">
                                <p class="mt-3 text-muted">
                                    <i class="fab fa-whatsapp me-1"></i>
                                    Escaneie com o WhatsApp do seu celular
                                </p>
                                <small class="text-warning">
                                    <i class="fas fa-clock me-1"></i>
                                    QR Code expira em 60 segundos
                                </small>
                            </div>
                        `;
                        
                        // Auto-refresh do status após mostrar QR code
                        setTimeout(refreshStatus, 5000);
                    } else {
                        throw new Error('QR Code inválido recebido');
                    }
                } else {
                    qrcodeContainer.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${data.message || 'QR Code não disponível. Tente novamente.'}
                        </div>
                    `;
                }
            } else {
                // Se a requisição não teve sucesso (mas não é necessariamente erro)
                qrcodeContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        ${data.message || data.error || 'QR Code não disponível no momento'}
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-1"></i>
                                Tentar Novamente
                            </button>
                        </div>
                    </div>
                `;
                
                // Não fazer throw Error para success: false - é uma resposta válida
                return data;
            }
            
            return data;
        });
    });

    // Atualizar status
    btnRefreshStatus.addEventListener('click', refreshStatus);

    // Desconectar
    btnDisconnect.addEventListener('click', async function() {
        if (!confirm('Tem certeza que deseja desconectar o WhatsApp?')) return;
        
        await executeAction(this, async () => {
            const response = await fetch(routes.disconnect, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            return await response.json();
        }, 'WhatsApp desconectado com sucesso!');
    });

    // Deletar instância
    btnDeleteInstance.addEventListener('click', async function() {
        if (!confirm('Tem certeza que deseja deletar a instância? Esta ação não pode ser desfeita.')) return;
        
        await executeAction(this, async () => {
            const response = await fetch(routes.deleteInstance, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            return await response.json();
        }, 'Instância deletada com sucesso!');
    });

    // Teste de mensagem
    testMessageForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const formData = new FormData(this);
        
        await executeAction(submitBtn, async () => {
            const response = await fetch(routes.testMessage, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            });
            return await response.json();
        }, 'Mensagem de teste enviada com sucesso!');
    });

    // Função auxiliar para executar ações
    async function executeAction(button, action, successMessage = 'Operação realizada com sucesso!') {
        try {
            button.disabled = true;
            // Salvar texto original
            if (!button.dataset.originalText) {
                button.dataset.originalText = button.innerHTML;
            }
            const originalText = button.dataset.originalText;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';
            
            const result = await action();
            
            if (result.success) {
                toastr.success(result.message || successMessage);
                // Aguardar um pouco antes de atualizar status para evitar conflitos
                setTimeout(refreshStatus, 500);
            } else {
                throw new Error(result.error || result.message || 'Erro desconhecido');
            }
        } catch (error) {
            console.error('Erro:', error);
            toastr.error('Erro: ' + error.message);
        } finally {
            button.disabled = false;
            // Restaurar texto original imediatamente
            button.innerHTML = button.dataset.originalText;
        }
    }

    // Atualizar status da conexão
    async function refreshStatus() {
        try {
            // Primeiro buscar o status
            const statusResponse = await fetch(routes.status);
            const statusData = await statusResponse.json();
            
            if (statusData.success) {
                const status = statusData.data || {};
                
                // Atualizar badge
                connectionBadge.innerHTML = status.connected 
                    ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Conectado</span>'
                    : '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Desconectado</span>';
                
                // Atualizar mensagem
                statusMessage.className = `alert ${status.connected ? 'alert-success' : 'alert-info'} mb-3`;
                statusMessage.innerHTML = `<i class="fas fa-info-circle me-2"></i>${status.message || 'Status desconhecido'}`;
                
                // Buscar se instância existe (não crítico se falhar)
                let instanceExists = true; // assumir que existe por padrão
                try {
                    const instanceResponse = await fetch('<?php echo e(route("admin.settings.whatsapp.check-instance")); ?>');
                    const instanceData = await instanceResponse.json();
                    if (instanceData.success && instanceData.data) {
                        instanceExists = instanceData.data.exists || false;
                    }
                } catch (e) {
                    console.warn('Erro ao verificar instância:', e);
                }
                
                // Atualizar visibilidade dos botões
                updateButtonVisibility(instanceExists, status.connected);
                
                // Atualizar botão de envio de teste
                const testSubmitBtn = document.querySelector('#test-message-form button[type="submit"]');
                if (testSubmitBtn) {
                    testSubmitBtn.disabled = !status.connected;
                }
                
                // Limpar QR code se conectado
                if (status.connected && qrcodeContainer.querySelector('.qr-code-display')) {
                    qrcodeContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Dispositivo conectado com sucesso!
                        </div>
                    `;
                }
            } else {
                console.warn('Status request failed:', statusData);
                // Em caso de erro, usar valores padrão
                updateButtonVisibility(true, false);
            }
        } catch (error) {
            console.error('Erro ao atualizar status:', error);
            // Em caso de erro, usar valores padrão
            updateButtonVisibility(true, false);
        }
    }

    // Função para atualizar visibilidade dos botões
    function updateButtonVisibility(instanceExists, connected) {
        if (btnCreateInstance) {
            btnCreateInstance.style.display = instanceExists ? 'none' : 'inline-block';
        }
        if (btnGenerateQr) {
            btnGenerateQr.style.display = instanceExists ? 'inline-block' : 'none';
        }
        if (btnRefreshStatus) {
            btnRefreshStatus.style.display = instanceExists ? 'inline-block' : 'none';
        }
        if (btnDisconnect) {
            btnDisconnect.disabled = !connected;
        }
        if (btnDeleteInstance) {
            btnDeleteInstance.style.display = instanceExists ? 'inline-block' : 'none';
        }
    }

    // Verificar estado inicial
    refreshStatus();

    // Auto-refresh do status a cada 30 segundos
    setInterval(refreshStatus, 30000);
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/douglas/Downloads/ec-complete-backup-20250728_105142/ec-complete-backup-20250813_144041/resources/views/admin/settings/whatsapp.blade.php ENDPATH**/ ?>