<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Links de Pagamento Disponíveis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .payment-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .payment-info h3 {
            margin-top: 0;
            color: #007bff;
        }
        .payment-info p {
            margin: 5px 0;
        }
        .payment-method {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .payment-method h5 {
            margin-top: 0;
            color: #495057;
        }
        .pix-code {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 14px;
            word-break: break-all;
            white-space: pre-wrap;
            margin: 10px 0;
            border: 1px solid #ddd;
        }
        .btn-payment {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 0;
            text-align: center;
        }
        .btn-payment:hover {
            background-color: #0056b3;
        }
        .btn-payment.boleto {
            background-color: #28a745;
        }
        .btn-payment.boleto:hover {
            background-color: #1e7e34;
        }
        .btn-payment.credit-card {
            background-color: #17a2b8;
        }
        .btn-payment.credit-card:hover {
            background-color: #138496;
        }
        .instructions {
            margin-bottom: 20px;
        }
        .instructions ol {
            padding-left: 20px;
        }
        .instructions li {
            margin-bottom: 5px;
        }
        .highlight {
            color: #dc3545;
            font-weight: bold;
        }
        .contact-info {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin-bottom: 20px;
        }
        .contact-info h4 {
            margin-top: 0;
            color: #856404;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 12px;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔗 Links de Pagamento Disponíveis</h1>
            <p>Seus links de pagamento estão prontos!</p>
        </div>

        <div class="payment-info">
            <h3>📋 Informações do Pagamento</h3>
            <p><strong>Estudante:</strong> {{ $matricula->nome_completo }}</p>
            <p><strong>Valor:</strong> {{ $formattedAmount }}</p>
            <p><strong>Vencimento:</strong> {{ $formattedDueDate }}</p>
            <p><strong>Descrição:</strong> {{ $payment->descricao }}</p>
            @if($isParceled)
                <p><strong>Parcela:</strong> {{ $parcelInfo }}</p>
            @endif
            <p><strong>Forma de pagamento:</strong> {{ $paymentMethod }}</p>
        </div>

        <div class="instructions">
            <h4>💳 Como Pagar</h4>
            @if($payment->mercadopago_data)
                @php
                    $mpData = $payment->mercadopago_data;
                @endphp
                
                @switch($payment->forma_pagamento)
                    @case('pix')
                        @if(isset($mpData['point_of_interaction']['transaction_data']['qr_code']))
                            <div class="payment-method">
                                <h5>🔑 Código PIX</h5>
                                <p>Copie o código PIX abaixo e cole no seu banco ou carteira digital:</p>
                                <div class="pix-code">{{ $mpData['point_of_interaction']['transaction_data']['qr_code'] }}</div>
                                <p><strong>Passos para pagar:</strong></p>
                                <ol>
                                    <li>Copie o código PIX acima</li>
                                    <li>Abra seu banco ou carteira digital</li>
                                    <li>Escolha PIX → Pagar → Colar código</li>
                                    <li>Confirme o pagamento</li>
                                </ol>
                                <p class="highlight">⚡ Pagamento instantâneo!</p>
                            </div>
                        @endif
                        @break
                    
                    @case('boleto')
                        @if(isset($mpData['transaction_details']['external_resource_url']))
                            <div class="payment-method">
                                <h5>🧾 Boleto Bancário</h5>
                                <p>Clique no botão abaixo para baixar seu boleto:</p>
                                <a href="{{ $mpData['transaction_details']['external_resource_url'] }}" target="_blank" class="btn-payment boleto">
                                    📄 Baixar Boleto PDF
                                </a>
                                <p><strong>Passos para pagar:</strong></p>
                                <ol>
                                    <li>Clique no botão acima para baixar o boleto</li>
                                    <li>Pague em qualquer banco, lotérica ou app bancário</li>
                                    <li>O pagamento pode levar até 3 dias úteis para ser processado</li>
                                </ol>
                            </div>
                        @endif
                        @break
                    
                    @case('cartao_credito')
                        @if(isset($mpData['point_of_interaction']['transaction_data']['ticket_url']))
                            <div class="payment-method">
                                <h5>💳 Cartão de Crédito</h5>
                                <p>Clique no botão abaixo para pagar com cartão:</p>
                                <a href="{{ $mpData['point_of_interaction']['transaction_data']['ticket_url'] }}" target="_blank" class="btn-payment credit-card">
                                    💳 Pagar com Cartão
                                </a>
                                <p><strong>Passos para pagar:</strong></p>
                                <ol>
                                    <li>Clique no botão acima</li>
                                    <li>Insira os dados do seu cartão</li>
                                    <li>Confirme o pagamento</li>
                                </ol>
                                <p class="highlight">⚡ Processamento imediato!</p>
                            </div>
                        @endif
                        @break
                @endswitch
            @endif
        </div>

        <div class="contact-info">
            <h4>📞 Precisa de Ajuda?</h4>
            <p>Se você tiver dúvidas sobre este pagamento, entre em contato conosco:</p>
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