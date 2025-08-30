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
        // Adicionar configurações de logos
        DB::table('system_settings')->insert([
            [
                'key' => 'sidebar_logo_path',
                'value' => '/assets/images/logotipo-dark.svg',
                'type' => 'string',
                'category' => 'appearance',
                'description' => 'Caminho para o logo do sidebar',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'login_logo_path',
                'value' => '/assets/images/logotipo-dark.svg',
                'type' => 'string',
                'category' => 'appearance',
                'description' => 'Caminho para o logo da página de login',
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
            'sidebar_logo_path',
            'login_logo_path'
        ])->delete();
    }
};
