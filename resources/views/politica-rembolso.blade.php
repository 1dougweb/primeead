<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Reembolso - Ensino Certo</title>
    <meta name="description" content="Política de Reembolso da Ensino Certo. Saiba as condições e procedimentos para solicitar reembolso.">
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
        
        .highlight-box {
            background: #f0f4ff;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #2E1065;
            margin: 20px 0;
        }
        
        .highlight-box h4 {
            color: #2E1065;
            margin-bottom: 10px;
            font-weight: 600;
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
        
        .deadline-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .deadline-info strong {
            color: #856404;
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
            <h1>Política de Reembolso</h1>
            <p class="subtitle">Condições e procedimentos para solicitação de reembolso</p>
        </div>
        
        <div class="policy-section">
            <h2>1. Compromisso com a satisfação</h2>
            <p>A Ensino Certo preza pela qualidade dos seus cursos e serviços. Trabalhamos para oferecer a melhor experiência de aprendizado, mas entendemos que podem surgir situações que levem à solicitação de reembolso. Esta política estabelece as condições, prazos e procedimentos para o reembolso de valores pagos.</p>
        </div>
        
        <div class="policy-section">
            <h2>2. Condições para solicitação de reembolso</h2>
            <p>O reembolso poderá ser solicitado nas seguintes situações:</p>
            <ul>
                <li>Inscrição realizada em curso online, respeitando o prazo de arrependimento previsto no Código de Defesa do Consumidor.</li>
                <li>Problemas técnicos que inviabilizem o acesso ao conteúdo, sem que haja solução após contato com nosso suporte.</li>
            </ul>
        </div>
        
        <div class="policy-section">
            <h2>3. Prazo para solicitação</h2>
            <div class="deadline-info">
                <strong>⚠️ Prazo Legal:</strong> O pedido de reembolso deve ser feito em até <strong>7 (sete) dias corridos</strong> após a confirmação da compra, conforme previsto no artigo 49 do Código de Defesa do Consumidor (Lei nº 8.078/1990).
            </div>
            <p>Após esse prazo, não será possível solicitar reembolso, salvo em casos excepcionais devidamente analisados pela nossa equipe.</p>
        </div>
        
        <div class="policy-section">
            <h2>4. Procedimento para solicitação</h2>
            <p>Para solicitar o reembolso, o aluno deverá:</p>
            <div class="highlight-box">
                <h4>Canais de Contato:</h4>
                <ul>
                    <li>Enviar um e-mail para [email de atendimento]</li>
                    <li>Entrar em contato pelo telefone <strong>(11) 4210-3596</strong></li>
                </ul>
            </div>
            <p><strong>Informações necessárias:</strong></p>
            <ul>
                <li>Nome completo</li>
                <li>CPF</li>
                <li>Curso adquirido</li>
                <li>Motivo do pedido de reembolso</li>
                <li>Comprovante de pagamento</li>
            </ul>
            <p>Nossa equipe avaliará a solicitação e retornará em até <strong>5 dias úteis</strong> com a resposta.</p>
        </div>
        
        <div class="policy-section">
            <h2>5. Forma de reembolso</h2>
            <ul>
                <li><strong>Compras realizadas via cartão de crédito:</strong> o estorno será solicitado à operadora do cartão e poderá ocorrer em até 2 faturas subsequentes, dependendo da administradora.</li>
                <li><strong>Compras realizadas via boleto bancário ou Pix:</strong> o reembolso será feito por transferência bancária para conta de titularidade do solicitante, em até 10 dias úteis após a aprovação do pedido.</li>
            </ul>
        </div>
        
        <div class="policy-section">
            <h2>6. Observações importantes</h2>
            <div class="highlight-box">
                <h4>🎯 Regra dos 20%:</h4>
                <p>O reembolso será <strong>integral</strong> apenas se o curso não tiver sido consumido em mais de <strong>20% do seu conteúdo</strong>.</p>
                <p>Caso o aluno tenha acessado mais de 20% das aulas ou materiais disponibilizados, a solicitação será analisada individualmente e poderá ser recusada.</p>
            </div>
            <ul>
                <li>Em casos de fraudes, uso indevido ou descumprimento dos termos de uso, a Ensino Certo se reserva o direito de negar o reembolso.</li>
            </ul>
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