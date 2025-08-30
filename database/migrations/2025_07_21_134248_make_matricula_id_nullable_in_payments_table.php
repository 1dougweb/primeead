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
            // Tornar matricula_id nullable para permitir pagamentos de teste via API
            $table->unsignedBigInteger('matricula_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Reverter para não nullable
            $table->unsignedBigInteger('matricula_id')->nullable(false)->change();
        });
    }
};
