<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WhatsAppTemplate;

class WhatsAppTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'confirmacao_inscricao',
                'description' => 'Confirmação de inscrição no curso',
                'content' => '🎓 *Parabéns, {{nome}}!*

Sua inscrição no curso de {{curso}} foi confirmada com sucesso! ✅

📋 *Próximos passos:*
• Aguarde contato da nossa equipe
• Prepare sua documentação
• Fique atento ao WhatsApp

📞 *Dúvidas?* Entre em contato conosco!

_Sua conquista começa aqui!_ 🚀',
                'category' => 'inscricao',
                'variables' => ['nome', 'curso'],
                'active' => true
            ],
            [
                'name' => 'lembrete_documentacao',
                'description' => 'Lembrete para envio de documentação',
                'content' => '📄 *Oi {{nome}}!*

Lembrando que estamos aguardando o envio da sua documentação para dar continuidade ao seu processo de certificação.

📋 *Documentos necessários:*
• RG e CPF
• Comprovante de residência
• Histórico escolar

⏰ *Prazo:* Até {{data_limite}}

Envie pelo WhatsApp ou portal do aluno.

_Estamos aqui para ajudar!_ 💪',
                'category' => 'lembrete',
                'variables' => ['nome', 'data_limite'],
                'active' => true
            ],
            [
                'name' => 'boas_vindas',
                'description' => 'Mensagem de boas-vindas para novos alunos',
                'content' => '🎉 *Bem-vindo(a) {{nome}}!*

É com muita alegria que recebemos você em nossa família educacional! 

🎓 *Você está prestes a conquistar seu diploma de {{curso}}!*

📚 *O que vem agora:*
• Análise da sua documentação
• Montagem do seu plano de estudos
• Acompanhamento personalizado

💡 *Dica:* Mantenha-se sempre motivado(a)! Sua dedicação é o que fará a diferença.

_Juntos, vamos longe!_ 🌟',
                'category' => 'geral',
                'variables' => ['nome', 'curso'],
                'active' => true
            ]
        ];

        foreach ($templates as $template) {
            WhatsAppTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
} 