@extends('layouts.admin')

@section('title', 'Configurações do Sistema')

@section('page-title', 'Configurações do Sistema')

@section('styles')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
@endsection

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    window.selectMigration = function(filename) {
        document.getElementById('migration_file').value = filename;
    }

    window.executeMigration = function() {
        const migrationFile = document.getElementById('migration_file').value;
        
        if (!migrationFile) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Por favor, digite o nome do arquivo migration'
            });
            return;
        }

        Swal.fire({
            title: 'Confirmar Execução',
            text: `Deseja executar a migration "${migrationFile}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, executar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Executando Migration',
                    text: 'Por favor, aguarde...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar requisição
                fetch('{{ route("admin.settings.execute-migration") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        migration_file: migrationFile
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Mostrar resultado
                        document.getElementById('migration-output').textContent = data.details;
                        document.getElementById('migration-result').style.display = 'block';
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: data.message
                        });
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro completo:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: error.message || 'Erro ao executar migration'
                    });
                });
            }
        });
    }

    // Teste de conexão com ChatGPT na página principal
    document.getElementById('testAiConnection')?.addEventListener('click', function() {
        const apiKey = document.getElementById('ai_api_key').value;
        const model = document.getElementById('ai_model').value;

        if (!apiKey) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Por favor, insira a API Key'
            });
            return;
        }

        // Mostrar loading
        Swal.fire({
            title: 'Testando Conexão',
            text: 'Por favor, aguarde...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Enviar requisição
        fetch('{{ route("admin.settings.ai.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                api_key: apiKey,
                model: model
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    text: 'Conexão estabelecida com sucesso!'
                });
            } else {
                throw new Error(data.message || 'Erro ao testar conexão');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: error.message || 'Erro ao testar conexão'
            });
        });
    });

    // Teste de conexão com Mercado Pago
    document.getElementById('testMercadoPagoConnection')?.addEventListener('click', function() {
        const sandbox = document.getElementById('mercadopago_sandbox').checked;
        
        // Determinar qual token usar baseado no modo sandbox
        let accessToken;
        if (sandbox) {
            accessToken = document.getElementById('mercadopago_sandbox_access_token').value || 
                         document.getElementById('mercadopago_access_token').value;
        } else {
            accessToken = document.getElementById('mercadopago_access_token').value;
        }

        if (!accessToken) {
            const modeText = sandbox ? 'sandbox' : 'produção';
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: `Por favor, insira o Access Token do Mercado Pago para o modo ${modeText}`
            });
            return;
        }

        // Mostrar loading
        Swal.fire({
            title: 'Testando Conexão',
            text: 'Verificando credenciais do Mercado Pago...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Enviar requisição
        fetch('{{ route("admin.payments.test-connection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                access_token: accessToken,
                sandbox: sandbox
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    text: 'Conexão com Mercado Pago estabelecida com sucesso!',
                    html: `
                        <div class="text-start">
                            <p><strong>Conexão estabelecida com sucesso!</strong></p>
                            <p><small>Ambiente: ${sandbox ? 'Sandbox (Teste)' : 'Produção'}</small></p>
                            ${data.data ? `<p><small>User ID: ${data.data.user_id || 'N/A'}</small></p>` : ''}
                        </div>
                    `
                });
            } else {
                throw new Error(data.message || 'Erro ao testar conexão');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro na Conexão',
                text: error.message || 'Erro ao testar conexão com Mercado Pago',
                footer: 'Verifique suas credenciais e tente novamente'
            });
        });
    });

    // Repeater functionality for courses and modalities
    let courseCounter = 0;
    let modalityCounter = 0;

    // Add Course Button
    document.getElementById('addCourseBtn')?.addEventListener('click', function() {
        courseCounter++;
        const tbody = document.getElementById('coursesTableBody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm" 
                       name="available_courses_keys[]" placeholder="chave-curso-${courseCounter}" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" 
                       name="available_courses_values[]" placeholder="Nome do Curso" required>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-row">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
    });

    // Add Modality Button
    document.getElementById('addModalityBtn')?.addEventListener('click', function() {
        modalityCounter++;
        const tbody = document.getElementById('modalitiesTableBody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm" 
                       name="available_modalities_keys[]" placeholder="chave-modalidade-${modalityCounter}" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" 
                       name="available_modalities_values[]" placeholder="Nome da Modalidade" required>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-row">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
    });

    // Remove Row functionality (event delegation)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
            const row = e.target.closest('tr');
            if (row) {
                row.remove();
            }
        }
    });
});

// Signature pad functionality for school signature
let schoolSignaturePad = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeSchoolSignaturePad();
    loadExistingSignature();
    
    // Reinitialize canvas when contracts tab is shown
    const contractsTab = document.getElementById('contracts-tab');
    if (contractsTab) {
        contractsTab.addEventListener('shown.bs.tab', function() {
            setTimeout(() => {
                initializeSchoolSignaturePad();
                loadExistingSignature();
            }, 100);
        });
    }
});

function initializeSchoolSignaturePad() {
    const canvas = document.getElementById('school-signature-pad');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Set canvas size - use fixed dimensions since getBoundingClientRect might be 0 if hidden
    canvas.width = 600;
    canvas.height = 200;
    
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;
    
    // Mouse events
    canvas.addEventListener('mousedown', (e) => {
        isDrawing = true;
        [lastX, lastY] = [e.offsetX, e.offsetY];
    });
    
    canvas.addEventListener('mousemove', (e) => {
        if (!isDrawing) return;
        
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
        
        [lastX, lastY] = [e.offsetX, e.offsetY];
    });
    
    canvas.addEventListener('mouseup', () => isDrawing = false);
    canvas.addEventListener('mouseout', () => isDrawing = false);
    
    // Touch events
    canvas.addEventListener('touchstart', (e) => {
        e.preventDefault();
        const touch = e.touches[0];
        const rect = canvas.getBoundingClientRect();
        isDrawing = true;
        lastX = touch.clientX - rect.left;
        lastY = touch.clientY - rect.top;
    });
    
    canvas.addEventListener('touchmove', (e) => {
        e.preventDefault();
        if (!isDrawing) return;
        
        const touch = e.touches[0];
        const rect = canvas.getBoundingClientRect();
        const x = touch.clientX - rect.left;
        const y = touch.clientY - rect.top;
        
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(x, y);
        ctx.stroke();
        
        [lastX, lastY] = [x, y];
    });
    
    canvas.addEventListener('touchend', () => isDrawing = false);
}

function loadExistingSignature() {
    const signatureData = document.getElementById('school_signature_data').value;
    if (signatureData) {
        const canvas = document.getElementById('school-signature-pad');
        if (!canvas) return;
        
        // Ensure canvas has proper dimensions
        if (canvas.width === 0 || canvas.height === 0) {
            canvas.width = 600;
            canvas.height = 200;
        }
        
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.onload = function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        };
        
        img.src = signatureData;
    }
}

function clearSchoolSignature() {
    const canvas = document.getElementById('school-signature-pad');
    if (canvas) {
        // Ensure canvas has proper dimensions
        if (canvas.width === 0 || canvas.height === 0) {
            canvas.width = 600;
            canvas.height = 200;
        }
        
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('school_signature_data').value = '';
        console.log('School signature cleared');
    }
}

function saveSchoolSignature() {
    const canvas = document.getElementById('school-signature-pad');
    if (!canvas) {
        alert('Erro: Campo de assinatura não encontrado.');
        return;
    }
    
    // Ensure canvas has proper dimensions
    if (canvas.width === 0 || canvas.height === 0) {
        canvas.width = 600;
        canvas.height = 200;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Check if signature is empty
    console.log('Canvas dimensions:', canvas.width, 'x', canvas.height);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const isEmpty = imageData.data.every(pixel => pixel === 0);
    
    if (isEmpty) {
        alert('Por favor, desenhe uma assinatura antes de salvar.');
        return;
    }
    
    // Get signature data
    const signatureData = canvas.toDataURL('image/png');
    document.getElementById('school_signature_data').value = signatureData;
    
    // Show success message
    Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: 'Assinatura salva temporariamente. Clique em "Salvar Configurações" para confirmar.',
        timer: 3000,
        showConfirmButton: false
    });
    
    console.log('School signature saved to form');
}
</script>
@endpush

@section('content')
<div class="container-fluid">
    
    <form id="settingsForm" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        
        <!-- Navegação por Abas com Scroll Horizontal -->
        <div class="settings-tabs-container mb-4">
            <div class="settings-tabs-scroll" id="settingsTabsScroll">
                <div class="settings-tabs-wrapper" id="settingsTabs" role="tablist">
                    <button class="settings-tab-btn active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                        <i class="fas fa-cog me-2"></i>Geral
                    </button>
            
                    <button class="settings-tab-btn" id="leads-tab" data-bs-toggle="tab" data-bs-target="#leads" type="button" role="tab">
                        <i class="fas fa-users me-2"></i>Leads
                    </button>
            
                    <button class="settings-tab-btn" id="migrations-tab" data-bs-toggle="tab" data-bs-target="#migrations" type="button" role="tab">
                        <i class="fas fa-database me-2"></i>Migrations
                    </button>

                    <button class="settings-tab-btn" id="tracking-tab" data-bs-toggle="tab" data-bs-target="#tracking" type="button" role="tab">
                        <i class="fas fa-chart-line me-2"></i>Tracking
                    </button>
            
                    <button class="settings-tab-btn" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                        <i class="fas fa-envelope me-2"></i>Email
                    </button>
            
                    <button class="settings-tab-btn" id="thank-you-tab" data-bs-toggle="tab" data-bs-target="#thank-you" type="button" role="tab">
                        <i class="fas fa-check-circle me-2"></i>Obrigado
                    </button>
            
                    <button class="settings-tab-btn" id="whatsapp-tab" data-bs-toggle="tab" data-bs-target="#whatsapp" type="button" role="tab">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </button>
            
                    <button class="settings-tab-btn" id="countdown-tab" data-bs-toggle="tab" data-bs-target="#countdown" type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>Countdown
                    </button>
            
                    <button class="settings-tab-btn" id="landing-page-tab" data-bs-toggle="tab" data-bs-target="#landing-page" type="button" role="tab">
                        <i class="fas fa-globe me-2"></i>Landing Page
                    </button>

                    <button class="settings-tab-btn" id="chatgpt-tab" data-bs-toggle="tab" data-bs-target="#chatgpt" type="button" role="tab">
                        <i class="fas fa-robot me-2"></i>ChatGPT
                    </button>
            
                    <button class="settings-tab-btn" id="contracts-tab" data-bs-toggle="tab" data-bs-target="#contracts" type="button" role="tab">
                        <i class="fas fa-file-contract me-2"></i>Contratos
                    </button>
            
                    <button class="settings-tab-btn" id="mercadopago-tab" data-bs-toggle="tab" data-bs-target="#mercadopago" type="button" role="tab">
                        <i class="fas fa-credit-card me-2"></i>Mercado Pago
                    </button>
                </div>
            </div>
            
            <!-- Indicadores de Scroll -->
            <div class="settings-tabs-indicators">
                <button class="settings-tabs-indicator left" id="scrollLeft" type="button" aria-label="Rolar para esquerda">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="settings-tabs-indicator right" id="scrollRight" type="button" aria-label="Rolar para direita">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Conteúdo das Abas -->
        <div class="tab-content" id="settingsTabContent">
            <!-- Conteúdo da aba Geral -->
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <div class="card-body">
                    <h5 class="mb-4">Configurações Gerais</h5>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Configure as informações básicas e aparência do sistema aqui.
                    </div>
                    
                    <!-- Configuração de Logos -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-image me-2"></i> Configuração de Logos</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Logo do Sidebar -->
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Logo do Sidebar</h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <img id="sidebar-logo-preview" 
                                                     src="{{ $appearanceSettings['sidebar_logo_path'] ?? '/assets/images/logotipo-dark.svg' }}" 
                                                     alt="Logo Sidebar" 
                                                     class="img-fluid" 
                                                     style="max-height: 80px; max-width: 200px;">
                                            </div>
                                            <div class="mb-3">
                                                <input type="file" 
                                                       id="sidebar-logo-input" 
                                                       accept="image/jpeg,image/png,image/svg+xml" 
                                                       style="display: none;">
                                                <button type="button" 
                                                        class="btn btn-primary btn-sm me-2" 
                                                        onclick="document.getElementById('sidebar-logo-input').click()">
                                                    <i class="fas fa-upload me-1"></i> Alterar Logo
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-secondary btn-sm" 
                                                        onclick="resetLogo('sidebar')">
                                                    <i class="fas fa-undo me-1"></i> Resetar
                                                </button>
                                            </div>
                                            <small class="text-muted">
                                                Formatos aceitos: JPG, PNG, SVG<br>
                                                Tamanho máximo: 2MB
                                            </small>
                                            <div id="sidebar-logo-debug" class="mt-2 small text-muted"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Logo da Página de Login -->
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Logo da Página de Login</h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <img id="login-logo-preview" 
                                                     src="{{ $appearanceSettings['login_logo_path'] ?? '/assets/images/logotipo-dark.svg' }}" 
                                                     alt="Logo Login" 
                                                     class="img-fluid" 
                                                     style="max-height: 80px; max-width: 200px;">
                                            </div>
                                            <div class="mb-3">
                                                <input type="file" 
                                                       id="login-logo-input" 
                                                       accept="image/*" 
                                                       style="display: none;">
                                                <button type="button" 
                                                        class="btn btn-primary btn-sm me-2" 
                                                        onclick="document.getElementById('login-logo-input').click()">
                                                    <i class="fas fa-upload me-1"></i> Alterar Logo
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-secondary btn-sm" 
                                                        onclick="resetLogo('login')">
                                                    <i class="fas fa-undo me-1"></i> Resetar
                                                </button>
                                            </div>
                                            <small class="text-muted">
                                                Formatos aceitos: JPG, PNG, SVG<br>
                                                Tamanho máximo: 2MB
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo da aba Leads -->
            <div class="tab-pane fade" id="leads" role="tabpanel" aria-labelledby="leads-tab">
                <div class="card-body">
                    <h5 class="mb-4">Configurações de Leads</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="lead_cooldown_minutes" class="form-label">Tempo de Cooldown (minutos)</label>
                                <input type="number" class="form-control" id="lead_cooldown_minutes" name="lead_cooldown_minutes" 
                                       value="{{ old('lead_cooldown_minutes', $leadSettings['cooldown_minutes'] ?? 2) }}" min="0">
                                <div class="form-text">Tempo que um usuário deve aguardar após pegar um lead</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="auto_unlock_hours" class="form-label">Destravamento Automático (horas)</label>
                                <input type="number" class="form-control" id="auto_unlock_hours" name="auto_unlock_hours" 
                                       value="{{ old('auto_unlock_hours', $leadSettings['auto_unlock_hours'] ?? 24) }}" min="0">
                                <div class="form-text">Tempo para destravar leads automaticamente</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="max_leads_per_user" class="form-label">Máximo de Leads por Usuário</label>
                                <input type="number" class="form-control" id="max_leads_per_user" name="max_leads_per_user" 
                                       value="{{ old('max_leads_per_user', $leadSettings['max_leads_per_user'] ?? 10) }}" min="1">
                                <div class="form-text">Número máximo de leads que um usuário pode ter travados</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Nova aba de Migrations -->
            <div class="tab-pane fade" id="migrations" role="tabpanel" aria-labelledby="migrations-tab">
                <div class="card-body">
                    <h5 class="mb-4">Executar Migrations Específicas</h5>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atenção!</strong> Execute migrations com cuidado pois podem afetar a estrutura do banco de dados.
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-code me-2"></i>Executar Migration</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="migration_file" class="form-label">Nome do Arquivo Migration</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="migration_file" name="migration_file" 
                                           placeholder="Ex: 2024_03_21_000001_add_google_drive_folder_to_matriculas.php">
                                    <button type="button" class="btn btn-primary" onclick="executeMigration()">
                                        <i class="fas fa-play me-2"></i>Executar
                                    </button>
                                </div>
                                <div class="form-text">Digite o nome completo do arquivo migration que deseja executar</div>
                            </div>

                            <!-- Área para exibir o resultado -->
                            <div id="migration-result" class="mt-4" style="display: none;">
                                <h6 class="mb-3">Resultado da Execução</h6>
                                <div class="alert alert-info">
                                    <pre id="migration-output" class="mb-0" style="white-space: pre-wrap;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de migrations disponíveis -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Migrations Disponíveis</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome do Arquivo</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(File::files(database_path('migrations')) as $file)
                                        <tr>
                                            <td>{{ $file->getFilename() }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        onclick="selectMigration('{{ $file->getFilename() }}')">
                                                    <i class="fas fa-check me-1"></i>Selecionar
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo da aba Tracking -->
            <div class="tab-pane fade" id="tracking" role="tabpanel" aria-labelledby="tracking-tab">
                <div class="card-body">
                    <h5 class="mb-4">Configurações de Tracking</h5>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Configure aqui as ferramentas de rastreamento para monitorar o desempenho do seu site e campanhas.
                    </div>
                    
                    <!-- Google Tag Manager -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fab fa-google me-2"></i> Google Tag Manager</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_google_analytics" name="enable_google_analytics" value="1"
                                           {{ old('enable_google_analytics', $settings['enable_google_analytics'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_google_analytics">Ativar</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="google_tag_manager_id" class="form-label">Google Tag Manager ID</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="google_tag_manager_id" name="google_tag_manager_id" 
                                                   value="{{ old('google_tag_manager_id', $settings['google_tag_manager_id'] ?? '') }}"
                                                   placeholder="GTM-XXXXXXX ou ID numérico">
                                            <button class="btn btn-outline-primary" type="button" id="validateGTM">Validar</button>
                                        </div>
                                        <div class="form-text">Formatos aceitos: GTM-XXXXXXX ou ID numérico</div>
                                        <div id="gtm-validation-result" class="mt-2"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body bg-light">
                                            <h6 class="card-title">O que é o GTM?</h6>
                                            <p class="card-text small">O Google Tag Manager é uma ferramenta que permite gerenciar tags de marketing e rastreamento sem modificar o código do site.</p>
                                            <a href="https://tagmanager.google.com/" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-external-link-alt me-1"></i> Acessar GTM
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Eventos a serem rastreados</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="track_pageviews" name="track_pageviews" value="1" checked disabled>
                                            <label class="form-check-label" for="track_pageviews">Visualizações de página</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="track_forms" name="track_forms" value="1" checked disabled>
                                            <label class="form-check-label" for="track_forms">Envios de formulário</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="track_clicks" name="track_clicks" value="1" checked disabled>
                                            <label class="form-check-label" for="track_clicks">Cliques em botões</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Facebook Pixel -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fab fa-facebook me-2"></i> Facebook Pixel</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_facebook_pixel" name="enable_facebook_pixel" value="1"
                                           {{ old('enable_facebook_pixel', $settings['enable_facebook_pixel'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_facebook_pixel">Ativar</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="facebook_pixel_id" class="form-label">Facebook Pixel ID</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="facebook_pixel_id" name="facebook_pixel_id" 
                                                   value="{{ old('facebook_pixel_id', $settings['facebook_pixel_id'] ?? '') }}"
                                                   placeholder="ID numérico (ex: 123456789012345)">
                                            <button class="btn btn-outline-primary" type="button" id="validatePixel">Validar</button>
                                        </div>
                                        <div class="form-text">Formato: ID numérico de 8-16 dígitos</div>
                                        <div id="pixel-validation-result" class="mt-2"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body bg-light">
                                            <h6 class="card-title">O que é o Facebook Pixel?</h6>
                                            <p class="card-text small">O Facebook Pixel permite rastrear conversões, otimizar anúncios e criar públicos para remarketing.</p>
                                            <a href="https://www.facebook.com/business/help/952192354843755" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-external-link-alt me-1"></i> Saiba mais
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Eventos padrão do Facebook</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="fb_track_pageview" name="fb_track_pageview" value="1" checked disabled>
                                            <label class="form-check-label" for="fb_track_pageview">PageView</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="fb_track_lead" name="fb_track_lead" value="1" checked disabled>
                                            <label class="form-check-label" for="fb_track_lead">Lead</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="fb_track_complete_registration" name="fb_track_complete_registration" value="1" checked disabled>
                                            <label class="form-check-label" for="fb_track_complete_registration">CompleteRegistration</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Google Tag Manager - Landing Page -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fab fa-google me-2"></i> Google Tag Manager - Landing Page</h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="landing_gtm_enabled" name="landing_gtm_enabled" value="1"
                                           {{ old('landing_gtm_enabled', $landingSettings['gtm_enabled'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="landing_gtm_enabled">Ativar</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Configure o Google Tag Manager especificamente para a landing page. Estas configurações são independentes das configurações gerais de tracking.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="landing_gtm_id" class="form-label">ID do GTM para Landing Page</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="landing_gtm_id" name="landing_gtm_id" 
                                                   value="{{ old('landing_gtm_id', $landingSettings['gtm_id'] ?? '') }}"
                                                   placeholder="GTM-XXXXXXX">
                                            <button class="btn btn-outline-primary" type="button" id="validateLandingGTM">Validar</button>
                                        </div>
                                        <div class="form-text">ID específico do GTM para a landing page (ex: GTM-NPXJKW38)</div>
                                        <div id="landing-gtm-validation-result" class="mt-2"></div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="landing_gtm_events" class="form-label">Eventos Personalizados</label>
                                        <textarea class="form-control" id="landing_gtm_events" name="landing_gtm_events" rows="4" 
                                                  placeholder="Configurações de eventos personalizados para GTM da landing page">{{ old('landing_gtm_events', $landingSettings['gtm_events'] ?? '') }}</textarea>
                                        <div class="form-text">Configurações adicionais de eventos para o GTM da landing page (opcional)</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body bg-light">
                                            <h6 class="card-title">GTM Landing Page</h6>
                                            <p class="card-text small">Configure um container GTM específico para a landing page, independente das configurações gerais de tracking.</p>
                                            <div class="alert alert-warning small">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <strong>Importante:</strong> Esta configuração sobrescreve o GTM geral apenas na landing page.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Teste de Tracking -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-vial me-2"></i> Testar Configurações</h6>
                        </div>
                        <div class="card-body">
                            <p>Verifique se suas configurações de rastreamento estão funcionando corretamente.</p>
                            <button type="button" class="btn btn-primary" id="testTracking">
                                <i class="fas fa-check-circle me-2"></i> Verificar Configurações
                            </button>
                            <div id="tracking-test-result" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo da aba Configurações de Email -->
            <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                <div class="card-body">
                    <h5 class="mb-4">Configurações de Email (SMTP)</h5>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Configure aqui as credenciais SMTP para envio de emails automáticos do sistema.
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <!-- Configurações Gerais de Email -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i> Configurações Gerais</h6>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="enable_email" name="enable_email" value="1"
                                                   {{ old('enable_email', $settings['enable_email'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enable_email">Ativar Envio</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label for="mail_from_address" class="form-label">Email de Origem</label>
                                        <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" 
                                               value="{{ old('mail_from_address', $settings['mail_from_address'] ?? 'contato@ensinocerto.com.br') }}"
                                               placeholder="nome@dominio.com.br">
                                        <div class="form-text">Email que aparecerá como remetente</div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="mail_from_name" class="form-label">Nome de Exibição</label>
                                        <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" 
                                               value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'EJA Admin') }}"
                                               placeholder="Nome da Empresa">
                                        <div class="form-text">Nome que aparecerá como remetente</div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="mail_reply_to" class="form-label">Email de Resposta (Reply-To)</label>
                                        <input type="email" class="form-control" id="mail_reply_to" name="mail_reply_to" 
                                               value="{{ old('mail_reply_to', $settings['mail_reply_to'] ?? '') }}"
                                               placeholder="resposta@dominio.com.br">
                                        <div class="form-text">Deixe em branco para usar o mesmo email de origem</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Configurações de Notificação -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-bell me-2"></i> Configurações de Notificação</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label for="admin_notification_email" class="form-label">Email para Notificações Admin</label>
                                        <input type="email" class="form-control" id="admin_notification_email" name="admin_notification_email" 
                                               value="{{ old('admin_notification_email', $settings['admin_notification_email'] ?? '') }}"
                                               placeholder="admin@dominio.com.br">
                                        <div class="form-text">Email para receber notificações administrativas</div>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="notify_new_lead" name="notify_new_lead" value="1"
                                               {{ old('notify_new_lead', $settings['notify_new_lead'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_new_lead">
                                            Notificar sobre novos leads
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="notify_status_change" name="notify_status_change" value="1"
                                               {{ old('notify_status_change', $settings['notify_status_change'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_status_change">
                                            Notificar sobre mudanças de status
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <!-- Configurações do Servidor SMTP -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-server me-2"></i> Servidor SMTP</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label for="mail_mailer" class="form-label">Driver de Email</label>
                                        <select class="form-select" id="mail_mailer" name="mail_mailer">
                                            <option value="smtp" {{ (old('mail_mailer', $settings['mail_mailer'] ?? '') == 'smtp') ? 'selected' : '' }}>SMTP</option>
                                            <option value="sendmail" {{ (old('mail_mailer', $settings['mail_mailer'] ?? '') == 'sendmail') ? 'selected' : '' }}>Sendmail</option>
                                            <option value="mailgun" {{ (old('mail_mailer', $settings['mail_mailer'] ?? '') == 'mailgun') ? 'selected' : '' }}>Mailgun</option>
                                            <option value="ses" {{ (old('mail_mailer', $settings['mail_mailer'] ?? '') == 'ses') ? 'selected' : '' }}>Amazon SES</option>
                                            <option value="log" {{ (old('mail_mailer', $settings['mail_mailer'] ?? '') == 'log') ? 'selected' : '' }}>Log (para testes)</option>
                                        </select>
                                        <div class="form-text">Método de envio de emails</div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="mail_host" class="form-label">Servidor SMTP</label>
                                        <input type="text" class="form-control" id="mail_host" name="mail_host" 
                                               value="{{ old('mail_host', $settings['mail_host'] ?? 'smtp.gmail.com') }}"
                                               placeholder="smtp.exemplo.com.br">
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="mail_port" class="form-label">Porta SMTP</label>
                                                <input type="number" class="form-control" id="mail_port" name="mail_port" 
                                                       value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}"
                                                       placeholder="587 ou 465">
                                                <div class="form-text">Comum: 587 (TLS) ou 465 (SSL)</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="mail_encryption" class="form-label">Criptografia</label>
                                                <select class="form-select" id="mail_encryption" name="mail_encryption">
                                                    <option value="tls" {{ (old('mail_encryption', $settings['mail_encryption'] ?? '') == 'tls') ? 'selected' : '' }}>TLS</option>
                                                    <option value="ssl" {{ (old('mail_encryption', $settings['mail_encryption'] ?? '') == 'ssl') ? 'selected' : '' }}>SSL</option>
                                                    <option value="" {{ (old('mail_encryption', $settings['mail_encryption'] ?? '') == '') ? 'selected' : '' }}>Nenhuma</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="mail_username" class="form-label">Usuário SMTP</label>
                                        <input type="text" class="form-control" id="mail_username" name="mail_username" 
                                               value="{{ old('mail_username', $settings['mail_username'] ?? '') }}"
                                               placeholder="seu_email@gmail.com">
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="mail_password" class="form-label">Senha SMTP</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="mail_password" name="mail_password" 
                                                   value="{{ old('mail_password', $settings['mail_password'] ?? '') }}"
                                                   placeholder="Senha ou chave de aplicativo">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Para Gmail, use uma <a href="https://support.google.com/accounts/answer/185833" target="_blank">Senha de App</a></div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary" id="testEmailConnection">
                                            <i class="fas fa-paper-plane me-2"></i> Testar Conexão
                                        </button>
                                        <div id="email-test-result" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Templates de Email -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i> Templates de Email</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Os templates de email podem ser personalizados no editor de templates.
                                <a href="{{ route('admin.email-templates.index') }}" class="alert-link">Clique aqui</a> para editar os templates de email.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title">Email de Confirmação</h6>
                                            <p class="card-text small">Enviado ao cliente após inscrição</p>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="send_confirmation_email" name="send_confirmation_email" value="1"
                                                       {{ old('send_confirmation_email', $settings['send_confirmation_email'] ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="send_confirmation_email">Ativar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title">Email de Notificação</h6>
                                            <p class="card-text small">Enviado ao admin após nova inscrição</p>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="send_admin_notification" name="send_admin_notification" value="1"
                                                       {{ old('send_admin_notification', $settings['send_admin_notification'] ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="send_admin_notification">Ativar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h6 class="card-title">Email de Acompanhamento</h6>
                                            <p class="card-text small">Enviado após X dias da inscrição</p>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="send_followup_email" name="send_followup_email" value="1"
                                                       {{ old('send_followup_email', $settings['send_followup_email'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="send_followup_email">Ativar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo da aba Página Obrigado -->
            <div class="tab-pane fade" id="thank-you" role="tabpanel" aria-labelledby="thank-you-tab">
                <div class="card-body">
                    <h5 class="mb-4">Configurações da Página de Agradecimento</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="thank_you_page_title" class="form-label">Título da Página</label>
                                <input type="text" class="form-control" id="thank_you_page_title" name="thank_you_page_title" 
                                       value="{{ old('thank_you_page_title', $settings['thank_you_page_title'] ?? 'Inscrição Confirmada!') }}">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="thank_you_header_color" class="form-label">Cor do Cabeçalho</label>
                                <input type="color" class="form-control form-control-color" id="thank_you_header_color" name="thank_you_header_color" 
                                       value="{{ old('thank_you_header_color', $settings['thank_you_header_color'] ?? '#3a5998') }}">
                                <div class="form-text">Selecione a cor do cabeçalho da página</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="thank_you_page_subtitle" class="form-label">Subtítulo da Página</label>
                        <input type="text" class="form-control" id="thank_you_page_subtitle" name="thank_you_page_subtitle" 
                               value="{{ old('thank_you_page_subtitle', $settings['thank_you_page_subtitle'] ?? 'Sua inscrição foi realizada com sucesso. Nossa equipe entrará em contato em breve.') }}">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="thank_you_custom_message" class="form-label">Mensagem Personalizada</label>
                        <textarea class="form-control" id="thank_you_custom_message" name="thank_you_custom_message" rows="4">{{ old('thank_you_custom_message', $settings['thank_you_custom_message'] ?? '') }}</textarea>
                        <div class="form-text">Você pode usar HTML para formatação</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="thank_you_contact_phone" class="form-label">Telefone de Contato</label>
                                <input type="text" class="form-control" id="thank_you_contact_phone" name="thank_you_contact_phone" 
                                       value="{{ old('thank_you_contact_phone', $settings['thank_you_contact_phone'] ?? '(11) 9999-9999') }}">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="thank_you_contact_email" class="form-label">Email de Contato</label>
                                <input type="email" class="form-control" id="thank_you_contact_email" name="thank_you_contact_email" 
                                       value="{{ old('thank_you_contact_email', $settings['thank_you_contact_email'] ?? 'contato@ensinocerto.com.br') }}">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="thank_you_contact_hours" class="form-label">Horário de Atendimento</label>
                                <input type="text" class="form-control" id="thank_you_contact_hours" name="thank_you_contact_hours" 
                                       value="{{ old('thank_you_contact_hours', $settings['thank_you_contact_hours'] ?? 'Seg-Sex: 8h às 18h') }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="thank_you_show_contact_info" name="thank_you_show_contact_info" value="1"
                                       {{ old('thank_you_show_contact_info', $settings['thank_you_show_contact_info'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="thank_you_show_contact_info">Exibir Informações de Contato</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="thank_you_show_steps" name="thank_you_show_steps" value="1"
                                       {{ old('thank_you_show_steps', $settings['thank_you_show_steps'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="thank_you_show_steps">Exibir Próximos Passos</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="thank_you_show_tips" name="thank_you_show_tips" value="1"
                                       {{ old('thank_you_show_tips', $settings['thank_you_show_tips'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="thank_you_show_tips">Exibir Dicas Importantes</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        As alterações serão aplicadas imediatamente na página de agradecimento após salvar.
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo da aba WhatsApp -->
            <div class="tab-pane fade" id="whatsapp" role="tabpanel" aria-labelledby="whatsapp-tab">
                <div class="card-body">
                    <h5 class="mb-4">Configurações do WhatsApp</h5>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Configure aqui o botão flutuante do WhatsApp que aparecerá na página principal.
                    </div>
                    
                    <!-- Ativar/Desativar WhatsApp -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fab fa-whatsapp me-2"></i> Ativação do Botão WhatsApp</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="whatsapp_enabled" name="whatsapp_enabled" value="1"
                                       {{ old('whatsapp_enabled', $whatsappSettings['whatsapp_enabled'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="whatsapp_enabled">Ativar botão flutuante do WhatsApp</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configurações do WhatsApp -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-cog me-2"></i> Configurações do Botão</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="whatsapp_number" class="form-label">Número do WhatsApp</label>
                                        <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" 
                                               value="{{ old('whatsapp_number', $whatsappSettings['whatsapp_number'] ?? '5511999999999') }}"
                                               placeholder="5511999999999">
                                        <div class="form-text">Formato: código do país + DDD + número (apenas números). Ex: 5511999999999</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="whatsapp_button_position" class="form-label">Posição do Botão</label>
                                        <select class="form-select" id="whatsapp_button_position" name="whatsapp_button_position">
                                            <option value="bottom-right" {{ old('whatsapp_button_position', $whatsappSettings['whatsapp_button_position'] ?? 'bottom-right') == 'bottom-right' ? 'selected' : '' }}>Canto inferior direito</option>
                                            <option value="bottom-left" {{ old('whatsapp_button_position', $whatsappSettings['whatsapp_button_position'] ?? 'bottom-right') == 'bottom-left' ? 'selected' : '' }}>Canto inferior esquerdo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="whatsapp_button_color" class="form-label">Cor do Botão</label>
                                        <input type="color" class="form-control form-control-color" id="whatsapp_button_color" name="whatsapp_button_color" 
                                               value="{{ old('whatsapp_button_color', $whatsappSettings['whatsapp_button_color'] ?? '#25d366') }}">
                                        <div class="form-text">Cor de fundo do botão WhatsApp</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Preview do Botão</label>
                                        <div class="p-3 border rounded" style="position: relative; height: 80px; background-color: #f8f9fa;">
                                            <div id="whatsapp-preview" style="position: absolute; bottom: 15px; right: 15px; width: 50px; height: 50px; border-radius: 50%; background-color: #25d366; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                                                <i class="fab fa-whatsapp"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="whatsapp_message" class="form-label">Mensagem Padrão</label>
                                <textarea class="form-control" id="whatsapp_message" name="whatsapp_message" rows="3">{{ old('whatsapp_message', $whatsappSettings['whatsapp_message'] ?? 'Olá! Tenho interesse no curso EJA. Podem me ajudar?') }}</textarea>
                                <div class="form-text">Mensagem que será enviada automaticamente quando o usuário clicar no botão</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        O botão aparecerá na página principal (welcome) quando ativado. As alterações são aplicadas imediatamente após salvar.
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo da aba Countdown -->
            <div class="tab-pane fade" id="countdown" role="tabpanel" aria-labelledby="countdown-tab">
                <div class="card-body">
                    <h5 class="mb-4">Configurações do Countdown</h5>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Configure aqui o contador regressivo da oferta especial que aparece na página principal.
                    </div>
                    
                    <!-- Ativar/Desativar Countdown -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-clock me-2"></i> Ativação do Countdown</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="countdown_enabled" name="countdown_enabled" value="1"
                                       {{ old('countdown_enabled', $countdownSettings['enabled'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="countdown_enabled">Ativar contador regressivo da oferta</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Data e Hora -->
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-calendar me-2"></i> Data e Hora de Término</h6>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="renewCountdown('daily')">+1 Dia</button>
                                <button type="button" class="btn btn-outline-primary" onclick="renewCountdown('weekly')">+7 Dias</button>
                                <button type="button" class="btn btn-outline-primary" onclick="renewCountdown('monthly')">+30 Dias</button>
                                <button type="button" class="btn btn-outline-warning" onclick="showCustomRenewal()">Personalizado</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="countdown_end_date" class="form-label">Data de Término</label>
                                        <input type="date" class="form-control" id="countdown_end_date" name="countdown_end_date" 
                                               value="{{ old('countdown_end_date', $countdownSettings['end_date'] ?? '2025-12-31') }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="countdown_end_time" class="form-label">Horário de Término</label>
                                        <input type="time" class="form-control" id="countdown_end_time" name="countdown_end_time" 
                                               value="{{ old('countdown_end_time', $countdownSettings['end_time'] ?? '23:59') }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="countdown_timezone" class="form-label">Fuso Horário</label>
                                        <select class="form-select" id="countdown_timezone" name="countdown_timezone">
                                            <option value="America/Sao_Paulo" {{ old('countdown_timezone', $countdownSettings['timezone'] ?? 'America/Sao_Paulo') == 'America/Sao_Paulo' ? 'selected' : '' }}>Brasília (GMT-3)</option>
                                            <option value="America/New_York" {{ old('countdown_timezone', $countdownSettings['timezone'] ?? 'America/Sao_Paulo') == 'America/New_York' ? 'selected' : '' }}>Nova York (GMT-5)</option>
                                            <option value="Europe/London" {{ old('countdown_timezone', $countdownSettings['timezone'] ?? 'America/Sao_Paulo') == 'Europe/London' ? 'selected' : '' }}>Londres (GMT)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Data Atual da Oferta:</strong> {{ $countdownSettings['end_date_formatted'] ?? '31 de Dezembro' }} às {{ $countdownSettings['end_time'] ?? '23:59' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Textos -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-font me-2"></i> Textos da Oferta</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="countdown_text" class="form-label">Texto Antes da Data</label>
                                        <input type="text" class="form-control" id="countdown_text" name="countdown_text" 
                                               value="{{ old('countdown_text', $countdownSettings['text'] ?? 'Somente até') }}">
                                        <div class="form-text">Ex: "Somente até", "Oferta válida até"</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="countdown_discount_text" class="form-label">Texto do Desconto</label>
                                        <input type="text" class="form-control" id="countdown_discount_text" name="countdown_discount_text" 
                                               value="{{ old('countdown_discount_text', $countdownSettings['discount_text'] ?? '50% OFF') }}">
                                        <div class="form-text">Ex: "50% OFF", "70% DE DESCONTO"</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preços -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-dollar-sign me-2"></i> Configuração de Preços</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="countdown_price_discount" class="form-label">Preço com Desconto</label>
                                        <input type="text" class="form-control" id="countdown_price_discount" name="countdown_price_discount" 
                                               value="{{ old('countdown_price_discount', $countdownSettings['price_discount'] ?? 'R$ 89,90') }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="countdown_price_installments_discount" class="form-label">Parcelas (Desconto)</label>
                                        <input type="text" class="form-control" id="countdown_price_installments_discount" name="countdown_price_installments_discount" 
                                               value="{{ old('countdown_price_installments_discount', $countdownSettings['price_installments_discount'] ?? '12x') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="countdown_price_original" class="form-label">Preço Original</label>
                                        <input type="text" class="form-control" id="countdown_price_original" name="countdown_price_original" 
                                               value="{{ old('countdown_price_original', $countdownSettings['price_original'] ?? 'R$ 284,90') }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="countdown_price_installments_original" class="form-label">Parcelas (Original)</label>
                                        <input type="text" class="form-control" id="countdown_price_installments_original" name="countdown_price_installments_original" 
                                               value="{{ old('countdown_price_installments_original', $countdownSettings['price_installments_original'] ?? '24x') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="countdown_pix_price" class="form-label">Preço no PIX</label>
                                        <input type="text" class="form-control" id="countdown_pix_price" name="countdown_pix_price" 
                                               value="{{ old('countdown_pix_price', $countdownSettings['pix_price'] ?? 'R$ 899,00') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Renovação Automática -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-sync me-2"></i> Renovação Automática</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="countdown_renewal_type" class="form-label">Tipo de Renovação</label>
                                        <select class="form-select" id="countdown_renewal_type" name="countdown_renewal_type">
                                            <option value="manual" {{ old('countdown_renewal_type', $countdownSettings['renewal_type'] ?? 'monthly') == 'manual' ? 'selected' : '' }}>Manual</option>
                                            <option value="daily" {{ old('countdown_renewal_type', $countdownSettings['renewal_type'] ?? 'monthly') == 'daily' ? 'selected' : '' }}>Diária</option>
                                            <option value="weekly" {{ old('countdown_renewal_type', $countdownSettings['renewal_type'] ?? 'monthly') == 'weekly' ? 'selected' : '' }}>Semanal</option>
                                            <option value="monthly" {{ old('countdown_renewal_type', $countdownSettings['renewal_type'] ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Mensal</option>
                                        </select>
                                        <div class="form-text">Como a oferta será renovada quando expirar</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="countdown_auto_extend_days" class="form-label">Dias para Estender</label>
                                        <input type="number" class="form-control" id="countdown_auto_extend_days" name="countdown_auto_extend_days" 
                                               value="{{ old('countdown_auto_extend_days', $countdownSettings['auto_extend_days'] ?? 30) }}" min="1">
                                        <div class="form-text">Quantos dias adicionar na renovação automática</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        O countdown aparecerá na seção de oferta especial da página principal quando ativado.
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo da aba Landing Page -->
            <div class="tab-pane fade" id="landing-page" role="tabpanel" aria-labelledby="landing-page-tab">
                <div class="card-body">
                    <h5 class="mb-4">Configurações da Landing Page</h5>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Configure aqui todos os elementos da página principal de cadastro.
                    </div>
                    
                    <!-- Cabeçalho/Hero Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-star me-2"></i> Seção Principal (Hero)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="landing_hero_title" class="form-label">Título Principal</label>
                                        <input type="text" class="form-control" id="landing_hero_title" name="landing_hero_title" 
                                               value="{{ old('landing_hero_title', $landingSettings['hero_title'] ?? 'Transforme sua vida com Ensino Superior de Qualidade!') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="landing_hero_subtitle" class="form-label">Subtítulo</label>
                                        <textarea class="form-control" id="landing_hero_subtitle" name="landing_hero_subtitle" rows="2">{{ old('landing_hero_subtitle', $landingSettings['hero_subtitle'] ?? 'Diplomas reconhecidos pelo MEC, aulas 100% online e suporte dedicado para sua jornada acadêmica.') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_cta_button_text" class="form-label">Texto do Botão CTA</label>
                                        <input type="text" class="form-control" id="landing_cta_button_text" name="landing_cta_button_text" 
                                               value="{{ old('landing_cta_button_text', $landingSettings['cta_button_text'] ?? 'QUERO MINHA VAGA!') }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_cta_button_color" class="form-label">Cor do Botão CTA</label>
                                        <input type="color" class="form-control form-control-color" id="landing_cta_button_color" name="landing_cta_button_color" 
                                               value="{{ old('landing_cta_button_color', $landingSettings['cta_button_color'] ?? '#28a745') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Benefícios -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i> Seção de Benefícios</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="landing_benefits_title" class="form-label">Título da Seção</label>
                                        <input type="text" class="form-control" id="landing_benefits_title" name="landing_benefits_title" 
                                               value="{{ old('landing_benefits_title', $landingSettings['benefits_title'] ?? 'Por que escolher a Ensino Certo?') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_benefit_1" class="form-label">Benefício 1</label>
                                        <input type="text" class="form-control" id="landing_benefit_1" name="landing_benefit_1" 
                                               value="{{ old('landing_benefit_1', $landingSettings['benefit_1'] ?? '✅ Diplomas Reconhecidos pelo MEC') }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_benefit_2" class="form-label">Benefício 2</label>
                                        <input type="text" class="form-control" id="landing_benefit_2" name="landing_benefit_2" 
                                               value="{{ old('landing_benefit_2', $landingSettings['benefit_2'] ?? '✅ Aulas 100% Online') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_benefit_3" class="form-label">Benefício 3</label>
                                        <input type="text" class="form-control" id="landing_benefit_3" name="landing_benefit_3" 
                                               value="{{ old('landing_benefit_3', $landingSettings['benefit_3'] ?? '✅ Preços Acessíveis') }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_benefit_4" class="form-label">Benefício 4</label>
                                        <input type="text" class="form-control" id="landing_benefit_4" name="landing_benefit_4" 
                                               value="{{ old('landing_benefit_4', $landingSettings['benefit_4'] ?? '✅ Suporte Especializado') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Formulário -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-wpforms me-2"></i> Configurações do Formulário</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="landing_form_title" class="form-label">Título do Formulário</label>
                                        <input type="text" class="form-control" id="landing_form_title" name="landing_form_title" 
                                               value="{{ old('landing_form_title', $landingSettings['form_title'] ?? 'Preencha seus dados e garanta sua vaga!') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="landing_form_subtitle" class="form-label">Subtítulo do Formulário</label>
                                        <input type="text" class="form-control" id="landing_form_subtitle" name="landing_form_subtitle" 
                                               value="{{ old('landing_form_subtitle', $landingSettings['form_subtitle'] ?? 'É rápido, fácil e gratuito!') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_form_button_text" class="form-label">Texto do Botão de Envio</label>
                                        <input type="text" class="form-control" id="landing_form_button_text" name="landing_form_button_text" 
                                               value="{{ old('landing_form_button_text', $landingSettings['form_button_text'] ?? 'GARANTIR MINHA VAGA') }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_form_button_color" class="form-label">Cor do Botão</label>
                                        <input type="color" class="form-control form-control-color" id="landing_form_button_color" name="landing_form_button_color" 
                                               value="{{ old('landing_form_button_color', $landingSettings['form_button_color'] ?? '#dc3545') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Configurações de Campos do Formulário -->
                            <h6 class="mt-4 mb-3 text-muted">Campos do Formulário</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="default_course" class="form-label">Curso Padrão</label>
                                        <select class="form-select" id="default_course" name="default_course">
                                            @foreach($formSettings['available_courses'] ?? [] as $key => $label)
                                                <option value="{{ $key }}" {{ ($formSettings['default_course'] ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="default_modality" class="form-label">Modalidade Padrão</label>
                                        <select class="form-select" id="default_modality" name="default_modality">
                                            @foreach($formSettings['available_modalities'] ?? [] as $key => $label)
                                                <option value="{{ $key }}" {{ ($formSettings['default_modality'] ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Cursos Disponíveis</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="coursesEditor" class="json-editor">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Chave</th>
                                                            <th>Nome</th>
                                                            <th width="50"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="coursesTableBody">
                                                        @foreach($formSettings['available_courses'] ?? [] as $key => $value)
                                                            <tr>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="available_courses_keys[]" value="{{ $key }}" required>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="available_courses_values[]" value="{{ $value }}" required>
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-danger remove-row">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <button type="button" class="btn btn-sm btn-primary mt-2" id="addCourseBtn">
                                                    <i class="fas fa-plus me-1"></i> Adicionar Curso
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Modalidades Disponíveis</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="modalitiesEditor" class="json-editor">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Chave</th>
                                                            <th>Nome</th>
                                                            <th width="50"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modalitiesTableBody">
                                                        @foreach($formSettings['available_modalities'] ?? [] as $key => $value)
                                                            <tr>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="available_modalities_keys[]" value="{{ $key }}" required>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="available_modalities_values[]" value="{{ $value }}" required>
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-danger remove-row">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <button type="button" class="btn btn-sm btn-primary mt-2" id="addModalityBtn">
                                                    <i class="fas fa-plus me-1"></i> Adicionar Modalidade
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rodapé -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Configurações do Rodapé</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="landing_footer_company_name" class="form-label">Nome da Empresa</label>
                                        <input type="text" class="form-control" id="landing_footer_company_name" name="landing_footer_company_name" 
                                               value="{{ old('landing_footer_company_name', $landingSettings['footer_company_name'] ?? 'Ensino Certo') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_footer_email" class="form-label">Email de Contato</label>
                                        <input type="email" class="form-control" id="landing_footer_email" name="landing_footer_email" 
                                               value="{{ old('landing_footer_email', $landingSettings['footer_email'] ?? 'contato@ensinocerto.com.br') }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_footer_phone" class="form-label">Telefone de Contato</label>
                                        <input type="text" class="form-control" id="landing_footer_phone" name="landing_footer_phone" 
                                               value="{{ old('landing_footer_phone', $landingSettings['footer_phone'] ?? '(11) 99999-9999') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="landing_footer_address" class="form-label">Endereço</label>
                                        <input type="text" class="form-control" id="landing_footer_address" name="landing_footer_address" 
                                               value="{{ old('landing_footer_address', $landingSettings['footer_address'] ?? 'São Paulo, SP - Brasil') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="landing_footer_copyright" class="form-label">Texto de Copyright</label>
                                        <input type="text" class="form-control" id="landing_footer_copyright" name="landing_footer_copyright" 
                                               value="{{ old('landing_footer_copyright', $landingSettings['footer_copyright'] ?? '© 2025 Ensino Certo. Todos os direitos reservados.') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="landing_mec_authorization_file" class="form-label">Autorização MEC (PDF)</label>
                                        <input type="file" class="form-control" id="landing_mec_authorization_file" name="landing_mec_authorization_file" accept=".pdf">
                                        <div class="form-text">
                                            Faça upload do documento de autorização do MEC em formato PDF. 
                                            @if(isset($landingSettings['mec_authorization_file']) && $landingSettings['mec_authorization_file'])
                                                <br><strong>Arquivo atual:</strong> 
                                                <a href="{{ asset('storage/' . $landingSettings['mec_authorization_file']) }}" target="_blank" class="text-primary">
                                                    <i class="fas fa-file-pdf me-1"></i>Visualizar PDF atual
                                                </a>
                                            @else
                                                <br><em>Nenhum arquivo foi enviado ainda.</em>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="landing_mec_address" class="form-label">Endereço abaixo da autorização do MEC</label>
                                        <textarea class="form-control" id="landing_mec_address" name="landing_mec_address" rows="3" placeholder="Digite o endereço que aparecerá abaixo da autorização do MEC">{{ old('landing_mec_address', $landingSettings['mec_address'] ?? '') }}</textarea>
                                        <div class="form-text">Este endereço será exibido abaixo do link de autorização do MEC no rodapé.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Imagens do Rodapé -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <h6 class="mt-4 mb-3 text-muted">Imagens do Rodapé (abaixo do endereço)</h6>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Faça upload de 3 imagens que serão exibidas abaixo do endereço no rodapé. Recomendamos imagens em formato JPG ou PNG com dimensões similares.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="landing_footer_image_1" class="form-label">Imagem 1</label>
                                        <input type="file" class="form-control" id="landing_footer_image_1" name="landing_footer_image_1" accept="image/*">
                                        <div class="form-text">
                                            @if(isset($landingSettings['footer_image_1']) && $landingSettings['footer_image_1'])
                                                <strong>Imagem atual:</strong><br>
                                                <img src="{{ asset('storage/' . $landingSettings['footer_image_1']) }}" alt="Imagem 1" class="img-thumbnail mt-2" style="max-width: 150px; max-height: 100px;">
                                            @else
                                                <em>Nenhuma imagem foi enviada ainda.</em>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="landing_footer_image_2" class="form-label">Imagem 2</label>
                                        <input type="file" class="form-control" id="landing_footer_image_2" name="landing_footer_image_2" accept="image/*">
                                        <div class="form-text">
                                            @if(isset($landingSettings['footer_image_2']) && $landingSettings['footer_image_2'])
                                                <strong>Imagem atual:</strong><br>
                                                <img src="{{ asset('storage/' . $landingSettings['footer_image_2']) }}" alt="Imagem 2" class="img-thumbnail mt-2" style="max-width: 150px; max-height: 100px;">
                                            @else
                                                <em>Nenhuma imagem foi enviada ainda.</em>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="landing_footer_image_3" class="form-label">Imagem 3</label>
                                        <input type="file" class="form-control" id="landing_footer_image_3" name="landing_footer_image_3" accept="image/*">
                                        <div class="form-text">
                                            @if(isset($landingSettings['footer_image_3']) && $landingSettings['footer_image_3'])
                                                <strong>Imagem atual:</strong><br>
                                                <img src="{{ asset('storage/' . $landingSettings['footer_image_3']) }}" alt="Imagem 3" class="img-thumbnail mt-2" style="max-width: 150px; max-height: 100px;">
                                            @else
                                                <em>Nenhuma imagem foi enviada ainda.</em>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Redes Sociais -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-share-alt me-2"></i> Redes Sociais</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_social_facebook" class="form-label">Facebook URL</label>
                                        <input type="url" class="form-control" id="landing_social_facebook" name="landing_social_facebook" 
                                               value="{{ old('landing_social_facebook', $landingSettings['social_facebook'] ?? '') }}" placeholder="https://facebook.com/suapagina">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_social_instagram" class="form-label">Instagram URL</label>
                                        <input type="url" class="form-control" id="landing_social_instagram" name="landing_social_instagram" 
                                               value="{{ old('landing_social_instagram', $landingSettings['social_instagram'] ?? '') }}" placeholder="https://instagram.com/suapagina">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_social_linkedin" class="form-label">LinkedIn URL</label>
                                        <input type="url" class="form-control" id="landing_social_linkedin" name="landing_social_linkedin" 
                                               value="{{ old('landing_social_linkedin', $landingSettings['social_linkedin'] ?? '') }}" placeholder="https://linkedin.com/company/suaempresa">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_social_youtube" class="form-label">YouTube URL</label>
                                        <input type="url" class="form-control" id="landing_social_youtube" name="landing_social_youtube" 
                                               value="{{ old('landing_social_youtube', $landingSettings['social_youtube'] ?? '') }}" placeholder="https://youtube.com/c/seucanal">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_social_tiktok" class="form-label">TikTok URL</label>
                                        <input type="url" class="form-control" id="landing_social_tiktok" name="landing_social_tiktok" 
                                               value="{{ old('landing_social_tiktok', $landingSettings['social_tiktok'] ?? '') }}" placeholder="https://tiktok.com/@seuusuario">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configurações de Aparência -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-palette me-2"></i> Aparência e Cores</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="landing_primary_color" class="form-label">Cor Primária</label>
                                        <input type="color" class="form-control form-control-color" id="landing_primary_color" name="landing_primary_color" 
                                               value="{{ old('landing_primary_color', $landingSettings['primary_color'] ?? '#007bff') }}">
                                        <div class="form-text">Cor principal do site</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="landing_secondary_color" class="form-label">Cor Secundária</label>
                                        <input type="color" class="form-control form-control-color" id="landing_secondary_color" name="landing_secondary_color" 
                                               value="{{ old('landing_secondary_color', $landingSettings['secondary_color'] ?? '#6c757d') }}">
                                        <div class="form-text">Cor secundária do site</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="landing_accent_color" class="form-label">Cor de Destaque</label>
                                        <input type="color" class="form-control form-control-color" id="landing_accent_color" name="landing_accent_color" 
                                               value="{{ old('landing_accent_color', $landingSettings['accent_color'] ?? '#28a745') }}">
                                        <div class="form-text">Cor para elementos de destaque</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configurações do Chat de Suporte -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-comments mr-2"></i> Chat de Suporte ao Cliente</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Configure o chat de suporte que aparecerá na landing page para ajudar os visitantes.
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="landing_chat_enabled" name="landing_chat_enabled" 
                                                   value="1" {{ old('landing_chat_enabled', $landingSettings['chat_enabled'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="landing_chat_enabled">Ativar Chat de Suporte</label>
                                        </div>
                                        <div class="form-text">Ative para exibir o chat de suporte na landing page</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_chat_title" class="form-label">Título do Chat</label>
                                        <input type="text" class="form-control" id="landing_chat_title" name="landing_chat_title" 
                                               value="{{ old('landing_chat_title', $landingSettings['chat_title'] ?? 'Precisa de ajuda?') }}">
                                        <div class="form-text">Título que aparecerá no botão do chat</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_chat_welcome_message" class="form-label">Mensagem de Boas-vindas</label>
                                        <textarea class="form-control" id="landing_chat_welcome_message" name="landing_chat_welcome_message" rows="3" placeholder="Mensagem exibida quando o chat é aberto">{{ $landingSettings['chat_welcome_message'] ?? 'Olá! Sou o assistente virtual da Ensino Certo, especializado em EJA Supletivo. Como posso ajudá-lo hoje? Posso orientar sobre matrículas, pagamentos, disciplinas e muito mais!' }}</textarea>
                                        <small class="form-text text-muted">Mensagem de boas-vindas exibida quando o usuário abre o chat.</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_chat_position" class="form-label">Posição do Chat</label>
                                        <select class="form-select" id="landing_chat_position" name="landing_chat_position">
                                            <option value="bottom-right" {{ ($landingSettings['chat_position'] ?? 'bottom-right') == 'bottom-right' ? 'selected' : '' }}>Canto inferior direito</option>
                                            <option value="bottom-left" {{ ($landingSettings['chat_position'] ?? 'bottom-right') == 'bottom-left' ? 'selected' : '' }}>Canto inferior esquerdo</option>
                                        </select>
                                        <div class="form-text">Posição onde o botão do chat aparecerá na página</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_chat_color" class="form-label">Cor do Botão do Chat</label>
                                        <input type="color" class="form-control form-control-color" id="landing_chat_color" name="landing_chat_color" 
                                               value="{{ old('landing_chat_color', $landingSettings['chat_color'] ?? '#007bff') }}">
                                        <div class="form-text">Cor principal do botão do chat</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="landing_chat_icon" class="form-label">Ícone do Chat</label>
                                        <select class="form-select" id="landing_chat_icon" name="landing_chat_icon">
                                            <option value="fas fa-comments" {{ ($landingSettings['chat_icon'] ?? 'fas fa-comments') == 'fas fa-comments' ? 'selected' : '' }}>💬 Comentários</option>
                                            <option value="fas fa-headset" {{ ($landingSettings['chat_icon'] ?? 'fas fa-comments') == 'fas fa-headset' ? 'selected' : '' }}>🎧 Headset</option>
                                            <option value="fas fa-question-circle" {{ ($landingSettings['chat_icon'] ?? 'fas fa-comments') == 'fas fa-question-circle' ? 'selected' : '' }}>❓ Interrogação</option>
                                            <option value="fas fa-robot" {{ ($landingSettings['chat_icon'] ?? 'fas fa-comments') == 'fas fa-robot' ? 'selected' : '' }}>🤖 Robô</option>
                                        </select>
                                        <div class="form-text">Ícone que aparecerá no botão do chat</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Todas as configurações da landing page são aplicadas automaticamente após salvar.
                    </div>
                </div>
            </div>

            <!-- Aba do ChatGPT -->
            <div class="tab-pane fade" id="chatgpt" role="tabpanel" aria-labelledby="chatgpt-tab">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-4">Configurações do ChatGPT</h5>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Configure a integração com o ChatGPT para geração automática de templates.
                        </div>

                        <div class="mb-3">
                            <label for="ai_api_key" class="form-label">API Key <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('ai_settings.api_key') is-invalid @enderror" 
                                   id="ai_api_key" name="ai_settings[api_key]" value="{{ old('ai_settings.api_key', $aiSettings['api_key'] ?? '') }}">
                            <div class="form-text">Sua chave de API do OpenAI. Obtenha em: <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com/api-keys</a></div>
                            @error('ai_settings.api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="ai_model" class="form-label">Modelo <span class="text-danger">*</span></label>
                            <select class="form-select @error('ai_settings.model') is-invalid @enderror" id="ai_model" name="ai_settings[model]">
                                <optgroup label="Modelos Mini (Mais Rápidos e Econômicos)">
                                    <option value="gpt-4o-mini" {{ ($aiSettings['model'] ?? 'gpt-4o-mini') == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini (Recomendado)</option>
                                    <option value="o1-mini" {{ ($aiSettings['model'] ?? 'gpt-4o-mini') == 'o1-mini' ? 'selected' : '' }}>o1 Mini</option>
                                    <option value="o3-mini" {{ ($aiSettings['model'] ?? 'gpt-4o-mini') == 'o3-mini' ? 'selected' : '' }}>o3 Mini</option>
                                    <option value="gpt-3.5-turbo" {{ ($aiSettings['model'] ?? 'gpt-4o-mini') == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                                </optgroup>
                                <optgroup label="Modelos Padrão">
                                    <option value="gpt-4o" {{ ($aiSettings['model'] ?? 'gpt-4o-mini') == 'gpt-4o' ? 'selected' : '' }}>GPT-4o</option>
                                    <option value="gpt-4-turbo" {{ ($aiSettings['model'] ?? 'gpt-4o-mini') == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>
                                    <option value="gpt-4" {{ ($aiSettings['model'] ?? 'gpt-4o-mini') == 'gpt-4' ? 'selected' : '' }}>GPT-4</option>
                                </optgroup>
                                <optgroup label="Modelos de Raciocínio">
                                    <option value="o1-preview" {{ ($aiSettings['model'] ?? 'gpt-4o-mini') == 'o1-preview' ? 'selected' : '' }}>o1 Preview</option>
                                    <option value="o1" {{ ($aiSettings['model'] ?? 'gpt-4o-mini') == 'o1' ? 'selected' : '' }}>o1</option>
                                    <option value="o3" {{ ($aiSettings['model'] ?? 'gpt-4o-mini') == 'o3' ? 'selected' : '' }}>o3</option>
                                </optgroup>
                            </select>
                            <div class="form-text">Escolha o modelo do ChatGPT a ser utilizado. Modelos mini são mais rápidos e econômicos.</div>
                            @error('ai_settings.model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="ai_system_prompt" class="form-label">Prompt do Sistema</label>
                            <textarea class="form-control @error('ai_settings.system_prompt') is-invalid @enderror" 
                                      id="ai_system_prompt" name="ai_settings[system_prompt]" rows="4">{{ old('ai_settings.system_prompt', $aiSettings['system_prompt'] ?? '') }}</textarea>
                            <div class="form-text">Defina a personalidade ou comportamento do assistente ao criar templates.</div>
                            @error('ai_settings.system_prompt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ai_email_template_prompt" class="form-label">Prompt para Templates de Email</label>
                            <textarea class="form-control @error('ai_settings.email_template_prompt') is-invalid @enderror" 
                                      id="ai_email_template_prompt" name="ai_settings[email_template_prompt]" rows="8">{{ old('ai_settings.email_template_prompt', $aiSettings['email_template_prompt'] ?? '') }}</textarea>
                            <div class="form-text">Prompt específico para geração de templates de email marketing. Use variáveis como {templateType}, {objective}, {targetAudience}, {additionalInstructions}, {variablesText}.</div>
                            @error('ai_settings.email_template_prompt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ai_whatsapp_template_prompt" class="form-label">Prompt para Templates de WhatsApp</label>
                            <textarea class="form-control @error('ai_settings.whatsapp_template_prompt') is-invalid @enderror" 
                                      id="ai_whatsapp_template_prompt" name="ai_settings[whatsapp_template_prompt]" rows="6">{{ old('ai_settings.whatsapp_template_prompt', $aiSettings['whatsapp_template_prompt'] ?? '') }}</textarea>
                            <div class="form-text">Prompt específico para geração de templates de WhatsApp. Use variáveis como {objective}, {targetAudience}, {additionalInstructions}.</div>
                            @error('ai_settings.whatsapp_template_prompt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ai_contract_template_prompt" class="form-label">Prompt para Templates de Contratos</label>
                            <textarea class="form-control @error('ai_settings.contract_template_prompt') is-invalid @enderror" 
                                      id="ai_contract_template_prompt" name="ai_settings[contract_template_prompt]" rows="10">{{ old('ai_settings.contract_template_prompt', $aiSettings['contract_template_prompt'] ?? '') }}</textarea>
                            <div class="form-text">Prompt específico para geração de templates de contratos. Use variáveis como {objective}, {contractType}, {variablesText}, {referenceInstructions}, {additionalInstructions}.</div>
                            @error('ai_settings.contract_template_prompt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ai_support_prompt" class="form-label">Prompt para Suporte ao Cliente</label>
                            <textarea class="form-control @error('ai_settings.support_prompt') is-invalid @enderror" 
                                      id="ai_support_prompt" name="ai_settings[support_prompt]" rows="8">{{ old('ai_settings.support_prompt', $aiSettings['support_prompt'] ?? '') }}</textarea>
                            <div class="form-text">Prompt específico para o chat de suporte ao cliente. Este prompt define como o ChatGPT deve se comportar ao atender dúvidas dos alunos.</div>
                            @error('ai_settings.support_prompt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="ai_is_active" name="ai_settings[is_active]" 
                                       value="1" {{ ($aiSettings['is_active'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="ai_is_active">Ativar ChatGPT</label>
                            </div>
                            <div class="form-text">Ative para permitir a geração de templates com IA.</div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Salvar Configurações
                            </button>
                            
                            <button type="button" class="btn btn-outline-secondary" id="testAiConnection">
                                <i class="fas fa-plug me-2"></i>
                                Testar Conexão
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo da aba Contratos -->
            <div class="tab-pane fade" id="contracts" role="tabpanel" aria-labelledby="contracts-tab">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-4">Configurações de Contratos</h5>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Configure aqui as configurações relacionadas aos contratos digitais, incluindo a assinatura padrão da escola.
                        </div>

                        <!-- Assinatura da Escola -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fas fa-signature me-2"></i>Assinatura da Escola</h6>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enable_school_signature" name="contract_settings[enable_school_signature]" 
                                               value="1" {{ ($contractSettings['enable_school_signature'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_school_signature">Ativar Assinatura Automática</label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="school_signature_name" class="form-label">Nome do Responsável</label>
                                            <input type="text" class="form-control" id="school_signature_name" name="contract_settings[school_signature_name]" 
                                                   value="{{ old('contract_settings.school_signature_name', $contractSettings['school_signature_name'] ?? '') }}"
                                                   placeholder="Nome completo do responsável">
                                            <div class="form-text">Nome que aparecerá abaixo da assinatura</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="school_signature_title" class="form-label">Cargo/Função</label>
                                            <input type="text" class="form-control" id="school_signature_title" name="contract_settings[school_signature_title]" 
                                                   value="{{ old('contract_settings.school_signature_title', $contractSettings['school_signature_title'] ?? '') }}"
                                                   placeholder="Ex: Diretor(a), Coordenador(a)">
                                            <div class="form-text">Cargo ou função do responsável</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Assinatura Digital</label>
                                    <div class="signature-container">
                                        <canvas id="school-signature-pad" class="signature-pad" width="600" height="200" 
                                                style="border: 2px solid #ddd; border-radius: 8px; background: white; cursor: crosshair; display: block; margin: 0 auto;"></canvas>
                                        <div class="signature-controls text-center mt-2">
                                            <button type="button" class="btn btn-secondary btn-sm me-2" onclick="clearSchoolSignature()">
                                                <i class="fas fa-eraser me-1"></i>Limpar
                                            </button>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="saveSchoolSignature()">
                                                <i class="fas fa-save me-1"></i>Salvar Assinatura
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text">Desenhe a assinatura no campo acima usando o mouse ou toque na tela</div>
                                    
                                    <!-- Campo hidden para armazenar a assinatura -->
                                    <input type="hidden" id="school_signature_data" name="contract_settings[school_signature_data]" 
                                           value="{{ old('contract_settings.school_signature_data', $contractSettings['school_signature_data'] ?? '') }}">
                                </div>
                                
                                <!-- Preview da assinatura atual -->
                                @if(!empty($contractSettings['school_signature_data']))
                                <div class="mb-3">
                                    <label class="form-label">Assinatura Atual</label>
                                    <div class="current-signature-preview" style="border: 1px solid #ddd; border-radius: 8px; padding: 1rem; background: #f8f9fa; text-align: center;">
                                        <img src="{{ $contractSettings['school_signature_data'] }}" alt="Assinatura da Escola" style="max-width: 300px; max-height: 100px;">
                                        <div class="mt-2">
                                            <strong>{{ $contractSettings['school_signature_name'] ?? 'Nome não definido' }}</strong><br>
                                            <small class="text-muted">{{ $contractSettings['school_signature_title'] ?? 'Cargo não definido' }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Configurações Gerais de Contratos -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Configurações Gerais</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="contract_validity_days" class="form-label">Validade do Link (dias)</label>
                                            <input type="number" class="form-control" id="contract_validity_days" name="contract_settings[validity_days]" 
                                                   value="{{ old('contract_settings.validity_days', $contractSettings['validity_days'] ?? 30) }}"
                                                   min="1" max="365">
                                            <div class="form-text">Número de dias que o link do contrato permanecerá válido</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="contract_reminder_days" class="form-label">Lembrete antes do vencimento (dias)</label>
                                            <input type="number" class="form-control" id="contract_reminder_days" name="contract_settings[reminder_days]" 
                                                   value="{{ old('contract_settings.reminder_days', $contractSettings['reminder_days'] ?? 3) }}"
                                                   min="1" max="30">
                                            <div class="form-text">Dias antes do vencimento para enviar lembrete</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="contract_auto_send" name="contract_settings[auto_send]" 
                                           value="1" {{ ($contractSettings['auto_send'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="contract_auto_send">
                                        Enviar contrato automaticamente após geração
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="contract_auto_reminder" name="contract_settings[auto_reminder]" 
                                           value="1" {{ ($contractSettings['auto_reminder'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="contract_auto_reminder">
                                        Enviar lembretes automáticos
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo da aba Mercado Pago -->
            <div class="tab-pane fade" id="mercadopago" role="tabpanel" aria-labelledby="mercadopago-tab">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-4">Configurações do Mercado Pago</h5>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Configure aqui as credenciais e configurações do Mercado Pago para processar pagamentos.
                        </div>

                        <!-- Status da Integração -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fas fa-toggle-on me-2"></i>Status da Integração</h6>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="mercadopago_enabled" name="payment_settings[mercadopago_enabled]" 
                                               value="1" {{ ($paymentSettings['mercadopago_enabled'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="mercadopago_enabled">Ativar Mercado Pago</label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="mercadopago_sandbox" name="payment_settings[mercadopago_sandbox]" 
                                                   value="1" {{ ($paymentSettings['mercadopago_sandbox'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="mercadopago_sandbox">
                                                Modo Sandbox (Teste)
                                            </label>
                                            <div class="form-text">Ative para usar o ambiente de testes do Mercado Pago</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-outline-primary" id="testMercadoPagoConnection">
                                                <i class="fas fa-plug me-2"></i>
                                                Testar Conexão
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Credenciais do Mercado Pago -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-key me-2"></i>Credenciais</h6>
                            </div>
                            <div class="card-body">
                                <!-- Credenciais de Produção (Campos Existentes) -->
                                <div class="alert alert-success">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <strong>Credenciais Produção:</strong> Use estas credenciais para pagamentos reais. Elas são usadas quando o modo sandbox está desativado.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mercadopago_access_token" class="form-label">Access Token (Produção)</label>
                                            <input type="password" class="form-control" id="mercadopago_access_token" name="payment_settings[mercadopago_access_token]" 
                                                   value="{{ old('payment_settings.mercadopago_access_token', $paymentSettings['mercadopago_access_token'] ?? '') }}"
                                                   placeholder="APP_USR-xxxxx-xxxxxx-xxxxx">
                                            <div class="form-text">Token de acesso para ambiente de produção</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mercadopago_public_key" class="form-label">Public Key (Produção)</label>
                                            <input type="text" class="form-control" id="mercadopago_public_key" name="payment_settings[mercadopago_public_key]" 
                                                   value="{{ old('payment_settings.mercadopago_public_key', $paymentSettings['mercadopago_public_key'] ?? '') }}"
                                                   placeholder="APP_USR-xxxxx-xxxxxx-xxxxx">
                                            <div class="form-text">Chave pública para ambiente de produção</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- Credenciais Específicas para Sandbox -->
                                <div class="alert alert-warning">
                                    <i class="fas fa-flask me-2"></i>
                                    <strong>Credenciais Sandbox:</strong> Use estas credenciais para testes. Elas têm prioridade quando o modo sandbox está ativo.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mercadopago_sandbox_access_token" class="form-label">Access Token (Sandbox)</label>
                                            <input type="password" class="form-control" id="mercadopago_sandbox_access_token" name="payment_settings[mercadopago_sandbox_access_token]" 
                                                   value="{{ old('payment_settings.mercadopago_sandbox_access_token', $paymentSettings['mercadopago_sandbox_access_token'] ?? '') }}"
                                                   placeholder="TEST-xxxxx-xxxxxx-xxxxx">
                                            <div class="form-text">Token de acesso para ambiente de testes</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mercadopago_sandbox_public_key" class="form-label">Public Key (Sandbox)</label>
                                            <input type="text" class="form-control" id="mercadopago_sandbox_public_key" name="payment_settings[mercadopago_sandbox_public_key]" 
                                                   value="{{ old('payment_settings.mercadopago_sandbox_public_key', $paymentSettings['mercadopago_sandbox_public_key'] ?? '') }}"
                                                   placeholder="TEST-xxxxx-xxxxxx-xxxxx">
                                            <div class="form-text">Chave pública para ambiente de testes</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- Webhook Secret -->
                                <div class="mb-3">
                                    <label for="mercadopago_webhook_secret" class="form-label">Webhook Secret</label>
                                    <input type="password" class="form-control" id="mercadopago_webhook_secret" name="payment_settings[mercadopago_webhook_secret]" 
                                           value="{{ old('payment_settings.mercadopago_webhook_secret', $paymentSettings['mercadopago_webhook_secret'] ?? '') }}"
                                           placeholder="Digite o secret para validação dos webhooks">
                                    <div class="form-text">Chave secreta para validar webhooks do Mercado Pago</div>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>URL do Webhook:</strong> <code>{{ url('/webhook/mercadopago') }}</code><br>
                                    Configure esta URL no painel do Mercado Pago para receber notificações de pagamento.
                                </div>
                            </div>
                        </div>

                        <!-- Configurações de Notificações -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Notificações</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="mercadopago_email_notifications" name="payment_settings[mercadopago_email_notifications]" 
                                                   value="1" {{ ($paymentSettings['mercadopago_email_notifications'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="mercadopago_email_notifications">
                                                Notificações por Email
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="mercadopago_whatsapp_notifications" name="payment_settings[mercadopago_whatsapp_notifications]" 
                                                   value="1" {{ ($paymentSettings['mercadopago_whatsapp_notifications'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="mercadopago_whatsapp_notifications">
                                                Notificações por WhatsApp
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="mercadopago_sms_notifications" name="payment_settings[mercadopago_sms_notifications]" 
                                                   value="1" {{ ($paymentSettings['mercadopago_sms_notifications'] ?? false) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="mercadopago_sms_notifications">
                                                Notificações por SMS
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configurações de Automação -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-robot me-2"></i>Automação</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="mercadopago_auto_reminders" name="payment_settings[mercadopago_auto_reminders]" 
                                                   value="1" {{ ($paymentSettings['mercadopago_auto_reminders'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="mercadopago_auto_reminders">
                                                Lembretes Automáticos
                                            </label>
                                            <div class="form-text">Enviar lembretes automáticos de pagamento</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="mercadopago_auto_generation" name="payment_settings[mercadopago_auto_generation]" 
                                                   value="1" {{ ($paymentSettings['mercadopago_auto_generation'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="mercadopago_auto_generation">
                                                Geração Automática
                                            </label>
                                            <div class="form-text">Gerar próximos pagamentos automaticamente</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Comando Cron:</strong> Configure o comando <code>php artisan payments:process-reminders</code> para executar a cada 30 minutos.
                                </div>
                            </div>
                        </div>

                        <!-- Configurações de Pagamento -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Configurações de Pagamento</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mercadopago_currency" class="form-label">Moeda</label>
                                            <select class="form-control" id="mercadopago_currency" name="payment_settings[mercadopago_currency]">
                                                <option value="BRL" {{ ($paymentSettings['mercadopago_currency'] ?? 'BRL') === 'BRL' ? 'selected' : '' }}>Real Brasileiro (BRL)</option>
                                                <option value="USD" {{ ($paymentSettings['mercadopago_currency'] ?? 'BRL') === 'USD' ? 'selected' : '' }}>Dólar Americano (USD)</option>
                                                <option value="EUR" {{ ($paymentSettings['mercadopago_currency'] ?? 'BRL') === 'EUR' ? 'selected' : '' }}>Euro (EUR)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="mercadopago_country" class="form-label">País</label>
                                            <select class="form-control" id="mercadopago_country" name="payment_settings[mercadopago_country]">
                                                <option value="BR" {{ ($paymentSettings['mercadopago_country'] ?? 'BR') === 'BR' ? 'selected' : '' }}>Brasil</option>
                                                <option value="AR" {{ ($paymentSettings['mercadopago_country'] ?? 'BR') === 'AR' ? 'selected' : '' }}>Argentina</option>
                                                <option value="MX" {{ ($paymentSettings['mercadopago_country'] ?? 'BR') === 'MX' ? 'selected' : '' }}>México</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Salvar Configurações
                            </button>
                            
                            <div>
                                <a href="{{ route('admin.payments.dashboard') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Dashboard de Pagamentos
                                </a>
                                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-info">
                                    <i class="fas fa-list me-2"></i>
                                    Gerenciar Pagamentos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações do Formulário -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-end align-items-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Salvar Configurações
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript para Scroll Horizontal das Abas -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scrollContainer = document.getElementById('settingsTabsScroll');
    const scrollLeftBtn = document.getElementById('scrollLeft');
    const scrollRightBtn = document.getElementById('scrollRight');
    
    if (!scrollContainer || !scrollLeftBtn || !scrollRightBtn) {
        console.log('⚠️ Elementos de scroll das abas não encontrados');
        return;
    }
    
    console.log('🎯 Inicializando scroll horizontal das abas...');
    
    // Função para verificar se pode rolar
    function updateScrollButtons() {
        const { scrollLeft, scrollWidth, clientWidth } = scrollContainer;
        
        // Botão esquerdo
        scrollLeftBtn.disabled = scrollLeft <= 0;
        
        // Botão direito
        scrollRightBtn.disabled = scrollLeft >= scrollWidth - clientWidth - 1;
        
        // Atualizar aparência dos botões
        if (scrollLeftBtn.disabled) {
            scrollLeftBtn.style.opacity = '0.5';
            scrollLeftBtn.style.cursor = 'not-allowed';
        } else {
            scrollLeftBtn.style.opacity = '1';
            scrollLeftBtn.style.cursor = 'pointer';
        }
        
        if (scrollRightBtn.disabled) {
            scrollRightBtn.style.opacity = '0.5';
            scrollRightBtn.style.cursor = 'not-allowed';
        } else {
            scrollRightBtn.style.opacity = '1';
            scrollRightBtn.style.cursor = 'pointer';
        }
    }
    
    // Função para rolar para esquerda
    function scrollLeft() {
        const scrollAmount = scrollContainer.clientWidth * 0.8;
        scrollContainer.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    }
    
    // Função para rolar para direita
    function scrollRight() {
        const scrollAmount = scrollContainer.clientWidth * 0.8;
        scrollContainer.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    }
    
    // Event listeners
    scrollLeftBtn.addEventListener('click', scrollLeft);
    scrollRightBtn.addEventListener('click', scrollRight);
    
    // Atualizar botões quando scrollar
    scrollContainer.addEventListener('scroll', updateScrollButtons);
    
    // Atualizar botões quando redimensionar
    window.addEventListener('resize', updateScrollButtons);
    
    // Atualizar botões inicialmente
    updateScrollButtons();
    
    // Adicionar suporte para scroll com mouse wheel
    scrollContainer.addEventListener('wheel', function(e) {
        e.preventDefault();
        const scrollAmount = e.deltaY > 0 ? 100 : -100;
        scrollContainer.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    });
    
    console.log('✅ Scroll horizontal das abas inicializado');
});
</script>

@endsection 