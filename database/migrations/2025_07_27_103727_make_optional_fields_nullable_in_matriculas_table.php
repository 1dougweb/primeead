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
            // Campos que são nullable na validação mas NOT NULL no banco
            $table->string('ultima_serie')->nullable()->change();
            $table->year('ano_conclusao')->nullable()->change();
            $table->string('escola_origem')->nullable()->change();
            $table->decimal('valor_matricula', 10, 2)->nullable()->change();
            $table->decimal('valor_mensalidade', 10, 2)->nullable()->change();
            $table->integer('dia_vencimento')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->string('ultima_serie')->nullable(false)->change();
            $table->year('ano_conclusao')->nullable(false)->change();
            $table->string('escola_origem')->nullable(false)->change();
            $table->decimal('valor_matricula', 10, 2)->nullable(false)->change();
            $table->decimal('valor_mensalidade', 10, 2)->nullable(false)->change();
            $table->integer('dia_vencimento')->nullable(false)->change();
        });
    }
};
