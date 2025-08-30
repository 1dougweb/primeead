<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Cobrança Gerada</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .header .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .payment-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .payment-info h3 {
            color: #007bff;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            color: #212529;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        .due-date {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
        }
        .payment-method {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
            text-align: center;
        }
        .payment-method-pix {
            background-color: #e8f5e8;
            color: #155724;
        }
        .payment-method-boleto {
            background-color: #fff3cd;
            color: #856404;
        }
        .payment-method-cartao {
            background-color: #d4edda;
            color: #155724;
        }
        .instructions {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .instructions h4 {
            color: #856404;
            margin-top: 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .contact-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .contact-info h4 {
            color: #007bff;
            margin-top: 0;
        }
        .pix-code {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 16px;
            word-break: break-all;
            white-space: pre-wrap;
            margin-bottom: 10px;
        }
        .boleto-link {
            margin-bottom: 10px;
        }
        .boleto-link .btn-payment {
            display: inline-block;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .card-link {
            margin-bottom: 10px;
        }
        .card-link .btn-payment {
            display: inline-block;
            padding: 8px 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .highlight {
            color: #dc3545; /* Red for highlight */
            font-weight: bold;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 20px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">💳</div>
            <h1>Nova Cobrança Gerada</h1>
        </div>

        <div class="greeting">
            Olá <strong>{{ $matricula->nome_completo }}</strong>,
        </div>

        <p>Uma nova cobrança foi gerada para você. Confira os detalhes abaixo:</p>

        <div class="payment-info">
            <h3>📋 Detalhes da Cobrança</h3>
            
            <div class="info-row">
                <span class="info-label">💰 Valor:</span>
                <span class="info-value amount">{{ $formattedAmount }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">📅 Data de Vencimento:</span>
                <span class="info-value due-date">{{ $formattedDueDate }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">📝 Descrição:</span>
                <span class="info-value">{{ $payment->descricao }}</span>
            </div>
            
            @if($isParceled)
            <div class="info-row">
                <span class="info-label">🔢 Parcela:</span>
                <span class="info-value">{{ $parcelInfo }}</span>
            </div>
            @endif
        </div>

        <div class="payment-method payment-method-{{ $payment->forma_pagamento }}">
            <strong>💳 Forma de Pagamento: {{ $paymentMethod }}</strong>
        </div>

        <div class="instructions">
            <h4>📋 Próximos Passos</h4>
            @if($payment->mercadopago_data)
                @php
                    $mpData = $payment->mercadopago_data;
                @endphp
                
                @switch($payment->forma_pagamento)
                    @case('pix')
                        @if(isset($mpData['point_of_interaction']['transaction_data']['qr_code']))
                            <div class="payment-method pix">
                                <h5>🔑 Código PIX</h5>
                                <div class="pix-code">
                                    <code>{{ $mpData['point_of_interaction']['transaction_data']['qr_code'] }}</code>
                                </div>
                                <p><strong>Como pagar:</strong></p>
                                <ol>
                                    <li>Copie o código PIX acima</li>
                                    <li>Abra seu banco ou carteira digital</li>
                                    <li>Escolha PIX → Pagar → Colar código</li>
                                    <li>Confirme o pagamento</li>
                                </ol>
                                <p class="highlight">⚡ Pagamento instantâneo!</p>
                            </div>
                        @else
                            <p>🔑 <strong>PIX:</strong> O código PIX será enviado em breve para o seu email e WhatsApp.</p>
                        @endif
                        @break
                    
                    @case('boleto')
                        @if(isset($mpData['transaction_details']['external_resource_url']))
                            <div class="payment-method boleto">
                                <h5>🧾 Boleto Bancário</h5>
                                <div class="boleto-link">
                                    <a href="{{ $mpData['transaction_details']['external_resource_url'] }}" target="_blank" class="btn-payment">
                                        📄 Baixar Boleto PDF
                                    </a>
                                </div>
                                <p><strong>Como pagar:</strong></p>
                                <ol>
                                    <li>Clique no botão acima para baixar o boleto</li>
                                    <li>Pague em qualquer banco, lotérica ou app bancário</li>
                                    <li>O pagamento pode levar até 3 dias úteis para ser processado</li>
                                </ol>
                            </div>
                        @else
                            <p>🧾 <strong>Boleto Bancário:</strong> O boleto será enviado em breve.</p>
                        @endif
                        @break
                    
                    @case('cartao_credito')
                        @if(isset($mpData['point_of_interaction']['transaction_data']['ticket_url']))
                            <div class="payment-method credit-card">
                                <h5>💳 Cartão de Crédito</h5>
                                <div class="card-link">
                                    <a href="{{ $mpData['point_of_interaction']['transaction_data']['ticket_url'] }}" target="_blank" class="btn-payment">
                                        💳 Pagar com Cartão
                                    </a>
                                </div>
                                <p><strong>Como pagar:</strong></p>
                                <ol>
                                    <li>Clique no botão acima</li>
                                    <li>Insira os dados do seu cartão</li>
                                    <li>Confirme o pagamento</li>
                                </ol>
                                <p class="highlight">⚡ Processamento imediato!</p>
                            </div>
                        @else
                            <p>💳 <strong>Cartão de Crédito:</strong> Link para pagamento será enviado em breve.</p>
                        @endif
                        @break
                @endswitch
            @else
                {{-- Fallback para quando não há dados do Mercado Pago --}}
                @switch($payment->forma_pagamento)
                    @case('pix')
                        <p>🔑 <strong>PIX:</strong> O código PIX será enviado em breve para o seu email e WhatsApp.</p>
                        <p>• Você pode pagar instantaneamente usando o código PIX</p>
                        <p>• O pagamento é processado imediatamente</p>
                        @break
                    
                    @case('boleto')
                        <p>🧾 <strong>Boleto Bancário:</strong> O boleto será enviado em breve.</p>
                        <p>• Você pode pagar em qualquer banco, lotérica ou app bancário</p>
                        <p>• O pagamento pode levar até 3 dias úteis para ser processado</p>
                        @break
                    
                    @case('cartao_credito')
                        <p>💳 <strong>Cartão de Crédito:</strong> Link para pagamento será enviado em breve.</p>
                        <p>• Pagamento seguro através do Mercado Pago</p>
                        <p>• Processamento imediato</p>
                        @break
                @endswitch
            @endif
        </div>

        <div class="contact-info">
            <h4>📞 Precisa de Ajuda?</h4>
            <p>Se você tiver dúvidas sobre esta cobrança, entre em contato conosco:</p>
            <p>
                📧 Email: contato@ensinocerto.com.br<br>
                📱 WhatsApp: (11) 99999-9999<br>
                🕒 Horário de atendimento: Segunda a Sexta, 8h às 18h
            </p>
        </div>

        <div class="footer">
            <p>Este é um email automático. Por favor, não responda.</p>
            <p><strong>EJA Supletivo</strong> - Educação de Qualidade</p>
            <p>{{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html> 