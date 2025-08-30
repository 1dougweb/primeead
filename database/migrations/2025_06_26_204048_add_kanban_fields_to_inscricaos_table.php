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
        Schema::table('inscricaos', function (Blueprint $table) {
            $table->text('notas')->nullable()->after('locked_at');
            $table->json('todolist')->nullable()->after('notas');
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media')->after('todolist');
            $table->integer('kanban_order')->default(0)->after('prioridade');
            $table->timestamp('ultimo_contato')->nullable()->after('kanban_order');
            $table->timestamp('proximo_followup')->nullable()->after('ultimo_contato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscricaos', function (Blueprint $table) {
            $table->dropColumn([
                'notas',
                'todolist', 
                'prioridade',
                'kanban_order',
                'ultimo_contato',
                'proximo_followup'
            ]);
        });
    }
};
