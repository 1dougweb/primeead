<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class ChatSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configurações do chat
        SystemSetting::set('landing_chat_enabled', true, 'boolean', 'landing_page', 'Ativar chat de suporte na landing page');
        SystemSetting::set('landing_chat_title', 'Precisa de ajuda com EJA Supletivo?', 'string', 'landing_page', 'Título do chat de suporte');
        SystemSetting::set('landing_chat_welcome_message', 'Olá! Me chamo Anne da Ensino Certo. Como posso te ajudar hoje? Posso orientar sobre matrículas, pagamentos, disciplinas e muito mais! 💡 Digite seu email para atendimento personalizado!', 'string', 'landing_page', 'Mensagem de boas-vindas do chat');
        SystemSetting::set('landing_chat_position', 'bottom-right', 'string', 'landing_page', 'Posição do chat na página');
        SystemSetting::set('landing_chat_color', '#007bff', 'string', 'landing_page', 'Cor do botão do chat');
        SystemSetting::set('landing_chat_icon', 'fas fa-graduation-cap', 'string', 'landing_page', 'Ícone do chat de suporte');
        
        // Configurações de contato
        SystemSetting::set('thank_you_contact_phone', '(11) 91701-2033', 'string', 'landing_page', 'Telefone de contato para suporte');
        SystemSetting::set('thank_you_contact_hours', 'Segunda a Sexta, 8h às 18h', 'string', 'landing_page', 'Horário de atendimento');
        SystemSetting::set('mail_from_address', 'contato@ensinocerto.com.br', 'string', 'landing_page', 'Email de contato para suporte');

        // Prompt padrão para suporte ao cliente
        SystemSetting::set('ai_support_prompt', SystemSetting::getDefaultSupportPrompt(), 'text', 'ai', 'Prompt padrão para o ChatGPT no atendimento ao cliente');
        
        $this->command->info('Configurações do chat configuradas com sucesso!');
    }
}
