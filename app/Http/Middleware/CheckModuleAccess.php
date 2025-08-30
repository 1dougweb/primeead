<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        // Se o usuário não está autenticado, redirecionar para login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Verificar se o usuário tem acesso ao módulo
        if (!auth()->user()->hasModuleAccess($module)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado. Você não tem permissão para acessar este módulo.'
                ], 403);
            }
            
            return redirect()->route('dashboard')->with('error', 'Acesso negado. Você não tem permissão para acessar este módulo.');
        }

        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate($request, $response): void
    {
        // Do nothing - prevent Laravel from trying to resolve parameters during termination
    }
} 