<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Central de Ajuda') - EJA Supletivo</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Prism.js for code highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet">
    
    <style>
        :root {
            --help-primary: #4f46e5;
            --help-secondary: #6b7280;
            --help-success: #10b981;
            --help-warning: #f59e0b;
            --help-danger: #ef4444;
            --help-info: #3b82f6;
            --help-light: #f9fafb;
            --help-dark: #1f2937;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
        }

        .help-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--help-primary) 0%, #6366f1 100%);
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .help-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }

        .help-sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .help-sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
            font-weight: 600;
        }

        .help-sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }

        .help-content {
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin: 20px;
        }

        .help-header {
            background: linear-gradient(135deg, var(--help-primary) 0%, #6366f1 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 12px 12px 0 0;
            margin: 20px 20px 0 20px;
        }

        .help-breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }

        .help-breadcrumb .breadcrumb-item a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }

        .help-breadcrumb .breadcrumb-item.active {
            color: white;
        }

        .help-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .help-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        .help-card .card-header {
            background: var(--help-light);
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: var(--help-dark);
        }

        .code-block {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }

        .alert-help {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: var(--help-primary);
            color: white;
            border-radius: 50%;
            font-weight: bold;
            margin-right: 10px;
        }

        .help-section {
            margin-bottom: 30px;
        }

        .help-section h3 {
            color: var(--help-dark);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--help-primary);
        }

        .back-to-admin {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        @media (max-width: 768px) {
            .help-sidebar {
                position: fixed;
                top: 0;
                left: -280px;
                width: 280px;
                z-index: 1050;
                transition: left 0.3s ease;
            }

            .help-sidebar.show {
                left: 0;
            }

            .help-content {
                margin: 10px;
                padding: 20px;
            }

            .help-header {
                margin: 10px 10px 0 10px;
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Botão voltar para admin -->
    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary back-to-admin">
        <i class="fas fa-arrow-left me-2"></i>
        Voltar ao Admin
    </a>

    <div class="d-flex">
        <!-- Sidebar de Ajuda -->
        <div class="help-sidebar" id="helpSidebar">
            <div class="p-3" style="width: 300px!important;">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-question-circle fa-2x me-3"></i>
                    <h5 class="mb-0">Central de Ajuda</h5>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link {{ request()->routeIs('admin.help.index') ? 'active' : '' }}" 
                       href="{{ route('admin.help.index') }}">
                        <i class="fas fa-home"></i>
                        Início
                    </a>
                    
                    <div class="mt-3 mb-2">
                        <small class="text-uppercase fw-bold opacity-75 px-3">SISTEMA DE PAGAMENTOS</small>
                    </div>
                    
                    <a class="nav-link {{ request()->routeIs('admin.help.mercado-pago') ? 'active' : '' }}" 
                       href="{{ route('admin.help.mercado-pago') }}">
                        <i class="fas fa-credit-card"></i>
                        Configurar Mercado Pago
                    </a>
                    
                    <a class="nav-link {{ request()->routeIs('admin.help.configuracao-pagamentos') ? 'active' : '' }}" 
                       href="{{ route('admin.help.configuracao-pagamentos') }}">
                        <i class="fas fa-cogs"></i>
                        Configurações de Pagamento
                    </a>
                    
                    <a class="nav-link {{ request()->routeIs('admin.help.dashboard-pagamentos') ? 'active' : '' }}" 
                       href="{{ route('admin.help.dashboard-pagamentos') }}">
                        <i class="fas fa-chart-pie"></i>
                        Dashboard de Pagamentos
                    </a>
                    
                    <a class="nav-link" 
                       href="{{ route('admin.payments.index') }}">
                        <i class="fas fa-credit-card"></i>
                        Gerenciar Pagamentos
                    </a>
                    
                    <a class="nav-link {{ request()->routeIs('admin.help.automacao-pagamentos') ? 'active' : '' }}" 
                       href="{{ route('admin.help.automacao-pagamentos') }}">
                        <i class="fas fa-robot"></i>
                        Automação de Pagamentos
                    </a>
                </nav>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="flex-grow-1">
            <!-- Header da Página -->
            <div class="help-header">
                <nav aria-label="breadcrumb" class="help-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.help.index') }}">
                                <i class="fas fa-home me-1"></i>
                                Central de Ajuda
                            </a>
                        </li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
                <h1 class="h2 mb-0">@yield('page-title', 'Central de Ajuda')</h1>
                @if(isset($pageDescription))
                    <p class="mb-0 mt-2 opacity-90">{{ $pageDescription }}</p>
                @endif
            </div>

            <!-- Conteúdo -->
            <div class="help-content">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="btn btn-primary d-md-none" 
            style="position: fixed; top: 20px; left: 20px; z-index: 1060;"
            onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Prism.js for code highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('helpSidebar');
            sidebar.classList.toggle('show');
        }

        // Fechar sidebar ao clicar fora (mobile)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('helpSidebar');
            const toggleBtn = document.querySelector('[onclick="toggleSidebar()"]');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });

        // Smooth scrolling for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html> 