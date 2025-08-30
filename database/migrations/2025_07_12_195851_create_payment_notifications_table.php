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
        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('cascade'); // Relaciona com pagamento
            $table->foreignId('contact_id')->constrained('contacts')->onDelete('cascade'); // Relaciona com o lead/aluno
            $table->enum('type', ['payment_reminder', 'payment_overdue', 'payment_confirmed', 'payment_failed', 'subscription_cancelled']); // Tipo de notificação
            $table->enum('channel', ['email', 'whatsapp', 'sms']); // Canal de notificação
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered', 'read'])->default('pending'); // Status da notificação
            $table->string('subject')->nullable(); // Assunto (para email)
            $table->text('message'); // Mensagem enviada
            $table->string('recipient'); // Destinatário (email ou telefone)
            $table->datetime('scheduled_at')->nullable(); // Data agendada para envio
            $table->datetime('sent_at')->nullable(); // Data de envio
            $table->datetime('delivered_at')->nullable(); // Data de entrega
            $table->datetime('read_at')->nullable(); // Data de leitura
            $table->string('external_id')->nullable(); // ID externo (WhatsApp, SMS, etc.)
            $table->text('error_message')->nullable(); // Mensagem de erro
            $table->json('metadata')->nullable(); // Dados adicionais
            $table->timestamps();
            
            // Índices para performance
            $table->index(['contact_id', 'type']);
            $table->index(['status', 'scheduled_at']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_notifications');
    }
};
