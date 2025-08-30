@extends('layouts.admin')

@section('title', 'Gerenciamento de Permissões')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">🔐 Gerenciamento de Permissões</h1>
                    <p class="text-muted mb-0">Gerencie permissões, roles e acesso dos usuários</p>
                </div>
                <div class="d-flex">
                    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary me-2">
                        <i class="fas fa-plus"></i> Nova Permissão
                    </a>
                    <a href="{{ route('admin.permissions.roles.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-users"></i> Roles
                    </a>
                    <a href="{{ route('admin.permissions.users.index') }}" class="btn btn-info me-2">
                        <i class="fas fa-user-cog"></i> Usuários
                    </a>
                    @if(auth()->user()->hasPermissionTo('permissoes.migrate'))
                    <a href="{{ route('admin.permissions.migration.index') }}" class="btn btn-warning">
                        <i class="fas fa-exchange-alt"></i> Migração
                    </a>
                    @endif
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-key fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $permissions->count() }}</h3>
                                    <p class="mb-0">Permissões</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $roles->count() }}</h3>
                                    <p class="mb-0">Roles</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-folder fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $groupedPermissions->count() }}</h3>
                                    <p class="mb-0">Módulos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-users-cog fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $users->count() }}</h3>
                                    <p class="mb-0">Usuários</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt text-warning"></i> Ações Rápidas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <button class="btn btn-outline-primary w-100" onclick="syncPermissions()">
                                        <i class="fas fa-sync"></i> Sincronizar Permissões
                                    </button>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <button class="btn btn-outline-warning w-100" onclick="clearCache()">
                                        <i class="fas fa-trash"></i> Limpar Cache
                                    </button>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <button class="btn btn-outline-info w-100" onclick="exportPermissions()">
                                        <i class="fas fa-download"></i> Exportar
                                    </button>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <button class="btn btn-outline-success w-100" onclick="showBulkActions()">
                                        <i class="fas fa-tasks"></i> Ações em Lote
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie text-info"></i> Estatísticas Rápidas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border-end">
                                        <h4 class="text-primary">{{ $permissions->where('guard_name', 'web')->count() }}</h4>
                                        <small class="text-muted">Web</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <h4 class="text-success">{{ $roles->where('guard_name', 'web')->count() }}</h4>
                                        <small class="text-muted">Roles Web</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-info">{{ $users->filter(function($user) { return $user->roles->count() > 0; })->count() }}</h4>
                                    <small class="text-muted">Com Roles</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter"></i> Filtros
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.permissions.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="search" class="form-label">Buscar</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Nome da permissão...">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="module" class="form-label">Módulo</label>
                                <select class="form-select" id="module" name="module">
                                    <option value="">Todos os módulos</option>
                                    @foreach($groupedPermissions as $module => $perms)
                                        <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                                            {{ ucfirst($module) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="guard" class="form-label">Guard</label>
                                <select class="form-select" id="guard" name="guard">
                                    <option value="">Todos os guards</option>
                                    <option value="web" {{ request('guard') == 'web' ? 'selected' : '' }}>Web</option>
                                    <option value="api" {{ request('guard') == 'api' ? 'selected' : '' }}>API</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Permissions by Module -->
            <div class="row">
                @foreach($groupedPermissions as $module => $modulePermissions)
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-folder-open text-primary"></i> 
                                    {{ ucfirst($module) }}
                                    <span class="badge bg-secondary ms-2">{{ count($modulePermissions) }}</span>
                                </h5>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="selectAllModule('{{ $module }}')">
                                        <i class="fas fa-check-square"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="toggleModule('{{ $module }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" id="module-{{ $module }}">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th width="40">
                                                    <input type="checkbox" class="form-check-input" onclick="toggleModulePermissions('{{ $module }}')">
                                                </th>
                                                <th>Permissão</th>
                                                <th>Guard</th>
                                                <th width="100">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modulePermissions as $permission)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" 
                                                               class="form-check-input permission-checkbox" 
                                                               data-module="{{ $module }}"
                                                               data-id="{{ $permission->id }}"
                                                               value="{{ $permission->id }}">
                                                    </td>
                                                    <td>
                                                        <strong>{{ $permission->name }}</strong>
                                                        @if($permission->description)
                                                            <br><small class="text-muted">{{ $permission->description }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $permission->guard_name === 'web' ? 'primary' : 'info' }}">
                                                            {{ $permission->guard_name }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('admin.permissions.edit', $permission) }}" 
                                                               class="btn btn-outline-primary btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button class="btn btn-outline-danger btn-sm" 
                                                                    onclick="deletePermission({{ $permission->id }})">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bulk Actions Modal -->
            <div class="modal fade" id="bulkActionsModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ações em Lote</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Selecionar Ação:</label>
                                <select class="form-select" id="bulkAction">
                                    <option value="">Escolha uma ação...</option>
                                    <option value="delete">Excluir Selecionadas</option>
                                    <option value="change-guard">Alterar Guard</option>
                                    <option value="export">Exportar Selecionadas</option>
                                </select>
                            </div>
                            <div id="bulkActionOptions"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" onclick="executeBulkAction()">Executar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar axios
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
});

// Função para sincronizar permissões
function syncPermissions() {
    if (confirm('Deseja sincronizar as permissões? Esta ação pode demorar alguns segundos.')) {
        axios.post('{{ route("admin.permissions.sync") }}')
            .then(response => {
                toastr.success('Permissões sincronizadas com sucesso!');
                location.reload();
            })
            .catch(error => {
                toastr.error('Erro ao sincronizar permissões');
                console.error(error);
            });
    }
}

// Função para limpar cache
function clearCache() {
    if (confirm('Deseja limpar o cache de permissões?')) {
        axios.post('{{ route("admin.permissions.clear-cache") }}')
            .then(response => {
                toastr.success('Cache limpo com sucesso!');
            })
            .catch(error => {
                toastr.error('Erro ao limpar cache');
                console.error(error);
            });
    }
}

// Função para exportar permissões
function exportPermissions() {
    window.location.href = '{{ route("admin.permissions.export") }}';
}

// Função para mostrar ações em lote
function showBulkActions() {
    const selected = document.querySelectorAll('.permission-checkbox:checked');
    if (selected.length === 0) {
        toastr.warning('Selecione pelo menos uma permissão');
        return;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('bulkActionsModal'));
    modal.show();
}

// Função para selecionar todas as permissões de um módulo
function selectAllModule(module) {
    const checkboxes = document.querySelectorAll(`input[data-module="${module}"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

// Função para alternar visualização do módulo
function toggleModule(module) {
    const moduleDiv = document.getElementById(`module-${module}`);
    if (moduleDiv.style.display === 'none') {
        moduleDiv.style.display = 'block';
    } else {
        moduleDiv.style.display = 'none';
    }
}

// Função para alternar todas as permissões de um módulo
function toggleModulePermissions(module) {
    const checkboxes = document.querySelectorAll(`input[data-module="${module}"]`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
}

// Função para excluir permissão
function deletePermission(id) {
    if (confirm('Tem certeza que deseja excluir esta permissão?')) {
        axios.delete(`/admin/permissions/${id}`)
            .then(response => {
                toastr.success('Permissão excluída com sucesso!');
                location.reload();
            })
            .catch(error => {
                toastr.error('Erro ao excluir permissão');
                console.error(error);
            });
    }
}

// Função para executar ação em lote
function executeBulkAction() {
    const action = document.getElementById('bulkAction').value;
    const selected = Array.from(document.querySelectorAll('.permission-checkbox:checked')).map(cb => cb.value);
    
    if (!action || selected.length === 0) {
        toastr.warning('Selecione uma ação e pelo menos uma permissão');
        return;
    }
    
    axios.post('{{ route("admin.permissions.bulk-action") }}', {
        action: action,
        permissions: selected
    })
    .then(response => {
        toastr.success('Ação executada com sucesso!');
        location.reload();
    })
    .catch(error => {
        toastr.error('Erro ao executar ação');
        console.error(error);
    });
}
</script>
@endsection 