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
        Schema::table('matriculas', function (Blueprint $table) {
            // Verificar e remover campos antigos de documentos booleanos
            $colunas = [
                'doc_rg',
                'doc_cpf',
                'doc_comprovante_residencia',
                'doc_foto',
                'doc_historico',
                'doc_certificado',
                'doc_certidao',
                'doc_responsavel'
            ];
            
            // Filtrar apenas as colunas que existem
            $colunasExistentes = [];
            foreach ($colunas as $coluna) {
                if (Schema::hasColumn('matriculas', $coluna)) {
                    $colunasExistentes[] = $coluna;
                }
            }
            
            // Remover apenas se houver colunas existentes
            if (!empty($colunasExistentes)) {
                $table->dropColumn($colunasExistentes);
            }
        });
        
        Schema::table('matriculas', function (Blueprint $table) {
            // Campos da calculadora de pagamento
            if (!Schema::hasColumn('matriculas', 'tipo_boleto')) {
                $table->enum('tipo_boleto', ['avista', 'parcelado'])->nullable()->after('forma_pagamento');
            }
            
            if (!Schema::hasColumn('matriculas', 'valor_total_curso')) {
                $table->decimal('valor_total_curso', 10, 2)->nullable()->after('valor_matricula');
            }
            
            if (!Schema::hasColumn('matriculas', 'numero_parcelas')) {
                $table->integer('numero_parcelas')->nullable()->after('valor_mensalidade');
            }
            
            if (!Schema::hasColumn('matriculas', 'data_vencimento')) {
                $table->date('data_vencimento')->nullable()->after('dia_vencimento');
            }
            
            if (!Schema::hasColumn('matriculas', 'percentual_juros')) {
                $table->decimal('percentual_juros', 5, 2)->nullable()->after('data_vencimento');
            }
            
            if (!Schema::hasColumn('matriculas', 'desconto')) {
                $table->decimal('desconto', 5, 2)->nullable()->after('percentual_juros');
            }
            
            // Campos para documentos (caminhos dos arquivos)
            if (!Schema::hasColumn('matriculas', 'doc_rg_cpf')) {
                $table->json('doc_rg_cpf')->nullable()->after('observacoes');
            }
            
            if (!Schema::hasColumn('matriculas', 'doc_comprovante')) {
                $table->string('doc_comprovante')->nullable()->after('doc_rg_cpf');
            }
            
            if (!Schema::hasColumn('matriculas', 'doc_historico')) {
                $table->string('doc_historico')->nullable()->after('doc_comprovante');
            }
            
            if (!Schema::hasColumn('matriculas', 'doc_certificado')) {
                $table->string('doc_certificado')->nullable()->after('doc_historico');
            }
            
            if (!Schema::hasColumn('matriculas', 'doc_outros')) {
                $table->json('doc_outros')->nullable()->after('doc_certificado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            // Remover campos da calculadora
            $colunas = [
                'tipo_boleto',
                'valor_total_curso',
                'numero_parcelas',
                'data_vencimento',
                'percentual_juros',
                'desconto',
                'doc_rg_cpf',
                'doc_comprovante',
                'doc_historico',
                'doc_certificado',
                'doc_outros'
            ];
            
            // Filtrar apenas as colunas que existem
            $colunasExistentes = [];
            foreach ($colunas as $coluna) {
                if (Schema::hasColumn('matriculas', $coluna)) {
                    $colunasExistentes[] = $coluna;
                }
            }
            
            // Remover apenas se houver colunas existentes
            if (!empty($colunasExistentes)) {
                $table->dropColumn($colunasExistentes);
            }
        });
        
        Schema::table('matriculas', function (Blueprint $table) {
            // Recriar campos antigos
            if (!Schema::hasColumn('matriculas', 'doc_rg')) {
                $table->boolean('doc_rg')->default(false);
            }
            
            if (!Schema::hasColumn('matriculas', 'doc_cpf')) {
                $table->boolean('doc_cpf')->default(false);
            }
            
            if (!Schema::hasColumn('matriculas', 'doc_comprovante_residencia')) {
                $table->boolean('doc_comprovante_residencia')->default(false);
            }
            
            if (!Schema::hasColumn('matriculas', 'doc_foto')) {
                $table->boolean('doc_foto')->default(false);
            }
            
            if (!Schema::hasColumn('matriculas', 'doc_historico')) {
                $table->boolean('doc_historico')->default(false);
            }
            
            if (!Schema::hasColumn('matriculas', 'doc_certificado')) {
                $table->boolean('doc_certificado')->default(false);
            }
            
            if (!Schema::hasColumn('matriculas', 'doc_certidao')) {
                $table->boolean('doc_certidao')->default(false);
            }
            
            if (!Schema::hasColumn('matriculas', 'doc_responsavel')) {
                $table->boolean('doc_responsavel')->default(false);
            }
        });
    }
};
