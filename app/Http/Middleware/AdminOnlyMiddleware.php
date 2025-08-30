<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Allow access only if user is admin
        if (auth()->user()->isAdmin()) {
            return $next($request);
        }

        // Deny access if not admin
        return redirect()->route('dashboard')->with('error', 'Acesso negado. Apenas administradores podem acessar esta área.');
    }
}
