<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'description',
        'content',
        'category',
        'type',
        'is_ai_generated',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_ai_generated' => 'boolean',
    ];

    /**
     * Relacionamento com o usuário que criou o template
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relacionamento com o usuário que atualizou o template
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Escopos para filtrar por categoria
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Escopos para filtrar por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Escopos para templates gerados por IA
     */
    public function scopeAiGenerated($query)
    {
        return $query->where('is_ai_generated', true);
    }

    /**
     * Escopos para templates manuais
     */
    public function scopeManual($query)
    {
        return $query->where('is_ai_generated', false);
    }

    /**
     * Obter badge da categoria
     */
    public function getCategoryBadgeAttribute()
    {
        $badges = [
            'welcome' => '<span class="badge bg-success">Boas-vindas</span>',
            'followup' => '<span class="badge bg-info">Follow-up</span>',
            'promotional' => '<span class="badge bg-warning">Promocional</span>',
            'informational' => '<span class="badge bg-primary">Informativo</span>',
            'invitation' => '<span class="badge bg-purple">Convite</span>',
            'reminder' => '<span class="badge bg-orange">Lembrete</span>',
            'thank_you' => '<span class="badge bg-pink">Agradecimento</span>',
            'custom' => '<span class="badge bg-secondary">Personalizado</span>',
        ];

        return $badges[$this->category] ?? '<span class="badge bg-secondary">Outro</span>';
    }

    /**
     * Obter ícone do tipo
     */
    public function getTypeIconAttribute()
    {
        $icons = [
            'marketing' => 'fas fa-bullhorn',
            'transactional' => 'fas fa-receipt',
            'newsletter' => 'fas fa-newspaper',
            'automation' => 'fas fa-robot',
            'custom' => 'fas fa-cogs',
        ];

        return $icons[$this->type] ?? 'fas fa-envelope';
    }
} 