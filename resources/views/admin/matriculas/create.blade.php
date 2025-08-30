@extends('layouts.admin')

@section('title', 'Nova Matrícula')

@section('content') 
<style>
    /* Estilos para validação AJAX */
    .invalid-feedback {
        display: block !important;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    
    /* Estilos para toasts personalizados */
    .custom-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        border: none;
    }
    
    /* Barra de progresso melhorada */
    .profile-progress-container .progress {
        height: 10px;
        border-radius: 5px;
        background-color: #e9ecef;
    }
    
    .profile-progress-container .progress-bar {
        border-radius: 5px;
        transition: width 0.6s ease;
    }
    
    /* Botão de submit com loading */
    .btn-loading {
        display: none;
    }
    
    .btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
</style>

<div class="container-fluid px-4">
    <h3 class="mt-4">
        <i class="fas fa-user-graduate me-2"></i>
        Nova Matrícula
    </h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.matriculas.index') }}">Matrículas</a></li>
        <li class="breadcrumb-item active">Nova Matrícula</li>
    </ol>
    
    <!-- Resumo de Erros (inicialmente oculto) -->
    <div id="errorSummary" class="alert alert-danger d-none mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div class="flex-grow-1">
                <h6 class="mb-1">Erros de Validação</h6>
                <div id="errorList" class="small"></div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearValidationErrors()">
                    <i class="fas fa-times me-1"></i>Limpar Erros
                </button>
                <button type="button" class="btn-close" onclick="$('#errorSummary').addClass('d-none')"></button>
            </div>
        </div>
    </div>

    <!-- Barra de Progresso do Perfil -->
    <div class="profile-progress-container mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-plus-circle text-primary fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    Nova Matrícula
                                    <span class="badge bg-primary">Em Criação</span>
                                </h6>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">
                                    Preencha os campos obrigatórios para criar a matrícula
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Campos flexíveis
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="matriculaForm" method="POST" action="{{ route('admin.matriculas.store') }}" enctype="multipart/form-data">
        @csrf

        @if($inscricao)
            <input type="hidden" name="inscricao_id" value="{{ $inscricao->id }}">
        @endif
        <input type="hidden" name="google_drive_folder_id" id="google_drive_folder_id">

        <div class="row">
            <!-- Dados Pessoais -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            Dados Pessoais
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nome_completo" class="form-label">Nome Completo</label>
                                <input type="text" 
                                       class="form-control @error('nome_completo') is-invalid @enderror" 
                                       id="nome_completo" 
                                       name="nome_completo" 
                                       value="{{ old('nome_completo', $inscricao->nome ?? '') }}">
                                @error('nome_completo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                <input type="date" 
                                       class="form-control @error('data_nascimento') is-invalid @enderror" 
                                       id="data_nascimento" 
                                       name="data_nascimento" 
                                       value="{{ old('data_nascimento') }}">
                                @error('data_nascimento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="sexo" class="form-label">Sexo</label>
                                <select class="form-select @error('sexo') is-invalid @enderror" 
                                        id="sexo" 
                                        name="sexo">
                                    <option value="">Selecione</option>
                                    <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Feminino</option>
                                    <option value="O" {{ old('sexo') == 'O' ? 'selected' : '' }}>Outro</option>
                                </select>
                                @error('sexo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="cpf" class="form-label">CPF</label>
                                <input type="text" 
                                       class="form-control @error('cpf') is-invalid @enderror" 
                                       id="cpf" 
                                       name="cpf" 
                                       value="{{ old('cpf') }}" 
                                       placeholder="000.000.000-00">
                                @error('cpf')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="rg" class="form-label">RG</label>
                                <input type="text" 
                                       class="form-control @error('rg') is-invalid @enderror" 
                                       id="rg" 
                                       name="rg" 
                                       value="{{ old('rg') }}" 
                                       placeholder="00.000.000-0">
                                @error('rg')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="orgao_emissor" class="form-label">Órgão Emissor</label>
                                <input type="text" 
                                       class="form-control @error('orgao_emissor') is-invalid @enderror" 
                                       id="orgao_emissor" 
                                       name="orgao_emissor" 
                                       value="{{ old('orgao_emissor') }}">
                                @error('orgao_emissor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="estado_civil" class="form-label">Estado Civil</label>
                                <select class="form-select @error('estado_civil') is-invalid @enderror" 
                                        id="estado_civil" 
                                        name="estado_civil">
                                    <option value="">Selecione</option>
                                    <option value="solteiro" {{ old('estado_civil') == 'solteiro' ? 'selected' : '' }}>Solteiro(a)</option>
                                    <option value="casado" {{ old('estado_civil') == 'casado' ? 'selected' : '' }}>Casado(a)</option>
                                    <option value="divorciado" {{ old('estado_civil') == 'divorciado' ? 'selected' : '' }}>Divorciado(a)</option>
                                    <option value="viuvo" {{ old('estado_civil') == 'viuvo' ? 'selected' : '' }}>Viúvo(a)</option>
                                    <option value="outro" {{ old('estado_civil') == 'outro' ? 'selected' : '' }}>Outro</option>
                                </select>
                                @error('estado_civil')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="nacionalidade" class="form-label">Nacionalidade</label>
                                <input type="text" 
                                       class="form-control @error('nacionalidade') is-invalid @enderror" 
                                       id="nacionalidade" 
                                       name="nacionalidade" 
                                       value="{{ old('nacionalidade', 'Brasileira') }}">
                                @error('nacionalidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="naturalidade" class="form-label">Naturalidade</label>
                                <input type="text" 
                                       class="form-control @error('naturalidade') is-invalid @enderror" 
                                       id="naturalidade" 
                                       name="naturalidade" 
                                       value="{{ old('naturalidade') }}">
                                @error('naturalidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Endereço
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="cep" class="form-label">CEP</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control @error('cep') is-invalid @enderror" 
                                           id="cep" 
                                           name="cep" 
                                           value="{{ old('cep') }}" 
                                           placeholder="00000-000">
                                    <span class="input-group-text" id="cep-loading" style="display: none;">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                    @error('cep')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">Digite o CEP para buscar o endereço automaticamente</small>
                            </div>

                            <div class="col-md-7">
                                <label for="logradouro" class="form-label">Logradouro</label>
                                <input type="text" 
                                       class="form-control @error('logradouro') is-invalid @enderror" 
                                       id="logradouro" 
                                       name="logradouro" 
                                       value="{{ old('logradouro') }}">
                                @error('logradouro')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="numero" class="form-label">Número</label>
                                <input type="text" 
                                       class="form-control @error('numero') is-invalid @enderror" 
                                       id="numero" 
                                       name="numero" 
                                       value="{{ old('numero') }}">
                                @error('numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="complemento" class="form-label">Complemento</label>
                                <input type="text" 
                                       class="form-control @error('complemento') is-invalid @enderror" 
                                       id="complemento" 
                                       name="complemento" 
                                       value="{{ old('complemento') }}">
                                @error('complemento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="bairro" class="form-label">Bairro</label>
                                <input type="text" 
                                       class="form-control @error('bairro') is-invalid @enderror" 
                                       id="bairro" 
                                       name="bairro" 
                                       value="{{ old('bairro') }}">
                                @error('bairro')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" 
                                       class="form-control @error('cidade') is-invalid @enderror" 
                                       id="cidade" 
                                       name="cidade" 
                                       value="{{ old('cidade') }}">
                                @error('cidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado">
                                    <option value="">UF</option>
                                    @foreach(['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $uf)
                                        <option value="{{ $uf }}" {{ old('estado') == $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                    @endforeach
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contato -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-phone me-2"></i>
                            Contato
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="telefone_fixo" class="form-label">Celular (Opcional)</label>
                                <input type="text" 
                                       class="form-control @error('telefone_fixo') is-invalid @enderror" 
                                       id="telefone_fixo" 
                                       name="telefone_fixo" 
                                       value="{{ old('telefone_fixo') }}"
                                       placeholder="(00) 00000-0000">
                                @error('telefone_fixo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="telefone_celular" class="form-label">Celular</label>
                                <input type="text" 
                                       class="form-control @error('telefone_celular') is-invalid @enderror" 
                                       id="telefone_celular" 
                                       name="telefone_celular" 
                                       value="{{ old('telefone_celular', $inscricao->telefone ?? '') }}" 
                                       placeholder="(00) 00000-0000">
                                @error('telefone_celular')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $inscricao->email ?? '') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nome_mae" class="form-label">Nome da Mãe</label>
                                <input type="text" 
                                       class="form-control @error('nome_mae') is-invalid @enderror" 
                                       id="nome_mae" 
                                       name="nome_mae" 
                                       value="{{ old('nome_mae') }}">
                                @error('nome_mae')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nome_pai" class="form-label">Nome do Pai</label>
                                <input type="text" 
                                       class="form-control @error('nome_pai') is-invalid @enderror" 
                                       id="nome_pai" 
                                       name="nome_pai" 
                                       value="{{ old('nome_pai') }}">
                                @error('nome_pai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações e Escola Parceira -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Informações Adicionais
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="observacoes" class="form-label">Observações</label>
                                <textarea class="form-control @error('observacoes') is-invalid @enderror" 
                                          id="observacoes" 
                                          name="observacoes" 
                                          rows="3" 
                                          placeholder="Digite aqui observações adicionais sobre o aluno...">{{ old('observacoes') }}</textarea>
                                @error('observacoes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input @error('escola_parceira') is-invalid @enderror" 
                                           type="checkbox" 
                                           id="escola_parceira" 
                                           name="escola_parceira" 
                                           value="1"
                                           {{ old('escola_parceira') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="escola_parceira">
                                        <strong>Este aluno vem de uma escola parceira</strong>
                                    </label>
                                    @error('escola_parceira')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6" id="campo_parceiro" style="display: none;">
                                <label for="parceiro_id" class="form-label">Escola Parceira <span class="text-danger">*</span></label>
                                <select class="form-select @error('parceiro_id') is-invalid @enderror" 
                                        id="parceiro_id" 
                                        name="parceiro_id">
                                    <option value="">Selecione a escola parceira...</option>
                                    @foreach($parceiros as $parceiro)
                                        <option value="{{ $parceiro->id }}" {{ old('parceiro_id') == $parceiro->id ? 'selected' : '' }}>
                                            {{ $parceiro->nome_exibicao }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parceiro_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados Acadêmicos -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-graduation-cap me-2"></i>
                            Dados Acadêmicos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="modalidade" class="form-label">Modalidade</label>
                                <select class="form-select @error('modalidade') is-invalid @enderror" 
                                        id="modalidade" 
                                        name="modalidade">
                                    <option value="">Selecione</option>
                                    @foreach($formSettings['available_modalities'] ?? [] as $value => $label)
                                        <option value="{{ $value }}" {{ old('modalidade', $inscricao->modalidade ?? '') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('modalidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="curso" class="form-label">Curso</label>
                                <select class="form-select @error('curso') is-invalid @enderror" 
                                        id="curso" 
                                        name="curso">
                                    <option value="">Selecione</option>
                                    @foreach($formSettings['available_courses'] ?? [] as $value => $label)
                                        <option value="{{ $value }}" {{ old('curso', $inscricao->curso ?? '') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('curso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="ultima_serie" class="form-label">Última Série Cursada</label>
                                <input type="text" 
                                       class="form-control @error('ultima_serie') is-invalid @enderror" 
                                       id="ultima_serie" 
                                       name="ultima_serie" 
                                       value="{{ old('ultima_serie') }}">
                                @error('ultima_serie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="ano_conclusao" class="form-label">Ano de Conclusão</label>
                                <input type="number" 
                                       class="form-control @error('ano_conclusao') is-invalid @enderror" 
                                       id="ano_conclusao" 
                                       name="ano_conclusao" 
                                       value="{{ old('ano_conclusao') }}" 
                                       min="1950" 
                                       max="{{ date('Y') }}">
                                @error('ano_conclusao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="escola_origem" class="form-label">Escola de Origem</label>
                                <input type="text" 
                                       class="form-control @error('escola_origem') is-invalid @enderror" 
                                       id="escola_origem" 
                                       name="escola_origem" 
                                       value="{{ old('escola_origem') }}">
                                @error('escola_origem')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados da Pagamento -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            Dados de Pagamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Passo 1: Gateway de Pagamento -->
                            <div class="col-md-3 mb-3">
                                <label for="payment_gateway" class="form-label">1. Gateway de Pagamento</label>
                                <select class="form-select" id="payment_gateway" name="payment_gateway">
                                    <option value="mercado_pago" selected>Mercado Pago</option>
                                    <option value="asas">Banco Asas</option>
                                    <option value="infiny_pay">Banco Infiny Pay</option>
                                    <option value="cora">Banco Cora</option>
                                </select>
                            </div>

                            <!-- Passo 2: Forma de Pagamento (apenas para Mercado Pago) -->
                            <div class="col-md-3 mb-3 campo-forma-pagamento">
                                <label for="forma_pagamento" class="form-label">2. Forma de Pagamento</label>
                                <select class="form-select" id="forma_pagamento" name="forma_pagamento">
                                    <option value="" disabled selected>Selecione</option>
                                    <option value="pix">PIX</option>
                                    <option value="cartao_credito">Cartão de Crédito</option>
                                    <option value="boleto">Boleto</option>
                                </select>
                            </div>

                            <!-- Status (visível para todos os gateways) -->
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="">Selecione</option>
                                    <option value="pre_matricula" {{ old('status', 'pre_matricula') == 'pre_matricula' ? 'selected' : '' }}>Pré-Matrícula</option>
                                    <option value="matricula_confirmada" {{ old('status') == 'matricula_confirmada' ? 'selected' : '' }}>Matrícula Confirmada</option>
                                    <option value="cancelada" {{ old('status') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                    <option value="trancada" {{ old('status') == 'trancada' ? 'selected' : '' }}>Trancada</option>
                                    <option value="concluida" {{ old('status') == 'concluida' ? 'selected' : '' }}>Concluída</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Campo de Valor Pago (apenas para gateways manuais) -->
                            <div class="col-md-3 mb-3 campo-valor-pago" style="display: none;">
                                <label for="valor_pago" class="form-label">Valor Pago</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="valor_pago" 
                                           name="valor_pago" 
                                           step="0.01" 
                                           min="0" 
                                           placeholder="0,00">
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Valor que o aluno efetivamente pagou.
                                </small>
                            </div>

                            <!-- Campo de Informações Bancárias (apenas para gateways manuais) -->
                            <div class="col-md-12 mb-3 campo-bank-info" style="display: none;">
                                <label for="bank_info" class="form-label">Informações do Banco</label>
                                <textarea class="form-control" id="bank_info" name="bank_info" rows="3" 
                                          placeholder="Cole aqui o link de pagamento ou informações bancárias..."></textarea>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Insira o link de pagamento ou dados bancários para este gateway.
                                </small>
                            </div>

                            <!-- Passo 2: Tipo de Pagamento (apenas para boleto) -->
                            <div class="col-md-3 mb-3 campo-boleto passo-2" style="display: none;">
                                <label for="tipo_boleto" class="form-label">2. Tipo de Pagamento</label>
                                <select class="form-select" id="tipo_boleto" name="tipo_boleto">
                                    <option value="" disabled selected>Selecione</option>
                                    <option value="avista">À Vista</option>
                                    <option value="parcelado">Parcelado</option>
                                </select>
                            </div>

                            <!-- Passo 3: Valor Total do Curso -->
                            <div class="col-md-3 mb-3 passo-3" style="display: none;">
                                <label for="valor_total_curso" class="form-label">3. Valor Total do Curso</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control" id="valor_total_curso" name="valor_total_curso" step="0.01">
                                </div>
                            </div>

                            <!-- Passo 4: Valor da Matrícula (apenas para parcelado) -->
                            <div class="col-md-3 mb-3 campo-matricula passo-4" style="display: none;">
                                <label for="valor_matricula" class="form-label">4. Valor da Matrícula</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control" id="valor_matricula" name="valor_matricula" step="0.01">
                                </div>
                            </div>



                            <!-- Passo 5: Número de Parcelas -->
                            <div class="col-md-3 mb-3 campo-parcelas passo-5" style="display: none;">
                                <label for="numero_parcelas" class="form-label">5. Número de Parcelas</label>
                                <input type="number" max="6" class="form-control" id="numero_parcelas" name="numero_parcelas" min="1" max="12" value="1">
                            </div>

                            <!-- Dia de Vencimento -->
                            <div class="col-md-3 mb-3 campo-dia-vencimento passo-6" style="display: none;">
                                <label for="dia_vencimento" class="form-label">6. Dia de Vencimento</label>
                                <select class="form-select" id="dia_vencimento" name="dia_vencimento">
                                    <option value="">Selecione</option>
                                    @for($i = 1; $i <= 31; $i++)
                                        <option value="{{ $i }}" {{ old('dia_vencimento') == $i ? 'selected' : '' }}>
                                            Dia {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                <small class="form-text text-muted">Dia do mês para vencimento das mensalidades</small>
                            </div>

                            <!-- Passo 7: Desconto -->
                            <div class="col-md-3 mb-3 campo-desconto passo-7" style="display: none;">
                                <label for="desconto" class="form-label">7. Desconto</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="desconto" name="desconto" min="0" max="100" step="0.01" value="0">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="form-text text-muted">Digite a porcentagem de desconto (0-100%)</small>
                            </div>

                            <!-- Passo 7 alternativo: Juros do Boleto -->
                            <div class="col-md-3 mb-3 campo-juros-boleto passo-7" style="display: none;">
                                <label for="percentual_juros" class="form-label">7. Juros ao Mês</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           id="percentual_juros" 
                                           name="percentual_juros" 
                                           min="0" 
                                           max="100"
                                           value="0"
                                           placeholder="Ex: 5 para 5%">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="form-text text-muted">Taxa de juros aplicada somente em parcelas vencidas</small>
                            </div>
                            
                            <!-- Aviso sobre juros -->
                            <div class="col-12 mt-2 campo-aviso-juros passo-7" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Sobre os juros para boletos:</strong> Os juros serão aplicados apenas nas parcelas com pagamento em atraso, calculados sobre o valor da parcela vencida. Não são aplicados no valor total do curso.
                                </div>
                            </div>

                            <!-- Calculadora de Totais -->
                            <div class="col-12 mt-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="fas fa-calculator me-2"></i>
                                            Resumo Financeiro
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-2">
                                                    <small class="text-muted">Valor da Matrícula:</small>
                                                    <div class="fw-bold text-primary" id="display-matricula">R$ 0,00</div>
                                                </div>
                                            </div>

                                            <div class="col-md-3 campo-display-mensalidade">
                                                <div class="mb-2">
                                                    <small class="text-muted">Valor da Mensalidade:</small>
                                                    <div class="fw-bold text-secondary" id="display-mensalidade">R$ 0,00</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 campo-display-juros" style="display: none;">
                                                <div class="mb-2">
                                                    <small class="text-muted">Valor dos Juros:</small>
                                                    <div class="fw-bold text-warning" id="display-juros">R$ 0,00</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-2">
                                                    <small class="text-muted">Total do Curso:</small>
                                                    <div class="fw-bold text-success fs-5" id="display-total">R$ 0,00</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2 campo-detalhes-parcelamento" style="display: none;">
                                            <div class="col-12">
                                                <small class="text-muted">Detalhamento:</small>
                                                <div id="detalhes-calculo" class="small"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentos -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cloud me-2"></i>
                            Documentos do Aluno
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Informações sobre a pasta -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info d-flex align-items-center mb-3">
                                    <i class="fas fa-info-circle me-3"></i>
                                    <div>
                                        <strong>Como funciona:</strong> 
                                        Escolha uma pasta de organização (turma, período, etc.) e após preencher o nome e CPF do aluno, clique em "Criar Pasta" para gerar uma pasta exclusiva no Google Drive onde você poderá organizar todos os documentos da matrícula.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seletor de Pasta Pai -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="parentFolderSelect" class="form-label">
                                    <i class="fas fa-folder me-2"></i>
                                    Pasta de Organização (Opcional)
                                </label>
                                <div class="input-group">
                                    <select class="form-select" id="parentFolderSelect">
                                        <option value="">Criar na pasta raiz do Drive</option>
                                        <option value="loading" disabled>Carregando pastas...</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary" id="refreshFoldersBtn" title="Atualizar lista de pastas">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <!-- <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createParentFolderModal">
                                        <i class="fas fa-plus me-1"></i>
                                        Nova Pasta
                                    </button> -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ação</label>
                                <div class="d-grid">
                                    <button type="button" 
                                            id="createFolderBtn" 
                                            class="btn btn-primary" 
                                            disabled>
                                        <i class="fas fa-folder-plus me-2"></i>
                                        Criar Pasta do Aluno
                                    </button>
                                </div>
                            </div>
                                </div>
                                
                        <!-- Status da Pasta -->
                        <div id="folderStatusSection" class="d-none">
                            <div class="alert alert-success d-flex align-items-center mb-4">
                                <i class="fas fa-check-circle me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1">Pasta criada com sucesso!</h6>
                                    <p class="mb-0">Nome da pasta: <strong id="folderName"></strong></p>
                                    <small class="text-muted">Agora você pode fazer upload dos documentos do aluno.</small>
                                </div>
                                <div class="ms-auto">
                                    <a id="openFolderBtn" href="#" target="_blank" class="btn btn-success btn-sm">
                                        <i class="fas fa-external-link-alt me-1"></i>
                                        Abrir no Drive
                                    </a>
                                    <button type="button" class="btn btn-info btn-sm ms-2" onclick="debugFolderId(); loadFiles();">
                                        <i class="fas fa-bug me-1"></i>
                                        Debug
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm ms-2" onclick="debugApiResponse();">
                                        <i class="fas fa-code me-1"></i>
                                        Test API
                                    </button>
                                </div>
                            </div>
                                    </div>
                                    
                        <!-- Área de Upload -->
                        <div id="uploadSection" class="d-none">
                            <div class="row">
                                <!-- Área de Drag & Drop -->
                                <div class="col-md-6">
                                    <div class="card border-2 border-dashed h-100" id="dropZone" style="border-color: #dee2e6;">
                                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 200px;">
                                            <div id="dropZoneContent">
                                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">Arraste e solte arquivos aqui</h5>
                                                <p class="text-muted mb-3">ou clique para selecionar</p>
                                                <input type="file" id="fileInput" multiple class="d-none" 
                                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileInput').click()">
                                                    <i class="fas fa-plus me-2"></i>
                                                    Selecionar Arquivos
                                            </button>
                                            </div>
                                            <div id="uploadProgress" class="d-none w-100">
                                                <div class="progress mb-3">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                         role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <p class="text-muted mb-0">Enviando arquivo...</p>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    
                                <!-- Lista de Arquivos -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-file-alt me-2"></i>
                                                Documentos Enviados
                                                <span class="badge bg-primary ms-2" id="fileCount">0</span>
                                            </h6>
                                        </div>
                                        <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                                            <div id="filesList" class="list-group list-group-flush">
                                                <div class="list-group-item text-center text-muted py-4">
                                                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                                                    <p class="mb-0">Nenhum documento enviado</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    </div>
                                </div>
                                
                                <!-- Loading Spinner -->
                                <div id="loadingSpinner" class="text-center d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                            <p class="mt-2 text-muted">Processando...</p>
                                </div>
                            </div>
                        </div>
                    </div>

            <!-- Modal para Criar Pasta Pai -->
            <div class="modal fade" id="createParentFolderModal" tabindex="-1" aria-labelledby="createParentFolderModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createParentFolderModalLabel">
                                <i class="fas fa-folder-plus me-2"></i>
                                Criar Nova Pasta de Organização
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createParentFolderForm">
                                <div class="mb-3">
                                    <label for="parentFolderName" class="form-label">Nome da Pasta</label>
                                    <input type="text" class="form-control" id="parentFolderName" 
                                           placeholder="Ex: Turma 2025.1, EJA Fundamental, etc.">
                                    <div class="form-text">
                                        Esta pasta será criada na raiz do Google Drive e poderá ser usada para organizar documentos de múltiplos alunos.
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="confirmCreateParentFolder">
                                <i class="fas fa-plus me-2"></i>
                                Criar Pasta
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Compartilhamento -->
            <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="shareModalLabel">
                                <i class="fas fa-share-alt me-2"></i>
                                Compartilhar Arquivo
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Informações do Arquivo -->
                            <div class="alert alert-light d-flex align-items-center mb-4">
                                <i class="fas fa-file fa-2x me-3 text-primary"></i>
                                <div>
                                    <h6 class="mb-0" id="shareFileName">Nome do Arquivo</h6>
                                    <small class="text-muted">Configure as permissões de acesso abaixo</small>
                                </div>
                            </div>

                            <!-- Tabs de Compartilhamento -->
                            <ul class="nav nav-tabs" id="shareTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="user-share-tab" data-bs-toggle="tab" 
                                            data-bs-target="#user-share" type="button" role="tab">
                                        <i class="fas fa-user me-2"></i>Compartilhar com Usuário
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="public-link-tab" data-bs-toggle="tab" 
                                            data-bs-target="#public-link" type="button" role="tab">
                                        <i class="fas fa-link me-2"></i>Link Público
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="embed-tab" data-bs-toggle="tab" 
                                            data-bs-target="#embed" type="button" role="tab">
                                        <i class="fas fa-code me-2"></i>Incorporar
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" 
                                            data-bs-target="#permissions" type="button" role="tab">
                                        <i class="fas fa-shield-alt me-2"></i>Permissões
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mt-3" id="shareTabContent">
                                <!-- Compartilhar com Usuário -->
                                <div class="tab-pane fade show active" id="user-share" role="tabpanel">
                                    <form id="shareUserForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="shareEmail" class="form-label">E-mail do Usuário</label>
                                                <input type="email" class="form-control" id="shareEmail" 
                                                       placeholder="usuario@exemplo.com">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="shareRole" class="form-label">Permissão</label>
                                                <select class="form-select" id="shareRole">
                                                    <option value="reader">Visualizador</option>
                                                    <option value="commenter">Comentarista</option>
                                                    <option value="writer">Editor</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="fas fa-share me-1"></i>Compartilhar
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="notifyUser" checked>
                                            <label class="form-check-label" for="notifyUser">
                                                Enviar notificação por e-mail
                                            </label>
                                        </div>
                                    </form>
                                </div>

                                <!-- Link Público -->
                                <div class="tab-pane fade" id="public-link" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label for="publicRole" class="form-label">Permissão do Link Público</label>
                                            <select class="form-select" id="publicRole">
                                                <option value="reader">Visualizador</option>
                                                <option value="commenter">Comentarista</option>
                                                <option value="writer">Editor</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="button" class="btn btn-success w-100" id="createPublicLinkBtn">
                                                <i class="fas fa-link me-1"></i>Criar Link
                                            </button>
                                        </div>
                                    </div>
                                    <div id="publicLinkResult" class="mt-3 d-none">
                                        <label class="form-label">Link Compartilhável</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="publicLinkUrl" readonly>
                                            <button class="btn btn-outline-secondary" type="button" id="copyPublicLink">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Incorporar -->
                                <div class="tab-pane fade" id="embed" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="embedWidth" class="form-label">Largura</label>
                                            <input type="text" class="form-control" id="embedWidth" value="100%" placeholder="100% ou 800px">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="embedHeight" class="form-label">Altura</label>
                                            <input type="text" class="form-control" id="embedHeight" value="600px" placeholder="600px">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-info w-100" id="generateEmbedBtn">
                                                <i class="fas fa-code me-1"></i>Gerar
                                            </button>
                                        </div>
                                    </div>
                                    <div id="embedResult" class="mt-3 d-none">
                                        <label class="form-label">Código de Incorporação</label>
                                        <div class="input-group">
                                            <textarea class="form-control" id="embedCode" rows="3" readonly></textarea>
                                            <button class="btn btn-outline-secondary" type="button" id="copyEmbedCode">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Copie este código e cole em seu site ou aplicação</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Permissões Atuais -->
                                <div class="tab-pane fade" id="permissions" role="tabpanel">
                                    <div id="permissionsList">
                                        <div class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Carregando permissões...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="col-12 mt-4">
                <div class="d-flex justify-content-end align-items-center gap-2">
                    <a href="{{ route('admin.matriculas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-1"></i>
                        <span class="btn-text">Salvar Matrícula</span>
                        <span class="btn-loading d-none">
                            <i class="fas fa-spinner fa-spin me-1"></i>
                            Salvando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script src="https://unpkg.com/imask"></script>
    <script>
        // Capturar erros globais de JavaScript
        window.addEventListener('error', function(e) {
            console.error('Erro JavaScript capturado:', {
                message: e.message,
                filename: e.filename,
                lineno: e.lineno,
                colno: e.colno,
                error: e.error
            });
        });
        
        // Capturar erros não tratados
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Promise rejeitada não tratada:', e.reason);
        });
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    $(document).ready(function() {
        console.log('DOM carregado - Iniciando scripts da página de matrícula');
        
        // Verificar se jQuery está disponível
        if (typeof $ === 'undefined') {
            console.error('jQuery não está disponível!');
            return;
        }
        
        // Verificar se o formulário existe
        if ($('#matriculaForm').length === 0) {
            console.error('Formulário de matrícula não encontrado!');
            return;
        }
        // Cache dos elementos DOM - Google Drive
        const driveElements = {
            nomeCompletoInput: $('#nome_completo'),
            cpfInput: $('#cpf'),
            parentFolderSelect: $('#parentFolderSelect'),
            refreshFoldersBtn: $('#refreshFoldersBtn'),
            createFolderBtn: $('#createFolderBtn'),
            folderStatusSection: $('#folderStatusSection'),
            folderName: $('#folderName'),
            openFolderBtn: $('#openFolderBtn'),
            uploadSection: $('#uploadSection'),
            loadingSpinner: $('#loadingSpinner'),
            filesList: $('#filesList'),
            fileCount: $('#fileCount'),
            fileInput: $('#fileInput'),
            dropZone: $('#dropZone'),
            dropZoneContent: $('#dropZoneContent'),
            uploadProgress: $('#uploadProgress'),
            googleDriveFolderId: $('#google_drive_folder_id'),
            // Modal elements
            parentFolderName: $('#parentFolderName'),
            confirmCreateParentFolder: $('#confirmCreateParentFolder'),
            createParentFolderModal: $('#createParentFolderModal')
        };
        
        // Debug: verificar se todos os elementos foram encontrados
        console.log('Drive elements found:', {
            filesList: driveElements.filesList.length,
            fileCount: driveElements.fileCount.length,
            uploadSection: driveElements.uploadSection.length,
            folderStatusSection: driveElements.folderStatusSection.length
        });

        // Cache dos elementos DOM - Matrícula
        const matriculaElements = {
            formaPagamento: $('#forma_pagamento'),
            paymentGateway: $('#payment_gateway'),
            bankInfo: $('#bank_info'),
            tipoBoleto: $('#tipo_boleto'),
            valorTotalCurso: $('#valor_total_curso'),
            valorMatricula: $('#valor_matricula'),
            numeroParcelas: $('#numero_parcelas'),
            desconto: $('#desconto'),
            percentualJuros: $('#percentual_juros'),
            displays: {
                total: $('#display-total'),
                matricula: $('#display-matricula'),
                mensalidade: $('#display-mensalidade'),
                juros: $('#display-juros'),
                detalhes: $('#detalhes-calculo')
            }
        };

        let studentFolderId = null;
        let availableFolders = [];
        
        // Debug function to check folder ID
        function debugFolderId() {
            console.log('Current studentFolderId:', studentFolderId);
            console.log('Hidden input value:', driveElements.googleDriveFolderId.val());
            return studentFolderId;
        }

        // Folder Management Functions
        function loadAvailableFolders() {
            driveElements.parentFolderSelect.html('<option value="loading" disabled selected>Carregando pastas...</option>');
            
            $.ajax({
                url: '{{ route("admin.files.list-folders") }}',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        availableFolders = response.folders;
                        updateFolderSelect();
                    } else {
                        driveElements.parentFolderSelect.html('<option value="">Erro ao carregar pastas</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading folders:', { status, error, response: xhr.responseText });
                    driveElements.parentFolderSelect.html('<option value="">Erro ao carregar pastas</option>');
                }
            });
        }

        function updateFolderSelect() {
            let options = '<option value="">Criar na pasta raiz do Drive</option>';
            
            if (availableFolders.length > 0) {
                availableFolders.forEach(folder => {
                    options += `<option value="${folder.id}">${folder.name}</option>`;
                });
            } else {
                options += '<option value="" disabled>Nenhuma pasta encontrada</option>';
            }
            
            driveElements.parentFolderSelect.html(options);
        }

        function createParentFolder() {
            const folderName = driveElements.parentFolderName.val().trim();
            if (!folderName) {
                showToast('Por favor, digite o nome da pasta.', 'error');
                return;
            }

            driveElements.confirmCreateParentFolder.prop('disabled', true);
            driveElements.confirmCreateParentFolder.html('<i class="fas fa-spinner fa-spin me-2"></i>Criando...');

            $.ajax({
                url: '{{ route("admin.files.create-parent-folder") }}',
                method: 'POST',
                data: { name: folderName },
                success: function(response) {
                    console.log('Parent folder created:', response);
                    if (response.success) {
                        toastr.success('Pasta criada com sucesso!');
                        
                        // Adicionar a nova pasta à lista e selecionar
                        availableFolders.push(response.folder);
                        updateFolderSelect();
                        driveElements.parentFolderSelect.val(response.folder.id);
                        
                        // Fechar modal e limpar form
                        const modal = bootstrap.Modal.getInstance(driveElements.createParentFolderModal[0]);
                        modal.hide();
                        driveElements.parentFolderName.val('');
                    } else {
                        toastr.error('Erro ao criar pasta: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error creating parent folder:', { status, error, response: xhr.responseText });
                    toastr.error('Erro ao criar pasta. Tente novamente.');
                },
                complete: function() {
                    driveElements.confirmCreateParentFolder.prop('disabled', false);
                    driveElements.confirmCreateParentFolder.html('<i class="fas fa-plus me-2"></i>Criar Pasta');
                }
            });
        }

        // Utility Functions
        function getFileIcon(mimeType) {
            if (!mimeType) return 'fas fa-file';
            
            if (mimeType.includes('pdf')) return 'fas fa-file-pdf';
            if (mimeType.includes('image')) return 'fas fa-file-image';
            if (mimeType.includes('video')) return 'fas fa-file-video';
            if (mimeType.includes('audio')) return 'fas fa-file-audio';
            if (mimeType.includes('word') || mimeType.includes('document')) return 'fas fa-file-word';
            if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fas fa-file-excel';
            if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) return 'fas fa-file-powerpoint';
            if (mimeType.includes('zip') || mimeType.includes('rar') || mimeType.includes('tar')) return 'fas fa-file-archive';
            
            return 'fas fa-file';
        }

        function formatFileSize(bytes) {
            if (!bytes) return 'Tamanho desconhecido';
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function uploadFile(file) {
            if (!file || !studentFolderId) {
                console.log('No file selected or no folder ID');
                return;
            }

            console.log('Uploading file:', file.name, 'to folder:', studentFolderId);
            const formData = new FormData();
            formData.append('file', file);
            formData.append('folder_id', studentFolderId);

            // Show upload progress
            driveElements.dropZoneContent.addClass('d-none');
            driveElements.uploadProgress.removeClass('d-none');

            $.ajax({
                url: '{{ route("admin.files.store") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = (evt.loaded / evt.total) * 100;
                            driveElements.uploadProgress.find('.progress-bar')
                                .css('width', percentComplete + '%')
                                .attr('aria-valuenow', percentComplete);
                            driveElements.uploadProgress.find('p')
                                .text(`Enviando ${file.name}... ${percentComplete.toFixed(0)}%`);
                        }
                    });
                    return xhr;
                },
                success: function(response) {
                    console.log('Upload response:', response);
                    if (response.success) {
                        console.log('Upload successful, reloading files...');
                        // Aguardar um pouco antes de recarregar para garantir que o arquivo foi processado
                        setTimeout(function() {
                            loadFiles();
                        }, 1000);
                        // Show success message
                        toastr.success(`Arquivo "${file.name}" enviado com sucesso!`);
                    } else {
                        console.error('Upload error response:', response);
                        toastr.error('Erro ao fazer upload do arquivo: ' + (response.message || 'Erro desconhecido'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Upload error:', { status, error, response: xhr.responseText });
                    let errorMessage = 'Erro ao fazer upload do arquivo.';
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMessage = errorResponse.message;
                        }
                    } catch (e) {
                        // Se não conseguir parsear o JSON, usar a mensagem padrão
                    }
                    toastr.error(errorMessage);
                },
                complete: function() {
                    // Reset upload area
                    driveElements.dropZoneContent.removeClass('d-none');
                    driveElements.uploadProgress.addClass('d-none');
                    driveElements.uploadProgress.find('.progress-bar').css('width', '0%');
                }
            });
        }

        // Google Drive Functions
        function generateFolderName() {
            const name = driveElements.nomeCompletoInput.val().trim();
            const cpf = driveElements.cpfInput.val().trim();
            const date = new Date().toLocaleDateString('pt-BR', { 
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit' 
            });
            
            let folderName = name;
            if (cpf) {
                // Limpar formatação do CPF para usar apenas números
                const cleanCpf = cpf.replace(/\D/g, '');
                if (cleanCpf.length >= 3) {
                    folderName += ` - CPF ${cleanCpf}`;
                }
            }
            folderName += ` - ${date} - Documentos`;
            
            return folderName;
        }

        function validateFolderCreation() {
            const name = driveElements.nomeCompletoInput.val().trim();
            const cpf = driveElements.cpfInput.val().trim();
            return name.length >= 3 && cpf.length >= 11; // Nome mínimo e CPF com pelo menos 11 caracteres
        }

        function handleCreateFolder(e) {
            console.log('Create folder button clicked');
            e.preventDefault();
            
            if (!validateFolderCreation()) {
                toastr.error('Por favor, preencha o nome completo e CPF do aluno antes de criar a pasta.');
                return;
            }

            const folderName = generateFolderName();
            const parentFolderId = driveElements.parentFolderSelect.val();
            
            console.log('Generated folder name:', folderName);
            console.log('Parent folder ID:', parentFolderId);

            driveElements.loadingSpinner.removeClass('d-none');
            driveElements.createFolderBtn.prop('disabled', true);
            driveElements.createFolderBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Criando...');

            const url = '{{ route("admin.files.create-folder") }}';
            console.log('Making AJAX request to:', url);

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    name: folderName,
                    parent_id: parentFolderId || null,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log('AJAX success response:', response);
                    if (response.success) {
                        studentFolderId = response.folder.file_id;
                        console.log('Student folder ID set to:', studentFolderId);
                        
                        driveElements.googleDriveFolderId.val(studentFolderId);
                        driveElements.folderStatusSection.removeClass('d-none');
                        driveElements.folderName.text(response.folder.name);
                        driveElements.openFolderBtn.attr('href', response.folder.web_view_link);
                        driveElements.uploadSection.removeClass('d-none');
                        
                        // Atualizar botão para estado de sucesso
                        driveElements.createFolderBtn.html('<i class="fas fa-check me-2"></i>Pasta Criada');
                        driveElements.createFolderBtn.removeClass('btn-primary').addClass('btn-success');
                        
                        // Aguardar um pouco antes de carregar os arquivos
                        setTimeout(function() {
                            console.log('Loading files after folder creation...');
                            loadFiles();
                        }, 1000);
                        
                        toastr.success('Pasta criada com sucesso! Agora você pode fazer upload dos documentos.');
                    } else {
                        console.error('Error response:', response);
                        toastr.error('Erro ao criar pasta: ' + (response.message || 'Erro desconhecido'));
                        driveElements.createFolderBtn.prop('disabled', false);
                        driveElements.createFolderBtn.html('<i class="fas fa-folder-plus me-2"></i>Criar Pasta do Aluno');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    
                    let errorMessage = 'Erro ao criar pasta.';
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMessage = errorResponse.message;
                        }
                    } catch (e) {
                        // Se não conseguir parsear o JSON, usar a mensagem padrão
                    }
                    
                    toastr.error(errorMessage);
                    driveElements.createFolderBtn.prop('disabled', false);
                    driveElements.createFolderBtn.html('<i class="fas fa-folder-plus me-2"></i>Criar Pasta do Aluno');
                },
                complete: function() {
                    driveElements.loadingSpinner.addClass('d-none');
                }
            });
        }

        function handleMultipleFileUpload(files) {
            if (!files || files.length === 0 || !studentFolderId) {
                console.log('No files selected or no folder ID');
                return;
            }

            console.log('Handling multiple file upload:', files.length, 'files');
            
            // Upload files one by one with a small delay between each
            Array.from(files).forEach((file, index) => {
                setTimeout(() => {
                    uploadFile(file);
                }, index * 500); // 500ms delay between each upload
            });
        }

        function loadFiles() {
            if (!studentFolderId) {
                console.log('No folder ID available');
                return;
            }

            console.log('Loading files for folder:', studentFolderId);
            console.log('Files list element:', driveElements.filesList.length > 0 ? 'Found' : 'Not found');
            
            driveElements.loadingSpinner.removeClass('d-none');
            driveElements.filesList.empty();

            $.ajax({
                url: '{{ route("admin.files.index") }}',
                method: 'GET',
                data: { folder: studentFolderId },
                success: function(response) {
                    console.log('List files response:', response);
                    driveElements.filesList.empty();
                    
                    if (response.files && response.files.length > 0) {
                        console.log('Found', response.files.length, 'files');
                        response.files.forEach(function(file) {
                            const fileIcon = getFileIcon(file.mime_type);
                            const fileSize = formatFileSize(file.size);
                            
                            const fileItem = `
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="${fileIcon} fa-lg me-3 text-primary"></i>
                                        <div>
                                            <h6 class="mb-0">${file.name}</h6>
                                            <small class="text-muted">${fileSize}</small>
                                        </div>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <a href="${file.web_view_link}" target="_blank" 
                                           class="btn btn-outline-primary btn-sm" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-info btn-sm share-file" 
                                                data-id="${file.id}" data-name="${file.name}" title="Compartilhar">
                                            <i class="fas fa-share-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm delete-file" 
                                                data-id="${file.id}" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                            driveElements.filesList.append(fileItem);
                        });

                        // Event handlers are now delegated globally
                        driveElements.fileCount.text(response.files.length);
                    } else {
                        console.log('No files found');
                        driveElements.filesList.append(`
                            <div class="list-group-item text-center text-muted py-4">
                                <i class="fas fa-file-alt fa-2x mb-2"></i>
                                <p class="mb-0">Nenhum documento enviado</p>
                            </div>
                        `);
                        driveElements.fileCount.text(0);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('List files error:', { status, error, response: xhr.responseText });
                    driveElements.filesList.append(`
                        <div class="list-group-item text-center text-danger py-4">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <p class="mb-0">Erro ao carregar arquivos</p>
                            <small>Verifique o console para mais detalhes</small>
                        </div>
                    `);
                },
                complete: function() {
                    driveElements.loadingSpinner.addClass('d-none');
                }
            });
        }

        function handleDeleteFile(e) {
            e.preventDefault();
            
            try {
                const fileId = $(this).data('id');
                console.log('Delete file clicked, fileId:', fileId, 'this:', this);
                
                if (!fileId) {
                    console.error('No file ID found in data attribute');
                    toastr.error('Erro: ID do arquivo não encontrado');
                    return;
                }
                
                if (!confirm('Tem certeza que deseja excluir este arquivo?')) {
                    return;
                }

                console.log('Deleting file with ID:', fileId);

                // Teste da rota primeiro
                console.log('Testing route access...');
                
                $.ajax({
                    url: '/dashboard/files/' + fileId,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    beforeSend: function() {
                        console.log('Sending DELETE request to:', '/dashboard/files/' + fileId);
                        console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
                    },
                    success: function(response) {
                        console.log('Delete response:', response);
                        if (response.success) {
                            console.log('File deleted successfully, reloading files...');
                            toastr.success('Arquivo excluído com sucesso!');
                            // Aguardar um pouco antes de recarregar
                            setTimeout(function() {
                                loadFiles();
                            }, 500);
                        } else {
                            console.error('Delete error response:', response);
                            toastr.error('Erro ao excluir arquivo: ' + (response.message || 'Erro desconhecido'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete AJAX error:', { 
                            status: status, 
                            error: error, 
                            response: xhr.responseText,
                            xhr: xhr,
                            readyState: xhr.readyState,
                            statusText: xhr.statusText
                        });
                        
                        let errorMessage = 'Erro ao excluir arquivo.';
                        
                        // Verificar se é erro de CSRF
                        if (xhr.status === 419) {
                            errorMessage = 'Erro de CSRF. Página expirou. Recarregue a página.';
                            console.error('CSRF token mismatch or expired');
                        }
                        // Verificar se é erro de permissão
                        else if (xhr.status === 403) {
                            errorMessage = 'Você não tem permissão para excluir este arquivo.';
                            console.error('Permission denied');
                        }
                        // Verificar se é erro de não encontrado
                        else if (xhr.status === 404) {
                            errorMessage = 'Arquivo não encontrado.';
                            console.error('File not found');
                        }
                        else {
                            try {
                                const errorResponse = JSON.parse(xhr.responseText);
                                if (errorResponse.message) {
                                    errorMessage = errorResponse.message;
                                }
                            } catch (parseError) {
                                console.warn('Could not parse error response:', parseError);
                            }
                        }
                        
                        toastr.error(errorMessage);
                    }
                });
            } catch (error) {
                console.error('Error in handleDeleteFile:', error);
                toastr.error('Erro interno ao processar exclusão: ' + error.message);
            }
        }

        // Sharing Functions
        let currentFileId = null;
        let currentFileName = null;

        function handleShareFile(e) {
            e.preventDefault();
            currentFileId = $(this).data('id');
            currentFileName = $(this).data('name');
            
            $('#shareFileName').text(currentFileName);
            $('#shareModal').modal('show');
            
            // Reset forms
            resetShareModal();
            
            // Load permissions when permissions tab is active
            $('#permissions-tab').on('shown.bs.tab', loadPermissions);
        }

        function resetShareModal() {
            // Reset user share form
            $('#shareUserForm')[0].reset();
            $('#notifyUser').prop('checked', true);
            
            // Reset public link
            $('#publicLinkResult').addClass('d-none');
            $('#publicLinkUrl').val('');
            
            // Reset embed
            $('#embedResult').addClass('d-none');
            $('#embedCode').val('');
            $('#embedWidth').val('100%');
            $('#embedHeight').val('600px');
            
            // Go to first tab
            $('#user-share-tab').tab('show');
        }

        function shareWithUser(email, role, notify) {
            if (!currentFileId) return;

            const submitBtn = $('#shareUserForm button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Compartilhando...');

            $.ajax({
                url: '{{ url("dashboard/files") }}/' + currentFileId + '/share-user',
                method: 'POST',
                data: {
                    email: email,
                    role: role,
                    notify: notify
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(`Arquivo compartilhado com ${email}!`);
                        $('#shareUserForm')[0].reset();
                        // Reload permissions if tab is active
                        if ($('#permissions-tab').hasClass('active')) {
                            loadPermissions();
                        }
                    } else {
                        toastr.error('Erro: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Share error:', { status, error, response: xhr.responseText });
                    toastr.error('Erro ao compartilhar arquivo.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        }

        function createPublicLink(role) {
            if (!currentFileId) return;

            const submitBtn = $('#createPublicLinkBtn');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Criando...');

            $.ajax({
                url: '{{ url("dashboard/files") }}/' + currentFileId + '/public-link',
                method: 'POST',
                data: { role: role },
                success: function(response) {
                    if (response.success) {
                        $('#publicLinkUrl').val(response.link_info.link);
                        $('#publicLinkResult').removeClass('d-none');
                        toastr.success('Link público criado com sucesso!');
                    } else {
                        toastr.error('Erro: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Public link error:', { status, error, response: xhr.responseText });
                    toastr.error('Erro ao criar link público.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        }

        function generateEmbed(width, height) {
            if (!currentFileId) return;

            const submitBtn = $('#generateEmbedBtn');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Gerando...');

            $.ajax({
                url: '{{ url("dashboard/files") }}/' + currentFileId + '/embed',
                method: 'POST',
                data: {
                    width: width,
                    height: height
                },
                success: function(response) {
                    if (response.success) {
                        $('#embedCode').val(response.embed_info.iframe_code);
                        $('#embedResult').removeClass('d-none');
                        toastr.success('Código de incorporação gerado!');
                    } else {
                        toastr.error('Erro: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Embed error:', { status, error, response: xhr.responseText });
                    toastr.error('Erro ao gerar código de incorporação.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        }

        function loadPermissions() {
            if (!currentFileId) return;

            $('#permissionsList').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando permissões...</span>
                    </div>
                </div>
            `);

            $.ajax({
                url: '{{ url("dashboard/files") }}/' + currentFileId + '/permissions',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        displayPermissions(response.permissions);
                    } else {
                        $('#permissionsList').html('<div class="alert alert-danger">Erro ao carregar permissões</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Permissions error:', { status, error, response: xhr.responseText });
                    $('#permissionsList').html('<div class="alert alert-danger">Erro ao carregar permissões</div>');
                }
            });
        }

        function displayPermissions(permissions) {
            if (!permissions || permissions.length === 0) {
                $('#permissionsList').html('<div class="alert alert-info">Nenhuma permissão encontrada</div>');
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            
            permissions.forEach(permission => {
                const isOwner = permission.role === 'owner';
                const roleText = {
                    'owner': 'Proprietário',
                    'writer': 'Editor',
                    'commenter': 'Comentarista',
                    'reader': 'Visualizador'
                }[permission.role] || permission.role;

                const typeIcon = {
                    'user': 'fas fa-user',
                    'anyone': 'fas fa-globe',
                    'domain': 'fas fa-building'
                }[permission.type] || 'fas fa-question';

                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="${typeIcon} fa-lg me-3 text-primary"></i>
                            <div>
                                <h6 class="mb-0">${permission.displayName || permission.emailAddress || 'Qualquer pessoa'}</h6>
                                <small class="text-muted">${roleText}</small>
                            </div>
                        </div>
                        ${!isOwner ? `
                            <div class="btn-group btn-group-sm">
                                <select class="form-select form-select-sm me-2" onchange="updatePermission('${permission.id}', this.value)">
                                    <option value="reader" ${permission.role === 'reader' ? 'selected' : ''}>Visualizador</option>
                                    <option value="commenter" ${permission.role === 'commenter' ? 'selected' : ''}>Comentarista</option>
                                    <option value="writer" ${permission.role === 'writer' ? 'selected' : ''}>Editor</option>
                                </select>
                                <button class="btn btn-outline-danger btn-sm" onclick="removePermission('${permission.id}')" title="Remover">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        ` : ''}
                    </div>
                `;
            });
            
            html += '</div>';
            $('#permissionsList').html(html);
        }

        function updatePermission(permissionId, newRole) {
            if (!currentFileId) return;

            $.ajax({
                url: '{{ url("dashboard/files") }}/' + currentFileId + '/permissions',
                method: 'PUT',
                data: {
                    permission_id: permissionId,
                    role: newRole
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Permissão atualizada!');
                        loadPermissions();
                    } else {
                        toastr.error('Erro: ' + response.message);
                        loadPermissions(); // Reload to revert changes
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Update permission error:', { status, error, response: xhr.responseText });
                    toastr.error('Erro ao atualizar permissão.');
                    loadPermissions(); // Reload to revert changes
                }
            });
        }

        function removePermission(permissionId) {
            if (!currentFileId || !confirm('Tem certeza que deseja remover esta permissão?')) return;

            $.ajax({
                url: '{{ url("dashboard/files") }}/' + currentFileId + '/permissions',
                method: 'DELETE',
                data: {
                    permission_id: permissionId
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Permissão removida!');
                        loadPermissions();
                    } else {
                        toastr.error('Erro: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Remove permission error:', { status, error, response: xhr.responseText });
                    toastr.error('Erro ao remover permissão.');
                }
            });
        }

        // Copy to clipboard function
        function copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    toastr.success('Copiado para a área de transferência!');
                }).catch(() => {
                    fallbackCopyToClipboard(text);
                });
            } else {
                fallbackCopyToClipboard(text);
            }
        }

        function fallbackCopyToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                toastr.success('Copiado para a área de transferência!');
            } catch (err) {
                toastr.error('Erro ao copiar. Por favor, copie manualmente.');
            }
            
            document.body.removeChild(textArea);
        }

        // Event Listeners - Google Drive
        function updateCreateFolderButton() {
            const isValid = validateFolderCreation();
            driveElements.createFolderBtn.prop('disabled', !isValid);
            
            if (isValid) {
                const folderName = generateFolderName();
                const parentFolderId = driveElements.parentFolderSelect.val();
                let tooltip = `Criar pasta: ${folderName}`;
                
                if (parentFolderId) {
                    const parentFolder = availableFolders.find(f => f.id == parentFolderId);
                    if (parentFolder) {
                        tooltip += `\nDentro da pasta: ${parentFolder.name}`;
                    }
                } else {
                    tooltip += '\nNa pasta raiz do Google Drive';
                }
                
                driveElements.createFolderBtn.attr('title', tooltip);
            } else {
                driveElements.createFolderBtn.attr('title', 'Preencha o nome completo e CPF para criar a pasta');
            }
        }

        driveElements.nomeCompletoInput.on('input', updateCreateFolderButton);
        driveElements.cpfInput.on('input', updateCreateFolderButton);
        driveElements.parentFolderSelect.on('change', updateCreateFolderButton);
        driveElements.createFolderBtn.on('click', handleCreateFolder);

        // File input change event
        driveElements.fileInput.on('change', function(e) {
            const files = e.target.files;
            if (files.length > 0) {
                handleMultipleFileUpload(files);
                // Clear the input so the same file can be selected again
                $(this).val('');
            }
        });

        // Drag and Drop Events
        driveElements.dropZone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('border-primary bg-light');
        });

        driveElements.dropZone.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('border-primary bg-light');
        });

        driveElements.dropZone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('border-primary bg-light');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                handleMultipleFileUpload(files);
            }
        });

        // Click to select files
        driveElements.dropZone.on('click', function(e) {
            if (e.target === this || $(e.target).closest('#dropZoneContent').length) {
                driveElements.fileInput.click();
            }
        });

        // Parent folder management events
        driveElements.refreshFoldersBtn.on('click', loadAvailableFolders);
        driveElements.confirmCreateParentFolder.on('click', function() {
            const folderName = driveElements.parentFolderName.val().trim();
            if (!folderName) {
                showToast('Por favor, digite o nome da pasta', 'error');
                driveElements.parentFolderName.focus();
                return;
            }
            createParentFolder();
        });
        
        // Enter key to create parent folder
        driveElements.parentFolderName.on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                const folderName = $(this).val().trim();
                if (!folderName) {
                    showToast('Por favor, digite o nome da pasta', 'error');
                    $(this).focus();
                    return;
                }
                createParentFolder();
            }
        });

        // Global event delegation for dynamic content
        $(document).on('click', '.delete-file', handleDeleteFile);
        $(document).on('click', '.share-file', handleShareFile);
        
        // Load folders on page load
        loadAvailableFolders();
        
        // Debug function to test API response
        window.debugApiResponse = function() {
            if (!studentFolderId) {
                console.log('No student folder ID available');
                return;
            }
            
            console.log('Testing API response for folder:', studentFolderId);
            
            $.ajax({
                url: '{{ route("admin.files.index") }}',
                method: 'GET',
                data: { folder: studentFolderId },
                success: function(response) {
                    console.log('API Response:', response);
                    console.log('Response type:', typeof response);
                    console.log('Files array:', response.files);
                    console.log('Files count:', response.files ? response.files.length : 'undefined');
                    
                    if (response.files && response.files.length > 0) {
                        console.log('First file:', response.files[0]);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('API Error:', { status, error, response: xhr.responseText });
                }
            });
        };

        // Debug function to test user permissions
        window.debugUserPermissions = function() {
            console.log('Testing user permissions...');
            
            $.ajax({
                url: '{{ route("admin.files.index") }}',
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    console.log('Permission test successful:', response);
                },
                error: function(xhr, status, error) {
                    console.error('Permission test failed:', { 
                        status: status, 
                        error: error, 
                        response: xhr.responseText 
                    });
                    
                    if (xhr.status === 403) {
                        console.error('User does not have google-drive.index permission');
                    } else if (xhr.status === 419) {
                        console.error('CSRF token issue');
                    }
                }
            });
        };

        // Share modal event listeners
        $('#shareUserForm').on('submit', function(e) {
            e.preventDefault();
            const email = $('#shareEmail').val().trim();
            const role = $('#shareRole').val();
            const notify = $('#notifyUser').is(':checked');
            
            if (email && role) {
                shareWithUser(email, role, notify);
            }
        });

        $('#createPublicLinkBtn').on('click', function() {
            const role = $('#publicRole').val();
            createPublicLink(role);
        });

        $('#generateEmbedBtn').on('click', function() {
            const width = $('#embedWidth').val().trim() || '100%';
            const height = $('#embedHeight').val().trim() || '600px';
            generateEmbed(width, height);
        });

        $('#copyPublicLink').on('click', function() {
            const link = $('#publicLinkUrl').val();
            if (link) copyToClipboard(link);
        });

        $('#copyEmbedCode').on('click', function() {
            const code = $('#embedCode').val();
            if (code) copyToClipboard(code);
        });

        // Make functions global for inline onclick handlers
        window.updatePermission = updatePermission;
        window.removePermission = removePermission;

        // Matrícula Functions
        function toggleFields(fields, show) {
            fields.forEach(field => {
                $(field).toggle(show);
            });
        }

        function formatarReal(valor) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(valor);
        }

        function calcularValores() {
            const valorTotal = parseFloat(matriculaElements.valorTotalCurso.val()) || 0;
            const valorMatricula = parseFloat(matriculaElements.valorMatricula.val()) || 0;
            const numeroParcelas = parseInt(matriculaElements.numeroParcelas.val()) || 1;
            const desconto = parseFloat(matriculaElements.desconto.val()) || 0;

            // Cálculo do desconto
            const valorComDesconto = valorTotal * (1 - desconto / 100);

            // Para cartão de crédito, não há parcelas - pagamento único do valor total
            let valorMensalidade = 0;
            let valorTotalComJuros = valorComDesconto;

            if (numeroParcelas > 1) {
                const valorParaParcelar = valorComDesconto - valorMatricula;
                valorMensalidade = valorParaParcelar / numeroParcelas;
                valorTotalComJuros = valorComDesconto;
            }

            // Atualizar displays
            matriculaElements.displays.total.text(formatarReal(valorTotalComJuros));
            matriculaElements.displays.matricula.text(formatarReal(valorMatricula));
            matriculaElements.displays.mensalidade.text(formatarReal(valorMensalidade));

            // Ocultar campo de juros no resumo financeiro (os juros só serão aplicados em parcelas vencidas)
            $('.campo-display-juros').hide();

            // Mostrar detalhamento
            if (numeroParcelas > 1) {
                const detalhes = `
                    Valor total: ${formatarReal(valorTotal)}
                    Desconto: ${desconto}% (${formatarReal(valorTotal - valorComDesconto)})
                    Valor com desconto: ${formatarReal(valorComDesconto)}
                    Matrícula: ${formatarReal(valorMatricula)}
                    ${numeroParcelas}x de ${formatarReal(valorMensalidade)}

                    Total final: ${formatarReal(valorTotalComJuros)}
                `;
                matriculaElements.displays.detalhes.text(detalhes.replace(/\n\s+/g, '\n'));
                $('.campo-detalhes-parcelamento').show();
            } else {
                $('.campo-detalhes-parcelamento').hide();
            }
        }

        // Event Listeners - Matrícula
        matriculaElements.formaPagamento.on('change', function() {
            // Resetar campos e remover required de todos os campos condicionais
            const conditionalFields = [
                '#tipo_boleto', '#valor_total_curso', '#valor_matricula', 
                '#numero_parcelas', '#data_vencimento', '#dia_vencimento', '#desconto', '#percentual_juros'
            ];
            
            conditionalFields.forEach(field => {
                $(field).prop('required', false);
            });
            
            // Resetar campos
            toggleFields([
                '.passo-2', '.passo-3', '.passo-4', '.passo-5', '.passo-6', '.passo-7',
                '.campo-boleto', '.campo-matricula', '.campo-mensalidade',
                '.campo-parcelas', '.campo-desconto', '.campo-vencimento', '.campo-dia-vencimento',
                '.campo-juros-boleto', '.campo-display-juros'
            ], false);

            // Controlar número máximo de parcelas baseado na forma de pagamento
            const numeroParcelasField = $('#numero_parcelas');
            const formaPagamento = $(this).val();
            
            if (formaPagamento === 'cartao_credito') {
                numeroParcelasField.attr('max', '12');
                // Se o valor atual for maior que 12, ajustar para 12
                if (parseInt(numeroParcelasField.val()) > 12) {
                    numeroParcelasField.val(12);
                }
            } else if (formaPagamento === 'boleto') {
                numeroParcelasField.attr('max', '6');
                // Se o valor atual for maior que 6, ajustar para 6
                if (parseInt(numeroParcelasField.val()) > 6) {
                    numeroParcelasField.val(6);
                }
            } else {
                // Para PIX, manter o máximo padrão
                numeroParcelasField.attr('max', '12');
            }

            // Mostrar campos específicos baseado na seleção
            switch(formaPagamento) {
                case 'pix':
                    toggleFields(['.passo-3', '.campo-desconto', '.campo-vencimento', '.campo-dia-vencimento'], true);
                    // Fields are now optional
                    // $('#valor_total_curso').prop('required', true);
                    // $('#dia_vencimento').prop('required', true);
                    break;
                case 'cartao_credito':
                    toggleFields([
                        '.passo-3', '.campo-mensalidade', '.campo-desconto',
                        '.campo-vencimento', '.campo-dia-vencimento', '.campo-display-mensalidade'
                    ], true);
                    // Ocultar campo de parcelas para cartão (parcelamento é feito no checkout do Mercado Pago)
                    toggleFields(['.campo-parcelas'], false);
                    // Fields are now optional
                    // $('#valor_total_curso').prop('required', true);
                    // $('#dia_vencimento').prop('required', true);
                    break;
                case 'boleto':
                    toggleFields(['.passo-2', '.passo-3'], true);
                    // Fields are now optional
                    // $('#tipo_boleto').prop('required', true);
                    // $('#valor_total_curso').prop('required', true);
                    break;
            }
            calcularValores();
        });

        // Event Listener - Payment Gateway
        matriculaElements.paymentGateway.on('change', function() {
            const gateway = $(this).val();
            
            if (gateway === 'mercado_pago') {
                // Mercado Pago: Mostrar campos de configuração, ocultar bank_info e valor_pago
                toggleFields(['.campo-bank-info', '.campo-valor-pago'], false);
                toggleFields(['.campo-forma-pagamento'], true);
                $('#bank_info, #valor_pago').prop('required', false);
                $('#forma_pagamento').prop('required', true);
                
                // Resetar campos para permitir configuração completa
                const currentFormaPagamento = $('#forma_pagamento').val();
                if (currentFormaPagamento) {
                    $('#forma_pagamento').trigger('change');
                }
            } else {
                // Outros bancos: Mostrar bank_info e valor_pago, ocultar configurações
                toggleFields(['.campo-bank-info', '.campo-valor-pago'], true);
                toggleFields([
                    '.campo-forma-pagamento', '.passo-2', '.passo-3', '.passo-4', '.passo-5', '.passo-6', '.passo-7',
                    '.campo-boleto', '.campo-matricula', '.campo-mensalidade',
                    '.campo-parcelas', '.campo-desconto', '.campo-vencimento', '.campo-dia-vencimento',
                    '.campo-juros-boleto', '.campo-display-juros', '.campo-display-mensalidade'
                ], false);
                $('#bank_info').prop('required', true);
                $('#forma_pagamento, #valor_pago').prop('required', false);
                
                // Limpar campos não necessários
                $('#forma_pagamento, #tipo_boleto, #valor_matricula, #numero_parcelas, #desconto, #percentual_juros').val('');
            }
        });

        matriculaElements.tipoBoleto.on('change', function() {
            if (matriculaElements.formaPagamento.val() === 'boleto') {
                // Remover required de campos que podem ser ocultados
                const conditionalBoletoFields = [
                    '#valor_matricula', '#numero_parcelas', '#data_vencimento', '#dia_vencimento',
                    '#desconto', '#percentual_juros'
                ];
                
                conditionalBoletoFields.forEach(field => {
                    $(field).prop('required', false);
                });
                
                toggleFields([
                    '.campo-matricula', '.campo-mensalidade', '.campo-parcelas',
                    '.campo-desconto', '.campo-vencimento', '.campo-dia-vencimento', '.campo-juros-boleto',
                    '.campo-display-juros', '.campo-aviso-juros'
                ], false);

                if ($(this).val() === 'avista') {
                    toggleFields(['.campo-desconto', '.campo-vencimento', '.campo-dia-vencimento'], true);
                    // Fields are now optional
                    // $('#dia_vencimento').prop('required', true);
                } else if ($(this).val() === 'parcelado') {
                    toggleFields([
                        '.campo-matricula', '.campo-mensalidade', '.campo-parcelas',
                        '.campo-desconto', '.campo-vencimento', '.campo-dia-vencimento', '.campo-juros-boleto',
                        '.campo-display-juros', '.campo-aviso-juros'
                    ], true);
                    // Fields are now optional
                    // $('#valor_matricula').prop('required', true);
                    // $('#dia_vencimento').prop('required', true);
                }
                calcularValores();
            }
        });

        // Adicionar listeners para recalcular valores
        ['valor_total_curso', 'valor_matricula', 'numero_parcelas', 'desconto', 'percentual_juros'].forEach(id => {
            $(`#${id}`).on('input change', calcularValores);
        });

        // Lógica para escola parceira
        function toggleCampoParceiro() {
            const isChecked = $('#escola_parceira').is(':checked');
            const campoParceiro = $('#campo_parceiro');
            const selectParceiro = $('#parceiro_id');
            
            if (isChecked) {
                campoParceiro.slideDown(300);
                // Fields are now optional
                // selectParceiro.prop('required', true);
            } else {
                campoParceiro.slideUp(300);
                selectParceiro.prop('required', false).val('');
            }
        }

        $('#escola_parceira').on('change', toggleCampoParceiro);
        
        // Inicializar estado
        toggleCampoParceiro();

        // Validação customizada do formulário antes do submit
        $('form').on('submit', function(e) {
            let isValid = true;
            const errors = [];
            
            // Validar apenas campos visíveis e required
            $(this).find('input[required], select[required]').each(function() {
                const $field = $(this);
                const $container = $field.closest('.col-md-3, .col-md-6, .col-md-12');
                
                // Verificar se o campo está visível
                if ($container.is(':visible') && $field.is(':visible')) {
                    if (!$field.val() || $field.val().trim() === '') {
                        isValid = false;
                        const fieldName = $field.attr('name') || $field.attr('id');
                        const label = $field.closest('.form-group, .mb-3, .col-md-3, .col-md-6, .col-md-12').find('label').text() || fieldName;
                        errors.push(`Campo "${label}" é obrigatório`);
                        
                        // Destacar campo com erro
                        $field.addClass('is-invalid');
                    } else {
                        $field.removeClass('is-invalid');
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast('Por favor, preencha todos os campos obrigatórios:\n' + errors.join('\n'), 'error');
                
                // Focar no primeiro campo com erro
                $(this).find('.is-invalid').first().focus();
                return false;
            }
        });

        // Sistema de progresso da matrícula
        function updateMatriculaProgress() {
            // Definir campos essenciais (peso 2)
            const camposEssenciais = [
                'nome_completo', 'cpf', 'telefone_celular', 'email', 
                'curso', 'forma_pagamento'
            ];
            
            // Definir campos importantes (peso 1)
            const camposImportantes = [
                'data_nascimento', 'sexo', 'rg', 'orgao_emissor', 
                'estado_civil', 'nacionalidade', 'naturalidade',
                'cep', 'logradouro', 'numero', 'bairro', 'cidade', 'estado',
                'telefone_fixo', 'nome_mae', 'nome_pai',
                'modalidade', 'ultima_serie', 'ano_conclusao', 'escola_origem'
            ];

            // Contar campos preenchidos
            let camposEssenciaisPreenchidos = 0;
            let totalCamposEssenciais = camposEssenciais.length;
            
            let camposImportantesPreenchidos = 0;
            let totalCamposImportantes = camposImportantes.length;
            
            // Verificar campos essenciais
            camposEssenciais.forEach(campo => {
                const valor = $(`#${campo}`).val();
                if (valor && valor.trim() !== '') {
                    camposEssenciaisPreenchidos++;
                }
            });
            
            // Verificar campos importantes
            camposImportantes.forEach(campo => {
                const valor = $(`#${campo}`).val();
                if (valor && valor.trim() !== '') {
                    camposImportantesPreenchidos++;
                }
            });
            
            // Calcular progresso (campos essenciais têm peso 2, importantes peso 1)
            const pesoTotal = (totalCamposEssenciais * 2) + totalCamposImportantes;
            const pesoPreenchido = (camposEssenciaisPreenchidos * 2) + camposImportantesPreenchidos;
            
            // Calcular porcentagem de progresso
            const porcentagem = Math.round((pesoPreenchido / pesoTotal) * 100);
            
            // Atualizar barra de progresso
            const $progressBar = $('.progress-bar');
            $progressBar.css('width', porcentagem + '%');
            $progressBar.attr('aria-valuenow', porcentagem);
            
            // Atualizar cor da barra de progresso
            if (porcentagem < 50) {
                $progressBar.removeClass('bg-warning bg-success').addClass('bg-danger');
            } else if (porcentagem < 80) {
                $progressBar.removeClass('bg-danger bg-success').addClass('bg-warning');
            } else {
                $progressBar.removeClass('bg-danger bg-warning').addClass('bg-success');
            }
            
            // Atualizar texto informativo
            let mensagem = '';
            if (porcentagem < 50) {
                mensagem = `Perfil incompleto (${porcentagem}%) - Preencha mais campos para melhorar o cadastro`;
            } else if (porcentagem < 80) {
                mensagem = `Perfil parcialmente completo (${porcentagem}%) - Continue preenchendo`;
            } else if (porcentagem < 100) {
                mensagem = `Perfil quase completo (${porcentagem}%) - Faltam poucos campos`;
            } else {
                mensagem = `Perfil 100% completo - Todos os campos preenchidos!`;
            }
            
            $('.progress').siblings('small').text(mensagem);
            
            return {
                porcentagem,
                camposEssenciaisPreenchidos,
                totalCamposEssenciais,
                camposImportantesPreenchidos,
                totalCamposImportantes
            };
        }
        
        // Adicionar evento de input para todos os campos do formulário
        $('form input, form select, form textarea').on('input change', function() {
            updateMatriculaProgress();
        });
        
        // Atualizar progresso ao carregar a página
        updateMatriculaProgress();
        
        // Adicionar botão para mostrar campos faltantes
        $('.profile-progress-container .card-body .row .col-md-4').html(`
            <button type="button" class="btn btn-outline-info btn-sm float-end" id="showMissingFieldsBtn">
                <i class="fas fa-list-check me-1"></i>
                Campos Faltantes
            </button>
        `);
        
        // Modal para mostrar campos faltantes
        $('body').append(`
            <div class="modal fade" id="missingFieldsModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-list-check me-2"></i>
                                Campos Faltantes
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Preencha os campos abaixo para completar o perfil do aluno
                            </div>
                            <div id="missingEssentialFields">
                                <h6 class="text-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Campos Essenciais
                                </h6>
                                <ul class="list-group mb-3" id="essentialFieldsList"></ul>
                            </div>
                            <div id="missingImportantFields">
                                <h6 class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Campos Importantes
                                </h6>
                                <ul class="list-group" id="importantFieldsList"></ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        // Função para mostrar campos faltantes
        $('#showMissingFieldsBtn').on('click', function() {
            // Definir campos essenciais (peso 2)
            const camposEssenciais = [
                { id: 'nome_completo', label: 'Nome Completo' },
                { id: 'cpf', label: 'CPF' },
                { id: 'telefone_celular', label: 'Telefone Celular' },
                { id: 'email', label: 'E-mail' },
                { id: 'curso', label: 'Curso' },
                { id: 'forma_pagamento', label: 'Forma de Pagamento' }
            ];
            
            // Definir campos importantes (peso 1)
            const camposImportantes = [
                { id: 'data_nascimento', label: 'Data de Nascimento' },
                { id: 'sexo', label: 'Sexo' },
                { id: 'rg', label: 'RG' },
                { id: 'orgao_emissor', label: 'Órgão Emissor' },
                { id: 'estado_civil', label: 'Estado Civil' },
                { id: 'nacionalidade', label: 'Nacionalidade' },
                { id: 'naturalidade', label: 'Naturalidade' },
                { id: 'cep', label: 'CEP' },
                { id: 'logradouro', label: 'Logradouro' },
                { id: 'numero', label: 'Número' },
                { id: 'bairro', label: 'Bairro' },
                { id: 'cidade', label: 'Cidade' },
                { id: 'estado', label: 'Estado' },
                { id: 'telefone_fixo', label: 'Celular (Opcional)' },
                { id: 'nome_mae', label: 'Nome da Mãe' },
                { id: 'nome_pai', label: 'Nome do Pai' },
                { id: 'modalidade', label: 'Modalidade' },
                { id: 'ultima_serie', label: 'Última Série Cursada' },
                { id: 'ano_conclusao', label: 'Ano de Conclusão' },
                { id: 'escola_origem', label: 'Escola de Origem' }
            ];
            
            // Limpar listas
            $('#essentialFieldsList').empty();
            $('#importantFieldsList').empty();
            
            // Verificar campos essenciais faltantes
            let camposEssenciaisFaltantes = 0;
            camposEssenciais.forEach(campo => {
                const valor = $(`#${campo.id}`).val();
                if (!valor || valor.trim() === '') {
                    camposEssenciaisFaltantes++;
                    $('#essentialFieldsList').append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ${campo.label}
                            <button class="btn btn-sm btn-outline-primary goto-field" data-field="${campo.id}">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </li>
                    `);
                }
            });
            
            // Verificar campos importantes faltantes
            let camposImportantesFaltantes = 0;
            camposImportantes.forEach(campo => {
                const valor = $(`#${campo.id}`).val();
                if (!valor || valor.trim() === '') {
                    camposImportantesFaltantes++;
                    $('#importantFieldsList').append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ${campo.label}
                            <button class="btn btn-sm btn-outline-primary goto-field" data-field="${campo.id}">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </li>
                    `);
                }
            });
            
            // Mostrar/ocultar seções
            if (camposEssenciaisFaltantes === 0) {
                $('#missingEssentialFields').hide();
            } else {
                $('#missingEssentialFields').show();
            }
            
            if (camposImportantesFaltantes === 0) {
                $('#missingImportantFields').hide();
            } else {
                $('#missingImportantFields').show();
            }
            
            // Se não houver campos faltantes
            if (camposEssenciaisFaltantes === 0 && camposImportantesFaltantes === 0) {
                $('#missingFieldsModal .modal-body').html(`
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Parabéns!</strong> Todos os campos foram preenchidos.
                    </div>
                `);
            }
            
            // Mostrar modal
            const missingFieldsModal = new bootstrap.Modal(document.getElementById('missingFieldsModal'));
            missingFieldsModal.show();
        });
        
        // Evento para navegar até o campo
        $(document).on('click', '.goto-field', function() {
            const fieldId = $(this).data('field');
            const $field = $(`#${fieldId}`);
            
            // Fechar modal
            bootstrap.Modal.getInstance(document.getElementById('missingFieldsModal')).hide();
            
            // Scroll até o campo
            $('html, body').animate({
                scrollTop: $field.offset().top - 100
            }, 500);
            
            // Destacar o campo
            $field.focus().addClass('highlight-field');
            setTimeout(() => {
                $field.removeClass('highlight-field');
            }, 3000);
        });
        
        // Adicionar estilo para destacar campo
        $('<style>')
            .text(`
                .highlight-field {
                    box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
                    border-color: #007bff !important;
                    background-color: rgba(0, 123, 255, 0.1);
                    transition: all 0.3s;
                }
            `)
            .appendTo('head');
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Máscaras para campos usando IMask
        const cpfMask = IMask(document.getElementById('cpf'), {
            mask: '000.000.000-00'
        });
        
        const rgMask = IMask(document.getElementById('rg'), {
            mask: '00.000.000-0'
        });
        
        const cepMask = IMask(document.getElementById('cep'), {
            mask: '00000-000'
        });
        
        const telefoneFixoMask = IMask(document.getElementById('telefone_fixo'), {
            mask: '(00) 00000-0000'
        });
        
        const telefoneCelularMask = IMask(document.getElementById('telefone_celular'), {
            mask: '(00) 00000-0000'
        });

        // Busca automática de endereço por CEP
        const cepInput = document.getElementById('cep');
        const cepLoading = document.getElementById('cep-loading');
        
        cepInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            
            if (cep.length === 8) {
                // Mostrar loading
                cepLoading.style.display = 'inline-block';
                
                // Limpar campos de endereço
                document.getElementById('logradouro').value = '';
                document.getElementById('bairro').value = '';
                document.getElementById('cidade').value = '';
                document.getElementById('estado').value = '';
                
                // Fazer requisição para API
                fetch(`/api/cep/${cep}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && !data.erro && !data.error) {
                                                         // Preencher campos automaticamente
                             document.getElementById('logradouro').value = data.logradouro || '';
                             document.getElementById('bairro').value = data.bairro || '';
                             document.getElementById('cidade').value = data.localidade || '';
                             
                             // Selecionar o estado no select
                             const estadoSelect = document.getElementById('estado');
                             if (estadoSelect && data.uf) {
                                 estadoSelect.value = data.uf;
                             }
                            
                            // Focar no campo número
                            document.getElementById('numero').focus();
                            
                            // Mostrar mensagem de sucesso
                            showToast('Endereço encontrado!', 'success');
                        } else {
                            showToast('CEP não encontrado. Verifique o código digitado.', 'warning');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar CEP:', error);
                        showToast('Erro ao buscar CEP. Tente novamente.', 'error');
                    })
                    .finally(() => {
                        // Ocultar loading
                        cepLoading.style.display = 'none';
                    });
            }
        });

        // Função para mostrar toast notifications
        function showToast(message, type = 'info') {
            // Mapear tipos para classes Bootstrap
            const typeMap = {
                'success': 'success',
                'warning': 'warning', 
                'error': 'danger',
                'info': 'info'
            };
            
            const iconMap = {
                'success': 'check-circle',
                'warning': 'exclamation-triangle',
                'error': 'exclamation-circle',
                'info': 'info-circle'
            };
            
            const bootstrapType = typeMap[type] || 'info';
            const icon = iconMap[type] || 'info-circle';
            
            // Criar elemento do toast
            const toast = document.createElement('div');
            toast.className = `alert alert-${bootstrapType} alert-dismissible fade show custom-toast`;
            
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${icon} me-2"></i>
                    <span class="flex-grow-1">${message}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Adicionar ao body
            document.body.appendChild(toast);
            
            // Animar entrada
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            // Remover automaticamente após 5 segundos
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }

        // Validação de CPF em tempo real
        cpfMask.on('accept', function() {
            const cpf = cpfMask.value.replace(/\D/g, '');
            if (cpf.length === 11) {
                if (!isValidCPF(cpf)) {
                    document.getElementById('cpf').classList.add('is-invalid');
                    showToast('CPF inválido. Verifique os números digitados.', 'error');
                } else {
                    document.getElementById('cpf').classList.remove('is-invalid');
                }
            }
        });

        // Função para validar CPF
        function isValidCPF(cpf) {
            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                return false;
            }
            
            let sum = 0;
            for (let i = 0; i < 9; i++) {
                sum += parseInt(cpf.charAt(i)) * (10 - i);
            }
            let remainder = (sum * 10) % 11;
            if (remainder === 10 || remainder === 11) remainder = 0;
            if (remainder !== parseInt(cpf.charAt(9))) return false;
            
            sum = 0;
            for (let i = 0; i < 10; i++) {
                sum += parseInt(cpf.charAt(i)) * (11 - i);
            }
            remainder = (sum * 10) % 11;
            if (remainder === 10 || remainder === 11) remainder = 0;
            if (remainder !== parseInt(cpf.charAt(10))) return false;
            
            return true;
        }

        // Validação de telefone celular
        telefoneCelularMask.on('accept', function() {
            const telefone = telefoneCelularMask.value.replace(/\D/g, '');
            if (telefone.length === 11) {
                // Verificar se o primeiro dígito do celular é 9
                if (telefone.charAt(2) !== '9') {
                    document.getElementById('telefone_celular').classList.add('is-invalid');
                    showToast('Número de celular inválido. Deve começar com 9.', 'error');
                } else {
                    document.getElementById('telefone_celular').classList.remove('is-invalid');
                }
            }
        });

        // ===== VALIDAÇÃO AJAX DO FORMULÁRIO =====

        // Função para limpar erros de validação
        function clearValidationErrors() {
            // Remover classes de erro
            $('.is-invalid').removeClass('is-invalid');
            
            // Remover mensagens de erro
            $('.invalid-feedback').remove();
            
            // Ocultar resumo de erros
            $('#errorSummary').addClass('d-none');
            
            // Remover toasts de erro (exceto o resumo)
            $('.alert:not(#errorSummary)').remove();
        }

        // Função para exibir erros de validação
        function displayValidationErrors(errors) {
            console.log('Exibindo erros de validação:', errors);
            
            let firstErrorField = null;
            
            Object.keys(errors).forEach(field => {
                const fieldElement = $(`[name="${field}"]`);
                
                if (fieldElement.length > 0) {
                    // Adicionar classe de erro
                    fieldElement.addClass('is-invalid');
                    
                    // Remover mensagem de erro anterior se existir
                    fieldElement.siblings('.invalid-feedback').remove();
                    
                    // Adicionar mensagem de erro
                    const errorMessage = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                    const errorDiv = $(`<div class="invalid-feedback">${errorMessage}</div>`);
                    
                    // Inserir após o campo
                    fieldElement.after(errorDiv);
                    
                    // Marcar o primeiro erro para scroll
                    if (!firstErrorField) {
                        firstErrorField = fieldElement;
                    }
                    
                    console.log(`Erro no campo ${field}:`, errorMessage);
                } else {
                    console.warn(`Campo não encontrado: ${field}`);
                }
            });
            
            // Scroll para o primeiro erro
            if (firstErrorField) {
                $('html, body').animate({
                    scrollTop: firstErrorField.offset().top - 100
                }, 500);
            }
            
            // Mostrar resumo de erros
            const errorCount = Object.keys(errors).length;
            if (errorCount > 0) {
                const errorList = $('#errorList');
                errorList.empty();
                
                Object.keys(errors).forEach(field => {
                    const errorMessage = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                    const fieldLabel = $(`[name="${field}"]`).closest('.col-md-6, .col-md-4, .col-md-3, .col-md-12, .col-12').find('label').text() || field;
                    errorList.append(`<div>• <strong>${fieldLabel}:</strong> ${errorMessage}</div>`);
                });
                
                $('#errorSummary').removeClass('d-none');
                
                // Scroll para o resumo de erros
                $('html, body').animate({
                    scrollTop: $('#errorSummary').offset().top - 20
                }, 500);
            }
        }

        // Função para mostrar/ocultar loading no botão
        function showSubmitLoading(show) {
            const submitBtn = $('#submitBtn');
            const btnText = submitBtn.find('.btn-text');
            const btnLoading = submitBtn.find('.btn-loading');
            
            if (show) {
                submitBtn.prop('disabled', true);
                btnText.addClass('d-none');
                btnLoading.removeClass('d-none');
            } else {
                submitBtn.prop('disabled', false);
                btnText.removeClass('d-none');
                btnLoading.addClass('d-none');
            }
        }

        // Atualizar barra de progresso baseada nos campos preenchidos
        function updateProgressBar() {
            const requiredFields = [
                'nome_completo', 'data_nascimento', 'sexo', 'cpf', 'rg', 'orgao_emissor',
                'estado_civil', 'nacionalidade', 'naturalidade', 'cep', 'logradouro',
                'numero', 'bairro', 'cidade', 'estado', 'telefone_celular', 'email',
                'nome_mae', 'nome_pai', 'escolaridade', 'curso_interesse', 'forma_pagamento'
            ];
            
            let filledFields = 0;
            let totalFields = requiredFields.length;
            
            requiredFields.forEach(field => {
                const element = $(`[name="${field}"]`);
                if (element.length > 0) {
                    const value = element.val();
                    if (value && value.trim() !== '') {
                        filledFields++;
                    }
                }
            });
            
            const progress = Math.round((filledFields / totalFields) * 100);
            $('.progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
            
            // Atualizar texto da barra de progresso
            $('.profile-progress-container small').text(
                `${filledFields} de ${totalFields} campos preenchidos`
            );
        }

        // Atualizar progresso quando campos são alterados
        $('input, select, textarea').on('input change', function() {
            updateProgressBar();
        });

        // Inicializar barra de progresso
        updateProgressBar();
        
        // ===== VALIDAÇÃO EM TEMPO REAL =====
        
        // Validar campos obrigatórios em tempo real
        $('input[required], select[required], textarea[required]').on('blur', function() {
            const field = $(this);
            const value = field.val();
            
            if (!value || value.trim() === '') {
                field.addClass('is-invalid');
                if (!field.siblings('.invalid-feedback').length) {
                    const fieldName = field.closest('.col-md-6, .col-md-4, .col-md-3, .col-md-12, .col-12').find('label').text() || 'Este campo';
                    field.after(`<div class="invalid-feedback">${fieldName} é obrigatório</div>`);
                }
            } else {
                field.removeClass('is-invalid');
                field.siblings('.invalid-feedback').remove();
            }
        });
        
        // Validar email em tempo real
        $('input[type="email"]').on('blur', function() {
            const field = $(this);
            const value = field.val();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (value && !emailRegex.test(value)) {
                field.addClass('is-invalid');
                if (!field.siblings('.invalid-feedback').length) {
                    field.after('<div class="invalid-feedback">Digite um email válido</div>');
                }
            } else if (value) {
                field.removeClass('is-invalid');
                field.siblings('.invalid-feedback').remove();
            }
        });
        
        // ===== SALVAMENTO AUTOMÁTICO DESABILITADO =====
        // Desabilitado para evitar conflitos de CSRF
        
        // Função para carregar dados salvos (desabilitada)
        function loadFormData() {
            // Desabilitado para evitar conflitos de CSRF
            console.log('Carregamento automático de dados desabilitado');
        }
        
        // Carregar dados salvos ao carregar a página
        loadFormData();
        
        // Inicializar lógica de parcelas baseado na forma de pagamento
        function inicializarParcelas() {
            const formaPagamento = $('#forma_pagamento').val();
            const numeroParcelasField = $('#numero_parcelas');
            
            if (formaPagamento === 'cartao_credito') {
                numeroParcelasField.attr('max', '12');
                if (parseInt(numeroParcelasField.val()) > 12) {
                    numeroParcelasField.val(12);
                }
            } else if (formaPagamento === 'boleto') {
                numeroParcelasField.attr('max', '6');
                if (parseInt(numeroParcelasField.val()) > 6) {
                    numeroParcelasField.val(6);
                }
            } else {
                numeroParcelasField.attr('max', '12');
            }
        }
        
        // Chamar inicialização após carregar dados
        setTimeout(inicializarParcelas, 100);
        
        // Limpar dados salvos após sucesso
        function clearSavedData() {
            localStorage.removeItem('matricula_form_data');
            console.log('Dados salvos removidos do localStorage');
        }
        
        // Modificar a função de sucesso para limpar dados salvos
        const originalSubmitHandler = $('#matriculaForm').data('submit-handler');
        $('#matriculaForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            
            // Limpar erros anteriores
            clearValidationErrors();
            
            // Mostrar loading no botão
            showSubmitLoading(true);
            
            // Coletar dados do formulário
            const formData = new FormData(this);
            
            // Garantir que o token CSRF seja incluído
            const csrfToken = $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content');
            console.log('Token CSRF encontrado:', csrfToken ? 'Sim' : 'Não');
            
            if (csrfToken) {
                formData.set('_token', csrfToken);
                console.log('Token CSRF adicionado ao FormData');
            } else {
                console.error('Token CSRF não encontrado');
                showToast('Erro: Token de segurança não encontrado. Recarregue a página.', 'error');
                showSubmitLoading(false);
                return;
            }
            
            // Fazer requisição AJAX
            console.log('Enviando requisição para:', $(this).attr('action'));
            console.log('Token CSRF no header:', csrfToken);
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        // Limpar dados salvos
                        clearSavedData();
                        
                        // Sucesso - redirecionar ou mostrar mensagem
                        showToast('Matrícula criada com sucesso!', 'success');
                        
                        // Redirecionar após 2 segundos
                        setTimeout(() => {
                            try {
                                const redirectUrl = response.redirect_url || '{{ route("admin.matriculas.index") }}';
                                console.log('Redirecionando para:', redirectUrl);
                                window.location.href = redirectUrl;
                            } catch (e) {
                                console.error('Erro ao redirecionar:', e);
                                window.location.href = '{{ route("admin.matriculas.index") }}';
                            }
                        }, 2000);
                    } else {
                        // Erro geral
                        showToast(response.message || 'Erro ao criar matrícula', 'error');
                        showSubmitLoading(false);
                    }
                },
                error: function(xhr, status, error) {
                    showSubmitLoading(false);
                    
                    console.log('Erro na requisição:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    
                    if (xhr.status === 422) {
                        // Erros de validação
                        try {
                            const response = xhr.responseJSON;
                            if (response && response.errors) {
                                displayValidationErrors(response.errors);
                                showToast('Por favor, corrija os erros destacados no formulário', 'warning');
                            } else {
                                showToast('Erro de validação: ' + (response.message || 'Dados inválidos'), 'error');
                            }
                        } catch (e) {
                            console.error('Erro ao processar resposta de validação:', e);
                            showToast('Erro ao processar resposta do servidor', 'error');
                        }
                    } else if (xhr.status === 500) {
                        // Erro interno do servidor
                        showToast('Erro interno do servidor. Tente novamente ou contate o suporte.', 'error');
                    } else if (xhr.status === 0) {
                        // Erro de conexão
                        showToast('Erro de conexão. Verifique sua internet e tente novamente.', 'error');
                    } else {
                        // Outros erros
                        showToast('Erro inesperado (Status: ' + xhr.status + '). Tente novamente.', 'error');
                    }
                }
            });
        });
        
        console.log('Scripts da página de matrícula carregados com sucesso');
    });
</script>
@endpush
@endsection 