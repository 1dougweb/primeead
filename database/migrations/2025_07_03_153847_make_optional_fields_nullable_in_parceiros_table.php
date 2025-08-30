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
            // Tornar campos opcionais como nullable
            $table->string('telefone')->nullable()->change();
            $table->string('documento')->nullable()->change();
            $table->string('tipo_documento')->nullable()->change();
            $table->string('cep')->nullable()->change();
            $table->string('endereco')->nullable()->change();
            $table->string('numero')->nullable()->change();
            $table->string('bairro')->nullable()->change();
            $table->string('cidade')->nullable()->change();
            $table->string('estado')->nullable()->change();
            $table->string('disponibilidade')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parceiros', function (Blueprint $table) {
            // Reverter para not null (cuidado com dados existentes)
            $table->string('telefone')->nullable(false)->change();
            $table->string('documento')->nullable(false)->change();
            $table->string('tipo_documento')->nullable(false)->change();
            $table->string('cep')->nullable(false)->change();
            $table->string('endereco')->nullable(false)->change();
            $table->string('numero')->nullable(false)->change();
            $table->string('bairro')->nullable(false)->change();
            $table->string('cidade')->nullable(false)->change();
            $table->string('estado')->nullable(false)->change();
            $table->string('disponibilidade')->nullable(false)->change();
        });
    }
};
