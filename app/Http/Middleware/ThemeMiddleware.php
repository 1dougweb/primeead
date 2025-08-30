<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\ThemeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ThemeMiddleware
{
    public function __construct(
        private readonly ThemeService $themeService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $theme = $this->themeService->getCurrentTheme();
        $time = $this->themeService->getCurrentTime();
        
        View::share('currentTheme', $theme);
        View::share('currentTime', $time);

        return $next($request);
    }
} 