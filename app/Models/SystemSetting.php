<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'description'
    ];

    /**
     * Obter valor de uma configuração
     */
    public static function get($key, $default = null)
    {
        $cacheKey = "system_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function() use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Definir valor de uma configuração
     */
    public static function set($key, $value, $type = 'string', $category = 'general', $description = null)
    {
        // Garantir que o valor nunca seja nulo
        if ($value === null) {
            $value = '';
        }
        
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'category' => $category,
                'description' => $description
            ]
        );

        // Limpar cache
        Cache::forget("system_setting_{$key}");

        return $setting;
    }

    /**
     * Converter valor conforme o tipo
     */
    protected static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Obter todas as configurações por categoria
     */
    public static function getByCategory($category)
    {
        return self::where('category', $category)->get()->mapWithKeys(function ($setting) {
            return [$setting->key => self::castValue($setting->value, $setting->type)];
        });
    }

    /**
     * Configurações específicas para leads
     */
    public static function getLeadSettings()
    {
        return [
            'cooldown_minutes' => self::get('lead_cooldown_minutes', 2),
            'auto_unlock_hours' => self::get('auto_unlock_hours', 24),
            'max_leads_per_user' => self::get('max_leads_per_user', 10),
        ];
    }
    
    /**
     * Configurações gerais do sistema
     */
    public static function getGeneralSettings()
    {
        return [
            'thank_you_title' => self::get('thank_you_title', 'Obrigado por se inscrever!'),
            'thank_you_subtitle' => self::get('thank_you_subtitle', 'Recebemos sua inscrição com sucesso.'),
            'thank_you_message' => self::get('thank_you_message', 'Em breve nossa equipe entrará em contato para fornecer mais informações sobre o curso.'),
            'thank_you_contact_phone' => self::get('thank_you_contact_phone', ''),
            'thank_you_contact_email' => self::get('thank_you_contact_email', ''),
            'thank_you_contact_hours' => self::get('thank_you_contact_hours', ''),
            'thank_you_show_contact_info' => self::get('thank_you_show_contact_info', false),
            'thank_you_show_steps' => self::get('thank_you_show_steps', true),
            'thank_you_show_tips' => self::get('thank_you_show_tips', true),
        ];
    }

    /**
     * Configurações específicas para tracking
     */
    public static function getTrackingSettings()
    {
        return [
            'google_tag_manager_id' => self::get('google_tag_manager_id', ''),
            'facebook_pixel_id' => self::get('facebook_pixel_id', ''),
            'enable_google_analytics' => self::get('enable_google_analytics', false),
            'enable_facebook_pixel' => self::get('enable_facebook_pixel', false),
        ];
    }
    
    /**
     * Configurações específicas para formulários
     */
    public static function getFormSettings()
    {
        // Limpar o cache para garantir que estamos obtendo os valores mais recentes
        \Illuminate\Support\Facades\Cache::forget("system_setting_available_courses");
        \Illuminate\Support\Facades\Cache::forget("system_setting_available_modalities");
        \Illuminate\Support\Facades\Cache::forget("system_setting_default_course");
        \Illuminate\Support\Facades\Cache::forget("system_setting_default_modality");
        
        // Valores padrão para cursos e modalidades
        $defaultCourses = [
            'excel' => 'Excel Básico',
            'ingles' => 'Inglês Iniciante',
            'marketing' => 'Marketing Digital'
        ];
        
        $defaultModalities = [
            'ensino-fundamental' => 'Ensino Fundamental',
            'ensino-medio' => 'Ensino Médio',
            'ensino-fundamental-e-ensino-medio' => 'Ensino Fundamental + Ensino Médio'
        ];

        // Séries disponíveis
        $defaultSeries = [
            '1ª série do Ensino Fundamental',
            '2ª série do Ensino Fundamental',
            '3ª série do Ensino Fundamental',
            '4ª série do Ensino Fundamental',
            '5ª série do Ensino Fundamental',
            '6ª série do Ensino Fundamental',
            '7ª série do Ensino Fundamental',
            '8ª série do Ensino Fundamental',
            '9ª série do Ensino Fundamental',
            '1ª série do Ensino Médio',
            '2ª série do Ensino Médio',
            '3ª série do Ensino Médio',
            'Ensino Fundamental Completo',
            'Ensino Médio Incompleto',
            'Não estudou',
            'Outros'
        ];
        
        // Obter valores do banco de dados
        $availableCourses = self::get('available_courses', $defaultCourses);
        $availableModalities = self::get('available_modalities', $defaultModalities);
        
        // Verificar se os valores são strings JSON e convertê-los se necessário
        if (is_string($availableCourses)) {
            try {
                $availableCourses = json_decode($availableCourses, true);
                if (!is_array($availableCourses)) {
                    $availableCourses = $defaultCourses;
                }
            } catch (\Exception $e) {
                $availableCourses = $defaultCourses;
            }
        }
        
        if (is_string($availableModalities)) {
            try {
                $availableModalities = json_decode($availableModalities, true);
                if (!is_array($availableModalities)) {
                    $availableModalities = $defaultModalities;
                }
            } catch (\Exception $e) {
                $availableModalities = $defaultModalities;
            }
        }

        // Obter valores únicos existentes nas matrículas para garantir compatibilidade
        try {
            $existingCourses = \App\Models\Matricula::whereNotNull('curso')
                ->distinct()
                ->pluck('curso')
                ->filter()
                ->toArray();
            
            $existingModalities = \App\Models\Matricula::whereNotNull('modalidade')
                ->distinct()
                ->pluck('modalidade')
                ->filter()
                ->toArray();
            
            $existingSeries = \App\Models\Matricula::whereNotNull('ultima_serie')
                ->distinct()
                ->pluck('ultima_serie')
                ->filter()
                ->toArray();
            
            // Mesclar com os valores padrão
            $allCourses = array_unique(array_merge(array_values($availableCourses), $existingCourses));
            $allModalities = array_unique(array_merge(array_values($availableModalities), $existingModalities));
            $allSeries = array_unique(array_merge($defaultSeries, $existingSeries));
            
            // Ordenar os arrays
            sort($allCourses);
            sort($allModalities);
            sort($allSeries);
            
        } catch (\Exception $e) {
            // Em caso de erro, usar apenas os valores padrão
            $allCourses = array_values($availableCourses);
            $allModalities = array_values($availableModalities);
            $allSeries = $defaultSeries;
        }
        
        return [
            // Chaves originais (para compatibilidade)
            'available_courses' => $availableCourses,
            'available_modalities' => $availableModalities,
            'default_course' => self::get('default_course', 'excel'),
            'default_modality' => self::get('default_modality', 'ensino-medio'),
            
            // Chaves esperadas pelo formulário de edição
            'cursos' => $allCourses,
            'modalidades' => $allModalities,
            'series' => $allSeries
        ];
    }

    /**
     * Configurações específicas para aparência (logos)
     */
    public static function getAppearanceSettings()
    {
        return [
            'sidebar_logo_path' => self::get('sidebar_logo_path', '/assets/images/logotipo-dark.svg'),
            'login_logo_path' => self::get('login_logo_path', '/assets/images/logotipo-dark.svg'),
        ];
    }

    /**
     * Configurações específicas para WhatsApp
     */
    public static function getWhatsAppSettings()
    {
        return [
            'whatsapp_enabled' => self::get('whatsapp_enabled', true),
            'whatsapp_number' => self::get('whatsapp_number', '5511999999999'),
            'whatsapp_message' => self::get('whatsapp_message', 'Olá! Tenho interesse no curso EJA. Podem me ajudar?'),
            'whatsapp_button_position' => self::get('whatsapp_button_position', 'bottom-right'),
            'whatsapp_button_color' => self::get('whatsapp_button_color', '#25d366'),
        ];
    }

    /**
     * Atualizar configurações de logo
     */
    public static function updateLogos($sidebarLogo = null, $loginLogo = null)
    {
        if ($sidebarLogo !== null) {
            self::set('sidebar_logo_path', $sidebarLogo, 'string', 'appearance', 'Caminho para o logo do sidebar');
        }
        
        if ($loginLogo !== null) {
            self::set('login_logo_path', $loginLogo, 'string', 'appearance', 'Caminho para o logo da página de login');
        }
    }

    /**
     * Verificar se usuário está em cooldown para travar um NOVO lead
     */
    public static function isUserInCooldown($userId)
    {
        $cooldownMinutes = self::get('lead_cooldown_minutes', 2);
        
        // Buscar o último lead travado (que não foi destravado)
        $lastLock = \App\Models\Inscricao::where('locked_by', $userId)
                                        ->whereNotNull('locked_at')
                                        ->orderBy('locked_at', 'desc')
                                        ->first();

        if (!$lastLock || !$lastLock->locked_at) {
            return false;
        }

        // Verificar se passou o tempo de cooldown desde o último lock
        $cooldownEnd = $lastLock->locked_at->addMinutes($cooldownMinutes);
        return now() < $cooldownEnd;
    }

    /**
     * Obter tempo restante do cooldown em segundos
     */
    public static function getCooldownRemainingSeconds($userId)
    {
        $cooldownMinutes = self::get('lead_cooldown_minutes', 2);
        
        // Buscar o último lead travado (que não foi destravado)
        $lastLock = \App\Models\Inscricao::where('locked_by', $userId)
                                        ->whereNotNull('locked_at')
                                        ->orderBy('locked_at', 'desc')
                                        ->first();

        if (!$lastLock || !$lastLock->locked_at) {
            return 0;
        }

        $cooldownEnd = $lastLock->locked_at->addMinutes($cooldownMinutes);
        $remaining = $cooldownEnd->diffInSeconds(now(), false);
        
        return max(0, -$remaining);
    }

    /**
     * Configurações específicas para email
     */
    public static function getEmailSettings()
    {
        // Limpar cache para garantir que estamos obtendo os valores mais recentes
        \Illuminate\Support\Facades\Cache::forget("system_setting_enable_email");
        \Illuminate\Support\Facades\Cache::forget("system_setting_mail_mailer");
        \Illuminate\Support\Facades\Cache::forget("system_setting_mail_host");
        \Illuminate\Support\Facades\Cache::forget("system_setting_mail_port");
        \Illuminate\Support\Facades\Cache::forget("system_setting_mail_username");
        \Illuminate\Support\Facades\Cache::forget("system_setting_mail_password");
        \Illuminate\Support\Facades\Cache::forget("system_setting_mail_encryption");
        \Illuminate\Support\Facades\Cache::forget("system_setting_mail_from_address");
        \Illuminate\Support\Facades\Cache::forget("system_setting_mail_from_name");
        \Illuminate\Support\Facades\Cache::forget("system_setting_mail_reply_to");
        \Illuminate\Support\Facades\Cache::forget("system_setting_admin_notification_email");
        \Illuminate\Support\Facades\Cache::forget("system_setting_notify_new_lead");
        \Illuminate\Support\Facades\Cache::forget("system_setting_notify_status_change");
        \Illuminate\Support\Facades\Cache::forget("system_setting_send_confirmation_email");
        \Illuminate\Support\Facades\Cache::forget("system_setting_send_admin_notification");
        \Illuminate\Support\Facades\Cache::forget("system_setting_send_followup_email");
        
        return [
            'enable_email' => self::get('enable_email', false),
            'mail_mailer' => self::get('mail_mailer', 'smtp'),
            'mail_host' => self::get('mail_host', 'smtp.gmail.com'),
            'mail_port' => self::get('mail_port', '587'),
            'mail_username' => self::get('mail_username', ''),
            'mail_password' => self::get('mail_password', ''),
            'mail_encryption' => self::get('mail_encryption', 'tls'),
            'mail_from_address' => self::get('mail_from_address', 'contato@ensinocerto.com.br'),
            'mail_from_name' => self::get('mail_from_name', 'EJA Admin'),
            'mail_reply_to' => self::get('mail_reply_to', ''),
            'admin_notification_email' => self::get('admin_notification_email', ''),
            'notify_new_lead' => self::get('notify_new_lead', true),
            'notify_status_change' => self::get('notify_status_change', false),
            'send_confirmation_email' => self::get('send_confirmation_email', true),
            'send_admin_notification' => self::get('send_admin_notification', true),
            'send_followup_email' => self::get('send_followup_email', false),
        ];
    }

    /**
     * Configurações específicas para Evolution API (WhatsApp)
     */
    public static function getEvolutionApiSettings()
    {
        return [
            'base_url' => self::get('evolution_api_base_url', ''),
            'api_key' => self::get('evolution_api_key', ''),
            'instance' => self::get('evolution_api_instance', 'default'),
            'connected' => self::get('evolution_api_connected', false),
            'last_connection' => self::get('evolution_api_last_connection', '')
        ];
    }

    /**
     * Configurações específicas para a página de agradecimento
     */
    public static function getThankYouPageSettings()
    {
        // Limpar cache para garantir que estamos obtendo os valores mais recentes
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_page_title");
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_page_subtitle");
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_header_color");
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_show_contact_info");
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_show_steps");
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_show_tips");
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_contact_phone");
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_contact_email");
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_contact_hours");
        \Illuminate\Support\Facades\Cache::forget("system_setting_thank_you_custom_message");
        
        return [
            'page_title' => self::get('thank_you_page_title', 'Inscrição Confirmada!'),
            'page_subtitle' => self::get('thank_you_page_subtitle', 'Sua inscrição foi realizada com sucesso. Nossa equipe entrará em contato em breve.'),
            'header_color' => self::get('thank_you_header_color', '#3a5998'),
            'show_contact_info' => self::get('thank_you_show_contact_info', true),
            'show_steps' => self::get('thank_you_show_steps', true),
            'show_tips' => self::get('thank_you_show_tips', true),
            'contact_phone' => self::get('thank_you_contact_phone', '(11) 9999-9999'),
            'contact_email' => self::get('thank_you_contact_email', 'contato@ensinocerto.com.br'),
            'contact_hours' => self::get('thank_you_contact_hours', 'Seg-Sex: 8h às 18h'),
            'custom_message' => self::get('thank_you_custom_message', ''),
        ];
    }

    /**
     * Configurações específicas para o countdown
     */
    public static function getCountdownSettings()
    {
        $settings = [
            'enabled' => self::get('countdown_enabled', true),
            'end_date' => self::get('countdown_end_date', '2025-12-31'),
            'end_time' => self::get('countdown_end_time', '23:59'),
            'timezone' => self::get('countdown_timezone', 'America/Sao_Paulo'),
            'text' => self::get('countdown_text', 'Somente até'),
            'discount_text' => self::get('countdown_discount_text', '50% OFF'),
            'price_original' => self::get('countdown_price_original', 'R$ 284,90'),
            'price_discount' => self::get('countdown_price_discount', 'R$ 89,90'),
            'price_installments_original' => self::get('countdown_price_installments_original', '24x'),
            'price_installments_discount' => self::get('countdown_price_installments_discount', '12x'),
            'pix_price' => self::get('countdown_pix_price', 'R$ 899,00'),
            'renewal_type' => self::get('countdown_renewal_type', 'monthly'),
            'auto_extend_days' => self::get('countdown_auto_extend_days', 30),
        ];
        
        // Calcular a data de término completa em JavaScript timestamp
        try {
            $endDateTime = new \DateTime($settings['end_date'] . ' ' . $settings['end_time'], new \DateTimeZone($settings['timezone']));
            $settings['end_timestamp'] = $endDateTime->getTimestamp() * 1000; // JavaScript usa milissegundos
            
            // Formatar data em português
            $months = [
                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
            ];
            
            $day = $endDateTime->format('d');
            $month = $months[(int)$endDateTime->format('n')];
            $year = $endDateTime->format('Y');
            
            $settings['end_date_formatted'] = $day . ' de ' . $month;
            $settings['end_date_month_year'] = $month . ' de ' . $year;
        } catch (\Exception $e) {
            $settings['end_timestamp'] = strtotime('2025-12-31 23:59:59') * 1000;
            $settings['end_date_formatted'] = '31 de Dezembro';
            $settings['end_date_month_year'] = 'Dezembro de 2025';
        }
        
        return $settings;
    }
    
    /**
     * Verificar se o countdown expirou e renovar automaticamente se configurado
     */
    public static function checkAndRenewCountdown()
    {
        $settings = self::getCountdownSettings();
        
        if (!$settings['enabled']) {
            return false;
        }
        
        $now = time();
        $endTime = $settings['end_timestamp'] / 1000; // Converter de volta para segundos
        
        // Se não expirou, não fazer nada
        if ($now < $endTime) {
            return false;
        }
        
        // Se expirou e tem renovação automática ativada
        if ($settings['renewal_type'] !== 'manual') {
            $extendDays = $settings['auto_extend_days'];
            
            switch ($settings['renewal_type']) {
                case 'daily':
                    $extendDays = 1;
                    break;
                case 'weekly':
                    $extendDays = 7;
                    break;
                case 'monthly':
                    $extendDays = 30;
                    break;
            }
            
            // Calcular nova data
            $newEndDate = new \DateTime($settings['end_date'] . ' ' . $settings['end_time'], new \DateTimeZone($settings['timezone']));
            $newEndDate->add(new \DateInterval('P' . $extendDays . 'D'));
            
            // Atualizar configurações
            self::set('countdown_end_date', $newEndDate->format('Y-m-d'), 'string', 'countdown', 'Data de término da oferta (formato: YYYY-MM-DD)');
            
            return true;
        }
        
        return false;
    }

    /**
     * Configurações específicas para a landing page
     */
    public static function getLandingPageSettings()
    {
        // Limpar cache para garantir que estamos obtendo os valores mais recentes
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_hero_title");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_hero_subtitle");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_cta_button_text");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_cta_button_color");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_benefits_title");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_benefit_1");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_benefit_2");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_benefit_3");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_benefit_4");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_form_title");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_form_subtitle");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_form_button_text");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_form_button_color");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_footer_company_name");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_footer_email");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_footer_phone");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_footer_address");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_footer_copyright");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_social_facebook");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_social_instagram");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_social_linkedin");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_social_youtube");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_social_tiktok");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_primary_color");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_secondary_color");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_accent_color");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_mec_authorization_file");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_mec_address");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_footer_image_1");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_footer_image_2");
        \Illuminate\Support\Facades\Cache::forget("system_setting_landing_footer_image_3");
        
        return [
            'hero_title' => self::get('landing_hero_title', 'Transforme sua vida com Ensino Superior de Qualidade!'),
            'hero_subtitle' => self::get('landing_hero_subtitle', 'Diplomas reconhecidos pelo MEC, aulas 100% online e suporte dedicado para sua jornada acadêmica.'),
            'cta_button_text' => self::get('landing_cta_button_text', 'QUERO MINHA VAGA!'),
            'cta_button_color' => self::get('landing_cta_button_color', '#28a745'),
            'benefits_title' => self::get('landing_benefits_title', 'Por que escolher a Ensino Certo?'),
            'benefit_1' => self::get('landing_benefit_1', '✅ Diplomas Reconhecidos pelo MEC'),
            'benefit_2' => self::get('landing_benefit_2', '✅ Aulas 100% Online'),
            'benefit_3' => self::get('landing_benefit_3', '✅ Preços Acessíveis'),
            'benefit_4' => self::get('landing_benefit_4', '✅ Suporte Especializado'),
            'form_title' => self::get('landing_form_title', 'Preencha seus dados e garanta sua vaga!'),
            'form_subtitle' => self::get('landing_form_subtitle', 'É rápido, fácil e gratuito!'),
            'form_button_text' => self::get('landing_form_button_text', 'GARANTIR MINHA VAGA'),
            'form_button_color' => self::get('landing_form_button_color', '#dc3545'),
            'footer_company_name' => self::get('landing_footer_company_name', 'Centro de Ensino Certo Educacional'),
            'footer_email' => self::get('landing_footer_email', 'contato@ensinocerto.com.br'),
            'footer_phone' => self::get('landing_footer_phone', '(11) 91701-2033'),
            'footer_address' => self::get('landing_footer_address', 'Av. José Caballero, 231 - Vila Bastos, Santo André - SP, 09040-210'),
            'footer_copyright' => self::get('landing_footer_copyright', '© 2024 Ensino Certo. Todos os direitos reservados.'),
            'social_facebook' => self::get('landing_social_facebook', ''),
            'social_instagram' => self::get('landing_social_instagram', ''),
            'social_linkedin' => self::get('landing_social_linkedin', ''),
            'social_youtube' => self::get('landing_social_youtube', ''),
            'social_tiktok' => self::get('landing_social_tiktok', ''),
            'primary_color' => self::get('landing_primary_color', '#007bff'),
            'secondary_color' => self::get('landing_secondary_color', '#6c757d'),
            'accent_color' => self::get('landing_accent_color', '#28a745'),
            'mec_authorization_file' => self::get('landing_mec_authorization_file', ''),
            'mec_address' => self::get('landing_mec_address', ''),
            'footer_image_1' => self::get('landing_footer_image_1', ''),
            'footer_image_2' => self::get('landing_footer_image_2', ''),
            'footer_image_3' => self::get('landing_footer_image_3', ''),
            'chat_enabled' => self::get('landing_chat_enabled', false),
            'chat_title' => self::get('landing_chat_title', 'Precisa de ajuda?'),
            'chat_welcome_message' => self::get('landing_chat_welcome_message', 'Olá! Como posso ajudá-lo hoje?'),
            'chat_position' => self::get('landing_chat_position', 'bottom-right'),
            'chat_color' => self::get('landing_chat_color', '#007bff'),
            'chat_icon' => self::get('landing_chat_icon', 'fas fa-comments'),
            'gtm_enabled' => self::get('landing_gtm_enabled', false),
            'gtm_id' => self::get('landing_gtm_id', ''),
            'gtm_events' => self::get('landing_gtm_events', ''),
        ];
    }

    /**
     * Configurações específicas para o ChatGPT
     */
    public static function getAiSettings()
    {
        return [
            'api_key' => self::get('ai_api_key', ''),
            'model' => self::get('ai_model', 'gpt-4o-mini'),
            'system_prompt' => self::get('ai_system_prompt', 'Você é um especialista em email marketing educacional e designer de templates HTML profissionais. Crie conteúdo persuasivo, responsivo e otimizado para conversão, sempre incluindo as variáveis fornecidas pelo usuário.'),
            'email_template_prompt' => self::get('ai_email_template_prompt', self::getDefaultEmailTemplatePrompt()),
            'whatsapp_template_prompt' => self::get('ai_whatsapp_template_prompt', self::getDefaultWhatsAppTemplatePrompt()),
            'contract_template_prompt' => self::get('ai_contract_template_prompt', self::getDefaultContractTemplatePrompt()),
            'payment_template_prompt' => self::get('ai_payment_template_prompt', self::getDefaultPaymentTemplatePrompt()),
            'enrollment_template_prompt' => self::get('ai_enrollment_template_prompt', self::getDefaultEnrollmentTemplatePrompt()),
            'matriculation_template_prompt' => self::get('ai_matriculation_template_prompt', self::getDefaultMatriculationTemplatePrompt()),
            'support_prompt' => self::get('ai_support_prompt', self::getDefaultSupportPrompt()),
            'is_active' => self::get('ai_is_active', false),
        ];
    }

    /**
     * Prompt padrão para templates de email
     */
    private static function getDefaultEmailTemplatePrompt()
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
    private static function getDefaultWhatsAppTemplatePrompt()
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
    private static function getDefaultContractTemplatePrompt()
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
    private static function getDefaultPaymentTemplatePrompt()
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
    private static function getDefaultEnrollmentTemplatePrompt()
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
    private static function getDefaultMatriculationTemplatePrompt()
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

    /**
     * Obter prompt padrão para suporte ao cliente
     */
    public static function getDefaultSupportPrompt(): string
    {
        return 'Você é um assistente virtual especializado em EJA (Educação de Jovens e Adultos) Supletivo da Ensino Certo, uma instituição de ensino superior reconhecida pelo MEC.

## 🎯 SUA FUNÇÃO PRINCIPAL
Você é um especialista em EJA Supletivo com autonomia para:
- Explicar o funcionamento completo do sistema EJA
- Orientar sobre processos de matrícula e documentação
- Fornecer informações sobre cursos, disciplinas e carga horária
- Explicar metodologia de ensino e avaliações
- Orientar sobre pagamentos e financeiro
- Ajudar com questões acadêmicas e certificação

## 📚 CONHECIMENTO ESPECÍFICO SOBRE EJA SUPLETIVO

### **O que é EJA Supletivo:**
- Modalidade de ensino para jovens e adultos que não concluíram estudos
- Reconhecimento de conhecimentos prévios e experiência de vida
- Flexibilidade de horários e metodologia adaptada
- Certificação oficial com validade nacional

### **Metodologia de Ensino:**
- Estudos dirigidos com material didático específico
- Acompanhamento pedagógico personalizado
- Avaliações por competências e habilidades
- Reconhecimento de saberes adquiridos na vida prática

### **Cursos Disponíveis:**
- Ensino Fundamental (1º ao 9º ano)
- Ensino Médio completo
- Cursos técnicos integrados
- Preparação para ENEM e vestibulares

## 🔍 AUTONOMIA PARA BUSCAR INFORMAÇÕES

### **Dados que você pode acessar automaticamente:**
- **Matrículas do aluno**: Status, curso, data de matrícula, progresso
- **Pagamentos**: Valores, vencimentos, status, formas de pagamento
- **Disciplinas**: Carga horária, notas, frequência, aprovação
- **Documentação**: Status de documentos enviados, pendências
- **Calendário acadêmico**: Datas importantes, prazos, feriados
- **Contatos**: Equipe pedagógica, coordenação, secretaria

### **Como usar essas informações:**
- Sempre verifique dados reais antes de responder
- Compare informações com o que o aluno está relatando
- Identifique inconsistências e oriente sobre correções
- Forneça dados precisos sobre prazos e valores

## 💬 EXEMPLOS DE ATENDIMENTO

### **Exemplo 1 - Dúvida sobre Matrícula:**
```
Usuário: "Como está minha matrícula no curso de EJA?"
Ação: Verificar status da matrícula, curso, data de início
Resposta: "Verifiquei sua matrícula e está [STATUS]. Você está cursando [CURSO] desde [DATA]. [DETALHES ESPECÍFICOS]"
```

### **Exemplo 2 - Questão Financeira:**
```
Usuário: "Tenho alguma mensalidade em atraso?"
Ação: Verificar pagamentos pendentes e vencidos
Resposta: "Analisando seus pagamentos, identifiquei [X] mensalidades pendentes. Próximo vencimento: [DATA], valor: [VALOR]"
```

### **Exemplo 3 - Dúvida Acadêmica:**
```
Usuário: "Quantas disciplinas faltam para eu concluir o curso?"
Ação: Verificar disciplinas cursadas vs. necessárias
Resposta: "De acordo com seu histórico, você já concluiu [X] disciplinas. Faltam [Y] disciplinas para concluir o curso."
```

## 📋 PROCESSOS QUE VOCÊ PODE EXPLICAR

### **Matrícula:**
1. Documentação necessária (RG, CPF, histórico escolar)
2. Avaliação de conhecimentos prévios
3. Definição do plano de estudos
4. Escolha do horário de atendimento
5. Formalização da matrícula

### **Avaliações:**
1. Tipos de avaliação (diagnóstica, formativa, somativa)
2. Critérios de aprovação
3. Recuperação e segunda chamada
4. Reconhecimento de saberes prévios

### **Certificação:**
1. Requisitos para conclusão
2. Documentos para diploma
3. Validação pelo MEC
4. Prazos para emissão

## 🚨 SITUAÇÕES ESPECIAIS

### **Quando o aluno deve procurar atendimento presencial:**
- Problemas com documentação oficial
- Questões financeiras complexas
- Reclamações formais
- Solicitações de transferência
- Problemas de saúde que afetam estudos

### **O que você NÃO pode fazer:**
- Alterar dados cadastrais
- Processar pagamentos
- Emitir documentos oficiais
- Alterar notas ou frequência
- Resolver problemas técnicos da plataforma

## 🎨 ESTILO DE COMUNICAÇÃO

### **Características:**
- **Profissional mas acolhedor**: Use linguagem clara e acessível
- **Empático**: Reconheça as dificuldades do aluno
- **Orientador**: Sempre ofereça próximos passos
- **Preciso**: Use dados reais da plataforma
- **Motivador**: Encoraje a continuidade dos estudos

### **Linguagem:**
- Use termos técnicos com explicações simples
- Evite jargões desnecessários
- Seja direto e objetivo
- Use emojis ocasionalmente para tornar mais amigável
- Mantenha tom positivo e encorajador

### **Formatação de Mensagens:**
- Use **NEGRITO** para status importantes (ex: **ATIVA**, **PENDENTE**, **APROVADA**)
- Use 🟢 VERDE para valores pagos e situações positivas
- Use 🟠 LARANJA para valores pendentes e situações que precisam de atenção
- Use 🔴 VERMELHO para valores vencidos e situações críticas
- Use 📱 para indicar contato via WhatsApp
- Use 💰 para valores monetários
- Use 📅 para datas e prazos

### **Exemplos de Formatação:**
- Status da matrícula: **ATIVA** 🟢
- Mensalidade paga: 💰 R$ 150,00 🟢
- Mensalidade pendente: 💰 R$ 150,00 🟠 (Vence: 📅 15/08/2024)
- Mensalidade vencida: 💰 R$ 150,00 🔴 (Venceu: 📅 15/07/2024)

### **Botão de WhatsApp:**
Quando o usuário solicitar atendimento via WhatsApp, sempre inclua:
📱 **Atendimento via WhatsApp**
Clique no botão abaixo para conversar diretamente com nossa equipe:
[BOTÃO_WHATSAPP]
Nossa equipe está pronta para ajudá-lo de forma mais personalizada!';
    }

    /**
     * Configurações específicas para contratos
     */
    public static function getContractSettings()
    {
        return [
            'enable_school_signature' => self::get('contract_enable_school_signature', false),
            'school_signature_name' => self::get('contract_school_signature_name', ''),
            'school_signature_title' => self::get('contract_school_signature_title', ''),
            'school_signature_data' => self::get('contract_school_signature_data', ''),
            'validity_days' => self::get('contract_validity_days', 30),
            'reminder_days' => self::get('contract_reminder_days', 3),
            'auto_send' => self::get('contract_auto_send', false),
            'auto_reminder' => self::get('contract_auto_reminder', false),
        ];
    }

    /**
     * Configurações específicas para pagamentos (Mercado Pago)
     */
    public static function getPaymentSettings()
    {
        return [
            'mercadopago_enabled' => self::get('mercadopago_enabled', false),
            'mercadopago_sandbox' => self::get('mercadopago_sandbox', false),
            
            // Credenciais de produção (campos existentes)
            'mercadopago_access_token' => self::get('mercadopago_access_token', ''),
            'mercadopago_public_key' => self::get('mercadopago_public_key', ''),
            'mercadopago_webhook_secret' => self::get('mercadopago_webhook_secret', ''),
            
            // Credenciais específicas para sandbox
            'mercadopago_sandbox_access_token' => self::get('mercadopago_sandbox_access_token', ''),
            'mercadopago_sandbox_public_key' => self::get('mercadopago_sandbox_public_key', ''),
            
            'mercadopago_email_notifications' => self::get('mercadopago_email_notifications', true),
            'mercadopago_whatsapp_notifications' => self::get('mercadopago_whatsapp_notifications', false),
            'mercadopago_sms_notifications' => self::get('mercadopago_sms_notifications', false),
            'mercadopago_auto_reminders' => self::get('mercadopago_auto_reminders', true),
            'mercadopago_auto_generation' => self::get('mercadopago_auto_generation', true),
            'mercadopago_currency' => self::get('mercadopago_currency', 'BRL'),
            'mercadopago_country' => self::get('mercadopago_country', 'BR'),
            
            // Configurações legadas (manter por compatibilidade)
            'payment_auto_charge' => self::get('payment_auto_charge', true),
            'payment_email_notifications' => self::get('payment_email_notifications', true),
            'payment_whatsapp_notifications' => self::get('payment_whatsapp_notifications', true),
            'payment_reminder_days' => self::get('payment_reminder_days', 3),
            'payment_overdue_days' => self::get('payment_overdue_days', 7),
        ];
    }
}
