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
        // Adicionar configuração para prompt de templates de contratos
        DB::table('system_settings')->insert([
            [
                'key' => 'ai_contract_template_prompt',
                'value' => 'Crie um template de contrato HTML profissional com as seguintes especificações:

🎯 OBJETIVO: {objective}
📋 TIPO DE CONTRATO: {contractType}

📝 VARIÁVEIS DISPONÍVEIS (use no formato {{variavel}}):
{variablesText}

{referenceInstructions}

📋 REQUISITOS OBRIGATÓRIOS:
- Contrato deve ser para instituição de ensino brasileira
- Incluir identificação completa das partes (escola e aluno)
- Objeto do contrato (curso, modalidade, duração)
- Valores e formas de pagamento
- Direitos e obrigações de ambas as partes
- Política de cancelamento e reembolso
- Cláusulas sobre certificação e documentação
- Local e data para assinatura
- Foro e legislação aplicável

🎨 FORMATAÇÃO:
- Use HTML com CSS inline para formatação profissional
- Aparência formal similar a documentos jurídicos
- Fonte legível (Arial, Times New Roman)
- Margens adequadas para impressão (2cm)
- Cabeçalho com nome da instituição
- Numeração clara de cláusulas e subcláusulas
- Espaços adequados para assinatura das partes
- Quebras de página quando necessário

⚖️ ASPECTOS LEGAIS:
- Conforme legislação brasileira
- CDC (Código de Defesa do Consumidor)
- Lei de Diretrizes e Bases da Educação
- Linguagem jurídica apropriada e clara

{additionalInstructions}',
                'type' => 'text',
                'category' => 'ai',
                'description' => 'Prompt para templates de contratos',
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
        // Remover configuração
        DB::table('system_settings')->where('key', 'ai_contract_template_prompt')->delete();
    }
};
