<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrMediaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o usuário está autenticado e é admin ou mídia
        if (!auth()->check() || !in_array(auth()->user()->tipo_usuario, ['admin', 'midia'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado. Apenas administradores e usuários de mídia podem acessar este recurso.'
                ], 403);
            }
            
            return redirect()->route('login')
                ->with('error', 'Acesso negado. Apenas administradores e usuários de mídia podem acessar este recurso.');
        }

        // Definir variáveis de sessão para compatibilidade
        session([
            'admin_logged_in' => true,
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'admin_email' => auth()->user()->email,
            'admin_tipo' => auth()->user()->tipo_usuario
        ]);

        return $next($request);
    }
}
