@extends('layouts.admin')

@section('title', 'Teste de Pagamentos MercadoPago')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">🧪 Teste de Pagamentos MercadoPago</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Teste PIX -->
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">💳 Teste PIX</h5>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-primary btn-lg w-100" onclick="testPixPayment()">
                                        <i class="fas fa-qrcode me-2"></i>Criar PIX
                                    </button>
                                    <div id="pixResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Teste Boleto -->
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">📄 Teste Boleto</h5>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-warning btn-lg w-100" onclick="testBoletoPayment()">
                                        <i class="fas fa-file-invoice me-2"></i>Criar Boleto
                                    </button>
                                    <div id="boletoResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Teste Cartão -->
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">💳 Teste Cartão</h5>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-success btn-lg w-100" onclick="testCardPayment()">
                                        <i class="fas fa-credit-card me-2"></i>Criar Checkout
                                    </button>
                                    <div id="cardResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Logs de teste -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">📝 Logs de Teste</h5>
                                    <button class="btn btn-sm btn-outline-secondary float-end" onclick="clearLogs()">
                                        <i class="fas fa-trash"></i> Limpar
                                    </button>
                                </div>
                                <div class="card-body">
                                    <pre id="testLogs" class="bg-dark text-light p-3" style="height: 300px; overflow-y: auto;"></pre>
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

    function log(message) {
        const timestamp = new Date().toLocaleTimeString();
        const logElement = document.getElementById('testLogs');
        if (logElement) {
            logElement.textContent += `[${timestamp}] ${message}\n`;
            logElement.scrollTop = logElement.scrollHeight;
        }
    }

    function clearLogs() {
        const logElement = document.getElementById('testLogs');
        if (logElement) {
            logElement.textContent = '';
        }
    }

    // Definir as funções no escopo global
    window.testPixPayment = async function() {
        const button = event.target;
        const originalText = button.innerHTML;
        
        try {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando PIX...';
            
            log('🔄 Iniciando teste de PIX...');
            
            const paymentData = {
                transaction_amount: 15.75,
                description: 'Teste PIX - Validação Frontend',
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
            };

            const response = await fetch('/api/create-pix-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(paymentData)
            });

            log(`📡 Status HTTP PIX: ${response.status} ${response.statusText}`);
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
            }

            const result = await response.json();
            log(`📡 Resposta da API PIX: ${JSON.stringify(result).substring(0, 150)}...`);
            
            if (result.success) {
                log('✅ PIX criado com sucesso! ID: ' + result.id);
                
                const resultDiv = document.getElementById('pixResult');
                
                if (result.point_of_interaction && result.point_of_interaction.transaction_data) {
                    const qrCode = result.point_of_interaction.transaction_data.qr_code_base64;
                    const pixCode = result.point_of_interaction.transaction_data.qr_code;
                    
                    resultDiv.innerHTML = `
                        <div class="text-center">
                            <img src="data:image/png;base64,${qrCode}" alt="QR Code PIX" class="img-fluid mb-2" style="max-width: 150px;">
                            <br>
                            <small class="text-muted">PIX: ${pixCode.substring(0, 20)}...</small>
                        </div>
                    `;
                    
                    log('📱 QR Code PIX disponível');
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-warning">PIX criado, mas QR Code não disponível</div>';
                    log('⚠️ PIX criado, mas QR Code não disponível');
                }
            } else {
                log('❌ Erro ao criar PIX: ' + result.error);
                document.getElementById('pixResult').innerHTML = '<div class="alert alert-danger">Erro: ' + result.error + '</div>';
            }
            
        } catch (error) {
            log('❌ Erro na requisição PIX: ' + error.message);
            document.getElementById('pixResult').innerHTML = '<div class="alert alert-danger">Erro: ' + error.message + '</div>';
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    };

    window.testBoletoPayment = async function() {
        const button = event.target;
        const originalText = button.innerHTML;
        
        try {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando Boleto...';
            
            log('🔄 Iniciando teste de Boleto...');
            
            const paymentData = {
                transaction_amount: 89.50,
                description: 'Teste Boleto - Validação Frontend',
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
            };

            const response = await fetch('/api/create-boleto-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(paymentData)
            });

            log(`📡 Status HTTP Boleto: ${response.status} ${response.statusText}`);
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
            }

            const result = await response.json();
            log(`📡 Resposta da API Boleto: ${JSON.stringify(result).substring(0, 150)}...`);
            
            if (result.success) {
                log('✅ Boleto criado com sucesso! ID: ' + result.id);
                
                const resultDiv = document.getElementById('boletoResult');
                
                if (result.transaction_details && result.transaction_details.external_resource_url) {
                    const ticketUrl = result.transaction_details.external_resource_url;
                    
                    resultDiv.innerHTML = `
                        <div class="text-center">
                            <a href="${ticketUrl}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i>Ver Boleto
                            </a>
                        </div>
                    `;
                    
                    log('📄 Boleto disponível para download');
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-warning">Boleto criado, mas link não disponível</div>';
                    log('⚠️ Boleto criado, mas link não disponível');
                }
            } else {
                log('❌ Erro ao criar Boleto: ' + result.error);
                document.getElementById('boletoResult').innerHTML = '<div class="alert alert-danger">Erro: ' + result.error + '</div>';
            }
            
        } catch (error) {
            log('❌ Erro na requisição Boleto: ' + error.message);
            document.getElementById('boletoResult').innerHTML = '<div class="alert alert-danger">Erro: ' + error.message + '</div>';
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    };

    window.testCardPayment = async function() {
        const button = event.target;
        const originalText = button.innerHTML;
        
        try {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando Checkout...';
            
            log('🔄 Iniciando teste de Cartão...');
            
            const preferenceData = {
                items: [{
                    title: 'Teste Cartão - Validação Frontend',
                    unit_price: 127.90,
                    quantity: 1
                }],
                payer: {
                    email: 'teste.cartao@example.com',
                    name: 'Pedro Oliveira',
                    identification: {
                        type: 'CPF',
                        number: '19119119100'
                    }
                }
            };

            const response = await fetch('/api/create-card-preference', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(preferenceData)
            });

            log(`📡 Status HTTP Cartão: ${response.status} ${response.statusText}`);
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
            }

            const result = await response.json();
            log(`📡 Resposta da API Cartão: ${JSON.stringify(result).substring(0, 150)}...`);
            
            if (result.success) {
                log('✅ Checkout criado com sucesso! ID: ' + result.id);
                
                const resultDiv = document.getElementById('cardResult');
                
                if (result.init_point) {
                    resultDiv.innerHTML = `
                        <div class="text-center">
                            <a href="${result.init_point}" target="_blank" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-external-link-alt me-1"></i>Abrir Checkout
                            </a>
                        </div>
                    `;
                    
                    log('💳 Checkout disponível');
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-warning">Checkout criado, mas link não disponível</div>';
                    log('⚠️ Checkout criado, mas link não disponível');
                }
            } else {
                log('❌ Erro ao criar Checkout: ' + result.error);
                document.getElementById('cardResult').innerHTML = '<div class="alert alert-danger">Erro: ' + result.error + '</div>';
            }
            
        } catch (error) {
            log('❌ Erro na requisição Cartão: ' + error.message);
            document.getElementById('cardResult').innerHTML = '<div class="alert alert-danger">Erro: ' + error.message + '</div>';
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    };

    // Log inicial quando o DOM carrega
    document.addEventListener('DOMContentLoaded', function() {
        log('🚀 Página de teste carregada. SDK MercadoPago inicializado.');
        log('🔑 Chave pública: {{ substr($publicKey, 0, 15) }}...');
        log('🧪 Pronto para testes!');
    });
</script>
@endpush 