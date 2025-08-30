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
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos
            $table->foreignId('inscricao_id')->nullable()->constrained('inscricaos')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Dados pessoais
            $table->string('nome_completo');
            $table->date('data_nascimento');
            $table->string('cpf', 14)->unique();
            $table->string('rg');
            $table->string('orgao_emissor');
            $table->enum('sexo', ['M', 'F', 'O']);
            $table->enum('estado_civil', ['solteiro', 'casado', 'divorciado', 'viuvo', 'outro']);
            $table->string('nacionalidade');
            $table->string('naturalidade');
            
            // Endereço
            $table->string('cep', 9);
            $table->string('logradouro');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('cidade');
            $table->string('estado', 2);
            
            // Contato
            $table->string('telefone_fixo')->nullable();
            $table->string('telefone_celular');
            $table->string('email');
            $table->string('nome_mae');
            $table->string('nome_pai')->nullable();
            
            // Dados acadêmicos
            $table->string('modalidade');
            $table->string('curso');
            $table->enum('turno', ['matutino', 'vespertino', 'noturno']);
            $table->string('ultima_serie');
            $table->year('ano_conclusao');
            $table->string('escola_origem');
            
            // Dados da matrícula
            $table->string('numero_matricula')->unique();
            $table->enum('status', ['pre_matricula', 'matricula_confirmada', 'cancelada', 'trancada', 'concluida'])
                  ->default('pre_matricula');
            $table->enum('forma_pagamento', ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto']);
            $table->decimal('valor_matricula', 10, 2);
            $table->decimal('valor_mensalidade', 10, 2);
            $table->integer('dia_vencimento');
            $table->text('observacoes')->nullable();
            
            // Documentos entregues
            $table->boolean('doc_rg')->default(false);
            $table->boolean('doc_cpf')->default(false);
            $table->boolean('doc_comprovante_residencia')->default(false);
            $table->boolean('doc_foto')->default(false);
            $table->boolean('doc_historico')->default(false);
            $table->boolean('doc_certificado')->default(false);
            $table->boolean('doc_certidao')->default(false);
            $table->boolean('doc_responsavel')->default(false);
            
            // Controle
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
}; 