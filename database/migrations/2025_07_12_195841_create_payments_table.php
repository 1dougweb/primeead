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
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contact_id')->constrained('contacts')->onDelete('cascade'); // Relaciona com o lead/aluno
                // Removido: $table->foreignId('payment_plan_id')->nullable()->constrained('payment_plans')->onDelete('set null'); // Plano de pagamento
                $table->string('mercadopago_payment_id')->nullable(); // ID do pagamento no Mercado Pago
                $table->string('mercadopago_subscription_id')->nullable(); // ID da assinatura no Mercado Pago
                $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'refunded', 'charged_back'])->default('pending');
                $table->decimal('amount', 10, 2); // Valor do pagamento
                $table->string('currency', 3)->default('BRL'); // Moeda
                $table->string('payment_method')->nullable(); // Método de pagamento
                $table->text('description')->nullable(); // Descrição do pagamento
                $table->datetime('due_date')->nullable(); // Data de vencimento
                $table->datetime('paid_at')->nullable(); // Data do pagamento
                $table->integer('installment_number')->default(1); // Número da parcela
                $table->integer('total_installments')->default(1); // Total de parcelas
                $table->json('mercadopago_data')->nullable(); // Dados do Mercado Pago
                $table->json('metadata')->nullable(); // Dados adicionais
                $table->timestamps();
                
                // Índices para performance
                $table->index(['contact_id', 'status']);
                $table->index(['status', 'due_date']);
                $table->index('mercadopago_payment_id');
                $table->index('mercadopago_subscription_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
