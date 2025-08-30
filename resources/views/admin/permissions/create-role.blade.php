@extends('layouts.admin')

@section('title', 'Novo Role')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">➕ Novo Role</h1>
                    <p class="text-muted mb-0">Criar um novo role no sistema</p>
                </div>
                <div>
                    <a href="{{ route('admin.permissions.roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            <!-- Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-tag text-primary"></i> Informações do Role
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.permissions.roles.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome do Role <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Ex: Administrador, Editor, Visualizador"
                                           required>
                                    @error('name')
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
                                      placeholder="Descrição do role...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Role Ativo
                                </label>
                            </div>
                        </div>

                        <!-- Permissions Section -->
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-key text-warning"></i> Permissões
                                <small class="text-muted">(<span id="selectedCount">0</span> selecionadas)</small>
                            </h5>
                            
                            <!-- Select All Controls -->
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="selectAll()">
                                    <i class="fas fa-check-square"></i> Selecionar Todas
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deselectAll()">
                                    <i class="fas fa-square"></i> Desmarcar Todas
                                </button>
                            </div>

                            <!-- Quick Role Templates -->
                            <div class="mb-3">
                                <h6>Templates Rápidos:</h6>
                                <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="applyTemplate('admin')">
                                    <i class="fas fa-crown"></i> Administrador
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm me-2" onclick="applyTemplate('editor')">
                                    <i class="fas fa-edit"></i> Editor
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="applyTemplate('viewer')">
                                    <i class="fas fa-eye"></i> Visualizador
                                </button>
                            </div>

                            <!-- Permissions by Module -->
                            <div class="row">
                                @foreach($groupedPermissions as $module => $modulePermissions)
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-folder-open text-primary"></i> 
                                                    {{ ucfirst($module) }}
                                                    <span class="badge bg-secondary ms-2">{{ count($modulePermissions) }}</span>
                                                </h6>
                                                <div class="form-check">
                                                    <input class="form-check-input module-check" 
                                                           type="checkbox" 
                                                           id="module_{{ $module }}" 
                                                           data-module="{{ $module }}"
                                                           onchange="toggleModule('{{ $module }}')">
                                                    <label class="form-check-label" for="module_{{ $module }}">
                                                        Todos
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                @foreach($modulePermissions as $permission)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input permission-check" 
                                                               type="checkbox" 
                                                               id="permission_{{ $permission->id }}" 
                                                               name="permissions[]" 
                                                               value="{{ $permission->id }}"
                                                               data-module="{{ $module }}"
                                                               onchange="updateCounts()">
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            <strong>{{ $permission->name }}</strong>
                                                            @if($permission->description)
                                                                <br><small class="text-muted">{{ $permission->description }}</small>
                                                            @endif
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.permissions.roles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Criar Role
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
    updateCounts();
});

function selectAll() {
    const checkboxes = document.querySelectorAll('.permission-check');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateModuleCheckboxes();
    updateCounts();
}

function deselectAll() {
    const checkboxes = document.querySelectorAll('.permission-check');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateModuleCheckboxes();
    updateCounts();
}

function toggleModule(module) {
    const moduleCheckbox = document.getElementById(`module_${module}`);
    const permissionCheckboxes = document.querySelectorAll(`input[data-module="${module}"]`);
    
    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = moduleCheckbox.checked;
    });
    
    updateCounts();
}

function updateModuleCheckboxes() {
    const modules = @json($groupedPermissions->keys());
    
    modules.forEach(module => {
        const moduleCheckbox = document.getElementById(`module_${module}`);
        const permissionCheckboxes = document.querySelectorAll(`input[data-module="${module}"]`);
        const checkedPermissions = document.querySelectorAll(`input[data-module="${module}"]:checked`);
        
        if (checkedPermissions.length === 0) {
            moduleCheckbox.checked = false;
            moduleCheckbox.indeterminate = false;
        } else if (checkedPermissions.length === permissionCheckboxes.length) {
            moduleCheckbox.checked = true;
            moduleCheckbox.indeterminate = false;
        } else {
            moduleCheckbox.checked = false;
            moduleCheckbox.indeterminate = true;
        }
    });
}

function updateCounts() {
    const selectedPermissions = document.querySelectorAll('.permission-check:checked');
    document.getElementById('selectedCount').textContent = selectedPermissions.length;
    updateModuleCheckboxes();
}

function applyTemplate(template) {
    // Desmarcar tudo primeiro
    deselectAll();
    
    const permissions = @json($permissions->pluck('name', 'id'));
    
    // Definir templates de permissões
    const templates = {
        'admin': Object.keys(permissions), // Todas as permissões
        'editor': Object.keys(permissions).filter(id => {
            const name = permissions[id];
            return name.includes('.index') || name.includes('.create') || name.includes('.edit') || name.includes('.show');
        }),
        'viewer': Object.keys(permissions).filter(id => {
            const name = permissions[id];
            return name.includes('.index') || name.includes('.show');
        })
    };
    
    // Aplicar template
    if (templates[template]) {
        templates[template].forEach(permissionId => {
            const checkbox = document.getElementById(`permission_${permissionId}`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
    
    updateCounts();
}
</script>
@endsection 