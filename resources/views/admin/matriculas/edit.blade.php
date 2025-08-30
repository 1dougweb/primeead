@extends('layouts.admin')

@section('title', 'Editar Matrícula')

@push('styles')
<style>
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h3 class="mt-4">
        <i class="fas fa-edit me-2"></i>
        Editar Matrícula
    </h3>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.matriculas.index') }}">Matrículas</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.matriculas.show', $matricula) }}">{{ $matricula->nome_completo }}</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>

    <!-- Barra de Progresso do Perfil -->
    <x-profile-progress :progress="$matricula->getProfileProgress()" />

    <form method="POST" action="{{ route('admin.matriculas.update', $matricula) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="google_drive_folder_id" id="google_drive_folder_id" value="{{ $matricula->google_drive_folder_id }}">

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
                                       value="{{ old('nome_completo', $matricula->nome_completo) }}" 
                                       required>
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
                                       value="{{ old('data_nascimento', $matricula->data_nascimento ? $matricula->data_nascimento->format('Y-m-d') : '') }}">
                                @error('data_nascimento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="cpf" class="form-label">CPF</label>
                                <input type="text" 
                                       class="form-control @error('cpf') is-invalid @enderror" 
                                       id="cpf" 
                                       name="cpf" 
                                       value="{{ old('cpf', $matricula->cpf) }}" 
                                       placeholder="000.000.000-00"
                                       required>
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
                                       value="{{ old('rg', $matricula->rg) }}" 
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
                                       value="{{ old('orgao_emissor', $matricula->orgao_emissor) }}">
                                @error('orgao_emissor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="sexo" class="form-label">Sexo</label>
                                <select class="form-select @error('sexo') is-invalid @enderror" id="sexo" name="sexo">
                                    <option value="">Selecione</option>
                                    <option value="M" {{ old('sexo', $matricula->sexo) == 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo', $matricula->sexo) == 'F' ? 'selected' : '' }}>Feminino</option>
                                    <option value="O" {{ old('sexo', $matricula->sexo) == 'O' ? 'selected' : '' }}>Outro</option>
                                </select>
                                @error('sexo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="estado_civil" class="form-label">Estado Civil</label>
                                <select class="form-select @error('estado_civil') is-invalid @enderror" id="estado_civil" name="estado_civil">
                                    <option value="">Selecione</option>
                                    <option value="solteiro" {{ old('estado_civil', $matricula->estado_civil) == 'solteiro' ? 'selected' : '' }}>Solteiro</option>
                                    <option value="casado" {{ old('estado_civil', $matricula->estado_civil) == 'casado' ? 'selected' : '' }}>Casado</option>
                                    <option value="divorciado" {{ old('estado_civil', $matricula->estado_civil) == 'divorciado' ? 'selected' : '' }}>Divorciado</option>
                                    <option value="viuvo" {{ old('estado_civil', $matricula->estado_civil) == 'viuvo' ? 'selected' : '' }}>Viúvo</option>
                                    <option value="outro" {{ old('estado_civil', $matricula->estado_civil) == 'outro' ? 'selected' : '' }}>Outro</option>
                                </select>
                                @error('estado_civil')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nacionalidade" class="form-label">Nacionalidade</label>
                                <input type="text" 
                                       class="form-control @error('nacionalidade') is-invalid @enderror" 
                                       id="nacionalidade" 
                                       name="nacionalidade" 
                                       value="{{ old('nacionalidade', $matricula->nacionalidade) }}">
                                @error('nacionalidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="naturalidade" class="form-label">Naturalidade</label>
                                <input type="text" 
                                       class="form-control @error('naturalidade') is-invalid @enderror" 
                                       id="naturalidade" 
                                       name="naturalidade" 
                                       value="{{ old('naturalidade', $matricula->naturalidade) }}">
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
                                           value="{{ old('cep', $matricula->cep) }}" 
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
                                       value="{{ old('logradouro', $matricula->logradouro) }}">
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
                                       value="{{ old('numero', $matricula->numero) }}">
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
                                       value="{{ old('complemento', $matricula->complemento) }}">
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
                                       value="{{ old('bairro', $matricula->bairro) }}">
                                @error('bairro')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" 
                                       class="form-control @error('cidade') is-invalid @enderror" 
                                       id="cidade" 
                                       name="cidade" 
                                       value="{{ old('cidade', $matricula->cidade) }}">
                                @error('cidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado">
                                    <option value="">UF</option>
                                    @foreach(['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $uf)
                                        <option value="{{ $uf }}" {{ old('estado', $matricula->estado) == $uf ? 'selected' : '' }}>{{ $uf }}</option>
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
                                       value="{{ old('telefone_fixo', $matricula->telefone_fixo) }}"
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
                                       value="{{ old('telefone_celular', $matricula->telefone_celular) }}" 
                                       placeholder="(00) 00000-0000"
                                       required>
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
                                       value="{{ old('email', $matricula->email) }}" 
                                       required>
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
                                       value="{{ old('nome_mae', $matricula->nome_mae) }}">
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
                                       value="{{ old('nome_pai', $matricula->nome_pai) }}">
                                @error('nome_pai')
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
                            <div class="col-md-4">
                                <label for="modalidade" class="form-label">Modalidade</label>
                                <select class="form-select @error('modalidade') is-invalid @enderror" id="modalidade" name="modalidade">
                                    <option value="">Selecione</option>
                                    @foreach($formSettings['modalidades'] ?? [] as $modalidade)
                                        <option value="{{ $modalidade }}" {{ old('modalidade', $matricula->modalidade) == $modalidade ? 'selected' : '' }}>{{ $modalidade }}</option>
                                    @endforeach
                                </select>
                                @error('modalidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="curso" class="form-label">Curso</label>
                                <select class="form-select @error('curso') is-invalid @enderror" id="curso" name="curso">
                                    <option value="">Selecione</option>
                                    @foreach($formSettings['cursos'] ?? [] as $curso)
                                        <option value="{{ $curso }}" {{ old('curso', $matricula->curso) == $curso ? 'selected' : '' }}>{{ $curso }}</option>
                                    @endforeach
                                </select>
                                @error('curso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="ultima_serie" class="form-label">Última Série Concluída</label>
                                <select class="form-select @error('ultima_serie') is-invalid @enderror" id="ultima_serie" name="ultima_serie">
                                    <option value="">Selecione</option>
                                    @foreach($formSettings['series'] ?? [] as $serie)
                                        <option value="{{ $serie }}" {{ old('ultima_serie', $matricula->ultima_serie) == $serie ? 'selected' : '' }}>{{ $serie }}</option>
                                    @endforeach
                                </select>
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
                                       min="1950" 
                                       max="{{ date('Y') }}" 
                                       value="{{ old('ano_conclusao', $matricula->ano_conclusao) }}">
                                @error('ano_conclusao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-8">
                                <label for="escola_origem" class="form-label">Escola de Origem</label>
                                <input type="text" 
                                       class="form-control @error('escola_origem') is-invalid @enderror" 
                                       id="escola_origem" 
                                       name="escola_origem" 
                                       value="{{ old('escola_origem', $matricula->escola_origem) }}">
                                @error('escola_origem')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados Financeiros -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-dollar-sign me-2"></i>
                            Dados Financeiros
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Gateway de Pagamento -->
                            <div class="col-md-3 mb-3">
                                <label for="payment_gateway" class="form-label">Gateway de Pagamento</label>
                                <select class="form-select @error('payment_gateway') is-invalid @enderror" id="payment_gateway" name="payment_gateway">
                                    <option value="mercado_pago" {{ old('payment_gateway', $matricula->payment_gateway ?? 'mercado_pago') == 'mercado_pago' ? 'selected' : '' }}>Mercado Pago</option>
                                    <option value="asas" {{ old('payment_gateway', $matricula->payment_gateway) == 'asas' ? 'selected' : '' }}>Banco Asas</option>
                                    <option value="infiny_pay" {{ old('payment_gateway', $matricula->payment_gateway) == 'infiny_pay' ? 'selected' : '' }}>Banco Infiny Pay</option>
                                    <option value="cora" {{ old('payment_gateway', $matricula->payment_gateway) == 'cora' ? 'selected' : '' }}>Banco Cora</option>
                                </select>
                                @error('payment_gateway')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Forma de Pagamento (apenas para Mercado Pago) -->
                            <div class="col-md-3 mb-3 campo-forma-pagamento">
                                <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                                <select class="form-select @error('forma_pagamento') is-invalid @enderror" id="forma_pagamento" name="forma_pagamento">
                                    <option value="">Selecione</option>
                                    <option value="pix" {{ old('forma_pagamento', $matricula->forma_pagamento) == 'pix' ? 'selected' : '' }}>PIX</option>
                                    <option value="cartao_credito" {{ old('forma_pagamento', $matricula->forma_pagamento) == 'cartao_credito' ? 'selected' : '' }}>Cartão de Crédito</option>
                                    <option value="boleto" {{ old('forma_pagamento', $matricula->forma_pagamento) == 'boleto' ? 'selected' : '' }}>Boleto</option>
                                </select>
                                @error('forma_pagamento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status (visível para todos os gateways) -->
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="">Selecione</option>
                                    <option value="pre_matricula" {{ old('status', $matricula->status) == 'pre_matricula' ? 'selected' : '' }}>Pré-Matrícula</option>
                                    <option value="matricula_confirmada" {{ old('status', $matricula->status) == 'matricula_confirmada' ? 'selected' : '' }}>Matrícula Confirmada</option>
                                    <option value="cancelada" {{ old('status', $matricula->status) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                    <option value="trancada" {{ old('status', $matricula->status) == 'trancada' ? 'selected' : '' }}>Trancada</option>
                                    <option value="concluida" {{ old('status', $matricula->status) == 'concluida' ? 'selected' : '' }}>Concluída</option>
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
                                           class="form-control @error('valor_pago') is-invalid @enderror" 
                                           id="valor_pago" 
                                           name="valor_pago" 
                                           step="0.01" 
                                           min="0" 
                                           value="{{ old('valor_pago', $matricula->valor_pago) }}"
                                           placeholder="0,00">
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Valor que o aluno efetivamente pagou.
                                </small>
                                @error('valor_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Campo de Informações Bancárias (apenas para gateways manuais) -->
                            <div class="col-md-12 mb-3 campo-bank-info" style="display: none;">
                                <label for="bank_info" class="form-label">Informações do Banco</label>
                                <textarea class="form-control @error('bank_info') is-invalid @enderror" id="bank_info" name="bank_info" rows="3" 
                                          placeholder="Cole aqui o link de pagamento ou informações bancárias...">{{ old('bank_info', $matricula->bank_info) }}</textarea>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Insira o link de pagamento ou dados bancários para este gateway.
                                </small>
                                @error('bank_info')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Passo 2: Tipo de Pagamento (apenas para boleto) -->
                            <div class="col-md-3 mb-3 campo-boleto passo-2" style="display: none;">
                                <label for="tipo_boleto" class="form-label">Tipo de Pagamento</label>
                                <select class="form-select @error('tipo_boleto') is-invalid @enderror" id="tipo_boleto" name="tipo_boleto">
                                    <option value="">Selecione</option>
                                    <option value="avista" {{ old('tipo_boleto', $matricula->tipo_boleto) == 'avista' ? 'selected' : '' }}>À Vista</option>
                                    <option value="parcelado" {{ old('tipo_boleto', $matricula->tipo_boleto) == 'parcelado' ? 'selected' : '' }}>Parcelado</option>
                                </select>
                                @error('tipo_boleto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Passo 3: Valor Total do Curso -->
                            <div class="col-md-3 mb-3 passo-3" style="display: none;">
                                <label for="valor_total_curso" class="form-label">Valor Total do Curso</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" 
                                           class="form-control @error('valor_total_curso') is-invalid @enderror" 
                                           id="valor_total_curso" 
                                           name="valor_total_curso" 
                                           step="0.01" 
                                           min="0" 
                                           value="{{ old('valor_total_curso', $matricula->valor_total_curso) }}">
                                </div>
                                @error('valor_total_curso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Passo 4: Valor da Matrícula -->
                            <div class="col-md-3 mb-3 campo-matricula passo-4" style="display: none;">
                                <label for="valor_matricula" class="form-label">Valor da Matrícula</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" 
                                           class="form-control @error('valor_matricula') is-invalid @enderror" 
                                           id="valor_matricula" 
                                           name="valor_matricula" 
                                           step="0.01" 
                                           min="0" 
                                           value="{{ old('valor_matricula', $matricula->valor_matricula) }}">
                                </div>
                                @error('valor_matricula')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>



                            <!-- Passo 5: Número de Parcelas -->
                            <div class="col-md-3 mb-3 campo-parcelas passo-5" style="display: none;">
                                <label for="numero_parcelas" class="form-label">Número de Parcelas</label>
                                <input type="number" 
                                       class="form-control @error('numero_parcelas') is-invalid @enderror" 
                                       id="numero_parcelas" 
                                       name="numero_parcelas" 
                                       min="1" 
                                       max="12" 
                                       value="{{ old('numero_parcelas', $matricula->numero_parcelas ?? 1) }}">
                                @error('numero_parcelas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Dia de Vencimento -->
                            <div class="col-md-3 mb-3 campo-dia-vencimento passo-6" style="display: none;">
                                <label for="dia_vencimento" class="form-label">Dia de Vencimento</label>
                                <select class="form-select @error('dia_vencimento') is-invalid @enderror" id="dia_vencimento" name="dia_vencimento">
                                    <option value="">Selecione</option>
                                    @for($i = 1; $i <= 31; $i++)
                                        <option value="{{ $i }}" {{ old('dia_vencimento', $matricula->dia_vencimento) == $i ? 'selected' : '' }}>
                                            Dia {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                <small class="form-text text-muted">Dia do mês para vencimento das mensalidades</small>
                                @error('dia_vencimento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Passo 7: Desconto -->
                            <div class="col-md-3 mb-3 campo-desconto passo-7" style="display: none;">
                                <label for="desconto" class="form-label">Desconto</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('desconto') is-invalid @enderror" 
                                           id="desconto" 
                                           name="desconto" 
                                           min="0" 
                                           max="100" 
                                           step="0.01" 
                                           value="{{ old('desconto', $matricula->desconto ?? 0) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="form-text text-muted">Digite a porcentagem de desconto (0-100%)</small>
                                @error('desconto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Passo 7 alternativo: Juros do Boleto -->
                            <div class="col-md-3 mb-3 campo-juros-boleto passo-7" style="display: none;">
                                <label for="percentual_juros" class="form-label">Juros ao Mês</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('percentual_juros') is-invalid @enderror" 
                                           id="percentual_juros" 
                                           name="percentual_juros" 
                                           min="0" 
                                           max="100"
                                           step="0.01"
                                           value="{{ old('percentual_juros', $matricula->percentual_juros ?? 0) }}"
                                           placeholder="Ex: 5 para 5%">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="form-text text-muted">Taxa de juros aplicada somente em parcelas vencidas</small>
                                @error('percentual_juros')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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

            <!-- Escola Parceira -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-handshake me-2"></i>
                            Escola Parceira
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="escola_parceira" name="escola_parceira" value="1" {{ old('escola_parceira', $matricula->escola_parceira) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="escola_parceira">
                                        É uma escola parceira?
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6" id="parceiro_select_container" style="{{ old('escola_parceira', $matricula->escola_parceira) ? '' : 'display: none;' }}">
                                <label for="parceiro_id" class="form-label">Selecione o Parceiro</label>
                                <select class="form-select @error('parceiro_id') is-invalid @enderror" id="parceiro_id" name="parceiro_id">
                                    <option value="">Selecione um parceiro</option>
                                    @foreach($parceiros as $parceiro)
                                        <option value="{{ $parceiro->id }}" {{ old('parceiro_id', $matricula->parceiro_id) == $parceiro->id ? 'selected' : '' }}>
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

            <!-- Observações -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-sticky-note me-2"></i>
                            Observações
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea class="form-control @error('observacoes') is-invalid @enderror" 
                                      id="observacoes" 
                                      name="observacoes" 
                                      rows="4">{{ old('observacoes', $matricula->observacoes) }}</textarea>
                            @error('observacoes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-4">
            <div>
                <a href="{{ route('admin.matriculas.show', $matricula) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Voltar
                </a>
                
                @if($matricula->forma_pagamento && $matricula->valor_total_curso)
                    <button type="button" class="btn btn-warning ms-2" id="regeneratePaymentsBtn">
                        <i class="fas fa-sync-alt me-2"></i>
                        Regenerar Pagamentos
                    </button>
                @endif
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>
                Atualizar Matrícula
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Scripts específicos da página de edição de matrícula
document.addEventListener('DOMContentLoaded', function() {
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

    // Função para formatar valores em Real
    function formatarReal(valor) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(valor);
    }

    // Função para mostrar/ocultar campos
    function toggleFields(fields, show) {
        fields.forEach(field => {
            $(field).toggle(show);
        });
    }

    // Função para calcular valores
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
                    break;
                case 'cartao_credito':
                    toggleFields([
                        '.passo-3', '.campo-mensalidade', '.campo-desconto',
                        '.campo-vencimento', '.campo-dia-vencimento', '.campo-display-mensalidade'
                    ], true);
                    // Ocultar campo de parcelas para cartão (parcelamento é feito no checkout do Mercado Pago)
                    toggleFields(['.campo-parcelas'], false);
                    break;
                case 'boleto':
                    toggleFields(['.passo-2', '.passo-3'], true);
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
            
            // Trigger change no forma_pagamento se já estiver selecionado
            const currentFormaPagamento = $('#forma_pagamento').val();
            if (currentFormaPagamento) {
                $('#forma_pagamento').trigger('change');
            }
        } else {
            // Outros bancos: Mostrar bank_info e valor_pago, ocultar configurações
            toggleFields(['.campo-bank-info', '.campo-valor-pago'], true);
            toggleFields([
                '.campo-forma-pagamento', '.passo-2', '.passo-3', '.campo-matricula', '.campo-mensalidade',
                '.campo-parcelas', '.campo-desconto', '.campo-vencimento', '.campo-dia-vencimento',
                '.campo-juros-boleto', '.campo-display-juros', '.campo-display-mensalidade'
            ], false);
            $('#bank_info').prop('required', true);
            $('#forma_pagamento, #valor_pago').prop('required', false);
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
            } else if ($(this).val() === 'parcelado') {
                toggleFields([
                    '.campo-matricula', '.campo-mensalidade', '.campo-parcelas',
                    '.campo-desconto', '.campo-vencimento', '.campo-dia-vencimento', '.campo-juros-boleto',
                    '.campo-display-juros', '.campo-aviso-juros'
                ], true);
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
        const campoParceiro = $('#parceiro_select_container');
        const selectParceiro = $('#parceiro_id');
        
        if (isChecked) {
            campoParceiro.slideDown(300);
        } else {
            campoParceiro.slideUp(300);
            selectParceiro.val('');
        }
    }

    $('#escola_parceira').on('change', toggleCampoParceiro);

    // Inicializar campos baseado nos dados salvos
    function inicializarCampos() {
        const formaPagamento = matriculaElements.formaPagamento.val();
        const tipoBoleto = matriculaElements.tipoBoleto.val();
        
        // Aplicar lógica de parcelas baseado na forma de pagamento atual
        const numeroParcelasField = $('#numero_parcelas');
        if (formaPagamento === 'cartao_credito') {
            // Para cartão de crédito, ocultar campo de parcelas (feito no checkout)
            toggleFields(['.campo-parcelas'], false);
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
        
        // Mostrar campos baseado na forma de pagamento
        if (formaPagamento) {
            matriculaElements.formaPagamento.trigger('change');
            
            // Para boleto, mostrar campos específicos
            if (formaPagamento === 'boleto' && tipoBoleto) {
                matriculaElements.tipoBoleto.trigger('change');
            }
        }
        
        // Inicializar campo de gateway de pagamento
        matriculaElements.paymentGateway.trigger('change');
        
        // Calcular valores iniciais
        calcularValores();
    }

    // Inicializar quando a página carregar
    inicializarCampos();
    
    // Funcionalidade do botão "Regenerar Pagamentos" - COM PROTEÇÃO ANTI-LOOP
    const regeneratePaymentsBtn = document.getElementById('regeneratePaymentsBtn');
    let isRegeneratingPayments = false; // Flag para evitar cliques múltiplos

    if (regeneratePaymentsBtn) {
        // 🛡️ PROTEÇÃO EXTRA: Debounce agressivo (3 segundos)
        let lastClickTime = 0;
        const DEBOUNCE_TIME = 3000; // 3 segundos
        
        regeneratePaymentsBtn.addEventListener('click', function(event) {
            const now = Date.now();
            
            // 🔍 LOG: Registrar detalhes do clique para debug
            console.log('Clique no botão Regenerar Pagamentos detectado:', {
                timestamp: new Date().toISOString(),
                isTrusted: event.isTrusted,
                type: event.type,
                target: event.target.id,
                isRegeneratingPayments: isRegeneratingPayments,
                timeSinceLastClick: now - lastClickTime,
                debounceTime: DEBOUNCE_TIME
            });
            
            // DEBUG removido - problema identificado no PaymentNotificationService
            
            // 🚨 PROTEÇÃO ANTI-SPAM: Debounce de 3 segundos
            if (now - lastClickTime < DEBOUNCE_TIME) {
                console.warn('Clique muito rápido após anterior, ignorando (debounce)');
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
            
            lastClickTime = now;
            
            // 🚨 PROTEÇÃO ANTI-LOOP: Verificar se já está processando
            if (isRegeneratingPayments) {
                console.warn('Regeneração de pagamentos já em andamento, ignorando clique');
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
            if (confirm('Tem certeza que deseja regenerar os pagamentos desta matrícula? Esta ação irá:\n\n' +
                       '• Cancelar todos os pagamentos pendentes existentes\n' +
                       '• Criar novos pagamentos baseados nos dados atuais\n' +
                       '• Manter apenas pagamentos já confirmados\n\n' +
                       'Deseja continuar?')) {
                
                // Ativar flag de proteção
                isRegeneratingPayments = true;
                
                // Mostrar loading no botão
                const originalText = regeneratePaymentsBtn.innerHTML;
                regeneratePaymentsBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Regenerando...';
                regeneratePaymentsBtn.disabled = true;
                
                // Fazer requisição AJAX
                fetch('{{ route("admin.matriculas.regenerate-payments", $matricula) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reset da flag antes de redirecionar
                        isRegeneratingPayments = false;
                        
                        // Mostrar mensagem de sucesso
                        if (typeof toastr !== 'undefined') {
                            toastr.success(data.message);
                        } else {
                            alert(data.message);
                        }
                        
                        // Redirecionar após um breve delay
                        setTimeout(() => {
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            }
                        }, 1500);
                    } else {
                        // Mostrar mensagem de erro
                        if (typeof toastr !== 'undefined') {
                            toastr.error(data.message);
                        } else {
                            alert('Erro: ' + data.message);
                        }
                        
                        // Restaurar botão e reset da flag
                        regeneratePaymentsBtn.innerHTML = originalText;
                        regeneratePaymentsBtn.disabled = false;
                        isRegeneratingPayments = false;
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                    
                    // Mostrar mensagem de erro
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Erro ao regenerar pagamentos. Tente novamente.');
                    } else {
                        alert('Erro ao regenerar pagamentos. Tente novamente.');
                    }
                    
                    // Restaurar botão e reset da flag
                    regeneratePaymentsBtn.innerHTML = originalText;
                    regeneratePaymentsBtn.disabled = false;
                    isRegeneratingPayments = false;
                });
            }
        });
    }
    

    
    // Carregar IMask e aplicar máscaras
    if (typeof IMask === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/imask';
        script.onload = function() {
            aplicarMascaras();
        };
        document.head.appendChild(script);
    } else {
        aplicarMascaras();
    }
    
    function aplicarMascaras() {
        const cpfElement = document.getElementById('cpf');
        const rgElement = document.getElementById('rg');
        const cepElement = document.getElementById('cep');
        const telefoneFixoElement = document.getElementById('telefone_fixo');
        const telefoneCelularElement = document.getElementById('telefone_celular');
        
        if (cpfElement) {
            const cpfMask = IMask(cpfElement, {
                mask: '000.000.000-00'
            });
        }
        
        if (rgElement) {
            const rgMask = IMask(rgElement, {
                mask: '00.000.000-0'
            });
        }
        
        if (cepElement) {
            const cepMask = IMask(cepElement, {
                mask: '00000-000'
            });
        }
        
        if (telefoneFixoElement) {
            const telefoneFixoMask = IMask(telefoneFixoElement, {
                mask: '(00) 00000-0000'
            });
        }
        
        if (telefoneCelularElement) {
            const telefoneCelularMask = IMask(telefoneCelularElement, {
                mask: '(00) 00000-0000'
            });
        }
    }
});
</script>
@endpush 