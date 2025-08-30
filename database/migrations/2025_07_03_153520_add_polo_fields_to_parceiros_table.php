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
        Schema::table('parceiros', function (Blueprint $table) {
            $table->string('modalidade_parceria')->nullable();
            $table->boolean('possui_estrutura')->default(false);
            $table->text('plano_negocio')->nullable();
            $table->boolean('tem_site')->default(false);
            $table->boolean('tem_experiencia_educacional')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parceiros', function (Blueprint $table) {
            $table->dropColumn([
                'modalidade_parceria',
                'possui_estrutura',
                'plano_negocio',
                'tem_site',
                'tem_experiencia_educacional'
            ]);
        });
    }
};
