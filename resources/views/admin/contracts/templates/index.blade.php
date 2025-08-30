@extends('layouts.admin')

@section('title', 'Templates de Contratos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Templates de Contratos
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Voltar aos Contratos
                        </a>
                        <button type="button" class="btn btn-success" onclick="showAiGeneratorModal()">
                            <i class="fas fa-robot me-1"></i>
                            Gerar com IA
                        </button>
                        <a href="{{ route('admin.contracts.templates.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Novo Template
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @php
                        $aiSettings = \App\Models\SystemSetting::getAiSettings();
                        $aiConfigured = $aiSettings['is_active'] && !empty($aiSettings['api_key']);
                    @endphp

                    @if(!$aiConfigured)
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>ChatGPT não configurado!</strong> Para usar a geração automática de templates com IA, 
                            <a href="{{ route('admin.settings.index') }}#chatgpt-tab" class="alert-link">configure a API key do ChatGPT</a> 
                            nas configurações do sistema.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Estatísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1">{{ $templates->total() }}</h4>
                                    <small>Total de Templates</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1">{{ $templates->where('is_active', true)->count() }}</h4>
                                    <small>Ativos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1">{{ $templates->where('is_default', true)->count() }}</h4>
                                    <small>Padrão</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4 class="mb-1">{{ $templates->sum('contracts_count') }}</h4>
                                    <small>Contratos Gerados</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Templates -->
                    <div class="row">
                        @forelse($templates as $template)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 {{ $template->is_default ? 'border-warning' : ($template->is_active ? 'border-success' : 'border-secondary') }}">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0">
                                            @if($template->is_default)
                                                <i class="fas fa-star text-warning me-1" title="Template Padrão"></i>
                                            @endif
                                            {{ $template->name }}
                                        </h6>
                                        <div class="d-flex gap-1">
                                            <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                                {{ $template->is_active ? 'Ativo' : 'Inativo' }}
                                            </span>
                                            @if($template->is_default)
                                                <span class="badge bg-warning">Padrão</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        @if($template->description)
                                            <p class="card-text text-muted small">{{ Str::limit($template->description, 100) }}</p>
                                        @endif
                                        
                                        <div class="row text-center mb-3">
                                            <div class="col-6">
                                                <h6 class="mb-0">{{ $template->contracts_count }}</h6>
                                                <small class="text-muted">Contratos</small>
                                            </div>
                                            <div class="col-6">
                                                <h6 class="mb-0">{{ $template->validity_days }}</h6>
                                                <small class="text-muted">Dias Válidos</small>
                                            </div>
                                        </div>
                                        
                                        <div class="text-muted small mb-3">
                                            <i class="fas fa-calendar me-1"></i>
                                            Criado em {{ $template->created_at->format('d/m/Y') }}
                                            @if($template->creator)
                                                por {{ $template->creator->name }}
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ route('admin.contracts.templates.show', $template) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.contracts.templates.preview', $template) }}" class="btn btn-sm btn-outline-info" target="_blank">
                                                <i class="fas fa-search"></i>
                                            </a>
                                            <a href="{{ route('admin.contracts.templates.edit', $template) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if(!$template->is_default)
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="setDefault({{ $template->id }})">
                                                                <i class="fas fa-star me-2"></i>
                                                                Definir como Padrão
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="toggleActive({{ $template->id }})">
                                                            <i class="fas fa-toggle-{{ $template->is_active ? 'off' : 'on' }} me-2"></i>
                                                            {{ $template->is_active ? 'Desativar' : 'Ativar' }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.contracts.templates.duplicate', $template) }}">
                                                            <i class="fas fa-copy me-2"></i>
                                                            Duplicar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    @if($template->contracts_count == 0)
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" onclick="deleteTemplate({{ $template->id }})">
                                                                <i class="fas fa-trash me-2"></i>
                                                                Excluir
                                                            </a>
                                                        </li>
                                                    @else
                                                        <li>
                                                            <span class="dropdown-item text-muted">
                                                                <i class="fas fa-info-circle me-2"></i>
                                                                Não pode ser excluído
                                                            </span>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nenhum template encontrado</h5>
                                    <p class="text-muted">Crie seu primeiro template de contrato para começar.</p>
                                    <a href="{{ route('admin.contracts.templates.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>
                                        Criar Primeiro Template
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Paginação -->
                    @if($templates->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $templates->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Geração com IA -->
<div class="modal fade" id="aiGeneratorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-robot me-2"></i>
                    Gerar Template com Inteligência Artificial
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="aiGeneratorForm">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Como funciona:</strong> Descreva o tipo de contrato que você precisa e a IA criará um template profissional e juridicamente adequado para sua instituição.
                    </div>

                    <div class="mb-3">
                        <label for="objective" class="form-label">Objetivo do Contrato *</label>
                        <textarea class="form-control" id="objective" name="objective" rows="3" 
                                  maxlength="500"
                                  placeholder="Ex: Contrato para matrícula em curso EJA com pagamento parcelado e certificação reconhecida pelo MEC..." 
                                  required
                                  oninput="updateCharacterCount('objective', 500)"></textarea>
                        <div class="form-text">
                            Descreva detalhadamente o que o contrato deve cobrir (máx. 500 caracteres)
                            <span id="objective_count" class="text-muted">0/500 caracteres</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="contract_type" class="form-label">Tipo de Contrato *</label>
                        <select class="form-select" id="contract_type" name="contract_type" required>
                            <option value="">Selecione o tipo...</option>
                            <option value="educacional">Prestação de Serviços Educacionais</option>
                            <option value="matricula">Contrato de Matrícula</option>
                            <option value="curso">Contrato de Curso</option>
                            <option value="supletivo">Ensino Supletivo</option>
                            <option value="eja">Educação de Jovens e Adultos (EJA)</option>
                            <option value="tecnico">Curso Técnico</option>
                            <option value="superior">Ensino Superior</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="reference_file" class="form-label">Arquivo de Referência (Opcional)</label>
                        <input type="file" class="form-control" id="reference_file" name="reference_file" 
                               accept=".docx,.doc,.pdf,.txt" onchange="handleFileUpload(this)">
                        <div class="form-text">
                            Envie um arquivo DOCX, DOC, PDF ou TXT para que a IA se baseie no seu formato e conteúdo
                            <div id="file_status" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="additional_instructions" class="form-label">Instruções Adicionais</label>
                        <textarea class="form-control" id="additional_instructions" name="additional_instructions" rows="3" 
                                  maxlength="1000" 
                                  placeholder="Ex: Incluir cláusulas específicas sobre aulas online, material didático, política de faltas..."
                                  oninput="updateCharacterCount('additional_instructions', 1000)"></textarea>
                        <div class="form-text">
                            Especificações extras que devem ser incluídas no contrato (opcional)
                            <span id="additional_instructions_count" class="text-muted">0/1000 caracteres</span>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Importante:</strong> O template gerado deve ser revisado por um profissional jurídico antes do uso.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="generateWithAi()" id="generateBtn">
                    <i class="fas fa-robot me-1"></i>
                    Gerar Template
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Loading -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-success mb-3" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <h6>Gerando template com IA...</h6>
                <p class="text-muted mb-0">Isso pode levar alguns segundos</p>
            </div>
        </div>
    </div>
</div>

<script>
function showAiGeneratorModal() {
    const modal = new bootstrap.Modal(document.getElementById('aiGeneratorModal'));
    modal.show();
}

function updateCharacterCount(fieldId, maxLength) {
    const field = document.getElementById(fieldId);
    const countElement = document.getElementById(fieldId + '_count');
    const currentLength = field.value.length;
    
    countElement.textContent = currentLength + '/' + maxLength + ' caracteres';
    
    // Mudar cor baseado no limite
    if (currentLength > maxLength * 0.9) {
        countElement.className = 'text-warning';
    } else if (currentLength >= maxLength) {
        countElement.className = 'text-danger';
    } else {
        countElement.className = 'text-muted';
    }
}

let uploadedFileContent = null;

function handleFileUpload(input) {
    const file = input.files[0];
    const statusDiv = document.getElementById('file_status');
    
    if (!file) {
        statusDiv.innerHTML = '';
        uploadedFileContent = null;
        return;
    }
    
    // Validar tamanho do arquivo (máx 5MB)
    if (file.size > 5 * 1024 * 1024) {
        statusDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Arquivo muito grande. Máximo 5MB.</span>';
        input.value = '';
        uploadedFileContent = null;
        return;
    }
    
    // Mostrar status de upload
    statusDiv.innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-spin"></i> Processando arquivo...</span>';
    
    // Criar FormData para upload
    const formData = new FormData();
    formData.append('file', file);
    
    // Fazer upload do arquivo
    fetch('{{ route("admin.contracts.templates.upload-reference") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            uploadedFileContent = data.content;
            statusDiv.innerHTML = `
                <span class="text-success">
                    <i class="fas fa-check-circle"></i> 
                    Arquivo processado com sucesso! (${Math.round(file.size / 1024)} KB)
                </span>
            `;
        } else {
            statusDiv.innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ${data.message}</span>`;
            uploadedFileContent = null;
        }
    })
    .catch(error => {
        console.error('Erro no upload:', error);
        statusDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Erro ao processar arquivo.</span>';
        uploadedFileContent = null;
    });
}

function generateWithAi() {
    const form = document.getElementById('aiGeneratorForm');
    const formData = new FormData(form);
    
    // Validar campos obrigatórios
    const objective = formData.get('objective');
    const contractType = formData.get('contract_type');
    const additionalInstructions = formData.get('additional_instructions');
    
    if (!objective || !contractType) {
        alert('Por favor, preencha todos os campos obrigatórios.');
        return;
    }
    
    // Validar limites de caracteres
    if (objective.length > 500) {
        alert('O campo "Objetivo do Contrato" não pode ter mais de 500 caracteres.');
        return;
    }
    
    if (additionalInstructions && additionalInstructions.length > 1000) {
        alert('O campo "Instruções Adicionais" não pode ter mais de 1000 caracteres.');
        return;
    }
    
    // Mostrar loading
    const aiModal = bootstrap.Modal.getInstance(document.getElementById('aiGeneratorModal'));
    aiModal.hide();
    
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    loadingModal.show();
    
    // Fazer requisição
    fetch('{{ route("admin.contracts.templates.generate-ai") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            objective: objective,
            contract_type: contractType,
            additional_instructions: formData.get('additional_instructions'),
            reference_content: uploadedFileContent
        })
    })
    .then(response => {
        // Tratar diferentes tipos de erro HTTP
        if (response.status === 419) {
            throw new Error('Token de segurança expirado. Por favor, recarregue a página e tente novamente.');
        }
        if (response.status === 422) {
            // Tentar extrair a mensagem de erro do JSON
            return response.json().then(errorData => {
                console.log('Erro 422 detalhado:', errorData);
                let errorMessage = 'Dados inválidos enviados';
                
                if (errorData.message) {
                    errorMessage = errorData.message;
                } else if (errorData.errors) {
                    // Extrair primeira mensagem de erro
                    const firstError = Object.values(errorData.errors)[0];
                    if (Array.isArray(firstError)) {
                        errorMessage = firstError[0];
                    } else {
                        errorMessage = firstError;
                    }
                }
                
                throw new Error(errorMessage);
            }).catch((parseError) => {
                console.error('Erro ao processar resposta 422:', parseError);
                throw new Error('Dados inválidos. Verifique se todos os campos obrigatórios estão preenchidos.');
            });
        }
        if (!response.ok) {
            throw new Error(`Erro ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        loadingModal.hide();
        
        if (data.success) {
            // Redirecionar para a página de criação com os dados preenchidos
            const template = data.template;
            const params = new URLSearchParams({
                ai_generated: '1',
                name: template.name,
                description: template.description,
                content: template.content,
                validity_days: template.validity_days,
                is_active: template.is_active ? '1' : '0',
                is_default: template.is_default ? '1' : '0'
            });
            
            window.location.href = '{{ route("admin.contracts.templates.create") }}?' + params.toString();
        } else {
            // Verificar se é erro de configuração
            if (data.message && data.message.includes('ChatGPT não está configurado')) {
                if (confirm('⚠️ ChatGPT não está configurado.\n\nDeseja ir para as configurações agora?')) {
                    window.location.href = '{{ route("admin.settings.index") }}#chatgpt-tab';
                }
            } else {
                alert('Erro ao gerar template: ' + (data.message || 'Erro desconhecido'));
            }
        }
    })
    .catch(error => {
        loadingModal.hide();
        console.error('Erro completo:', error);
        
        // Mensagens de erro mais específicas e amigáveis
        if (error.message.includes('Token de segurança expirado')) {
            alert('🔄 ' + error.message);
            // Opcional: recarregar a página automaticamente após 3 segundos
            setTimeout(() => {
                if (confirm('Deseja recarregar a página agora?')) {
                    window.location.reload();
                }
            }, 2000);
        } else if (error.message.includes('Dados inválidos')) {
            alert('⚠️ ' + error.message);
        } else if (error.message.includes('Erro 500')) {
            alert('🔧 Erro interno do servidor. Tente novamente em alguns minutos.');
        } else {
            alert('❌ Erro de conexão: ' + error.message);
        }
    });
}

function setDefault(templateId) {
    if (!confirm('Tem certeza que deseja definir este template como padrão?')) {
        return;
    }
    
    fetch(`/admin/contracts/templates/${templateId}/set-default`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Template definido como padrão com sucesso!');
            window.location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao definir template como padrão.');
    });
}

function toggleActive(templateId) {
    fetch(`/admin/contracts/templates/${templateId}/toggle-active`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status do template atualizado com sucesso!');
            window.location.reload();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao alterar status do template.');
    });
}

function deleteTemplate(templateId) {
    if (!confirm('Tem certeza que deseja excluir este template? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/contracts/templates/${templateId}`;
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    
    const tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = '_token';
    tokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    form.appendChild(methodInput);
    form.appendChild(tokenInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection 