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
            // Adicionar apenas os campos que não existem
            if (!Schema::hasColumn('matriculas', 'forma_pagamento_mensalidade')) {
                $table->string('forma_pagamento_mensalidade')->nullable()->after('dia_vencimento');
            }
            if (!Schema::hasColumn('matriculas', 'parcelas_ativas')) {
                $table->boolean('parcelas_ativas')->default(false)->after('forma_pagamento_mensalidade');
            }
            if (!Schema::hasColumn('matriculas', 'parcelas_geradas')) {
                $table->integer('parcelas_geradas')->default(0)->after('parcelas_ativas');
            }
            if (!Schema::hasColumn('matriculas', 'parcelas_pagas')) {
                $table->integer('parcelas_pagas')->default(0)->after('parcelas_geradas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            // Remover apenas os campos que foram adicionados
            if (Schema::hasColumn('matriculas', 'forma_pagamento_mensalidade')) {
                $table->dropColumn('forma_pagamento_mensalidade');
            }
            if (Schema::hasColumn('matriculas', 'parcelas_ativas')) {
                $table->dropColumn('parcelas_ativas');
            }
            if (Schema::hasColumn('matriculas', 'parcelas_geradas')) {
                $table->dropColumn('parcelas_geradas');
            }
            if (Schema::hasColumn('matriculas', 'parcelas_pagas')) {
                $table->dropColumn('parcelas_pagas');
            }
        });
    }
};
