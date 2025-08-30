@extends('layouts.admin')

@section('title', 'Editar Inscrição')

@section('content')
<div class="container-fluid px-4">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mt-4 mb-0">
                <i class="fas fa-edit me-2"></i>
                Editar Inscrição
            </h3>
            <ol class="breadcrumb mb-0 mt-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.inscricoes') }}">Inscrições</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </div>
    </div>

    <!-- Formulário -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.inscricoes.atualizar', $inscricao->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Nome -->
                            <div class="col-md-6">
                                <label class="form-label">Nome</label>
                                <input type="text" 
                                       class="form-control @error('nome') is-invalid @enderror" 
                                       name="nome" 
                                       value="{{ old('nome', $inscricao->nome) }}" 
                                       required>
                                @error('nome')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" 
                                       value="{{ old('email', $inscricao->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Telefone -->
                            <div class="col-md-6">
                                <label class="form-label">Telefone</label>
                                <input type="tel" 
                                       class="form-control @error('telefone') is-invalid @enderror" 
                                       name="telefone" 
                                       value="{{ old('telefone', $inscricao->telefone) }}" 
                                       required>
                                @error('telefone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Curso -->
                            <div class="col-md-6">
                                <label class="form-label">Curso</label>
                                <select class="form-select @error('curso') is-invalid @enderror" 
                                        name="curso" 
                                        required>
                                    <option value="">Selecione um curso</option>
                                    @foreach($formSettings['available_courses'] ?? [] as $key => $label)
                                        <option value="{{ $key }}" {{ old('curso', $inscricao->curso) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('curso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Modalidade -->
                            <div class="col-md-6">
                                <label class="form-label">Modalidade</label>
                                <select class="form-select @error('modalidade') is-invalid @enderror" 
                                        name="modalidade" 
                                        required>
                                    <option value="">Selecione uma modalidade</option>
                                    @foreach($formSettings['available_modalities'] ?? [] as $key => $label)
                                        <option value="{{ $key }}" {{ old('modalidade', $inscricao->modalidade) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('modalidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select @error('etiqueta') is-invalid @enderror" 
                                        name="etiqueta" 
                                        required>
                                    <option value="pendente" {{ old('etiqueta', $inscricao->etiqueta) == 'pendente' ? 'selected' : '' }}>
                                        Pendente
                                    </option>
                                    <option value="contatado" {{ old('etiqueta', $inscricao->etiqueta) == 'contatado' ? 'selected' : '' }}>
                                        Contatado
                                    </option>
                                    <option value="interessado" {{ old('etiqueta', $inscricao->etiqueta) == 'interessado' ? 'selected' : '' }}>
                                        Interessado
                                    </option>
                                    <option value="nao_interessado" {{ old('etiqueta', $inscricao->etiqueta) == 'nao_interessado' ? 'selected' : '' }}>
                                        Não Interessado
                                    </option>
                                    <option value="matriculado" {{ old('etiqueta', $inscricao->etiqueta) == 'matriculado' ? 'selected' : '' }}>
                                        Matriculado
                                    </option>
                                </select>
                                @error('etiqueta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Observações -->
                            <div class="col-12">
                                <label class="form-label">Observações</label>
                                <textarea class="form-control @error('observacoes') is-invalid @enderror" 
                                          name="observacoes" 
                                          rows="3">{{ old('observacoes', '') }}</textarea>
                                @error('observacoes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Adicione observações sobre o contato ou mudança de status.
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.inscricoes') }}" class="btn btn-light">
                                <i class="fas fa-times me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Histórico de Status -->
            @if($inscricao->statusHistories->count() > 0)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-1"></i>
                        Histórico de Status
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Status Anterior</th>
                                    <th>Novo Status</th>
                                    <th>Usuário</th>
                                    <th>Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inscricao->statusHistories->sortByDesc('created_at') as $history)
                                <tr>
                                    <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($history->status_anterior) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ ucfirst($history->status_novo) }}
                                        </span>
                                    </td>
                                    <td>{{ $history->usuario->name ?? 'N/A' }}</td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $history->observacoes ?: 'Nenhuma observação' }}
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
