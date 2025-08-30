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
        Schema::table('matriculas', function (Blueprint $table) {
            // Remover o campo data_vencimento que está causando confusão
            // Vamos manter apenas dia_vencimento para as mensalidades recorrentes
            if (Schema::hasColumn('matriculas', 'data_vencimento')) {
                $table->dropColumn('data_vencimento');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            // Restaurar o campo caso precise fazer rollback
            if (!Schema::hasColumn('matriculas', 'data_vencimento')) {
                $table->date('data_vencimento')->nullable()->after('dia_vencimento');
            }
        });
    }
};
