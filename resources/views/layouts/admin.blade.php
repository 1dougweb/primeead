<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel Administrativo') - EJA Supletivo</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- Admin Settings CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin-settings.css') }}">
    
    <!-- Charts CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/charts.css') }}">
    
    <!-- Tracking Scripts -->
    @include('components.tracking-scripts')
    
    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <!-- Stack para estilos customizados -->
    @stack('styles')
    
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 0;
            --transition-speed: 0.35s;
            --transition-curve: cubic-bezier(0.215, 0.610, 0.355, 1.000);
            --sidebar-color: #5a2d91!important;
            --sidebar-hover: #3a3a7c;
            --sidebar-color: {{ $currentTheme['primary_color'] ?? '#4a4a9c' }};
            --sidebar-hover:#3A5998;
            --theme-background: {{ $currentTheme['background'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }};
            --theme-card-bg: {{ $currentTheme['card_bg'] ?? 'rgba(255, 255, 255, 0.95)' }};
            --theme-shadow: {{ $currentTheme['shadow'] ?? '0 8px 32px rgba(102, 126, 234, 0.3)' }};
        }
        
        body {
            overflow-x: hidden;
            min-height: 100vh;
        }
        
        .sidebar {
            min-height: 100vh;
            background: var(--sidebar-color);
            width: var(--sidebar-width);
            position: fixed;
            z-index: 1031;
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            transform: translateX(0);
        }
        
        /* Scrollbar customizado para o sidebar */
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Para Firefox */
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) rgba(255, 255, 255, 0.1);
        }
        
        /* Estados de visibilidade do scrollbar */
        .sidebar::-webkit-scrollbar {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar.scrollbar-visible::-webkit-scrollbar,
        .sidebar.scrolling::-webkit-scrollbar,
        .sidebar:hover::-webkit-scrollbar {
            opacity: 1;
        }
        
        .sidebar.scrollbar-visible::-webkit-scrollbar-thumb,
        .sidebar.scrolling::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.4);
        }
        
        /* Indicador de scroll ativo */
        .sidebar.scrolling {
            box-shadow: inset -2px 0 4px rgba(255, 255, 255, 0.1);
        }
        
        /* Foco para controle por teclado */
        .sidebar:focus {
            outline: 2px solid rgba(255, 255, 255, 0.3);
            outline-offset: -2px;
        }
        
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        
        .sidebar.collapsed {
            transform: translateX(calc(-1 * var(--sidebar-width)));
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            border-radius: 8px;
            margin: 4px 10px;
            padding: 10px 15px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.15);
        }
        
        .sidebar .nav-link.active {
            border-left: 4px solid #ffffff;
        }
        
        .main-content {
            background-color: #f8f9fa;
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 15px;
            transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
        }
        
        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
            width: 100%;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            background: var(--theme-card-bg);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);
            color: white;
        }
        
        .stats-card .card-body {
            padding: 1.5rem;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .stats-label {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #6f42c1;
            border-color: #6f42c1;
        }
        
        .btn-primary:hover {
            background: #5a2d91;
            border-color: #5a2d91;
        }
        
        .table th {
            border-top: none;
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        /* Botão de toggle da sidebar */
        .sidebar-toggle {
            position: fixed !important;
            top: 15px !important;
            left: calc(var(--sidebar-width) - 26px) !important;
            z-index: 1033 !important;
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            background: #5a2d91 !important;
            color: white !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            border: none !important;
            opacity: 1 !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
            font-size: 16px !important;
        }
        
        /* Quando a sidebar está recolhida, o botão fica na posição esquerda */
        .sidebar.collapsed ~ .sidebar-toggle,
        .sidebar-toggle.collapsed-position {
            left: 15px !important;
        }
        
        #sidebarToggle {
            position: fixed !important;
            display: flex !important;
            visibility: visible !important;
        }
        
        .sidebar-toggle:hover {
            background: var(--sidebar-hover);
            opacity: 1;
        }
        
        .sidebar-toggle {
            transition: left 0.3s ease-in-out, background-color 0.3s ease;
        }
        
        .sidebar-toggle.position-changing {
            transition: left 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .sidebar-toggle.icon-rotating {
            transition: transform 0.3s ease;
        }
        
        .sidebar-toggle.icon-rotating i {
            animation: rotateIcon 0.3s ease;
        }
        
        @keyframes rotateIcon {
            0% { transform: rotate(0deg); }
            50% { transform: rotate(90deg); }
            100% { transform: rotate(0deg); }
        }
        

        

        

        
        /* Estilos para o tema dinâmico */
        .greeting-icon {
            animation: float 3s ease-in-out infinite;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .greeting-icon:hover {
            transform: scale(1.1);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        .topbar-greeting {
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .topbar-greeting:hover {
            transform: translateY(-2px);
            box-shadow: var(--theme-shadow);
        }
        
        .current-time {
            font-family: 'Courier New', monospace;
        }
        
        .stats-card {
            background: var(--theme-background);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes rotate-icon {
            0% {
                transform: rotate(0deg);
            }
            50% {
                transform: rotate(180deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        
        /* Removido o seletor CSS para posição do botão - agora controlado via JavaScript */
        
        /* Overlay para quando a sidebar está aberta em dispositivos móveis */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1030;
            display: none;
            opacity: 0;
            transition: opacity var(--transition-speed) var(--transition-curve);
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Estilos adicionais para o sidebar */
        .sidebar-header {
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 15px;
        }
        
        .sidebar-logo {
            background: rgba(255,255,255,0.1);
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .sidebar-logo:hover {
            background: rgba(255,255,255,0.2);
            transform: rotate(5deg);
        }
        
        .user-greeting {
            border-left: 4px solid rgba(255,255,255,0.5);
        }
        
        /* Estilos para o topbar */
        .topbar-greeting {
            border-left: 4px solid var(--sidebar-color);
            position: relative;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 1));
        }
        
        .topbar-greeting:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .greeting-icon-container {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .greeting-icon-container:hover {
            opacity: 0.8;
        }
        
        .greeting-icon {
            font-size: 1.5rem;
        }
        
        .current-time {
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .current-time:hover {
            background-color: #e9ecef;
        }
        
        /* Estilos para o avatar e dropdown do perfil */
        .avatar-circle {
            width: 32px;
            height: 32px;
            background-color: var(--sidebar-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .profile-dropdown .dropdown-toggle {
            border-radius: 30px;
            padding: 5px 15px;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .profile-dropdown .dropdown-toggle:hover {
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
            background-color: #f8f9fa;
        }
        
        .profile-dropdown .dropdown-toggle:hover .avatar-circle {
            opacity: 0.9;
        }
        
        .profile-dropdown .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 8px;
        }
        
        .profile-dropdown .dropdown-item {
            border-radius: 6px;
            padding: 8px 15px;
            margin-bottom: 2px;
            transition: all 0.2s ease;
            z-index: 1000!important;
        }
        
        .profile-dropdown .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .profile-dropdown .dropdown-item.text-danger:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }
        
        .nav-section-title {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 10px;
        }
        
        .logout-btn {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin-top: 20px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            color: white;
        }
        
        .logout-btn:active {
            transform: translateY(0);
        }
        
        /* Estilos do menu mobile */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 100% !important;
                max-width: 300px;
                z-index: 1031;
                background: var(--sidebar-color);
            }
            
            .sidebar.mobile-show {
                transform: translateX(0);
            }
            
            .sidebar-toggle {
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1032;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: var(--sidebar-color);
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                border: none;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }
            
            .sidebar-toggle:hover {
                background: var(--sidebar-hover);
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1030;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .sidebar-overlay.active {
                display: block;
                opacity: 1;
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding-top: 70px !important;
            }
            
            /* Ajustes para o conteúdo do sidebar */
            .sidebar-nav-container {
                padding: 1rem;
            }
            
            .nav-link {
                padding: 0.75rem 1rem;
                font-size: 1rem;
            }
            
            .sidebar-header {
                padding: 1rem;
                margin-bottom: 0;
            }
            
            /* Esconder scrollbar mas manter funcionalidade */
            .sidebar {
                scrollbar-width: none;
                -ms-overflow-style: none;
            }
            
            .sidebar::-webkit-scrollbar {
                display: none;
            }
        }

        /* Estilos para logos personalizados */
        .sidebar-custom-logo {
            height: 50px;
            width: auto;
            max-width: 200px;
            object-fit: contain;
            object-position: center;
        }

        .sidebar-custom-logo[src$=".svg"] {
            filter: brightness(0) invert(1);
            -webkit-filter: brightness(0) invert(1);
        }

        /* Ajustes para o container do logo */
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 70px;
        }

        /* Estilos para gráficos responsivos */
        .chart-wrapper {
            position: relative;
            margin: auto;
            height: 100%;
            min-height: 300px;
        }
        
        .chart-canvas {
            width: 100% !important;
            height: 100% !important;
        }
        
        @media (max-width: 768px) {
            .chart-wrapper {
                min-height: 250px;
            }
            
            .card-header {
                padding: 0.75rem;
            }
            
            .card-body {
                padding: 0.75rem;
            }
            
            .card-header h5 {
                font-size: 1rem;
            }
            
            .stats-number {
                font-size: 1.75rem;
            }
            
            .stats-label {
                font-size: 0.8rem;
            }
            
            #chartFilterForm {
                flex-wrap: wrap;
                gap: 0.5rem !important;
            }
            
            #chartFilterForm .input-group {
                width: 100%;
            }
            
            #chartFilterForm button {
                width: 100%;
            }
            
            .card-header.d-flex {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .card-header.d-flex > div {
                width: 100%;
            }
        }
        
        /* Melhorias visuais para os cards */
        .chart-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .chart-card .card-header {
            border-bottom: none;
            padding: 1rem;
        }
        
        .chart-card .card-body {
            padding: 1rem;
        }
        
        /* Melhorias para o formulário de filtro */
        #chartFilterForm {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem;
            border-radius: 0.5rem;
        }
        
        #chartFilterForm .input-group-text {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
        }
        
        #chartFilterForm .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: none;
        }
        
        #chartFilterForm .btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
        }
        
        #chartFilterForm .btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Estilos para os gráficos */
        .chart-container {
            position: relative;
            height: 100%;
            width: 100%;
        }
        
        .chart-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .chart-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .chart-container canvas {
            max-height: 100%;
        }
        
        @media (max-width: 768px) {
            .chart-container {
                height: 300px !important;
            }
            
            .chart-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    @include('components.tracking-noscript')
    
    <!-- Botão de toggle da sidebar -->
    <button class="sidebar-toggle d-flex d-md-flex" id="sidebarToggle" title="Alternar menu lateral">
        <i class="fas fa-chevron-left"></i>
    </button>
    
    <!-- Overlay para dispositivos móveis -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="sidebar" id="sidebar">
                <div class="position-sticky pt-4">
                    <div class="sidebar-header px-3 mb-4">
                        @php
                            $sidebarLogoPath = \App\Models\SystemSetting::get('sidebar_logo_path', '/assets/images/logotipo-dark.svg');
                        @endphp
                        
                        <!-- Logo - sempre exibir a imagem -->
                        <div class="text-center sidebar-logo-container">
                            <img src="{{ asset($sidebarLogoPath) }}" 
                                 alt="Logo" 
                                 class="img-fluid sidebar-custom-logo" 
                                 style="max-height: 50px; max-width: 200px;"
                                 onerror="this.onerror=null; console.error('Erro ao carregar logo:', this.src);">
                        </div>
                        
                        <style>
                        .sidebar-logo-container {
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            min-height: 50px;
                            padding: 10px;
                        }
                        
                        .sidebar-custom-logo {
                            height: 50px;
                            width: auto;
                            max-width: 200px;
                            object-fit: contain;
                            object-position: center;
                        }
                        
                        /* Controle de cor para SVGs */
                        .sidebar-custom-logo[src$=".svg"] {
                            /* Deixa o SVG branco no sidebar escuro */
                            filter: brightness(0) invert(1);
                            -webkit-filter: brightness(0) invert(1);
                        }

                        /* Para mudar para outra cor, use estas propriedades:
                        
                        Azul:
                        filter: invert(42%) sepia(95%) saturate(1000%) hue-rotate(201deg) brightness(119%) contrast(119%);

                        Vermelho:
                        filter: invert(13%) sepia(95%) saturate(7154%) hue-rotate(359deg) brightness(96%) contrast(117%);
                        
                        Verde:
                        filter: invert(42%) sepia(95%) saturate(1000%) hue-rotate(100deg) brightness(119%) contrast(119%);
                        
                        Preto:
                        filter: brightness(0%);
                        
                        Cinza:
                        filter: brightness(50%);
                        */
                        </style>
                    </div>
                    
                    <div class="sidebar-nav-container">


                        
                        <div class="nav-section mb-3">
                            <h6 class="nav-section-title text-white-50 px-2 mb-2">MENU PRINCIPAL</h6>
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                                       href="{{ route('dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-3"></i>
                                        Dashboard
                                    </a>
                                </li>
                                @if((!empty($userMenuPermissions['admin.inscricoes']) && $userMenuPermissions['admin.inscricoes']) || session('admin_tipo') === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.inscricoes') ? 'active' : '' }}" 
                                       href="{{ route('admin.inscricoes') }}">
                                        <i class="fas fa-users me-3"></i>
                                        Inscrições
                                    </a>
                                </li>
                                @endif
                                @if((!empty($userMenuPermissions['admin.matriculas.*']) && $userMenuPermissions['admin.matriculas.*']) || session('admin_tipo') === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.matriculas.*') ? 'active' : '' }}"
                                        href="{{ route('admin.matriculas.index') }}">
                                        <i class="fas fa-graduation-cap me-3"></i>
                                        <span>Matrículas</span>
                                    </a>
                                </li>
                                @endif
                                @if((!empty($userMenuPermissions['admin.contracts.*']) && $userMenuPermissions['admin.contracts.*']) || session('admin_tipo') === 'admin')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.contracts.*') ? 'active' : '' }}" 
                                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-file-contract me-3"></i>
                                        <span>Contratos</span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('admin.contracts.index') ? 'active' : '' }}" 
                                               href="{{ route('admin.contracts.index') }}">
                                                <i class="fas fa-list me-2"></i>
                                                Listar Contratos
                                            </a>
                                        </li>
                                        @if((!empty($userMenuPermissions['admin.contracts.templates.*']) && $userMenuPermissions['admin.contracts.templates.*']) || session('admin_tipo') === 'admin')
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('admin.contracts.templates.*') ? 'active' : '' }}" 
                                               href="{{ route('admin.contracts.templates.index') }}">
                                                <i class="fas fa-file-alt me-2"></i>
                                                Templates
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </li>
                                @endif
                                @if((!empty($userMenuPermissions['admin.kanban.*']) && $userMenuPermissions['admin.kanban.*']) || session('admin_tipo') === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.kanban.*') ? 'active' : '' }}" 
                                       href="{{ route('admin.kanban.index') }}">
                                        <i class="fas fa-columns me-3"></i>
                                        Kanban
                                    </a>
                                </li>
                                @endif
                                @if((!empty($userMenuPermissions['admin.files.*']) && $userMenuPermissions['admin.files.*']) || session('admin_tipo') === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.files.*') ? 'active' : '' }}" 
                                       href="{{ route('admin.files.index') }}">
                                        <i class="fas fa-cloud me-3"></i>
                                        Arquivos
                                    </a>
                                </li>
                                @endif
                                @if((!empty($userMenuPermissions['dashboard.contacts.*']) && $userMenuPermissions['contacts.*']) || session('admin_tipo') === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}" 
                                       href="{{ route('contacts.index') }}">
                                        <i class="fas fa-address-book me-3"></i>
                                        Contatos
                                    </a>
                                </li>
                                @endif
                                @if((!empty($userMenuPermissions['admin.parceiros.*']) && $userMenuPermissions['admin.parceiros.*']) || session('admin_tipo') === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.parceiros.*') ? 'active' : '' }}" 
                                       href="{{ route('admin.parceiros.index') }}">
                                        <i class="fas fa-handshake me-3"></i>
                                        Parceiros
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                        
                        @if(session('admin_tipo') === 'admin' || 
                           (!empty($userMenuPermissions['admin.contracts.templates.*']) && $userMenuPermissions['admin.contracts.templates.*']) ||
                           (!empty($userMenuPermissions['admin.email-templates.*']) && $userMenuPermissions['admin.email-templates.*']) ||
                           (!empty($userMenuPermissions['admin.whatsapp.templates.*']) && $userMenuPermissions['admin.whatsapp.templates.*']))
                            <div class="nav-section mb-3">
                                <h6 class="nav-section-title text-white-50 px-2 mb-2">TEMPLATES</h6>
                                <ul class="nav flex-column">
                                    @if((!empty($userMenuPermissions['admin.contracts.templates.*']) && $userMenuPermissions['admin.contracts.templates.*']) || session('admin_tipo') === 'admin')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.contracts.templates.*') ? 'active' : '' }}" 
                                           href="{{ route('admin.contracts.templates.index') }}">
                                            <i class="fas fa-file-contract me-3"></i>
                                            Templates de Contratos
                                        </a>
                                    </li>
                                    @endif
                                    @if((!empty($userMenuPermissions['admin.email-templates.*']) && $userMenuPermissions['admin.email-templates.*']) || session('admin_tipo') === 'admin')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}" 
                                           href="{{ route('admin.email-templates.index') }}">
                                            <i class="fas fa-envelope me-3"></i>
                                            Templates de Email
                                        </a>
                                    </li>
                                    @endif
                                    @if((!empty($userMenuPermissions['admin.whatsapp.templates.*']) && $userMenuPermissions['admin.whatsapp.templates.*']) || session('admin_tipo') === 'admin')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.whatsapp.templates.*') ? 'active' : '' }}" 
                                           href="{{ route('admin.whatsapp.templates.index') }}">
                                            <i class="fas fa-comment-dots me-3"></i>
                                            Templates WhatsApp
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                        
                        @if(session('admin_tipo') === 'admin' || 
                           (!empty($userMenuPermissions['admin.monitoramento']) && $userMenuPermissions['admin.monitoramento']) || 
                           (!empty($userMenuPermissions['admin.usuarios.*']) && $userMenuPermissions['admin.usuarios.*']) || 
                           (!empty($userMenuPermissions['admin.settings.*']) && $userMenuPermissions['admin.settings.*']) || 
                           (!empty($userMenuPermissions['admin.whatsapp.*']) && $userMenuPermissions['admin.whatsapp.*']) ||
                           (!empty($userMenuPermissions['admin.permissions.*']) && $userMenuPermissions['admin.permissions.*']))
                            <div class="nav-section mb-3">
                                <h6 class="nav-section-title text-white-50 px-2 mb-2">ADMINISTRAÇÃO</h6>
                                <ul class="nav flex-column">
                                    @if((!empty($userMenuPermissions['admin.monitoramento']) && $userMenuPermissions['admin.monitoramento']) || session('admin_tipo') === 'admin')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.monitoramento') ? 'active' : '' }}" 
                                           href="{{ route('admin.monitoramento') }}">
                                            <i class="fas fa-chart-line me-3"></i>
                                            Monitoramento
                                        </a>
                                    </li>
                                    @endif
                                    @if((!empty($userMenuPermissions['admin.usuarios.*']) && $userMenuPermissions['admin.usuarios.*']) || session('admin_tipo') === 'admin')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}" 
                                           href="{{ route('admin.usuarios.index') }}">
                                            <i class="fas fa-user-cog me-3"></i>
                                            Usuários
                                        </a>
                                    </li>
                                    @endif
                                    @if((!empty($userMenuPermissions['admin.whatsapp.*']) && $userMenuPermissions['admin.whatsapp.*']) || session('admin_tipo') === 'admin')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.settings.whatsapp') ? 'active' : '' }}" 
                                           href="{{ route('admin.settings.whatsapp') }}">
                                            <i class="fab fa-whatsapp me-3"></i>
                                            WhatsApp
                                        </a>
                                    </li>
                                    @endif
                                    @if((!empty($userMenuPermissions['admin.settings.*']) && $userMenuPermissions['admin.settings.*']) || session('admin_tipo') === 'admin')
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}" 
                                           href="{{ route('admin.settings.index') }}">
                                            <i class="fas fa-cogs me-3"></i>
                                            Configurações
                                        </a>
                                    </li>
                                    @endif
                                    @if(session('admin_tipo') === 'admin' || (!empty($userMenuPermissions['admin.permissions.*']) && $userMenuPermissions['admin.permissions.*']))
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" 
                                           href="{{ route('admin.permissions.index') }}">
                                            <i class="fas fa-user-shield me-3"></i>
                                            Permissões
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                        
                        <div class="nav-section mb-3">
                            <h6 class="nav-section-title text-white-50 px-2 mb-2">AJUDA</h6>
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.help.*') ? 'active' : '' }}" 
                                       href="{{ route('admin.help.index') }}">
                                        <i class="fas fa-question-circle me-3"></i>
                                        Central de Ajuda
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="nav-section mt-auto">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="nav-link btn btn-link text-start w-100 logout-btn">
                                            <i class="fas fa-sign-out-alt me-3"></i>
                                            Sair
                                        </button>
                                    </form>
                                </li>
                            </ul>
                            
                            <div class="sidebar-footer text-center mt-4 mb-3">
                                <div class="version small text-white-50 mb-1">
                                    <i class="fas fa-code-branch me-1"></i> v1.0.4
                                </div>
                                <div class="copyright small text-white-50">
                                    &copy; {{ date('Y') }} Ensino Certo
                                </div>
                            </div>
                            

                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="main-content" id="mainContent">
                <!-- Topbar com saudação e tema dinâmico -->
                @if(session('admin_email'))
                    <div class="topbar-greeting p-3 mb-4 shadow-sm" style="background: var(--theme-card-bg); backdrop-filter: blur(10px); border-radius: 12px;">
                        <div class="row align-items-center">
                            @php
                                $name = session('admin_name');
                                if ($name) {
                                    // Pegar apenas o primeiro nome
                                    $firstName = explode(' ', $name)[0];
                                } else {
                                    $firstName = 'Usuário';
                                }
                            @endphp
                            
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="greeting-icon-container me-3">
                                        <i class="greeting-icon {{ $currentTheme['icon'] ?? 'fas fa-sun' }}" style="font-size: 2rem; color: {{ $currentTheme['primary_color'] ?? '#667eea' }};"></i>
                                    </div>
                                    <div>
                                        <h4 class="m-0">{{ $currentTheme['greeting'] ?? 'Olá' }}, {{ $firstName }}!</h4>
                                        @if(session('admin_tipo'))
                                            <div class="user-role mt-1">
                                                @switch(session('admin_tipo'))
                                                    @case('admin')
                                                        <span class="badge bg-warning text-dark">👑 Administrador</span>
                                                        @break
                                                    @case('vendedor')
                                                        <span class="badge bg-success">💼 Vendedor</span>
                                                        @break
                                                    @case('colaborador')
                                                        <span class="badge bg-info">👤 Colaborador</span>
                                                        @break
                                                    @case('midia')
                                                        <span class="badge bg-primary">📱 Mídia</span>
                                                        @break
                                                @endswitch
                                                
                                                <!-- Notificação de Impersonation -->
                                                @if(session('is_impersonating'))
                                                    <div class="mt-2">
                                                        <div class="alert alert-warning alert-dismissible fade show py-2 px-3 mb-0" role="alert">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-user-secret me-2"></i>
                                                                <div class="flex-grow-1">
                                                                    <strong>Modo Impersonation:</strong> 
                                                                    Logado como <strong>{{ session('impersonated_user_name') }}</strong>
                                                                    <small class="d-block text-muted">
                                                                        Usuário original: {{ session('impersonating_user_name') }}
                                                                    </small>
                                                                </div>
                                                                <form method="POST" action="{{ route('admin.usuarios.stop-impersonation') }}" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger ms-2">
                                                                        <i class="fas fa-sign-out-alt me-1"></i>
                                                                        Sair da Impersonation
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-md-end align-items-center mt-3 mt-md-0">
                                    <div class="current-time text-muted me-3">
                                        <div class="d-flex flex-column text-end">
                                            <div class="fw-bold">{{ $currentTime['time'] ?? date('H:i') }}</div>
                                            <small>{{ $currentTime['date'] ?? date('d/m/Y') }}</small>
                                        </div>
                                    </div>
                                    @if(session('admin_id'))
                                        <div class="profile-dropdown dropdown">
                                            <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                <div class="avatar-circle me-2">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <span class="d-none d-md-inline">Perfil</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                                    <i class="fas fa-user me-2"></i> Meu Perfil
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('logout') }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-sign-out-alt me-2"></i> Sair
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Sair">
                                                <i class="fas fa-sign-out-alt"></i> Sair
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('page-title', 'Dashboard')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        @yield('page-actions')
                    </div>
                </div>

                <!-- Alertas -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Conteúdo da página -->
                @yield('content')
                
                <!-- Footer -->
                <footer class="pb-2 text-center text-muted border-top pt-3 mt-5">
                    <small>&copy; {{ date('Y') }} Ensino Certo. Todos os direitos reservados.</small>
                </footer>
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
            crossorigin="anonymous"></script>
    
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Configurar o Axios para incluir o token CSRF em todas as requisições
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    </script>
    
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- Chart.js v4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    
    <!-- CSRF Helper Fixado -->
    <script src="{{ asset('js/csrf-helper-fixed.js') }}"></script>
    
    <!-- Scripts adicionais -->
    @stack('scripts')
    
    <script>
        // Script para controlar a visibilidade da sidebar
        document.addEventListener('DOMContentLoaded', function() {
            // Função para atualizar o relógio em tempo real
            function updateClock() {
                const now = new Date();
                const timeElement = document.getElementById('currentTime');
                
                if (timeElement) {
                    const options = { 
                        hour: '2-digit', 
                        minute: '2-digit',
                        hour12: false
                    };
                    
                    timeElement.textContent = now.toLocaleTimeString('pt-BR', options);
                    
                    // Adicionar a data atual
                    const dateOptions = { 
                        weekday: 'long', 
                        day: 'numeric', 
                        month: 'long'
                    };
                    
                    const dateStr = now.toLocaleDateString('pt-BR', dateOptions);
                    timeElement.textContent += ' · ' + dateStr;
                }
            }
            
            // Atualizar o relógio imediatamente
            updateClock();
            
            // Atualizar o relógio a cada minuto
            setInterval(updateClock, 60000);
            
            // Inicializar controle avançado do scrollbar do sidebar
            initSidebarScrollControl();
            
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            
            // Se o botão não foi encontrado, criar um novo
            if (!sidebarToggle) {
                console.warn('Botão sidebar-toggle não encontrado, criando um novo...');
                const newToggle = document.createElement('button');
                newToggle.id = 'sidebarToggle';
                newToggle.className = 'sidebar-toggle d-flex d-md-flex';
                newToggle.title = 'Alternar menu lateral';
                newToggle.innerHTML = '<i class="fas fa-chevron-left"></i>';
                document.body.appendChild(newToggle);
                
                // Atualizar a referência
                const updatedToggle = document.getElementById('sidebarToggle');
                console.log('Novo botão criado:', updatedToggle);
                
                // Adicionar event listener ao novo botão
                if (updatedToggle) {
                    updatedToggle.addEventListener('click', toggleSidebar);
                }
            }
            
            // Verificar se há uma preferência salva
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            
            // Função para atualizar a posição do botão toggle
            function updateTogglePosition() {
                const isCollapsed = sidebar.classList.contains('collapsed');
                const isMobile = window.innerWidth <= 768;
                
                // Calcular a posição do botão
                let newPosition;
                
                if (isMobile || isCollapsed) {
                    // Em dispositivos móveis ou quando a sidebar está recolhida
                    newPosition = '15px';
                    sidebarToggle.classList.add('collapsed-position');
                } else {
                    // Em desktop com sidebar expandida, posicionar do lado direito da sidebar (fora dela)
                    newPosition = 'calc(var(--sidebar-width) + 10px)';
                    sidebarToggle.classList.remove('collapsed-position');
                }
                
                // Aplicar a nova posição com uma pequena animação
                if (sidebarToggle.style.left !== newPosition) {
                    // Adicionar classe para animar a transição de posição
                    sidebarToggle.classList.add('position-changing');
                    
                    // Atualizar a posição
                    sidebarToggle.style.left = newPosition;
                    
                    // Remover a classe após a animação
                    setTimeout(() => {
                        sidebarToggle.classList.remove('position-changing');
                    }, 350);
                }
            }
            
            // Função para atualizar o ícone do botão
            function updateToggleIcon() {
                const isCollapsed = sidebar.classList.contains('collapsed');
                
                // Adicionar classe de transição para animar a rotação do ícone
                sidebarToggle.classList.add('icon-rotating');
                
                // Atualizar o ícone
                sidebarToggle.innerHTML = isCollapsed 
                    ? '<i class="fas fa-chevron-right"></i>' 
                    : '<i class="fas fa-chevron-left"></i>';
                
                // Remover a classe após a animação
                setTimeout(() => {
                    sidebarToggle.classList.remove('icon-rotating');
                }, 300);
            }
            
            // Aplicar estado inicial
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            }
            
            // Atualizar ícone e posição inicial
            updateToggleIcon();
            updateTogglePosition();
            
            // Função para alternar a sidebar
            function toggleSidebar() {
                const isCollapsed = sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                
                // Atualizar ícone e posição do botão
                updateToggleIcon();
                updateTogglePosition();
                
                // Ativar/desativar overlay em dispositivos móveis
                if (window.innerWidth <= 768) {
                    if (!isCollapsed) {
                        sidebarOverlay.classList.add('active');
                    } else {
                        sidebarOverlay.classList.remove('active');
                    }
                }
                
                // Salvar preferência
                localStorage.setItem('sidebarCollapsed', isCollapsed);
                
                // Disparar evento de redimensionamento para atualizar gráficos ou outros componentes
                window.dispatchEvent(new Event('resize'));
            }
            
            // Adicionar evento de clique ao botão
            sidebarToggle.addEventListener('click', toggleSidebar);
            
            // Fechar sidebar quando clicar no overlay
            sidebarOverlay.addEventListener('click', function() {
                if (!sidebar.classList.contains('collapsed')) {
                    toggleSidebar();
                }
            });
            
            // Lidar com responsividade em dispositivos móveis
            function handleResize() {
                const isMobile = window.innerWidth <= 768;
                
                if (isMobile) {
                    // Em dispositivos móveis, sempre começar com sidebar recolhida
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                    sidebarOverlay.classList.remove('active');
                    
                    // Reposicionar o botão toggle para o canto esquerdo
                    sidebarToggle.style.left = '15px';
                    sidebarToggle.classList.add('collapsed-position');
                } else {
                    // Em desktop, respeitar a preferência do usuário
                    const preferCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                    if (preferCollapsed) {
                        sidebar.classList.add('collapsed');
                        mainContent.classList.add('expanded');
                    } else {
                        sidebar.classList.remove('collapsed');
                        mainContent.classList.remove('expanded');
                    }
                    
                    // Atualizar posição do botão toggle baseado no estado da sidebar
                    updateTogglePosition();
                }
                
                // Atualizar ícone do botão
                updateToggleIcon();
            }
            
            // Verificar tamanho da tela no carregamento
            handleResize();
            
            // Adicionar listener para redimensionamento da janela
            window.addEventListener('resize', handleResize);
        });
        
        // Função para controle avançado do scrollbar do sidebar
        function initSidebarScrollControl() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;
            
            let isScrolling = false;
            let scrollTimeout;
            
            // Adicionar classe para mostrar scrollbar durante o scroll
            sidebar.addEventListener('scroll', function() {
                if (!isScrolling) {
                    sidebar.classList.add('scrolling');
                    isScrolling = true;
                }
                
                // Limpar timeout anterior
                clearTimeout(scrollTimeout);
                
                // Ocultar scrollbar após parar de fazer scroll
                scrollTimeout = setTimeout(() => {
                    sidebar.classList.remove('scrolling');
                    isScrolling = false;
                }, 1000);
            });
            
            // Mostrar scrollbar ao passar o mouse sobre a sidebar
            sidebar.addEventListener('mouseenter', function() {
                sidebar.classList.add('scrollbar-visible');
            });
            
            sidebar.addEventListener('mouseleave', function() {
                sidebar.classList.remove('scrollbar-visible');
            });
            
            // Controle por teclado
            sidebar.addEventListener('keydown', function(e) {
                const scrollAmount = 50;
                
                switch(e.key) {
                    case 'ArrowUp':
                        e.preventDefault();
                        sidebar.scrollTop -= scrollAmount;
                        break;
                    case 'ArrowDown':
                        e.preventDefault();
                        sidebar.scrollTop += scrollAmount;
                        break;
                    case 'PageUp':
                        e.preventDefault();
                        sidebar.scrollTop -= sidebar.clientHeight * 0.8;
                        break;
                    case 'PageDown':
                        e.preventDefault();
                        sidebar.scrollTop += sidebar.clientHeight * 0.8;
                        break;
                    case 'Home':
                        e.preventDefault();
                        sidebar.scrollTop = 0;
                        break;
                    case 'End':
                        e.preventDefault();
                        sidebar.scrollTop = sidebar.scrollHeight;
                        break;
                }
            });
            
            // Fazer a sidebar focável para controle por teclado
            sidebar.setAttribute('tabindex', '0');
            
            // Smooth scroll personalizado
            sidebar.style.scrollBehavior = 'smooth';
        }
        
        // Função para limpar dados do formulário de matrícula
        function clearMatriculaFormData() {
            localStorage.removeItem('matricula_form_data');
            console.log('Dados do formulário de matrícula limpos');
        }
        
        // Limpar dados quando navegar para criação de matrícula
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar se estamos na página de criação de matrícula
            if (window.location.pathname.includes('/admin/matriculas/create')) {
                // Aguardar um pouco para garantir que a página carregou
                setTimeout(() => {
                    const inscricaoId = document.querySelector('input[name="inscricao_id"]');
                    if (!inscricaoId || !inscricaoId.value) {
                        clearMatriculaFormData();
                    }
                }, 100);
            }
            
            // Adicionar listener para links de criação de matrícula
            const matriculaLinks = document.querySelectorAll('a[href*="/admin/matriculas/create"]');
            matriculaLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Limpar dados antes de navegar
                    clearMatriculaFormData();
                });
            });
        });
    </script>
</body>
</html> 