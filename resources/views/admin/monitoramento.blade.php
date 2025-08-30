@extends('layouts.admin')

@section('title', 'Monitoramento de Atendimento')

@section('page-title', 'Monitoramento de Atendimento')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Voltar ao Dashboard
        </a>
    </div>
@endsection

@push('styles')
<style>
    .monitoring-chart-container {
        height: 400px !important;
        width: 100% !important;
        position: relative !important;
        background-color: #f8f9fa !important;
        border-radius: 8px !important;
        margin: 0 !important;
        padding: 20px !important;
    }
    
    .chart-wrapper {
        height: 100% !important;
        width: 100% !important;
        position: relative !important;
        margin: 0 !important;
    }
    
    #conversionChart {
        width: 100% !important;
        height: 100% !important;
        max-width: none !important;
        max-height: none !important;
        display: block !important;
        position: relative !important;
    }
</style>
@endpush

@section('content')
    <!-- Gráfico de Conversão -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Métricas de Conversão
            </h5>
        </div>
        <div class="card-body">
            <div class="monitoring-chart-container">
                <div class="chart-wrapper">
                    <canvas id="conversionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['total_leads'] }}</h4>
                            <p class="mb-0">Total de Leads</p>
                        </div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['leads_travados'] }}</h4>
                            <p class="mb-0">Em Atendimento</p>
                        </div>
                        <i class="fas fa-lock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>
                Filtros
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.monitoramento') }}" class="monitoring-filters">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos os status</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="usuario" class="form-label fw-bold">Responsável</label>
                        <select class="form-select" id="usuario" name="usuario">
                            <option value="">Todos os usuários</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}" {{ request('usuario') == $usuario->id ? 'selected' : '' }}>
                                    {{ $usuario->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="locked_status" class="form-label fw-bold">Situação</label>
                        <select class="form-select" id="locked_status" name="locked_status">
                            <option value="">Todos</option>
                            <option value="locked" {{ request('locked_status') == 'locked' ? 'selected' : '' }}>
                                Em Atendimento
                            </option>
                            <option value="unlocked" {{ request('locked_status') == 'unlocked' ? 'selected' : '' }}>
                                Livres
                            </option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="data_inicio" class="form-label fw-bold">Data Início</label>
                        <input type="date" 
                               class="form-control" 
                               id="data_inicio" 
                               name="data_inicio" 
                               value="{{ request('data_inicio') }}"
                               max="{{ now()->format('Y-m-d') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="data_fim" class="form-label fw-bold">Data Fim</label>
                        <input type="date" 
                               class="form-control" 
                               id="data_fim" 
                               name="data_fim" 
                               value="{{ request('data_fim') }}"
                               max="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
                
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>
                        Filtrar
                    </button>
                    <a href="{{ route('admin.monitoramento') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Leads -->
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Leads em Monitoramento ({{ $inscricoes->total() }})
            </h5>
            
            @if(request()->hasAny(['status', 'usuario', 'locked_status', 'data_inicio', 'data_fim']))
                <span class="badge bg-primary">Filtros aplicados</span>
            @endif
        </div>
        
        <div class="card-body p-0">
            @if($inscricoes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Status</th>
                                <th>Responsável</th>
                                <th>Situação</th>
                                <th>Travado em</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inscricoes as $inscricao)
                                <tr class="{{ $inscricao->isLocked() ? 'table-warning' : '' }}">
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $inscricao->id }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $inscricao->nome }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $inscricao->email }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $statusMap = [
                                                'pendente' => '🟡 Pendente',
                                                'contatado' => '🔵 Contatado',
                                                'interessado' => '🟢 Interessado',
                                                'nao_interessado' => '🔴 Não Interessado',
                                                'matriculado' => '⭐ Matriculado'
                                            ];
                                            $statusColors = [
                                                'pendente' => 'bg-warning',
                                                'contatado' => 'bg-info',
                                                'interessado' => 'bg-success',
                                                'nao_interessado' => 'bg-danger',
                                                'matriculado' => 'bg-primary'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusColors[$inscricao->etiqueta] ?? 'bg-secondary' }}">
                                            {{ $statusMap[$inscricao->etiqueta] ?? $inscricao->etiqueta }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($inscricao->lockedBy)
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle me-2 text-primary"></i>
                                                <div>
                                                    <strong>{{ $inscricao->lockedBy->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $inscricao->lockedBy->tipo_usuario_formatado }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-user-slash me-1"></i>
                                                Nenhum
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inscricao->isLocked())
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-lock me-1"></i>
                                                Em Atendimento
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="fas fa-unlock me-1"></i>
                                                Livre
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inscricao->locked_at)
                                            <small>
                                                {{ $inscricao->locked_at->format('d/m/Y') }}<br>
                                                <span class="text-muted">{{ $inscricao->locked_at->format('H:i:s') }}</span>
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            {{ $inscricao->created_at->format('d/m/Y') }}<br>
                                            <span class="text-muted">{{ $inscricao->created_at->format('H:i:s') }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="verDetalhes({{ $inscricao->id }})"
                                                    title="Ver Detalhes">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            @if($inscricao->isLocked())
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="destravar({{ $inscricao->id }})"
                                                        title="Destravar Lead">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                            @endif
                                            
                                            <a href="https://wa.me/55{{ preg_replace('/[^0-9]/', '', $inscricao->telefone) }}?text=Olá {{ $inscricao->nome }}, tudo bem? Estou entrando em contato sobre sua inscrição no EJA." 
                                               target="_blank"
                                               class="btn btn-sm btn-outline-success" 
                                               title="WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div class="card-footer">
                    {{ $inscricoes->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum lead encontrado</h5>
                    <p class="text-muted">Tente ajustar os filtros para encontrar os leads desejados.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal para detalhes -->
    <div class="modal fade" id="detalhesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Lead</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalhesConteudo">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aguardar 1 segundo para garantir que o Chart.js esteja carregado
    setTimeout(function() {
        // Verificar se Chart.js está disponível
        if (typeof Chart === 'undefined') {
            console.error('Chart.js não está carregado');
            return;
        }

        // Dados do chart
        const chartData = @json($chartData ?? []);
        console.log('Dados do chart:', chartData);

        // Configuração do chart
        const ctx = document.getElementById('conversionChart').getContext('2d');
        
        // Destruir chart existente se houver
        if (window.monitoringChart instanceof Chart) {
            window.monitoringChart.destroy();
        }

        // Criar novo chart
        window.monitoringChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels || [],
                datasets: [
                    {
                        label: 'Leads Recebidos',
                        data: chartData.leads || [],
                        borderColor: '#36A2EB',
                        backgroundColor: '#36A2EB20',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Contatados',
                        data: chartData.contatados || [],
                        borderColor: '#FF9F40',
                        backgroundColor: '#FF9F4020',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Matriculados',
                        data: chartData.matriculados || [],
                        borderColor: '#4BC0C0',
                        backgroundColor: '#4BC0C020',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                }
            }
        });

        console.log('Chart inicializado com sucesso');
    }, 1000);
});
</script>
@endpush 