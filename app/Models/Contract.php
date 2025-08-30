<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'matricula_id',
        'created_by',
        'template_id',
        'contract_number',
        'title',
        'content',
        'variables',
        'access_token',
        'access_expires_at',
        'student_email',
        'email_verified_at',
        'status',
        'sent_at',
        'viewed_at',
        'signed_at',
        'signature_data',
        'signature_ip',
        'signature_metadata',
        'school_signature_data',
        'school_signature_name',
        'school_signature_title',
        'school_signed_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'signature_metadata' => 'array',
        'access_expires_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contract) {
            // Gerar número único do contrato
            $contract->contract_number = 'CONT-' . date('Y') . '-' . str_pad(
                Contract::whereYear('created_at', date('Y'))->count() + 1,
                6,
                '0',
                STR_PAD_LEFT
            );
            
            // Gerar token de acesso único
            $contract->access_token = Str::random(64);
            
            // Definir expiração padrão (30 dias)
            $contract->access_expires_at = Carbon::now()->addDays(30);
            
            // Registrar usuário criador
            if (auth()->check()) {
                $contract->created_by = auth()->id();
            }
        });
    }

    /**
     * Relacionamento com matrícula
     */
    public function matricula()
    {
        return $this->belongsTo(Matricula::class);
    }

    /**
     * Relacionamento com template
     */
    public function template()
    {
        return $this->belongsTo(ContractTemplate::class);
    }

    /**
     * Relacionamento com usuário criador
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Gerar novo token de acesso
     */
    public function generateNewAccessToken($validityDays = 30)
    {
        $this->access_token = Str::random(64);
        $this->access_expires_at = Carbon::now()->addDays($validityDays);
        $this->save();
        
        return $this->access_token;
    }

    /**
     * Verificar se o token ainda é válido
     */
    public function isTokenValid()
    {
        return $this->access_expires_at && $this->access_expires_at->isFuture();
    }

    /**
     * Verificar se o contrato pode ser assinado
     */
    public function canBeSigned()
    {
        return in_array($this->status, ['sent', 'viewed']) && $this->isTokenValid();
    }

    /**
     * Marcar contrato como visualizado
     */
    public function markAsViewed()
    {
        if ($this->status === 'sent') {
            $this->update([
                'status' => 'viewed',
                'viewed_at' => now(),
            ]);
        }
    }

    /**
     * Assinar contrato
     */
    public function sign($signatureData, $ipAddress, $metadata = [])
    {
        if (!$this->canBeSigned()) {
            throw new \Exception('Contrato não pode ser assinado no status atual ou token expirado.');
        }

        // Obter configurações de contrato
        $contractSettings = \App\Models\SystemSetting::getContractSettings();
        
        // Preparar dados de assinatura
        $updateData = [
            'status' => 'signed',
            'signed_at' => now(),
            'signature_data' => $signatureData,
            'signature_ip' => $ipAddress,
            'signature_metadata' => $metadata,
        ];
        
        // Adicionar assinatura da escola se configurada
        if ($contractSettings['enable_school_signature'] && !empty($contractSettings['school_signature_data'])) {
            $updateData['school_signature_data'] = $contractSettings['school_signature_data'];
            $updateData['school_signature_name'] = $contractSettings['school_signature_name'];
            $updateData['school_signature_title'] = $contractSettings['school_signature_title'];
            $updateData['school_signed_at'] = now();
        }

        $this->update($updateData);
    }

    /**
     * Verificar email do aluno
     */
    public function verifyEmail()
    {
        $this->email_verified_at = now();
        $this->save();
    }

    /**
     * Processar variáveis no conteúdo
     */
    public function processContent()
    {
        $content = $this->content;
        $variables = $this->variables ?? [];
        
        // Substituir variáveis no formato {{variavel}}
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Atualizar variáveis do contrato com dados atuais da matrícula
     */
    public function updateVariables()
    {
        if (!$this->matricula) {
            return false;
        }

        $matricula = $this->matricula;
        
        // Gerar variáveis atualizadas
        $updatedVariables = [
            'student_name' => $matricula->nome_completo,
            'student_email' => $matricula->email,
            'student_cpf' => $matricula->cpf_formatado ?? $matricula->cpf,
            'student_rg' => $matricula->rg,
            'student_phone' => $matricula->telefone_celular_formatado ?? $matricula->telefone_celular,
            'student_address' => $matricula->endereco_completo,
            'student_birth_date' => $matricula->data_nascimento ? $matricula->data_nascimento->format('d/m/Y') : '',
            'student_nationality' => $matricula->nacionalidade,
            'student_civil_status' => $matricula->estado_civil,
            'student_mother_name' => $matricula->nome_mae,
            'student_father_name' => $matricula->nome_pai,
            
            'course_name' => $matricula->curso,
            'course_modality' => $matricula->modalidade_formatada ?? $matricula->modalidade,
            'course_shift' => 'EAD - Ensino a Distância',
            'tuition_value' => $matricula->valor_mensalidade && $matricula->valor_mensalidade > 0 
                ? 'R$ ' . number_format($matricula->valor_mensalidade, 2, ',', '.') 
                : 'A definir',
            'enrollment_value' => $matricula->valor_matricula && $matricula->valor_matricula > 0 
                ? 'R$ ' . number_format($matricula->valor_matricula, 2, ',', '.') 
                : 'A definir',
            'enrollment_number' => $matricula->numero_matricula,
            'enrollment_date' => $matricula->created_at->format('d/m/Y'),
            'due_date' => $matricula->dia_vencimento ?: 'A definir',
            'payment_method' => $matricula->forma_pagamento_formatada ?? $matricula->forma_pagamento ?? 'A definir',
            
            'school_name' => config('app.name'),
            'current_date' => now()->format('d/m/Y'),
            'current_year' => now()->format('Y'),
            'contract_date' => now()->format('d/m/Y'),
        ];

        // Atualizar variáveis e conteúdo
        $this->variables = $updatedVariables;
        
        // Reprocessar conteúdo se houver template
        if ($this->template) {
            $content = $this->template->content;
            foreach ($updatedVariables as $key => $value) {
                $content = str_replace('{{' . $key . '}}', $value, $content);
            }
            $this->content = $content;
        }

        $this->save();
        
        return true;
    }

    /**
     * Gerar link de acesso público
     */
    public function getAccessLink()
    {
        return route('contracts.access', $this->access_token);
    }

    /**
     * Obter status formatado
     */
    public function getStatusFormattedAttribute()
    {
        return match($this->status) {
            'draft' => 'Rascunho',
            'sent' => 'Enviado',
            'viewed' => 'Visualizado',
            'signed' => 'Assinado',
            'expired' => 'Expirado',
            'cancelled' => 'Cancelado',
            default => $this->status,
        };
    }

    /**
     * Obter cor do status
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'sent' => 'info',
            'viewed' => 'warning',
            'signed' => 'success',
            'expired' => 'danger',
            'cancelled' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Verificar se está expirado
     */
    public function isExpired()
    {
        return $this->access_expires_at && $this->access_expires_at->isPast();
    }

    /**
     * Atualizar status para expirado se necessário
     */
    public function checkExpiration()
    {
        if ($this->isExpired() && !in_array($this->status, ['signed', 'cancelled'])) {
            $this->update(['status' => 'expired']);
        }
    }

    /**
     * Scope para contratos ativos
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'expired']);
    }

    /**
     * Scope para contratos por matrícula
     */
    public function scopeForMatricula($query, $matriculaId)
    {
        return $query->where('matricula_id', $matriculaId);
    }

    /**
     * Scope para contratos assinados
     */
    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    /**
     * Scope para contratos pendentes
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['sent', 'viewed']);
    }
}
