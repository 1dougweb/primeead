<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ExecuteMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:single {migration : The migration file name to execute}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute a specific migration file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $migrationName = $this->argument('migration');
        $migrationPath = database_path('migrations');

        // Remover .php do nome se foi incluído
        $migrationName = str_replace('.php', '', $migrationName);
        
        // Debug: Mostrar o que estamos procurando
        $this->info("Searching for migration matching: {$migrationName}");
        $this->info("Looking in directory: {$migrationPath}");
        
        // Listar todos os arquivos de migração
        $allFiles = File::glob($migrationPath . '/*.php');
        $this->line("\nAvailable migrations:");
        foreach ($allFiles as $file) {
            $this->line(" - " . basename($file));
        }
        
        // Procurar o arquivo de migração de forma mais flexível
        $matchingFiles = array_filter($allFiles, function($file) use ($migrationName) {
            $basename = basename($file);
            return str_contains(strtolower($basename), strtolower($migrationName));
        });
        
        if (empty($matchingFiles)) {
            $this->error("\nMigration file not found: {$migrationName}");
            $this->line("\nTry using one of these filenames:");
            foreach ($allFiles as $file) {
                $this->line(" - " . basename($file, '.php'));
            }
            return 1;
        }

        if (count($matchingFiles) > 1) {
            $this->warn("\nMultiple migrations found matching: {$migrationName}");
            $this->line("\nPlease be more specific. Matching files:");
            foreach ($matchingFiles as $file) {
                $this->line(" - " . basename($file));
            }
            return 1;
        }

        $file = array_values($matchingFiles)[0];
        $filename = basename($file);

        // Confirmar execução
        if (!$this->confirm("\nAre you sure you want to run migration: {$filename}?")) {
            return 0;
        }

        try {
            // Executar a migração
            $this->info("\nRunning migration: {$filename}");
            $output = [];
            $command = "php artisan migrate --path=database/migrations/{$filename} 2>&1";
            
            // Debug: Mostrar comando que será executado
            $this->line("\nExecuting command: " . $command);
            
            exec($command, $output);
            
            // Mostrar output
            $this->line("\nMigration output:");
            foreach ($output as $line) {
                $this->line($line);
            }

            $this->info("\nMigration completed successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error("\nError running migration: " . $e->getMessage());
            return 1;
        }
    }
} 