<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'webhook/*',
        'admin/permissions/migration/*',
        'dashboard/files/sync',
        'dashboard/files/check-changes',
        'dashboard/matriculas', // Temporário para resolver erro 419
        'dashboard/matriculas/*', // Temporário para resolver erro 419
        'test-csrf', // Debug route
        'refresh-csrf', // CSRF token refresh route
        'api/chat/*', // Excluir rotas da API do chat da verificação CSRF
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        // Log CSRF verification attempts
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('DELETE')) {
            Log::info('CSRF Verification', [
                'url' => $request->url(),
                'method' => $request->method(),
                'session_id' => session()->getId(),
                'has_token' => session()->has('_token'),
                'token_present' => $request->has('_token') || $request->hasHeader('X-CSRF-TOKEN'),
            ]);
        }

        return parent::handle($request, $next);
    }
} 