<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembrete - Contrato Digital</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .urgent-notice {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        
        .urgent-notice h3 {
            color: #856404;
            margin: 0 0 10px 0;
            font-size: 20px;
        }
        
        .urgent-notice p {
            color: #856404;
            margin: 0;
            font-size: 16px;
            font-weight: 500;
        }
        
        .days-left {
            font-size: 32px;
            font-weight: bold;
            color: #e67e22;
            margin: 10px 0;
        }
        
        .contract-info {
            background-color: #f8f9fa;
            border-left: 4px solid #f39c12;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 5px 5px 0;
        }
        
        .contract-info h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 18px;
        }
        
        .contract-info p {
            margin: 5px 0;
            color: #6c757d;
        }
        
        .action-button {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4);
        }
        
        .consequences {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 20px;
            margin: 25px 0;
        }
        
        .consequences h4 {
            color: #721c24;
            margin: 0 0 15px 0;
            font-size: 16px;
        }
        
        .consequences p {
            margin: 8px 0;
            color: #721c24;
        }
        
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        
        .footer p {
            margin: 5px 0;
            font-size: 14px;
            opacity: 0.8;
        }
        
        .footer .company-name {
            font-weight: 600;
            font-size: 16px;
            opacity: 1;
        }
        
        .contact-info {
            background-color: #e8f4f8;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .contact-info p {
            margin: 0;
            color: #0c5460;
            font-size: 14px;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            
            .header, .content, .footer {
                padding: 20px;
            }
            
            .btn {
                padding: 12px 25px;
                font-size: 14px;
            }
            
            .days-left {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>⚠️ Lembrete Importante</h1>
            <p>Seu contrato digital está próximo do vencimento</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Olá, <strong>{{ $student_name }}</strong>!
            </div>
            
            <div class="urgent-notice">
                <h3>🕐 Tempo Restante</h3>
                <div class="days-left">{{ $days_left }} {{ $days_left == 1 ? 'dia' : 'dias' }}</div>
                <p>para assinar seu contrato digital</p>
            </div>
            
            <p>
                Este é um lembrete importante sobre seu contrato digital que ainda não foi assinado. 
                O prazo para assinatura está se aproximando e é necessário que você tome uma ação imediata.
            </p>
            
            <div class="contract-info">
                <h3>📄 Informações do Contrato</h3>
                <p><strong>Título:</strong> {{ $contract->title }}</p>
                <p><strong>Número:</strong> {{ $contract->contract_number }}</p>
                <p><strong>Status:</strong> {{ $contract->status_formatted }}</p>
                <p><strong>Expira em:</strong> {{ $expires_at->format('d/m/Y \à\s H:i') }}</p>
            </div>
            
            <div class="action-button">
                <a href="{{ $access_link }}" class="btn">
                    🔗 Assinar Contrato Agora
                </a>
            </div>
            
            @if($days_left <= 3)
                <div class="consequences">
                    <h4>⚠️ Atenção - Prazo Crítico</h4>
                    <p>
                        <strong>Após o vencimento:</strong> O link de acesso será invalidado e você 
                        precisará entrar em contato conosco para solicitar um novo contrato.
                    </p>
                    <p>
                        <strong>Recomendação:</strong> Assine o contrato o quanto antes para evitar 
                        atrasos em seu processo de matrícula.
                    </p>
                </div>
            @endif
            
            <p>
                Se você não conseguir acessar o contrato clicando no botão, 
                copie e cole o link abaixo em seu navegador:
            </p>
            
            <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px;">
                {{ $access_link }}
            </p>
            
            <div class="contact-info">
                <p>
                    <strong>💬 Precisa de ajuda?</strong> 
                    Entre em contato conosco através dos canais oficiais de atendimento 
                    se tiver dúvidas ou problemas técnicos.
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p class="company-name">EJA Supletivo</p>
            <p>Sistema de Contratos Digitais</p>
            <p>Este é um email automático, não responda.</p>
        </div>
    </div>
</body>
</html> 