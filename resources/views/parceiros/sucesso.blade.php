<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro Realizado | Ensino Certo</title>
    <meta name="description" content="Seu cadastro como polo parceiro foi realizado com sucesso">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    
    <!-- Font Rubik -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Rubik', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0F001A 0%, #1A0B2E 50%, #2E1065 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 30px 80px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }
        
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .success-title {
            font-size: 2.8rem;
            color: #0d47a1;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .success-subtitle {
            font-size: 1.3rem;
            color: #28a745;
            margin-bottom: 40px;
            font-weight: 600;
        }
        
        .success-message {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 50px;
            line-height: 1.6;
        }
        
        .next-steps {
            background: #f8f9fa;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 40px;
            text-align: left;
        }
        
        .next-steps h3 {
            color: #0d47a1;
            font-size: 1.5rem;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .steps-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .steps-list li {
            padding: 15px 0;
            position: relative;
            padding-left: 40px;
            font-size: 1rem;
            color: #495057;
        }
        
        .steps-list li::before {
            content: counter(step-counter);
            counter-increment: step-counter;
            position: absolute;
            left: 0;
            top: 15px;
            background: #1e88e5;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .steps-list {
            counter-reset: step-counter;
        }
        
        .important-info {
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 40px;
        }
        
        .important-info h3 {
            color: white;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }
        
        .important-info ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .important-info li {
            padding: 8px 0;
            position: relative;
            padding-left: 25px;
        }
        
        .important-info li::before {
            content: "💡";
            position: absolute;
            left: 0;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .contact-item {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .contact-item:hover {
            border-color: #1e88e5;
            transform: translateY(-5px);
        }
        
        .contact-item i {
            font-size: 2rem;
            color: #1e88e5;
            margin-bottom: 15px;
        }
        
        .contact-item h4 {
            color: #0d47a1;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .contact-item p {
            color: #6c757d;
            margin: 0;
            font-size: 0.95rem;
        }
        
        .btn-home {
            background: linear-gradient(135deg, #1e88e5, #0d47a1);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(30, 136, 229, 0.4);
            color: white;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .success-card {
                padding: 40px 25px;
            }
            
            .success-title {
                font-size: 2.2rem;
            }
            
            .success-subtitle {
                font-size: 1.1rem;
            }
            
            .next-steps {
                padding: 25px;
            }
            
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1 class="success-title">🎉 Cadastro Realizado!</h1>
            <p class="success-subtitle">Bem-vindo à família Ensino Certo!</p>
            
            <p class="success-message">
                Obrigado por se cadastrar como nosso parceiro! Seu cadastro foi recebido com sucesso e nossa equipe comercial entrará em contato em breve para dar início à nossa parceria.
            </p>
            
            <div class="next-steps">
                <h3>📋 Próximos Passos</h3>
                <ol class="steps-list">
                    <li>Nossa equipe analisará seu cadastro em até 2 dias úteis</li>
                    <li>Você receberá um contato do nosso time comercial</li>
                    <li>Agendaremos uma reunião para apresentar a plataforma</li>
                    <li>Definiremos os detalhes da parceria</li>
                    <li>Daremos início ao processo de credenciamento</li>
                </ol>
            </div>
            
            <div class="important-info">
                <h3>💼 Informações Importantes</h3>
                <ul>
                    <li>Mantenha seus dados de contato atualizados</li>
                    <li>Verifique regularmente seu email (incluindo spam)</li>
                    <li>Prepare-se para conhecer nossa plataforma educacional</li>
                    <li>Tenha em mente sua estrutura e público-alvo</li>
                </ul>
            </div>
            
            <div class="contact-grid">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <h4>Email</h4>
                    <p>comercial@ensinocerto.com.br</p>
                </div>
                
                <div class="contact-item">
                    <i class="fab fa-whatsapp"></i>
                    <h4>WhatsApp</h4>
                    <p>(11) 9 9999-9999</p>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <h4>Horário de Atendimento</h4>
                    <p>Segunda a Sexta<br>9h às 18h</p>
                </div>
                
                <div class="contact-item">
                    <i class="fas fa-calendar"></i>
                    <h4>Tempo de Resposta</h4>
                    <p>Até 2 dias úteis</p>
                </div>
            </div>
            
            <a href="{{ route('home') }}" class="btn-home">
                <i class="fas fa-home"></i>
                Voltar ao Site
            </a>
        </div>
    </div>
</body>
</html> 