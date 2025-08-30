<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    /**
     * Exibir página de configurações
     */
    public function index()
    {
        // Verificar se tem permissão para acessar configurações
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            abort(403, 'Acesso negado. Você não tem permissão para acessar as configurações.');
        }

        // Limpar cache para garantir que estamos obtendo os valores mais recentes
        \Illuminate\Support\Facades\Cache::flush();
        
        // Obter todas as configurações do banco de dados
        $allSettings = SystemSetting::all();
        $settingsMap = [];
        
        // Mapear configurações por chave para uso na view
        foreach ($allSettings as $setting) {
            $value = $setting->value;
            if ($setting->type === 'json') {
                try {
                    $value = json_decode($value, true);
                } catch (\Exception $e) {
                    $value = [];
                }
            } elseif ($setting->type === 'boolean') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
            $settingsMap[$setting->key] = $value;
        }
        
        $settings = $settingsMap;
        
        // Obter configurações específicas para uso na view
        $leadSettings = SystemSetting::getLeadSettings();
        $trackingSettings = SystemSetting::getTrackingSettings();
        $formSettings = SystemSetting::getFormSettings();
        $emailSettings = SystemSetting::getEmailSettings();
        $thankYouSettings = SystemSetting::getThankYouPageSettings();
        $generalSettings = SystemSetting::getGeneralSettings();
        $appearanceSettings = SystemSetting::getAppearanceSettings();
        $whatsappSettings = SystemSetting::getWhatsAppSettings();
        $countdownSettings = SystemSetting::getCountdownSettings();
        $landingSettings = SystemSetting::getLandingPageSettings();
        $aiSettings = SystemSetting::getAiSettings();
        $contractSettings = SystemSetting::getContractSettings();
        $paymentSettings = SystemSetting::getPaymentSettings();
        
        // Passar as configurações para a view
        return view('admin.settings.index', compact(
            'settings',
            'leadSettings',
            'trackingSettings',
            'formSettings',
            'emailSettings',
            'thankYouSettings',
            'generalSettings',
            'appearanceSettings',
            'whatsappSettings',
            'countdownSettings',
            'landingSettings',
            'aiSettings',
            'contractSettings',
            'paymentSettings'
        ));
    }

    /**
     * Atualizar configurações
     */
    public function update(Request $request)
    {
        // Verificar se tem permissão para alterar configurações
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Você não tem permissão para alterar configurações.'
            ], 403);
        }
        try {
            // Configurações de leads
            SystemSetting::set('lead_cooldown_minutes', $request->lead_cooldown_minutes, 'integer', 'leads', 'Tempo de cooldown entre leads em minutos');
            SystemSetting::set('auto_unlock_hours', $request->auto_unlock_hours, 'integer', 'leads', 'Tempo para destravar leads automaticamente em horas');
            SystemSetting::set('max_leads_per_user', $request->max_leads_per_user, 'integer', 'leads', 'Número máximo de leads por usuário');
            
            // Configurações de tracking
            SystemSetting::set('google_tag_manager_id', $request->google_tag_manager_id, 'string', 'tracking', 'ID do Google Tag Manager');
            
            // Não salvar valor vazio para o facebook_pixel_id se já existir um valor
            $currentPixelId = SystemSetting::get('facebook_pixel_id', '');
            $pixelId = !empty($request->facebook_pixel_id) ? $request->facebook_pixel_id : $currentPixelId;
            SystemSetting::set('facebook_pixel_id', $pixelId, 'string', 'tracking', 'ID do Facebook Pixel');
            
            SystemSetting::set('enable_google_analytics', $request->has('enable_google_analytics'), 'boolean', 'tracking', 'Ativar Google Analytics');
            SystemSetting::set('enable_facebook_pixel', $request->has('enable_facebook_pixel'), 'boolean', 'tracking', 'Ativar Facebook Pixel');
            
            // Configurações de formulários - processar arrays de chaves e valores
            if ($request->has('available_courses_keys') && $request->has('available_courses_values')) {
                $coursesKeys = $request->available_courses_keys;
                $coursesValues = $request->available_courses_values;
                $courses = [];
                
                for ($i = 0; $i < count($coursesKeys); $i++) {
                    if (!empty($coursesKeys[$i]) && !empty($coursesValues[$i])) {
                        $courses[$coursesKeys[$i]] = $coursesValues[$i];
                    }
                }
                
                SystemSetting::set('available_courses', json_encode($courses), 'json', 'forms', 'Cursos disponíveis');
            }
            
            if ($request->has('available_modalities_keys') && $request->has('available_modalities_values')) {
                $modalitiesKeys = $request->available_modalities_keys;
                $modalitiesValues = $request->available_modalities_values;
                $modalities = [];
                
                for ($i = 0; $i < count($modalitiesKeys); $i++) {
                    if (!empty($modalitiesKeys[$i]) && !empty($modalitiesValues[$i])) {
                        $modalities[$modalitiesKeys[$i]] = $modalitiesValues[$i];
                    }
                }
                
                SystemSetting::set('available_modalities', json_encode($modalities), 'json', 'forms', 'Modalidades disponíveis');
            }
            
            SystemSetting::set('default_course', $request->default_course, 'string', 'forms', 'Curso padrão');
            SystemSetting::set('default_modality', $request->default_modality, 'string', 'forms', 'Modalidade padrão');
            
            // Configurações de Email (SMTP)
            SystemSetting::set('enable_email', $request->has('enable_email'), 'boolean', 'email', 'Ativar envio de emails');
            SystemSetting::set('mail_mailer', $request->mail_mailer, 'string', 'email', 'Driver de email');
            SystemSetting::set('mail_host', $request->mail_host, 'string', 'email', 'Servidor SMTP');
            SystemSetting::set('mail_port', $request->mail_port, 'string', 'email', 'Porta SMTP');
            SystemSetting::set('mail_username', $request->mail_username, 'string', 'email', 'Usuário SMTP');
            
            // Só atualizar a senha se foi fornecida
            if (!empty($request->mail_password)) {
                SystemSetting::set('mail_password', $request->mail_password, 'string', 'email', 'Senha SMTP');
            }
            
            SystemSetting::set('mail_encryption', $request->mail_encryption, 'string', 'email', 'Criptografia SMTP');
            SystemSetting::set('mail_from_address', $request->mail_from_address, 'string', 'email', 'Email de origem');
            SystemSetting::set('mail_from_name', $request->mail_from_name, 'string', 'email', 'Nome de exibição');
            SystemSetting::set('mail_reply_to', $request->mail_reply_to, 'string', 'email', 'Email de resposta');
            SystemSetting::set('admin_notification_email', $request->admin_notification_email, 'string', 'email', 'Email para notificações admin');
            SystemSetting::set('notify_new_lead', $request->has('notify_new_lead'), 'boolean', 'email', 'Notificar sobre novos leads');
            SystemSetting::set('notify_status_change', $request->has('notify_status_change'), 'boolean', 'email', 'Notificar sobre mudanças de status');
            SystemSetting::set('send_confirmation_email', $request->has('send_confirmation_email'), 'boolean', 'email', 'Enviar email de confirmação');
            SystemSetting::set('send_admin_notification', $request->has('send_admin_notification'), 'boolean', 'email', 'Enviar notificação ao admin');
            SystemSetting::set('send_followup_email', $request->has('send_followup_email'), 'boolean', 'email', 'Enviar email de acompanhamento');
            
            // Configurações da página de agradecimento
            SystemSetting::set('thank_you_page_title', $request->thank_you_page_title, 'string', 'thank_you', 'Título da página de agradecimento');
            SystemSetting::set('thank_you_page_subtitle', $request->thank_you_page_subtitle, 'string', 'thank_you', 'Subtítulo da página de agradecimento');
            SystemSetting::set('thank_you_header_color', $request->thank_you_header_color, 'string', 'thank_you', 'Cor do cabeçalho da página de agradecimento');
            SystemSetting::set('thank_you_custom_message', $request->thank_you_custom_message, 'string', 'thank_you', 'Mensagem personalizada da página de agradecimento');
            SystemSetting::set('thank_you_contact_phone', $request->thank_you_contact_phone, 'string', 'thank_you', 'Telefone de contato na página de agradecimento');
            SystemSetting::set('thank_you_contact_email', $request->thank_you_contact_email, 'string', 'thank_you', 'Email de contato na página de agradecimento');
            SystemSetting::set('thank_you_contact_hours', $request->thank_you_contact_hours, 'string', 'thank_you', 'Horário de atendimento na página de agradecimento');
            SystemSetting::set('thank_you_show_contact_info', $request->has('thank_you_show_contact_info'), 'boolean', 'thank_you', 'Exibir informações de contato');
            SystemSetting::set('thank_you_show_steps', $request->has('thank_you_show_steps'), 'boolean', 'thank_you', 'Exibir próximos passos');
            SystemSetting::set('thank_you_show_tips', $request->has('thank_you_show_tips'), 'boolean', 'thank_you', 'Exibir dicas importantes');
            
            // Configurações de WhatsApp
            SystemSetting::set('whatsapp_enabled', $request->has('whatsapp_enabled'), 'boolean', 'whatsapp', 'Ativar botão flutuante do WhatsApp');
            SystemSetting::set('whatsapp_number', $request->whatsapp_number, 'string', 'whatsapp', 'Número do WhatsApp (formato: 5511999999999)');
            SystemSetting::set('whatsapp_message', $request->whatsapp_message, 'string', 'whatsapp', 'Mensagem padrão do WhatsApp');
            SystemSetting::set('whatsapp_button_position', $request->whatsapp_button_position, 'string', 'whatsapp', 'Posição do botão WhatsApp');
            SystemSetting::set('whatsapp_button_color', $request->whatsapp_button_color, 'string', 'whatsapp', 'Cor do botão WhatsApp');
            
            // Configurações de Countdown
            SystemSetting::set('countdown_enabled', $request->has('countdown_enabled'), 'boolean', 'countdown', 'Ativar contador regressivo da oferta');
            SystemSetting::set('countdown_end_date', $request->countdown_end_date, 'string', 'countdown', 'Data de término da oferta (formato: YYYY-MM-DD)');
            SystemSetting::set('countdown_end_time', $request->countdown_end_time, 'string', 'countdown', 'Horário de término da oferta (formato: HH:MM)');
            SystemSetting::set('countdown_timezone', $request->countdown_timezone, 'string', 'countdown', 'Fuso horário para o countdown');
            SystemSetting::set('countdown_text', $request->countdown_text, 'string', 'countdown', 'Texto antes da data da oferta');
            SystemSetting::set('countdown_discount_text', $request->countdown_discount_text, 'string', 'countdown', 'Texto do desconto');
            SystemSetting::set('countdown_price_original', $request->countdown_price_original, 'string', 'countdown', 'Preço original da oferta');
            SystemSetting::set('countdown_price_discount', $request->countdown_price_discount, 'string', 'countdown', 'Preço com desconto da oferta');
            SystemSetting::set('countdown_price_installments_original', $request->countdown_price_installments_original, 'string', 'countdown', 'Número de parcelas do preço original');
            SystemSetting::set('countdown_price_installments_discount', $request->countdown_price_installments_discount, 'string', 'countdown', 'Número de parcelas do preço com desconto');
            SystemSetting::set('countdown_pix_price', $request->countdown_pix_price, 'string', 'countdown', 'Preço no PIX');
            SystemSetting::set('countdown_renewal_type', $request->countdown_renewal_type, 'string', 'countdown', 'Tipo de renovação automática');
            SystemSetting::set('countdown_auto_extend_days', $request->countdown_auto_extend_days, 'integer', 'countdown', 'Dias para estender automaticamente');
            
            // Configurações da Landing Page
            SystemSetting::set('landing_hero_title', $request->landing_hero_title, 'string', 'landing_page', 'Título principal da landing page');
            SystemSetting::set('landing_hero_subtitle', $request->landing_hero_subtitle, 'string', 'landing_page', 'Subtítulo da landing page');
            SystemSetting::set('landing_cta_button_text', $request->landing_cta_button_text, 'string', 'landing_page', 'Texto do botão CTA principal');
            SystemSetting::set('landing_cta_button_color', $request->landing_cta_button_color, 'string', 'landing_page', 'Cor do botão CTA principal');
            SystemSetting::set('landing_benefits_title', $request->landing_benefits_title, 'string', 'landing_page', 'Título da seção de benefícios');
            SystemSetting::set('landing_benefit_1', $request->landing_benefit_1, 'string', 'landing_page', 'Benefício 1');
            SystemSetting::set('landing_benefit_2', $request->landing_benefit_2, 'string', 'landing_page', 'Benefício 2');
            SystemSetting::set('landing_benefit_3', $request->landing_benefit_3, 'string', 'landing_page', 'Benefício 3');
            SystemSetting::set('landing_benefit_4', $request->landing_benefit_4, 'string', 'landing_page', 'Benefício 4');
            SystemSetting::set('landing_form_title', $request->landing_form_title, 'string', 'landing_page', 'Título do formulário');
            SystemSetting::set('landing_form_subtitle', $request->landing_form_subtitle, 'string', 'landing_page', 'Subtítulo do formulário');
            SystemSetting::set('landing_form_button_text', $request->landing_form_button_text, 'string', 'landing_page', 'Texto do botão do formulário');
            SystemSetting::set('landing_form_button_color', $request->landing_form_button_color, 'string', 'landing_page', 'Cor do botão do formulário');
            SystemSetting::set('landing_footer_company_name', $request->landing_footer_company_name, 'string', 'landing_page', 'Nome da empresa no rodapé');
            SystemSetting::set('landing_footer_email', $request->landing_footer_email, 'string', 'landing_page', 'Email de contato no rodapé');
            SystemSetting::set('landing_footer_phone', $request->landing_footer_phone, 'string', 'landing_page', 'Telefone de contato no rodapé');
            SystemSetting::set('landing_footer_address', $request->landing_footer_address, 'string', 'landing_page', 'Endereço no rodapé');
            SystemSetting::set('landing_footer_copyright', $request->landing_footer_copyright, 'string', 'landing_page', 'Texto de copyright');
            SystemSetting::set('landing_social_facebook', $request->landing_social_facebook, 'string', 'landing_page', 'URL do Facebook');
            SystemSetting::set('landing_social_instagram', $request->landing_social_instagram, 'string', 'landing_page', 'URL do Instagram');
            SystemSetting::set('landing_social_linkedin', $request->landing_social_linkedin, 'string', 'landing_page', 'URL do LinkedIn');
            SystemSetting::set('landing_social_youtube', $request->landing_social_youtube, 'string', 'landing_page', 'URL do YouTube');
            SystemSetting::set('landing_social_tiktok', $request->landing_social_tiktok, 'string', 'landing_page', 'URL do TikTok');
            SystemSetting::set('landing_primary_color', $request->landing_primary_color, 'string', 'landing_page', 'Cor primária do site');
            SystemSetting::set('landing_secondary_color', $request->landing_secondary_color, 'string', 'landing_page', 'Cor secundária do site');
            SystemSetting::set('landing_accent_color', $request->landing_accent_color, 'string', 'landing_page', 'Cor de destaque do site');
            SystemSetting::set('landing_mec_authorization_file', $request->landing_mec_authorization_file, 'string', 'landing_page', 'Arquivo de autorização do MEC');
            SystemSetting::set('landing_mec_address', $request->landing_mec_address, 'string', 'landing_page', 'Endereço da autorização do MEC');
            
            // Configurações do Chat de Suporte
            SystemSetting::set('landing_chat_enabled', $request->has('landing_chat_enabled'), 'boolean', 'landing_page', 'Ativar chat de suporte na landing page');
            SystemSetting::set('landing_chat_title', $request->landing_chat_title, 'string', 'landing_page', 'Título do chat de suporte');
            SystemSetting::set('landing_chat_welcome_message', $request->landing_chat_welcome_message, 'string', 'landing_page', 'Mensagem de boas-vindas do chat');
            SystemSetting::set('landing_chat_position', $request->landing_chat_position, 'string', 'landing_page', 'Posição do chat na página');
            SystemSetting::set('landing_chat_color', $request->landing_chat_color, 'string', 'landing_page', 'Cor do botão do chat');
            SystemSetting::set('landing_chat_icon', $request->landing_chat_icon, 'string', 'landing_page', 'Ícone do chat de suporte');
            
            // Configurações do Google Tag Manager para Landing Page
            SystemSetting::set('landing_gtm_enabled', $request->has('landing_gtm_enabled'), 'boolean', 'landing_page', 'Ativar Google Tag Manager na landing page');
            SystemSetting::set('landing_gtm_id', $request->landing_gtm_id, 'string', 'landing_page', 'ID do Google Tag Manager para landing page');
            SystemSetting::set('landing_gtm_events', $request->landing_gtm_events, 'text', 'landing_page', 'Eventos personalizados do GTM para landing page');
            
            // Processar upload das imagens do rodapé
            if ($request->hasFile('landing_footer_image_1')) {
                $image1 = $request->file('landing_footer_image_1');
                $image1Name = 'footer_image_1_' . time() . '.' . $image1->getClientOriginalExtension();
                $image1->storeAs('public/footer', $image1Name);
                SystemSetting::set('landing_footer_image_1', 'footer/' . $image1Name, 'string', 'landing_page', 'Imagem 1 do rodapé');
            }
            
            if ($request->hasFile('landing_footer_image_2')) {
                $image2 = $request->file('landing_footer_image_2');
                $image2Name = 'footer_image_2_' . time() . '.' . $image2->getClientOriginalExtension();
                $image2->storeAs('public/footer', $image2Name);
                SystemSetting::set('landing_footer_image_2', 'footer/' . $image2Name, 'string', 'landing_page', 'Imagem 2 do rodapé');
            }
            
            if ($request->hasFile('landing_footer_image_3')) {
                $image3 = $request->file('landing_footer_image_3');
                $image3Name = 'footer_image_3_' . time() . '.' . $image3->getClientOriginalExtension();
                $image3->storeAs('public/footer', $image3Name);
                SystemSetting::set('landing_footer_image_3', 'footer/' . $image3Name, 'string', 'landing_page', 'Imagem 3 do rodapé');
            }

            // Configurações do ChatGPT
            // Verificar se estamos recebendo os campos diretamente ou como parte do array ai_settings
            if ($request->has('ai_settings')) {
                $aiSettings = $request->ai_settings;
                SystemSetting::set('ai_api_key', $aiSettings['api_key'] ?? '', 'string', 'ai', 'API Key do ChatGPT');
                SystemSetting::set('ai_model', $aiSettings['model'] ?? 'gpt-4o-mini', 'string', 'ai', 'Modelo do ChatGPT');
                SystemSetting::set('ai_system_prompt', $aiSettings['system_prompt'] ?? '', 'text', 'ai', 'Prompt do sistema para o ChatGPT');
                SystemSetting::set('ai_email_template_prompt', $aiSettings['email_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de email');
                SystemSetting::set('ai_whatsapp_template_prompt', $aiSettings['whatsapp_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de WhatsApp');
                SystemSetting::set('ai_contract_template_prompt', $aiSettings['contract_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de contratos');
                SystemSetting::set('ai_payment_template_prompt', $aiSettings['payment_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de pagamento');
                SystemSetting::set('ai_enrollment_template_prompt', $aiSettings['enrollment_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de inscrição');
                SystemSetting::set('ai_matriculation_template_prompt', $aiSettings['matriculation_template_prompt'] ?? '', 'text', 'ai', 'Prompt para templates de matrícula');
                SystemSetting::set('ai_support_prompt', $aiSettings['support_prompt'] ?? '', 'text', 'ai', 'Prompt para suporte ao cliente');
                SystemSetting::set('ai_is_active', isset($aiSettings['is_active']), 'boolean', 'ai', 'Status de ativação do ChatGPT');
            } else if ($request->has('api_key') || $request->has('model') || $request->has('system_prompt')) {
                // Se estamos recebendo os campos diretamente (da página de configurações do AI)
                SystemSetting::set('ai_api_key', $request->api_key ?? '', 'string', 'ai', 'API Key do ChatGPT');
                SystemSetting::set('ai_model', $request->model ?? 'gpt-3.5-turbo', 'string', 'ai', 'Modelo do ChatGPT');
                SystemSetting::set('ai_system_prompt', $request->system_prompt ?? '', 'text', 'ai', 'Prompt do sistema para o ChatGPT');
                SystemSetting::set('ai_email_template_prompt', $request->email_template_prompt ?? '', 'text', 'ai', 'Prompt para templates de email');
                SystemSetting::set('ai_whatsapp_template_prompt', $request->whatsapp_template_prompt ?? '', 'text', 'ai', 'Prompt para templates de WhatsApp');
                SystemSetting::set('ai_contract_template_prompt', $request->contract_template_prompt ?? '', 'text', 'ai', 'Prompt para templates de contratos');
                SystemSetting::set('ai_payment_template_prompt', $request->payment_template_prompt ?? '', 'text', 'ai', 'Prompt para templates de pagamento');
                SystemSetting::set('ai_enrollment_template_prompt', $request->enrollment_template_prompt ?? '', 'text', 'ai', 'Prompt para templates de inscrição');
                SystemSetting::set('ai_matriculation_template_prompt', $request->matriculation_template_prompt ?? '', 'text', 'ai', 'Prompt para templates de matrícula');
                SystemSetting::set('ai_support_prompt', $request->support_prompt ?? '', 'text', 'ai', 'Prompt para suporte ao cliente');
                SystemSetting::set('ai_is_active', $request->has('is_active'), 'boolean', 'ai', 'Status de ativação do ChatGPT');
            }

            // Configurações de Contratos
            if ($request->has('contract_settings')) {
                $contractSettings = $request->contract_settings;
                
                SystemSetting::set('contract_enable_school_signature', isset($contractSettings['enable_school_signature']), 'boolean', 'contracts', 'Ativar assinatura automática da escola');
                SystemSetting::set('contract_school_signature_name', $contractSettings['school_signature_name'] ?? '', 'string', 'contracts', 'Nome do responsável pela assinatura');
                SystemSetting::set('contract_school_signature_title', $contractSettings['school_signature_title'] ?? '', 'string', 'contracts', 'Cargo do responsável pela assinatura');
                SystemSetting::set('contract_school_signature_data', $contractSettings['school_signature_data'] ?? '', 'text', 'contracts', 'Dados da assinatura digital da escola');
                SystemSetting::set('contract_validity_days', $contractSettings['validity_days'] ?? 30, 'integer', 'contracts', 'Validade do link do contrato em dias');
                SystemSetting::set('contract_reminder_days', $contractSettings['reminder_days'] ?? 3, 'integer', 'contracts', 'Dias antes do vencimento para enviar lembrete');
                SystemSetting::set('contract_auto_send', isset($contractSettings['auto_send']), 'boolean', 'contracts', 'Enviar contrato automaticamente após geração');
                SystemSetting::set('contract_auto_reminder', isset($contractSettings['auto_reminder']), 'boolean', 'contracts', 'Enviar lembretes automáticos');
            }

            // Configurações de Pagamento (Mercado Pago)
            if ($request->has('payment_settings')) {
                $paymentSettings = $request->payment_settings;
                
                SystemSetting::set('mercadopago_enabled', isset($paymentSettings['mercadopago_enabled']) && $paymentSettings['mercadopago_enabled'] == '1', 'boolean', 'payments', 'Ativar integração com Mercado Pago');
                SystemSetting::set('mercadopago_sandbox', isset($paymentSettings['mercadopago_sandbox']) && $paymentSettings['mercadopago_sandbox'] == '1', 'boolean', 'payments', 'Usar ambiente sandbox do Mercado Pago');
                SystemSetting::set('mercadopago_access_token', $paymentSettings['mercadopago_access_token'] ?? '', 'string', 'payments', 'Access Token do Mercado Pago');
                SystemSetting::set('mercadopago_public_key', $paymentSettings['mercadopago_public_key'] ?? '', 'string', 'payments', 'Chave pública do Mercado Pago');
                SystemSetting::set('mercadopago_webhook_secret', $paymentSettings['mercadopago_webhook_secret'] ?? '', 'string', 'payments', 'Chave secreta para validação de webhooks');
                
                // Credenciais específicas para sandbox
                SystemSetting::set('mercadopago_sandbox_access_token', $paymentSettings['mercadopago_sandbox_access_token'] ?? '', 'string', 'payments', 'Access Token do Mercado Pago para ambiente sandbox');
                SystemSetting::set('mercadopago_sandbox_public_key', $paymentSettings['mercadopago_sandbox_public_key'] ?? '', 'string', 'payments', 'Chave pública do Mercado Pago para ambiente sandbox');
                SystemSetting::set('mercadopago_email_notifications', isset($paymentSettings['mercadopago_email_notifications']) && $paymentSettings['mercadopago_email_notifications'] == '1', 'boolean', 'payments', 'Enviar notificações por email');
                SystemSetting::set('mercadopago_whatsapp_notifications', isset($paymentSettings['mercadopago_whatsapp_notifications']) && $paymentSettings['mercadopago_whatsapp_notifications'] == '1', 'boolean', 'payments', 'Enviar notificações por WhatsApp');
                SystemSetting::set('mercadopago_sms_notifications', isset($paymentSettings['mercadopago_sms_notifications']) && $paymentSettings['mercadopago_sms_notifications'] == '1', 'boolean', 'payments', 'Enviar notificações por SMS');
                SystemSetting::set('mercadopago_auto_reminders', isset($paymentSettings['mercadopago_auto_reminders']) && $paymentSettings['mercadopago_auto_reminders'] == '1', 'boolean', 'payments', 'Enviar lembretes automáticos');
                SystemSetting::set('mercadopago_auto_generation', isset($paymentSettings['mercadopago_auto_generation']) && $paymentSettings['mercadopago_auto_generation'] == '1', 'boolean', 'payments', 'Gerar próximos pagamentos automaticamente');
                SystemSetting::set('mercadopago_currency', $paymentSettings['mercadopago_currency'] ?? 'BRL', 'string', 'payments', 'Moeda para pagamentos');
                SystemSetting::set('mercadopago_country', $paymentSettings['mercadopago_country'] ?? 'BR', 'string', 'payments', 'País para pagamentos');
            }

            // Upload do arquivo PDF de autorização do MEC
            if ($request->hasFile('landing_mec_authorization_file')) {
                $file = $request->file('landing_mec_authorization_file');
                $path = $file->store('mec', 'public');
                SystemSetting::set('landing_mec_authorization_file', $path, 'string', 'landing_page', 'Arquivo de autorização do MEC');
            }

            // Endereço abaixo da autorização do MEC
            SystemSetting::set('landing_mec_address', $request->landing_mec_address, 'string', 'landing_page', 'Endereço abaixo da autorização do MEC');
            
            // Limpar cache para garantir que as alterações sejam aplicadas imediatamente
            \Illuminate\Support\Facades\Cache::flush();
            
            // Verificar se é uma requisição AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configurações atualizadas com sucesso!'
                ]);
            }
            
            return redirect()->route('admin.settings.index')->with('success', 'Configurações atualizadas com sucesso!');
        } catch (\Exception $e) {
            // Verificar se é uma requisição AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar configurações: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.settings.index')->with('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
        }
    }

    /**
     * Obter configurações via API
     */
    public function getSettings()
    {
        $settings = SystemSetting::getLeadSettings();
        
        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }

    /**
     * Verificar cooldown do usuário
     */
    public function checkCooldown()
    {
        $userId = session('admin_id');
        $isInCooldown = SystemSetting::isUserInCooldown($userId);
        $remainingSeconds = SystemSetting::getCooldownRemainingSeconds($userId);

        return response()->json([
            'success' => true,
            'in_cooldown' => $isInCooldown,
            'remaining_seconds' => $remainingSeconds,
            'remaining_formatted' => $this->formatCooldownTime($remainingSeconds)
        ]);
    }

    /**
     * Testar configurações de tracking
     */
    public function testTracking()
    {
        // Verificar se é admin
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado.'
            ], 403);
        }

        $trackingSettings = SystemSetting::getTrackingSettings();
        
        // Verificar se os IDs são válidos
        $gtmValid = !empty($trackingSettings['google_tag_manager_id']) && 
                   (preg_match('/^GTM-[A-Z0-9]{1,7}$/i', $trackingSettings['google_tag_manager_id']) || 
                    preg_match('/^[0-9]{8,12}$/i', $trackingSettings['google_tag_manager_id']));
                    
        $fbValid = !empty($trackingSettings['facebook_pixel_id']) && 
                  preg_match('/^[0-9]{8,16}$/i', $trackingSettings['facebook_pixel_id']);
        
        $status = [
            'google_tag_manager' => [
                'configured' => !empty($trackingSettings['google_tag_manager_id']),
                'enabled' => $trackingSettings['enable_google_analytics'],
                'id' => $trackingSettings['google_tag_manager_id'],
                'valid_format' => $gtmValid
            ],
            'facebook_pixel' => [
                'configured' => !empty($trackingSettings['facebook_pixel_id']),
                'enabled' => $trackingSettings['enable_facebook_pixel'],
                'id' => $trackingSettings['facebook_pixel_id'],
                'valid_format' => $fbValid
            ],
            'environment' => [
                'host' => request()->getHost(),
                'tracking_active' => true
            ]
        ];

        return response()->json([
            'success' => true,
            'tracking_status' => $status,
            'message' => 'Status de tracking verificado com sucesso!'
        ]);
    }

    /**
     * Validar ID do Google Tag Manager
     */
    public function validateGTM(Request $request)
    {
        $id = $request->input('id');
        
        // Verificação básica de formato
        $isValidFormat = !empty($id) && 
                        (preg_match('/^GTM-[A-Z0-9]{1,7}$/i', $id) || 
                         preg_match('/^[0-9]{8,12}$/i', $id));
        
        // Verificações adicionais
        $isValidStructure = false;
        $additionalInfo = '';
        
        if ($isValidFormat) {
            // Verificar se o ID segue o padrão correto de dígitos para IDs numéricos
            if (preg_match('/^[0-9]+$/', $id)) {
                // Para IDs numéricos, verificar se está no intervalo típico
                $isValidStructure = (strlen($id) >= 8 && strlen($id) <= 12);
                
                if (!$isValidStructure) {
                    $additionalInfo = 'IDs numéricos do GTM geralmente têm entre 8 e 12 dígitos.';
                }
            } else {
                // Para IDs no formato GTM-XXXXXX
                $isValidStructure = preg_match('/^GTM-[A-Z0-9]{5,7}$/i', $id);
                
                if (!$isValidStructure) {
                    $additionalInfo = 'IDs do GTM geralmente têm o formato GTM-XXXXXX com 5-7 caracteres após o prefixo.';
                }
            }
        }
        
        // Verificar se o ID tem características comuns de IDs reais
        $commonPatterns = false;
        if ($isValidFormat) {
            // Verificar se o ID tem características comuns de GTM-IDs reais
            if (strpos(strtoupper($id), 'GTM-') === 0) {
                // A maioria dos GTM-IDs têm pelo menos um número
                $commonPatterns = preg_match('/[0-9]/', $id);
            } else {
                // IDs numéricos geralmente não começam com zeros
                $commonPatterns = $id[0] !== '0';
            }
        }
        
        // Determinar o resultado final
        $isValid = $isValidFormat && ($isValidStructure || $commonPatterns);
        
        // Preparar a mensagem de resposta
        $message = $isValid ? 'ID do GTM válido' : 'Formato de ID do GTM inválido';
        if (!$isValid && !empty($additionalInfo)) {
            $message .= '. ' . $additionalInfo;
        }
        
        // Registrar a validação para fins de auditoria
        \Illuminate\Support\Facades\Log::info('Validação de GTM ID', [
            'id' => $id,
            'valid' => $isValid,
            'format_valid' => $isValidFormat,
            'structure_valid' => $isValidStructure,
            'common_patterns' => $commonPatterns,
            'user_id' => session('admin_id')
        ]);
        
        return response()->json([
            'success' => true,
            'valid' => $isValid,
            'format_valid' => $isValidFormat,
            'structure_valid' => $isValidStructure,
            'message' => $message
        ]);
    }

    /**
     * Validar ID do Facebook Pixel
     */
    public function validatePixel(Request $request)
    {
        $id = $request->input('id');
        
        // Verificação básica de formato
        $isValidFormat = !empty($id) && preg_match('/^[0-9]{8,16}$/i', $id);
        
        // Verificações adicionais
        $isValidStructure = false;
        $additionalInfo = '';
        
        if ($isValidFormat) {
            // Facebook Pixel IDs são geralmente 15-16 dígitos, mas aceitamos 8-16 para compatibilidade
            $isValidStructure = strlen($id) >= 10;
            
            if (!$isValidStructure && strlen($id) < 10) {
                $additionalInfo = 'IDs do Facebook Pixel geralmente têm pelo menos 10 dígitos.';
            }
            
            // Verificar características específicas de Pixel IDs
            // A maioria dos Pixel IDs não começa com zeros
            if ($id[0] === '0') {
                $isValidStructure = false;
                $additionalInfo = 'IDs do Facebook Pixel geralmente não começam com zero.';
            }
            
            // Verificar se o ID tem uma estrutura de dígitos que parece um Pixel ID real
            // (Muitos Pixel IDs seguem padrões específicos de dígitos)
            if ($isValidStructure) {
                // Verificar se há sequências improváveis como '123456789'
                if (strpos($id, '123456789') !== false || strpos($id, '987654321') !== false) {
                    $isValidStructure = false;
                    $additionalInfo = 'O ID parece conter uma sequência numérica improvável.';
                }
                
                // Verificar se há dígitos repetidos em excesso
                $digitCounts = [];
                for ($i = 0; $i < strlen($id); $i++) {
                    $digit = $id[$i];
                    if (!isset($digitCounts[$digit])) {
                        $digitCounts[$digit] = 0;
                    }
                    $digitCounts[$digit]++;
                }
                
                // Se mais de 70% dos dígitos são iguais, provavelmente é inválido
                $mostFrequentDigit = max($digitCounts);
                if ($mostFrequentDigit > strlen($id) * 0.7) {
                    $isValidStructure = false;
                    $additionalInfo = 'O ID contém muitos dígitos repetidos.';
                }
            }
        }
        
        // Determinar o resultado final
        $isValid = $isValidFormat && $isValidStructure;
        
        // Preparar a mensagem de resposta
        $message = $isValid ? 'ID do Facebook Pixel válido' : 'Formato de ID do Facebook Pixel inválido';
        if (!$isValid && !empty($additionalInfo)) {
            $message .= '. ' . $additionalInfo;
        }
        
        // Registrar a validação para fins de auditoria
        \Illuminate\Support\Facades\Log::info('Validação de Facebook Pixel ID', [
            'id' => $id,
            'valid' => $isValid,
            'format_valid' => $isValidFormat,
            'structure_valid' => $isValidStructure,
            'user_id' => session('admin_id')
        ]);
        
        return response()->json([
            'success' => true,
            'valid' => $isValid,
            'format_valid' => $isValidFormat,
            'structure_valid' => $isValidStructure,
            'message' => $message
        ]);
    }

    /**
     * Validar valor da configuração conforme o tipo
     */
    private function validateSettingValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            case 'integer':
                $intValue = filter_var($value, FILTER_VALIDATE_INT);
                if ($intValue === false) {
                    throw new \InvalidArgumentException("Valor deve ser um número inteiro válido.");
                }
                return (string) $intValue;
            case 'float':
                $floatValue = filter_var($value, FILTER_VALIDATE_FLOAT);
                if ($floatValue === false) {
                    throw new \InvalidArgumentException("Valor deve ser um número decimal válido.");
                }
                return (string) $floatValue;
            default:
                return (string) $value;
        }
    }

    /**
     * Formatar tempo do cooldown
     */
    private function formatCooldownTime($seconds)
    {
        if ($seconds <= 0) {
            return '0s';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes > 0) {
            return $minutes . 'm ' . $remainingSeconds . 's';
        }

        return $remainingSeconds . 's';
    }

    /**
     * Resetar configurações para valores padrão
     */
    /**
     * Testar conexão com servidor SMTP
     */
    public function testEmailConnection(Request $request)
    {
        // Garantir que a resposta seja sempre JSON
        try {
            // Verificar se tem permissão para testar email
            if (!auth()->user()->hasPermission('configuracoes.index')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado. Você não tem permissão para testar configurações de email.'
                ], 403);
            }
            
            // Obter configurações do request
            $mailer = $request->input('mail_mailer', 'smtp');
            $host = $request->input('mail_host');
            $port = $request->input('mail_port');
            $username = $request->input('mail_username');
            $password = $request->input('mail_password');
            $encryption = $request->input('mail_encryption');
            $fromAddress = $request->input('mail_from_address');
            $fromName = $request->input('mail_from_name');
            
            // Validar dados básicos
            if (empty($host) || empty($port) || empty($username) || empty($password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Por favor, preencha todos os campos obrigatórios (servidor, porta, usuário e senha).'
                ]);
            }
            
            // Testar conexão usando o Laravel Mail diretamente
            try {
                // Configurar temporariamente as configurações de email
                config([
                    'mail.mailers.test_smtp' => [
                        'transport' => 'smtp',
                        'host' => $host,
                        'port' => $port,
                        'encryption' => $encryption,
                        'username' => $username,
                        'password' => $password,
                        'timeout' => 10,
                    ],
                    'mail.from' => [
                        'address' => $fromAddress,
                        'name' => $fromName,
                    ]
                ]);
                
                // Tentar enviar um email de teste usando o mailer configurado
                try {
                    \Mail::mailer('test_smtp')->send([], [], function ($message) use ($fromAddress) {
                        $message->to($fromAddress)
                                ->subject('Teste de Conexão SMTP')
                                ->html('<p>Este é um teste de conexão SMTP. Se você recebeu este email, a configuração está funcionando!</p>');
                    });
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Conexão SMTP testada com sucesso! Email de teste enviado para ' . $fromAddress
                    ]);
                    
                } catch (\Swift_TransportException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro de transporte SMTP: ' . $e->getMessage()
                    ]);
                } catch (\Exception $e) {
                    // Se falhar, tentar conexão básica por socket
                    return $this->testSocketConnection($host, $port, $encryption, $username, $password);
                }
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao testar conexão: ' . $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Método auxiliar para testar conexão por socket (fallback)
     */
    private function testSocketConnection($host, $port, $encryption, $username, $password)
    {
        try {
            $timeout = 10;
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            
            // Determinar se usar SSL/TLS
            $useSSL = ($encryption === 'ssl' || $port == 465);
            
            // Conectar ao servidor
            if ($useSSL) {
                $connection = stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
            } else {
                $connection = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
            }
            
            if (!$connection) {
                return response()->json([
                    'success' => false,
                    'message' => "Erro ao conectar com o servidor SMTP: {$errstr} ({$errno})"
                ]);
            }
            
            // Ler resposta inicial do servidor
            $response = fgets($connection);
            if (!$response || substr($response, 0, 3) !== '220') {
                fclose($connection);
                return response()->json([
                    'success' => false,
                    'message' => 'Servidor SMTP não respondeu corretamente: ' . trim($response)
                ]);
            }
            
            fclose($connection);
            return response()->json([
                'success' => true,
                'message' => 'Conexão básica com servidor SMTP estabelecida com sucesso! (Porta: ' . $port . ', Criptografia: ' . $encryption . ')'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao conectar por socket: ' . $e->getMessage()
            ]);
        }
    }
    
    public function reset()
    {
        // Verificar se é admin
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado.'
            ], 403);
        }

        try {
            // Resetar configurações para valores padrão
            SystemSetting::set('lead_cooldown_minutes', '2', 'integer', 'leads', 'Tempo em minutos que um usuário deve aguardar após pegar um lead para pegar outro');
            SystemSetting::set('auto_unlock_hours', '24', 'integer', 'leads', 'Tempo em horas para destravar automaticamente leads inativos');
            SystemSetting::set('max_leads_per_user', '10', 'integer', 'leads', 'Número máximo de leads que um usuário pode ter travados simultaneamente');
            SystemSetting::set('enable_lead_notifications', 'true', 'boolean', 'notifications', 'Ativar notificações de novos leads');
            
            // Resetar configurações de tracking
            SystemSetting::set('google_tag_manager_id', '', 'string', 'tracking', 'ID do Google Tag Manager (GTM-XXXXXXX)');
            
            // Manter o ID do Facebook Pixel se existir
            $currentPixelId = SystemSetting::get('facebook_pixel_id', '');
            if (empty($currentPixelId)) {
                SystemSetting::set('facebook_pixel_id', '', 'string', 'tracking', 'ID do Facebook Pixel');
            }
            
            SystemSetting::set('enable_google_analytics', 'false', 'boolean', 'tracking', 'Ativar Google Analytics via GTM');
            SystemSetting::set('enable_facebook_pixel', 'false', 'boolean', 'tracking', 'Ativar Facebook Pixel');
            
            // Resetar configurações de formulário
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
            
            SystemSetting::set('default_course', 'excel', 'string', 'forms', 'Curso padrão selecionado no formulário');
            SystemSetting::set('default_modality', 'ensino-medio', 'string', 'forms', 'Modalidade padrão selecionada no formulário');

            return response()->json([
                'success' => true,
                'message' => 'Configurações resetadas para valores padrão!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao resetar configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatar nome da configuração
     */
    private function formatSettingName($key)
    {
        $names = [
            'lead_cooldown_minutes' => 'Cooldown entre Leads',
            'auto_unlock_hours' => 'Destravamento Automático',
            'max_leads_per_user' => 'Máximo de Leads por Usuário',
            'enable_lead_notifications' => 'Notificações de Leads',
            'google_tag_manager_id' => 'Google Tag Manager ID',
            'facebook_pixel_id' => 'Facebook Pixel ID',
            'enable_google_analytics' => 'Ativar Google Analytics',
            'enable_facebook_pixel' => 'Ativar Facebook Pixel',
            'available_courses' => 'Cursos Disponíveis',
            'available_modalities' => 'Modalidades Disponíveis',
            'default_course' => 'Curso Padrão',
            'default_modality' => 'Modalidade Padrão'
        ];
        
        return $names[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Upload de logos
     */
    public function uploadLogo(Request $request)
    {
        // Verificar se tem permissão para upload de logo
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Você não tem permissão para alterar configurações.'
            ], 403);
        }

        try {
            // Log inicial
            \Log::info('Iniciando upload de logo', [
                'request_has_file' => $request->hasFile('logo_file'),
                'logo_type' => $request->logo_type,
                'content_type' => $request->hasFile('logo_file') ? $request->file('logo_file')->getMimeType() : null
            ]);

            if (!$request->hasFile('logo_file')) {
                throw new \Exception('Nenhum arquivo foi enviado.');
            }

            $request->validate([
                'logo_type' => 'required|in:sidebar,login',
                'logo_file' => 'required|file|max:2048'
            ]);

            $logoType = $request->logo_type;
            $file = $request->file('logo_file');
            
            // Validar tipo de arquivo manualmente
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType = $file->getMimeType();
            $allowedTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'svg'];
            
            if (!in_array($extension, $allowedExtensions)) {
                throw new \Exception("Extensão de arquivo não permitida: {$extension}");
            }
            
            // Para SVGs, validar o conteúdo
            if ($extension === 'svg') {
                $content = file_get_contents($file->getRealPath());
                if (!str_contains($content, '<svg') || !str_contains($content, '</svg>')) {
                    throw new \Exception('Arquivo SVG inválido.');
                }
            }
            // Para outros tipos de imagem, validar o mime type
            else if (!in_array($mimeType, $allowedTypes)) {
                throw new \Exception("Tipo de arquivo não permitido: {$mimeType}");
            }
            
            // Log informações do arquivo
            \Log::info('Arquivo recebido', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'extension' => $extension,
                'size' => $file->getSize()
            ]);
            
            // Criar diretório se não existir
            $logoDir = public_path('assets/images/logos');
            if (!file_exists($logoDir)) {
                mkdir($logoDir, 0755, true);
                \Log::info('Diretório de logos criado', ['path' => $logoDir]);
            }
            
            // Gerar nome único para o arquivo
            $fileName = $logoType . '_' . time() . '.' . $extension;
            $filePath = '/assets/images/logos/' . $fileName;
            $fullPath = public_path('assets/images/logos/' . $fileName);
            
            // Log antes de mover
            \Log::info('Tentando mover arquivo', [
                'from' => $file->getRealPath(),
                'to' => $fullPath
            ]);
            
            // Mover o arquivo
            if ($file->move($logoDir, $fileName)) {
                \Log::info('Arquivo movido com sucesso');
                
                // Se for SVG, processar o arquivo
                if ($extension === 'svg') {
                    $svgContent = file_get_contents($fullPath);
                    
                    // Remover scripts e atributos perigosos
                    $svgContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $svgContent);
                    $svgContent = preg_replace('/on\w+="[^"]*"/i', '', $svgContent);
                    $svgContent = preg_replace('/on\w+=\'[^\']*\'/i', '', $svgContent);
                    
                    // Adicionar atributos de preservação de aspecto se não existirem
                    if (!preg_match('/preserveAspectRatio/i', $svgContent)) {
                        $svgContent = preg_replace('/<svg/i', '<svg preserveAspectRatio="xMidYMid meet"', $svgContent);
                    }
                    
                    // Garantir que o SVG tenha viewBox ou width/height
                    if (!preg_match('/viewBox/i', $svgContent) && !preg_match('/width.*height/i', $svgContent)) {
                        $svgContent = preg_replace('/<svg/i', '<svg viewBox="0 0 100 100"', $svgContent);
                    }
                    
                    // Salvar SVG processado
                    file_put_contents($fullPath, $svgContent);
                    \Log::info('SVG processado e salvo');
                }
            } else {
                \Log::error('Falha ao mover arquivo');
                throw new \Exception('Não foi possível mover o arquivo.');
            }
            
            // Verificar se o arquivo existe após mover
            if (!file_exists($fullPath)) {
                \Log::error('Arquivo não encontrado após mover', ['path' => $fullPath]);
                throw new \Exception('Arquivo não encontrado após upload.');
            }
            
            // Atualizar configuração
            if ($logoType === 'sidebar') {
                SystemSetting::set('sidebar_logo_path', $filePath, 'string', 'appearance', 'Caminho para o logo do sidebar');
            } else {
                SystemSetting::set('login_logo_path', $filePath, 'string', 'appearance', 'Caminho para o logo da página de login');
            }
            
            // Log final
            \Log::info('Logo atualizado com sucesso', [
                'file_path' => $filePath,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'file_size' => filesize($fullPath),
                'file_permissions' => substr(sprintf('%o', fileperms($fullPath)), -4)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Logo atualizado com sucesso!',
                'file_path' => $filePath,
                'debug_info' => [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'extension' => $extension,
                    'size' => $file->getSize(),
                    'saved_path' => $fullPath,
                    'file_exists' => file_exists($fullPath),
                    'file_size' => filesize($fullPath),
                    'file_permissions' => substr(sprintf('%o', fileperms($fullPath)), -4)
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro no upload do logo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer upload do logo: ' . $e->getMessage(),
                'debug_info' => [
                    'has_file' => $request->hasFile('logo_file'),
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Resetar logo para padrão
     */
    public function resetLogo(Request $request)
    {
        // Verificar se tem permissão para resetar logo
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Você não tem permissão para alterar configurações.'
            ], 403);
        }

        $request->validate([
            'logo_type' => 'required|in:sidebar,login'
        ]);

        try {
            $logoType = $request->logo_type;
            $defaultPath = '/assets/images/logotipo-dark.svg';
            
            if ($logoType === 'sidebar') {
                SystemSetting::set('sidebar_logo_path', $defaultPath, 'string', 'appearance', 'Caminho para o logo do sidebar');
            } else {
                SystemSetting::set('login_logo_path', $defaultPath, 'string', 'appearance', 'Caminho para o logo da página de login');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Logo resetado para padrão!',
                'file_path' => $defaultPath
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao resetar logo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reiniciar countdown da oferta
     */
    public function renewCountdown(Request $request)
    {
        // Verificar se é admin
        if (!auth()->user()->hasPermission('configuracoes.index')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado.'
            ], 403);
        }

        try {
            $type = $request->input('type', 'weekly');
            $customDays = $request->input('days', 7);
            
            // Calcular nova data
            $now = new \DateTime('now', new \DateTimeZone('America/Sao_Paulo'));
            
            switch ($type) {
                case 'daily':
                    $now->add(new \DateInterval('P1D'));
                    break;
                case 'weekly':
                    $now->add(new \DateInterval('P7D'));
                    break;
                case 'monthly':
                    $now->add(new \DateInterval('P30D'));
                    break;
                case 'custom':
                    $days = max(1, intval($customDays));
                    $now->add(new \DateInterval('P' . $days . 'D'));
                    break;
                default:
                    $now->add(new \DateInterval('P7D'));
                    break;
            }
            
            // Atualizar configurações
            SystemSetting::set('countdown_end_date', $now->format('Y-m-d'), 'string', 'countdown', 'Data de término da oferta (formato: YYYY-MM-DD)');
            SystemSetting::set('countdown_end_time', '23:59', 'string', 'countdown', 'Horário de término da oferta (formato: HH:MM)');
            
            // Limpar cache
            \Illuminate\Support\Facades\Cache::flush();
            
            return response()->json([
                'success' => true,
                'message' => 'Oferta reiniciada com sucesso!',
                'new_date' => $now->format('d/m/Y'),
                'new_timestamp' => $now->getTimestamp() * 1000
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao reiniciar oferta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Executar migration específica
     */
    public function executeMigration(Request $request)
    {
        try {
            // Verificar se é admin
            if (!auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado. Apenas administradores podem executar migrations.'
                ], 403);
            }

            // Validar input
            $request->validate([
                'migration_file' => 'required|string'
            ]);

            $migrationFile = $request->input('migration_file');

            // Remover .php do nome se foi incluído
            $migrationFile = str_replace('.php', '', $migrationFile);

            // Verificar se o arquivo existe
            $migrationPath = database_path('migrations');
            $files = glob($migrationPath . '/*' . $migrationFile . '*.php');

            if (empty($files)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Migration não encontrada: ' . $migrationFile
                ], 404);
            }

            if (count($files) > 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Múltiplas migrations encontradas. Por favor, seja mais específico.',
                    'details' => array_map('basename', $files)
                ], 400);
            }

            // Capturar output do comando
            try {
                // Iniciar buffer de saída
                ob_start();
                
                $exitCode = Artisan::call('migrate:single', [
                    'migration' => basename($files[0], '.php')
                ]);

                // Capturar output
                $output = ob_get_clean();
                $commandOutput = Artisan::output();
                $fullOutput = $output . "\n" . $commandOutput;

                // Se contém mensagem de erro, retornar erro
                if ($exitCode !== 0 || 
                    str_contains(strtolower($fullOutput), 'error') || 
                    str_contains(strtolower($fullOutput), 'exception')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao executar migration',
                        'details' => $fullOutput
                    ], 400);
                }

                // Retornar sucesso
                return response()->json([
                    'success' => true,
                    'message' => 'Migration executada com sucesso!',
                    'details' => $fullOutput
                ]);

            } catch (\Exception $e) {
                // Limpar buffer em caso de erro
                ob_end_clean();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erro ao executar migration: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar migration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testar conexão com o ChatGPT
     */
    public function testAiConnection(Request $request)
    {
        try {
            $request->validate([
                'api_key' => 'required|string',
                'model' => 'required|string'
            ]);

            $client = \OpenAI::client($request->api_key);
            
            // Tenta criar uma completação simples para testar a conexão
            $result = $client->chat()->create([
                'model' => $request->model,
                'messages' => [
                    ['role' => 'user', 'content' => 'Olá! Teste de conexão.']
                ],
                'max_tokens' => 10
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conexão estabelecida com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage()
            ], 500);
        }
    }
}
