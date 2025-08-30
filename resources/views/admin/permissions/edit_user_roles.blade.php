@extends('layouts.admin')

@section('title', 'Editar Papéis do Usuário')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Editar Papéis do Usuário: {{ $user->name }}</h1>
        <a href="{{ route('admin.permissions.user-roles') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informações do Usuário</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nome:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Tipo de Usuário:</strong> {{ $user->tipo_usuario_formatado }}</p>
                    <p>
                        <strong>Status:</strong>
                        @if($user->ativo)
                            <span class="badge badge-success">Ativo</span>
                        @else
                            <span class="badge badge-danger">Inativo</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Atribuir Papéis</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permissions.user-roles.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="alert alert-info">
                    Selecione os papéis que deseja atribuir a este usuário.
                </div>
                
                <div class="row">
                    @foreach($roles as $role)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 {{ $role->is_active ? '' : 'bg-light' }}">
                                <div class="card-body">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" 
                                            id="role-{{ $role->id }}" 
                                            name="roles[]" 
                                            value="{{ $role->id }}"
                                            {{ in_array($role->id, old('roles', $userRoles)) ? 'checked' : '' }}
                                            {{ !$role->is_active ? 'disabled' : '' }}>
                                        <label class="custom-control-label" for="role-{{ $role->id }}">
                                            <strong>{{ $role->name }}</strong>
                                            @if(!$role->is_active)
                                                <span class="badge badge-danger">Inativo</span>
                                            @endif
                                        </label>
                                    </div>
                                    
                                    <p class="text-muted mt-2 mb-2">{{ $role->description }}</p>
                                    
                                    <small>
                                        <strong>Permissões:</strong> {{ $role->permissions->count() }}
                                        <a href="#" data-toggle="modal" data-target="#rolePermissionsModal{{ $role->id }}" class="ml-2">
                                            Ver detalhes
                                        </a>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal de Permissões do Papel -->
                        <div class="modal fade" id="rolePermissionsModal{{ $role->id }}" tabindex="-1" role="dialog" aria-labelledby="rolePermissionsModalLabel{{ $role->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="rolePermissionsModalLabel{{ $role->id }}">Permissões do papel: {{ $role->name }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        @if($role->permissions->isEmpty())
                                            <p class="text-muted">Este papel não possui permissões atribuídas.</p>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Nome</th>
                                                            <th>Módulo</th>
                                                            <th>Descrição</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $permissionsByModule = $role->permissions->groupBy('module');
                                                        @endphp
                                                        
                                                        @foreach($permissionsByModule as $module => $permissions)
                                                            <tr class="table-primary">
                                                                <td colspan="3"><strong>{{ ucfirst($module) }}</strong></td>
                                                            </tr>
                                                            @foreach($permissions as $permission)
                                                                <tr>
                                                                    <td>{{ $permission->name }}</td>
                                                                    <td><code>{{ $permission->slug }}</code></td>
                                                                    <td>{{ $permission->description }}</td>
                                                                </tr>
                                                            @endforeach
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @error('roles')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Papéis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 