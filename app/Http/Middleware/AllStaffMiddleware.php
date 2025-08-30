<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllStaffMiddleware
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

        // Definir variáveis de sessão para compatibilidade
        session([
            'admin_logged_in' => auth()->user()->isAdmin(),
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'admin_email' => auth()->user()->email,
            'admin_tipo' => auth()->user()->tipo_usuario
        ]);

        return $next($request);
    }
} 