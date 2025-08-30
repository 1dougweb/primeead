@extends('layouts.help')

@section('title', 'Dashboard de Pagamentos')
@section('page-title', 'Como Usar o Dashboard de Pagamentos')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard de Pagamentos</li>
@endsection

@section('content')
<div class="help-section">
    <div class="alert alert-info alert-help">
        <h6><i class="fas fa-info-circle me-2"></i>Sobre o Dashboard</h6>
        <p class="mb-0">
            O Dashboard de Pagamentos é o centro de controle para acompanhar todas as transações, 
            visualizar estatísticas e gerenciar pagamentos do sistema.
        </p>
    </div>
</div>

<div class="help-section">
    <h3>Acessando o Dashboard</h3>
    <div class="help-card card">
        <div class="card-body">
            <p>Para acessar o Dashboard de Pagamentos:</p>
            <ol>
                <li>Acesse o <strong>Dashboard Administrativo</strong></li>
                <li>Clique em <strong>"Pagamentos"</strong> no menu lateral</li>
                <li>Ou acesse diretamente via URL: <code>/dashboard/pagamentos</code></li>
            </ol>
            <div class="text-center mt-3">
                <a href="{{ route('admin.payments.dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-chart-pie me-2"></i>
                    Ir para Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Visão Geral das Estatísticas</h3>
    
    <div class="row">
        <div class="col-md-6">
            <div class="help-card card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-dollar-sign me-2"></i>
                        Total em Pagamentos
                    </h6>
                </div>
                <div class="card-body">
                    <p>Mostra o valor total de todos os pagamentos registrados no sistema.</p>
                    <ul>
                        <li><strong>Inclui:</strong> Pagamentos pagos e pendentes</li>
                        <li><strong>Formato:</strong> R$ 99.999,99</li>
                        <li><strong>Atualização:</strong> Tempo real</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="help-card card">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Pendentes
                    </h6>
                </div>
                <div class="card-body">
                    <p>Valor total dos pagamentos que ainda não foram processados.</p>
                    <ul>
                        <li><strong>Status:</strong> Aguardando pagamento</li>
                        <li><strong>Ação:</strong> Requer acompanhamento</li>
                        <li><strong>Cor:</strong> Amarelo (atenção)</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="help-card card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Em Atraso
                    </h6>
                </div>
                <div class="card-body">
                    <p>Valor dos pagamentos que passaram da data de vencimento.</p>
                    <ul>
                        <li><strong>Critério:</strong> Data de vencimento < hoje</li>
                        <li><strong>Urgência:</strong> Requer ação imediata</li>
                        <li><strong>Cor:</strong> Vermelho (urgente)</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="help-card card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Crescimento Mensal
                    </h6>
                </div>
                <div class="card-body">
                    <p>Percentual de crescimento comparado ao mês anterior.</p>
                    <ul>
                        <li><strong>Cálculo:</strong> (Mês atual - Mês anterior) / Mês anterior</li>
                        <li><strong>Formato:</strong> +15% ou -5%</li>
                        <li><strong>Cor:</strong> Verde (positivo)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Sistema de Filtros</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>
                Filtros Disponíveis
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <h6>Status</h6>
                    <ul>
                        <li><span class="badge bg-warning">Pendente</span></li>
                        <li><span class="badge bg-success">Pago</span></li>
                        <li><span class="badge bg-danger">Em Atraso</span></li>
                        <li><span class="badge bg-secondary">Cancelado</span></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Data Inicial</h6>
                    <p>Filtra pagamentos a partir de uma data específica.</p>
                    <div class="code-block">
                        <small>Formato: dd/mm/aaaa</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <h6>Data Final</h6>
                    <p>Filtra pagamentos até uma data específica.</p>
                    <div class="code-block">
                        <small>Formato: dd/mm/aaaa</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <h6>Buscar</h6>
                    <p>Pesquisa por nome do cliente ou email.</p>
                    <div class="code-block">
                        <small>Ex: João Silva</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Tabela de Pagamentos</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>
                Colunas da Tabela
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Coluna</th>
                            <th>Descrição</th>
                            <th>Exemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Cliente</strong></td>
                            <td>Nome e email do cliente</td>
                            <td>João Silva<br><small>joao@email.com</small></td>
                        </tr>
                        <tr>
                            <td><strong>Valor</strong></td>
                            <td>Valor do pagamento e parcela</td>
                            <td>R$ 299,00<br><small>1/12</small></td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>Status atual do pagamento</td>
                            <td><span class="badge bg-success">Pago</span></td>
                        </tr>
                        <tr>
                            <td><strong>Vencimento</strong></td>
                            <td>Data de vencimento</td>
                            <td>15/01/2024<br><small class="text-danger">há 5 dias</small></td>
                        </tr>
                        <tr>
                            <td><strong>Método</strong></td>
                            <td>Forma de pagamento</td>
                            <td><span class="badge bg-info">PIX</span></td>
                        </tr>
                        <tr>
                            <td><strong>Ações</strong></td>
                            <td>Botões de ação</td>
                            <td>👁️ 📝 ✅</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Ações Disponíveis</h3>
    
    <div class="row">
        <div class="col-md-4">
            <div class="help-card card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-eye me-2 text-info"></i>
                        Visualizar
                    </h6>
                </div>
                <div class="card-body">
                    <p>Abre os detalhes completos do pagamento.</p>
                    <ul>
                        <li>Informações do cliente</li>
                        <li>Histórico de transações</li>
                        <li>Detalhes do Mercado Pago</li>
                        <li>Logs de notificações</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="help-card card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-edit me-2 text-warning"></i>
                        Editar
                    </h6>
                </div>
                <div class="card-body">
                    <p>Permite modificar informações do pagamento.</p>
                    <ul>
                        <li>Alterar valor</li>
                        <li>Modificar data de vencimento</li>
                        <li>Atualizar dados do cliente</li>
                        <li>Adicionar observações</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="help-card card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-check me-2 text-success"></i>
                        Marcar como Pago
                    </h6>
                </div>
                <div class="card-body">
                    <p>Marca manualmente um pagamento como pago.</p>
                    <ul>
                        <li>Disponível para pagamentos pendentes</li>
                        <li>Atualiza status imediatamente</li>
                        <li>Registra data de pagamento</li>
                        <li>Envia notificação ao cliente</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Paginação e Navegação</h3>
    
    <div class="help-card card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5>Sistema de Paginação</h5>
                    <p>A tabela mostra 20 pagamentos por página com navegação completa:</p>
                    <ul>
                        <li><strong>Primeira página:</strong> Botão "Primeira"</li>
                        <li><strong>Página anterior:</strong> Botão "Anterior"</li>
                        <li><strong>Números das páginas:</strong> Navegação direta</li>
                        <li><strong>Próxima página:</strong> Botão "Próxima"</li>
                        <li><strong>Última página:</strong> Botão "Última"</li>
                    </ul>
                    <p>Os filtros são mantidos durante a navegação entre páginas.</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-list fa-4x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Casos de Uso Comuns</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-tasks me-2"></i>
                Tarefas Diárias
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>🔍 Verificar Pagamentos em Atraso</h6>
                    <ol>
                        <li>Filtre por status "Em Atraso"</li>
                        <li>Ordene por data de vencimento</li>
                        <li>Entre em contato com os clientes</li>
                        <li>Envie lembretes via email/WhatsApp</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h6>📊 Acompanhar Receita Mensal</h6>
                    <ol>
                        <li>Use filtro de data (mês atual)</li>
                        <li>Filtre por status "Pago"</li>
                        <li>Some os valores na tabela</li>
                        <li>Compare com mês anterior</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Dicas e Boas Práticas</h3>
    
    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-success alert-help">
                <h6><i class="fas fa-lightbulb me-2"></i>Dicas Úteis</h6>
                <ul class="mb-0">
                    <li>Verifique pagamentos em atraso diariamente</li>
                    <li>Use filtros para análises específicas</li>
                    <li>Mantenha dados de clientes atualizados</li>
                    <li>Monitore o crescimento mensal</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning alert-help">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Cuidados</h6>
                <ul class="mb-0">
                    <li>Confirme antes de marcar como pago</li>
                    <li>Verifique dados antes de editar</li>
                    <li>Não delete pagamentos com histórico</li>
                    <li>Mantenha backup das informações</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <div class="text-center">
        <a href="{{ route('admin.payments.dashboard') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-chart-pie me-2"></i>
            Acessar Dashboard
        </a>
        <a href="{{ route('admin.help.automacao-pagamentos') }}" class="btn btn-outline-danger btn-lg ms-3">
            <i class="fas fa-arrow-right me-2"></i>
            Próximo: Automação de Pagamentos
        </a>
    </div>
</div>
@endsection 