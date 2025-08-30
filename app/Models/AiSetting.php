<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $fillable = [
        'api_key',
        'model',
        'system_prompt',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Retorna as configurações ativas
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }
} 