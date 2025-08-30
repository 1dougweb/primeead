@extends('layouts.admin')

@section('title', 'Nova Permissão')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">➕ Nova Permissão</h1>
                    <p class="text-muted mb-0">Criar uma nova permissão no sistema</p>
                </div>
                <div>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            <!-- Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key text-primary"></i> Informações da Permissão
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.permissions.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome da Permissão <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Ex: users.create"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Use formato: modulo.acao (ex: users.create, posts.edit)</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="module" class="form-label">Módulo <span class="text-danger">*</span></label>
                                    <select class="form-select @error('module') is-invalid @enderror" 
                                            id="module" 
                                            name="module" 
                                            required>
                                        <option value="">Selecione um módulo</option>
                                        @foreach($modules as $module)
                                            <option value="{{ $module }}" {{ old('module') === $module ? 'selected' : '' }}>
                                                {{ ucfirst($module) }}
                                            </option>
                                        @endforeach
                                        <option value="novo" {{ old('module') === 'novo' ? 'selected' : '' }}>
                                            🆕 Novo Módulo
                                        </option>
                                    </select>
                                    @error('module')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row" id="newModuleField" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="new_module" class="form-label">Nome do Novo Módulo</label>
                                    <input type="text" 
                                           class="form-control @error('new_module') is-invalid @enderror" 
                                           id="new_module" 
                                           name="new_module" 
                                           value="{{ old('new_module') }}" 
                                           placeholder="Ex: produtos, vendas, relatórios">
                                    @error('new_module')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guard_name" class="form-label">Guard</label>
                                    <select class="form-select @error('guard_name') is-invalid @enderror" 
                                            id="guard_name" 
                                            name="guard_name">
                                        <option value="web" {{ old('guard_name', 'web') === 'web' ? 'selected' : '' }}>Web</option>
                                        <option value="api" {{ old('guard_name') === 'api' ? 'selected' : '' }}>API</option>
                                    </select>
                                    @error('guard_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Descrição da permissão...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Quick Actions -->
                        <div class="mb-4">
                            <h6 class="mb-3">Ações Rápidas</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-info btn-sm mb-2" onclick="fillCRUD('index')">
                                        <i class="fas fa-list"></i> Listar (index)
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm mb-2" onclick="fillCRUD('create')">
                                        <i class="fas fa-plus"></i> Criar (create)
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-warning btn-sm mb-2" onclick="fillCRUD('edit')">
                                        <i class="fas fa-edit"></i> Editar (edit)
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm mb-2" onclick="fillCRUD('delete')">
                                        <i class="fas fa-trash"></i> Excluir (delete)
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Criar Permissão
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const moduleSelect = document.getElementById('module');
    const newModuleField = document.getElementById('newModuleField');
    const guardSelect = document.getElementById('guard_name');
    
    // Show/hide new module field
    moduleSelect.addEventListener('change', function() {
        if (this.value === 'novo') {
            newModuleField.style.display = 'block';
        } else {
            newModuleField.style.display = 'none';
        }
    });
    
    // Check initial state
    if (moduleSelect.value === 'novo') {
        newModuleField.style.display = 'block';
    }
});

function fillCRUD(action) {
    const moduleSelect = document.getElementById('module');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    
    if (moduleSelect.value && moduleSelect.value !== 'novo') {
        const module = moduleSelect.value;
        nameInput.value = `${module}.${action}`;
        
        const descriptions = {
            'index': `Visualizar lista de ${module}`,
            'create': `Criar novo ${module}`,
            'edit': `Editar ${module}`,
            'delete': `Excluir ${module}`
        };
        
        descriptionInput.value = descriptions[action] || '';
    } else {
        alert('Selecione um módulo primeiro');
    }
}
</script>
@endsection 