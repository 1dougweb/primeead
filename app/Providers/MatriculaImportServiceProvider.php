<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\MatriculaImportService;
use App\Services\CsvParserService;
use App\Repositories\MatriculaRepository;
use App\Models\Matricula;
use Illuminate\Support\ServiceProvider;

class MatriculaImportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar Repository
        $this->app->bind(MatriculaRepository::class, function ($app) {
            return new MatriculaRepository(new Matricula());
        });

        // Registrar CSV Parser Service
        $this->app->bind(CsvParserService::class, function ($app) {
            return new CsvParserService();
        });

        // Registrar Column Mapping Service
        $this->app->bind(\App\Services\ColumnMappingService::class, function ($app) {
            return new \App\Services\ColumnMappingService();
        });

        // Registrar Matricula Import Service
        $this->app->bind(MatriculaImportService::class, function ($app) {
            return new MatriculaImportService(
                $app->make(MatriculaRepository::class),
                $app->make(CsvParserService::class)
            );
        });

        // Registrar Matricula Export Service
        $this->app->bind(\App\Services\MatriculaExportService::class, function ($app) {
            return new \App\Services\MatriculaExportService(
                $app->make(MatriculaRepository::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
