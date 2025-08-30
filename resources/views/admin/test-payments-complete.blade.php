@extends('layouts.admin')

@section('title', 'Testes Completos - Pagamentos e Emails')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🧪 Centro de Testes Completo</h3>
                    <div class="card-tools">
                        <button class="btn btn-warning btn-sm" onclick="runAllTests()">
                            <i class="fas fa-flask me-1"></i>Executar Todos os Testes
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Testes de Pagamento -->
                    <div class="row mb-4">
                        <!-- PIX -->
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">📱 PIX</h5>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-primary w-100 mb-2" onclick="testPayment('pix')">
                                        <i class="fas fa-qrcode me-2"></i>Testar PIX
                                    </button>
                                    <button class="btn btn-outline-primary w-100" onclick="testPaymentEmail('pix')">
                                        <i class="fas fa-envelope me-2"></i>Testar Email PIX
                                    </button>
                                    <div id="pixResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Boleto -->
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">📄 Boleto</h5>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-warning w-100 mb-2" onclick="testPayment('boleto')">
                                        <i class="fas fa-file-invoice me-2"></i>Testar Boleto
                                    </button>
                                    <button class="btn btn-outline-warning w-100" onclick="testPaymentEmail('boleto')">
                                        <i class="fas fa-envelope me-2"></i>Testar Email Boleto
                                    </button>
                                    <div id="boletoResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Cartão -->
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">💳 Cartão</h5>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-success w-100 mb-2" onclick="testPayment('cartao')">
                                        <i class="fas fa-credit-card me-2"></i>Testar Cartão
                                    </button>
                                    <button class="btn btn-outline-success w-100" onclick="testPaymentEmail('cartao')">
                                        <i class="fas fa-envelope me-2"></i>Testar Email Cartão
                                    </button>
                                    <div id="cartaoResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testes de Email -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">📧 Testes de Email</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>📅 Emails de Lembrete</h6>
                                    <button class="btn btn-info w-100 mb-2" onclick="testReminderEmail()">
                                        <i class="fas fa-bell me-2"></i>Testar Lembrete de Pagamento
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <h6>🔄 Emails Recorrentes</h6>
                                    <button class="btn btn-info w-100 mb-2" onclick="testRecurringProcess()">
                                        <i class="fas fa-sync me-2"></i>Testar Processo Recorrente
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Simulação de Matrícula -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">🎓 Simulação de Matrícula Completa</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Forma de Pagamento:</label>
                                    <select class="form-select" id="formaPagamentoTeste">
                                        <option value="a_vista_pix">À Vista (PIX)</option>
                                        <option value="boleto_parcelado">Boleto Parcelado</option>
                                        <option value="cartao_credito">Cartão de Crédito</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Número de Parcelas:</label>
                                    <select class="form-select" id="numeroParcelasTeste">
                                        <option value="1">1x (À vista)</option>
                                        <option value="2">2x</option>
                                        <option value="3">3x</option>
                                        <option value="6">6x</option>
                                        <option value="12">12x</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Valor:</label>
                                    <input type="number" class="form-control" id="valorTeste" value="150.00" step="0.01">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-primary" onclick="simulateMatriculaPayment()">
                                    <i class="fas fa-graduation-cap me-2"></i>Simular Matrícula Completa
                                </button>
                            </div>
                            <div id="matriculaResult" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Logs Detalhados -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">📝 Logs Detalhados</h5>
                            <button class="btn btn-sm btn-outline-secondary float-end" onclick="clearLogs()">
                                <i class="fas fa-trash"></i> Limpar
                            </button>
                        </div>
                        <div class="card-body">
                            <pre id="testLogs" class="bg-dark text-light p-3" style="height: 400px; overflow-y: auto;"></pre>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
    // Inicializar MercadoPago SDK
    @php
        $paymentSettings = \App\Models\SystemSetting::getPaymentSettings();
        $publicKey = $paymentSettings['mercadopago_sandbox'] 
            ? ($paymentSettings['mercadopago_sandbox_public_key'] ?: $paymentSettings['mercadopago_public_key'])
            : $paymentSettings['mercadopago_public_key'];
    @endphp
    
    const mp = new MercadoPago('{{ $publicKey }}', {
        locale: 'pt-BR'
    });

    function log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logElement = document.getElementById('testLogs');
        
        let icon = '📝';
        if (type === 'success') icon = '✅';
        if (type === 'error') icon = '❌';
        if (type === 'warning') icon = '⚠️';
        if (type === 'info') icon = 'ℹ️';
        
        logElement.textContent += `[${timestamp}] ${icon} ${message}\n`;
        logElement.scrollTop = logElement.scrollHeight;
    }

    function clearLogs() {
        document.getElementById('testLogs').textContent = '';
        log('Logs limpos', 'info');
    }

    async function testPayment(type) {
        log(`Iniciando teste de pagamento: ${type}`, 'info');
        
        const testData = {
            pix: {
                endpoint: '/api/create-pix-payment',
                data: {
                    transaction_amount: 25.75,
                    description: 'Teste PIX Completo',
                    payment_method_id: 'pix',
                    payer: {
                        email: 'teste.pix@example.com',
                        first_name: 'João',
                        last_name: 'Silva',
                        identification: {
                            type: 'CPF',
                            number: '19119119100'
                        }
                    }
                },
                resultDiv: 'pixResult'
            },
            boleto: {
                endpoint: '/api/create-boleto-payment',
                data: {
                    transaction_amount: 89.50,
                    description: 'Teste Boleto Completo',
                    payment_method_id: 'bolbradesco',
                    payer: {
                        email: 'teste.boleto@example.com',
                        first_name: 'Maria',
                        last_name: 'Santos',
                        identification: {
                            type: 'CPF',
                            number: '19119119100'
                        },
                        address: {
                            zip_code: '01310100',
                            street_name: 'Av. Paulista',
                            street_number: '1000',
                            neighborhood: 'Bela Vista',
                            city: 'São Paulo',
                            federal_unit: 'SP'
                        }
                    }
                },
                resultDiv: 'boletoResult'
            },
            cartao: {
                endpoint: '/api/create-card-preference',
                data: {
                    items: [{
                        title: 'Teste Cartão Completo',
                        unit_price: 127.90,
                        quantity: 1
                    }],
                    payer: {
                        email: 'teste.cartao@example.com',
                        name: 'Pedro Oliveira'
                    }
                },
                resultDiv: 'cartaoResult'
            }
        };

        const config = testData[type];
        if (!config) {
            log(`Tipo de pagamento inválido: ${type}`, 'error');
            return;
        }

        try {
            const response = await fetch(config.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(config.data)
            });

            log(`Status HTTP ${type}: ${response.status} ${response.statusText}`, 'info');

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
            }

            const result = await response.json();
            log(`Resposta ${type}: ${JSON.stringify(result).substring(0, 150)}...`, 'success');

            if (result.success) {
                displayPaymentResult(type, result, config.resultDiv);
                log(`${type.toUpperCase()} criado com sucesso! ID: ${result.id}`, 'success');
            } else {
                log(`Erro ao criar ${type}: ${result.error}`, 'error');
            }

        } catch (error) {
            log(`Erro na requisição ${type}: ${error.message}`, 'error');
        }
    }

    function displayPaymentResult(type, result, resultDiv) {
        const div = document.getElementById(resultDiv);
        
        if (type === 'pix' && result.point_of_interaction?.transaction_data) {
            const qrCode = result.point_of_interaction.transaction_data.qr_code_base64;
            div.innerHTML = `
                <div class="text-center">
                    <img src="data:image/png;base64,${qrCode}" alt="QR Code PIX" class="img-fluid" style="max-width: 120px;">
                    <br><small class="text-muted">PIX gerado</small>
                </div>
            `;
        } else if (type === 'boleto' && result.transaction_details?.external_resource_url) {
            div.innerHTML = `
                <div class="text-center">
                    <a href="${result.transaction_details.external_resource_url}" target="_blank" class="btn btn-sm btn-outline-warning">
                        📥 Ver Boleto
                    </a>
                </div>
            `;
        } else if (type === 'cartao' && result.init_point) {
            div.innerHTML = `
                <div class="text-center">
                    <a href="${result.init_point}" target="_blank" class="btn btn-sm btn-outline-success">
                        🔗 Abrir Checkout
                    </a>
                </div>
            `;
        }
    }

    async function testPaymentEmail(type) {
        log(`Testando email de pagamento: ${type}`, 'info');
        
        try {
            const response = await fetch('/admin/test-payment-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    payment_type: type,
                    email: 'teste@example.com'
                })
            });

            const result = await response.json();
            
            if (result.success) {
                log(`Email de ${type} enviado com sucesso!`, 'success');
            } else {
                log(`Erro ao enviar email de ${type}: ${result.error}`, 'error');
            }

        } catch (error) {
            log(`Erro ao testar email de ${type}: ${error.message}`, 'error');
        }
    }

    async function testReminderEmail() {
        log('Testando email de lembrete', 'info');
        
        try {
            const response = await fetch('/admin/test-reminder-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    email: 'teste@example.com'
                })
            });

            const result = await response.json();
            
            if (result.success) {
                log('Email de lembrete enviado com sucesso!', 'success');
            } else {
                log(`Erro ao enviar lembrete: ${result.error}`, 'error');
            }

        } catch (error) {
            log(`Erro ao testar lembrete: ${error.message}`, 'error');
        }
    }

    async function testRecurringProcess() {
        log('Testando processo recorrente (modo teste)', 'info');
        
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
                    command: 'payments:process-recurring --dry-run'
                })
            });

            const result = await response.json();
            
            if (result.success) {
                log('Processo recorrente executado (teste):', 'success');
                log(result.output, 'info');
            } else {
                log(`Erro no processo recorrente: ${result.error}`, 'error');
            }

        } catch (error) {
            log(`Erro ao testar processo recorrente: ${error.message}`, 'error');
        }
    }

    async function simulateMatriculaPayment() {
        const forma = document.getElementById('formaPagamentoTeste').value;
        const parcelas = document.getElementById('numeroParcelasTeste').value;
        const valor = document.getElementById('valorTeste').value;

        log(`Simulando matrícula: ${forma}, ${parcelas}x, R$ ${valor}`, 'info');

        const formData = new FormData();
        formData.append('forma_pagamento', forma);
        formData.append('numero_parcelas', parcelas);
        formData.append('valor_total_curso', valor);
        formData.append('curso', 'Curso de Teste');
        formData.append('nome_completo', 'João da Silva Teste');
        formData.append('email', 'joao.teste@example.com');
        formData.append('cpf', '19119119100');
        formData.append('cep', '01310100');
        formData.append('logradouro', 'Av. Paulista');
        formData.append('numero', '1000');
        formData.append('bairro', 'Bela Vista');
        formData.append('cidade', 'São Paulo');
        formData.append('estado', 'SP');

        // Determinar tipo de pagamento
        let paymentType = 'pix';
        if (forma === 'boleto_parcelado') paymentType = 'boleto';
        if (forma === 'cartao_credito') paymentType = 'cartao';

        // Simular criação de pagamento
        await testPayment(paymentType);
        
        log(`Matrícula simulada concluída para: ${paymentType}`, 'success');
    }

    async function runAllTests() {
        log('🧪 INICIANDO BATERIA COMPLETA DE TESTES', 'warning');
        
        await new Promise(resolve => setTimeout(resolve, 1000));
        await testPayment('pix');
        
        await new Promise(resolve => setTimeout(resolve, 2000));
        await testPayment('boleto');
        
        await new Promise(resolve => setTimeout(resolve, 2000));
        await testPayment('cartao');
        
        await new Promise(resolve => setTimeout(resolve, 2000));
        await testRecurringProcess();
        
        log('🎉 TODOS OS TESTES CONCLUÍDOS!', 'success');
    }

    // Log inicial
    document.addEventListener('DOMContentLoaded', function() {
        log('🚀 Centro de testes carregado');
        log(`🔑 Chave pública: {{ substr($publicKey, 0, 15) }}...`);
        log('💡 Use os botões para executar testes individuais ou todos juntos');
    });
</script>
@endpush 