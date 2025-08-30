<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugCsrfMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Log CSRF token information for debugging
        Log::info('CSRF Debug', [
            'url' => $request->url(),
            'method' => $request->method(),
            'session_id' => session()->getId(),
            'has_csrf_token' => session()->has('_token'),
            'csrf_token' => csrf_token(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }
} 