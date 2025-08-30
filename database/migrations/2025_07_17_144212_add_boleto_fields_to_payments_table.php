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
            $table->text('digitable_line')->nullable()->after('codigo_pix');
            $table->text('barcode_content')->nullable()->after('digitable_line');
            $table->string('financial_institution')->nullable()->after('barcode_content');
            $table->text('qr_code_base64')->nullable()->after('financial_institution');
            $table->text('ticket_url')->nullable()->after('qr_code_base64');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['digitable_line', 'barcode_content', 'financial_institution', 'qr_code_base64', 'ticket_url']);
        });
    }
};
