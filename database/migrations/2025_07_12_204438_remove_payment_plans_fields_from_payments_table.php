<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Remover campos relacionados a planos
            if (Schema::hasColumn('payments', 'payment_plan_id')) {
                // Verificar se a chave estrangeira existe usando SQL bruto
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'payments' 
                    AND COLUMN_NAME = 'payment_plan_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['payment_plan_id']);
                }
                $table->dropColumn('payment_plan_id');
            }
            
            if (Schema::hasColumn('payments', 'contact_id')) {
                // Verificar se a chave estrangeira existe usando SQL bruto
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'payments' 
                    AND COLUMN_NAME = 'contact_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['contact_id']);
                }
                $table->dropColumn('contact_id');
            }
            
            // Remover campos antigos e renomear para o padrão brasileiro
            if (Schema::hasColumn('payments', 'amount')) {
                $table->dropColumn('amount');
            }
            
            if (Schema::hasColumn('payments', 'currency')) {
                $table->dropColumn('currency');
            }
            
            if (Schema::hasColumn('payments', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            
            if (Schema::hasColumn('payments', 'description')) {
                $table->dropColumn('description');
            }
            
            if (Schema::hasColumn('payments', 'due_date')) {
                $table->dropColumn('due_date');
            }
            
            if (Schema::hasColumn('payments', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            
            if (Schema::hasColumn('payments', 'installment_number')) {
                $table->dropColumn('installment_number');
            }
            
            if (Schema::hasColumn('payments', 'total_installments')) {
                $table->dropColumn('total_installments');
            }
            
            if (Schema::hasColumn('payments', 'mercadopago_payment_id')) {
                $table->dropColumn('mercadopago_payment_id');
            }
            
            if (Schema::hasColumn('payments', 'mercadopago_subscription_id')) {
                $table->dropColumn('mercadopago_subscription_id');
            }
            
            if (Schema::hasColumn('payments', 'metadata')) {
                $table->dropColumn('metadata');
            }
            
            // Adicionar campos necessários se não existirem
            if (!Schema::hasColumn('payments', 'matricula_id')) {
                $table->unsignedBigInteger('matricula_id')->after('id');
                $table->foreign('matricula_id')->references('id')->on('matriculas')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('payments', 'valor')) {
                $table->decimal('valor', 10, 2)->after('matricula_id');
            }
            
            if (!Schema::hasColumn('payments', 'forma_pagamento')) {
                $table->enum('forma_pagamento', ['pix', 'cartao_credito', 'boleto'])->after('valor');
            }
            
            if (!Schema::hasColumn('payments', 'data_vencimento')) {
                $table->datetime('data_vencimento')->after('forma_pagamento');
            }
            
            if (!Schema::hasColumn('payments', 'data_pagamento')) {
                $table->datetime('data_pagamento')->nullable()->after('data_vencimento');
            }
            
            if (!Schema::hasColumn('payments', 'descricao')) {
                $table->string('descricao')->nullable()->after('data_pagamento');
            }
            
            if (!Schema::hasColumn('payments', 'numero_parcela')) {
                $table->integer('numero_parcela')->default(1)->after('descricao');
            }
            
            if (!Schema::hasColumn('payments', 'total_parcelas')) {
                $table->integer('total_parcelas')->default(1)->after('numero_parcela');
            }
            
            if (!Schema::hasColumn('payments', 'mercadopago_id')) {
                $table->string('mercadopago_id')->nullable()->after('total_parcelas');
            }
            
            if (!Schema::hasColumn('payments', 'mercadopago_status')) {
                $table->string('mercadopago_status')->nullable()->after('mercadopago_id');
            }
            
            if (!Schema::hasColumn('payments', 'observacoes')) {
                $table->text('observacoes')->nullable()->after('mercadopago_data');
            }
            
            // Ajustar status para usar valores corretos
            if (Schema::hasColumn('payments', 'status')) {
                $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'cancelled'])->default('pending')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Reverter alterações
            if (Schema::hasColumn('payments', 'matricula_id')) {
                $table->dropForeign(['matricula_id']);
                $table->dropColumn('matricula_id');
            }
            
            $table->dropColumn([
                'valor', 'forma_pagamento', 'data_vencimento', 'data_pagamento',
                'descricao', 'numero_parcela', 'total_parcelas', 'mercadopago_id',
                'mercadopago_status', 'observacoes'
            ]);
            
            // Restaurar campos antigos
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BRL');
            $table->enum('payment_method', ['pix', 'credit_card', 'boleto']);
            $table->string('description')->nullable();
            $table->datetime('due_date');
            $table->datetime('paid_at')->nullable();
            $table->integer('installment_number')->default(1);
            $table->integer('total_installments')->default(1);
            $table->string('mercadopago_payment_id')->nullable();
            $table->string('mercadopago_subscription_id')->nullable();
            $table->json('metadata')->nullable();
            
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
        });
    }
};
