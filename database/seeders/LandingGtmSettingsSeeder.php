<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class LandingGtmSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configurações do Google Tag Manager para Landing Page
        SystemSetting::set('landing_gtm_enabled', false, 'boolean', 'landing_page', 'Ativar Google Tag Manager na landing page');
        SystemSetting::set('landing_gtm_id', '', 'string', 'landing_page', 'ID do Google Tag Manager para landing page');
        SystemSetting::set('landing_gtm_events', '', 'text', 'landing_page', 'Eventos personalizados do GTM para landing page');
        
        $this->command->info('Configurações de GTM da landing page configuradas com sucesso!');
    }
}
