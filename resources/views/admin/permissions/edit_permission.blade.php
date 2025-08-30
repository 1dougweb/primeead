@extends('layouts.admin')

@section('title', 'Editar Permissão')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Editar Permissão: {{ $permission->name }}</h1>
        <a href="{{ route('admin.permissions.permissions') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informações da Permissão</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permissions.update', $permission) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="name">Nome da Permissão <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $permission->name) }}" required>
                    <small class="form-text text-muted">Ex: Ver Usuários, Criar Produtos, etc.</small>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="module">Módulo <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select class="custom-select @error('module') is-invalid @enderror" id="module" name="module">
                            @foreach($modules as $module)
                                <option value="{{ $module }}" {{ old('module', $permission->module) == $module ? 'selected' : '' }}>
                                    {{ ucfirst($module) }}
                                </option>
                            @endforeach
                            <option value="outro">Outro...</option>
                        </select>
                    </div>
                    <div id="custom-module-container" class="mt-2 d-none">
                        <input type="text" class="form-control" id="custom-module" placeholder="Nome do novo módulo">
                        <small class="form-text text-muted">Digite o nome do novo módulo</small>
                    </div>
                    @error('module')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="description">Descrição</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $permission->description) }}</textarea>
                    <small class="form-text text-muted">Descreva o que esta permissão permite fazer.</small>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" {{ old('is_active', $permission->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Permissão Ativa</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Atualizar Permissão
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Mostrar/esconder campo de módulo personalizado
        $('#module').change(function() {
            if ($(this).val() === 'outro') {
                $('#custom-module-container').removeClass('d-none');
            } else {
                $('#custom-module-container').addClass('d-none');
            }
        });
        
        // Atualizar o valor do módulo ao enviar o formulário
        $('form').submit(function() {
            if ($('#module').val() === 'outro') {
                var customModule = $('#custom-module').val().toLowerCase();
                if (customModule) {
                    $('#module').append($('<option>', {
                        value: customModule,
                        text: customModule
                    }));
                    $('#module').val(customModule);
                }
            }
        });
    });
</script>
@endsection 