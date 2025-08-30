<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Erro' }} - Contrato</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            max-width: 600px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
        }
        
        .error-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 2rem;
        }
        
        .error-content {
            padding: 2rem;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- Header -->
        <div class="error-header">
            <h1><i class="fas fa-exclamation-triangle me-2"></i>{{ $title ?? 'Erro' }}</h1>
        </div>
        
        <!-- Content -->
        <div class="error-content">
            <div class="error-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            
            <h3>Oops! Algo deu errado</h3>
            <p class="mb-4">{{ $message ?? 'Ocorreu um erro inesperado.' }}</p>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Voltar ao Início
                </a>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    Se o problema persistir, entre em contato com o suporte.
                </small>
            </div>
        </div>
    </div>
</body>
</html> 