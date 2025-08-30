<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscricao extends Model
{
        protected $fillable = [
        'nome',
        'email',
        'telefone',
        'curso',
        'modalidade',
        'termos',
        'ip_address',
        'etiqueta',
        'locked_by',
        'locked_at',
        'notas',
        'todolist',
        'photos',
        'prioridade',
        'kanban_order',
        'ultimo_contato',
        'proximo_followup'
    ];

    protected $casts = [
        'termos' => 'boolean',
        'locked_at' => 'datetime',
        'todolist' => 'array',
        'photos' => 'array',
        'ultimo_contato' => 'datetime',
        'proximo_followup' => 'datetime'
    ];

    /**
     * Relacionamento com histórico de status
     */
    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class);
    }

    /**
     * Último histórico de status
     */
    public function ultimoHistorico()
    {
        return $this->hasOne(StatusHistory::class)->latest('data_alteracao');
    }

    /**
     * Verifica se a inscrição está travada
     */
    public function isLocked(): bool
    {
        return !is_null($this->locked_by) && !is_null($this->locked_at);
    }

    /**
     * Verifica se a inscrição está travada por outro usuário
     */
    public function isLockedByOther($userId): bool
    {
        return $this->isLocked() && $this->locked_by !== $userId;
    }

    /**
     * Verifica se a inscrição está travada pelo usuário especificado
     */
    public function isLockedBy($userId): bool
    {
        return $this->isLocked() && $this->locked_by === $userId;
    }

    /**
     * Trava a inscrição para o usuário especificado
     */
    public function lock($userId): void
    {
        // Só atualizar se não está travado pelo mesmo usuário
        if (!$this->isLockedBy($userId)) {
            $updateData = [
                'locked_by' => $userId,
                'locked_at' => now()
            ];

            // Se o lead não tem etiqueta ou está pendente, atribuir à primeira coluna do usuário
            if (!$this->etiqueta || $this->etiqueta === 'pendente') {
                $firstColumn = \App\Models\KanbanColumn::forUser($userId)
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->first();
                
                if ($firstColumn) {
                    $updateData['etiqueta'] = $firstColumn->slug;
                }
            }

            $this->update($updateData);
        }
    }

    /**
     * Destrava a inscrição
     */
    public function unlock(): void
    {
        $this->update([
            'locked_by' => null,
            'locked_at' => null
        ]);
    }

    /**
     * Relacionamento com o usuário que travou a inscrição
     */
    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}
