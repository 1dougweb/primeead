<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParceiroMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->tipo_usuario !== 'parceiro') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado. Apenas parceiros podem acessar este recurso.'
                ], 403);
            }
            
            return redirect()->route('home')
                ->with('error', 'Acesso negado. Apenas parceiros podem acessar este recurso.');
        }

        return $next($request);
    }
} 