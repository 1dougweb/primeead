@extends('layouts.admin')

@section('title', 'Criar Template de Contrato')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>
                        Criar Novo Template
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.contracts.templates.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Voltar
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(request('ai_generated'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-robot me-2"></i>
                            <strong>Template gerado com IA!</strong> O conteúdo foi criado automaticamente. Revise e ajuste conforme necessário antes de salvar.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.contracts.templates.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Informações Básicas -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Informações Básicas
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome do Template *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', request('name')) }}" required>
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="validity_days" class="form-label">Dias de Validade *</label>
                                                                                        <input type="number" class="form-control @error('validity_days') is-invalid @enderror" 
                                           id="validity_days" name="validity_days" value="{{ old('validity_days', request('validity_days', 30)) }}" 
                                           min="1" max="365" required>
                                                    @error('validity_days')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Descrição</label>
                                                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', request('description')) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', request('is_active', true)) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_active">
                                                        Template Ativo
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                                                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" 
                                           {{ old('is_default', request('is_default')) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_default">
                                                        Template Padrão
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Conteúdo do Template -->
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-file-alt me-2"></i>
                                            Conteúdo do Template *
                                        </h5>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#aiGenerateModal">
                                                <i class="fas fa-robot me-1"></i>
                                                Gerar com IA
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="showPreview()">
                                                <i class="fas fa-eye me-1"></i>
                                                Preview
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="showVariablesHelp()">
                                                <i class="fas fa-question-circle me-1"></i>
                                                Variáveis Disponíveis
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="content" class="form-label">Conteúdo HTML</label>
                                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                                      id="content" name="content" rows="20" required>{{ old('content', request('content')) }}</textarea>
                                            @error('content')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                Use as variáveis no formato <code>@{{variable_name}}</code> para inserir dados dinâmicos.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- Variáveis Disponíveis -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-list me-2"></i>
                                            Variáveis Disponíveis
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" id="variableSearch" 
                                                   placeholder="Buscar variável..." onkeyup="filterVariables()">
                                        </div>
                                        
                                        <div id="variablesList" style="max-height: 400px; overflow-y: auto;">
                                            @foreach($systemVariables as $key => $description)
                                                <div class="variable-item mb-2 p-2 border rounded" data-variable="{{ $key }}">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <code class="text-primary">{{ $key }}</code>
                                                            <br>
                                                            <small class="text-muted">{{ $description }}</small>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                onclick="insertVariable('{{ $key }}')">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.contracts.templates.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Criar Template
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Ajuda -->
<div class="modal fade" id="variablesHelpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Variáveis Disponíveis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Use as seguintes variáveis no seu template para inserir dados dinâmicos:</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Dados do Aluno</h6>
                        <ul class="list-unstyled">
                            <li><code>student_name</code> - Nome completo</li>
                            <li><code>student_email</code> - Email</li>
                            <li><code>student_cpf</code> - CPF formatado</li>
                            <li><code>student_rg</code> - RG</li>
                            <li><code>student_phone</code> - Telefone</li>
                            <li><code>student_address</code> - Endereço completo</li>
                            <li><code>student_birth_date</code> - Data de nascimento</li>
                            <li><code>student_nationality</code> - Nacionalidade</li>
                            <li><code>student_civil_status</code> - Estado civil</li>
                            <li><code>student_mother_name</code> - Nome da mãe</li>
                            <li><code>student_father_name</code> - Nome do pai</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Dados do Curso</h6>
                        <ul class="list-unstyled">
                            <li><code>course_name</code> - Nome do curso</li>
                            <li><code>course_modality</code> - Modalidade</li>
                            <li><code>course_shift</code> - Turno</li>
                            <li><code>tuition_value</code> - Valor da mensalidade</li>
                            <li><code>enrollment_value</code> - Valor da matrícula</li>
                            <li><code>enrollment_number</code> - Número da matrícula</li>
                            <li><code>enrollment_date</code> - Data da matrícula</li>
                            <li><code>due_date</code> - Dia de vencimento</li>
                            <li><code>payment_method</code> - Forma de pagamento</li>
                        </ul>
                        
                        <h6>Dados do Sistema</h6>
                        <ul class="list-unstyled">
                            <li><code>school_name</code> - Nome da escola</li>
                            <li><code>current_date</code> - Data atual</li>
                            <li><code>current_year</code> - Ano atual</li>
                            <li><code>contract_date</code> - Data do contrato</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Gerar Template com IA -->
<div class="modal fade" id="aiGenerateModal" tabindex="-1" aria-labelledby="aiGenerateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aiGenerateModalLabel">
                    <i class="fas fa-robot me-2"></i>
                    Gerar Template com IA
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="aiGenerateForm">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Descreva o tipo de contrato que você precisa e o ChatGPT criará um template profissional e juridicamente adequado.
                    </div>

                    <div class="mb-3">
                        <label for="ai_objective" class="form-label">Objetivo do Contrato <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="ai_objective" name="objective" rows="3" 
                                  maxlength="500"
                                  placeholder="Ex: Contrato para matrícula em curso EJA com pagamento parcelado e certificação reconhecida pelo MEC..." 
                                  required></textarea>
                        <div class="form-text">Descreva detalhadamente o que o contrato deve cobrir (máx. 500 caracteres)</div>
                    </div>

                    <div class="mb-3">
                        <label for="ai_contract_type" class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                        <select class="form-select" id="ai_contract_type" name="contract_type" required>
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
                        <label for="ai_reference_file" class="form-label">Arquivo de Referência (Opcional)</label>
                        <input type="file" class="form-control" id="ai_reference_file" name="reference_file" 
                               accept=".docx,.doc,.pdf,.txt">
                        <div class="form-text">Envie um arquivo DOCX, DOC, PDF ou TXT para que a IA se baseie no seu formato e conteúdo</div>
                    </div>

                    <div class="mb-3">
                        <label for="ai_additional_instructions" class="form-label">Instruções Adicionais</label>
                        <textarea class="form-control" id="ai_additional_instructions" name="additional_instructions" rows="2" 
                                  maxlength="1000"
                                  placeholder="Ex: Incluir cláusulas específicas sobre aulas online, material didático, política de faltas..."></textarea>
                        <div class="form-text">Especificações extras que devem ser incluídas no contrato (opcional)</div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Importante:</strong> O template gerado deve ser revisado por um profissional jurídico antes do uso.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="generateWithAI()" id="generateBtn">
                    <i class="fas fa-robot me-2"></i>
                    Gerar Template
                </button>
            </div>
        </div>
    </div>
</div>


<script>
function insertVariable(variable) {
    const textarea = document.getElementById('content');
    const variableText = '{{' + variable + '}}';
    
    // Inserir no cursor atual
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    textarea.value = text.substring(0, start) + variableText + text.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + variableText.length, start + variableText.length);
}

function filterVariables() {
    const search = document.getElementById('variableSearch').value.toLowerCase();
    const items = document.querySelectorAll('.variable-item');
    
    items.forEach(item => {
        const variable = item.getAttribute('data-variable').toLowerCase();
        const description = item.querySelector('small').textContent.toLowerCase();
        
        if (variable.includes(search) || description.includes(search)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function showVariablesHelp() {
    const modal = new bootstrap.Modal(document.getElementById('variablesHelpModal'));
    modal.show();
}

function generateWithAI() {
    const objective = document.getElementById('ai_objective').value;
    const contractType = document.getElementById('ai_contract_type').value;
    const additionalInstructions = document.getElementById('ai_additional_instructions').value;
    const referenceFile = document.getElementById('ai_reference_file').files[0];

    if (!objective.trim() || !contractType) {
        alert('Por favor, preencha todos os campos obrigatórios.');
        return;
    }

    const generateBtn = document.getElementById('generateBtn');
    const originalText = generateBtn.innerHTML;
    
    // Mostrar loading
    generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Gerando...';
    generateBtn.disabled = true;

    // Preparar dados para envio
    const formData = new FormData();
    formData.append('objective', objective);
    formData.append('contract_type', contractType);
    formData.append('additional_instructions', additionalInstructions);
    
    if (referenceFile) {
        formData.append('reference_file', referenceFile);
    }

    // Fazer requisição
    fetch('{{ route("admin.contracts.templates.generate-ai") }}', {
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
            // Preencher o textarea com o conteúdo gerado
            document.getElementById('content').value = data.template.content;
            
            // Preencher outros campos se disponíveis
            if (data.template.name) {
                document.getElementById('name').value = data.template.name;
            }
            if (data.template.description) {
                document.getElementById('description').value = data.template.description;
            }
            
            // Fechar o modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('aiGenerateModal'));
            modal.hide();
            
            // Mostrar mensagem de sucesso
            showAlert('success', data.message || 'Template gerado com sucesso!');
            
            // Limpar o formulário do modal
            document.getElementById('aiGenerateForm').reset();
        } else {
            showAlert('error', data.message || 'Erro ao gerar template.');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showAlert('error', 'Erro ao gerar template. Tente novamente.');
    })
    .finally(() => {
        // Restaurar botão
        generateBtn.innerHTML = originalText;
        generateBtn.disabled = false;
    });
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Inserir no topo da página
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-remover após 5 segundos
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

function showPreview() {
    const content = document.getElementById('content').value;
    
    if (!content.trim()) {
        alert('Nenhum conteúdo encontrado para gerar o preview.\nDigite algum conteúdo no campo "Conteúdo HTML" primeiro.');
        return;
    }
    
    // Dados de exemplo para preview
    const sampleData = {
        'student_name': 'João da Silva Santos',
        'student_email': 'joao.silva@exemplo.com',
        'student_cpf': '123.456.789-00',
        'student_rg': '12.345.678-9',
        'student_phone': '(11) 99999-9999',
        'student_address': 'Rua das Flores, 123, Centro, São Paulo - SP, CEP: 01234-567',
        'student_birth_date': '01/01/1990',
        'student_nationality': 'Brasileira',
        'student_civil_status': 'Solteiro(a)',
        'student_mother_name': 'Maria da Silva',
        'student_father_name': 'José Santos',
        'student_profession': 'Vendedor',
        'course_name': 'Ensino Médio EJA',
        'course_modality': 'Presencial',
        'course_shift': 'Noturno',
        'course_duration': '18 meses',
        'course_workload': '1.200 horas',
        'tuition_value': 'R$ 250,00',
        'enrollment_value': 'R$ 100,00',
        'enrollment_number': '2024001',
        'enrollment_date': '15/01/2024',
        'due_date': '10',
        'payment_method': 'Boleto Bancário',
        'school_name': 'EJA Supletivo - Centro de Educação',
        'school_address': 'Av. Educação, 456, Centro, São Paulo - SP',
        'school_cnpj': '12.345.678/0001-90',
        'school_phone': '(11) 3333-4444',
        'school_email': 'contato@ejasupletivo.com.br',
        'director_name': 'Dr. Roberto Silva',
        'current_date': new Date().toLocaleDateString('pt-BR'),
        'current_year': new Date().getFullYear(),
        'contract_date': new Date().toLocaleDateString('pt-BR'),
        'partner_school_name': 'Escola Parceira ABC',
        'is_partner_student': 'Sim',
        'witness1_name': 'Ana Costa',
        'witness1_cpf': '987.654.321-00',
        'witness2_name': 'Carlos Oliveira',
        'witness2_cpf': '456.789.123-00'
    };
    
    // Substituir variáveis
    let previewContent = content;
    Object.keys(sampleData).forEach(key => {
        const regex = new RegExp('{{' + key + '}}', 'g');
        previewContent = previewContent.replace(regex, sampleData[key]);
    });
    
    // Criar HTML completo para a nova aba
    const fullHtml = `
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Preview do Template - ${document.getElementById('name').value || 'Novo Template'}</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style>
                body {
                    background-color: #f8f9fa;
                    font-family: 'Times New Roman', serif;
                    padding: 20px;
                }
                .preview-container {
                    max-width: 800px;
                    margin: 0 auto;
                    background: white;
                    padding: 40px;
                    border-radius: 8px;
                    box-shadow: 0 0 20px rgba(0,0,0,0.1);
                    line-height: 1.6;
                    color: #333;
                    font-size: 14px;
                }
                .preview-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 20px;
                    border-radius: 8px 8px 0 0;
                    margin: -40px -40px 30px -40px;
                    text-align: center;
                }
                .preview-footer {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #dee2e6;
                    text-align: center;
                    color: #6c757d;
                    font-size: 12px;
                }
                .print-button {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 1000;
                }
                @media print {
                    body {
                        background: white;
                        padding: 0;
                    }
                    .preview-header {
                        display: none;
                    }
                    .print-button {
                        display: none;
                    }
                    .preview-footer {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <button class="btn btn-primary print-button" onclick="window.print()">
                <i class="fas fa-print me-2"></i>
                Imprimir
            </button>
            
            <div class="preview-container">
                <div class="preview-header">
                    <h4><i class="fas fa-eye me-2"></i>Preview do Template</h4>
                    <p class="mb-0">Visualização com dados de exemplo</p>
                </div>
                
                <div class="preview-content">
                    ${previewContent}
                </div>
                
                <div class="preview-footer">
                    <p class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Este é um preview gerado automaticamente com dados de exemplo para visualização.
                    </p>
                </div>
            </div>
        </body>
        </html>
    `;
    
    // Abrir em nova aba
    const newWindow = window.open('', '_blank');
    newWindow.document.write(fullHtml);
    newWindow.document.close();
}
</script>
@endsection 