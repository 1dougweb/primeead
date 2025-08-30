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
        // Adicionar configurações de WhatsApp
        DB::table('system_settings')->insert([
            [
                'key' => 'whatsapp_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'whatsapp',
                'description' => 'Ativar botão flutuante do WhatsApp',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'whatsapp_number',
                'value' => '5511999999999',
                'type' => 'string',
                'category' => 'whatsapp',
                'description' => 'Número do WhatsApp (formato: 5511999999999)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'whatsapp_message',
                'value' => 'Olá! Tenho interesse no curso EJA. Podem me ajudar?',
                'type' => 'string',
                'category' => 'whatsapp',
                'description' => 'Mensagem padrão do WhatsApp',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'whatsapp_button_position',
                'value' => 'bottom-right',
                'type' => 'string',
                'category' => 'whatsapp',
                'description' => 'Posição do botão WhatsApp',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'whatsapp_button_color',
                'value' => '#25d366',
                'type' => 'string',
                'category' => 'whatsapp',
                'description' => 'Cor do botão WhatsApp',
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
            'whatsapp_enabled',
            'whatsapp_number',
            'whatsapp_message',
            'whatsapp_button_position',
            'whatsapp_button_color'
        ])->delete();
    }
};
