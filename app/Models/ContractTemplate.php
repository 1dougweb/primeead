<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'content',
        'available_variables',
        'is_active',
        'is_default',
        'validity_days',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'available_variables' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (auth()->check()) {
                $template->created_by = auth()->id();
                $template->updated_by = auth()->id();
            }
        });

        static::updating(function ($template) {
            if (auth()->check()) {
                $template->updated_by = auth()->id();
            }
        });

        // Garantir que apenas um template seja padrão
        static::saving(function ($template) {
            if ($template->is_default) {
                static::where('is_default', true)
                    ->where('id', '!=', $template->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Relacionamento com usuário criador
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relacionamento com usuário que atualizou
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relacionamento com contratos gerados
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'template_id');
    }

    /**
     * Gerar contrato a partir do template
     */
    public function generateContract(Matricula $matricula, $customVariables = [])
    {
        // Variáveis padrão do sistema
        $defaultVariables = $this->getDefaultVariables($matricula);
        
        // Mesclar com variáveis customizadas
        $variables = array_merge($defaultVariables, $customVariables);
        
        // Processar conteúdo
        $content = $this->processContent($variables);
        
        return Contract::create([
            'matricula_id' => $matricula->id,
            'template_id' => $this->id,
            'title' => $this->name,
            'content' => $content,
            'variables' => $variables,
            'student_email' => $matricula->email,
            'access_expires_at' => now()->addDays($this->validity_days),
        ]);
    }

    /**
     * Obter variáveis padrão do sistema
     */
    private function getDefaultVariables(Matricula $matricula)
    {
        return [
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
    }

    /**
     * Processar conteúdo substituindo variáveis
     */
    private function processContent($variables)
    {
        $content = $this->content;
        
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Scope para templates ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para template padrão
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Obter template padrão
     */
    public static function getDefault()
    {
        return static::default()->active()->first();
    }

    /**
     * Obter lista de variáveis disponíveis
     */
    public function getAvailableVariablesList()
    {
        return $this->available_variables ?? $this->getSystemVariables();
    }

    /**
     * Obter variáveis do sistema
     */
    public static function getSystemVariables()
    {
        return [
            'student_name' => 'Nome completo do aluno',
            'student_email' => 'Email do aluno',
            'student_cpf' => 'CPF formatado do aluno',
            'student_rg' => 'RG do aluno',
            'student_phone' => 'Telefone do aluno',
            'student_address' => 'Endereço completo do aluno',
            'student_birth_date' => 'Data de nascimento do aluno',
            'student_nationality' => 'Nacionalidade do aluno',
            'student_civil_status' => 'Estado civil do aluno',
            'student_mother_name' => 'Nome da mãe do aluno',
            'student_father_name' => 'Nome do pai do aluno',
            'student_profession' => 'Profissão do aluno',
            'partner_school_name' => 'Nome da escola parceira (se aplicável)',
            'is_partner_student' => 'Se o aluno vem de escola parceira (Sim/Não)',
            'course_name' => 'Nome do curso',
            'course_modality' => 'Modalidade do curso',
            'course_shift' => 'Turno do curso',
            'course_duration' => 'Duração do curso',
            'course_workload' => 'Carga horária do curso',
            'tuition_value' => 'Valor da mensalidade',
            'enrollment_value' => 'Valor da matrícula',
            'enrollment_number' => 'Número da matrícula',
            'enrollment_date' => 'Data da matrícula',
            'due_date' => 'Dia de vencimento',
            'payment_method' => 'Forma de pagamento',
            'school_name' => 'Nome da escola/instituição',
            'school_address' => 'Endereço da escola',
            'school_cnpj' => 'CNPJ da escola',
            'school_phone' => 'Telefone da escola',
            'school_email' => 'Email da escola',
            'director_name' => 'Nome do diretor/responsável',
            'current_date' => 'Data atual',
            'current_year' => 'Ano atual',
            'contract_date' => 'Data do contrato',
            'witness1_name' => 'Nome da primeira testemunha',
            'witness1_cpf' => 'CPF da primeira testemunha',
            'witness2_name' => 'Nome da segunda testemunha',
            'witness2_cpf' => 'CPF da segunda testemunha',
        ];
    }
}
