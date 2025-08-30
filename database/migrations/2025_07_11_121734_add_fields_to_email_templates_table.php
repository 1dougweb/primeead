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
        Schema::table('email_templates', function (Blueprint $table) {
            $table->string('category')->default('custom')->after('content'); // welcome, followup, promotional, etc.
            $table->string('type')->default('marketing')->after('category'); // marketing, transactional, newsletter, etc.
            $table->boolean('is_ai_generated')->default(false)->after('type');
            $table->unsignedBigInteger('created_by')->nullable()->after('is_ai_generated');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            
            // Chaves estrangeiras
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['category', 'type', 'is_ai_generated', 'created_by', 'updated_by']);
        });
    }
};
