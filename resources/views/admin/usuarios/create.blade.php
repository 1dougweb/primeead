@extends('layouts.admin')

@section('title', 'Novo Usuário')

@section('page-title', 'Criar Novo Usuário')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar à Lista
        </a>
    </div>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Dados do Novo Usuário
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.usuarios.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome Completo</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Senha</label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Senha</label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo_usuario" class="form-label">Tipo de Usuário</label>
                                    <select class="form-select @error('tipo_usuario') is-invalid @enderror" 
                                            id="tipo_usuario" 
                                            name="tipo_usuario" 
                                            required>
                                        <option value="">Selecione o tipo</option>
                                        <option value="admin" {{ old('tipo_usuario') == 'admin' ? 'selected' : '' }}>
                                            👑 Administrador
                                        </option>
                                        <option value="vendedor" {{ old('tipo_usuario') == 'vendedor' ? 'selected' : '' }}>
                                            💼 Vendedor
                                        </option>
                                        <option value="colaborador" {{ old('tipo_usuario') == 'colaborador' ? 'selected' : '' }}>
                                            👤 Colaborador
                                        </option>
                                        <option value="midia" {{ old('tipo_usuario') == 'midia' ? 'selected' : '' }}>
                                            📱 Mídia
                                        </option>
                                    </select>
                                    @error('tipo_usuario')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="ativo" 
                                               name="ativo" 
                                               value="1" 
                                               {{ old('ativo', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ativo">
                                            Usuário ativo
                                        </label>
                                    </div>
                                    <small class="text-muted">Usuários inativos não conseguem fazer login</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Papéis e Permissões -->
                        <div class="card mb-4 mt-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-shield me-2"></i>
                                    Papéis (Roles)
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    Selecione os papéis que este usuário terá. Os papéis determinam quais permissões o usuário terá no sistema.
                                </p>
                                
                                <div class="row">
                                    @foreach($roles as $role)
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="role-{{ $role->id }}" 
                                                       name="roles[]" 
                                                       value="{{ $role->id }}"
                                                       {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="role-{{ $role->id }}">
                                                    <strong>{{ $role->name }}</strong>
                                                    @if(!$role->is_active)
                                                        <span class="badge bg-danger">Inativo</span>
                                                    @endif
                                                </label>
                                                <p class="text-muted small mb-0">{{ $role->description }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Dica:</strong> O tipo de usuário define o acesso básico ao sistema, enquanto os papéis definem permissões específicas.
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Criar Usuário
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 