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
        Schema::create('google_drive_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_id')->unique(); // ID do arquivo no Google Drive
            $table->string('mime_type');
            $table->string('web_view_link')->nullable();
            $table->string('web_content_link')->nullable();
            $table->string('thumbnail_link')->nullable();
            $table->bigInteger('size')->nullable();
            $table->string('path')->nullable(); // Caminho virtual no sistema
            $table->foreignId('created_by')->constrained('users');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_folder')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_trashed')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Adiciona a foreign key após a criação da tabela para evitar referência circular
            $table->foreign('parent_id')->references('id')->on('google_drive_files')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_drive_files');
    }
};
