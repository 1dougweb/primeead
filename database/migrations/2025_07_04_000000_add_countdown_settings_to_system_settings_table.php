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
        // Adicionar configurações do countdown
        DB::table('system_settings')->insert([
            [
                'key' => 'countdown_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'countdown',
                'description' => 'Ativar contador regressivo da oferta',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_end_date',
                'value' => '2025-06-27',
                'type' => 'string',
                'category' => 'countdown',
                'description' => 'Data de término da oferta (formato: YYYY-MM-DD)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_end_time',
                'value' => '23:59',
                'type' => 'string',
                'category' => 'countdown',
                'description' => 'Horário de término da oferta (formato: HH:MM)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_timezone',
                'value' => 'America/Sao_Paulo',
                'type' => 'string',
                'category' => 'countdown',
                'description' => 'Fuso horário para o countdown',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_text',
                'value' => 'Somente até',
                'type' => 'string',
                'category' => 'countdown',
                'description' => 'Texto antes da data da oferta',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_discount_text',
                'value' => '50% OFF',
                'type' => 'string',
                'category' => 'countdown',
                'description' => 'Texto do desconto',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_price_original',
                'value' => 'R$ 284,90',
                'type' => 'string',
                'category' => 'countdown',
                'description' => 'Preço original da oferta',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_price_discount',
                'value' => 'R$ 89,90',
                'type' => 'string',
                'category' => 'countdown',
                'description' => 'Preço com desconto da oferta',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_price_installments_original',
                'value' => '24x',
                'type' => 'string',
                'category' => 'countdown',
                'description' => 'Número de parcelas do preço original',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_price_installments_discount',
                'value' => '12x',
                'type' => 'string',
                'category' => 'countdown',
                'description' => 'Número de parcelas do preço com desconto',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_pix_price',
                'value' => 'R$ 899,00',
                'type' => 'string',
                'category' => 'countdown',
                'description' => 'Preço no PIX',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_renewal_type',
                'value' => 'monthly',
                'type' => 'string',
                'category' => 'countdown',
                'description' => 'Tipo de renovação automática (monthly, weekly, daily, manual)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'countdown_auto_extend_days',
                'value' => '30',
                'type' => 'integer',
                'category' => 'countdown',
                'description' => 'Dias para estender automaticamente quando ativado',
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
        DB::table('system_settings')->whereIn('key', [
            'countdown_enabled',
            'countdown_end_date',
            'countdown_end_time',
            'countdown_timezone',
            'countdown_text',
            'countdown_discount_text',
            'countdown_price_original',
            'countdown_price_discount',
            'countdown_price_installments_original',
            'countdown_price_installments_discount',
            'countdown_pix_price',
            'countdown_renewal_type',
            'countdown_auto_extend_days'
        ])->delete();
    }
}; 