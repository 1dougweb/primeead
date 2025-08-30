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
        Schema::table('payments', function (Blueprint $table) {
            // Controle de vias de boleto
            $table->integer('boleto_vias_count')->default(1)->after('status');
            $table->timestamp('last_boleto_generated_at')->nullable()->after('boleto_vias_count');
            $table->json('boleto_history')->nullable()->after('last_boleto_generated_at');
            
            // Campos para controle de segunda via
            $table->boolean('can_generate_second_via')->default(true)->after('boleto_history');
            $table->integer('max_boleto_vias')->default(3)->after('can_generate_second_via');
            $table->timestamp('boleto_expires_at')->nullable()->after('max_boleto_vias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'boleto_vias_count',
                'last_boleto_generated_at',
                'boleto_history',
                'can_generate_second_via',
                'max_boleto_vias',
                'boleto_expires_at'
            ]);
        });
    }
};
