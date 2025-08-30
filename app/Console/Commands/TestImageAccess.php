<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestImageAccess extends Command
{
    protected $signature = 'images:test';
    protected $description = 'Testar acesso às imagens do footer';

    public function handle()
    {
        $this->info('🖼️ Testando acesso às imagens...');
        
        // Testar logos que funcionam
        $this->info('📋 Testando logos (que funcionam):');
        $logoPaths = [
            'assets/images/logotipo-dark.svg',
            'assets/images/anhangue-vip.svg'
        ];
        
        foreach ($logoPaths as $logoPath) {
            $fullPath = public_path($logoPath);
            if (file_exists($fullPath)) {
                $this->info("✅ Logo existe: {$logoPath}");
                $this->info("   Tamanho: " . filesize($fullPath) . " bytes");
                $this->info("   Permissões: " . substr(sprintf('%o', fileperms($fullPath)), -4));
            } else {
                $this->error("❌ Logo não existe: {$logoPath}");
            }
        }
        
        // Testar imagens do footer
        $this->info('📋 Testando imagens do footer:');
        $footerImages = [
            'footer/footer_image_1_1755020408.png',
            'footer/footer_image_2_1755020408.png',
            'footer/footer_image_3_1755020408.png'
        ];
        
        foreach ($footerImages as $imagePath) {
            // Testar via Storage facade
            $storagePath = storage_path('app/public/' . $imagePath);
            $publicPath = public_path('storage/' . $imagePath);
            
            $this->info("📁 Testando: {$imagePath}");
            
            if (file_exists($storagePath)) {
                $this->info("✅ Existe em storage: {$storagePath}");
                $this->info("   Tamanho: " . filesize($storagePath) . " bytes");
                $this->info("   Permissões: " . substr(sprintf('%o', fileperms($storagePath)), -4));
            } else {
                $this->error("❌ Não existe em storage: {$storagePath}");
            }
            
            if (file_exists($publicPath)) {
                $this->info("✅ Existe em public: {$publicPath}");
                $this->info("   Tamanho: " . filesize($publicPath) . " bytes");
                $this->info("   Permissões: " . substr(sprintf('%o', fileperms($publicPath)), -4));
            } else {
                $this->error("❌ Não existe em public: {$publicPath}");
            }
            
            // Testar via Storage facade
            try {
                if (Storage::disk('public')->exists($imagePath)) {
                    $this->info("✅ Acessível via Storage facade");
                    $this->info("   URL: " . Storage::disk('public')->url($imagePath));
                } else {
                    $this->error("❌ Não acessível via Storage facade");
                }
            } catch (\Exception $e) {
                $this->error("❌ Erro ao acessar via Storage: " . $e->getMessage());
            }
            
            $this->info('');
        }
        
        // Testar URLs
        $this->info('🌐 Testando URLs:');
        $appUrl = config('app.url');
        $this->info("APP_URL: {$appUrl}");
        
        foreach ($footerImages as $imagePath) {
            $url = $appUrl . '/storage/' . $imagePath;
            $this->info("URL: {$url}");
            
            // Testar se a URL é acessível
            $context = stream_context_create([
                'http' => [
                    'method' => 'HEAD',
                    'timeout' => 5
                ]
            ]);
            
            $headers = @get_headers($url, 1, $context);
            if ($headers && strpos($headers[0], '200') !== false) {
                $this->info("✅ URL acessível");
            } else {
                $this->error("❌ URL não acessível");
                if ($headers) {
                    $this->error("   Status: " . $headers[0]);
                }
            }
        }
        
        $this->info('✅ Teste concluído!');
    }
}
