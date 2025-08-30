@extends('layouts.admin')

@section('title', 'Editar Papel')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Editar Papel: {{ $role->name }}</h1>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informações do Papel</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permissions.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="name">Nome do Papel <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="description">Descrição</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $role->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" {{ old('is_active', $role->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Papel Ativo</label>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Permissões</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            Selecione as permissões que este papel terá acesso.
                        </div>
                        
                        <div class="form-group mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="select-all">
                                <label class="custom-control-label" for="select-all"><strong>Selecionar/Desmarcar Todas</strong></label>
                            </div>
                        </div>
                        
                        @foreach($modules as $module)
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input module-checkbox" id="module-{{ $module }}">
                                            <label class="custom-control-label" for="module-{{ $module }}">
                                                <strong>{{ ucfirst($module) }}</strong>
                                            </label>
                                        </div>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($permissions->where('module', $module) as $permission)
                                            <div class="col-md-3 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input permission-checkbox" 
                                                        data-module="{{ $module }}"
                                                        id="permission-{{ $permission->id }}" 
                                                        name="permissions[]" 
                                                        value="{{ $permission->id }}"
                                                        {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="permission-{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        @error('permissions')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Atualizar Papel
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Inicializar checkboxes de módulos
        updateModuleCheckboxes();
        updateSelectAllCheckbox();
        
        // Selecionar/desmarcar todas as permissões
        $('#select-all').change(function() {
            $('.permission-checkbox').prop('checked', $(this).prop('checked'));
            $('.module-checkbox').prop('checked', $(this).prop('checked'));
        });
        
        // Selecionar/desmarcar permissões por módulo
        $('.module-checkbox').change(function() {
            var module = $(this).attr('id').replace('module-', '');
            $('.permission-checkbox[data-module="' + module + '"]').prop('checked', $(this).prop('checked'));
            
            updateSelectAllCheckbox();
        });
        
        // Atualizar checkbox do módulo quando as permissões individuais são alteradas
        $('.permission-checkbox').change(function() {
            var module = $(this).data('module');
            updateModuleCheckbox(module);
            updateSelectAllCheckbox();
        });
        
        function updateModuleCheckboxes() {
            var modules = [];
            $('.module-checkbox').each(function() {
                var module = $(this).attr('id').replace('module-', '');
                modules.push(module);
            });
            
            modules.forEach(function(module) {
                updateModuleCheckbox(module);
            });
        }
        
        function updateModuleCheckbox(module) {
            var moduleCheckbox = $('#module-' + module);
            var modulePermissions = $('.permission-checkbox[data-module="' + module + '"]');
            var checkedModulePermissions = $('.permission-checkbox[data-module="' + module + '"]:checked');
            
            moduleCheckbox.prop('checked', modulePermissions.length === checkedModulePermissions.length && modulePermissions.length > 0);
        }
        
        function updateSelectAllCheckbox() {
            var totalPermissions = $('.permission-checkbox').length;
            var checkedPermissions = $('.permission-checkbox:checked').length;
            
            $('#select-all').prop('checked', totalPermissions === checkedPermissions && totalPermissions > 0);
        }
    });
</script>
@endsection 