@extends('layouts.help')

@section('title', 'Automação de Pagamentos')
@section('page-title', 'Automação de Pagamentos')

@section('breadcrumb')
<li class="breadcrumb-item active">Automação de Pagamentos</li>
@endsection

@section('content')
<div class="help-section">
    <div class="alert alert-info alert-help">
        <h6><i class="fas fa-info-circle me-2"></i>Sobre a Automação</h6>
        <p class="mb-0">
            O sistema de automação de pagamentos permite o envio automático de lembretes, 
            geração de próximos pagamentos e sincronização com o Mercado Pago sem intervenção manual.
        </p>
    </div>
</div>

<div class="help-section">
    <h3>Funcionalidades da Automação</h3>
    
    <div class="row">
        <div class="col-md-6">
            <div class="help-card card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-bell me-2"></i>
                        Lembretes Automáticos
                    </h6>
                </div>
                <div class="card-body">
                    <p>Envio automático de lembretes de pagamento via email e WhatsApp.</p>
                    <ul>
                        <li><strong>Próximo ao vencimento:</strong> 7, 3 e 1 dias antes</li>
                        <li><strong>Pagamentos em atraso:</strong> Diário, depois a cada 3 dias, depois semanal</li>
                        <li><strong>Escalação automática:</strong> Aumenta frequência gradualmente</li>
                        <li><strong>Limite de envios:</strong> Para após 30 dias de atraso</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="help-card card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-sync me-2"></i>
                        Geração Automática
                    </h6>
                </div>
                <div class="card-body">
                    <p>Criação automática de próximos pagamentos baseado nas matrículas.</p>
                    <ul>
                        <li><strong>Pagamentos parcelados:</strong> Mensalidades e parcelas</li>
                        <li><strong>Cronograma inteligente:</strong> Baseado na matrícula</li>
                        <li><strong>Integração Mercado Pago:</strong> Pagamentos automáticos</li>
                        <li><strong>Controle de parcelas:</strong> Respeita configuração da matrícula</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Configuração da Automação</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">1</span>
                Ativar Automação nas Configurações
            </h5>
        </div>
        <div class="card-body">
            <p>Para ativar a automação:</p>
            <ol>
                <li>Acesse <strong>Configurações</strong> → <strong>Mercado Pago</strong></li>
                <li>Na seção <strong>"Automação"</strong>, ative:</li>
            </ol>
            <div class="row">
                <div class="col-md-6">
                    <h6>✅ Lembretes Automáticos</h6>
                    <p>Habilita o envio automático de lembretes de pagamento.</p>
                </div>
                <div class="col-md-6">
                    <h6>✅ Geração Automática</h6>
                    <p>Permite a criação automática de próximos pagamentos.</p>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="{{ route('admin.settings.index') }}#mercadopago" class="btn btn-primary">
                    <i class="fas fa-cog me-2"></i>
                    Configurar Automação
                </a>
            </div>
        </div>
    </div>

    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <span class="step-number">2</span>
                Configurar Comando Cron
            </h5>
        </div>
        <div class="card-body">
            <p>Para que a automação funcione, é necessário configurar um comando cron:</p>
            <div class="code-block">
                <strong>Comando:</strong> php artisan payments:process-reminders<br>
                <strong>Frequência:</strong> A cada 30 minutos<br>
                <strong>Cron:</strong> */30 * * * * cd /caminho/para/projeto && php artisan payments:process-reminders
            </div>
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Importante:</strong> Este comando deve ser configurado no servidor pelo administrador do sistema.
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Como Funciona a Automação</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-cogs me-2"></i>
                Processamento Automático
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>🔄 Ciclo de Execução</h6>
                    <ol>
                        <li><strong>Verifica pagamentos próximos ao vencimento</strong></li>
                        <li><strong>Identifica pagamentos em atraso</strong></li>
                        <li><strong>Processa schedules de pagamento</strong></li>
                        <li><strong>Sincroniza status com Mercado Pago</strong></li>
                        <li><strong>Envia notificações conforme regras</strong></li>
                        <li><strong>Gera próximos pagamentos se necessário</strong></li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h6>📊 Estatísticas de Processamento</h6>
                    <ul>
                        <li><strong>Lembretes enviados:</strong> Contabiliza emails e WhatsApp</li>
                        <li><strong>Pagamentos processados:</strong> Status atualizados</li>
                        <li><strong>Schedules processados:</strong> Próximos pagamentos gerados</li>
                        <li><strong>Erros:</strong> Falhas no processamento</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Tipos de Lembretes</h3>
    
    <div class="row">
        <div class="col-md-6">
            <div class="help-card card">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Lembretes de Vencimento
                    </h6>
                </div>
                <div class="card-body">
                    <p>Enviados antes do vencimento para evitar atrasos.</p>
                    <ul>
                        <li><strong>7 dias antes:</strong> Primeiro lembrete</li>
                        <li><strong>3 dias antes:</strong> Segundo lembrete</li>
                        <li><strong>1 dia antes:</strong> Lembrete final</li>
                    </ul>
                    <div class="alert alert-info mt-3">
                        <small>Apenas para pagamentos com status "pendente"</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="help-card card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Lembretes de Atraso
                    </h6>
                </div>
                <div class="card-body">
                    <p>Enviados após o vencimento com escalação de frequência.</p>
                    <ul>
                        <li><strong>1-7 dias:</strong> Diariamente</li>
                        <li><strong>8-21 dias:</strong> A cada 3 dias</li>
                        <li><strong>22-30 dias:</strong> Semanalmente</li>
                        <li><strong>Após 30 dias:</strong> Para de enviar</li>
                    </ul>
                    <div class="alert alert-danger mt-3">
                        <small>Apenas para pagamentos em atraso</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Templates de Notificação</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-envelope me-2"></i>
                Templates de Email
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Template de Vencimento</h6>
                    <p>Usado para lembretes antes do vencimento.</p>
                    <div class="code-block">
                        <strong>Assunto:</strong> Lembrete: Pagamento vence em {dias} dias<br>
                        <strong>Variáveis:</strong> {cliente}, {valor}, {vencimento}, {dias}
                    </div>
                </div>
                <div class="col-md-6">
                    <h6>Template de Atraso</h6>
                    <p>Usado para pagamentos em atraso.</p>
                    <div class="code-block">
                        <strong>Assunto:</strong> Urgente: Pagamento em atraso<br>
                        <strong>Variáveis:</strong> {cliente}, {valor}, {vencimento}, {dias_atraso}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Sincronização com Mercado Pago</h3>
    
    <div class="help-card card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5>Atualização Automática de Status</h5>
                    <p>O sistema sincroniza automaticamente com o Mercado Pago:</p>
                    <ul>
                        <li><strong>Pagamentos aprovados:</strong> Status atualizado para "paid"</li>
                        <li><strong>Pagamentos rejeitados:</strong> Status atualizado para "cancelled"</li>
                        <li><strong>Pagamentos pendentes:</strong> Mantém status "pending"</li>
                        <li><strong>Assinaturas:</strong> Atualiza schedules automaticamente</li>
                    </ul>
                    <p>Esta sincronização evita enviar lembretes para pagamentos já processados.</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-sync-alt fa-4x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Monitoramento da Automação</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Logs e Relatórios
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Logs do Sistema</h6>
                    <p>Monitore a execução da automação:</p>
                    <ul>
                        <li><strong>Laravel Log:</strong> storage/logs/laravel.log</li>
                        <li><strong>Processamento:</strong> Detalhes de cada execução</li>
                        <li><strong>Erros:</strong> Falhas e exceções</li>
                        <li><strong>Estatísticas:</strong> Quantidade processada</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Tabela de Notificações</h6>
                    <p>Acompanhe notificações enviadas:</p>
                    <ul>
                        <li><strong>payment_notifications:</strong> Histórico completo</li>
                        <li><strong>Status de envio:</strong> Enviado/Falhado</li>
                        <li><strong>Canais:</strong> Email, WhatsApp, SMS</li>
                        <li><strong>Timestamps:</strong> Data/hora do envio</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Resolução de Problemas</h3>
    
    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-warning alert-help">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Problemas Comuns</h6>
                <ul>
                    <li><strong>Cron não configurado:</strong> Comando não executa</li>
                    <li><strong>Credenciais inválidas:</strong> Falha na sincronização</li>
                    <li><strong>Limite de email:</strong> Provedor bloqueia envios</li>
                    <li><strong>WhatsApp inativo:</strong> API não responde</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-success alert-help">
                <h6><i class="fas fa-tools me-2"></i>Soluções</h6>
                <ul>
                    <li><strong>Verificar cron:</strong> crontab -l</li>
                    <li><strong>Testar comando:</strong> php artisan payments:process-reminders</li>
                    <li><strong>Verificar logs:</strong> tail -f storage/logs/laravel.log</li>
                    <li><strong>Testar conexão:</strong> Botão nas configurações</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Exemplo de Execução</h3>
    
    <div class="help-card card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-terminal me-2"></i>
                Comando Manual
            </h5>
        </div>
        <div class="card-body">
            <p>Para testar a automação manualmente:</p>
            <div class="code-block">
                # Navegar para o diretório do projeto
                cd /caminho/para/projeto

                # Executar o comando de processamento
                php artisan payments:process-reminders

                # Exemplo de saída
                Processing payment reminders...
                ✓ Processed 5 upcoming payments
                ✓ Processed 3 overdue payments  
                ✓ Processed 2 payment schedules
                ✓ Sent 8 email notifications
                ✓ Sent 4 WhatsApp notifications
                Completed in 2.34 seconds
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <h3>Boas Práticas</h3>
    
    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-success alert-help">
                <h6><i class="fas fa-check-circle me-2"></i>Recomendações</h6>
                <ul class="mb-0">
                    <li>Configure o cron para executar a cada 30 minutos</li>
                    <li>Monitore os logs regularmente</li>
                    <li>Teste a automação antes de ativar</li>
                    <li>Mantenha templates de email atualizados</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-info alert-help">
                <h6><i class="fas fa-lightbulb me-2"></i>Dicas</h6>
                <ul class="mb-0">
                    <li>Use modo sandbox para testes</li>
                    <li>Configure limites de envio por hora</li>
                    <li>Personalize templates para sua marca</li>
                    <li>Monitore taxa de abertura dos emails</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="help-section">
    <div class="text-center">
        <a href="{{ route('admin.settings.index') }}#mercadopago" class="btn btn-primary btn-lg">
            <i class="fas fa-robot me-2"></i>
            Configurar Automação
        </a>
        <a href="{{ route('admin.help.index') }}" class="btn btn-outline-secondary btn-lg ms-3">
            <i class="fas fa-home me-2"></i>
            Voltar à Central de Ajuda
        </a>
    </div>
</div>
@endsection 