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
        Schema::create('parceiros', function (Blueprint $table) {
            $table->id();
            $table->string('nome_completo');
            $table->string('nome_fantasia')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('email')->unique();
            $table->string('telefone');
            $table->string('whatsapp')->nullable();
            $table->string('documento'); // CPF ou CNPJ
            $table->enum('tipo_documento', ['cpf', 'cnpj']);
            $table->string('cep');
            $table->string('endereco');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('cidade');
            $table->string('estado');
            $table->string('banco')->nullable();
            $table->string('agencia')->nullable();
            $table->string('conta')->nullable();
            $table->string('pix')->nullable();
            $table->text('experiencia_vendas')->nullable();
            $table->text('motivacao')->nullable();
            $table->enum('disponibilidade', ['meio_periodo', 'integral', 'fins_semana', 'flexivel']);
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado', 'ativo', 'inativo'])->default('pendente');
            $table->decimal('comissao_percentual', 5, 2)->default(10.00);
            $table->text('observacoes')->nullable();
            $table->timestamp('data_aprovacao')->nullable();
            $table->timestamp('ultimo_contato')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parceiros');
    }
};
