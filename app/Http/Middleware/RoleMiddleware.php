<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role, string $guard = null): Response
    {
        // Verificar se o usuário está autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Verificar se o usuário tem o role necessário
        if (!$user->hasRole($role, $guard)) {
            // Log da tentativa de acesso negado
            \Log::warning('Acesso negado por role', [
                'user_id' => $user->id,
                'required_role' => $role,
                'user_roles' => $user->roles->pluck('name')->toArray(),
                'route' => $request->route()->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Redirecionar com mensagem de erro
            return redirect()->back()->with('error', 'Você não tem o papel necessário para acessar esta página.');
        }

        return $next($request);
    }
}
