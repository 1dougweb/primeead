@extends('layouts.admin')

@section('title', 'Usuários')

@section('page-title', 'Gerenciar Usuários')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>
            Novo Usuário
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar ao Dashboard
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-users me-2"></i>
                Lista de Usuários ({{ $usuarios->total() }})
            </h5>
        </div>
        
        <div class="card-body p-0">
            @if($usuarios->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Papéis</th>
                                <th>Status</th>
                                <th>Último Acesso</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuarios as $usuario)
                                <tr class="{{ !$usuario->ativo ? 'table-secondary' : '' }}">
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $usuario->id }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $usuario->name }}</strong>
                                        @if($usuario->id == session('admin_id'))
                                            <span class="badge bg-info text-white ms-1">Você</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $usuario->email }}" class="text-decoration-none">
                                            {{ $usuario->email }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge {{ $usuario->tipo_usuario === 'admin' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                            {{ $usuario->tipo_usuario_formatado }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($usuario->roles->isEmpty())
                                            <span class="text-muted small">Nenhum papel atribuído</span>
                                        @else
                                            <div>
                                                @foreach($usuario->roles as $role)
                                                    <span class="badge {{ $role->is_active ? 'bg-info' : 'bg-secondary' }} mb-1">
                                                        {{ $role->name }}
                                                    </span>
                                                    @if(!$loop->last) @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-toggle" 
                                                   type="checkbox" 
                                                   data-id="{{ $usuario->id }}"
                                                   {{ $usuario->ativo ? 'checked' : '' }}
                                                   {{ $usuario->id == session('admin_id') ? 'disabled' : '' }}>
                                            <label class="form-check-label">
                                                <span class="badge {{ $usuario->ativo ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        @if($usuario->ultimo_acesso)
                                            <small>
                                                {{ $usuario->ultimo_acesso->format('d/m/Y') }}<br>
                                                <span class="text-muted">{{ $usuario->ultimo_acesso->format('H:i:s') }}</span>
                                            </small>
                                        @else
                                            <span class="text-muted">Nunca acessou</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.usuarios.show', $usuario->id) }}" 
                                               class="btn btn-sm btn-outline-info"
                                               title="Ver Detalhes">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            @if($usuario->id != session('admin_id'))
                                                @if($userMenuPermissions['admin.usuarios.impersonate'] ?? false)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success" 
                                                            onclick="confirmarImpersonation({{ $usuario->id }}, '{{ addslashes($usuario->name) }}')"
                                                            title="Fazer Login como {{ $usuario->name }}">
                                                        <i class="fas fa-user-secret"></i>
                                                    </button>
                                                @endif
                                                
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmarExclusao({{ $usuario->id }}, '{{ addslashes($usuario->name) }}')"
                                                        title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $usuarios->firstItem() }} a {{ $usuarios->lastItem() }} 
                            de {{ $usuarios->total() }} registros
                        </div>
                        
                        {{ $usuarios->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum usuário encontrado</h5>
                    <p class="text-muted">Clique no botão "Novo Usuário" para adicionar o primeiro usuário.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalExclusao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o usuário <strong id="nomeUsuario"></strong>?</p>
                    <p class="text-danger small">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="formExclusao" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function confirmarExclusao(id, nome) {
    document.getElementById('nomeUsuario').textContent = nome;
    document.getElementById('formExclusao').action = `/dashboard/usuarios/${id}`;
    new bootstrap.Modal(document.getElementById('modalExclusao')).show();
}

// Função para confirmar impersonation
function confirmarImpersonation(id, nome) {
    if (confirm(`Tem certeza que deseja fazer login como "${nome}"?\n\nVocê será redirecionado para o dashboard como este usuário. Para voltar ao seu usuário original, clique no botão "Sair da Impersonation" no topo da página.`)) {
        // Criar formulário dinâmico para impersonation
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/dashboard/usuarios/${id}/impersonate`;
        
        // Adicionar token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Adicionar ao body e submeter
        document.body.appendChild(form);
        form.submit();
    }
}

// Toggle status do usuário
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const id = this.dataset.id;
            const isChecked = this.checked;
            
            fetch(`/dashboard/usuarios/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar badge
                    const badge = this.nextElementSibling.querySelector('.badge');
                    if (data.novo_status) {
                        badge.className = 'badge bg-success';
                        badge.textContent = 'Ativo';
                        this.closest('tr').classList.remove('table-secondary');
                    } else {
                        badge.className = 'badge bg-danger';
                        badge.textContent = 'Inativo';
                        this.closest('tr').classList.add('table-secondary');
                    }
                } else {
                    alert(data.message);
                    // Reverter toggle
                    this.checked = !isChecked;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao alterar status do usuário');
                // Reverter toggle
                this.checked = !isChecked;
            });
        });
    });
});
</script>
@endpush 