@extends('layouts.admin')

@section('title', 'Detalhes do Usuário')

@section('page-title', 'Detalhes: ' . $usuario->name)

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>
            Editar
        </a>
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar à Lista
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- Informações Básicas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Informações Básicas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td><span class="badge bg-light text-dark">{{ $usuario->id }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Nome:</strong></td>
                                    <td>{{ $usuario->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>
                                        <a href="mailto:{{ $usuario->email }}" class="text-decoration-none">
                                            {{ $usuario->email }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo:</strong></td>
                                    <td>
                                        <span class="badge {{ $usuario->tipo_usuario === 'admin' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                            {{ $usuario->tipo_usuario_formatado }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge {{ $usuario->ativo ? 'bg-success' : 'bg-danger' }}">
                                            {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Criado em:</strong></td>
                                    <td>{{ $usuario->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Último Acesso:</strong></td>
                                    <td>
                                        @if($usuario->ultimo_acesso)
                                            {{ $usuario->ultimo_acesso->format('d/m/Y H:i:s') }}
                                        @else
                                            <span class="text-muted">Nunca acessou</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Criado por:</strong></td>
                                    <td>{{ $usuario->criado_por ?? 'Sistema' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Papéis e Permissões -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-shield me-2"></i>
                        Papéis e Permissões
                    </h5>
                </div>
                <div class="card-body">
                    @if($usuario->roles->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Este usuário não possui papéis atribuídos.
                        </div>
                    @else
                        <h6>Papéis Atribuídos:</h6>
                        <div class="mb-3">
                            @foreach($usuario->roles as $role)
                                <div class="card mb-2 {{ $role->is_active ? '' : 'bg-light' }}">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $role->name }}</strong>
                                                @if(!$role->is_active)
                                                    <span class="badge bg-danger ms-2">Inativo</span>
                                                @endif
                                                <p class="text-muted small mb-0">{{ $role->description }}</p>
                                            </div>
                                            <a href="{{ route('admin.permissions.roles.edit', $role) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Editar Papel">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <h6>Permissões Efetivas:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Módulo</th>
                                        <th>Permissões</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $allPermissions = collect();
                                        foreach($usuario->roles as $role) {
                                            $allPermissions = $allPermissions->merge($role->permissions);
                                        }
                                        $permissionsByModule = $allPermissions->unique('id')->groupBy('module');
                                    @endphp
                                    
                                    @forelse($permissionsByModule as $module => $permissions)
                                        <tr>
                                            <td><strong>{{ ucfirst($module) }}</strong></td>
                                            <td>
                                                @foreach($permissions as $permission)
                                                    <span class="badge {{ $permission->is_active ? 'bg-info' : 'bg-secondary' }} me-1 mb-1">
                                                        {{ $permission->name }}
                                                    </span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">
                                                Nenhuma permissão encontrada
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                    
                    <div class="mt-3">
                        <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit me-2"></i>
                            Editar Papéis
                        </a>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-sm btn-outline-secondary ms-2">
                            <i class="fas fa-cog me-2"></i>
                            Gerenciar Permissões
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estatísticas de Leads -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Estatísticas de Atendimento
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $leadsAtivos = $usuario->leadsTravasdos()->count();
                        $totalLeadsAtendidos = \App\Models\StatusHistory::where('alterado_por', $usuario->name)->count();
                    @endphp
                    
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $leadsAtivos }}</h3>
                                    <p class="mb-0">Leads em Atendimento</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h3 class="mb-0">{{ $totalLeadsAtendidos }}</h3>
                                    <p class="mb-0">Total de Alterações</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Ações Rápidas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>
                            Editar Usuário
                        </a>
                        
                        <a href="mailto:{{ $usuario->email }}" class="btn btn-outline-info">
                            <i class="fas fa-envelope me-2"></i>
                            Enviar Email
                        </a>
                        
                        @if($usuario->ativo)
                            <button class="btn btn-outline-warning" onclick="toggleStatus({{ $usuario->id }}, false)">
                                <i class="fas fa-pause me-2"></i>
                                Desativar Usuário
                            </button>
                        @else
                            <button class="btn btn-outline-success" onclick="toggleStatus({{ $usuario->id }}, true)">
                                <i class="fas fa-play me-2"></i>
                                Ativar Usuário
                            </button>
                        @endif
                        
                        @if($usuario->id != session('admin_id'))
                            <button class="btn btn-outline-danger" onclick="confirmarExclusao({{ $usuario->id }}, '{{ addslashes($usuario->name) }}')">
                                <i class="fas fa-trash me-2"></i>
                                Excluir Usuário
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Leads Atualmente em Atendimento -->
            @if($leadsAtivos > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-lock me-2"></i>
                            Leads em Atendimento
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($usuario->leadsTravasdos()->limit(5)->get() as $lead)
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div>
                                    <strong>{{ $lead->nome }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $lead->email }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-light text-dark">
                                        @switch($lead->etiqueta)
                                            @case('pendente') 🟡 Pendente @break
                                            @case('contatado') 🔵 Contatado @break
                                            @case('interessado') 🟢 Interessado @break
                                            @case('nao_interessado') 🔴 Não Interessado @break
                                            @case('matriculado') ⭐ Matriculado @break
                                            @default {{ $lead->etiqueta }}
                                        @endswitch
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        {{ $lead->locked_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($leadsAtivos > 5)
                            <div class="text-center mt-2">
                                <small class="text-muted">E mais {{ $leadsAtivos - 5 }} leads...</small>
                            </div>
                        @endif
                    </div>
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

function toggleStatus(id, novoStatus) {
    const acao = novoStatus ? 'ativar' : 'desativar';
    
    if (confirm(`Tem certeza que deseja ${acao} este usuário?`)) {
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
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao alterar status do usuário');
        });
    }
}
</script>
@endpush 