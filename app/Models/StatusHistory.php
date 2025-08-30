<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'inscricao_id',
        'status_anterior',
        'status_novo',
        'alterado_por',
        'tipo_usuario',
        'observacoes',
        'data_alteracao'
    ];

    protected $casts = [
        'data_alteracao' => 'datetime'
    ];

    /**
     * Relacionamento com a inscrição
     */
    public function inscricao()
    {
        return $this->belongsTo(Inscricao::class);
    }

    /**
     * Relacionamento com o usuário que fez a alteração
     */
    public function usuario()
    {
        // Como alterado_por é uma string, não podemos fazer relacionamento direto
        // Retornamos null para evitar erros na view
        return null;
    }

    /**
     * Formatar nome do status para exibição
     */
    public function getStatusAnteriorFormatadoAttribute()
    {
        return $this->formatarStatus($this->status_anterior);
    }

    public function getStatusNovoFormatadoAttribute()
    {
        return $this->formatarStatus($this->status_novo);
    }

    private function formatarStatus($status)
    {
        $statusMap = [
            'pendente' => '🟡 Pendente',
            'contatado' => '🔵 Contatado',
            'interessado' => '🟢 Interessado',
            'nao_interessado' => '🔴 Não Interessado',
            'matriculado' => '⭐ Matriculado'
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * Formatar tipo de usuário
     */
    public function getTipoUsuarioFormatadoAttribute()
    {
        $tiposMap = [
            'admin' => '👑 Admin',
            'vendedor' => '💼 Vendedor',
            'colaborador' => '👤 Colaborador',
            'midia' => '📱 Mídia'
        ];

        return $tiposMap[$this->tipo_usuario] ?? $this->tipo_usuario;
    }
}
