<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;

class ThemeService
{
    public function getCurrentTheme(): array
    {
        $now = Carbon::now();
        $hour = $now->hour;
        
        return match (true) {
            $hour >= 6 && $hour < 12 => $this->getMorningTheme(),
            $hour >= 12 && $hour < 18 => $this->getAfternoonTheme(),
            default => $this->getNightTheme()
        };
    }
    
    private function getMorningTheme(): array
    {
        return [
            'period' => 'morning',
            'name' => 'Manhã',
            'greeting' => 'Bom dia!',
            'background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'primary_color' => '#667eea',
            'text_color' => '#ffffff',
            'card_bg' => 'rgba(255, 255, 255, 0.95)',
            'shadow' => '0 8px 32px rgba(102, 126, 234, 0.3)',
            'icon' => 'fas fa-cloud-sun',
            'description' => 'Céu claro da manhã'
        ];
    }
    
    private function getAfternoonTheme(): array
    {
        return [
            'period' => 'afternoon',
            'name' => 'Tarde',
            'greeting' => 'Boa tarde!',
            'background' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'primary_color' => '#f5576c',
            'text_color' => '#ffffff',
            'card_bg' => 'rgba(255, 255, 255, 0.95)',
            'icon' => 'fas fa-sun',
            'description' => 'Sol radiante da tarde'
        ];
    }
    
    private function getNightTheme(): array
    {
        return [
            'period' => 'night',
            'name' => 'Noite',
            'greeting' => 'Boa noite!',
            'background' => 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
            'primary_color' => '#34495e',
            'text_color' => '#ffffff',
            'card_bg' => 'rgba(255, 255, 255, 0.95)',
            'shadow' => '0 8px 32px rgba(52, 73, 94, 0.3)',
            'icon' => 'fas fa-moon',
            'description' => 'Noite serena e tranquila'
        ];
    }
    
    public function getCurrentTime(): array
    {
        $now = Carbon::now();
        
        return [
            'time' => $now->format('H:i'),
            'date' => $now->isoFormat('dddd, D [de] MMMM [de] YYYY'),
            'timezone' => $now->timezoneName,
            'formatted' => $now->format('d/m/Y H:i:s')
        ];
    }
} 