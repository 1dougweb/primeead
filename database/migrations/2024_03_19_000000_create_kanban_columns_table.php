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
        Schema::create('kanban_columns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->default('primary');
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default system columns
        DB::table('kanban_columns')->insert([
            [
                'name' => '🟡 Pendente',
                'slug' => 'pendente',
                'color' => 'warning',
                'icon' => '🟡',
                'order' => 0,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '🔵 Contatado',
                'slug' => 'contatado',
                'color' => 'primary',
                'icon' => '🔵',
                'order' => 1,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '🟢 Interessado',
                'slug' => 'interessado',
                'color' => 'success',
                'icon' => '🟢',
                'order' => 2,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '🔴 Não Interessado',
                'slug' => 'nao_interessado',
                'color' => 'danger',
                'icon' => '🔴',
                'order' => 3,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '⭐ Matriculado',
                'slug' => 'matriculado',
                'color' => 'info',
                'icon' => '⭐',
                'order' => 4,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_columns');
    }
}; 