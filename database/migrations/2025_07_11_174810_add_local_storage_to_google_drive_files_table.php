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
        Schema::table('google_drive_files', function (Blueprint $table) {
            $table->string('local_path')->nullable()->after('thumbnail_link');
            $table->boolean('is_local')->default(false)->after('local_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('google_drive_files', function (Blueprint $table) {
            $table->dropColumn(['local_path', 'is_local']);
        });
    }
};
