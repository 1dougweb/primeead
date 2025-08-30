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
        Schema::table('payments', function (Blueprint $table) {
            // Links de pagamento do Mercado Pago
            $table->string('boleto_url')->nullable()->after('init_point')->comment('URL do boleto em PDF');
            $table->string('pix_qr_code')->nullable()->after('boleto_url')->comment('Código PIX Copia e Cola');
            $table->string('pix_qr_code_base64')->nullable()->after('pix_qr_code')->comment('Imagem do QR Code PIX em Base64');
            $table->string('payment_link')->nullable()->after('pix_qr_code_base64')->comment('Link principal de pagamento (boleto, PIX ou cartão)');
            $table->string('payment_type')->nullable()->after('payment_link')->comment('Tipo de link: boleto, pix, cartao, generic');
            $table->timestamp('payment_link_expires_at')->nullable()->after('payment_type')->comment('Data de expiração do link de pagamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'boleto_url',
                'pix_qr_code', 
                'pix_qr_code_base64',
                'payment_link',
                'payment_type',
                'payment_link_expires_at'
            ]);
        });
    }
};
