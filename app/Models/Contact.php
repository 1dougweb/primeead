<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'whatsapp',
        'notes'
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Formatar WhatsApp para exibição
     */
    public function getFormattedWhatsappAttribute()
    {
        $whatsapp = preg_replace('/\D/', '', $this->whatsapp);
        
        if (strlen($whatsapp) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $whatsapp);
        } elseif (strlen($whatsapp) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $whatsapp);
        }
        
        return $this->whatsapp;
    }

    /**
     * Limpar WhatsApp para armazenamento
     */
    public function setWhatsappAttribute($value)
    {
        $this->attributes['whatsapp'] = preg_replace('/\D/', '', $value);
    }

    /**
     * Obter contatos do usuário
     */
    public static function getByUser($userId)
    {
        return static::where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Obter todos os contatos (para visualização)
     */
    public static function getAllWithUsers()
    {
        return static::with('user')
            ->orderBy('name')
            ->get();
    }
}
