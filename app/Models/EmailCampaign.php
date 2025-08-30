<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'subject',
        'content',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'sent_at',
        'total_recipients',
        'pending_count',
        'sent_count',
        'opened_count',
        'clicked_count',
        'failed_count',
        'created_by'
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Obter o usuário que criou a campanha.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obter os destinatários da campanha.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'campaign_id');
    }
    
    /**
     * Verificar se a campanha pode ser editada.
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']);
    }
    
    /**
     * Verificar se a campanha pode ser enviada.
     */
    public function canSend(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']) && $this->total_recipients > 0;
    }
    
    /**
     * Verificar se a campanha pode ser cancelada.
     */
    public function canCancel(): bool
    {
        return in_array($this->status, ['scheduled', 'sending']);
    }
    
    /**
     * Verificar se a campanha está concluída.
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['sent', 'canceled']);
    }
    
    /**
     * Calcular a taxa de abertura.
     */
    public function openRate(): float
    {
        if ($this->sent_count <= 0) {
            return 0;
        }
        
        return round(($this->opened_count / $this->sent_count) * 100, 2);
    }
    
    /**
     * Calcular a taxa de cliques.
     */
    public function clickRate(): float
    {
        if ($this->sent_count <= 0) {
            return 0;
        }
        
        return round(($this->clicked_count / $this->sent_count) * 100, 2);
    }
    
    /**
     * Calcular a taxa de falha.
     */
    public function failureRate(): float
    {
        if ($this->sent_count + $this->failed_count <= 0) {
            return 0;
        }
        
        return round(($this->failed_count / ($this->sent_count + $this->failed_count)) * 100, 2);
    }
    
    /**
     * Atualizar as contagens com base nos destinatários.
     */
    public function updateCounts(): void
    {
        $this->total_recipients = $this->recipients()->count();
        $this->sent_count = $this->recipients()->where('status', 'sent')->count();
        $this->opened_count = $this->recipients()->whereNotNull('opened_at')->count();
        $this->clicked_count = $this->recipients()->whereNotNull('clicked_at')->count();
        $this->failed_count = $this->recipients()->where('status', 'failed')->count();
        $this->save();
    }
}
