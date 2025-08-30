@extends('layouts.admin')

@section('title', 'Usuários e Permissões')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">👤 Usuários e Permissões</h1>
                    <p class="text-muted mb-0">Gerencie os roles dos usuários do sistema</p>
                </div>
                <div>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
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
                                    <h3 class="mb-0">{{ $users->count() }}</h3>
                                    <p class="mb-0">Total de Usuários</p>
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
                                    <i class="fas fa-user-check fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $users->filter(function($user) { return $user->roles->count() > 0; })->count() }}</h3>
                                    <p class="mb-0">Com Roles</p>
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
                                    <i class="fas fa-user-times fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $users->filter(function($user) { return $user->roles->count() === 0; })->count() }}</h3>
                                    <p class="mb-0">Sem Roles</p>
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
                                    <i class="fas fa-user-tag fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $roles->count() }}</h3>
                                    <p class="mb-0">Roles Disponíveis</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> Lista de Usuários
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Email</th>
                                    <th>Tipo</th>
                                    <th>Roles</th>
                                    <th>Último Login</th>
                                    <th width="150">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="avatar-circle bg-primary text-white">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <strong>{{ $user->name }}</strong>
                                                    <br><small class="text-muted">ID: {{ $user->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->tipo_usuario === 'admin')
                                                <span class="badge bg-danger">Admin</span>
                                            @elseif($user->tipo_usuario === 'vendedor')
                                                <span class="badge bg-warning">Vendedor</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($user->tipo_usuario) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->roles->count() > 0)
                                                @foreach($user->roles as $role)
                                                    <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">Nenhum role</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->last_login_at)
                                                <small class="text-muted">{{ $user->last_login_at->format('d/m/Y H:i') }}</small>
                                            @else
                                                <small class="text-muted">Nunca</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="editUserRoles({{ $user->id }})" 
                                                        title="Editar Roles">
                                                    <i class="fas fa-user-cog"></i>
                                                </button>
                                                <button class="btn btn-outline-info" 
                                                        onclick="viewUserPermissions({{ $user->id }})" 
                                                        title="Ver Permissões">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Nenhum usuário encontrado</p>
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

<!-- Edit User Roles Modal -->
<div class="modal fade" id="editUserRolesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Roles do Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserRolesForm">
                    <input type="hidden" id="editUserId" name="user_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Usuário:</label>
                        <div id="editUserInfo" class="alert alert-info"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Roles:</label>
                        <div id="editUserRolesList">
                            @foreach($roles as $role)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="edit_role_{{ $role->id }}" 
                                           name="roles[]" 
                                           value="{{ $role->id }}">
                                    <label class="form-check-label" for="edit_role_{{ $role->id }}">
                                        <strong>{{ $role->name }}</strong>
                                        @if($role->description)
                                            <br><small class="text-muted">{{ $role->description }}</small>
                                        @endif
                                        <br><small class="text-info">{{ $role->permissions->count() }} permissões</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveUserRoles()">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- View User Permissions Modal -->
<div class="modal fade" id="viewUserPermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Permissões do Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="userPermissionsContent">
                    <!-- Conteúdo será carregado via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>

<script>
function editUserRoles(userId) {
    // Buscar dados do usuário
    axios.get(`/admin/permissions/users/${userId}/edit`)
        .then(response => {
            const user = response.data.user;
            const userRoles = response.data.userRoles;
            
            // Preencher informações do usuário
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUserInfo').innerHTML = `
                <strong>${user.name}</strong><br>
                <small>${user.email}</small>
            `;
            
            // Marcar roles do usuário
            const roleCheckboxes = document.querySelectorAll('#editUserRolesList input[type="checkbox"]');
            roleCheckboxes.forEach(checkbox => {
                checkbox.checked = userRoles.includes(parseInt(checkbox.value));
            });
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('editUserRolesModal'));
            modal.show();
        })
        .catch(error => {
            toastr.error('Erro ao carregar dados do usuário');
            console.error(error);
        });
}

function saveUserRoles() {
    const userId = document.getElementById('editUserId').value;
    const formData = new FormData(document.getElementById('editUserRolesForm'));
    
    axios.put(`/admin/permissions/users/${userId}/roles`, formData)
        .then(response => {
            toastr.success('Roles atualizados com sucesso!');
            bootstrap.Modal.getInstance(document.getElementById('editUserRolesModal')).hide();
            location.reload();
        })
        .catch(error => {
            toastr.error('Erro ao atualizar roles');
            console.error(error);
        });
}

function viewUserPermissions(userId) {
    // Buscar permissões do usuário
    axios.get(`/admin/permissions/users/${userId}/permissions`)
        .then(response => {
            const user = response.data.user;
            const permissions = response.data.permissions;
            
            let content = `
                <div class="mb-3">
                    <h6>Usuário: ${user.name}</h6>
                    <p class="text-muted">${user.email}</p>
                </div>
                
                <div class="mb-3">
                    <h6>Roles:</h6>
                    ${user.roles.map(role => `<span class="badge bg-primary me-1">${role.name}</span>`).join('')}
                </div>
                
                <div class="mb-3">
                    <h6>Permissões (${permissions.length}):</h6>
                    <div class="row">
            `;
            
            // Agrupar permissões por módulo
            const groupedPermissions = {};
            permissions.forEach(permission => {
                if (!groupedPermissions[permission.module]) {
                    groupedPermissions[permission.module] = [];
                }
                groupedPermissions[permission.module].push(permission);
            });
            
            Object.keys(groupedPermissions).forEach(module => {
                content += `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">${module.charAt(0).toUpperCase() + module.slice(1)}</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    ${groupedPermissions[module].map(permission => `
                                        <li><small><i class="fas fa-check text-success me-1"></i> ${permission.name}</small></li>
                                    `).join('')}
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            content += '</div></div>';
            
            document.getElementById('userPermissionsContent').innerHTML = content;
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('viewUserPermissionsModal'));
            modal.show();
        })
        .catch(error => {
            toastr.error('Erro ao carregar permissões do usuário');
            console.error(error);
        });
}
</script>
@endsection 