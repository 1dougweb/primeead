@extends('layouts.admin')

@section('title', 'Editar Inscrição')

@section('page-title', 'Editar Inscrição')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.inscricoes') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar à Lista
        </a>
    </div>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Editando Inscrição #{{ $inscricao->id }}
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.inscricoes.atualizar', $inscricao->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">
                                    <i class="fas fa-user me-1"></i>
                                    Nome Completo
                                </label>
                                <input type="text" 
                                       class="form-control @error('nome') is-invalid @enderror" 
                                       id="nome" 
                                       name="nome" 
                                       value="{{ old('nome', $inscricao->nome) }}" 
                                       required>
                                @error('nome')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>
                                    Email
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $inscricao->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">
                                    <i class="fas fa-phone me-1"></i>
                                    Telefone
                                </label>
                                <input type="tel" 
                                       class="form-control @error('telefone') is-invalid @enderror" 
                                       id="telefone" 
                                       name="telefone" 
                                       value="{{ old('telefone', $inscricao->telefone) }}" 
                                       required>
                                @error('telefone')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="curso" class="form-label">
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    Curso de Interesse
                                </label>
                                <select class="form-select @error('curso') is-invalid @enderror" 
                                        id="curso" 
                                        name="curso" 
                                        required>
                                    <option value="">Selecione um curso</option>
                                    @foreach($cursos as $value => $label)
                                        <option value="{{ $value }}" 
                                                {{ old('curso', $inscricao->curso) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('curso')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="modalidade" class="form-label">Modalidade</label>
                            <select class="form-select" id="modalidade" name="modalidade">
                                @foreach($modalidades as $value => $label)
                                    <option value="{{ $value }}" {{ $inscricao->modalidade == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Informações adicionais (somente leitura) -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Informações Adicionais
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <small class="text-muted">Data de Inscrição:</small><br>
                                                <strong>{{ $inscricao->created_at->format('d/m/Y H:i:s') }}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted">Endereço IP:</small><br>
                                                <strong>{{ $inscricao->ip_address }}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <small class="text-muted">Aceita Termos:</small><br>
                                                <span class="badge {{ $inscricao->termos ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $inscricao->termos ? 'Sim' : 'Não' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.inscricoes') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                            
                            <div class="d-flex gap-2">
                                <button type="button" 
                                        class="btn btn-danger" 
                                        onclick="confirmarExclusao({{ $inscricao->id }}, '{{ addslashes($inscricao->nome) }}')">
                                    <i class="fas fa-trash me-2"></i>
                                    Excluir
                                </button>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalExclusao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir a inscrição de <strong id="nomeInscricao"></strong>?</p>
                    <p class="text-muted small">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formExclusao" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function confirmarExclusao(id, nome) {
        document.getElementById('nomeInscricao').textContent = nome;
        document.getElementById('formExclusao').action = '{{ route("admin.inscricoes.deletar", ":id") }}'.replace(':id', id);
        
        const modal = new bootstrap.Modal(document.getElementById('modalExclusao'));
        modal.show();
    }
    
    // Formatação do telefone
    document.getElementById('telefone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length <= 11) {
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            }
        }
        
        e.target.value = value;
    });
</script>
@endsection 