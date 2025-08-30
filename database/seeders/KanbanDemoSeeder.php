<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inscricao;
use Carbon\Carbon;

class KanbanDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dados de exemplo para demonstrar o Kanban
        $leads = [
            [
                'nome' => 'Maria Silva',
                'email' => 'maria.silva@email.com',
                'telefone' => '(11) 99999-1001',
                'curso' => 'excel',
                'etiqueta' => 'pendente',
                'prioridade' => 'alta',
                'notas' => "[" . now()->format('d/m/Y H:i') . " - Admin]\nLead interessante, demonstrou muito interesse no curso de Excel.\n\nPrecisa entrar em contato urgente.",
                'todolist' => [
                    ['id' => uniqid(), 'text' => 'Ligar para apresentar o curso', 'completed' => false, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Enviar material informativo', 'completed' => false, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Verificar disponibilidade financeira', 'completed' => false, 'created_at' => now()->toISOString()]
                ],
                'proximo_followup' => now()->addDays(1),
                'kanban_order' => 1
            ],
            [
                'nome' => 'João Santos',
                'email' => 'joao.santos@email.com',
                'telefone' => '(11) 99999-1002',
                'curso' => 'ingles',
                'etiqueta' => 'contatado',
                'prioridade' => 'media',
                'notas' => "[" . now()->subHours(2)->format('d/m/Y H:i') . " - Admin]\nPrimeiro contato realizado. Cliente interessado mas precisa consultar a esposa.\n\n[" . now()->subDays(1)->format('d/m/Y H:i') . " - Admin]\nEnviado WhatsApp com informações do curso.",
                'todolist' => [
                    ['id' => uniqid(), 'text' => 'Aguardar retorno do cliente', 'completed' => true, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Enviar proposta personalizada', 'completed' => false, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Agendar reunião com casal', 'completed' => false, 'created_at' => now()->toISOString()]
                ],
                'ultimo_contato' => now()->subHours(2),
                'proximo_followup' => now()->addDays(2),
                'kanban_order' => 1
            ],
            [
                'nome' => 'Ana Costa',
                'email' => 'ana.costa@email.com',
                'telefone' => '(11) 99999-1003',
                'curso' => 'marketing',
                'etiqueta' => 'interessado',
                'prioridade' => 'urgente',
                'notas' => "[" . now()->subHours(4)->format('d/m/Y H:i') . " - Admin]\nCliente muito interessado! Quer começar na próxima turma.\nPossui experiência prévia em redes sociais.\n\n[" . now()->subDays(2)->format('d/m/Y H:i') . " - Admin]\nEnviou várias perguntas específicas sobre o curso.",
                'todolist' => [
                    ['id' => uniqid(), 'text' => 'Enviar cronograma detalhado', 'completed' => true, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Explicar formas de pagamento', 'completed' => true, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Fechar matrícula hoje', 'completed' => false, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Enviar contrato por email', 'completed' => false, 'created_at' => now()->toISOString()]
                ],
                'ultimo_contato' => now()->subHours(4),
                'proximo_followup' => now()->addHours(2),
                'kanban_order' => 1
            ],
            [
                'nome' => 'Carlos Oliveira',
                'email' => 'carlos.oliveira@email.com',
                'telefone' => '(11) 99999-1004',
                'curso' => 'excel',
                'etiqueta' => 'nao_interessado',
                'prioridade' => 'baixa',
                'notas' => "[" . now()->subDays(3)->format('d/m/Y H:i') . " - Admin]\nCliente não tem interesse no momento. Disse que talvez no próximo ano.\nManter na base para campanhas futuras.",
                'todolist' => [
                    ['id' => uniqid(), 'text' => 'Adicionar na lista de newsletter', 'completed' => true, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Marcar para recontato em 6 meses', 'completed' => false, 'created_at' => now()->toISOString()]
                ],
                'ultimo_contato' => now()->subDays(3),
                'kanban_order' => 1
            ],
            [
                'nome' => 'Fernanda Lima',
                'email' => 'fernanda.lima@email.com',
                'telefone' => '(11) 99999-1005',
                'curso' => 'ingles',
                'etiqueta' => 'matriculado',
                'prioridade' => 'baixa',
                'notas' => "[" . now()->subDays(1)->format('d/m/Y H:i') . " - Admin]\nMatrícula confirmada! Pagamento realizado via PIX.\nCliente muito animada para começar as aulas.\n\n[" . now()->subDays(2)->format('d/m/Y H:i') . " - Admin]\nEnviado dados para matrícula.",
                'todolist' => [
                    ['id' => uniqid(), 'text' => 'Confirmar pagamento', 'completed' => true, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Enviar dados de acesso ao curso', 'completed' => true, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Agendar primeira aula', 'completed' => false, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Adicionar no grupo do WhatsApp', 'completed' => false, 'created_at' => now()->toISOString()]
                ],
                'ultimo_contato' => now()->subDays(1),
                'kanban_order' => 1
            ],
            [
                'nome' => 'Roberto Mendes',
                'email' => 'roberto.mendes@email.com',
                'telefone' => '(11) 99999-1006',
                'curso' => 'marketing',
                'etiqueta' => 'pendente',
                'prioridade' => 'media',
                'notas' => "[" . now()->subMinutes(30)->format('d/m/Y H:i') . " - Admin]\nLead recém chegado. Preencheu formulário completo.\nDemonstrou interesse em horários noturnos.",
                'todolist' => [
                    ['id' => uniqid(), 'text' => 'Fazer primeiro contato', 'completed' => false, 'created_at' => now()->toISOString()],
                    ['id' => uniqid(), 'text' => 'Verificar disponibilidade de horários', 'completed' => false, 'created_at' => now()->toISOString()]
                ],
                'proximo_followup' => now()->addHours(4),
                'kanban_order' => 2
            ]
        ];

        foreach ($leads as $leadData) {
            Inscricao::create(array_merge($leadData, [
                'termos' => true,
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDays(rand(0, 7)),
                'updated_at' => now()
            ]));
        }

        $this->command->info('✅ Dados de demonstração do Kanban criados com sucesso!');
        $this->command->info('📋 Criados ' . count($leads) . ' leads com diferentes status, prioridades e informações.');
    }
}
