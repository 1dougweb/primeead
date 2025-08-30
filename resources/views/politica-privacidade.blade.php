<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - Ensino Certo</title>
    <meta name="description" content="Política de Privacidade da Ensino Certo. Saiba como coletamos, utilizamos e protegemos seus dados pessoais.">
    <meta name="robots" content="index, follow">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}?v={{ time() }}">
    <!-- Font Rubik -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .policy-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            line-height: 1.8;
            color: #333;
        }
        
        .policy-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #FFB200;
        }
        
        .policy-header h1 {
            color: #2E1065;
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .policy-header .subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        
        .policy-section {
            margin-bottom: 30px;
        }
        
        .policy-section h2 {
            color: #2E1065;
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-left: 20px;
            position: relative;
        }
        
        .policy-section h2::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 20px;
            background: #FFB200;
            border-radius: 2px;
        }
        
        .policy-section p {
            margin-bottom: 15px;
            text-align: justify;
        }
        
        .policy-section ul {
            margin: 15px 0;
            padding-left: 30px;
        }
        
        .policy-section ul li {
            margin-bottom: 8px;
            position: relative;
        }
        
        .policy-section ul li::marker {
            color: #FFB200;
        }
        
        .contact-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #FFB200;
            margin-top: 40px;
        }
        
        .contact-info h3 {
            color: #2E1065;
            margin-bottom: 10px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #2E1065;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 30px;
            transition: color 0.3s ease;
        }
        
        .back-link:hover {
            color: #FFB200;
        }
        
        @media (max-width: 768px) {
            .policy-container {
                padding: 20px 15px;
            }
            
            .policy-header h1 {
                font-size: 2rem;
            }
            
            .policy-section {
                margin-bottom: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="policy-container">
        <a href="{{ route('home') }}" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Voltar ao site
        </a>
        
        <div class="policy-header">
            <h1>Política de Privacidade</h1>
            <p class="subtitle">Como coletamos, utilizamos e protegemos seus dados pessoais</p>
        </div>
        
        <div class="policy-section">
            <h2>1. Compromisso com a sua privacidade</h2>
            <p>A Ensino Certo valoriza a transparência e o respeito aos seus dados pessoais. Esta Política de Privacidade tem como objetivo esclarecer como coletamos, utilizamos e protegemos as informações fornecidas por você ao utilizar nossos serviços.</p>
        </div>
        
        <div class="policy-section">
            <h2>2. Coleta de informações</h2>
            <p>Podemos coletar as seguintes informações:</p>
            <ul>
                <li>Nome completo</li>
                <li>Endereço de e-mail</li>
                <li>Telefone de contato</li>
                <li>CPF ou RG (quando necessário)</li>
                <li>Dados de navegação (cookies, endereço IP, localização, entre outros)</li>
            </ul>
            <p>Essas informações são coletadas por meio de formulários de contato, cadastros em nossa plataforma ou interações com nossos conteúdos.</p>
        </div>
        
        <div class="policy-section">
            <h2>3. Uso das informações</h2>
            <p>As informações coletadas são utilizadas para:</p>
            <ul>
                <li>Entrar em contato com você, quando solicitado</li>
                <li>Enviar informações e conteúdos relacionados aos nossos cursos e serviços</li>
                <li>Processar inscrições e pagamentos</li>
                <li>Melhorar a experiência do usuário em nossa plataforma</li>
                <li>Cumprir obrigações legais e regulatórias</li>
            </ul>
        </div>
        
        <div class="policy-section">
            <h2>4. Compartilhamento de dados</h2>
            <p>A Ensino Certo não vende, aluga ou compartilha seus dados pessoais com terceiros, exceto nos seguintes casos:</p>
            <ul>
                <li>Quando exigido por lei ou autoridades competentes</li>
                <li>Para parceiros estratégicos, quando necessário para a execução dos nossos serviços (ex: provedores de pagamento)</li>
                <li>Com consentimento expresso do usuário</li>
            </ul>
        </div>
        
        <div class="policy-section">
            <h2>5. Armazenamento e segurança</h2>
            <p>Adotamos medidas técnicas e administrativas para proteger seus dados contra acesso não autorizado, destruição, perda ou alteração. Os dados são armazenados em servidores seguros e com acesso restrito.</p>
        </div>
        
        <div class="policy-section">
            <h2>6. Direitos do usuário</h2>
            <p>Você tem o direito de:</p>
            <ul>
                <li>Solicitar acesso aos seus dados pessoais</li>
                <li>Corrigir ou atualizar informações</li>
                <li>Solicitar a exclusão dos seus dados</li>
                <li>Revogar o consentimento para o uso de informações</li>
                <li>Portar seus dados para outro serviço, se desejar</li>
            </ul>
            <p>Para exercer seus direitos, entre em contato pelo telefone (11) 4210-3596 ou pelos canais de atendimento disponibilizados em nosso site.</p>
        </div>
        
        <div class="policy-section">
            <h2>7. Uso de cookies</h2>
            <p>Utilizamos cookies para melhorar sua navegação e personalizar conteúdos. Você pode desativar os cookies nas configurações do seu navegador, mas isso pode impactar algumas funcionalidades do site.</p>
        </div>
        
        <div class="policy-section">
            <h2>8. Alterações nesta política</h2>
            <p>Esta Política de Privacidade pode ser atualizada periodicamente. Recomendamos a leitura frequente desta página para estar ciente de eventuais mudanças.</p>
        </div>
        
        <div class="contact-info">
            <h3>Informações de Contato</h3>
            <p><strong>Última atualização:</strong> 29 de abril de 2025</p>
            <p><strong>Ensino Certo</strong><br>
            CNPJ: 73.075.954/0001-37<br>
            Telefone: (11) 4210-3596</p>
        </div>
    </div>
</body>
</html> 