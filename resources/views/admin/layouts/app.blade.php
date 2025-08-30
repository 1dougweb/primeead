<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    @stack('styles')

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background: #1e293b;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: .75rem 1.25rem;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link i {
            width: 1.25rem;
            margin-right: .5rem;
            text-align: center;
        }
        .content {
            min-height: 100vh;
            background: #f8fafc;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 280px;">
            <div class="p-3">
                <h5 class="mb-4">{{ config('app.name') }}</h5>
                
                <div class="mb-4">
                    <small class="text-uppercase fw-bold opacity-50 ls-wider d-block mb-2">Menu</small>
                    <nav class="nav flex-column">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                           href="{{ route('dashboard') }}">
                            <i class="fas fa-home"></i>
                            Dashboard
                        </a>
                        
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.email-campaigns.*') ? 'active' : '' }}"
                           href="{{ route('admin.email-campaigns.index') }}">
                            <i class="fas fa-envelope"></i>
                            Campanhas de Email
                        </a>
                    </nav>
                </div>
                
                <div>
                    <small class="text-uppercase fw-bold opacity-50 ls-wider d-block mb-2">Configurações</small>
                    <nav class="nav flex-column">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.settings.ai') ? 'active' : '' }}"
                           href="{{ route('admin.settings.ai') }}">
                            <i class="fas fa-robot"></i>
                            ChatGPT
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content flex-grow-1 p-4">
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html> 