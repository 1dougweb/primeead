<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelamento de Inscrição</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .unsubscribe-container {
            max-width: 600px;
            width: 100%;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
        }
        
        .icon-container {
            margin-bottom: 30px;
        }
        
        .icon-container i {
            font-size: 64px;
        }
        
        .success-icon {
            color: #28a745;
        }
        
        .error-icon {
            color: #dc3545;
        }
        
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        p {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 30px;
        }
    </style>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="unsubscribe-container">
        @if($success)
            <div class="icon-container">
                <i class="fas fa-check-circle success-icon"></i>
            </div>
            <h1>Inscrição cancelada com sucesso!</h1>
            <p>O email <strong>{{ $email }}</strong> foi removido da nossa lista de emails.</p>
        @else
            <div class="icon-container">
                <i class="fas fa-exclamation-circle error-icon"></i>
            </div>
            <h1>Ocorreu um erro</h1>
            <p>{{ $message }}</p>
        @endif
        
        <a href="{{ route('home') }}" class="btn btn-primary">Voltar para a página inicial</a>
    </div>
</body>
</html> 