@extends('layouts.admin')

@section('title', 'Criar Novo Papel')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Criar Novo Papel</h1>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informações do Papel</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permissions.roles.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="name">Nome do Papel <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="description">Descrição</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                                                        {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
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
                    <i class="fas fa-save"></i> Salvar Papel
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
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
            var moduleCheckbox = $('#module-' + module);
            var modulePermissions = $('.permission-checkbox[data-module="' + module + '"]');
            var checkedModulePermissions = $('.permission-checkbox[data-module="' + module + '"]:checked');
            
            moduleCheckbox.prop('checked', modulePermissions.length === checkedModulePermissions.length);
            
            updateSelectAllCheckbox();
        });
        
        function updateSelectAllCheckbox() {
            var totalPermissions = $('.permission-checkbox').length;
            var checkedPermissions = $('.permission-checkbox:checked').length;
            
            $('#select-all').prop('checked', totalPermissions === checkedPermissions);
        }
    });
</script>
@endsection 