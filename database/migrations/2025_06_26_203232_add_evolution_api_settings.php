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
        // Configurações da Evolution API
        $settings = [
            [
                'key' => 'evolution_api_base_url',
                'value' => '',
                'type' => 'string',
                'category' => 'evolution_api',
                'description' => 'URL base da Evolution API'
            ],
            [
                'key' => 'evolution_api_key',
                'value' => '',
                'type' => 'string',
                'category' => 'evolution_api',
                'description' => 'Chave da Evolution API'
            ],
            [
                'key' => 'evolution_api_instance',
                'value' => 'default',
                'type' => 'string',
                'category' => 'evolution_api',
                'description' => 'Nome da instância da Evolution API'
            ],
            [
                'key' => 'evolution_api_connected',
                'value' => 'false',
                'type' => 'boolean',
                'category' => 'evolution_api',
                'description' => 'Status da conexão da Evolution API'
            ],
            [
                'key' => 'evolution_api_last_connection',
                'value' => '',
                'type' => 'string',
                'category' => 'evolution_api',
                'description' => 'Última conexão da Evolution API'
            ]
        ];

        // Adicionar apenas se não existir
        foreach ($settings as $setting) {
            $exists = DB::table('system_settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('system_settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'evolution_api_base_url',
            'evolution_api_key',
            'evolution_api_instance',
            'evolution_api_connected',
            'evolution_api_last_connection'
        ])->delete();
    }
}; 