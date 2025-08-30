<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            // Alterar o campo status para usar enum mais específico
            $table->string('status')->default('pendente')->change();
            
            // Adicionar contadores
            $table->integer('open_count')->default(0)->after('tracking_code');
            $table->integer('click_count')->default(0)->after('open_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            $table->dropColumn([
                'open_count',
                'click_count'
            ]);
            
            // Reverter status para o valor original
            $table->string('status')->default('pending')->change();
        });
    }
};
