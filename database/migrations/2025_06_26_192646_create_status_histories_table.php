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
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscricao_id')->constrained('inscricaos')->onDelete('cascade');
            $table->string('status_anterior');
            $table->string('status_novo');
            $table->string('alterado_por'); // Email ou nome do usuário
            $table->string('tipo_usuario'); // admin, vendedor, etc.
            $table->text('observacoes')->nullable();
            $table->timestamp('data_alteracao');
            $table->timestamps();
            
            $table->index(['inscricao_id', 'data_alteracao']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};
