@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('page-actions')
    <div class="d-flex gap-2">
        <a href="{{ route('admin.inscricoes') }}" class="btn btn-primary">
            <i class="fas fa-users me-2"></i>
            Ver Inscrições
        </a>
        <a href="{{ route('admin.inscricoes.exportar') }}" class="btn btn-success">
        <i class="fas fa-download me-2"></i>Exportar CSV</a>
    </div>
@endsection

@section('content')
    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center py-0">
                        <div>
                            <div class="stats-number">{{ $totalInscricoes }}</div>
                            <div class="stats-label">Total de Inscrições</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center py-0">
                        <div>
                            <div class="stats-number">{{ $inscricoesHoje }}</div>
                            <div class="stats-label">Hoje</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center py-0">
                        <div>
                            <div class="stats-number">{{ $inscricoesUltimos7Dias }}</div>
                            <div class="stats-label">Últimos 7 dias</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-calendar-week fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%); color: white;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center py-0">
                        <div>
                            <div class="stats-number">{{ $inscricoesUltimos30Dias }}</div>
                            <div class="stats-label">Últimos 30 dias</div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de Cursos -->
        <div class="col-lg-4 mb-4">
            <div class="card chart-card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Inscrições por Curso
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="cursosChart" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráfico de Modalidades -->
        <div class="col-lg-4 mb-4">
            <div class="card chart-card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Modalidades de Ensino
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="modalidadesChart" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Inscrições Recentes -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Inscrições Recentes
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($inscricoesRecentes->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($inscricoesRecentes as $inscricao)
                                <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $inscricao->nome }}</h6>
                                            <p class="mb-1 text-muted small">{{ $inscricao->email }}</p>
                                            <small class="text-muted">
                                                <span class="badge bg-primary">{{ $availableCourses[$inscricao->curso] ?? $inscricao->curso }}</span>
                                                <span class="badge bg-success">{{ $availableModalities[$inscricao->modalidade] ?? $inscricao->modalidade }}</span>
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            {{ $inscricao->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.inscricoes') }}" class="btn btn-outline-primary btn-sm">
                                Ver todas as inscrições
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhuma inscrição encontrada</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Métricas de Conversão -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card chart-card">
                <div class="card-header">
                    <h5>
                        <i class="fas fa-chart-line"></i>
                        Métricas de Conversão
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="chart-subtitle">Métricas de conversão no período selecionado</p>
                        <div class="dropdown chart-period-dropdown">
                            <button class="btn btn-sm dropdown-toggle" type="button" id="periodDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Last 3 months <i class="fas fa-chevron-down ms-1"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="periodDropdown">
                                <li><a class="dropdown-item" href="#" data-period="7">Last 7 days</a></li>
                                <li><a class="dropdown-item" href="#" data-period="30">Last 30 days</a></li>
                                <li><a class="dropdown-item active" href="#" data-period="90">Last 3 months</a></li>
                                <li><a class="dropdown-item" href="#" data-period="365">Last year</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <div id="chartLoadingIndicator" class="chart-loading" style="display: none;">
                            <div class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                                <p class="mt-2 mb-0">Atualizando gráfico...</p>
                            </div>
                        </div>
                        <canvas id="conversaoChart" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
@endsection

@push('scripts')
<!-- Dashboard Charts JS -->
<script src="{{ asset('assets/js/dashboard-charts.js') }}"></script>

<script>
// Passar dados do PHP para JavaScript
window.dashboardData = {
    cursos: @json($estatisticasCursos ?? []),
    modalidades: @json($estatisticasModalidade ?? []),
    chartData: @json($chartData ?? []),
    availableCourses: @json($availableCourses ?? []),
    availableModalities: @json($availableModalities ?? [])
};

console.log('📋 Dashboard data loaded:', window.dashboardData);

// Funcionalidade do filtro de datas
document.addEventListener('DOMContentLoaded', function() {
    const periodDropdown = document.getElementById('periodDropdown');
    const loadingIndicator = document.getElementById('chartLoadingIndicator');
    const conversaoChart = document.getElementById('conversaoChart');
    
    if (periodDropdown) {
        // Event listener para mudança de período
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('dropdown-item')) {
                e.preventDefault();
                
                const period = e.target.dataset.period;
                const periodText = e.target.textContent;
                
                // Atualizar texto do dropdown
                periodDropdown.innerHTML = periodText + ' <i class="fas fa-chevron-down ms-1"></i>';
                
                // Atualizar subtítulo
                const subtitle = document.querySelector('.chart-subtitle');
                if (subtitle) {
                    subtitle.textContent = `Showing total visitors for ${periodText.toLowerCase()}`;
                }
                
                // Remover classe active de todos os itens
                document.querySelectorAll('.dropdown-menu .dropdown-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Adicionar classe active ao item selecionado
                e.target.classList.add('active');
                
                // Calcular datas baseadas no período
                const endDate = new Date();
                const startDate = new Date();
                
                switch(period) {
                    case '7':
                        startDate.setDate(endDate.getDate() - 7);
                        break;
                    case '30':
                        startDate.setDate(endDate.getDate() - 30);
                        break;
                    case '90':
                        startDate.setDate(endDate.getDate() - 90);
                        break;
                    case '365':
                        startDate.setDate(endDate.getDate() - 365);
                        break;
                    default:
                        startDate.setDate(endDate.getDate() - 90);
                }
                
                // Mostrar loading
                loadingIndicator.style.display = 'flex';
                
                // Fazer requisição AJAX
                const url = new URL(window.location.href);
                url.searchParams.set('chart_start_date', startDate.toISOString().split('T')[0]);
                url.searchParams.set('chart_end_date', endDate.toISOString().split('T')[0]);
                
                fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na requisição: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('📊 Novos dados recebidos:', data);
                    
                    // Atualizar dados globais
                    if (data.chartData) {
                        window.dashboardData.chartData = data.chartData;
                        
                        // Recriar apenas o gráfico de conversão
                        if (window.conversaoChart) {
                            window.conversaoChart.destroy();
                        }
                        
                        // Pequeno delay para garantir que o chart seja recriado corretamente
                        setTimeout(() => {
                            // Usar a função global para recriar o gráfico
                            if (window.DashboardCharts && window.DashboardCharts.createConversaoChart) {
                                window.DashboardCharts.createConversaoChart(window.dashboardData);
                            } else {
                                // Fallback: recriar todos os gráficos
                                window.DashboardCharts.destroy();
                                window.DashboardCharts.init(window.dashboardData);
                            }
                        }, 100);
                        
                        // Atualizar URL sem recarregar
                        history.pushState(null, '', url.toString());
                        
                        // Mostrar sucesso
                        toastr.success('Gráfico atualizado com sucesso!');
                        
                        console.log('✅ Gráfico atualizado com sucesso');
                    } else {
                        throw new Error('Dados de gráfico não encontrados na resposta');
                    }
                })
                .catch(error => {
                    console.error('❌ Erro ao atualizar gráfico:', error);
                    toastr.error('Erro ao atualizar gráfico: ' + error.message);
                })
                .finally(() => {
                    // Esconder loading
                    loadingIndicator.style.display = 'none';
                });
            }
        });
    }
});
</script>
@endpush 