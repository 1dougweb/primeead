<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nova Inscrição EJA</title>
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
            background-color: #6f42c1;
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
        .field {
            margin-bottom: 15px;
            padding: 10px;
            background-color: white;
            border-radius: 4px;
            border-left: 4px solid #6f42c1;
        }
        .field strong {
            color: #6f42c1;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 Nova Inscrição EJA</h1>
        <p>Você recebeu uma nova inscrição para o curso EJA</p>
    </div>
    
    <div class="content">
        <div class="field">
            <strong>Nome:</strong> {{ $nome }}
        </div>
        
        <div class="field">
            <strong>Email:</strong> {{ $email }}
        </div>
        
        <div class="field">
            <strong>Telefone:</strong> {{ $telefone }}
        </div>
        
        <div class="field">
            <strong>Curso de interesse:</strong> {{ $curso_label }}
        </div>
        
        <div class="field">
            <strong>Modalidade:</strong> {{ $modalidade_label }}
        </div>
        
        <div class="field">
            <strong>Aceita termos:</strong> {{ $termos }}
        </div>
        
        <div class="field">
            <strong>Data/Hora:</strong> {{ $data }}
        </div>
        
        <div class="field">
            <strong>IP:</strong> {{ $ip }}
        </div>
    </div>
    
    <div class="footer">
        <p>Sistema de Inscrições EJA - Ensino Certo</p>
        <p>Este email foi gerado automaticamente, não responda.</p>
    </div>
</body>
</html> 