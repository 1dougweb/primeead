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
        Schema::create('boleto_vias', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com pagamento
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            
            // Informações da via
            $table->integer('via_number'); // 1ª via, 2ª via, etc.
            $table->timestamp('generated_at');
            $table->timestamp('expires_at')->nullable();
            
            // Dados do boleto
            $table->string('boleto_url', 500)->nullable();
            $table->string('digitable_line', 100)->nullable();
            $table->text('barcode_content')->nullable();
            $table->string('financial_institution')->nullable();
            
            // Status da via
            $table->enum('status', ['active', 'expired', 'paid', 'cancelled'])->default('active');
            
            // Metadados adicionais
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Índices para performance
            $table->index(['payment_id', 'via_number']);
            $table->index(['status', 'expires_at']);
            $table->index('generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boleto_vias');
    }
};
