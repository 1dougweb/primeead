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
        // First, get all existing values to preserve them
        $inscricaos = DB::table('inscricaos')->select('id', 'etiqueta')->get();

        // Change the column type from ENUM to VARCHAR(50)
        Schema::table('inscricaos', function (Blueprint $table) {
            $table->string('etiqueta', 50)->change();
        });

        // Restore any values that might have been lost in the conversion
        foreach ($inscricaos as $inscricao) {
            DB::table('inscricaos')
                ->where('id', $inscricao->id)
                ->update(['etiqueta' => $inscricao->etiqueta]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, ensure all values are valid for the ENUM
        DB::table('inscricaos')
            ->whereNotIn('etiqueta', ['pendente', 'contatado', 'interessado', 'nao_interessado', 'matriculado'])
            ->update(['etiqueta' => 'pendente']);

        // Change back to ENUM
        Schema::table('inscricaos', function (Blueprint $table) {
            $table->enum('etiqueta', ['pendente', 'contatado', 'interessado', 'nao_interessado', 'matriculado'])->change();
        });
    }
};
