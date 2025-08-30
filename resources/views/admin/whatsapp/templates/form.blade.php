<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ isset($template) ? route('admin.whatsapp.templates.update', $template) : route('admin.whatsapp.templates.store') }}">
                        @csrf
                        @if(isset($template))
                            @method('PUT')
                        @endif

                        <!-- Nome -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" 
                                   value="{{ old('name', $template->name ?? '') }}" 
                                   required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Nome único para identificar o template (ex: inscricao_confirmacao)</div>
                        </div>

                        <!-- Descrição -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                   id="description" name="description" 
                                   value="{{ old('description', $template->description ?? '') }}" 
                                   required>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Breve descrição do propósito do template</div>
                        </div>

                        <!-- Categoria -->
                        <div class="mb-3">
                            <label for="category" class="form-label">Categoria</label>
                            <select class="form-select @error('category') is-invalid @enderror" 
                                    id="category" name="category">
                                <option value="inscricao" {{ (old('category', $template->category ?? '') == 'inscricao') ? 'selected' : '' }}>Inscrição</option>
                                <option value="matricula" {{ (old('category', $template->category ?? '') == 'matricula') ? 'selected' : '' }}>Matrícula</option>
                                <option value="lembrete" {{ (old('category', $template->category ?? '') == 'lembrete') ? 'selected' : '' }}>Lembrete</option>
                                <option value="geral" {{ (old('category', $template->category ?? '') == 'geral') ? 'selected' : '' }}>Geral</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Variáveis -->
                        <div class="mb-3">
                            <label class="form-label">Variáveis</label>
                            <div id="variables-container">
                                @if(isset($template) && $template->variables)
                                    @foreach($template->variables as $variable)
                                        <div class="input-group mb-2">
                                            <input type="text" name="variables[]" class="form-control" value="{{ $variable }}">
                                            <button type="button" class="btn btn-danger" onclick="removeVariable(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-secondary" onclick="addVariable()">
                                <i class="fas fa-plus"></i> Adicionar Variável
                            </button>
                            <div class="form-text">Variáveis disponíveis no template (ex: nome, curso, data)</div>
                        </div>

                        <!-- Conteúdo -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="content" class="form-label mb-0">Conteúdo</label>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#aiGenerateModal">
                                    <i class="fas fa-robot me-1"></i>
                                    Gerar com IA
                                </button>
                            </div>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="10" 
                                      required>{{ old('content', $template->content ?? '') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Use @{{ variavel }} para inserir variáveis dinâmicas.<br>
                                Exemplo: "Olá *@{{ nome }}*! Seu curso de @{{ curso }} está confirmado."
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" 
                                       id="active" name="active" value="1" 
                                       {{ old('active', $template->active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">Template Ativo</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.whatsapp.templates.index') }}" class="btn btn-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ isset($template) ? 'Atualizar' : 'Criar' }}
                            </button>
                        </div>
                    </form>
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
                        Descreva o objetivo da mensagem e o ChatGPT criará um template personalizado para WhatsApp.
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
                <button type="button" class="btn btn-success" onclick="generateWithAI()" id="generateBtn">
                    <i class="fas fa-robot me-2"></i>
                    Gerar Template
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function addVariable() {
        const container = document.getElementById('variables-container');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="text" name="variables[]" class="form-control">
            <button type="button" class="btn btn-danger" onclick="removeVariable(this)">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(div);
    }

    function removeVariable(button) {
        button.closest('.input-group').remove();
    }

    function generateWithAI() {
        const objective = document.getElementById('ai_objective').value;
        const targetAudience = document.getElementById('ai_target_audience').value;
        const tone = document.getElementById('ai_tone').value;
        const additionalInstructions = document.getElementById('ai_additional_instructions').value;

        if (!objective.trim()) {
            alert('Por favor, descreva o objetivo da mensagem.');
            return;
        }

        const generateBtn = document.getElementById('generateBtn');
        const originalText = generateBtn.innerHTML;
        
        // Mostrar loading
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Gerando...';
        generateBtn.disabled = true;

        // Fazer requisição AJAX
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
                // Preencher o textarea com o conteúdo gerado
                document.getElementById('content').value = data.content;
                
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