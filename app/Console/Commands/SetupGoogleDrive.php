<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupGoogleDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-drive:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Google Drive integration with migrations and permissions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Configurando integração com Google Drive...');
        
        // Run migrations
        $this->info('📦 Executando migrações...');
        Artisan::call('migrate');
        $this->line(Artisan::output());
        
        // Run Google Drive permissions seeder
        $this->info('🔐 Configurando permissões...');
        Artisan::call('db:seed', ['--class' => 'GoogleDrivePermissionsSeeder']);
        $this->line(Artisan::output());
        
        $this->info('✅ Integração com Google Drive configurada com sucesso!');
        
        $this->newLine();
        $this->warn('📋 Próximos passos:');
        $this->line('1. Configure as variáveis de ambiente no arquivo .env:');
        $this->line('   - GOOGLE_DRIVE_CLIENT_ID=seu_client_id');
        $this->line('   - GOOGLE_DRIVE_CLIENT_SECRET=seu_client_secret');
        $this->line('   - GOOGLE_DRIVE_REFRESH_TOKEN=seu_refresh_token');
        $this->line('   - GOOGLE_DRIVE_FOLDER_ID=id_da_pasta_raiz (opcional)');
        $this->newLine();
        $this->line('2. Siga o guia de configuração do Google Drive API para obter as credenciais');
        $this->line('3. Teste a integração criando uma matrícula e fazendo upload de documentos');
        
        return Command::SUCCESS;
    }
}
