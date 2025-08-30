<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KanbanColumn extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'order',
        'is_system',
        'is_active',
        'user_id'
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public function leads()
    {
        return $this->hasMany(Inscricao::class, 'etiqueta', 'slug');
    }

    /**
     * Relacionamento com usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar colunas por usuário
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para colunas globais (sistema)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('user_id');
    }
} 