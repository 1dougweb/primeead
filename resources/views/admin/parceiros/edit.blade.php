@extends('layouts.admin')

@section('title', 'Editar Parceiro')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit text-primary me-2"></i>
                Editar Parceiro
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.parceiros.index') }}">Parceiros</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.parceiros.show', $parceiro) }}">{{ $parceiro->nome_completo }}</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.parceiros.show', $parceiro) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.parceiros.update', $parceiro) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Dados Principais -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user me-2"></i>Dados do Parceiro
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Dados Pessoais -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-id-card me-2"></i>Dados Pessoais
                                </h6>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome_completo" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control @error('nome_completo') is-invalid @enderror" 
                                       id="nome_completo" name="nome_completo" value="{{ old('nome_completo', $parceiro->nome_completo) }}" required>
                                @error('nome_completo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $parceiro->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">Telefone *</label>
                                <input type="text" class="form-control @error('telefone') is-invalid @enderror" 
                                       id="telefone" name="telefone" value="{{ old('telefone', $parceiro->telefone) }}" required>
                                @error('telefone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="whatsapp" class="form-label">WhatsApp</label>
                                <input type="text" class="form-control @error('whatsapp') is-invalid @enderror" 
                                       id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $parceiro->whatsapp) }}">
                                @error('whatsapp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="documento" class="form-label">Documento (CPF/CNPJ) *</label>
                                <input type="text" class="form-control @error('documento') is-invalid @enderror" 
                                       id="documento" name="documento" value="{{ old('documento', $parceiro->documento) }}" required>
                                @error('documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipo_documento" class="form-label">Tipo de Documento *</label>
                                <select class="form-select @error('tipo_documento') is-invalid @enderror" 
                                        id="tipo_documento" name="tipo_documento" required>
                                    <option value="">Selecione</option>
                                    <option value="cpf" {{ old('tipo_documento', $parceiro->tipo_documento) === 'cpf' ? 'selected' : '' }}>CPF</option>
                                    <option value="cnpj" {{ old('tipo_documento', $parceiro->tipo_documento) === 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                                </select>
                                @error('tipo_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="row">
                            <div class="col-md-12 mt-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-map-marker-alt me-2"></i>Endereço
                                </h6>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="cep" class="form-label">CEP *</label>
                                <input type="text" class="form-control @error('cep') is-invalid @enderror" 
                                       id="cep" name="cep" value="{{ old('cep', $parceiro->cep) }}" required>
                                @error('cep')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="endereco" class="form-label">Endereço *</label>
                                <input type="text" class="form-control @error('endereco') is-invalid @enderror" 
                                       id="endereco" name="endereco" value="{{ old('endereco', $parceiro->endereco) }}" required>
                                @error('endereco')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="numero" class="form-label">Número *</label>
                                <input type="text" class="form-control @error('numero') is-invalid @enderror" 
                                       id="numero" name="numero" value="{{ old('numero', $parceiro->numero) }}" required>
                                @error('numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="complemento" class="form-label">Complemento</label>
                                <input type="text" class="form-control @error('complemento') is-invalid @enderror" 
                                       id="complemento" name="complemento" value="{{ old('complemento', $parceiro->complemento) }}">
                                @error('complemento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="bairro" class="form-label">Bairro *</label>
                                <input type="text" class="form-control @error('bairro') is-invalid @enderror" 
                                       id="bairro" name="bairro" value="{{ old('bairro', $parceiro->bairro) }}" required>
                                @error('bairro')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="cidade" class="form-label">Cidade *</label>
                                <input type="text" class="form-control @error('cidade') is-invalid @enderror" 
                                       id="cidade" name="cidade" value="{{ old('cidade', $parceiro->cidade) }}" required>
                                @error('cidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="estado" class="form-label">Estado *</label>
                                <select class="form-select @error('estado') is-invalid @enderror" 
                                        id="estado" name="estado" required>
                                    <option value="">Selecione</option>
                                    <option value="AC" {{ old('estado', $parceiro->estado) === 'AC' ? 'selected' : '' }}>Acre</option>
                                    <option value="AL" {{ old('estado', $parceiro->estado) === 'AL' ? 'selected' : '' }}>Alagoas</option>
                                    <option value="AP" {{ old('estado', $parceiro->estado) === 'AP' ? 'selected' : '' }}>Amapá</option>
                                    <option value="AM" {{ old('estado', $parceiro->estado) === 'AM' ? 'selected' : '' }}>Amazonas</option>
                                    <option value="BA" {{ old('estado', $parceiro->estado) === 'BA' ? 'selected' : '' }}>Bahia</option>
                                    <option value="CE" {{ old('estado', $parceiro->estado) === 'CE' ? 'selected' : '' }}>Ceará</option>
                                    <option value="DF" {{ old('estado', $parceiro->estado) === 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                                    <option value="ES" {{ old('estado', $parceiro->estado) === 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                                    <option value="GO" {{ old('estado', $parceiro->estado) === 'GO' ? 'selected' : '' }}>Goiás</option>
                                    <option value="MA" {{ old('estado', $parceiro->estado) === 'MA' ? 'selected' : '' }}>Maranhão</option>
                                    <option value="MT" {{ old('estado', $parceiro->estado) === 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                                    <option value="MS" {{ old('estado', $parceiro->estado) === 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                                    <option value="MG" {{ old('estado', $parceiro->estado) === 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                                    <option value="PA" {{ old('estado', $parceiro->estado) === 'PA' ? 'selected' : '' }}>Pará</option>
                                    <option value="PB" {{ old('estado', $parceiro->estado) === 'PB' ? 'selected' : '' }}>Paraíba</option>
                                    <option value="PR" {{ old('estado', $parceiro->estado) === 'PR' ? 'selected' : '' }}>Paraná</option>
                                    <option value="PE" {{ old('estado', $parceiro->estado) === 'PE' ? 'selected' : '' }}>Pernambuco</option>
                                    <option value="PI" {{ old('estado', $parceiro->estado) === 'PI' ? 'selected' : '' }}>Piauí</option>
                                    <option value="RJ" {{ old('estado', $parceiro->estado) === 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                                    <option value="RN" {{ old('estado', $parceiro->estado) === 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                                    <option value="RS" {{ old('estado', $parceiro->estado) === 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                                    <option value="RO" {{ old('estado', $parceiro->estado) === 'RO' ? 'selected' : '' }}>Rondônia</option>
                                    <option value="RR" {{ old('estado', $parceiro->estado) === 'RR' ? 'selected' : '' }}>Roraima</option>
                                    <option value="SC" {{ old('estado', $parceiro->estado) === 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                                    <option value="SP" {{ old('estado', $parceiro->estado) === 'SP' ? 'selected' : '' }}>São Paulo</option>
                                    <option value="SE" {{ old('estado', $parceiro->estado) === 'SE' ? 'selected' : '' }}>Sergipe</option>
                                    <option value="TO" {{ old('estado', $parceiro->estado) === 'TO' ? 'selected' : '' }}>Tocantins</option>
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Dados da Parceria -->
                        <div class="row">
                            <div class="col-md-12 mt-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-handshake me-2"></i>Dados da Parceria
                                </h6>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modalidade_parceria" class="form-label">Modalidade da Parceria *</label>
                                <select class="form-select @error('modalidade_parceria') is-invalid @enderror" 
                                        id="modalidade_parceria" name="modalidade_parceria" required>
                                    <option value="">Selecione</option>
                                    <option value="Polo Presencial" {{ old('modalidade_parceria', $parceiro->modalidade_parceria) === 'Polo Presencial' ? 'selected' : '' }}>Polo Presencial</option>
                                    <option value="EaD" {{ old('modalidade_parceria', $parceiro->modalidade_parceria) === 'EaD' ? 'selected' : '' }}>EaD</option>
                                    <option value="Híbrido" {{ old('modalidade_parceria', $parceiro->modalidade_parceria) === 'Híbrido' ? 'selected' : '' }}>Híbrido</option>
                                    <option value="Representante" {{ old('modalidade_parceria', $parceiro->modalidade_parceria) === 'Representante' ? 'selected' : '' }}>Representante</option>
                                </select>
                                @error('modalidade_parceria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="disponibilidade" class="form-label">Disponibilidade *</label>
                                <select class="form-select @error('disponibilidade') is-invalid @enderror" 
                                        id="disponibilidade" name="disponibilidade" required>
                                    <option value="">Selecione</option>
                                    <option value="meio_periodo" {{ old('disponibilidade', $parceiro->disponibilidade) === 'meio_periodo' ? 'selected' : '' }}>Meio Período</option>
                                    <option value="integral" {{ old('disponibilidade', $parceiro->disponibilidade) === 'integral' ? 'selected' : '' }}>Integral</option>
                                    <option value="fins_semana" {{ old('disponibilidade', $parceiro->disponibilidade) === 'fins_semana' ? 'selected' : '' }}>Fins de Semana</option>
                                    <option value="flexivel" {{ old('disponibilidade', $parceiro->disponibilidade) === 'flexivel' ? 'selected' : '' }}>Flexível</option>
                                </select>
                                @error('disponibilidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Possui Estrutura? *</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('possui_estrutura') is-invalid @enderror" 
                                               type="radio" name="possui_estrutura" id="possui_estrutura_sim" value="1" 
                                               {{ old('possui_estrutura', $parceiro->possui_estrutura ? '1' : '0') === '1' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="possui_estrutura_sim">Sim</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('possui_estrutura') is-invalid @enderror" 
                                               type="radio" name="possui_estrutura" id="possui_estrutura_nao" value="0" 
                                               {{ old('possui_estrutura', $parceiro->possui_estrutura ? '1' : '0') === '0' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="possui_estrutura_nao">Não</label>
                                    </div>
                                </div>
                                @error('possui_estrutura')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tem Site? *</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('tem_site') is-invalid @enderror" 
                                               type="radio" name="tem_site" id="tem_site_sim" value="1" 
                                               {{ old('tem_site', $parceiro->tem_site ? '1' : '0') === '1' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="tem_site_sim">Sim</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('tem_site') is-invalid @enderror" 
                                               type="radio" name="tem_site" id="tem_site_nao" value="0" 
                                               {{ old('tem_site', $parceiro->tem_site ? '1' : '0') === '0' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="tem_site_nao">Não</label>
                                    </div>
                                </div>
                                @error('tem_site')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Experiência Educacional? *</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('tem_experiencia_educacional') is-invalid @enderror" 
                                               type="radio" name="tem_experiencia_educacional" id="tem_experiencia_sim" value="1" 
                                               {{ old('tem_experiencia_educacional', $parceiro->tem_experiencia_educacional ? '1' : '0') === '1' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="tem_experiencia_sim">Sim</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('tem_experiencia_educacional') is-invalid @enderror" 
                                               type="radio" name="tem_experiencia_educacional" id="tem_experiencia_nao" value="0" 
                                               {{ old('tem_experiencia_educacional', $parceiro->tem_experiencia_educacional ? '1' : '0') === '0' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="tem_experiencia_nao">Não</label>
                                    </div>
                                </div>
                                @error('tem_experiencia_educacional')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row" id="site_url_field" style="{{ old('tem_site', $parceiro->tem_site ? '1' : '0') === '1' ? '' : 'display: none;' }}">
                            <div class="col-md-12 mb-3">
                                <label for="site_url" class="form-label">URL do Site</label>
                                <input type="url" class="form-control @error('site_url') is-invalid @enderror" 
                                       id="site_url" name="site_url" value="{{ old('site_url', $parceiro->site_url) }}">
                                @error('site_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="plano_negocio" class="form-label">Plano de Negócio</label>
                                <textarea class="form-control @error('plano_negocio') is-invalid @enderror" 
                                          id="plano_negocio" name="plano_negocio" rows="4">{{ old('plano_negocio', $parceiro->plano_negocio) }}</textarea>
                                @error('plano_negocio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="experiencia_vendas" class="form-label">Experiência em Vendas</label>
                                <textarea class="form-control @error('experiencia_vendas') is-invalid @enderror" 
                                          id="experiencia_vendas" name="experiencia_vendas" rows="3">{{ old('experiencia_vendas', $parceiro->experiencia_vendas) }}</textarea>
                                @error('experiencia_vendas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="motivacao" class="form-label">Motivação</label>
                                <textarea class="form-control @error('motivacao') is-invalid @enderror" 
                                          id="motivacao" name="motivacao" rows="3">{{ old('motivacao', $parceiro->motivacao) }}</textarea>
                                @error('motivacao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Dados Bancários -->
                        <div class="row">
                            <div class="col-md-12 mt-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-university me-2"></i>Dados Bancários
                                </h6>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="banco" class="form-label">Banco</label>
                                <input type="text" class="form-control @error('banco') is-invalid @enderror" 
                                       id="banco" name="banco" value="{{ old('banco', $parceiro->banco) }}">
                                @error('banco')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="agencia" class="form-label">Agência</label>
                                <input type="text" class="form-control @error('agencia') is-invalid @enderror" 
                                       id="agencia" name="agencia" value="{{ old('agencia', $parceiro->agencia) }}">
                                @error('agencia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="conta" class="form-label">Conta</label>
                                <input type="text" class="form-control @error('conta') is-invalid @enderror" 
                                       id="conta" name="conta" value="{{ old('conta', $parceiro->conta) }}">
                                @error('conta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="pix" class="form-label">PIX</label>
                                <input type="text" class="form-control @error('pix') is-invalid @enderror" 
                                       id="pix" name="pix" value="{{ old('pix', $parceiro->pix) }}">
                                @error('pix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="observacoes" class="form-label">Observações</label>
                                <textarea class="form-control @error('observacoes') is-invalid @enderror" 
                                          id="observacoes" name="observacoes" rows="3">{{ old('observacoes', $parceiro->observacoes) }}</textarea>
                                @error('observacoes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cog me-2"></i>Configurações
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="pendente" {{ old('status', $parceiro->status) === 'pendente' ? 'selected' : '' }}>Pendente</option>
                                <option value="aprovado" {{ old('status', $parceiro->status) === 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                                <option value="rejeitado" {{ old('status', $parceiro->status) === 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                                <option value="ativo" {{ old('status', $parceiro->status) === 'ativo' ? 'selected' : '' }}>Ativo</option>
                                <option value="inativo" {{ old('status', $parceiro->status) === 'inativo' ? 'selected' : '' }}>Inativo</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="comissao_percentual" class="form-label">Comissão (%) *</label>
                            <input type="number" class="form-control @error('comissao_percentual') is-invalid @enderror" 
                                   id="comissao_percentual" name="comissao_percentual" 
                                   value="{{ old('comissao_percentual', $parceiro->comissao_percentual) }}" 
                                   min="0" max="100" step="0.01" required>
                            @error('comissao_percentual')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Salvar Alterações
                            </button>
                            <a href="{{ route('admin.parceiros.show', $parceiro) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Informações -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Informações
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                                Cadastrado em
                            </div>
                            <div class="h6 mb-3 font-weight-bold text-gray-800">
                                {{ $parceiro->created_at->format('d/m/Y H:i') }}
                            </div>
                            
                            @if($parceiro->data_aprovacao)
                            <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                                Aprovado em
                            </div>
                            <div class="h6 mb-3 font-weight-bold text-success">
                                {{ $parceiro->data_aprovacao->format('d/m/Y H:i') }}
                            </div>
                            @endif
                            
                            @if($parceiro->ultimo_contato)
                            <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                                Último Contato
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-info">
                                {{ $parceiro->ultimo_contato->format('d/m/Y H:i') }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Máscara para telefones
    $('#telefone, #whatsapp').mask('(00) 00000-0000');
    
    // Máscara para CEP
    $('#cep').mask('00000-000');
    
    // Mostrar/ocultar campo de URL do site
    $('input[name="tem_site"]').change(function() {
        if ($(this).val() === '1') {
            $('#site_url_field').show();
        } else {
            $('#site_url_field').hide();
            $('#site_url').val('');
        }
    });
    
    // Buscar endereço por CEP
    $('#cep').blur(function() {
        var cep = $(this).val().replace(/\D/g, '');
        
        if (cep.length === 8) {
            $.get(`/api/cep/${cep}`, function(data) {
                if (data && !data.erro) {
                    $('#endereco').val(data.logradouro);
                    $('#bairro').val(data.bairro);
                    $('#cidade').val(data.localidade);
                    $('#estado').val(data.uf);
                }
            }).fail(function() {
                console.log('Erro ao buscar CEP');
            });
        }
    });
});
</script>
@endsection