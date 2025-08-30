@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Configurações do ChatGPT</h1>
    
    <div class="card mb-4">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                
                <!-- Abas de configuração -->
                <ul class="nav nav-tabs mb-4" id="aiSettingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab" aria-controls="basic" aria-selected="true">
                            <i class="fas fa-cog me-2"></i>Básico
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="prompts-tab" data-bs-toggle="tab" data-bs-target="#prompts" type="button" role="tab" aria-controls="prompts" aria-selected="false">
                            <i class="fas fa-comments me-2"></i>Prompts
                        </button>
                    </li>
                </ul>
                
                <!-- Conteúdo das abas -->
                <div class="tab-content" id="aiSettingsTabContent">
                    <!-- Aba Básico -->
                    <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                        <div class="mb-3">
                            <label for="api_key" class="form-label">API Key <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('ai_settings.api_key') is-invalid @error" 
                                   id="api_key" name="ai_settings[api_key]" value="{{ old('ai_settings.api_key', $settings->api_key) }}" required>
                            <div class="form-text">Sua chave de API do OpenAI. Obtenha em: <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com/api-keys</a></div>
                            @error('ai_settings.api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="model" class="form-label">Modelo <span class="text-danger">*</span></label>
                            <select class="form-select @error('ai_settings.model') is-invalid @enderror" id="model" name="ai_settings[model]" required>
                                <optgroup label="Modelos Mini (Mais Rápidos e Econômicos)">
                                    <option value="gpt-4o-mini" {{ ($settings->model ?? 'gpt-4o-mini') == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini (Recomendado)</option>
                                    <option value="o1-mini" {{ ($settings->model ?? 'gpt-4o-mini') == 'o1-mini' ? 'selected' : '' }}>o1 Mini</option>
                                    <option value="o3-mini" {{ ($settings->model ?? 'gpt-4o-mini') == 'o3-mini' ? 'selected' : '' }}>o3 Mini</option>
                                    <option value="gpt-3.5-turbo" {{ ($settings->model ?? 'gpt-4o-mini') == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                                </optgroup>
                                <optgroup label="Modelos Padrão">
                                    <option value="gpt-4o" {{ ($settings->model ?? 'gpt-4o-mini') == 'gpt-4o' ? 'selected' : '' }}>GPT-4o</option>
                                    <option value="gpt-4-turbo" {{ ($settings->model ?? 'gpt-4o-mini') == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>
                                    <option value="gpt-4" {{ ($settings->model ?? 'gpt-4o-mini') == 'gpt-4' ? 'selected' : '' }}>GPT-4</option>
                                </optgroup>
                                <optgroup label="Modelos de Raciocínio">
                                    <option value="o1-preview" {{ ($settings->model ?? 'gpt-4o-mini') == 'o1-preview' ? 'selected' : '' }}>o1 Preview</option>
                                    <option value="o1" {{ ($settings->model ?? 'gpt-4o-mini') == 'o1' ? 'selected' : '' }}>o1</option>
                                    <option value="o3" {{ ($settings->model ?? 'gpt-4o-mini') == 'o3' ? 'selected' : '' }}>o3</option>
                                </optgroup>
                            </select>
                            <div class="form-text">Escolha o modelo do ChatGPT a ser utilizado. Modelos mini são mais rápidos e econômicos.</div>
                            @error('ai_settings.model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="system_prompt" class="form-label">Prompt do Sistema</label>
                            <textarea class="form-control @error('ai_settings.system_prompt') is-invalid @enderror" 
                                      id="system_prompt" name="ai_settings[system_prompt]" rows="4">{{ old('ai_settings.system_prompt', $settings->system_prompt) }}</textarea>
                            <div class="form-text">Defina a personalidade ou comportamento do assistente ao criar templates.</div>
                            @error('ai_settings.system_prompt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="ai_settings[is_active]" 
                                       value="1" {{ $settings->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Ativar ChatGPT</label>
                            </div>
                            <div class="form-text">Ative para permitir a geração de templates com IA.</div>
                        </div>
                    </div>
                    
                    <!-- Aba Prompts -->
                    <div class="tab-pane fade" id="prompts" role="tabpanel" aria-labelledby="prompts-tab">
                        <div class="mb-3">
                            <label for="email_template_prompt" class="form-label">Prompt para Templates de Email</label>
                            <textarea class="form-control" id="email_template_prompt" name="ai_settings[email_template_prompt]" rows="6">{{ old('ai_settings.email_template_prompt', $settings->email_template_prompt ?? '') }}</textarea>
                            <div class="form-text">Instruções específicas para geração de templates de email marketing.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="whatsapp_template_prompt" class="form-label">Prompt para Templates de WhatsApp</label>
                            <textarea class="form-control" id="whatsapp_template_prompt" name="ai_settings[whatsapp_template_prompt]" rows="6">{{ old('ai_settings.whatsapp_template_prompt', $settings->whatsapp_template_prompt ?? '') }}</textarea>
                            <div class="form-text">Instruções específicas para geração de mensagens de WhatsApp.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contract_template_prompt" class="form-label">Prompt para Templates de Contratos</label>
                            <textarea class="form-control" id="contract_template_prompt" name="ai_settings[contract_template_prompt]" rows="6">{{ old('ai_settings.contract_template_prompt', $settings->contract_template_prompt ?? '') }}</textarea>
                            <div class="form-text">Instruções específicas para geração de contratos educacionais.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_template_prompt" class="form-label">Prompt para Templates de Pagamento</label>
                            <textarea class="form-control" id="payment_template_prompt" name="ai_settings[payment_template_prompt]" rows="6">{{ old('ai_settings.payment_template_prompt', $settings->payment_template_prompt ?? '') }}</textarea>
                            <div class="form-text">Instruções específicas para geração de documentos de pagamento.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="enrollment_template_prompt" class="form-label">Prompt para Templates de Inscrição</label>
                            <textarea class="form-control" id="enrollment_template_prompt" name="ai_settings[enrollment_template_prompt]" rows="6">{{ old('ai_settings.enrollment_template_prompt', $settings->enrollment_template_prompt ?? '') }}</textarea>
                            <div class="form-text">Instruções específicas para geração de documentos de inscrição.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="matriculation_template_prompt" class="form-label">Prompt para Templates de Matrícula</label>
                            <textarea class="form-control" id="matriculation_template_prompt" name="ai_settings[matriculation_template_prompt]" rows="6">{{ old('ai_settings.matriculation_template_prompt', $settings->matriculation_template_prompt ?? '') }}</textarea>
                            <div class="form-text">Instruções específicas para geração de documentos de matrícula.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="support_prompt" class="form-label">Prompt para Suporte ao Cliente</label>
                            <textarea class="form-control" id="support_prompt" name="ai_settings[support_prompt]" rows="6">{{ old('ai_settings.support_prompt', $settings->support_prompt ?? '') }}</textarea>
                            <div class="form-text">Instruções específicas para o comportamento do assistente de suporte.</div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Dica:</strong> Os prompts podem incluir variáveis como {objective}, {templateType}, {targetAudience}, etc. Use essas variáveis para personalizar a geração de conteúdo.
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Salvar Configurações
                    </button>
                    
                    <button type="button" class="btn btn-outline-secondary" id="testConnection">
                        <i class="fas fa-plug me-2"></i>
                        Testar Conexão
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const testButton = document.getElementById('testConnection');
    
    testButton.addEventListener('click', async function() {
        testButton.disabled = true;
        testButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testando...';
        
        try {
            const response = await fetch('{{ route("admin.settings.ai.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    api_key: document.getElementById('api_key').value,
                    model: document.getElementById('model').value
                })
            });
            const data = await response.json();
            
            if (data.success) {
                alert('Sucesso: ' + data.message);
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            alert('Erro ao testar conexão: ' + error.message);
        } finally {
            testButton.disabled = false;
            testButton.innerHTML = '<i class="fas fa-plug me-2"></i>Testar Conexão';
        }
    });
});
</script>
@endpush 