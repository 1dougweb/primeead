<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar configurações do Mercado Pago
        DB::table('system_settings')->insert([
            [
                'key' => 'mercadopago_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'category' => 'payments',
                'description' => 'Ativar integração com Mercado Pago',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'mercadopago_sandbox',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'payments',
                'description' => 'Usar ambiente de teste (sandbox)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'mercadopago_access_token',
                'value' => '',
                'type' => 'string',
                'category' => 'payments',
                'description' => 'Access Token do Mercado Pago',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'mercadopago_public_key',
                'value' => '',
                'type' => 'string',
                'category' => 'payments',
                'description' => 'Public Key do Mercado Pago',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'mercadopago_webhook_secret',
                'value' => '',
                'type' => 'string',
                'category' => 'payments',
                'description' => 'Webhook Secret para validação',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'payment_auto_charge',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'payments',
                'description' => 'Ativar cobrança automática',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'payment_email_notifications',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'payments',
                'description' => 'Ativar notificações por email',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'payment_whatsapp_notifications',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'payments',
                'description' => 'Ativar notificações por WhatsApp',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'payment_reminder_days',
                'value' => '3',
                'type' => 'integer',
                'category' => 'payments',
                'description' => 'Dias antes do vencimento para lembrete',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'payment_overdue_days',
                'value' => '7',
                'type' => 'integer',
                'category' => 'payments',
                'description' => 'Dias após vencimento para cobrança',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover configurações do Mercado Pago
        DB::table('system_settings')->whereIn('key', [
            'mercadopago_enabled',
            'mercadopago_sandbox',
            'mercadopago_access_token',
            'mercadopago_public_key',
            'mercadopago_webhook_secret',
            'payment_auto_charge',
            'payment_email_notifications',
            'payment_whatsapp_notifications',
            'payment_reminder_days',
            'payment_overdue_days'
        ])->delete();
    }
};
