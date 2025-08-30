<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Mensagem de Contato</title>
    <style>
        body {
            font-family: 'Rubik', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(145deg, rgba(37, 0, 173, 1) 0%, rgba(99, 0, 148, 1) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .content {
            padding: 30px;
        }
        .info-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #2500AD;
        }
        .info-row {
            display: flex;
            margin-bottom: 12px;
            align-items: center;
        }
        .info-label {
            font-weight: 600;
            color: #2500AD;
            min-width: 80px;
            margin-right: 15px;
        }
        .info-value {
            color: #333;
            flex: 1;
        }
        .message-section {
            background-color: #fff;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .message-label {
            font-weight: 600;
            color: #2500AD;
            margin-bottom: 10px;
            display: block;
        }
        .message-text {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            line-height: 1.8;
            white-space: pre-wrap;
            border-left: 3px solid #2500AD;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2500AD;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin: 10px 0;
        }
        .btn:hover {
            background-color: #1e0080;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 Nova Mensagem de Contato</h1>
            <p>Você recebeu uma nova mensagem através do formulário de contato</p>
        </div>
        
        <div class="content">
            <div class="info-section">
                <h3 style="margin-top: 0; color: #2500AD;">📋 Dados do Contato</h3>
                
                <div class="info-row">
                    <span class="info-label">👤 Nome:</span>
                    <span class="info-value"><strong>{{ $dadosContato['nome'] }}</strong></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">📧 Email:</span>
                    <span class="info-value">
                        <a href="mailto:{{ $dadosContato['email'] }}" style="color: #2500AD; text-decoration: none;">
                            {{ $dadosContato['email'] }}
                        </a>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">📱 Telefone:</span>
                    <span class="info-value">
                        <a href="tel:{{ $dadosContato['telefone'] }}" style="color: #2500AD; text-decoration: none;">
                            {{ $dadosContato['telefone'] }}
                        </a>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">📝 Assunto:</span>
                    <span class="info-value"><span class="highlight">{{ $dadosContato['assunto'] }}</span></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">🕒 Data:</span>
                    <span class="info-value">{{ $dadosContato['created_at']->format('d/m/Y H:i:s') }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">🌐 IP:</span>
                    <span class="info-value">{{ $dadosContato['ip_address'] }}</span>
                </div>
            </div>
            
            <div class="message-section">
                <span class="message-label">💭 Mensagem:</span>
                <div class="message-text">{{ $dadosContato['mensagem'] }}</div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="mailto:{{ $dadosContato['email'] }}?subject=Re: {{ $dadosContato['assunto'] }}" class="btn">
                    📩 Responder por Email
                </a>
                
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $dadosContato['telefone']) }}" class="btn" style="background-color: #25d366; margin-left: 10px;">
                    📱 Contatar via WhatsApp
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>Esta mensagem foi enviada através do formulário de contato do site <strong>Ensino Certo</strong></p>
            <p>Por favor, responda em até 24 horas para melhor atendimento ao cliente.</p>
        </div>
    </div>
</body>
</html> 