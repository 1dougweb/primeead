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
        // Verificar e adicionar apenas configurações específicas para sandbox do Mercado Pago que não existem
        $settingsToAdd = [
            [
                'key' => 'mercadopago_sandbox_access_token',
                'value' => '',
                'type' => 'string',
                'category' => 'payments',
                'description' => 'Access Token do Mercado Pago para ambiente sandbox',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'mercadopago_sandbox_public_key',
                'value' => '',
                'type' => 'string',
                'category' => 'payments',
                'description' => 'Public Key do Mercado Pago para ambiente sandbox',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($settingsToAdd as $setting) {
            // Verificar se a chave já existe antes de inserir
            $exists = DB::table('system_settings')
                ->where('key', $setting['key'])
                ->exists();
                
            if (!$exists) {
                DB::table('system_settings')->insert($setting);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover apenas configurações sandbox específicas
        DB::table('system_settings')->whereIn('key', [
            'mercadopago_sandbox_access_token',
            'mercadopago_sandbox_public_key'
        ])->delete();
    }
}; 