<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageSettings['page_title'] ?? 'Inscrição Confirmada!' }} | EJA Supletivo</title>
    <meta name="description" content="{{ $pageSettings['page_subtitle'] ?? 'Sua inscrição foi realizada com sucesso.' }}">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    
    <!-- Font Rubik -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tracking Scripts -->
    @include('components.tracking-scripts')
    
    <style>
        body {
            font-family: 'Rubik', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        .obrigado-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .obrigado-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: {{ $pageSettings['header_color'] }};
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .success-icon i {
            font-size: 40px;
            color: {{ $pageSettings['header_color'] }};
        }
        
        .card-body {
            padding: 30px;
        }
        
        .obrigado-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: white;
        }
        
        .obrigado-subtitle {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 0;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: {{ $pageSettings['header_color'] }};
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
        }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-card h3 {
            font-size: 1.1rem;
            color: {{ $pageSettings['header_color'] }};
            margin-top: 0;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .steps-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .steps-list li {
            padding: 10px 0 10px 30px;
            position: relative;
            border-bottom: 1px solid #eee;
        }
        
        .steps-list li:last-child {
            border-bottom: none;
        }
        
        .steps-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: {{ $pageSettings['header_color'] }};
            font-weight: bold;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .contact-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .contact-item i {
            font-size: 24px;
            color: {{ $pageSettings['header_color'] }};
            margin-bottom: 10px;
        }
        
        .contact-item h4 {
            font-weight: 600;
            margin: 10px 0 5px;
        }
        
        .contact-item p {
            margin: 0;
            color: #666;
        }
        
        .btn-voltar {
            display: inline-block;
            background: {{ $pageSettings['header_color'] }};
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-voltar:hover {
            background: {{ $pageSettings['header_color'] }};
            filter: brightness(90%);
            transform: translateY(-2px);
        }
        
        .inscricao-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .inscricao-details p {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        
        .inscricao-details strong {
            color: {{ $pageSettings['header_color'] }};
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            color: white;
        }
        
        .badge-primary {
            background-color: {{ $pageSettings['header_color'] }};
        }
        
        .badge-success {
            background-color: #28a745;
        }
        
        .custom-message {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid {{ $pageSettings['header_color'] }};
        }
        
        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
            
            .obrigado-title {
                font-size: 1.8rem;
            }
            
            .card-header, .card-body {
                padding: 20px;
            }
        }
    </style>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-NPXJKW38');</script>
    <!-- End Google Tag Manager -->
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NPXJKW38"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    @include('components.tracking-noscript')
    <div class="obrigado-container">
        <div class="obrigado-card">
            <div class="card-header">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1 class="obrigado-title">{{ $pageSettings['page_title'] }}</h1>
                <p class="obrigado-subtitle">
                    {{ $pageSettings['page_subtitle'] }}
                </p>
            </div>
            
            <div class="card-body">
                @if(isset($inscricao))
                    <div class="info-section">
                        <h2 class="section-title">
                            <i class="fas fa-clipboard-list"></i>
                            Detalhes da sua inscrição
                        </h2>
                        <div class="inscricao-details">
                            <p>
                                <strong>Nome:</strong> 
                                <span>{{ $inscricao->nome }}</span>
                            </p>
                            <p>
                                <strong>Email:</strong> 
                                <span>{{ $inscricao->email }}</span>
                            </p>
                            <p>
                                <strong>Curso:</strong> 
                                <span class="badge badge-primary">{{ $inscricao->curso_label }}</span>
                            </p>
                            <p>
                                <strong>Modalidade:</strong> 
                                <span class="badge badge-success">{{ $inscricao->modalidade_label }}</span>
                            </p>
                            <p>
                                <strong>Data:</strong> 
                                <span>{{ $inscricao->created_at->format('d/m/Y H:i') }}</span>
                            </p>
                        </div>
                        <p>Enviamos um email de confirmação para <strong>{{ $inscricao->email }}</strong> com todas as informações.</p>
                    </div>
                @endif
                
                @if(!empty($pageSettings['custom_message']))
                    <div class="custom-message">
                        {!! $pageSettings['custom_message'] !!}
                    </div>
                @endif
                
                @if($pageSettings['show_steps'])
                    <div class="info-section">
                        <h2 class="section-title">
                            <i class="fas fa-clipboard-check"></i>
                            Próximos passos
                        </h2>
                        <div class="info-card">
                            <ul class="steps-list">
                                <li>Nossa equipe irá analisar sua inscrição</li>
                                <li>Entraremos em contato por telefone ou email em até 24 horas</li>
                                <li>Você receberá orientações sobre a documentação necessária</li>
                                <li>Após a confirmação, você terá acesso ao material do curso</li>
                            </ul>
                        </div>
                    </div>
                @endif
                
                @if($pageSettings['show_contact_info'])
                    <div class="info-section">
                        <h2 class="section-title">
                            <i class="fas fa-phone-alt"></i>
                            Canais de atendimento
                        </h2>
                        <div class="contact-grid">
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <h4>Telefone</h4>
                                <p>{{ $pageSettings['contact_phone'] }}</p>
                            </div>
                            
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <h4>Email</h4>
                                <p>{{ $pageSettings['contact_email'] }}</p>
                            </div>
                            
                            <div class="contact-item">
                                <i class="fas fa-clock"></i>
                                <h4>Horário</h4>
                                <p>{{ $pageSettings['contact_hours'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if($pageSettings['show_tips'])
                    <div class="info-section">
                        <h2 class="section-title">
                            <i class="fas fa-lightbulb"></i>
                            Dicas importantes
                        </h2>
                        <div class="info-card">
                            <p>✓ Verifique sua caixa de entrada e spam para o email de confirmação</p>
                            <p>✓ Mantenha seu telefone disponível para contato</p>
                            <p>✓ Tenha em mãos seus documentos pessoais para agilizar o processo</p>
                        </div>
                    </div>
                @endif
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="{{ route('home') }}" class="btn-voltar">
                        <i class="fas fa-arrow-left"></i> Voltar ao Início
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 