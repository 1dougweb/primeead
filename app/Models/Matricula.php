<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Matricula extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inscricao_id',
        'numero_matricula',
        'nome_completo',
        'data_nascimento',
        'cpf',
        'rg',
        'orgao_emissor',
        'sexo',
        'estado_civil',
        'nacionalidade',
        'naturalidade',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'telefone_fixo',
        'telefone_celular',
        'email',
        'nome_pai',
        'nome_mae',
        'modalidade',
        'curso',
        'ultima_serie',
        'ano_conclusao',
        'escola_origem',
        'status',
        'forma_pagamento',
        'payment_gateway',
        'bank_info',
        'valor_pago',
        'tipo_boleto',
        'valor_total_curso',
        'valor_matricula',
        'valor_mensalidade',
        'numero_parcelas',
        'dia_vencimento',
        'forma_pagamento_mensalidade',
        'parcelas_ativas',
        'parcelas_geradas',
        'parcelas_pagas',
        'percentual_juros',
        'desconto',
        'dia_vencimento',
        'observacoes',
        'escola_parceira',
        'parceiro_id',
        'doc_rg_cpf',
        'doc_comprovante',
        'doc_historico',
        'doc_certificado',
        'doc_outros',
        'google_drive_folder_id'
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'valor_total_curso' => 'decimal:2',
        'valor_matricula' => 'decimal:2',
        'valor_mensalidade' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'percentual_juros' => 'decimal:2',
        'desconto' => 'decimal:2',
        'escola_parceira' => 'boolean',
        'doc_rg_cpf' => 'array',
        'doc_outros' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($matricula) {
            // Gera o número da matrícula de forma segura (ANO + contador sequencial com 6 dígitos)
            $currentYear = date('Y');
            
            // Busca o último número de matrícula do ano atual de forma mais confiável
            $lastMatricula = static::withTrashed()
                ->where('numero_matricula', 'like', $currentYear . '%')
                ->orderBy('numero_matricula', 'desc')
                ->lockForUpdate()
                ->first();
            
            if ($lastMatricula) {
                // Extrai o número sequencial do último registro
                $lastSequencial = (int) substr($lastMatricula->numero_matricula, 4);
                $nextNumber = $lastSequencial + 1;
            } else {
                // Primeiro registro do ano
                $nextNumber = 1;
            }
            
            // Tenta gerar número único até conseguir (máximo 10 tentativas)
            $attempts = 0;
            do {
                $numeroMatricula = $currentYear . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                $exists = static::withTrashed()->where('numero_matricula', $numeroMatricula)->exists();
                
                if (!$exists) {
                    $matricula->numero_matricula = $numeroMatricula;
                    break;
                }
                
                $nextNumber++;
                $attempts++;
            } while ($attempts < 10);
            
            if ($attempts >= 10) {
                throw new \Exception('Não foi possível gerar um número de matrícula único após 10 tentativas');
            }
            
            // Para outros gateways que não sejam Mercado Pago, definir forma_pagamento padrão
            if (isset($matricula->payment_gateway) && $matricula->payment_gateway !== 'mercado_pago') {
                if (empty($matricula->forma_pagamento)) {
                    $matricula->forma_pagamento = 'boleto';
                }
                if (empty($matricula->numero_parcelas)) {
                    $matricula->numero_parcelas = 1;
                }
            }
            
            // Registra o usuário que criou
            if (auth()->check()) {
                $matricula->created_by = auth()->id();
                $matricula->updated_by = auth()->id();
            }
        });

        static::updating(function ($matricula) {
            // Registra o usuário que atualizou
            if (auth()->check()) {
                $matricula->updated_by = auth()->id();
            }
        });

        static::deleting(function ($matricula) {
            // Excluir todos os pagamentos relacionados quando a matrícula for apagada
            if ($matricula->isForceDeleting()) {
                // Exclusão permanente
                $matricula->payments()->forceDelete();
                $matricula->contracts()->forceDelete();
            } else {
                // Soft delete
                $matricula->payments()->delete();
                $matricula->contracts()->delete();
            }
        });
    }

    /**
     * Retorna a inscrição relacionada
     */
    public function inscricao()
    {
        return $this->belongsTo(Inscricao::class);
    }

    /**
     * Retorna o usuário que criou o registro
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Retorna o usuário que atualizou o registro
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Retorna o parceiro relacionado
     */
    public function parceiro()
    {
        return $this->belongsTo(Parceiro::class);
    }

    /**
     * Retorna o status formatado
     */
    public function getStatusFormatadoAttribute()
    {
        return match($this->status) {
            'pre_matricula' => '🟡 Pré-Matrícula',
            'matricula_confirmada' => '🟢 Matrícula Confirmada',
            'cancelada' => '🔴 Cancelada',
            'trancada' => '⚫ Trancada',
            'concluida' => '⭐ Concluída',
            default => $this->status,
        };
    }



    /**
     * Retorna a modalidade formatada
     */
    public function getModalidadeFormatadaAttribute()
    {
        $modalidades = [
            'ensino-fundamental' => '📚 Ensino Fundamental',
            'ensino-medio' => '🎓 Ensino Médio',
            'ensino-fundamental-e-ensino-medio' => '📚🎓 Ensino Fundamental e Médio',
        ];

        return $modalidades[$this->modalidade] ?? $this->modalidade;
    }

    /**
     * Retorna a forma de pagamento formatada
     */
    public function getFormaPagamentoFormatadaAttribute()
    {
        return match($this->forma_pagamento) {
            'dinheiro' => '💵 Dinheiro',
            'pix' => '📱 PIX',
            'cartao_credito' => '💳 Cartão de Crédito',
            'cartao_debito' => '💳 Cartão de Débito',
            'boleto' => '📄 Boleto',
            default => $this->forma_pagamento,
        };
    }

    /**
     * Retorna a idade do aluno
     */
    public function getIdadeAttribute()
    {
        return $this->data_nascimento->age;
    }

    /**
     * Retorna se o aluno é menor de idade
     */
    public function getMenorDeIdadeAttribute()
    {
        return $this->idade < 18;
    }

    /**
     * Retorna o endereço completo formatado
     */
    public function getEnderecoCompletoAttribute()
    {
        $endereco = "{$this->logradouro}, {$this->numero}";
        
        if ($this->complemento) {
            $endereco .= " - {$this->complemento}";
        }
        
        $endereco .= " - {$this->bairro}";
        $endereco .= " - {$this->cidade}/{$this->estado}";
        $endereco .= " - CEP: {$this->cep}";
        
        return $endereco;
    }

    /**
     * Retorna o nome completo em maiúsculas
     */
    public function getNomeCompletoUpperAttribute()
    {
        return mb_strtoupper($this->nome_completo);
    }

    /**
     * Retorna o CPF formatado
     */
    public function getCpfFormatadoAttribute()
    {
        $cpf = preg_replace('/[^0-9]/', '', $this->cpf);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    /**
     * Retorna o telefone fixo formatado
     */
    public function getTelefoneFixoFormatadoAttribute()
    {
        if (!$this->telefone_fixo) {
            return null;
        }

        $telefone = preg_replace('/[^0-9]/', '', $this->telefone_fixo);
        
        // Se tem 12 dígitos (com código do país), remover o código
        if (strlen($telefone) === 12 && substr($telefone, 0, 2) === '55') {
            $telefone = substr($telefone, 2);
        }
        
        // Verificar se tem 11 dígitos (com 9º dígito) ou 10 dígitos (sem 9º dígito)
        if (strlen($telefone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        } elseif (strlen($telefone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
        }
        
        return $this->telefone_fixo; // Retornar original se não conseguir formatar
    }

    /**
     * Retorna o telefone celular formatado
     */
    public function getTelefoneCelularFormatadoAttribute()
    {
        $telefone = preg_replace('/[^0-9]/', '', $this->telefone_celular);
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    }

    /**
     * Retorna o CEP formatado
     */
    public function getCepFormatadoAttribute()
    {
        $cep = preg_replace('/[^0-9]/', '', $this->cep);
        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
    }

    /**
     * Relacionamento com contratos
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Relacionamento com pagamentos
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Verificar se tem contrato assinado
     */
    public function hasSignedContract()
    {
        return $this->contracts()->where('status', 'signed')->exists();
    }

    /**
     * Obter último contrato
     */
    public function getLatestContract()
    {
        return $this->contracts()->latest()->first();
    }

    /**
     * Obter contratos pendentes
     */
    public function getPendingContracts()
    {
        return $this->contracts()->whereIn('status', ['sent', 'viewed'])->get();
    }

    /**
     * Calcular progresso do perfil
     */
    public function getProfileProgress()
    {
        $fields = [
            // Dados pessoais essenciais (peso 2)
            'nome_completo' => 2,
            'cpf' => 2,
            'telefone_celular' => 2,
            'email' => 2,
            
            // Dados pessoais complementares (peso 1)
            'data_nascimento' => 1,
            'rg' => 1,
            'orgao_emissor' => 1,
            'sexo' => 1,
            'estado_civil' => 1,
            'nacionalidade' => 1,
            'naturalidade' => 1,
            'nome_mae' => 1,
            'nome_pai' => 1,
            
            // Endereço (peso 1)
            'cep' => 1,
            'logradouro' => 1,
            'numero' => 1,
            'bairro' => 1,
            'cidade' => 1,
            'estado' => 1,
            
            // Dados acadêmicos (peso 2)
            'modalidade' => 2,
            'curso' => 2,
            'ultima_serie' => 1,
            'ano_conclusao' => 1,
            'escola_origem' => 1,
            
            // Telefone adicional (peso 1)
            'telefone_fixo' => 1,
            
            // Dados de pagamento (peso 2)
            'forma_pagamento' => 2,
            'valor_total_curso' => 2,
            'dia_vencimento' => 1,
            
            // Dados de pagamento complementares (peso 1)
            'tipo_boleto' => 1,
            'valor_matricula' => 1,
            'valor_mensalidade' => 1,
            'numero_parcelas' => 1,
            'percentual_juros' => 1,
            'desconto' => 1,
        ];
        
        $totalWeight = array_sum($fields);
        $completedWeight = 0;
        $completed = 0;
        $total = count($fields);
        
        foreach ($fields as $field => $weight) {
            if (!empty($this->$field)) {
                $completedWeight += $weight;
                $completed++;
            }
        }
        
        $percentage = $totalWeight > 0 ? round(($completedWeight / $totalWeight) * 100) : 0;
        
        return [
            'percentage' => $percentage,
            'completed' => $completed,
            'total' => $total,
            'missing_fields' => $this->getMissingFields($fields)
        ];
    }
    
    /**
     * Obter campos faltantes
     */
    private function getMissingFields($fields)
    {
        $missing = [];
        $fieldLabels = [
            'nome_completo' => 'Nome Completo',
            'cpf' => 'CPF',
            'telefone_celular' => 'Celular',
            'email' => 'E-mail',
            'data_nascimento' => 'Data de Nascimento',
            'rg' => 'RG',
            'orgao_emissor' => 'Órgão Emissor',
            'sexo' => 'Sexo',
            'estado_civil' => 'Estado Civil',
            'nacionalidade' => 'Nacionalidade',
            'naturalidade' => 'Naturalidade',
            'nome_mae' => 'Nome da Mãe',
            'nome_pai' => 'Nome do Pai',
            'cep' => 'CEP',
            'logradouro' => 'Logradouro',
            'numero' => 'Número',
            'bairro' => 'Bairro',
            'cidade' => 'Cidade',
            'estado' => 'Estado',
            'modalidade' => 'Modalidade',
            'curso' => 'Curso',
            'ultima_serie' => 'Última Série',
            'ano_conclusao' => 'Ano de Conclusão',
            'escola_origem' => 'Escola de Origem',
            'telefone_fixo' => 'Celular (Opcional)',
            'forma_pagamento' => 'Forma de Pagamento',
            'valor_total_curso' => 'Valor Total do Curso',
            'dia_vencimento' => 'Dia de Vencimento',
            'tipo_boleto' => 'Tipo de Boleto',
            'valor_matricula' => 'Valor da Matrícula',
            'valor_mensalidade' => 'Valor da Mensalidade',
            'numero_parcelas' => 'Número de Parcelas',
            'percentual_juros' => 'Percentual de Juros',
            'desconto' => 'Desconto',
        ];
        
        foreach ($fields as $field => $weight) {
            if (empty($this->$field)) {
                $missing[] = $fieldLabels[$field] ?? $field;
            }
        }
        
        return $missing;
    }
} 