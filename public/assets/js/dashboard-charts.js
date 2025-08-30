/**
 * Dashboard Charts - EJA Admin
 * Versão: 1.0
 * Integração completa com Chart.js
 */

// Dashboard Charts - Versão Otimizada
let cursosChart, modalidadesChart, conversaoChart;

// Configuração de cores
const colors = {
    primary: '#36A2EB',
    success: '#4BC0C0',
    warning: '#FF9F40',
    danger: '#FF6384',
    info: '#9966FF',
    secondary: '#C9CBCF'
};

/**
 * Inicializar todos os gráficos do dashboard
 */
function initDashboardCharts(data = {}) {
    console.log('🎯 Iniciando charts do dashboard...', data);
    
    // Validar se Chart.js está disponível
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js não está carregado');
        showError('Chart.js não foi carregado. Recarregue a página.');
        return;
    }
    
    // Destruir charts existentes
    destroyExistingCharts();
    
    try {
        // Criar cada gráfico
        if (data.cursos) {
            createCursosChart(data);
        }
        
        if (data.modalidades) {
            createModalidadesChart(data);
        }
        
        createConversaoChart(data);
        
        console.log('✅ Charts criados com sucesso');
        
    } catch (error) {
        console.error('❌ Erro ao criar charts:', error);
        showError('Erro ao carregar gráficos: ' + error.message);
    }
}

/**
 * Destruir gráficos existentes para evitar conflitos
 */
function destroyExistingCharts() {
    console.log('🗑️ Destruindo charts existentes...');
    
    if (cursosChart) {
        cursosChart.destroy();
        cursosChart = null;
    }
    
    if (modalidadesChart) {
        modalidadesChart.destroy();
        modalidadesChart = null;
    }
    
    if (conversaoChart) {
        conversaoChart.destroy();
        conversaoChart = null;
    }
}

/**
 * Criar gráfico de cursos
 */
function createCursosChart(data) {
    const canvas = document.getElementById('cursosChart');
    if (!canvas) {
        console.warn('⚠️ Canvas cursosChart não encontrado');
        return;
    }
    
    try {
        const ctx = canvas.getContext('2d');
        
        const chartData = data.cursos || [];
        const labels = chartData.map(item => {
            const courseName = data.availableCourses ? data.availableCourses[item.curso] : item.curso;
            return courseName || item.curso || 'N/A';
        });
        const valores = chartData.map(item => item.total || 0);
        
        // Se não há dados, mostrar gráfico vazio
        if (valores.length === 0 || valores.every(v => v === 0)) {
            showChartError('cursosChart', 'Nenhum dado de curso disponível');
            return;
        }
        
        cursosChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: valores,
                    backgroundColor: [
                        colors.primary,
                        colors.success,
                        colors.warning,
                        colors.danger,
                        colors.info,
                        colors.secondary
                    ],
                    borderWidth: 0,
                    hoverBorderWidth: 2,
                    hoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            boxWidth: 10,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                layout: {
                    padding: 10
                },
                cutout: '50%'
            }
        });
        
    } catch (error) {
        console.error('❌ Erro ao criar gráfico de cursos:', error);
        showChartError('cursosChart', 'Erro ao carregar gráfico de cursos');
    }
}

/**
 * Criar gráfico de modalidades
 */
function createModalidadesChart(data) {
    const canvas = document.getElementById('modalidadesChart');
    if (!canvas) {
        console.warn('⚠️ Canvas modalidadesChart não encontrado');
        return;
    }
    
    try {
        const ctx = canvas.getContext('2d');
        
        const chartData = data.modalidades || [];
        const labels = chartData.map(item => {
            const modalityName = data.availableModalities ? data.availableModalities[item.modalidade] : item.modalidade;
            return modalityName || item.modalidade || 'N/A';
        });
        const valores = chartData.map(item => item.total || 0);
        
        // Se não há dados, mostrar gráfico vazio
        if (valores.length === 0 || valores.every(v => v === 0)) {
            showChartError('modalidadesChart', 'Nenhum dado de modalidade disponível');
            return;
        }
        
        modalidadesChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: valores,
                    backgroundColor: [
                        colors.success,
                        colors.warning,
                        colors.info,
                        colors.danger,
                        colors.primary,
                        colors.secondary
                    ],
                    borderWidth: 0,
                    hoverBorderWidth: 2,
                    hoverBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            boxWidth: 10,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                layout: {
                    padding: 10
                },
                cutout: '50%'
            }
        });
        
    } catch (error) {
        console.error('❌ Erro ao criar gráfico de modalidades:', error);
        showChartError('modalidadesChart', 'Erro ao carregar gráfico de modalidades');
    }
}

/**
 * Criar gráfico de conversão
 */
function createConversaoChart(data) {
    const canvas = document.getElementById('conversaoChart');
    if (!canvas) {
        console.warn('⚠️ Canvas conversaoChart não encontrado');
        return;
    }
    
    try {
        const ctx = canvas.getContext('2d');
        
        // Dados para o gráfico
        const chartData = data.chartData || {};
        const labels = chartData.labels || [];
        const leadsData = chartData.leads || [];
        const contatadosData = chartData.contatados || [];
        const matriculadosData = chartData.matriculados || [];
        
        // Se não há dados, usar dados de exemplo
        if (labels.length === 0) {
            console.warn('⚠️ Sem dados de conversão, usando dados de exemplo');
            labels.push(...['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom']);
            leadsData.push(...[5, 8, 3, 12, 7, 4, 9]);
            contatadosData.push(...[3, 6, 2, 8, 5, 2, 6]);
            matriculadosData.push(...[1, 2, 1, 3, 2, 1, 2]);
        }
        
        conversaoChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Leads Recebidos',
                        data: leadsData,
                        borderColor: colors.primary,
                        backgroundColor: colors.primary + '20',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: colors.primary,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Contatados',
                        data: contatadosData,
                        borderColor: colors.warning,
                        backgroundColor: colors.warning + '20',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: colors.warning,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Matriculados',
                        data: matriculadosData,
                        borderColor: colors.success,
                        backgroundColor: colors.success + '20',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: colors.success,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 0,
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                layout: {
                    padding: {
                        left: 10,
                        right: 10,
                        top: 10,
                        bottom: 10
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
        
        // Atualizar referência global
        window.conversaoChart = conversaoChart;
        
    } catch (error) {
        console.error('❌ Erro ao criar gráfico de conversão:', error);
        showChartError('conversaoChart', 'Erro ao carregar gráfico de conversão');
    }
}

/**
 * Mostrar erro em um gráfico específico
 */
function showChartError(canvasId, message) {
    const canvas = document.getElementById(canvasId);
    if (canvas && canvas.parentNode) {
        canvas.parentNode.innerHTML = `
            <div class="text-center p-4">
                <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i><br>
                <strong>${message}</strong><br>
                <small class="text-muted">Tente recarregar a página</small>
            </div>
        `;
    }
}

/**
 * Mostrar erro geral
 */
function showError(message) {
    console.error('❌ Erro nos gráficos:', message);
    
    const container = document.querySelector('.main-content') || document.body;
    if (container) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show m-3';
        errorDiv.innerHTML = `
            <strong>Erro nos gráficos:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        container.insertBefore(errorDiv, container.firstChild);
        
        // Auto remover após 10 segundos
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 10000);
    }
}

/**
 * Aguardar carregamento e inicializar
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM carregado, verificando Chart.js...');
    
    // Verificar se Chart.js está carregado
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js não foi carregado');
        showError('Chart.js não foi carregado. Verifique a conexão com a internet.');
        return;
    }
    
    // Aguardar dados serem carregados
    function tryInitCharts() {
        if (window.dashboardData) {
            console.log('📊 Dados encontrados, inicializando charts...');
            initDashboardCharts(window.dashboardData);
        } else {
            console.warn('⚠️ Dados ainda não carregados, tentando novamente...');
            setTimeout(tryInitCharts, 500);
        }
    }
    
    // Iniciar tentativas
    setTimeout(tryInitCharts, 100);
});

// Exportar para uso global
window.DashboardCharts = {
    init: initDashboardCharts,
    destroy: destroyExistingCharts,
    createCursosChart: createCursosChart,
    createModalidadesChart: createModalidadesChart,
    createConversaoChart: createConversaoChart
};

// Listener para redimensionamento (com debounce)
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        console.log('📏 Janela redimensionada, atualizando charts...');
        if (window.dashboardData) {
            destroyExistingCharts();
            initDashboardCharts(window.dashboardData);
        }
    }, 300);
}); 