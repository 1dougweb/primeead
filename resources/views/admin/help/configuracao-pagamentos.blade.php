@extends('layouts.help')

@section('title', 'Configuração de Pagamentos')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Configuração de Pagamentos
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="alert alert-success mb-4">
                                <h6><i class="fas fa-rocket me-2"></i>Sistema Atualizado!</h6>
                                <p class="mb-2">O sistema foi atualizado para a nova API do Mercado Pago com recursos aprimorados!</p>
                                <a href="{{ route('admin.help.mercado-pago-nova-api') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Ver atualizações
                                </a>
                            </div>
                            
                            <h4>Como Configurar o Sistema de Pagamentos</h4>
                            <p class="lead">O sistema de pagamentos permite gerenciar cobranças de matrículas de forma automática e integrada com o Mercado Pago.</p>
                            
                            <h5 class="mt-4">1. Configuração Básica</h5>
                            <p>Para configurar o sistema de pagamentos:</p>
                            <ol>
                                <li>Acesse <strong>Configurações → Mercado Pago</strong></li>
                                <li>Ative a integração com Mercado Pago</li>
                                <li>Configure suas credenciais da API</li>
                                <li>Defina as opções de notificação</li>
                                <li>Configure a automação de lembretes</li>
                            </ol>

                            <h5 class="mt-4">2. Credenciais do Mercado Pago</h5>
                            <p>Você precisa das seguintes credenciais:</p>
                            <ul>
                                <li><strong>Access Token:</strong> Token de acesso para autenticar com a API</li>
                                <li><strong>Public Key:</strong> Chave pública para transações frontend</li>
                                <li><strong>Webhook Secret:</strong> Chave secreta para validar webhooks</li>
                            </ul>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Dica:</strong> Use o modo sandbox para testes antes de ativar em produção.
                            </div>

                            <h5 class="mt-4">3. Configuração na Matrícula</h5>
                            <p>Durante a matrícula, o sistema permite:</p>
                            <ul>
                                <li><strong>Pagamento à vista:</strong> Valor total pago de uma vez</li>
                                <li><strong>Pagamento parcelado:</strong> Dividido em até 12 parcelas</li>
                                <li><strong>Valor da matrícula:</strong> Taxa inicial separada das mensalidades</li>
                                <li><strong>Formas de pagamento:</strong> PIX, Cartão de Crédito ou Boleto</li>
                            </ul>

                            <h5 class="mt-4">4. Geração Automática de Pagamentos</h5>
                            <p>O sistema gera automaticamente:</p>
                            <ul>
                                <li>Pagamento da matrícula (se houver valor)</li>
                                <li>Parcelas mensais baseadas no curso</li>
                                <li>Datas de vencimento personalizáveis</li>
                                <li>Integração com Mercado Pago para cobrança</li>
                            </ul>

                            <h5 class="mt-4">5. Notificações e Lembretes</h5>
                            <p>Configure notificações para:</p>
                            <ul>
                                <li><strong>Email:</strong> Lembretes de vencimento e confirmações</li>
                                <li><strong>WhatsApp:</strong> Mensagens automáticas de cobrança</li>
                                <li><strong>Automação:</strong> Lembretes escalados para pagamentos em atraso</li>
                            </ul>

                            <h5 class="mt-4">6. Monitoramento</h5>
                            <p>Acompanhe os pagamentos através do:</p>
                            <ul>
                                <li><strong>Dashboard:</strong> Visão geral de arrecadação e pendências</li>
                                <li><strong>Lista de Pagamentos:</strong> Controle detalhado de cada cobrança</li>
                                <li><strong>Relatórios:</strong> Análise de performance mensal</li>
                            </ul>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Importante:</strong> Sempre teste a integração no ambiente sandbox antes de ativar em produção.
                            </div>

                            <h5 class="mt-4">7. Configuração de Cron Jobs (Automação)</h5>
                            <p>Para que o sistema funcione automaticamente, configure os cron jobs no servidor:</p>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Automação Essencial:</strong> Os cron jobs garantem que notificações e cobranças sejam enviadas automaticamente.
                            </div>

                            <h6 class="mt-3">7.1. Acessar o Crontab</h6>
                            <p>No servidor, execute:</p>
                            <div class="bg-dark text-light p-3 rounded mb-3">
                                <code>crontab -e</code>
                            </div>

                            <h6 class="mt-3">7.2. Adicionar os Cron Jobs</h6>
                            <p>Adicione as seguintes linhas ao crontab:</p>
                            <div class="bg-dark text-light p-3 rounded mb-3">
                                <pre><code># Cron jobs para o sistema de pagamentos
# Executar a cada 5 minutos durante horário comercial (8h às 18h)
*/5 8-18 * * * cd /home/douglas/Apps/ec && php artisan payments:process-reminders >> /dev/null 2>&1

# Executar notificações agendadas a cada hora
0 * * * * cd /home/douglas/Apps/ec && php artisan payments:schedule-notifications >> /dev/null 2>&1

# Gerar pagamentos parcelados automaticamente (uma vez por dia às 8h)
0 8 * * * cd /home/douglas/Apps/ec && php artisan payments:generate-installments >> /dev/null 2>&1

# Limpar logs antigos (uma vez por dia às 2h)
0 2 * * * find /home/douglas/Apps/ec/storage/logs -name "*.log" -mtime +7 -delete >> /dev/null 2>&1</code></pre>
                            </div>

                            <h6 class="mt-3">7.3. O que cada Cron Job faz:</h6>
                            <ul>
                                <li><strong>payments:process-reminders:</strong> Processa pagamentos em atraso e sincroniza status com Mercado Pago</li>
                                <li><strong>payments:schedule-notifications:</strong> Envia notificações de vencimento (hoje, amanhã, 3 dias)</li>
                                <li><strong>payments:generate-installments:</strong> Gera pagamentos parcelados automaticamente</li>
                                <li><strong>Limpeza de logs:</strong> Remove logs antigos para economizar espaço</li>
                            </ul>

                            <h6 class="mt-3">7.4. Testar os Cron Jobs</h6>
                            <p>Para testar se os cron jobs estão funcionando:</p>
                            <div class="bg-dark text-light p-3 rounded mb-3">
                                <pre><code># Testar processamento de lembretes
php artisan payments:process-reminders --dry-run

# Testar notificações agendadas
php artisan payments:schedule-notifications --dry-run

# Testar geração de parcelas
php artisan payments:generate-installments --dry-run</code></pre>
                            </div>

                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Dica:</strong> Use o parâmetro <code>--dry-run</code> para testar sem enviar notificações reais.
                            </div>

                            <h5 class="mt-4">8. Solução de Problemas</h5>
                            <p>Problemas comuns e soluções:</p>
                            <ul>
                                <li><strong>Webhook não funciona:</strong> Verifique se a URL está acessível e o secret está correto</li>
                                <li><strong>Pagamentos não aparecem:</strong> Confirme se as credenciais estão corretas</li>
                                <li><strong>Notificações não chegam:</strong> Verifique configurações de email e WhatsApp</li>
                                <li><strong>Cron jobs não executam:</strong> Verifique se o crontab está configurado corretamente</li>
                                <li><strong>Logs não aparecem:</strong> Execute os comandos manualmente para verificar erros</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-link me-2"></i>
                                        Links Úteis
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="{{ route('admin.settings.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-cog me-2"></i>
                                            Configurações do Sistema
                                        </a>
                                        <a href="{{ route('admin.payments.dashboard') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-chart-bar me-2"></i>
                                            Dashboard de Pagamentos
                                        </a>
                                        <a href="{{ route('admin.payments.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-list me-2"></i>
                                            Lista de Pagamentos
                                        </a>
                                        <a href="{{ route('admin.help.mercado-pago') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-store me-2"></i>
                                            Configurar Mercado Pago
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light mt-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-lightbulb me-2"></i>
                                        Dicas Importantes
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Use sandbox para testes
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Configure webhooks corretamente
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Teste notificações antes de usar
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Monitore pagamentos regularmente
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            Configure cron jobs para automação
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card bg-warning mt-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        Automação (Cron Jobs)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <a href="#" onclick="copyCronJobs()" class="list-group-item list-group-item-action">
                                            <i class="fas fa-copy me-2"></i>
                                            Copiar Cron Jobs
                                        </a>
                                        <a href="#" onclick="testCronJobs()" class="list-group-item list-group-item-action">
                                            <i class="fas fa-play me-2"></i>
                                            Testar Comandos
                                        </a>
                                        <a href="{{ route('admin.settings.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-cog me-2"></i>
                                            Configurações
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyCronJobs() {
    const cronJobs = `# Cron jobs para o sistema de pagamentos
# Executar a cada 5 minutos durante horário comercial (8h às 18h)
*/5 8-18 * * * cd /home/douglas/Apps/ec && php artisan payments:process-reminders >> /dev/null 2>&1

# Executar notificações agendadas a cada hora
0 * * * * cd /home/douglas/Apps/ec && php artisan payments:schedule-notifications >> /dev/null 2>&1

# Gerar pagamentos parcelados automaticamente (uma vez por dia às 8h)
0 8 * * * cd /home/douglas/Apps/ec && php artisan payments:generate-installments >> /dev/null 2>&1

# Limpar logs antigos (uma vez por dia às 2h)
0 2 * * * find /home/douglas/Apps/ec/storage/logs -name "*.log" -mtime +7 -delete >> /dev/null 2>&1`;

    navigator.clipboard.writeText(cronJobs).then(function() {
        toastr.success('Cron jobs copiados para a área de transferência!');
    }).catch(function() {
        // Fallback para navegadores que não suportam clipboard API
        const textArea = document.createElement('textarea');
        textArea.value = cronJobs;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        toastr.success('Cron jobs copiados para a área de transferência!');
    });
}

function testCronJobs() {
    // Abrir modal ou nova aba com comandos de teste
    const commands = `# Testar processamento de lembretes
php artisan payments:process-reminders --dry-run

# Testar notificações agendadas
php artisan payments:schedule-notifications --dry-run

# Testar geração de parcelas
php artisan payments:generate-installments --dry-run

# Verificar logs
tail -f storage/logs/laravel.log`;

    const modal = `
    <div class="modal fade" id="testCronModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-terminal me-2"></i>
                        Comandos para Testar Cron Jobs
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Execute estes comandos no terminal do servidor para testar os cron jobs:</p>
                    <div class="bg-dark text-light p-3 rounded">
                        <pre><code>${commands}</code></pre>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Dica:</strong> Use <code>--dry-run</code> para testar sem enviar notificações reais.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="copyCommands()">Copiar Comandos</button>
                </div>
            </div>
        </div>
    </div>`;

    // Remover modal anterior se existir
    const existingModal = document.getElementById('testCronModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Adicionar novo modal
    document.body.insertAdjacentHTML('beforeend', modal);
    
    // Mostrar modal
    const modalElement = new bootstrap.Modal(document.getElementById('testCronModal'));
    modalElement.show();
}

function copyCommands() {
    const commands = `# Testar processamento de lembretes
php artisan payments:process-reminders --dry-run

# Testar notificações agendadas
php artisan payments:schedule-notifications --dry-run

# Testar geração de parcelas
php artisan payments:generate-installments --dry-run

# Verificar logs
tail -f storage/logs/laravel.log`;

    navigator.clipboard.writeText(commands).then(function() {
        toastr.success('Comandos copiados para a área de transferência!');
    }).catch(function() {
        const textArea = document.createElement('textarea');
        textArea.value = commands;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        toastr.success('Comandos copiados para a área de transferência!');
    });
}
</script>
@endpush 