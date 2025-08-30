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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('tipo_usuario', ['admin', 'vendedor', 'colaborador', 'midia'])
                  ->default('colaborador')
                  ->after('email_verified_at');
            $table->boolean('ativo')->default(true)->after('tipo_usuario');
            $table->timestamp('ultimo_acesso')->nullable()->after('ativo');
            $table->string('criado_por')->nullable()->after('ultimo_acesso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tipo_usuario', 'ativo', 'ultimo_acesso', 'criado_por']);
        });
    }
};
