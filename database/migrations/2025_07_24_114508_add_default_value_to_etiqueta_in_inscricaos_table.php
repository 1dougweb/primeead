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
        // Adicionar valor padrão 'pendente' para registros existentes que tenham etiqueta NULL
        DB::table('inscricaos')
            ->whereNull('etiqueta')
            ->update(['etiqueta' => 'pendente']);

        // Alterar a coluna para ter valor padrão
        Schema::table('inscricaos', function (Blueprint $table) {
            $table->string('etiqueta', 50)->default('pendente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricaos', function (Blueprint $table) {
            $table->string('etiqueta', 50)->nullable()->change();
        });
    }
};
