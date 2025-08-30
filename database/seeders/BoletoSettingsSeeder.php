<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class BoletoSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configurações padrão para boletos
        SystemSetting::set('boleto_max_vias', 3, 'integer', 'boleto', 'Número máximo de vias permitidas por boleto');
        SystemSetting::set('boleto_expiration_days', 3, 'integer', 'boleto', 'Dias para expiração de nova via');
        SystemSetting::set('boleto_allow_second_via', true, 'boolean', 'boleto', 'Permitir geração de segunda via');
        SystemSetting::set('boleto_auto_cancel_previous', true, 'boolean', 'boleto', 'Cancelar vias anteriores automaticamente');
        SystemSetting::set('boleto_notification_email', true, 'boolean', 'boleto', 'Enviar email ao gerar nova via');
        
        // Configurações de Mercado Pago para boletos
        SystemSetting::set('boleto_mercadopago_sandbox', true, 'boolean', 'boleto', 'Usar ambiente sandbox do Mercado Pago');
        SystemSetting::set('boleto_mercadopago_timeout', 60, 'integer', 'boleto', 'Timeout em segundos para API do Mercado Pago');
        SystemSetting::set('boleto_mercadopago_retry_attempts', 3, 'integer', 'boleto', 'Tentativas de retry para API do Mercado Pago');
        
        // Token de acesso do Mercado Pago (sandbox para testes)
        SystemSetting::set('mercado_pago_access_token', 'TEST-1234567890abcdef-1234-1234-1234-1234567890ab', 'string', 'mercado_pago', 'Token de acesso para API do Mercado Pago (sandbox)');
        SystemSetting::set('mercado_pago_public_key', 'TEST-12345678-1234-1234-1234-123456789012', 'string', 'mercado_pago', 'Chave pública do Mercado Pago (sandbox)');
        
        // Configurações de notificação
        SystemSetting::set('boleto_notification_template', 'boleto_second_via', 'string', 'boleto', 'Template de email para segunda via');
        SystemSetting::set('boleto_admin_notification', true, 'boolean', 'boleto', 'Notificar administradores sobre novas vias');
        
        $this->command->info('Configurações de boleto configuradas com sucesso!');
    }
}
