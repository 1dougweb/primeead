<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    protected $fillable = [
        'session_id',
        'user_email',
        'user_name',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Relacionamento com as mensagens da conversa
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    /**
     * Obter a última mensagem da conversa
     */
    public function getLastMessage()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Verificar se a conversa está ativa
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Marcar conversa como encerrada
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }
}
