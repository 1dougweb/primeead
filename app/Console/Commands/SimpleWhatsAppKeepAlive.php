<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class SimpleWhatsAppKeepAlive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:keep-alive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Keep-alive simples para WhatsApp - apenas verifica se está conectado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $service = new WhatsAppService();
            
            if (!$service->hasValidSettings()) {
                $this->info('WhatsApp não configurado');
                return 0;
            }

            // Keep-alive simples
            $connected = $service->keepAlive();
            
            if ($connected) {
                $this->info('✅ WhatsApp conectado');
            } else {
                $this->warn('⚠️ WhatsApp desconectado');
                
                // Apenas verificar se precisa reconectar, sem fazer automaticamente
                if ($service->needsReconnection()) {
                    $this->warn('💡 Acesse /admin/whatsapp para reconectar');
                }
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
            Log::error('Erro no keep-alive WhatsApp: ' . $e->getMessage());
            return 1;
        }
    }
} 