@extends('layouts.admin')

@section('title', 'Nova Campanha - Passo 3')

@section('page-title', 'Nova Campanha de Email')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.email-campaigns.create-step2') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar
        </a>
        <a href="{{ route('admin.email-campaigns.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-times me-2"></i>
            Cancelar
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <!-- Progress Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="step-progress">
                            <div class="step completed">
                                <div class="step-circle">1</div>
                                <div class="step-label">Conteúdo</div>
                            </div>
                            <div class="step-line completed"></div>
                            <div class="step completed">
                                <div class="step-circle">2</div>
                                <div class="step-label">Destinatários</div>
                            </div>
                            <div class="step-line completed"></div>
                            <div class="step active">
                                <div class="step-circle">3</div>
                                <div class="step-label">Confirmação</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Resumo da Campanha -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i>
                        Preview da Campanha
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Nome da Campanha</h6>
                            <p class="mb-3">{{ $campaignData['name'] }}</p>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Assunto do Email</h6>
                            <p class="mb-3">{{ $campaignData['subject'] }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Conteúdo do Email</h6>
                        <div class="border rounded p-3 bg-white" style="max-height: 400px; overflow-y: auto;">
                            {!! $campaignData['content'] !!}
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Tipo de Seleção</h6>
                            <p class="mb-3">
                                @switch($selectionData['selection_type'])
                                    @case('all')
                                        <span class="badge bg-primary">Todos os Leads</span>
                                        @break
                                    @case('status')
                                        <span class="badge bg-info">Por Status: {{ ucfirst(str_replace('_', ' ', $selectionData['status'])) }}</span>
                                        @break
                                    @case('course')
                                        <span class="badge bg-success">Por Curso: {{ $selectionData['course'] }}</span>
                                        @break
                                    @case('modality')
                                        <span class="badge bg-warning">Por Modalidade: {{ $selectionData['modality'] }}</span>
                                        @break
                                    @case('custom')
                                        <span class="badge bg-secondary">Filtros Personalizados</span>
                                        @if(!empty($selectionData['custom_filters']))
                                            <div class="mt-2 small text-muted">
                                                @if(!empty($selectionData['custom_filters']['status']))
                                                    <div>Status: {{ ucfirst(str_replace('_', ' ', $selectionData['custom_filters']['status'])) }}</div>
                                                @endif
                                                @if(!empty($selectionData['custom_filters']['curso']))
                                                    <div>Curso: {{ $selectionData['custom_filters']['curso'] }}</div>
                                                @endif
                                            </div>
                                        @endif
                                        @break
                                @endswitch
                            </p>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Total de Destinatários</h6>
                            <p class="mb-3">
                                <span class="h4 text-primary">{{ count($selectedLeads) }}</span> leads selecionados
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Formulário de Confirmação -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Finalizar Campanha
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Atenção!</strong> Após criar a campanha, você poderá:
                        <ul class="mb-0 mt-2">
                            <li>Enviar emails de teste</li>
                            <li>Agendar o envio para uma data específica</li>
                            <li>Enviar imediatamente</li>
                            <li>Editar a campanha enquanto ela estiver em rascunho</li>
                        </ul>
                    </div>
                    
                    <form action="{{ route('admin.email-campaigns.create-finish') }}" method="POST">
                        @csrf
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.email-campaigns.create-step2') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Anterior: Destinatários
                            </a>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-rocket me-2"></i>
                                Criar Campanha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Lista de Destinatários -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Destinatários Selecionados
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total:</span>
                            <span class="badge bg-primary">{{ count($selectedLeads) }} leads</span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="recipients-list" style="max-height: 500px; overflow-y: auto;">
                        @if(count($selectedLeads) > 0)
                            @foreach($selectedLeads->take(50) as $lead)
                                <div class="recipient-item mb-2 p-2 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-user text-muted me-2 mt-1"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold small">{{ $lead->nome }}</div>
                                            <div class="text-muted small">{{ $lead->email }}</div>
                                            @if($lead->curso)
                                                <div class="text-muted small">
                                                    <i class="fas fa-graduation-cap me-1"></i>
                                                    {{ $lead->curso }}
                                                </div>
                                            @endif
                                        </div>
                                        <span class="badge bg-{{ 
                                            $lead->etiqueta === 'pendente' ? 'secondary' : 
                                            ($lead->etiqueta === 'contatado' ? 'info' : 
                                            ($lead->etiqueta === 'interessado' ? 'warning' : 
                                            ($lead->etiqueta === 'matriculado' ? 'success' : 'danger'))) 
                                        }} small">
                                            {{ ucfirst(str_replace('_', ' ', $lead->etiqueta)) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if(count($selectedLeads) > 50)
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Mostrando os primeiros 50 de {{ count($selectedLeads) }} destinatários
                                    </small>
                                </div>
                            @endif
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <p>Nenhum lead selecionado</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .step-progress {
        display: flex;
        align-items: center;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }
    
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }
    
    .step.active .step-circle {
        background-color: #007bff;
        color: white;
    }
    
    .step.completed .step-circle {
        background-color: #28a745;
        color: white;
    }
    
    .step-label {
        font-size: 12px;
        color: #6c757d;
        white-space: nowrap;
    }
    
    .step.active .step-label {
        color: #007bff;
        font-weight: bold;
    }
    
    .step.completed .step-label {
        color: #28a745;
        font-weight: bold;
    }
    
    .step-line {
        flex: 1;
        height: 2px;
        background-color: #e9ecef;
        margin: 0 15px;
        margin-bottom: 20px;
    }
    
    .step-line.completed {
        background-color: #28a745;
    }
    
    .recipient-item {
        transition: background-color 0.2s ease;
    }
    
    .recipient-item:hover {
        background-color: #f8f9fa;
    }
    
    .recipients-list {
        scrollbar-width: thin;
        scrollbar-color: #ccc transparent;
    }
    
    .recipients-list::-webkit-scrollbar {
        width: 6px;
    }
    
    .recipients-list::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .recipients-list::-webkit-scrollbar-thumb {
        background-color: #ccc;
        border-radius: 3px;
    }
    
    .recipients-list::-webkit-scrollbar-thumb:hover {
        background-color: #999;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Adicionar animação de entrada
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@endsection 