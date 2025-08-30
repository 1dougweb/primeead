<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImpersonationSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se estamos em modo de impersonation
        if (session('is_impersonating')) {
            
            // Rotas que são proibidas durante impersonation
            $forbiddenRoutes = [
                'admin.usuarios.impersonate',
                'admin.usuarios.destroy',
                'admin.usuarios.store',
                'admin.usuarios.update',
                'admin.permissions.*',
                'admin.settings.*',
                'password.*',
                'logout'
            ];
            
            $currentRoute = $request->route()->getName();
            
            // Verificar se a rota atual está na lista de proibidas
            foreach ($forbiddenRoutes as $forbiddenRoute) {
                if (str_contains($forbiddenRoute, '*')) {
                    // Verificar padrão com wildcard
                    $pattern = str_replace('*', '', $forbiddenRoute);
                    if (str_starts_with($currentRoute, $pattern)) {
                        return $this->denyAccess($request);
                    }
                } else {
                    // Verificar rota exata
                    if ($currentRoute === $forbiddenRoute) {
                        return $this->denyAccess($request);
                    }
                }
            }
            
            // Verificar métodos HTTP sensíveis para certas rotas
            if (in_array($request->method(), ['DELETE', 'PUT', 'PATCH'])) {
                // Rotas que não devem aceitar modificações durante impersonation
                $sensitiveRoutes = [
                    'admin.usuarios',
                    'admin.permissions',
                    'admin.settings'
                ];
                
                foreach ($sensitiveRoutes as $sensitiveRoute) {
                    if (str_contains($currentRoute, $sensitiveRoute)) {
                        return $this->denyAccess($request);
                    }
                }
            }
        }

        return $next($request);
    }
    
    /**
     * Negar acesso e retornar resposta apropriada
     */
    private function denyAccess(Request $request)
    {
        $message = 'Esta ação não é permitida durante o modo de impersonation por motivos de segurança.';
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 403);
        }
        
        return redirect()->back()->with('error', $message);
    }
}
