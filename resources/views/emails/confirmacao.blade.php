<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmação de Inscrição - EJA Supletivo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3a5998;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .info-box {
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 4px;
            border-left: 4px solid #3a5998;
        }
        .info-box h3 {
            color: #3a5998;
            margin-top: 0;
        }
        .steps {
            margin: 20px 0;
            padding: 0;
            list-style-type: none;
        }
        .steps li {
            padding: 10px 0 10px 30px;
            position: relative;
        }
        .steps li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #3a5998;
            font-weight: bold;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            background-color: #3a5998;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .contact-info {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .contact-item {
            text-align: center;
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 Inscrição Confirmada!</h1>
        <p>Obrigado por se inscrever no EJA Supletivo</p>
    </div>
    
    <div class="content">
        <p>Olá <strong>{{ $nome }}</strong>,</p>
        
        <p>Recebemos sua inscrição com sucesso! Estamos muito felizes em ter você conosco.</p>
        
        <div class="info-box">
            <h3>Detalhes da sua inscrição:</h3>
            <p><strong>Curso:</strong> {{ $curso_label }}</p>
            <p><strong>Modalidade:</strong> {{ $modalidade_label }}</p>
            <p><strong>Data da inscrição:</strong> {{ $data }}</p>
        </div>
        
        <div class="info-box">
            <h3>Próximos passos:</h3>
            <ul class="steps">
                <li>Nossa equipe irá analisar sua inscrição</li>
                <li>Entraremos em contato por telefone ou email em até 24 horas</li>
                <li>Você receberá orientações sobre a documentação necessária</li>
                <li>Após a confirmação, você terá acesso ao material do curso</li>
            </ul>
        </div>
        
        <div class="info-box">
            <h3>Dicas importantes:</h3>
            <p>✓ Mantenha seu telefone disponível para contato</p>
            <p>✓ Verifique sua caixa de entrada e pasta de spam regularmente</p>
            <p>✓ Prepare seus documentos pessoais para agilizar o processo</p>
        </div>
        
        <p>Se tiver qualquer dúvida, não hesite em nos contatar pelos canais abaixo:</p>
        
        <div class="contact-info">
            <div class="contact-item">
                <p><strong>Telefone</strong><br>(11) 9999-9999</p>
            </div>
            <div class="contact-item">
                <p><strong>Email</strong><br>contato@ensinocerto.com.br</p>
            </div>
            <div class="contact-item">
                <p><strong>Horário</strong><br>Seg-Sex: 8h às 18h</p>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="https://www.ensinocerto.com.br" class="button">Visite nosso site</a>
        </div>
    </div>
    
    <div class="footer">
        <p>EJA Supletivo - Ensino Certo</p>
        <p>Este é um email automático, por favor não responda.</p>
    </div>
</body>
</html>