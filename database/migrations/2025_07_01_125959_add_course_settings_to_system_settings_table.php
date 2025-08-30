<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar configurações de cursos disponíveis
        SystemSetting::set(
            'available_courses',
            json_encode([
                'excel' => 'Excel Básico',
                'ingles' => 'Inglês Iniciante',
                'marketing' => 'Marketing Digital',
                'programacao' => 'Programação Web',
                'design' => 'Design Gráfico'
            ]),
            'json',
            'forms',
            'Lista de cursos disponíveis para seleção no formulário'
        );

        // Adicionar configurações de modalidades disponíveis
        SystemSetting::set(
            'available_modalities',
            json_encode([
                'ensino-fundamental' => 'Ensino Fundamental',
                'ensino-medio' => 'Ensino Médio',
                'ensino-fundamental-e-ensino-medio' => 'Ensino Fundamental + Ensino Médio'
            ]),
            'json',
            'forms',
            'Lista de modalidades disponíveis para seleção no formulário'
        );

        // Configuração para curso padrão selecionado
        SystemSetting::set(
            'default_course',
            'excel',
            'string',
            'forms',
            'Curso padrão selecionado no formulário'
        );

        // Configuração para modalidade padrão selecionada
        SystemSetting::set(
            'default_modality',
            'ensino-medio',
            'string',
            'forms',
            'Modalidade padrão selecionada no formulário'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover as configurações adicionadas
        SystemSetting::where('key', 'available_courses')->delete();
        SystemSetting::where('key', 'available_modalities')->delete();
        SystemSetting::where('key', 'default_course')->delete();
        SystemSetting::where('key', 'default_modality')->delete();
    }
};
