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
        Schema::table('kanban_columns', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Remover unique constraint do slug para permitir slugs duplicados entre usuários
            $table->dropUnique(['slug']);
            
            // Adicionar unique constraint composto para slug + user_id
            $table->unique(['slug', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kanban_columns', function (Blueprint $table) {
            $table->dropUnique(['slug', 'user_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            
            // Restaurar unique constraint original do slug
            $table->unique('slug');
        });
    }
};
