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
        Schema::table('contracts', function (Blueprint $table) {
            // Campos para assinatura da escola
            $table->text('school_signature_data')->nullable()->after('signature_metadata');
            $table->string('school_signature_name')->nullable()->after('school_signature_data');
            $table->string('school_signature_title')->nullable()->after('school_signature_name');
            $table->timestamp('school_signed_at')->nullable()->after('school_signature_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'school_signature_data',
                'school_signature_name',
                'school_signature_title',
                'school_signed_at'
            ]);
        });
    }
};
