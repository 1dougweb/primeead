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
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();
            
            // Dados do template
            $table->string('name'); // Nome do template
            $table->text('description')->nullable(); // Descrição
            $table->longText('content'); // Conteúdo HTML do template
            $table->text('available_variables')->nullable(); // Variáveis disponíveis (JSON)
            
            // Configurações
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('validity_days')->default(30); // Dias de validade do link
            
            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['is_active', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
    }
};
