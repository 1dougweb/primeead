<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckStorageStatus extends Command
{
    protected $signature = 'storage:check';
    protected $description = 'Verificar status do storage e symlinks';

    public function handle()
    {
        $this->info('🔍 Verificando status do storage...');
        
        // Verificar symlink
        $symlinkPath = public_path('storage');
        if (is_link($symlinkPath)) {
            $this->info('✅ Symlink existe: ' . $symlinkPath);
            $this->info('   → Aponta para: ' . readlink($symlinkPath));
        } else {
            $this->error('❌ Symlink não existe: ' . $symlinkPath);
        }
        
        // Verificar pasta storage/app/public
        $storagePath = storage_path('app/public');
        if (is_dir($storagePath)) {
            $this->info('✅ Pasta storage existe: ' . $storagePath);
        } else {
            $this->error('❌ Pasta storage não existe: ' . $storagePath);
        }
        
        // Verificar pasta footer
        $footerPath = storage_path('app/public/footer');
        if (is_dir($footerPath)) {
            $this->info('✅ Pasta footer existe: ' . $footerPath);
            $files = scandir($footerPath);
            $imageFiles = array_filter($files, function($file) {
                return in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            });
            if (!empty($imageFiles)) {
                $this->info('   📁 Arquivos encontrados: ' . implode(', ', $imageFiles));
            } else {
                $this->warn('   ⚠️ Nenhuma imagem encontrada');
            }
        } else {
            $this->error('❌ Pasta footer não existe: ' . $footerPath);
        }
        
        // Verificar permissões
        $this->info('🔐 Verificando permissões...');
        if (is_readable($footerPath)) {
            $this->info('✅ Pasta footer é legível');
        } else {
            $this->error('❌ Pasta footer não é legível');
        }
        
        // Testar URL do storage
        $this->info('🌐 Testando URL do storage...');
        $appUrl = config('app.url');
        $this->info('   APP_URL: ' . $appUrl);
        
        // Verificar configuração do filesystem
        $this->info('⚙️ Configuração do filesystem...');
        $defaultDisk = config('filesystems.default');
        $publicDisk = config('filesystems.disks.public');
        $this->info('   Default disk: ' . $defaultDisk);
        $this->info('   Public disk URL: ' . ($publicDisk['url'] ?? 'não configurado'));
        
        // Verificar se as imagens são acessíveis via Storage facade
        $this->info('📁 Testando acesso via Storage facade...');
        try {
            $files = Storage::disk('public')->files('footer');
            if (!empty($files)) {
                $this->info('✅ Arquivos acessíveis via Storage: ' . implode(', ', $files));
            } else {
                $this->warn('⚠️ Nenhum arquivo encontrado via Storage facade');
            }
        } catch (\Exception $e) {
            $this->error('❌ Erro ao acessar via Storage: ' . $e->getMessage());
        }
        
        $this->info('✅ Verificação concluída!');
    }
}
