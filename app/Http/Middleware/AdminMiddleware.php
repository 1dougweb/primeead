<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se o usuário não está autenticado, redirecionar para login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Se a rota é o dashboard, permitir acesso para todos os usuários autenticados
        if ($request->routeIs('dashboard')) {
            return $next($request);
        }

        // Permitir acesso à rota de sair da impersonation para todos os usuários autenticados
        if ($request->routeIs('admin.usuarios.stop-impersonation')) {
            return $next($request);
        }

        // Se o usuário é admin, permitir acesso a todas as rotas
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Mapeamento de rotas para permissões específicas baseado no novo sistema
        $routePermissionMap = [
            'admin.inscricoes' => 'inscricoes.index',
            'admin.inscricoes.*' => 'inscricoes.index',
            'admin.matriculas.*' => 'matriculas.index',
            'admin.kanban.*' => 'kanban.index',
            'admin.files.*' => 'google-drive.index',
            'admin.parceiros.*' => 'parceiros.index',
            'admin.contracts.*' => 'contratos.index',
            'admin.payments.*' => 'pagamentos.index',
            'admin.settings.whatsapp' => 'whatsapp.admin',
            'admin.settings.whatsapp.*' => 'whatsapp.admin',
            'admin.whatsapp.templates.*' => 'whatsapp.templates',
            'admin.email-campaigns.*' => 'whatsapp.admin',
            'admin.usuarios.*' => 'usuarios.index',
            'admin.settings.*' => 'configuracoes.index',
            'admin.monitoramento' => 'monitoramento.index',
            'admin.permissions.*' => 'permissoes.index',
            'contacts.*' => 'contatos.index',
            
            // Templates
            'admin.contracts.templates.*' => 'contract-templates.index',
            'admin.email-templates.*' => 'email-templates.index',
            'admin.whatsapp.templates.*' => 'whatsapp.templates',
            'templates.*' => 'contract-templates.index',
        ];

        // Verificar se o usuário tem permissão específica para a rota atual
        foreach ($routePermissionMap as $route => $permission) {
            if ($request->routeIs($route)) {
                $hasPermission = $user->hasPermission($permission);
                
                if ($hasPermission) {
                    return $next($request);
                }
                break;
            }
        }

        // Permissões padrão baseadas no tipo de usuário (fallback)
        // Apenas para casos onde não há permissões específicas no banco
        $allowedRoutes = [];
        switch ($user->tipo_usuario) {
            case 'vendedor':
                $allowedRoutes = [
                    'admin.inscricoes',
                    'admin.inscricoes.*',
                    'admin.matriculas.*',
                    'admin.contracts.*',
                    'admin.payments.*',
                    'admin.kanban.*',
                    'contacts.*'
                ];
                break;
            case 'colaborador':
                $allowedRoutes = [
                    'admin.inscricoes',
                    'admin.inscricoes.*',
                    'admin.matriculas.*',
                    'admin.kanban.*',
                    'admin.contracts.*'
                ];
                break;
            case 'midia':
                $allowedRoutes = [
                    'admin.email-campaigns.*',
                    'admin.kanban.*'
                ];
                break;
            case 'parceiro':
                $allowedRoutes = [
                    'admin.parceiros.*'
                ];
                break;
        }

        // Verificar se a rota está nas permitidas para o tipo de usuário
        foreach ($allowedRoutes as $allowedRoute) {
            if ($request->routeIs($allowedRoute)) {

                return $next($request);
            }
        }

        // Se chegou até aqui, não tem permissão

        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Você não tem permissão para acessar este recurso.'
            ], 403);
        }
        
        return redirect()->route('dashboard')->with('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate($request, $response): void
    {
        // Do nothing - prevent Laravel from trying to resolve parameters during termination
    }
}
