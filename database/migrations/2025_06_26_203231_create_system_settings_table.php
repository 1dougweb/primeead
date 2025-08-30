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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->string('category')->default('general');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Inserir configurações padrão
        DB::table('system_settings')->insert([
            [
                'key' => 'lead_cooldown_minutes',
                'value' => '2',
                'type' => 'integer',
                'category' => 'leads',
                'description' => 'Tempo em minutos que um usuário deve aguardar após pegar um lead para pegar outro',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'auto_unlock_hours',
                'value' => '24',
                'type' => 'integer',
                'category' => 'leads',
                'description' => 'Tempo em horas para destravar automaticamente leads inativos',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'max_leads_per_user',
                'value' => '10',
                'type' => 'integer',
                'category' => 'leads',
                'description' => 'Número máximo de leads que um usuário pode ter travados simultaneamente',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'enable_lead_notifications',
                'value' => 'true',
                'type' => 'boolean',
                'category' => 'notifications',
                'description' => 'Ativar notificações de novos leads',
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
        Schema::dropIfExists('system_settings');
    }
};
