<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;

class SetupAiSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:setup {--reset : Reset all AI settings to defaults}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup AI settings for ChatGPT integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('reset')) {
            $this->info('🔄 Resetando configurações de IA para valores padrão...');
            $this->resetAiSettings();
        } else {
            $this->info('🚀 Configurando configurações de IA...');
            $this->setupAiSettings();
        }

        $this->info('✅ Configurações de IA configuradas com sucesso!');
        
        // Mostrar configurações atuais
        $this->showCurrentSettings();
    }

    /**
     * Configurar configurações de IA
     */
    private function setupAiSettings()
    {
        // Configurações básicas do ChatGPT
        SystemSetting::set('ai_api_key', '', 'string', 'ai', 'API Key do ChatGPT');
        SystemSetting::set('ai_model', 'gpt-4o-mini', 'string', 'ai', 'Modelo do ChatGPT');
        SystemSetting::set('ai_is_active', false, 'boolean', 'ai', 'Status de ativação do ChatGPT');
        
        // Prompt do sistema
        SystemSetting::set('ai_system_prompt', 'Você é um especialista em email marketing educacional e designer de templates HTML profissionais. Crie conteúdo persuasivo, responsivo e otimizado para conversão, sempre incluindo as variáveis fornecidas pelo usuário.', 'text', 'ai', 'Prompt do sistema para o ChatGPT');
        
        // Prompt para templates de email
        SystemSetting::set('ai_email_template_prompt', $this->getDefaultEmailTemplatePrompt(), 'text', 'ai', 'Prompt para templates de email');
        
        // Prompt para templates de WhatsApp
        SystemSetting::set('ai_whatsapp_template_prompt', $this->getDefaultWhatsAppTemplatePrompt(), 'text', 'ai', 'Prompt para templates de WhatsApp');
        
        // Prompt para templates de contratos
        SystemSetting::set('ai_contract_template_prompt', $this->getDefaultContractTemplatePrompt(), 'text', 'ai', 'Prompt para templates de contratos');
        
        // Prompt para templates de pagamento
        SystemSetting::set('ai_payment_template_prompt', $this->getDefaultPaymentTemplatePrompt(), 'text', 'ai', 'Prompt para templates de pagamento');
        
        // Prompt para templates de inscrição
        SystemSetting::set('ai_enrollment_template_prompt', $this->getDefaultEnrollmentTemplatePrompt(), 'text', 'ai', 'Prompt para templates de inscrição');
        
        // Prompt para templates de matrícula
        SystemSetting::set('ai_matriculation_template_prompt', $this->getDefaultMatriculationTemplatePrompt(), 'text', 'ai', 'Prompt para templates de matrícula');
        
        // Prompt para suporte ao cliente
        SystemSetting::set('ai_support_prompt', SystemSetting::getDefaultSupportPrompt(), 'text', 'ai', 'Prompt padrão para o ChatGPT no atendimento ao cliente');
        
        $this->info('📝 Prompts configurados com sucesso!');
    }

    /**
     * Resetar configurações de IA
     */
    private function resetAiSettings()
    {
        $this->setupAiSettings();
        $this->info('🔄 Configurações resetadas para valores padrão!');
    }

    /**
     * Mostrar configurações atuais
     */
    private function showCurrentSettings()
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        $this->newLine();
        $this->info('📋 Configurações atuais de IA:');
        $this->table(
            ['Configuração', 'Valor'],
            [
                ['API Key', $aiSettings['api_key'] ? '***' . substr($aiSettings['api_key'], -4) : 'Não configurada'],
                ['Modelo', $aiSettings['model']],
                ['Ativo', $aiSettings['is_active'] ? 'Sim' : 'Não'],
                ['Prompt do Sistema', $aiSettings['system_prompt'] ? 'Configurado' : 'Padrão'],
                ['Prompt Email', $aiSettings['email_template_prompt'] ? 'Configurado' : 'Padrão'],
                ['Prompt WhatsApp', $aiSettings['whatsapp_template_prompt'] ? 'Configurado' : 'Padrão'],
                ['Prompt Contratos', $aiSettings['contract_template_prompt'] ? 'Configurado' : 'Padrão'],
                ['Prompt Pagamento', $aiSettings['payment_template_prompt'] ? 'Configurado' : 'Padrão'],
                ['Prompt Inscrição', $aiSettings['enrollment_template_prompt'] ? 'Configurado' : 'Padrão'],
                ['Prompt Matrícula', $aiSettings['matriculation_template_prompt'] ? 'Configurado' : 'Padrão'],
                ['Prompt Suporte', $aiSettings['support_prompt'] ? 'Configurado' : 'Padrão'],
            ]
        );
    }

    /**
     * Prompt padrão para templates de email
     */
    private function getDefaultEmailTemplatePrompt()
    {
        return 'Crie um template HTML completo de email marketing para campanhas de certificação do ensino médio e fundamental com as seguintes especificações:

🎯 TIPO DE TEMPLATE: {templateType}
🎯 OBJETIVO: {objective}
🎯 PÚBLICO-ALVO: {targetAudience}

🏫 CONTEXTO ESPECÍFICO:
- Empresa de certificação EJA/Supletivo (ensino médio e fundamental)
- Público adulto que não completou os estudos básicos
- Diplomas reconhecidos pelo MEC
- Foco em superação pessoal e conquista do certificado

📋 REQUISITOS OBRIGATÓRIOS:
- Template responsivo (600px máximo de largura)
- HTML com CSS inline para máxima compatibilidade
- Design moderno, colorido e divertido que inspire confiança
- Estrutura baseada em tabelas para compatibilidade com todos os clientes de email
- CTAs (botões de ação) com gradientes vibrantes e efeitos hover
- Seções bem organizadas: cabeçalho, conteúdo principal, rodapé
- Paleta de cores vibrante: vermelhos (#ef4444, #dc2626), dourados (#fbbf24, #f59e0b), azuis
- Cards com sombras marcantes e bordas arredondadas
- Animações CSS sutis (pulse, shine) em elementos de destaque

🔧 VARIÁVEIS DISPONÍVEIS (OBRIGATÓRIO USAR):
{variablesText}

{additionalInstructions}

Gere o HTML completo e funcional, pronto para uso em campanhas de email marketing educacional.';
    }

    /**
     * Prompt padrão para templates de WhatsApp
     */
    private function getDefaultWhatsAppTemplatePrompt()
    {
        return 'Crie uma mensagem de WhatsApp profissional e persuasiva para campanhas de certificação do ensino médio e fundamental:

🎯 OBJETIVO: {objective}
🎯 PÚBLICO-ALVO: {targetAudience}

🏫 CONTEXTO:
- Empresa de certificação EJA/Supletivo
- Público adulto que não completou os estudos
- Diplomas reconhecidos pelo MEC
- Foco em superação pessoal

📱 REQUISITOS:
- Mensagem clara e direta
- Tom motivacional e encorajador
- Máximo 300 caracteres
- Inclua emojis relevantes
- Call-to-action claro
- Linguagem acessível

{additionalInstructions}

Crie uma mensagem que motive o destinatário a buscar informações sobre certificação.';
    }

    /**
     * Prompt padrão para templates de contratos
     */
    private function getDefaultContractTemplatePrompt()
    {
        return 'Crie um template de contrato HTML profissional com as seguintes especificações:

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

{additionalInstructions}';
    }

    /**
     * Prompt padrão para templates de pagamento
     */
    private function getDefaultPaymentTemplatePrompt()
    {
        return 'Crie um template de documento de pagamento HTML profissional com as seguintes especificações:

🎯 OBJETIVO: {objective}
📋 TIPO DE PAGAMENTO: {paymentType}

📝 VARIÁVEIS DISPONÍVEIS (use no formato {{variavel}}):
{variablesText}

{referenceInstructions}

📋 REQUISITOS OBRIGATÓRIOS:
- Documento deve ser para instituição de ensino brasileira
- Incluir identificação completa do aluno e da escola
- Descrição do serviço ou curso
- Valores detalhados (matrícula, mensalidade, etc.)
- Formas de pagamento aceitas
- Prazos e vencimentos
- Política de multas e juros
- Informações de contato para dúvidas
- Local e data para assinatura

🎨 FORMATAÇÃO:
- Use HTML com CSS inline para formatação profissional
- Aparência formal similar a documentos financeiros
- Fonte legível (Arial, Times New Roman)
- Margens adequadas para impressão (2cm)
- Cabeçalho com nome da instituição
- Tabelas organizadas para valores e prazos
- Espaços adequados para assinatura
- Quebras de página quando necessário

💰 ASPECTOS FINANCEIROS:
- Valores em reais (R$)
- Formatação de moeda brasileira
- Cálculo de juros e multas
- Condições de parcelamento

{additionalInstructions}';
    }

    /**
     * Prompt padrão para templates de inscrição
     */
    private function getDefaultEnrollmentTemplatePrompt()
    {
        return 'Crie um template de documento de inscrição HTML profissional com as seguintes especificações:

🎯 OBJETIVO: {objective}
📋 TIPO DE INSCRIÇÃO: {enrollmentType}

📝 VARIÁVEIS DISPONÍVEIS (use no formato {{variavel}}):
{variablesText}

{referenceInstructions}

📋 REQUISITOS OBRIGATÓRIOS:
- Documento deve ser para instituição de ensino brasileira
- Incluir identificação completa do candidato
- Informações sobre o curso desejado
- Requisitos para inscrição
- Documentação necessária
- Processo seletivo (se houver)
- Datas importantes e prazos
- Informações de contato
- Local e data para assinatura

🎨 FORMATAÇÃO:
- Use HTML com CSS inline para formatação profissional
- Aparência formal similar a documentos acadêmicos
- Fonte legível (Arial, Times New Roman)
- Margens adequadas para impressão (2cm)
- Cabeçalho com nome da instituição
- Seções bem organizadas e numeradas
- Espaços adequados para preenchimento
- Quebras de página quando necessário

📚 ASPECTOS ACADÊMICOS:
- Requisitos educacionais
- Modalidade de ensino
- Carga horária
- Duração do curso
- Certificação oferecida

{additionalInstructions}';
    }

    /**
     * Prompt padrão para templates de matrícula
     */
    private function getDefaultMatriculationTemplatePrompt()
    {
        return 'Crie um template de documento de matrícula HTML profissional com as seguintes especificações:

🎯 OBJETIVO: {objective}
📋 TIPO DE MATRÍCULA: {matriculationType}

📝 VARIÁVEIS DISPONÍVEIS (use no formato {{variavel}}):
{variablesText}

{referenceInstructions}

📋 REQUISITOS OBRIGATÓRIOS:
- Documento deve ser para instituição de ensino brasileira
- Incluir identificação completa do aluno
- Informações sobre o curso matriculado
- Dados da instituição
- Condições de matrícula
- Valores e formas de pagamento
- Calendário acadêmico
- Direitos e deveres do aluno
- Local e data para assinatura

🎨 FORMATAÇÃO:
- Use HTML com CSS inline para formatação profissional
- Aparência formal similar a documentos acadêmicos
- Fonte legível (Arial, Times New Roman)
- Margens adequadas para impressão (2cm)
- Cabeçalho com nome da instituição
- Seções bem organizadas e numeradas
- Espaços adequados para preenchimento
- Quebras de página quando necessário

🎓 ASPECTOS ACADÊMICOS:
- Dados do curso
- Modalidade de ensino
- Carga horária
- Duração
- Certificação
- Coordenação responsável

{additionalInstructions}';
    }
}
