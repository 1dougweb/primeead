<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Parceiro extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_completo',
        'nome_fantasia',
        'razao_social',
        'email',
        'telefone',
        'whatsapp',
        'documento',
        'tipo_documento',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'banco',
        'agencia',
        'conta',
        'pix',
        'experiencia_vendas',
        'motivacao',
        'disponibilidade',
        'status',
        'comissao_percentual',
        'observacoes',
        'data_aprovacao',
        'ultimo_contato',
        'modalidade_parceria',
        'possui_estrutura',
        'plano_negocio',
        'tem_site',
        'site_url',
        'tem_experiencia_educacional',
    ];

    protected $casts = [
        'data_aprovacao' => 'datetime',
        'ultimo_contato' => 'datetime',
        'comissao_percentual' => 'decimal:2',
        'possui_estrutura' => 'boolean',
        'tem_site' => 'boolean',
        'tem_experiencia_educacional' => 'boolean',
    ];

    // Scopes
    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('status', 'ativo');
    }

    public function scopePendentes(Builder $query): Builder
    {
        return $query->where('status', 'pendente');
    }

    public function scopeAprovados(Builder $query): Builder
    {
        return $query->whereIn('status', ['aprovado', 'ativo']);
    }

    // Mutators
    public function setDocumentoAttribute($value)
    {
        $this->attributes['documento'] = preg_replace('/\D/', '', $value);
    }

    public function setTelefoneAttribute($value)
    {
        $this->attributes['telefone'] = preg_replace('/\D/', '', $value);
    }

    public function setWhatsappAttribute($value)
    {
        $this->attributes['whatsapp'] = $value ? preg_replace('/\D/', '', $value) : null;
    }

    public function setCepAttribute($value)
    {
        $this->attributes['cep'] = preg_replace('/\D/', '', $value);
    }

    // Accessors
    public function getDocumentoFormatadoAttribute(): string
    {
        if (!$this->documento) return '';
        
        if ($this->tipo_documento === 'cpf') {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $this->documento);
        } else {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $this->documento);
        }
    }

    public function getTelefoneFormatadoAttribute(): string
    {
        if (!$this->telefone) return '';
        
        $telefone = preg_replace('/\D/', '', $this->telefone);
        
        if (strlen($telefone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        } elseif (strlen($telefone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
        }
        
        return $this->telefone;
    }

    public function getWhatsappFormatadoAttribute(): string
    {
        if (!$this->whatsapp) return '';
        
        $whatsapp = preg_replace('/\D/', '', $this->whatsapp);
        
        if (strlen($whatsapp) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $whatsapp);
        } elseif (strlen($whatsapp) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $whatsapp);
        }
        
        return $this->whatsapp;
    }

    public function getCepFormatadoAttribute(): string
    {
        if (!$this->cep) return '';
        
        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $this->cep);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pendente' => '<span class="badge bg-warning">Pendente</span>',
            'aprovado' => '<span class="badge bg-success">Aprovado</span>',
            'rejeitado' => '<span class="badge bg-danger">Rejeitado</span>',
            'ativo' => '<span class="badge bg-primary">Ativo</span>',
            'inativo' => '<span class="badge bg-secondary">Inativo</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Desconhecido</span>';
    }

    public function getDisponibilidadeFormatadaAttribute()
    {
        $disponibilidades = [
            'meio_periodo' => 'Meio Período',
            'integral' => 'Integral',
            'fins_semana' => 'Fins de Semana',
            'flexivel' => 'Flexível',
        ];

        return $disponibilidades[$this->disponibilidade] ?? 'Não informado';
    }

    public function getNomeExibicaoAttribute(): string
    {
        return $this->nome_fantasia ?: $this->razao_social ?: $this->nome_completo;
    }

    // Métodos auxiliares
    public function aprovar()
    {
        $this->update([
            'status' => 'aprovado',
            'data_aprovacao' => now(),
        ]);
    }

    public function ativar()
    {
        $this->update(['status' => 'ativo']);
    }

    public function rejeitar()
    {
        $this->update(['status' => 'rejeitado']);
    }

    public function inativar()
    {
        $this->update(['status' => 'inativo']);
    }

    public function atualizarUltimoContato()
    {
        $this->update(['ultimo_contato' => now()]);
    }

    /**
     * Relacionamento com o usuário do parceiro
     */
    public function usuario()
    {
        return $this->hasOne(User::class);
    }
}
