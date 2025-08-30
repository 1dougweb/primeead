@extends('layouts.admin')

@section('title', 'Templates de Email')

@section('page-title', 'Templates de Email')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar às Configurações
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p>
                        Aqui você pode personalizar os templates de email enviados pelo sistema. 
                        Selecione um template para editar seu conteúdo HTML e visualizar como ficará para os destinatários.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($templates as $key => $template)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $template['name'] }}</h5>
                    <span class="badge {{ $template['exists'] ? 'bg-success' : 'bg-warning' }}">
                        {{ $template['exists'] ? 'Ativo' : 'Não configurado' }}
                    </span>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ $template['description'] }}</p>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="text-muted small">{{ $template['file'] }}</span>
                        <a href="{{ route('admin.email-templates.edit', $key) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> Editar Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        Dicas para edição de templates
                    </h5>
                    <ul class="mb-0">
                        <li>Use HTML para formatar seu email (negrito, itálico, cores, etc)</li>
                        <li>Utilize as variáveis disponíveis para personalizar o conteúdo</li>
                        <li>Teste o template antes de salvar usando a função de preview</li>
                        <li>Lembre-se que nem todos os clientes de email suportam CSS avançado</li>
                        <li>Mantenha o design responsivo para visualização em dispositivos móveis</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 