<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - EJA Supletivo Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4a4a9c;
            --primary-hover: #3a3a7c;
            --border-radius: 12px;
        }
        
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            border: none;
        }
        

        
        .login-body {
            padding: 3rem 2.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.75rem;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 74, 156, 0.15);
        }
        
        .input-group-text {
            background:transparent;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 8px 0 0 8px;
            color: #6c757d;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: var(--primary-color);
        }
        .btn-login {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 14px 20px;
            font-weight: 600;
            color: white;
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s ease;
            min-height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-login:hover:not(:disabled) {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 74, 156, 0.3);
            color: white;
        }
        
        .btn-login:focus {
            box-shadow: 0 0 0 0.2rem rgba(74, 74, 156, 0.25);
        }
        
        .btn-login:disabled {
            background: var(--primary-color);
            opacity: 0.8;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            font-size: 0.9rem;
        }
        
        .alert-success {
            background-color: #d1edff;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .back-link:hover {
            color: var(--primary-hover);
            text-decoration: none;
        }
        
        .divider {
            height: 1px;
            background: #e9ecef;
            margin: 1.5rem 0;
        }
        
                 /* Responsive */
         @media (max-width: 576px) {
             .login-container {
                 padding: 15px;
             }
             
             .login-body {
                 padding: 2.5rem 1.5rem;
             }
         }
        
        .login-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 60px;
            padding: 10px;
        }
        
        .login-custom-logo {
            height: 60px;
            width: auto;
            max-width: 200px;
            object-fit: contain;
            object-position: center;
        }
        
        /* Controle de cor para SVGs */
        .login-custom-logo[src$=".svg"] {
            /* Deixa o SVG preto na página de login clara */
            filter: brightness(50%);
            -webkit-filter: brightness(50%);
            color: white;
        }

        /* Para mudar para outra cor, use estas propriedades:
        
        Azul:
        filter: invert(42%) sepia(95%) saturate(1000%) hue-rotate(201deg) brightness(119%) contrast(119%);

        Vermelho:
        filter: invert(13%) sepia(95%) saturate(7154%) hue-rotate(359deg) brightness(96%) contrast(117%);
        
        Verde:
        filter: invert(42%) sepia(95%) saturate(1000%) hue-rotate(100deg) brightness(119%) contrast(119%);
        
        Branco:
        filter: brightness(0) invert(1);
        
        Cinza:
        filter: brightness(50%);
        */
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
    <div class="login-container">
        <div class="login-card">
            <div class="login-body">
                <div class="text-center mb-4">
                    @php
                        $loginLogoPath = \App\Models\SystemSetting::get('login_logo_path', '/assets/images/logotipo-dark.svg');
                    @endphp
                    <div class="login-logo mb-3">
                        <img src="{{ asset($loginLogoPath) }}" 
                             alt="Logo" 
                             class="img-fluid login-custom-logo" 
                             style="max-height: 60px; max-width: 200px;"
                             onerror="this.onerror=null; console.error('Erro ao carregar logo:', this.src);">
                    </div>
                    <!-- <h2 class="fw-bold text-dark mb-2">Acesso ao Sistema</h2>
                    <p class="text-muted mb-0">Entre com suas credenciais</p> -->
                </div>
                
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Digite seu email"
                                   required
                                   autocomplete="email"
                                   autofocus>
                        </div>
                        @error('email')
                            <div class="text-danger mt-2">
                                <small><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                            </div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Digite sua senha"
                                   required
                                   autocomplete="current-password">
                        </div>
                        @error('password')
                            <div class="text-danger mt-2">
                                <small><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                            </div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Manter-me conectado
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Entrar no Sistema
                    </button>
                </form>
                
                <div class="divider"></div>
                
                <div class="text-center">
                    <a href="{{ route('home') }}" class="back-link">
                        <i class="fas fa-arrow-left me-2"></i>
                        Voltar ao site principal
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            
            if (loginForm) {
                loginForm.addEventListener('submit', function() {
                    // Mostrar loading no botão
                    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Entrando...';
                    loginBtn.disabled = true;
                });
            }
            
            // Auto-focus no primeiro campo com erro
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.focus();
            }
        });
    </script>
</body>
</html>
