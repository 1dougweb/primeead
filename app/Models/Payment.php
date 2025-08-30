<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricula_id',
        'valor',
        'forma_pagamento',
        'data_vencimento',
        'data_pagamento',
        'status',
        'descricao',
        'numero_parcela',
        'total_parcelas',
        'mercadopago_id',
        'mercadopago_status',
        'mercadopago_data',
        'arquivo_boleto',
        'codigo_pix',
        'digitable_line',
        'barcode_content',
        'financial_institution',
        'qr_code_base64',
        'ticket_url',
        'observacoes'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'datetime',
        'data_pagamento' => 'datetime',
        'mercadopago_data' => 'array'
    ];

    /**
     * Relacionamentos
     */
    public function matricula()
    {
        return $this->belongsTo(Matricula::class);
    }

    // Note: Student data is stored directly in the matricula table
    // No separate aluno relationship needed

    /**
     * Scopes
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->where('data_vencimento', '<', now());
    }

    public function scopeDueSoon($query, $days = 3)
    {
        return $query->where('status', 'pending')
                    ->whereBetween('data_vencimento', [now(), now()->addDays($days)]);
    }

    public function scopeByMatricula($query, $matriculaId)
    {
        return $query->where('matricula_id', $matriculaId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByFormaPagamento($query, $formaPagamento)
    {
        return $query->where('forma_pagamento', $formaPagamento);
    }

    /**
     * Verificar se o pagamento tem boleto gerado
     */
    public function hasBoleto()
    {
        return !empty($this->arquivo_boleto) && file_exists(public_path('storage/boletos/' . $this->arquivo_boleto));
    }

    /**
     * Obter URL do boleto
     */
    public function getBoletoUrl()
    {
        if ($this->hasBoleto()) {
            return asset('storage/boletos/' . $this->arquivo_boleto);
        }
        return null;
    }

    /**
     * Verificar se o pagamento tem código PIX
     */
    public function hasPixCode()
    {
        return !empty($this->codigo_pix);
    }

    /**
     * Obter nome do arquivo do boleto
     */
    public function getBoletoFileName()
    {
        return "boleto_payment_{$this->id}_{$this->matricula_id}.pdf";
    }

    /**
     * Obter caminho completo do arquivo do boleto
     */
    public function getBoletoPath()
    {
        if ($this->hasBoleto()) {
            return public_path('storage/boletos/' . $this->arquivo_boleto);
        }
        return null;
    }

    /**
     * Métodos auxiliares
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isOverdue()
    {
        return $this->status === 'pending' && $this->data_vencimento < now();
    }

    public function isDueSoon($days = 3)
    {
        return $this->status === 'pending' && 
               $this->data_vencimento >= now() && 
               $this->data_vencimento <= now()->addDays($days);
    }

    public function getDaysOverdue()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return (int) ceil($this->data_vencimento->diffInDays(now()));
    }

    public function getDaysUntilDue()
    {
        if ($this->data_vencimento < now()) {
            return 0;
        }
        return (int) ceil(now()->diffInDays($this->data_vencimento, false));
    }

    public function getFormattedDaysOverdue()
    {
        $days = $this->getDaysOverdue();
        
        if ($days == 0) {
            return '';
        }
        
        if ($days == 1) {
            return '1 dia';
        }
        
        return $days . ' dias';
    }

    /**
     * Calcula o valor atualizado com juros de mora, se aplicável
     * 
     * @param float|null $taxaJurosDiaria Taxa de juros diária (em decimal, ex: 0.003333 para 0,3333% ao dia)
     * @return float Valor atualizado com juros
     */
    public function getValorAtualizado($taxaJurosDiaria = null)
    {
        // Se o pagamento não estiver vencido, retorna o valor original
        if (!$this->isOverdue()) {
            return (float) $this->valor;
        }
        
        // Se não foi informada uma taxa, usa a padrão de 0,033% ao dia (aproximadamente 1% ao mês)
        if ($taxaJurosDiaria === null) {
            // Tenta obter a taxa da matrícula, se disponível
            if ($this->matricula && $this->matricula->percentual_juros) {
                // Converte o percentual mensal para diário
                $taxaJurosDiaria = ($this->matricula->percentual_juros / 100) / 30;
            } else {
                // Taxa padrão de 0,033% ao dia (aproximadamente 1% ao mês)
                $taxaJurosDiaria = 0.00033;
            }
        }
        
        // Calcula os dias em atraso
        $diasAtraso = $this->getDaysOverdue();
        
        // Calcula o valor dos juros
        $valorJuros = (float) $this->valor * $taxaJurosDiaria * $diasAtraso;
        
        // Retorna o valor atualizado (valor original + juros)
        return (float) $this->valor + $valorJuros;
    }

    /**
     * Calcula o valor dos juros de mora
     * 
     * @param float|null $taxaJurosDiaria Taxa de juros diária (em decimal)
     * @return float Valor dos juros
     */
    public function getValorJurosMora($taxaJurosDiaria = null)
    {
        // Se o pagamento não estiver vencido, não há juros
        if (!$this->isOverdue()) {
            return 0;
        }
        
        // Retorna a diferença entre o valor atualizado e o valor original
        return $this->getValorAtualizado($taxaJurosDiaria) - (float) $this->valor;
    }

    /**
     * Formata o valor atualizado com juros de mora
     * 
     * @param float|null $taxaJurosDiaria Taxa de juros diária (em decimal)
     * @return string Valor formatado
     */
    public function getFormattedValorAtualizado($taxaJurosDiaria = null)
    {
        return 'R$ ' . number_format($this->getValorAtualizado($taxaJurosDiaria), 2, ',', '.');
    }

    /**
     * Formata o valor dos juros de mora
     * 
     * @param float|null $taxaJurosDiaria Taxa de juros diária (em decimal)
     * @return string Valor formatado
     */
    public function getFormattedValorJurosMora($taxaJurosDiaria = null)
    {
        return 'R$ ' . number_format($this->getValorJurosMora($taxaJurosDiaria), 2, ',', '.');
    }

    public function getFormattedAmount()
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'paid' => 'Pago',
            'failed' => 'Falhou',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => 'Desconhecido'
        };
    }

    public function getStatusColor()
    {
        return match($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'paid' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            'refunded' => 'info',
            default => 'secondary'
        };
    }

    public function getFormaPagamentoLabel()
    {
        return match($this->forma_pagamento) {
            'pix' => 'PIX',
            'cartao_credito' => 'Cartão de Crédito',
            'boleto' => 'Boleto',
            default => 'Não informado'
        };
    }

    public function getInstallmentLabel()
    {
        if ($this->total_parcelas <= 1) {
            return 'Pagamento único';
        }
        return "Parcela {$this->numero_parcela} de {$this->total_parcelas}";
    }

    public function getFormattedDueDate()
    {
        return $this->data_vencimento ? $this->data_vencimento->format('d/m/Y') : '-';
    }

    public function getFormattedPaidDate()
    {
        return $this->data_pagamento ? $this->data_pagamento->format('d/m/Y H:i') : '-';
    }
}
