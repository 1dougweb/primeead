<?php

declare(strict_types=1);

namespace App\DTOs;

class MatriculaExportData
{
    public function __construct(
        public readonly string $format,
        public readonly array $filters,
        public readonly array $columns,
        public readonly ?string $sortBy,
        public readonly string $sortDirection,
        public readonly int $limit,
        public readonly bool $includeHeaders,
        public readonly ?string $notificationEmail,
        public readonly int $userId,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            format: $data['format'],
            filters: $data['filters'] ?? [],
            columns: $data['columns'] ?? ['nome_completo', 'cpf', 'email', 'curso', 'status'],
            sortBy: $data['sort_by'] ?? null,
            sortDirection: $data['sort_direction'] ?? 'asc',
            limit: $data['limit'] ?? 1000,
            includeHeaders: $data['include_headers'] ?? true,
            notificationEmail: $data['notification_email'] ?? null,
            userId: $userId,
        );
    }

    public function toArray(): array
    {
        return [
            'format' => $this->format,
            'filters' => $this->filters,
            'columns' => $this->columns,
            'sort_by' => $this->sortBy,
            'sort_direction' => $this->sortDirection,
            'limit' => $this->limit,
            'include_headers' => $this->includeHeaders,
            'notification_email' => $this->notificationEmail,
            'user_id' => $this->userId,
        ];
    }

    /**
     * Obter colunas padrão se nenhuma for especificada
     */
    public function getDefaultColumns(): array
    {
        return [
            // Identificação
            'id' => 'ID',
            'inscricao_id' => 'ID da Inscrição',
            'numero_matricula' => 'Número da Matrícula',
            
            // Dados Pessoais
            'nome_completo' => 'Nome Completo',
            'data_nascimento' => 'Data de Nascimento',
            'cpf' => 'CPF',
            'rg' => 'RG',
            'orgao_emissor' => 'Órgão Emissor',
            'sexo' => 'Sexo',
            'estado_civil' => 'Estado Civil',
            'nacionalidade' => 'Nacionalidade',
            'naturalidade' => 'Naturalidade',
            
            // Contato
            'cep' => 'CEP',
            'logradouro' => 'Logradouro',
            'numero' => 'Número',
            'complemento' => 'Complemento',
            'bairro' => 'Bairro',
            'cidade' => 'Cidade',
            'estado' => 'Estado',
            'telefone_fixo' => 'Telefone Fixo',
            'telefone_celular' => 'Telefone Celular',
            'email' => 'E-mail',
            
            // Familiares
            'nome_pai' => 'Nome do Pai',
            'nome_mae' => 'Nome da Mãe',
            
            // Dados Acadêmicos
            'modalidade' => 'Modalidade',
            'curso' => 'Curso',
            'ultima_serie' => 'Última Série',
            'ano_conclusao' => 'Ano de Conclusão',
            'escola_origem' => 'Escola de Origem',
            
            // Status e Configuração
            'status' => 'Status',
            'escola_parceira' => 'Escola Parceira',
            'parceiro_id' => 'ID do Parceiro',
            
            // Pagamento
            'forma_pagamento' => 'Forma de Pagamento',
            'tipo_boleto' => 'Tipo de Boleto',
            'valor_total_curso' => 'Valor Total do Curso',
            'valor_matricula' => 'Valor da Matrícula',
            'valor_mensalidade' => 'Valor da Mensalidade',
            'numero_parcelas' => 'Número de Parcelas',
            'dia_vencimento' => 'Dia de Vencimento',
            'forma_pagamento_mensalidade' => 'Forma de Pagamento da Mensalidade',
            'parcelas_ativas' => 'Parcelas Ativas',
            'parcelas_geradas' => 'Parcelas Geradas',
            'parcelas_pagas' => 'Parcelas Pagas',
            'percentual_juros' => 'Percentual de Juros',
            'desconto' => 'Desconto',
            
            // Documentos
            'doc_rg_cpf' => 'Documentos RG/CPF',
            'doc_comprovante' => 'Comprovante',
            'doc_historico' => 'Histórico',
            'doc_certificado' => 'Certificado',
            'doc_outros' => 'Outros Documentos',
            
            // Google Drive
            'google_drive_folder_id' => 'ID da Pasta Google Drive',
            
            // Observações e Metadados
            'observacoes' => 'Observações',
            'created_at' => 'Data de Criação',
            'updated_at' => 'Data de Atualização',
            'deleted_at' => 'Data de Exclusão',
            'created_by' => 'Criado por',
            'updated_by' => 'Atualizado por',
        ];
    }

    /**
     * Obter colunas selecionadas com labels
     */
    public function getSelectedColumnsWithLabels(): array
    {
        $defaultColumns = $this->getDefaultColumns();
        $selectedColumns = [];

        foreach ($this->columns as $column) {
            if (isset($defaultColumns[$column])) {
                $selectedColumns[$column] = $defaultColumns[$column];
            }
        }

        return $selectedColumns;
    }

    /**
     * Verificar se tem filtros ativos
     */
    public function hasActiveFilters(): bool
    {
        return !empty(array_filter($this->filters, fn($value) => !is_null($value) && $value !== ''));
    }

    /**
     * Obter nome do arquivo de exportação
     */
    public function getFileName(): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filterInfo = $this->hasActiveFilters() ? '_filtrado' : '';
        
        return "matriculas_export_{$timestamp}{$filterInfo}.{$this->format}";
    }
}
