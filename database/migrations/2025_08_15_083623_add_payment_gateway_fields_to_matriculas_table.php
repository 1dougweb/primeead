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
        Schema::table('matriculas', function (Blueprint $table) {
            // Campo para gateway de pagamento
            $table->enum('payment_gateway', ['mercado_pago', 'asas', 'infiny_pay', 'cora'])
                  ->default('mercado_pago')
                  ->after('forma_pagamento');
            
            // Campo para informações bancárias (link ou dados do banco)
            $table->text('bank_info')->nullable()->after('payment_gateway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn(['payment_gateway', 'bank_info']);
        });
    }
};
