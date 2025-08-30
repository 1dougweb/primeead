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
        // Adicionar configurações de tracking
        DB::table('system_settings')->insert([
            [
                'key' => 'google_tag_manager_id',
                'value' => '',
                'type' => 'string',
                'category' => 'tracking',
                'description' => 'ID do Google Tag Manager (GTM-XXXXXXX)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'facebook_pixel_id',
                'value' => '',
                'type' => 'string',
                'category' => 'tracking',
                'description' => 'ID do Facebook Pixel',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'enable_google_analytics',
                'value' => 'false',
                'type' => 'boolean',
                'category' => 'tracking',
                'description' => 'Ativar Google Analytics via GTM',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'enable_facebook_pixel',
                'value' => 'false',
                'type' => 'boolean',
                'category' => 'tracking',
                'description' => 'Ativar Facebook Pixel',
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
            'google_tag_manager_id',
            'facebook_pixel_id',
            'enable_google_analytics',
            'enable_facebook_pixel'
        ])->delete();
    }
};
