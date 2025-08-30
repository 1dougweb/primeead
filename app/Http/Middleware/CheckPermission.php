<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Verificar se o usuário está autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Verificar se o usuário tem a permissão necessária
        if (!$user->hasPermissionTo($permission)) {
            // Log da tentativa de acesso negado
            \Log::warning('Acesso negado', [
                'user_id' => $user->id,
                'permission' => $permission,
                'route' => $request->route()->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Redirecionar com mensagem de erro
            return redirect()->back()->with('error', 'Você não tem permissão para acessar esta página.');
        }

        return $next($request);
    }
} 