<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembrete de Pagamento</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .alert { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .info-box { background: white; padding: 20px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Lembrete de Pagamento</h1>
            <p>Sua mensalidade vence em breve!</p>
        </div>
        
        <div class="content">
            <p>Olá, <strong>{{ $matricula->nome_completo }}</strong>!</p>
            
            <div class="alert">
                ⏰ <strong>Sua mensalidade vence em {{ $daysUntilDue }} dia{{ $daysUntilDue > 1 ? 's' : '' }}!</strong>
            </div>
            
            <div class="info-box">
                <h3>📋 Detalhes do Pagamento</h3>
                <ul>
                    <li><strong>Curso:</strong> {{ $matricula->curso }}</li>
                    <li><strong>Parcela:</strong> {{ $payment->numero_parcela }}/{{ $matricula->numero_parcelas }}</li>
                    <li><strong>Valor:</strong> R$ {{ number_format($payment->valor, 2, ',', '.') }}</li>
                    <li><strong>Vencimento:</strong> {{ $payment->data_vencimento->format('d/m/Y') }}</li>
                </ul>
            </div>
            
            <p>💡 <strong>Dica:</strong> O boleto da sua mensalidade será gerado automaticamente no dia do vencimento e enviado por email.</p>
            
            <p>Se você tiver alguma dúvida ou precisar de ajuda, entre em contato conosco:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="mailto:contato@ensinocerto.com" class="btn">📧 Entrar em Contato</a>
            </div>
            
            <div class="footer">
                <p>Este é um email automático. Por favor, não responda.</p>
                <p><strong>Ensino Certo</strong> - Educação de Qualidade</p>
            </div>
        </div>
    </div>
</body>
</html> 