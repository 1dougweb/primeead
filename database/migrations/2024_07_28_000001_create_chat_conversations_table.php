<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique()->index();
            $table->string('user_email')->nullable()->index();
            $table->string('user_name')->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_email', 'status']);
            $table->index(['session_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_conversations');
    }
};
