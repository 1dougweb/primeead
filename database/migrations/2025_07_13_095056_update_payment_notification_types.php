<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alterar o enum para incluir 'payment_created'
        DB::statement("ALTER TABLE payment_notifications MODIFY COLUMN type ENUM('payment_reminder', 'payment_overdue', 'payment_confirmed', 'payment_failed', 'payment_created', 'subscription_cancelled')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para o enum original
        DB::statement("ALTER TABLE payment_notifications MODIFY COLUMN type ENUM('payment_reminder', 'payment_overdue', 'payment_confirmed', 'payment_failed', 'subscription_cancelled')");
    }
};
