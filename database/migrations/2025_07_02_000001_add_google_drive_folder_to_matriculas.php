<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->string('google_drive_folder_id')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn('google_drive_folder_id');
        });
    }
}; 