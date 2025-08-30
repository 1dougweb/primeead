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
        // Primeiro, modificar a coluna para aceitar o novo tipo
        DB::statement("ALTER TABLE users MODIFY COLUMN tipo_usuario ENUM('admin', 'vendedor', 'colaborador', 'midia', 'parceiro') DEFAULT 'colaborador'");
        
        // Adicionar coluna de referência ao parceiro
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('parceiro_id')->nullable()->after('tipo_usuario')
                  ->constrained('parceiros')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover a coluna de referência ao parceiro
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parceiro_id']);
            $table->dropColumn('parceiro_id');
        });
        
        // Reverter a coluna tipo_usuario para os tipos originais
        DB::statement("ALTER TABLE users MODIFY COLUMN tipo_usuario ENUM('admin', 'vendedor', 'colaborador', 'midia') DEFAULT 'colaborador'");
    }
}; 