<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailCampaignRecipient extends Model
{
    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_id',
        'email',
        'name',
        'custom_fields',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'error_message',
        'tracking_code',
        'open_count',
        'click_count'
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'custom_fields' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    /**
     * Obter a campanha à qual este destinatário pertence.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }
    
    /**
     * Gerar um código de rastreamento único.
     */
    public static function generateTrackingCode(): string
    {
        return Str::random(32);
    }
    
    /**
     * Marcar como enviado.
     */
    public function markSent(): void
    {
        $this->status = 'sent';
        $this->sent_at = now();
        $this->save();
    }
    
    /**
     * Marcar como falha.
     */
    public function markFailed(?string $errorMessage = null): void
    {
        $this->status = 'failed';
        $this->error_message = $errorMessage;
        $this->save();
    }
    
    /**
     * Marcar como aberto.
     */
    public function markOpened(): void
    {
        if ($this->opened_at === null) {
            $this->opened_at = now();
        }
        $this->open_count = ($this->open_count ?? 0) + 1;
        $this->save();
    }
    
    /**
     * Marcar como clicado.
     */
    public function markClicked(): void
    {
        if ($this->clicked_at === null) {
            $this->clicked_at = now();
        }
        $this->click_count = ($this->click_count ?? 0) + 1;
        $this->save();
    }
    
    /**
     * Obter o nome de exibição (nome ou email).
     */
    public function getDisplayName(): string
    {
        return $this->name ?: $this->email;
    }
    
    /**
     * Verificar se o email foi aberto.
     */
    public function isOpened(): bool
    {
        return $this->opened_at !== null;
    }
    
    /**
     * Verificar se o email teve links clicados.
     */
    public function isClicked(): bool
    {
        return $this->clicked_at !== null;
    }
    
    /**
     * Verificar se o envio falhou.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
