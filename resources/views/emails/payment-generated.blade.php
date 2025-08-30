<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $paymentType }} Disponível</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn-primary { background: #007bff; }
        .btn-warning { background: #ffc107; color: #212529; }
        .info-box { background: white; padding: 20px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745; }
        .qr-code { text-align: center; margin: 20px 0; }
        .qr-code img { max-width: 200px; border: 2px solid #ddd; border-radius: 10px; }
        .pix-code { background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #ddd; word-break: break-all; font-family: monospace; font-size: 12px; margin: 10px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
        .copy-btn { background: #6c757d; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💳 {{ $paymentType }} Disponível</h1>
            <p>Sua mensalidade está pronta para pagamento!</p>
        </div>
        
        <div class="content">
            <p>Olá, <strong>{{ $matricula->nome_completo }}</strong>!</p>
            
            <div class="success">
                ✅ <strong>Seu {{ $paymentType }} foi gerado com sucesso!</strong>
            </div>
            
            <div class="info-box">
                <h3>📋 Detalhes do Pagamento</h3>
                <ul>
                    <li><strong>Curso:</strong> {{ $matricula->curso }}</li>
                    <li><strong>Parcela:</strong> {{ $payment->numero_parcela }}/{{ $matricula->numero_parcelas }}</li>
                    <li><strong>Valor:</strong> R$ {{ number_format($payment->valor, 2, ',', '.') }}</li>
                    <li><strong>Vencimento:</strong> {{ $payment->data_vencimento->format('d/m/Y') }}</li>
                    <li><strong>Status:</strong> {{ ucfirst($payment->status) }}</li>
                </ul>
            </div>

            @if(isset($paymentData['point_of_interaction']) && isset($paymentData['point_of_interaction']['transaction_data']))
                {{-- PIX --}}
                <div class="info-box">
                    <h3>📱 Pagamento via PIX</h3>
                    
                    @if(isset($paymentData['point_of_interaction']['transaction_data']['qr_code_base64']))
                        <div class="qr-code">
                            <p><strong>Escaneie o QR Code:</strong></p>
                            <img src="data:image/png;base64,{{ $paymentData['point_of_interaction']['transaction_data']['qr_code_base64'] }}" alt="QR Code PIX">
                        </div>
                    @endif
                    
                    @if(isset($paymentData['point_of_interaction']['transaction_data']['qr_code']))
                        <p><strong>Ou copie o código PIX:</strong></p>
                        <div class="pix-code">
                            {{ $paymentData['point_of_interaction']['transaction_data']['qr_code'] }}
                        </div>
                        <p><small>💡 <strong>Como pagar:</strong> Abra o app do seu banco → PIX → Copia e Cola → Cole o código acima</small></p>
                    @endif
                </div>

            @elseif(isset($paymentData['transaction_details']) && isset($paymentData['transaction_details']['external_resource_url']))
                {{-- Boleto --}}
                <div class="info-box">
                    <h3>📄 Boleto Bancário</h3>
                    <p>Seu boleto foi gerado e está disponível para download:</p>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="{{ $paymentData['transaction_details']['external_resource_url'] }}" target="_blank" class="btn btn-warning">
                            📥 Baixar Boleto
                        </a>
                    </div>
                    
                    <p><small>💡 <strong>Como pagar:</strong> Baixe o boleto e pague em qualquer banco, lotérica ou pelo internet banking</small></p>
                </div>

            @elseif(isset($paymentData['init_point']))
                {{-- Link de Pagamento (Cartão) --}}
                <div class="info-box">
                    <h3>💳 Link de Pagamento</h3>
                    <p>Acesse o link abaixo para pagar com cartão de crédito:</p>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="{{ $paymentData['init_point'] }}" target="_blank" class="btn btn-primary">
                            🔗 Pagar com Cartão
                        </a>
                    </div>
                    
                    <p><small>💡 <strong>Como pagar:</strong> Clique no link e complete o pagamento no site seguro do MercadoPago</small></p>
                </div>
            @endif

            <div class="info-box">
                <h3>🔒 Segurança</h3>
                <ul>
                    <li>✅ Pagamento processado via MercadoPago (empresa do Mercado Livre)</li>
                    <li>✅ Seus dados estão protegidos com criptografia</li>
                    <li>✅ Comprovante será enviado automaticamente após o pagamento</li>
                </ul>
            </div>
            
            <p>❓ <strong>Dúvidas?</strong> Entre em contato conosco se precisar de ajuda:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="mailto:contato@ensinocerto.com" class="btn">📧 Entrar em Contato</a>
                <a href="https://api.whatsapp.com/send?phone=5511999999999" class="btn btn-primary">📱 WhatsApp</a>
            </div>
            
            <div class="footer">
                <p>Este é um email automático. Por favor, não responda.</p>
                <p><strong>Ensino Certo</strong> - Educação de Qualidade</p>
                @if(isset($paymentData['id']))
                    <p>ID do Pagamento: {{ $paymentData['id'] }}</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html> 