@extends('layouts.admin')

@section('title', 'Gerenciar Cron Jobs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">⏰ Gerenciamento de Cron Jobs</h3>
                    <div class="card-tools">
                        <button class="btn btn-success btn-sm" onclick="testRecurringPayments()">
                            <i class="fas fa-play me-1"></i>Testar Agora
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Status dos Cron Jobs -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                                    <h5>Pagamentos Recorrentes</h5>
                                    <p class="text-muted">Execução diária às 09:00</p>
                                    <span class="badge bg-success">Ativo</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-bell fa-2x text-warning mb-2"></i>
                                    <h5>Lembretes</h5>
                                    <p class="text-muted">3 dias antes do vencimento</p>
                                    <span class="badge bg-success">Ativo</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                    <h5>Pagamentos Atrasados</h5>
                                    <p class="text-muted">Verificação diária</p>
                                    <span class="badge bg-success">Ativo</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comandos Disponíveis -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">📋 Comandos Disponíveis</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Comando</th>
                                            <th>Descrição</th>
                                            <th>Frequência</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>payments:send-reminders</code></td>
                                            <td>Enviar lembretes de pagamentos próximos do vencimento</td>
                                            <td>A cada 30 minutos</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="runCommand('payments:send-reminders')">
                                                    <i class="fas fa-play"></i> Executar
                                                </button>
                                                <button class="btn btn-sm btn-secondary" onclick="runCommand('payments:send-reminders', true)">
                                                    <i class="fas fa-bug"></i> Teste
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><code>payments:process-recurring</code></td>
                                            <td>Processar pagamentos recorrentes (legacy)</td>
                                            <td>Diariamente às 09:00</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="runCommand('payments:process-recurring')">
                                                    <i class="fas fa-play"></i> Executar
                                                </button>
                                                <button class="btn btn-sm btn-secondary" onclick="runCommand('payments:process-recurring', true)">
                                                    <i class="fas fa-bug"></i> Teste
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><code>queue:work</code></td>
                                            <td>Processar filas de emails e jobs</td>
                                            <td>Contínuo</td>
                                            <td>
                                                <button class="btn btn-sm btn-success" onclick="runCommand('queue:work')">
                                                    <i class="fas fa-play"></i> Iniciar
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Configuração do Servidor -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">⚙️ Configuração do Servidor</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Como configurar no servidor:</h6>
                                
                                <p><strong>1. Editar o crontab:</strong></p>
                                <pre class="bg-dark text-light p-3 rounded"><code>sudo crontab -e</code></pre>
                                
                                <p><strong>2. Adicionar as seguintes linhas:</strong></p>
                                <pre class="bg-dark text-light p-3 rounded"><code># Lembretes de pagamento - a cada 30 minutos
*/30 * * * * cd {{ base_path() }} && php artisan payments:send-reminders >> /dev/null 2>&1

# Processar pagamentos recorrentes (legacy) - todos os dias às 09:00
0 9 * * * cd {{ base_path() }} && php artisan payments:process-recurring >> /dev/null 2>&1

# Processar filas - a cada minuto
* * * * * cd {{ base_path() }} && php artisan queue:work --stop-when-empty >> /dev/null 2>&1

# Laravel scheduler - a cada minuto
* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code></pre>
                                
                                <p><strong>3. Verificar se está funcionando:</strong></p>
                                <pre class="bg-dark text-light p-3 rounded"><code>sudo crontab -l</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Logs de Execução -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">📝 Logs de Execução</h5>
                            <button class="btn btn-sm btn-outline-secondary float-end" onclick="clearLogs()">
                                <i class="fas fa-trash"></i> Limpar
                            </button>
                        </div>
                        <div class="card-body">
                            <pre id="cronLogs" class="bg-dark text-light p-3" style="height: 300px; overflow-y: auto;">
Logs aparecerão aqui após executar comandos...
                            </pre>
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
    function log(message) {
        const timestamp = new Date().toLocaleTimeString();
        const logElement = document.getElementById('cronLogs');
        logElement.textContent += `[${timestamp}] ${message}\n`;
        logElement.scrollTop = logElement.scrollHeight;
    }

    function clearLogs() {
        document.getElementById('cronLogs').textContent = 'Logs limpos.\n';
    }

    async function runCommand(command, isDryRun = false) {
        const commandWithOptions = isDryRun ? `${command} --dry-run` : command;
        
        log(`🔄 Executando: ${commandWithOptions}`);
        
        try {
            const response = await fetch('/admin/cron-jobs/run-command', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    command: commandWithOptions
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            
            if (result.success) {
                log(`✅ Comando executado com sucesso`);
                if (result.output) {
                    log(`📄 Saída:\n${result.output}`);
                }
            } else {
                log(`❌ Erro: ${result.error}`);
            }

        } catch (error) {
            log(`❌ Erro na requisição: ${error.message}`);
        }
    }

    async function testRecurringPayments() {
        log('🧪 Iniciando teste de pagamentos recorrentes...');
        await runCommand('payments:process-recurring', true);
    }

    // Log inicial
    document.addEventListener('DOMContentLoaded', function() {
        log('📋 Painel de cron jobs carregado');
        log('💡 Use os botões para executar comandos');
    });
</script>
@endpush 