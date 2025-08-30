<!DOCTYPE html>
<html lang="pt-br">
    <head>
    <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO Essentials -->
    <title>Eja Supletivo - Ensino Certo</title>
    <meta name="description" content="Conclua o Ensino Médio com o EJA Supletivo! Estude online ou presencial, com certificado válido em todo o Brasil. Rápido, fácil e reconhecido pelo MEC.">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Open Graph / Facebook -->
    <meta property="og:locale" content="pt_BR">
    <meta property="og:type" content="article">
    <meta property="og:title" content="Eja Supletivo - Ensino Certo">
    <meta property="og:description" content="Conclua o Ensino Médio com o EJA Supletivo! Estude online ou presencial, com certificado válido em todo o Brasil. Rápido, fácil e reconhecido pelo MEC.">
    <meta property="og:url" content="https://ensinocerto.com.br/eja-supletivo/">
    <meta property="og:site_name" content="Ensino Certo">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:label1" content="Est. tempo de leitura">
    <meta name="twitter:data1" content="20 minutos">

    <!-- Canonical -->
    <link rel="canonical" href="https://ensinocerto.com.br/eja-supletivo/">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <!-- Cleave.js para máscaras de input -->
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/addons/cleave-phone.br.js"></script>
    <!-- Intl-Tel-Input para seletor de país com bandeiras -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/styles.css')); ?>?v=<?php echo e(time()); ?>">
    <!-- Font Rubik -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tracking Scripts -->
    <?php echo $__env->make('components.tracking-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <!-- Google Tag Manager para Landing Page -->
    <?php if($landingSettings['gtm_enabled'] ?? false): ?>
        <?php if(!empty($landingSettings['gtm_id'])): ?>
            <!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','<?php echo e($landingSettings['gtm_id']); ?>');</script>
            <!-- End Google Tag Manager -->
        <?php endif; ?>
    <?php endif; ?>

    <style>
        /* Estilos para o banner de cookies */
        #cookie-consent {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.85);
            color: #fff;
            padding: 15px 20px;
            z-index: 9999;
            display: none;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
            font-family: 'Rubik', sans-serif;
        }
        
        .cookie-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            flex-wrap: wrap;
        }
        
        .cookie-text {
            flex: 1;
            margin-right: 20px;
            min-width: 280px;
        }
        
        .cookie-text p {
            margin: 0 0 10px 0;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .cookie-text a {
            color: #F7A633;
            text-decoration: underline;
        }
        
        .cookie-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .cookie-btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s ease;
            border: none;
        }
        
        .cookie-btn-accept {
            background-color: #F7A633;
            color: #000;
        }
        
        .cookie-btn-accept:hover {
            background-color: #e89b2a;
        }
        
        .cookie-btn-settings {
            background-color: transparent;
            border: 1px solid #fff;
            color: #fff;
        }
        
        .cookie-btn-settings:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        #cookie-settings-panel {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.9);
            color: #fff;
            padding: 20px;
            z-index: 10000;
            display: none;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.3);
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
        }
        
        .settings-header h3 {
            margin: 0;
            font-size: 18px;
        }
        
        .settings-close {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }
        
        .cookie-option {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .cookie-option:last-child {
            border-bottom: none;
        }
        
        .cookie-option-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .cookie-option-title {
            font-weight: 500;
            font-size: 16px;
        }
        
        .cookie-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .cookie-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #F7A633;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .cookie-option-desc {
            font-size: 13px;
            color: #ccc;
        }
        
        .settings-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .settings-save {
            background-color: #F7A633;
            color: #000;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            border: none;
        }
        
        @media (max-width: 768px) {
            .cookie-content {
                flex-direction: column;
            }
            
            .cookie-text {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .cookie-buttons {
                width: 100%;
                justify-content: center;
            }
            
            .cookie-btn {
                padding: 10px 16px;
            }
        }
        
        /* Estilos para mensagens de formulário */
        .form-message {
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-message.success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            border: 1px solid #198754;
        }
        
        .form-message.error {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        .form-message.warning {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        /* Estilos para campos com erro */
        .form-group input.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        .form-group input.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        

        
        /* Estilos para indicador de carregamento */
        .fa-spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
            <!-- Schema.org Structured Data para IA -->
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "EducationalOrganization",
        "name": "Ensino Certo",
        "description": "Instituição de ensino especializada em EJA Supletivo online com certificado reconhecido pelo MEC",
        "url": "https://ensinocerto.com.br",
        "logo": "{{ asset('assets/images/logotipo-dark.svg') }}",
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "BR"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+55-11-99295-0897",
            "contactType": "customer service",
            "availableLanguage": "Portuguese"
        },
        "offers": {
            "@type": "Offer",
            "name": "EJA Supletivo Ensino Médio",
            "description": "Curso online para conclusão do Ensino Médio com certificado MEC",
            "price": "{{ $countdownSettings['price_discount'] ?? 'R$ 197,00' }}",
            "priceCurrency": "BRL",
            "availability": "https://schema.org/InStock",
            "validFrom": "2025-01-01"
        }
    }
    </script>
    
    </head>
<body>
    <!-- Banner de Consentimento de Cookies LGPD -->
    <div id="cookie-consent">
        <div class="cookie-content">
            <div class="cookie-text">
                <p><strong>Utilizamos cookies para melhorar sua experiência</strong></p>
                <p>Este site utiliza cookies e tecnologias semelhantes para personalizar conteúdo, analisar o tráfego e melhorar sua experiência de navegação, conforme nossa <a href="<?php echo e(route('politica-privacidade')); ?>">Política de Privacidade</a>.</p>
                <p>Ao clicar em "Aceitar todos", você concorda com o uso de TODOS os cookies. Você pode gerenciar suas preferências clicando em "Configurações de cookies".</p>
            </div>
            <div class="cookie-buttons">
                <button id="cookie-settings-btn" class="cookie-btn cookie-btn-settings">Configurações de cookies</button>
                <button id="cookie-accept-btn" class="cookie-btn cookie-btn-accept">Aceitar todos</button>
            </div>
        </div>
    </div>

    <!-- Painel de Configurações de Cookies -->
    <div id="cookie-settings-panel">
        <div class="settings-header">
            <h3>Configurações de Cookies</h3>
            <button class="settings-close" id="settings-close-btn">&times;</button>
        </div>
        
        <div class="cookie-option">
            <div class="cookie-option-header">
                <span class="cookie-option-title">Cookies essenciais</span>
                <label class="cookie-switch">
                    <input type="checkbox" checked disabled>
                    <span class="slider"></span>
                </label>
            </div>
            <p class="cookie-option-desc">Cookies necessários para o funcionamento básico do site. O site não pode funcionar corretamente sem estes cookies.</p>
        </div>
        
        <div class="cookie-option">
            <div class="cookie-option-header">
                <span class="cookie-option-title">Cookies de desempenho</span>
                <label class="cookie-switch">
                    <input type="checkbox" id="performance-cookies">
                    <span class="slider"></span>
                </label>
            </div>
            <p class="cookie-option-desc">Cookies que coletam informações sobre como você usa nosso site, quais páginas você visitou e quaisquer erros que você possa ter encontrado.</p>
        </div>
        
        <div class="cookie-option">
            <div class="cookie-option-header">
                <span class="cookie-option-title">Cookies de funcionalidade</span>
                <label class="cookie-switch">
                    <input type="checkbox" id="functionality-cookies">
                    <span class="slider"></span>
                </label>
            </div>
            <p class="cookie-option-desc">Cookies que permitem que o site lembre as escolhas que você faz (como seu nome de usuário, idioma ou região) e fornecem recursos aprimorados.</p>
        </div>
        
        <div class="cookie-option">
            <div class="cookie-option-header">
                <span class="cookie-option-title">Cookies de marketing</span>
                <label class="cookie-switch">
                    <input type="checkbox" id="marketing-cookies">
                    <span class="slider"></span>
                </label>
            </div>
            <p class="cookie-option-desc">Cookies usados para rastrear visitantes em sites. A intenção é exibir anúncios relevantes e envolventes para o usuário individual.</p>
        </div>
        
        <div class="settings-footer">
            <button id="settings-save-btn" class="settings-save">Salvar preferências</button>
        </div>
    </div>

    <?php echo $__env->make('components.tracking-noscript', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <!-- Admin Access (apenas se logado) -->
    <?php if(session('admin_logged_in')): ?>
        <div class="admin-access-bar">
            <div class="container">
                <div class="admin-info">
                    
                    <div class="admin-actions">
                        <a href="<?php echo e(route('dashboard')); ?>" class="btn-admin">
                            <i class="fas fa-user-shield"></i>
                            Área Administrativa
                        </a>
                        <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn-admin btn-logout">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Seção 1: Header/Hero -->
    <header class="hero">
        <div class="hero-content container">
            <div class="hero-text">
                <div class="logo">
                    <img src="<?php echo e(asset('assets/images/anhangue-vip.svg')); ?>" alt="Logo Anhanguera VIP">
                    <img src="<?php echo e(asset('assets/images/logotipo-dark.svg')); ?>" alt="Logo Ensino Certo">
                </div>
                <div class="cta-buttons">
                    <button class="cta-button"><i class="fas fa-thumbs-up"></i> Conquiste seu emprego</button>
                    <button class="cta-button">Curso online atualizado 2025</button>
                </div>
                
                <h1>Construa sua carreira com o</h1>
                <div class="main-title">
                    <h2>SUPLETIVO <span class="highlight">EJA</span> ENSINO MÉDIO</h2>
                </div>
                
                <div class="benefits-list">
                    <div class="benefit">
                        <i class="fas fa-check-circle"></i>
                        <p>Mentores e especialistas de alto nível</p>
                    </div>
                    
                    <div class="benefit">
                        <i class="fas fa-check-circle"></i>
                        <p>Ideal para quem não tem tempo para estudar presencial</p>
                    </div>
                    
                    <div class="benefit">
                        <i class="fas fa-check-circle"></i>
                        <p>100% dos alunos que cursaram e terminaram o ensino médio, aumentaram a chance de conseguir emprego em <strong>227%</strong></p>
                    </div>
                </div>
            </div>
            
            <div class="hero-offer">
                <div class="offer-badge">
                    COMPRE 1, <br>LEVE 2
                </div>
                
                <div class="form-container">
                    <p class="form-header">Início imediato <span class="vagas-info">restam apenas 12 vagas</span></p>
                    <h3>Faça sua inscrição para o EJA</h3>
                    
                    <form method="POST" action="<?php echo e(route('inscricao.store')); ?>" id="formulario-contato">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="curso" value="<?php echo e($defaultCourse); ?>">
                        
                        <div class="form-group">
                            <input type="text" name="nome" placeholder="Nome" value="<?php echo e(old('nome')); ?>" required>
                            <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Email" value="<?php echo e(old('email')); ?>" required>
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group phone-input-container">
                            <input type="tel" id="phone" name="telefone" placeholder="Telefone" value="<?php echo e(old('telefone')); ?>" required>
                            <?php $__errorArgs = ['telefone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        

                        
                        <div class="form-group custom-select">
                            <select name="modalidade" required>
                                <option value="" disabled <?php echo e(old('modalidade') ? '' : 'selected'); ?>>Escolha a modalidade</option>
                                <?php $__currentLoopData = $availableModalities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e(old('modalidade', $defaultModality) == $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="select-arrow"></div>
                            <?php $__errorArgs = ['modalidade'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group checkbox">
                            <input type="checkbox" id="termos" name="termos" <?php echo e(old('termos') ? 'checked' : ''); ?> required>
                            <label for="termos">Eu li e aceito os termos e condições da Política de Privacidade, Contrato de Prestação de Serviço</label>
                            <?php $__errorArgs = ['termos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error-message"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <?php if($errors->has('error')): ?>
                            <div class="form-message error">
                                <?php echo e($errors->first('error')); ?>

                            </div>
                        <?php endif; ?>
                        
                        <?php if(session('success')): ?>
                            <div class="form-message success">
                                <?php echo e(session('success')); ?>

                            </div>
            <?php endif; ?>
                        
                        <button type="submit" class="btn btn-submit"></i>Quero me inscrever</button>
                    </form>
                </div>
            </div>
        </div>
        </header>

    <!-- Seção 2: Benefícios Principais -->
    <section id="beneficios-principais" class="section bg-light">
        <div class="container">
            <div class="benefits-grid">
                <div class="benefits-column">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <img src="<?php echo e(asset('assets/images/674325745_.png')); ?>" alt="Ícone Professor">
                        </div>
                        <div class="benefit-content">
                            <h3>Professores qualificados</h3>
                            <p>Professores e tutores atuando nas melhores empresas da área</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <img src="<?php echo e(asset('assets/images/674325743_.png')); ?>" alt="Ícone Relógio">
                        </div>
                        <div class="benefit-content">
                            <h3>Aprendizado flexível</h3>
                            <p>Estude apenas de 1h a 2hs por semana. Você pode aprender de qualquer lugar, em qualquer horário</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <img src="<?php echo e(asset('assets/images/674325744_.png')); ?>" alt="Ícone Certificado">
                        </div>
                        <div class="benefit-content">
                            <h3>Certificado garantido</h3>
                            <p>Na Ensino Certo você tem garantia de certificado reconhecido pelo MEC</p>
                        </div>
                    </div>
                </div>
                
                <div class="benefits-column">
                    <div class="benefit-highlight">
                        <h3>+ 2.000 vagas abertas</h3>
                        <p>Abertas por mês para quem busca empregos no LinkedIn Brasil*</p>
                    </div>
                    
                    <div class="benefit-highlight">
                        <h3>Certificado reconhecido pelo MEC</h3>
                        <p>Você poderá trabalhar com grandes empresas e fazer cursos profissionalizantes nas escolas mais renomadas do Brasil.</p>
                    </div>
                    
                    <div class="cta-highlight">
                        <a href="#oferta" class="btn btn-lg btn-cta gap-2"><i class="fab fa-whatsapp"></i>Quero terminar o ensino médio</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção 4: Diferenciais do Programa -->
    <section id="diferenciais" class="section bg-dark">
        <div class="container">
            <div class="section-header text-center">
                <h2>Por que escolher nosso EJA Supletivo?</h2>
                <p class="section-subtitle">Vantagens exclusivas que fazem a diferença na sua formação</p>
            </div>
            
            <div class="diferenciais-grid">
                <div class="diferencial-card">
                    <div class="diferencial-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Formação completa</h3>
                    <p>Seu certificado reconhecido pelo MEC em 180 dias</p>
                </div>
                
                <div class="diferencial-card">
                    <div class="diferencial-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3>Plataforma própria</h3>
                    <p>Plataforma inteligente que te ajuda no desenvolvimento</p>
                </div>
                
                <div class="diferencial-card">
                    <div class="diferencial-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Turbine seu currículo</h3>
                    <p>Prepare seu currículo para o mercado de trabalho</p>
                </div>
                
                <div class="diferencial-card">
                    <div class="diferencial-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3>Cursos profissionalizantes</h3>
                    <p>Domine habilidades para se diferenciar no mercado de trabalho</p>
                </div>
            </div>
            
            <div class="cta-section text-center">
                <p class="cta-text">Não perca mais tempo! Comece sua transformação hoje mesmo.</p>
                <a href="#formulario-contato" class="btn btn-lg btn-primary-cta">
                    <i class="fab fa-whatsapp me-2"></i>
                    Quero começar agora
                </a>
            </div>
        </div>
    </section>

    <!-- Seção 5: Estude de Casa -->
    <section id="estude-casa" class="section bg-light">
        <div class="container">
            <div class="estude-casa-content">
                <div class="estude-casa-text">
                    <h2>Estude de casa!</h2>
                    <p class="lead">Estude em nossa plataforma e se prepare-se para o dia da prova saindo na frente de 87% dos brasileiros.</p>
                    <p>Você estuda sem sair de casa ou de qualquer lugar, com seu celular, table ou computador.</p>
                    
                    <div class="prazo-info">
                        <h3>6 meses</h3>
                        <p>Após a conclusão do curso é o prazo máximo para conquistar seu certificado com a ajuda do Programa de Ensino Certo.</p>
                    </div>
                </div>
                
                <div class="estude-casa-images">
                    <div class="image-container image-1">
                        <img src="<?php echo e(asset('assets/images/depositphotos_196018166-stock-photo-serious-student-writing-homework-table.webp')); ?>" alt="Estudante estudando em casa" class="placeholder-img">
                    </div>
                    <div class="image-container image-2">
                        <img src="<?php echo e(asset('assets/images/istockphoto-1443305526-612x612-1.jpg')); ?>" alt="Estudante no computador" class="placeholder-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção 6: Tire suas dúvidas
    <section id="tire-duvidas" class="section bg-dark">
        <div class="container">
            <div class="duvidas-content">
                <div class="duvidas-form">
                    <h2>Tire suas dúvidas</h2>
                    <p class="subtitle">Consulte nossa equipe para saber mais sobre o nosso curso</p>
                    
                    <form id="formulario-duvidas" method="POST" action="<?php echo e(route('inscricao.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="curso" value="<?php echo e($defaultCourse); ?>">
                        
                        <div class="form-group">
                            <input type="text" name="nome" placeholder="Nome" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Email" required>
                        </div>
                        
                        <div class="form-group phone-input-container">
                            <input type="tel" id="phone-duvidas" name="telefone" placeholder="+55 (11) 99999-9999" required>
                        </div>
                        

                        
                        <div class="form-group custom-select">
                            <select name="modalidade" required>
                                <option value="" disabled selected>Escolha a modalidade</option>
                                <?php $__currentLoopData = $availableModalities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e($defaultModality == $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="select-arrow"></div>
                        </div>
                        
                        <div class="form-group checkbox">
                            <input type="checkbox" id="termos-duvidas" name="termos" required>
                            <label for="termos-duvidas">Eu li e aceito os termos e condições da Política de Privacidade, Contrato de Prestação de Serviço</label>
                        </div>
                        
                        <button type="submit" class="btn btn-submit-orange">Solicite uma consulta</button>
                    </form>
                </div>
                
                <div class="duvidas-stats">
                    <div class="stat-item">
                        <div class="stat-number">4,9/5</div>
                        <div class="stat-label">Nota média das tarefas</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number">70%</div>
                        <div class="stat-label">Prático</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number">145 mil</div>
                        <div class="stat-label">Alunos matriculados em nossos cursos</div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <!-- Seção 6.5: Oferta Especial -->
    <section id="oferta-especial" class="section bg-dark">
        <div class="container">
            <div class="oferta-content">
                <div class="oferta-text">
                    <h2 class="oferta-title">
                        Mude sua vida agora mesmo e
                        <span class="highlight-orange">TERMINE O ENSINO MÉDIO!</span>
                    </h2>
                    
                    <?php if($countdownSettings['enabled']): ?>
                    <div class="promocao-banner">                        
                        <div class="countdown-container">
                            <div class="promocao-header-container">
                                <span><strong><?php echo e($countdownSettings['text']); ?> <?php echo e($countdownSettings['end_date_formatted']); ?></strong></span>
                                <div class="promocao-desconto"><?php echo e($countdownSettings['discount_text']); ?></div>
                            </div>
                            <div class="countdown-icon">
                            🔥
                            </div>
                            <div class="countdown-timer">
                                <div class="timer-group">
                                    <span class="timer-number" id="days">00</span>
                                    <span class="timer-label">Dias</span>
                                </div>
                                <div class="timer-group">
                                    <span class="timer-number" id="hours">00</span>
                                    <span class="timer-label">Horas</span>
                                </div>
                                <div class="timer-group">
                                    <span class="timer-number" id="minutes">00</span>
                                    <span class="timer-label">Min</span>
                                </div>
                                <div class="timer-group">
                                    <span class="timer-number" id="seconds">00</span>
                                    <span class="timer-label">Seg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="precos-container">
                        <div class="preco-desconto">
                            <div class="preco-label">Preço com desconto</div>
                            <div class="preco-sublabel">até <?php echo e($countdownSettings['price_installments_discount']); ?> de</div>
                            <div class="preco-valor"><?php echo e($countdownSettings['price_discount']); ?></div>
                        </div>
                        
                        <div class="preco-original">
                            <div class="preco-label">Preço original</div>
                            <div class="preco-sublabel">até <?php echo e($countdownSettings['price_installments_original']); ?> de</div>
                            <div class="preco-valor"><?php echo e($countdownSettings['price_original']); ?></div>
                        </div>
                    </div>
                    
                    <div class="valor-total-info">
                        <div class="valor-pix-container">
                            <div class="pix-label">Valor total no PIX:</div>
                            <div class="pix-valor"><?php echo e($countdownSettings['pix_price']); ?></div>
                        </div>
                        <div class="garantia-box">
                            <span class="garantia-text">Garantimos seu dinheiro de volta por até 7 dias</span>
                        </div>
                    </div>
                    <div class="inscricao-info">
                        <small>*Inscreva-se para receber mais informações sobre as opções de pagamento</small>
                    </div>
                </div>
                
                <div class="oferta-form">
                    <div class="form-container-oferta">
                        <h3>Faça sua inscrição para o EJA</h3>
                        
                        <form method="POST" action="<?php echo e(route('inscricao.store')); ?>" id="formulario-oferta">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="curso" value="<?php echo e($defaultCourse); ?>">
                            
                            <div class="form-group">
                                <input type="text" name="nome" placeholder="Name" value="<?php echo e(old('nome')); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <input type="email" name="email" placeholder="Email" value="<?php echo e(old('email')); ?>" required>
                            </div>
                            
                            <div class="form-group phone-input-container">
                                <input type="tel" id="phone-oferta" name="telefone" placeholder="(11) 99999-9999" value="<?php echo e(old('telefone')); ?>" required>
                            </div>
                            
                            <div class="form-group custom-select">
                                <select name="modalidade" required>
                                    <option value="" disabled <?php echo e(old('modalidade') ? '' : 'selected'); ?>>Escolha a modalidade</option>
                                    <?php $__currentLoopData = $availableModalities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>" <?php echo e(old('modalidade', $defaultModality) == $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="select-arrow"></div>
                                <?php $__errorArgs = ['modalidade'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="error-message"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="form-group checkbox">
                                <input type="checkbox" id="termos-oferta" name="termos" <?php echo e(old('termos') ? 'checked' : ''); ?> required>
                                <label for="termos-oferta">Eu li e aceito os termos e condições da Política de Privacidade, Contrato de Prestação de Serviço</label>
                            </div>
                            
                            <button type="submit" class="btn btn-matricule"><i class="fab fa-whatsapp me-2"></i>MATRICULE-SE</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção 7: Perguntas Frequentes -->
    <section id="perguntas-frequentes" class="section bg-light">
        <div class="container">
            <div class="section-header text-center">
                <h2>Perguntas Frequentes</h2>
                <p class="section-subtitle">Tire suas dúvidas sobre o curso EJA Supletivo</p>
            </div>
            
            <div class="faq-content">
                <div class="faq-accordion">
                    <div class="faq-item">
                        <div class="faq-question">
                            <i class="fas fa-user-graduate"></i>
                            <span>Qual a idade mínima para estudar na Ensino Certo?</span>
                            <i class="fas fa-chevron-down faq-arrow"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Para o Ensino Fundamental, a idade mínima é de 15 anos completos. Para o Ensino Médio, você deve ter no mínimo 18 anos completos. Essas são exigências do MEC para certificação de EJA.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <i class="fas fa-certificate"></i>
                            <span>O curso à distância é reconhecido oficialmente?</span>
                            <i class="fas fa-chevron-down faq-arrow"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Sim! Nosso curso é totalmente reconhecido pelo MEC. O certificado de conclusão tem validade nacional e é aceito para ingresso no ensino superior, concursos públicos e mercado de trabalho.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <i class="fas fa-award"></i>
                            <span>O certificado é reconhecido pelo MEC?</span>
                            <i class="fas fa-chevron-down faq-arrow"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Sim, totalmente! Nosso certificado é expedido por instituição credenciada pelo MEC e tem validade em todo território nacional, sendo aceito em universidades, concursos e empresas.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <i class="fas fa-credit-card"></i>
                            <span>Eu tenho restrições em meu nome (SCPC / SERASA), posso fazer no boleto bancário mesmo assim?</span>
                            <i class="fas fa-chevron-down faq-arrow"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Claro! Aceitamos pagamento via boleto bancário independente de restrições no seu nome. Também oferecemos outras opções de pagamento para facilitar sua matrícula.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <i class="fas fa-home"></i>
                            <span>Posso estudar em casa, sem frequentar as aulas?</span>
                            <i class="fas fa-chevron-down faq-arrow"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Sim! Nosso curso é 100% online e você pode estudar no conforto da sua casa, no seu ritmo e horário. Você só precisa comparecer para realizar as avaliações presenciais obrigatórias.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <i class="fas fa-calendar-check"></i>
                            <span>Como utilizo o teste 7 dias?</span>
                            <i class="fas fa-chevron-down faq-arrow"></i>
                        </div>
                        <div class="faq-answer">
                            <div class="faq-answer-content">
                                <p>Após sua inscrição, você terá acesso completo à plataforma por 7 dias. Poderá conhecer o material, assistir às aulas e avaliar nossa metodologia. Se não ficar satisfeito, devolvemos 100% do valor pago.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acesso Vitalício -->
                <div class="acesso-vitalicio">
                    <div class="acesso-card">
                        <div class="acesso-icon">
                            <i class="fas fa-infinity"></i>
                        </div>
                        <div class="acesso-content">
                            <h3>Acesso vitalício</h3>
                            <p>Acesso ao curso por tempo ilimitado, relembre o conteúdo sempre que desejar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WhatsApp Floating Button -->
    <?php if($whatsappSettings['whatsapp_enabled'] ?? true): ?>
        <div id="whatsapp-float" class="whatsapp-float <?php echo e($whatsappSettings['whatsapp_button_position'] ?? 'bottom-right'); ?>" 
             style="background-color: <?php echo e($whatsappSettings['whatsapp_button_color'] ?? '#25d366'); ?>">
            <a href="https://wa.me/<?php echo e($whatsappSettings['whatsapp_number'] ?? '5511999999999'); ?>?text=<?php echo e(urlencode($whatsappSettings['whatsapp_message'] ?? 'Olá! Tenho interesse no curso EJA. Podem me ajudar?')); ?>" 
               target="_blank" 
               rel="noopener noreferrer"
               aria-label="Conversar no WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
        </div>
    <?php endif; ?>

    <!-- Incluir o componente footer -->
    <?php echo $__env->make('components.footer', ['landingSettings' => $landingSettings], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializar Intl-Tel-Input
        var input = document.querySelector("#phone");
        var iti = window.intlTelInput(input, {
            initialCountry: "br",
            preferredCountries: ["br"],
            separateDialCode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });
        
        // Aplicar máscara ao telefone
        var cleave = new Cleave('#phone', {
            phone: true,
            phoneRegionCode: 'BR'
        });
        
        // Função para verificar se email ou telefone já existem
        function checkExistingContact(email, phone, formId) {
            return new Promise((resolve, reject) => {
                // Mostrar indicador de carregamento
                const form = document.getElementById(formId);
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
                submitBtn.disabled = true;
                
                fetch('/api/check-existing-contact', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email, phone })
                })
                .then(response => response.json())
                .then(data => {
                    // Restaurar botão
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    if (data.exists) {
                        // Mostrar mensagem personalizada
                        let messageDiv = form.querySelector('.form-message');
                        
                        if (!messageDiv) {
                            messageDiv = document.createElement('div');
                            messageDiv.className = 'form-message warning';
                            form.insertBefore(messageDiv, form.querySelector('button[type="submit"]'));
                        }
                        
                        // Usar mensagem simplificada
                        messageDiv.innerHTML = data.message;
                        messageDiv.style.display = 'block';
                        

                        
                        // Tracking do evento
                        if (typeof gtag !== 'undefined') {
                            gtag('event', 'duplicate_contact', {
                                'event_category': 'formulario',
                                'event_label': data.field
                            });
                        }
                        
                        resolve(true); // Contato existe
                    } else {
                        // Remover mensagem se existir
                        const messageDiv = form.querySelector('.form-message.warning');
                        if (messageDiv) {
                            messageDiv.style.display = 'none';
                        }
                        resolve(false); // Contato não existe
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar contato:', error);
                    
                    // Restaurar botão
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Mostrar mensagem de erro
                    let messageDiv = form.querySelector('.form-message');
                    if (!messageDiv) {
                        messageDiv = document.createElement('div');
                        messageDiv.className = 'form-message error';
                        form.insertBefore(messageDiv, form.querySelector('button[type="submit"]'));
                    }
                    messageDiv.textContent = 'Erro ao verificar dados. Tente novamente.';
                    messageDiv.style.display = 'block';
                    
                    resolve(false); // Em caso de erro, permitir o envio
                });
            });
        }
        
        // Validação do formulário principal
        document.getElementById('formulario-contato').addEventListener('submit', async function(e) {
            e.preventDefault();
            var phone = document.getElementById('phone');
            var fullNumber = iti.getNumber();
            var email = this.querySelector('input[name="email"]').value;
            
            if (!iti.isValidNumber()) {
                phone.classList.add('is-invalid');
                
                // Criar mensagem de erro
                var errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = 'Por favor, insira um número de telefone válido.';
                
                // Remover mensagens de erro anteriores
                var existingErrors = phone.parentNode.querySelectorAll('.error-message');
                existingErrors.forEach(function(el) {
                    el.remove();
                });
                
                // Adicionar nova mensagem de erro
                phone.parentNode.appendChild(errorDiv);
                return;
            } else {
                // Atualizar o valor do campo com o número completo
                phone.value = fullNumber;
                phone.classList.remove('is-invalid');
                
                // Remover mensagens de erro anteriores
                var existingErrors = phone.parentNode.querySelectorAll('.error-message');
                existingErrors.forEach(function(el) {
                    el.remove();
                });
            }
            
            // Verificar se o contato já existe
            const exists = await checkExistingContact(email, fullNumber, 'formulario-contato');
            
            // Se não existir, enviar o formulário
            if (!exists) {
                this.submit();
            }
        });
        
        // Tracking de formulários
        document.getElementById('formulario-contato').addEventListener('submit', function() {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'form_submit', {
                    'event_category': 'formulario',
                    'event_label': 'inscricao_eja'
                });
            }
        });
        
        // Tracking de campos individuais
        document.querySelectorAll('#formulario-contato input, #formulario-contato select').forEach(function(el) {
            el.addEventListener('change', function() {
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'field_interaction', {
                        'event_category': 'formulario',
                        'event_label': el.name
                    });
                }
            });
        });
        
        // Validação em tempo real para email
        function initEmailValidation() {
            const emailInputs = document.querySelectorAll('input[name="email"]');
            let emailValidationTimeout;
            
            emailInputs.forEach(function(emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value.trim();
                    const form = this.closest('form');
                    
                    if (email && isValidEmail(email)) {
                        // Aguardar 500ms após o usuário parar de digitar
                        clearTimeout(emailValidationTimeout);
                        emailValidationTimeout = setTimeout(() => {
                            checkEmailAvailability(email, form);
                        }, 500);
                    }
                });
                
                emailInput.addEventListener('input', function() {
                    // Limpar mensagens de validação ao digitar
                    const form = this.closest('form');
                    const messageDiv = form.querySelector('.form-message.warning');
                    if (messageDiv) {
                        messageDiv.style.display = 'none';
                    }
                    
                    // Limpar classe de erro
                    this.classList.remove('is-invalid');
                });
            });
            
            // Adicionar listeners para outros campos
            const allInputs = document.querySelectorAll('input, select');
            allInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    // Limpar erros de validação ao digitar
                    const form = this.closest('form');
                    if (form) {
                        clearValidationErrors(form);
                    }
                });
                
                input.addEventListener('change', function() {
                    // Limpar erros de validação ao mudar
                    const form = this.closest('form');
                    if (form) {
                        clearValidationErrors(form);
                    }
                });
            });
        }
        
        // Verificar disponibilidade do email
        function checkEmailAvailability(email, form) {
            // Verificar se já existe uma inscrição com este email
            fetch('/api/check-existing-contact', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: email, phone: '' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists && data.field === 'email') {
                    // Mostrar aviso de email duplicado
                    let messageDiv = form.querySelector('.form-message');
                    
                    if (!messageDiv) {
                        messageDiv = document.createElement('div');
                        messageDiv.className = 'form-message warning';
                        form.insertBefore(messageDiv, form.querySelector('button[type="submit"]'));
                    }
                    
                    messageDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${data.message}`;
                    messageDiv.style.display = 'block';
                    
                    // Adicionar classe de erro ao campo
                    const emailField = form.querySelector('input[name="email"]');
                    emailField.classList.add('is-invalid');
                } else {
                    // Remover avisos e classes de erro
                    const messageDiv = form.querySelector('.form-message.warning');
                    if (messageDiv) {
                        messageDiv.style.display = 'none';
                    }
                    
                    const emailField = form.querySelector('input[name="email"]');
                    emailField.classList.remove('is-invalid');
                }
            })
            .catch(error => {
                console.error('Erro ao verificar email:', error);
            });
        }
        
        // Função para validar formato de email
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Função para lidar com erros de validação do servidor
        function handleServerValidationErrors(form, errors) {
            // Limpar mensagens anteriores
            const existingMessages = form.querySelectorAll('.form-message');
            existingMessages.forEach(msg => msg.remove());
            
            // Criar mensagem de erro
            const messageDiv = document.createElement('div');
            messageDiv.className = 'form-message error';
            
            let errorMessage = '<strong>Erro de validação:</strong><br>';
            
            if (errors.email) {
                errorMessage += `• ${errors.email}<br>`;
                const emailField = form.querySelector('input[name="email"]');
                if (emailField) {
                    emailField.classList.add('is-invalid');
                }
            }
            
            if (errors.telefone) {
                errorMessage += `• ${errors.telefone}<br>`;
                const phoneField = form.querySelector('input[name="telefone"]');
                if (phoneField) {
                    phoneField.classList.add('is-invalid');
                }
            }
            
            if (errors.nome) {
                errorMessage += `• ${errors.nome}<br>`;
            }
            
            if (errors.curso) {
                errorMessage += `• ${errors.curso}<br>`;
            }
            
            if (errors.modalidade) {
                errorMessage += `• ${errors.modalidade}<br>`;
            }
            
            if (errors.termos) {
                errorMessage += `• ${errors.termos}<br>`;
            }
            
            messageDiv.innerHTML = errorMessage;
            form.insertBefore(messageDiv, form.querySelector('button[type="submit"]'));
        }
        
        // Função para limpar erros de validação
        function clearValidationErrors(form) {
            const existingMessages = form.querySelectorAll('.form-message');
            existingMessages.forEach(msg => msg.remove());
            
            const invalidFields = form.querySelectorAll('.is-invalid');
            invalidFields.forEach(field => field.classList.remove('is-invalid'));
        }
        
        // Tracking de campos individuais
        document.querySelectorAll('#formulario-contato input, #formulario-contato select').forEach(function(el) {
            el.addEventListener('blur', function() {
                if (el.value && typeof gtag !== 'undefined') {
                    gtag('event', 'field_filled', {
                        'event_category': 'formulario',
                        'event_label': el.name
                    });
                }
            });
        });
        
        // Tracking de cliques em CTAs
        document.querySelectorAll('.cta-button, .btn-submit').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'cta_click', {
                        'event_category': 'engagement',
                        'event_label': btn.textContent.trim()
                    });
                }
            });
        });
        
        // Tracking de scroll (engajamento)
        let scrollDepthMarks = [25, 50, 75, 100];
        let scrollDepthReached = [];
        
        window.addEventListener('scroll', function() {
            let scrollPercent = Math.round((window.scrollY / (document.body.offsetHeight - window.innerHeight)) * 100);
            
            scrollDepthMarks.forEach(function(mark) {
                if (scrollPercent >= mark && !scrollDepthReached.includes(mark)) {
                    scrollDepthReached.push(mark);
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'scroll_depth', {
                            'event_category': 'engagement',
                            'event_label': mark + '%'
                        });
                    }
                }
            });
        });
        
        // Tracking de tempo na página
        let timeMarks = [30, 60, 120, 300]; // segundos
        let timeReached = [];
        let startTime = new Date().getTime();
        
        setInterval(function() {
            let timeSpent = Math.floor((new Date().getTime() - startTime) / 1000);
            
            timeMarks.forEach(function(mark) {
                if (timeSpent >= mark && !timeReached.includes(mark)) {
                    timeReached.push(mark);
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'time_on_page', {
                            'event_category': 'engagement',
                            'event_label': mark + ' segundos'
                        });
                    }
                }
            });
        }, 1000);
        
        // FAQ Accordion Functionality - MELHORADO
        function initFAQAccordion() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            if (faqItems.length === 0) {
                return;
            }
            
            faqItems.forEach((item, index) => {
                const question = item.querySelector('.faq-question');
                const answer = item.querySelector('.faq-answer');
                
                if (!question || !answer) {
                    return;
                }
                
                // Remover listeners anteriores para evitar duplicação
                const newQuestion = question.cloneNode(true);
                question.parentNode.replaceChild(newQuestion, question);
                
                newQuestion.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const isActive = item.classList.contains('active');
                    
                    // Fechar todos os outros itens
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                            const otherAnswer = otherItem.querySelector('.faq-answer');
                            if (otherAnswer) {
                                otherAnswer.classList.remove('show');
                            }
                        }
                    });
                    
                    // Toggle do item atual
                    if (isActive) {
                        item.classList.remove('active');
                        answer.classList.remove('show');
                    } else {
                        item.classList.add('active');
                        answer.classList.add('show');
                    }
                    
                    // Tracking
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'faq_click', {
                            'event_category': 'engagement',
                            'event_label': `faq_${index + 1}`
                        });
                    }
                });
                
                // Adicionar cursor pointer
                newQuestion.style.cursor = 'pointer';
            });
        }
        
        // Chamar a inicialização quando DOM estiver pronto
        document.addEventListener('DOMContentLoaded', function() {
            // Aguardar um pouco para garantir que tudo carregou
            setTimeout(initFAQAccordion, 100);
        });
        
        // Contador Regressivo
        function initCountdown() {
            <?php if($countdownSettings['enabled']): ?>
            // Data de término baseada nas configurações
            const endDate = <?php echo e($countdownSettings['end_timestamp']); ?>;
            
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = endDate - now;
                
                if (distance > 0) {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    document.getElementById('days').textContent = days.toString().padStart(2, '0');
                    document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                    document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                    document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
                } else {
                    // Oferta expirada
                    document.getElementById('days').textContent = '00';
                    document.getElementById('hours').textContent = '00';
                    document.getElementById('minutes').textContent = '00';
                    document.getElementById('seconds').textContent = '00';
                    
                    // Recarregar a página após 5 segundos para verificar renovação automática
                    setTimeout(() => {
                        window.location.reload();
                    }, 5000);
                }
            }
            
            // Atualizar imediatamente e depois a cada segundo
            updateCountdown();
            setInterval(updateCountdown, 1000);
            <?php else: ?>
            // Countdown desabilitado - ocultar elementos
            const countdownElements = document.querySelectorAll('#days, #hours, #minutes, #seconds');
            countdownElements.forEach(el => {
                if (el) el.textContent = '00';
            });
            <?php endif; ?>
        }
        
        // Inicializar telefone para formulário da oferta
        function initOfertaForm() {
            const phoneInput = document.querySelector("#phone-oferta");
            if (phoneInput) {
                const iti = window.intlTelInput(phoneInput, {
                    initialCountry: "br",
                    preferredCountries: ["br"],
                    separateDialCode: true,
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                });
                
                const cleave = new Cleave('#phone-oferta', {
                    phone: true,
                    phoneRegionCode: 'BR'
                });
                
                // Validação do formulário da oferta
                document.getElementById('formulario-oferta').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const phone = document.getElementById('phone-oferta');
                    const fullNumber = iti.getNumber();
                    const email = this.querySelector('input[name="email"]').value;
                    
                    if (!iti.isValidNumber()) {
                        phone.classList.add('is-invalid');
                        
                        // Criar mensagem de erro
                        let errorDiv = phone.parentNode.querySelector('.error-message');
                        if (!errorDiv) {
                            errorDiv = document.createElement('div');
                            errorDiv.className = 'error-message';
                            phone.parentNode.appendChild(errorDiv);
                        }
                        errorDiv.textContent = 'Por favor, insira um número de telefone válido.';
                        return;
                    } else {
                        // Atualizar o valor do campo com o número completo
                        phone.value = fullNumber;
                        phone.classList.remove('is-invalid');
                        
                        // Remover mensagem de erro se existir
                        const errorDiv = phone.parentNode.querySelector('.error-message');
                        if (errorDiv) {
                            errorDiv.remove();
                        }
                    }
                    
                    // Verificar se o contato já existe
                    const exists = await checkExistingContact(email, fullNumber, 'formulario-oferta');
                    
                    // Se não existir, enviar o formulário
                    if (!exists) {
                        this.submit();
                    }
                });
                
                // Tracking do formulário da oferta
                document.getElementById('formulario-oferta').addEventListener('submit', function() {
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'form_submit', {
                            'event_category': 'formulario',
                            'event_label': 'oferta_eja'
                        });
                    }
                });
            }
        }
        
        // Inicializar dúvidas form também se existir
        function initDuvidasForm() {
            const phoneInputDuvidas = document.querySelector("#phone-duvidas");
            if (phoneInputDuvidas) {
                const itiDuvidas = window.intlTelInput(phoneInputDuvidas, {
                    initialCountry: "br",
                    preferredCountries: ["br"],
                    separateDialCode: true,
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                });
                
                const cleaveDuvidas = new Cleave('#phone-duvidas', {
                    phone: true,
                    phoneRegionCode: 'BR'
                });
                
                // Validação do formulário de dúvidas
                document.getElementById('formulario-duvidas')?.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const phone = document.getElementById('phone-duvidas');
                    const fullNumber = itiDuvidas.getNumber();
                    const email = this.querySelector('input[name="email"]').value;
                    
                    if (!itiDuvidas.isValidNumber()) {
                        phone.classList.add('is-invalid');
                        
                        // Criar mensagem de erro
                        let errorDiv = phone.parentNode.querySelector('.error-message');
                        if (!errorDiv) {
                            errorDiv = document.createElement('div');
                            errorDiv.className = 'error-message';
                            phone.parentNode.appendChild(errorDiv);
                        }
                        errorDiv.textContent = 'Por favor, insira um número de telefone válido.';
                        return;
                    } else {
                        // Atualizar o valor do campo com o número completo
                        phone.value = fullNumber;
                        phone.classList.remove('is-invalid');
                        
                        // Remover mensagem de erro se existir
                        const errorDiv = phone.parentNode.querySelector('.error-message');
                        if (errorDiv) {
                            errorDiv.remove();
                        }
                    }
                    
                    // Verificar se o contato já existe
                    const exists = await checkExistingContact(email, fullNumber, 'formulario-duvidas');
                    
                    // Se não existir, enviar o formulário
                    if (!exists) {
                        this.submit();
                    }
                });
            }
        }
        
        // Inicializar tudo quando o DOM carregar
        document.addEventListener('DOMContentLoaded', function() {
            initCountdown();
            
            // Usar timeout para garantir que os elementos estão prontos
            setTimeout(function() {
                initOfertaForm();
                initDuvidasForm();
                initEmailValidation();
            }, 100);
            
            // Inicializar o banner de cookies
            initCookieConsent();
        });
        
        // Gerenciamento de Cookies LGPD
        function initCookieConsent() {
            const cookieConsent = document.getElementById('cookie-consent');
            const cookieSettingsPanel = document.getElementById('cookie-settings-panel');
            const cookieAcceptBtn = document.getElementById('cookie-accept-btn');
            const cookieSettingsBtn = document.getElementById('cookie-settings-btn');
            const settingsCloseBtn = document.getElementById('settings-close-btn');
            const settingsSaveBtn = document.getElementById('settings-save-btn');
            
            const performanceCookies = document.getElementById('performance-cookies');
            const functionalityCookies = document.getElementById('functionality-cookies');
            const marketingCookies = document.getElementById('marketing-cookies');
            
            // Verificar se o usuário já deu consentimento
            const consentGiven = getCookie('cookie_consent');
            
            if (!consentGiven) {
                // Mostrar o banner após 1 segundo
                setTimeout(() => {
                    cookieConsent.style.display = 'block';
                }, 1000);
            } else {
                // Aplicar as preferências salvas
                const cookieSettings = JSON.parse(consentGiven);
                applyStoredCookieSettings(cookieSettings);
            }
            
            // Aceitar todos os cookies
            cookieAcceptBtn.addEventListener('click', function() {
                const settings = {
                    essential: true,
                    performance: true,
                    functionality: true,
                    marketing: true,
                    timestamp: new Date().getTime()
                };
                
                setCookie('cookie_consent', JSON.stringify(settings), 365);
                applyStoredCookieSettings(settings);
                cookieConsent.style.display = 'none';
                
                // Registrar evento de aceitação
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'cookie_consent', {
                        'event_category': 'consent',
                        'event_label': 'accept_all'
                    });
                }
            });
            
            // Abrir painel de configurações
            cookieSettingsBtn.addEventListener('click', function() {
                cookieSettingsPanel.style.display = 'block';
                cookieConsent.style.display = 'none';
                
                // Carregar configurações salvas, se existirem
                const savedSettings = getCookie('cookie_consent');
                if (savedSettings) {
                    const settings = JSON.parse(savedSettings);
                    performanceCookies.checked = settings.performance;
                    functionalityCookies.checked = settings.functionality;
                    marketingCookies.checked = settings.marketing;
                }
            });
            
            // Fechar painel de configurações
            settingsCloseBtn.addEventListener('click', function() {
                cookieSettingsPanel.style.display = 'none';
                cookieConsent.style.display = 'block';
            });
            
            // Salvar preferências
            settingsSaveBtn.addEventListener('click', function() {
                const settings = {
                    essential: true, // Sempre obrigatório
                    performance: performanceCookies.checked,
                    functionality: functionalityCookies.checked,
                    marketing: marketingCookies.checked,
                    timestamp: new Date().getTime()
                };
                
                setCookie('cookie_consent', JSON.stringify(settings), 365);
                applyStoredCookieSettings(settings);
                cookieSettingsPanel.style.display = 'none';
                cookieConsent.style.display = 'none';
                
                // Registrar evento de configurações personalizadas
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'cookie_consent', {
                        'event_category': 'consent',
                        'event_label': 'custom_settings',
                        'performance': settings.performance,
                        'functionality': settings.functionality,
                        'marketing': settings.marketing
                    });
                }
            });
        }
        
        // Aplicar configurações de cookies
        function applyStoredCookieSettings(settings) {
            // Cookies essenciais são sempre permitidos
            
            // Aplicar configurações para cookies de desempenho
            if (!settings.performance) {
                disablePerformanceCookies();
            }
            
            // Aplicar configurações para cookies de funcionalidade
            if (!settings.functionality) {
                disableFunctionalityCookies();
            }
            
            // Aplicar configurações para cookies de marketing
            if (!settings.marketing) {
                disableMarketingCookies();
            }
        }
        
        // Desabilitar cookies de desempenho
        function disablePerformanceCookies() {
            // Aqui você pode adicionar código para desativar cookies de análise
            // Por exemplo, desativar o Google Analytics
            if (typeof window['ga-disable-UA-XXXXX-Y'] !== 'undefined') {
                window['ga-disable-UA-XXXXX-Y'] = true;
            }
        }
        
        // Desabilitar cookies de funcionalidade
        function disableFunctionalityCookies() {
            // Implementar lógica para desativar cookies de funcionalidade
            // Isso dependerá dos serviços específicos que o site usa
        }
        
        // Desabilitar cookies de marketing
        function disableMarketingCookies() {
            // Implementar lógica para desativar cookies de marketing
            // Por exemplo, desativar Facebook Pixel
            if (typeof fbq !== 'undefined') {
                fbq('consent', 'revoke');
            }
        }
        
        // Função auxiliar para definir cookies
        function setCookie(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax; Secure";
        }
        
        // Função auxiliar para obter cookies
        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
        
        // Função auxiliar para apagar cookies
        function eraseCookie(name) {
            document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }
    </script>
    
    <!-- Chat de Suporte ao Cliente -->
    <?php echo $__env->make('components.chat-widget', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <!-- Google Tag Manager (noscript) para Landing Page -->
    <?php if($landingSettings['gtm_enabled'] ?? false): ?>
        <?php if(!empty($landingSettings['gtm_id'])): ?>
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo e($landingSettings['gtm_id']); ?>"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
        <?php endif; ?>
    <?php endif; ?>
    
    </body>
</html>
<?php /**PATH C:\Users\Douglas\Documents\Projetos\ensinocerto\resources\views/welcome.blade.php ENDPATH**/ ?>