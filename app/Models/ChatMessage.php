<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tokens_used',
        'response_time_ms',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'response_time_ms' => 'integer',
        'tokens_used' => 'integer',
    ];

    /**
     * Relacionamento com a conversa
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    /**
     * Verificar se é uma mensagem do usuário
     */
    public function isUserMessage(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Verificar se é uma mensagem do assistente
     */
    public function isAssistantMessage(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Verificar se é uma mensagem do sistema
     */
    public function isSystemMessage(): bool
    {
        return $this->role === 'system';
    }
}
