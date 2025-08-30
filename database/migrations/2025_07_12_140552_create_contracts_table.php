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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos
            $table->foreignId('matricula_id')->constrained('matriculas')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Dados do contrato
            $table->string('contract_number')->unique(); // Número único do contrato
            $table->string('title'); // Título do contrato
            $table->longText('content'); // Conteúdo do contrato (HTML)
            $table->longText('variables')->nullable(); // Variáveis utilizadas (JSON)
            
            // Controle de acesso
            $table->string('access_token')->unique(); // Token único para acesso
            $table->timestamp('access_expires_at')->nullable(); // Expiração do token
            $table->string('student_email'); // Email do aluno para validação
            $table->timestamp('email_verified_at')->nullable(); // Quando o email foi verificado
            
            // Status e controle
            $table->enum('status', ['draft', 'sent', 'viewed', 'signed', 'expired', 'cancelled'])
                  ->default('draft');
            $table->timestamp('sent_at')->nullable(); // Quando foi enviado
            $table->timestamp('viewed_at')->nullable(); // Quando foi visualizado
            $table->timestamp('signed_at')->nullable(); // Quando foi assinado
            
            // Assinatura digital
            $table->text('signature_data')->nullable(); // Dados da assinatura (base64)
            $table->string('signature_ip')->nullable(); // IP de onde foi assinado
            $table->text('signature_metadata')->nullable(); // Metadados da assinatura (JSON)
            
            // Auditoria
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['matricula_id', 'status']);
            $table->index(['access_token', 'access_expires_at']);
            $table->index(['student_email', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
