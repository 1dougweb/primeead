@extends('layouts.admin')

@section('title', 'Templates do WhatsApp')

@section('page-actions')
<div class="d-flex gap-2">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#aiGenerateModal">
        <i class="fas fa-robot"></i> Gerar com IA
    </button>
    <a href="{{ route('admin.whatsapp.templates.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Template
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($templates as $template)
                                    <tr>
                                        <td>{{ $template->name }}</td>
                                        <td>{{ $template->description }}</td>
                                        <td>{{ $template->category }}</td>
                                        <td>
                                            <span class="badge {{ $template->active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $template->active ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.whatsapp.templates.edit', $template) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-info" 
                                                        onclick="testTemplate({{ $template->id }})">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                                <form action="{{ route('admin.whatsapp.templates.destroy', $template) }}" 
                                                      method="POST" 
                                                      class="d-inline" 
                                                      onsubmit="return confirm('Tem certeza que deseja excluir este template?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
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
</div>

<!-- Modal de Teste -->
<div class="modal fade" id="testModal" tabindex="-1" aria-labelledby="testModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testModalLabel">Testar Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="test_phone" class="form-label">Telefone para teste</label>
                    <input type="text" class="form-control" id="test_phone" name="test_phone">
                </div>
                <div id="test_variables">
                    <!-- Campos de variáveis serão adicionados aqui dinamicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="sendTestBtn">Enviar Teste</button>
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
                        Descreva o objetivo da mensagem e o ChatGPT criará um template personalizado para WhatsApp.
                    </div>

                    <div class="mb-3">
                        <label for="ai_name" class="form-label">Nome do Template <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ai_name" name="name" 
                               placeholder="Ex: confirmacao_inscricao">
                        <div class="form-text">Nome único para identificar o template</div>
                    </div>

                    <div class="mb-3">
                        <label for="ai_description" class="form-label">Descrição <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ai_description" name="description" 
                               placeholder="Ex: Confirmação de inscrição no curso">
                        <div class="form-text">Breve descrição do propósito do template</div>
                    </div>

                    <div class="mb-3">
                        <label for="ai_category" class="form-label">Categoria</label>
                        <select class="form-select" id="ai_category" name="category">
                            <option value="inscricao">Inscrição</option>
                            <option value="matricula">Matrícula</option>
                            <option value="lembrete">Lembrete</option>
                            <option value="geral">Geral</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="ai_objective" class="form-label">Objetivo da Mensagem <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="ai_objective" name="objective" rows="3" 
                                  placeholder="Ex: Confirmar inscrição no curso de ensino médio e orientar próximos passos"></textarea>
                        <div class="form-text">Descreva claramente o que você deseja comunicar na mensagem</div>
                    </div>

                    <div class="mb-3">
                        <label for="ai_target_audience" class="form-label">Público-Alvo</label>
                        <select class="form-select" id="ai_target_audience" name="target_audience">
                            <option value="estudantes">Estudantes</option>
                            <option value="pais">Pais/Responsáveis</option>
                            <option value="jovens_adultos">Jovens e Adultos</option>
                            <option value="profissionais">Profissionais</option>
                            <option value="geral">Público Geral</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="ai_tone" class="form-label">Tom da Mensagem</label>
                        <select class="form-select" id="ai_tone" name="tone">
                            <option value="formal">Formal</option>
                            <option value="amigavel">Amigável</option>
                            <option value="motivacional">Motivacional</option>
                            <option value="urgente">Urgente</option>
                            <option value="informativo">Informativo</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="ai_additional_instructions" class="form-label">Instruções Adicionais</label>
                        <textarea class="form-control" id="ai_additional_instructions" name="additional_instructions" rows="2" 
                                  placeholder="Ex: Incluir emojis, mencionar desconto, prazo limite, etc."></textarea>
                        <div class="form-text">Qualquer instrução específica para personalizar a mensagem</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="generateAndCreateTemplate()" id="generateBtn">
                    <i class="fas fa-robot me-2"></i>
                    Gerar e Criar Template
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentTemplateId = null;
    let currentTemplateVariables = null;
    let testModal = null;

    document.addEventListener('DOMContentLoaded', function() {
        testModal = new bootstrap.Modal(document.getElementById('testModal'));
    });

    function testTemplate(templateId) {
        currentTemplateId = templateId;
        
        // Buscar informações do template
        fetch(`/admin/whatsapp/templates/${templateId}`)
            .then(response => response.json())
            .then(data => {
                currentTemplateVariables = data.variables;
                
                // Limpar campos anteriores
                document.getElementById('test_variables').innerHTML = '';
                
                // Adicionar campos para cada variável
                data.variables.forEach(variable => {
                    const div = document.createElement('div');
                    div.className = 'mb-3';
                    div.innerHTML = `
                        <label for="var_${variable}" class="form-label">${variable}</label>
                        <input type="text" class="form-control" id="var_${variable}" name="var_${variable}">
                    `;
                    document.getElementById('test_variables').appendChild(div);
                });
                
                // Mostrar modal
                testModal.show();
            });
    }

    document.getElementById('sendTestBtn').addEventListener('click', function() {
        const phone = document.getElementById('test_phone').value;
        const testData = {};
        
        // Coletar valores das variáveis
        currentTemplateVariables.forEach(variable => {
            testData[variable] = document.getElementById(`var_${variable}`).value;
        });

        // Enviar requisição de teste
        fetch(`/admin/whatsapp/templates/${currentTemplateId}/test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                test_phone: phone,
                test_data: testData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Mensagem de teste enviada com sucesso!');
                testModal.hide();
            } else {
                alert('Erro ao enviar mensagem de teste: ' + data.message);
            }
        })
        .catch(error => {
            alert('Erro ao enviar mensagem de teste: ' + error.message);
        });
    });

    function generateAndCreateTemplate() {
        const name = document.getElementById('ai_name').value;
        const description = document.getElementById('ai_description').value;
        const category = document.getElementById('ai_category').value;
        const objective = document.getElementById('ai_objective').value;
        const targetAudience = document.getElementById('ai_target_audience').value;
        const tone = document.getElementById('ai_tone').value;
        const additionalInstructions = document.getElementById('ai_additional_instructions').value;

        if (!name.trim() || !description.trim() || !objective.trim()) {
            alert('Por favor, preencha todos os campos obrigatórios.');
            return;
        }

        const generateBtn = document.getElementById('generateBtn');
        const originalText = generateBtn.innerHTML;
        
        // Mostrar loading
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Gerando...';
        generateBtn.disabled = true;

        // Primeiro, gerar o conteúdo com IA
        fetch('{{ route("admin.whatsapp.templates.generate-ai") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                objective: objective,
                target_audience: targetAudience,
                tone: tone,
                additional_instructions: additionalInstructions
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Criar o template com o conteúdo gerado
                return fetch('{{ route("admin.whatsapp.templates.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: name,
                        description: description,
                        category: category,
                        content: data.content,
                        active: true
                    })
                });
            } else {
                throw new Error(data.message || 'Erro ao gerar template.');
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
                throw new Error('Erro ao criar template');
            }
        })
        .then(data => {
            if (data.success || data.id) {
                // Fechar o modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('aiGenerateModal'));
                modal.hide();
                
                // Mostrar mensagem de sucesso
                showAlert('success', 'Template criado com sucesso!');
                
                // Limpar o formulário do modal
                document.getElementById('aiGenerateForm').reset();
                
                // Recarregar a página para mostrar o novo template
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                throw new Error('Erro ao criar template.');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('error', error.message || 'Erro ao gerar template. Tente novamente.');
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
        
        // Inserir o alerta no topo da página
        const container = document.querySelector('.container-fluid');
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Remover o alerta após 5 segundos
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
</script>
@endpush
@endsection 