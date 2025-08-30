<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'contact_id',
        'type',
        'channel',
        'status',
        'subject',
        'message',
        'recipient',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'external_id',
        'error_message',
        'metadata'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Relacionamentos
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'pending')
                    ->where('scheduled_at', '<=', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeByContact($query, $contactId)
    {
        return $query->where('contact_id', $contactId);
    }

    /**
     * Métodos auxiliares
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isSent()
    {
        return $this->status === 'sent';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isRead()
    {
        return $this->status === 'read';
    }

    public function isScheduled()
    {
        return $this->isPending() && $this->scheduled_at && $this->scheduled_at <= now();
    }

    public function getTypeLabel()
    {
        return match($this->type) {
            'payment_reminder' => 'Lembrete de Pagamento',
            'payment_overdue' => 'Pagamento em Atraso',
            'payment_confirmed' => 'Pagamento Confirmado',
            'payment_failed' => 'Pagamento Falhou',
            'payment_created' => 'Cobrança Criada',
            'subscription_cancelled' => 'Assinatura Cancelada',
            default => 'Desconhecido'
        };
    }

    public function getChannelLabel()
    {
        return match($this->channel) {
            'email' => 'Email',
            'whatsapp' => 'WhatsApp',
            'sms' => 'SMS',
            default => 'Desconhecido'
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'sent' => 'Enviado',
            'failed' => 'Falhou',
            'delivered' => 'Entregue',
            'read' => 'Lido',
            default => 'Desconhecido'
        };
    }

    public function getStatusColor()
    {
        return match($this->status) {
            'pending' => 'warning',
            'sent' => 'info',
            'failed' => 'danger',
            'delivered' => 'success',
            'read' => 'primary',
            default => 'secondary'
        };
    }

    public function markAsSent($externalId = null)
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'external_id' => $externalId
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);
    }

    public function markAsRead()
    {
        $this->update([
            'status' => 'read',
            'read_at' => now()
        ]);
    }

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }

    public function getChannelIcon()
    {
        return match($this->channel) {
            'email' => 'fas fa-envelope',
            'whatsapp' => 'fab fa-whatsapp',
            'sms' => 'fas fa-sms',
            default => 'fas fa-bell'
        };
    }
}
