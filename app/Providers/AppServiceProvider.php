<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar localização do Carbon para português
        Carbon::setLocale('pt_BR');
        
        // Configurar paginação para usar Bootstrap 5
        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');
        
        // Add SVG mime type validation
        Validator::extend('svg', function ($attribute, $value, $parameters, $validator) {
            $file = $value->getPathname();
            $contents = file_get_contents($file);
            return str_starts_with($contents, '<?xml') || str_starts_with($contents, '<svg');
        });

        Validator::replacer('svg', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'O arquivo :attribute deve ser uma imagem SVG válida.');
        });
    }
}
