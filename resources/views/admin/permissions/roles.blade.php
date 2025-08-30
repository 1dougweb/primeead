@extends('layouts.admin')

@section('title', 'Gerenciar Roles')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">👥 Gerenciar Roles</h1>
                    <p class="text-muted mb-0">Gerencie os roles do sistema</p>
                </div>
                <div>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <a href="{{ route('admin.permissions.roles.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Role
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $roles->count() }}</h3>
                                    <p class="mb-0">Total de Roles</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $roles->where('is_active', true)->count() }}</h3>
                                    <p class="mb-0">Ativos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-user-friends fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $roles->sum(function($role) { return $role->users->count(); }) }}</h3>
                                    <p class="mb-0">Usuários Atribuídos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-key fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $roles->sum(function($role) { return $role->permissions->count(); }) }}</h3>
                                    <p class="mb-0">Permissões Totais</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> Lista de Roles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Guard</th>
                                    <th>Status</th>
                                    <th>Permissões</th>
                                    <th>Usuários</th>
                                    <th>Criado em</th>
                                    <th width="150">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $role->name }}</strong>
                                                @if($role->description)
                                                    <br><small class="text-muted">{{ $role->description }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $role->guard_name === 'web' ? 'primary' : 'info' }}">
                                                {{ $role->guard_name }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($role->is_active)
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-danger">Inativo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $role->permissions->count() }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ $role->users->count() }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $role->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.permissions.roles.edit', $role) }}" 
                                                   class="btn btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deleteRole({{ $role->id }})" 
                                                        title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Nenhum role encontrado</p>
                                            <a href="{{ route('admin.permissions.roles.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Criar Primeiro Role
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteRole(id) {
    if (confirm('Tem certeza que deseja excluir este role?')) {
        axios.delete(`/admin/permissions/roles/${id}`)
            .then(response => {
                toastr.success('Role excluído com sucesso!');
                location.reload();
            })
            .catch(error => {
                toastr.error('Erro ao excluir role');
                console.error(error);
            });
    }
}
</script>
@endsection 