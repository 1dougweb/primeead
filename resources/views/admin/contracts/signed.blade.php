@extends('layouts.admin')

@section('title', 'Contrato Assinado')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-signature me-2"></i>
                        Contrato Assinado
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.contracts.download-pdf', $contract) }}" class="btn btn-success">
                            <i class="fas fa-download me-1"></i>
                            Download PDF
                        </a>
                        <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Voltar
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Informações do Contrato -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">📄 Informações do Contrato</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Número:</strong></td>
                                            <td>{{ $contract->contract_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Título:</strong></td>
                                            <td>{{ $contract->title }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $contract->status_color }}">
                                                    {{ $contract->status_formatted }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Criado em:</strong></td>
                                            <td>{{ $contract->created_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Assinado em:</strong></td>
                                            <td>{{ $contract->signed_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">👤 Informações do Aluno</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Nome:</strong></td>
                                            <td>{{ $contract->matricula->nome_completo }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>CPF:</strong></td>
                                            <td>{{ $contract->matricula->cpf_formatado }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $contract->student_email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Matrícula:</strong></td>
                                            <td>{{ $contract->matricula->numero_matricula }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Curso:</strong></td>
                                            <td>{{ $contract->matricula->curso }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informações da Assinatura -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-signature me-2"></i>
                                        Detalhes da Assinatura Digital
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>Data/Hora:</strong></td>
                                                    <td>{{ $contract->signed_at->format('d/m/Y H:i:s') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Endereço IP:</strong></td>
                                                    <td>{{ $contract->signature_ip }}</td>
                                                </tr>
                                                @if($contract->signature_metadata && isset($contract->signature_metadata['user_agent']))
                                                    <tr>
                                                        <td><strong>Navegador:</strong></td>
                                                        <td>{{ Str::limit($contract->signature_metadata['user_agent'], 50) }}</td>
                                                    </tr>
                                                @endif
                                                @if($contract->signature_metadata && isset($contract->signature_metadata['screen_resolution']))
                                                    <tr>
                                                        <td><strong>Resolução:</strong></td>
                                                        <td>{{ $contract->signature_metadata['screen_resolution'] }}</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            @if($contract->signature_data)
                                                <div class="text-center">
                                                    <h6>Assinatura Capturada:</h6>
                                                    <div class="border p-3 bg-white" style="display: inline-block;">
                                                        <img src="{{ $contract->signature_data }}" alt="Assinatura Digital" style="max-width: 200px; max-height: 80px;">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conteúdo do Contrato -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-contract me-2"></i>
                                Conteúdo do Contrato
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="contract-content" style="border: 1px solid #ddd; padding: 20px; background-color: #fafafa; max-height: 500px; overflow-y: auto;">
                                {!! $contract->processContent() !!}
                            </div>
                        </div>
                    </div>

                    <!-- Informações de Validade -->
                    <div class="card mt-4 border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-shield-alt me-2"></i>
                                Validade Jurídica
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <p class="mb-2">
                                        <strong>Este documento foi assinado digitalmente e possui validade jurídica conforme:</strong>
                                    </p>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Lei nº 14.063/2020 - Lei de Assinatura Eletrônica</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Medida Provisória nº 2.200-2/2001 - ICP-Brasil</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Código Civil Brasileiro - Art. 107 e 221</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light p-3 rounded">
                                        <h6>Hash do Documento:</h6>
                                        <small class="text-muted font-monospace">
                                            {{ md5($contract->processContent() . $contract->signed_at) }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('admin.matriculas.show', $contract->matricula) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-user me-1"></i>
                                        Ver Matrícula
                                    </a>
                                </div>
                                <div>
                                    <a href="{{ route('admin.contracts.download-pdf', $contract) }}" class="btn btn-success">
                                        <i class="fas fa-download me-1"></i>
                                        Download PDF
                                    </a>
                                    <button class="btn btn-info" onclick="window.print()">
                                        <i class="fas fa-print me-1"></i>
                                        Imprimir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.contract-content {
    font-family: 'Times New Roman', serif;
    line-height: 1.6;
}

.contract-content h1, .contract-content h2, .contract-content h3 {
    color: #2c3e50;
    margin-top: 20px;
    margin-bottom: 15px;
}

.contract-content p {
    text-align: justify;
    margin-bottom: 15px;
}

.contract-content ul, .contract-content ol {
    margin-bottom: 15px;
}

.contract-content li {
    margin-bottom: 8px;
}

@media print {
    .card-header, .btn, .d-flex {
        display: none !important;
    }
    
    .contract-content {
        max-height: none !important;
        overflow: visible !important;
        border: none !important;
        background-color: white !important;
    }
}
</style>
@endsection 