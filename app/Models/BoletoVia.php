<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BoletoVia extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'via_number',
        'generated_at',
        'expires_at',
        'boleto_url',
        'digitable_line',
        'barcode_content',
        'financial_institution',
        'status',
        'metadata'
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Relacionamento com o pagamento
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Verificar se a via está ativa
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Verificar se a via expirou
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Verificar se a via foi paga
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Verificar se a via foi cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Obter status formatado
     */
    public function getStatusFormattedAttribute(): string
    {
        return match($this->status) {
            'active' => 'Ativa',
            'expired' => 'Expirada',
            'paid' => 'Paga',
            'cancelled' => 'Cancelada',
            default => 'Desconhecido'
        };
    }

    /**
     * Obter data de geração formatada
     */
    public function getGeneratedAtFormattedAttribute(): string
    {
        return $this->generated_at->format('d/m/Y H:i');
    }

    /**
     * Obter data de expiração formatada
     */
    public function getExpiresAtFormattedAttribute(): string
    {
        return $this->expires_at ? $this->expires_at->format('d/m/Y H:i') : 'Não expira';
    }

    /**
     * Obter número da via formatado
     */
    public function getViaNumberFormattedAttribute(): string
    {
        $suffix = match($this->via_number) {
            1 => 'ª',
            2 => 'ª',
            3 => 'ª',
            default => 'ª'
        };
        
        return $this->via_number . $suffix . ' via';
    }

    /**
     * Cancelar a via
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Marcar como paga
     */
    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }

    /**
     * Marcar como expirada
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Obter URL do boleto
     */
    public function getBoletoUrlAttribute($value): ?string
    {
        return $value ?: null;
    }

    /**
     * Obter linha digitável
     */
    public function getDigitableLineAttribute($value): ?string
    {
        return $value ?: null;
    }

    /**
     * Obter código de barras
     */
    public function getBarcodeContentAttribute($value): ?string
    {
        return $value ?: null;
    }

    /**
     * Obter instituição financeira
     */
    public function getFinancialInstitutionAttribute($value): ?string
    {
        return $value ?: null;
    }

    /**
     * Scope para vias ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para vias expiradas
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope para vias pagas
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope para vias canceladas
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
