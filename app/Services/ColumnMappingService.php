<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

class ColumnMappingService
{
    /**
     * Colunas padrão esperadas pelo sistema
     */
    private const EXPECTED_COLUMNS = [
        // Identificação
        'id' => ['id', 'identificador', 'codigo', 'codigo_matricula'],
        'inscricao_id' => ['inscricao_id', 'inscricao', 'id_inscricao', 'codigo_inscricao'],
        'numero_matricula' => ['numero_matricula', 'matricula', 'numero', 'codigo_matricula', 'matricula_numero'],
        
        // Dados Pessoais
        'nome_completo' => ['nome_completo', 'nome', 'nome_aluno', 'aluno', 'nome_estudante', 'estudante'],
        'data_nascimento' => ['data_nascimento', 'nascimento', 'data_nasc', 'nasc', 'idade', 'data_aniversario'],
        'cpf' => ['cpf', 'cpf_aluno', 'cpf_estudante', 'documento', 'numero_cpf'],
        'rg' => ['rg', 'rg_aluno', 'rg_estudante', 'identidade', 'numero_rg'],
        'orgao_emissor' => ['orgao_emissor', 'emissor', 'orgao', 'emissor_rg', 'orgao_rg'],
        'sexo' => ['sexo', 'genero', 'sexo_aluno', 'genero_aluno'],
        'estado_civil' => ['estado_civil', 'civil', 'estado_civil_aluno'],
        'nacionalidade' => ['nacionalidade', 'nacionalidade_aluno', 'pais_origem'],
        'naturalidade' => ['naturalidade', 'naturalidade_aluno', 'cidade_nascimento', 'local_nascimento'],
        
        // Contato
        'cep' => ['cep', 'cep_aluno', 'codigo_postal', 'postal'],
        'logradouro' => ['logradouro', 'rua', 'endereco', 'logradouro_aluno', 'rua_aluno'],
        'numero' => ['numero', 'numero_endereco', 'numero_casa', 'casa'],
        'complemento' => ['complemento', 'complemento_endereco', 'apto', 'apartamento', 'bloco'],
        'bairro' => ['bairro', 'bairro_aluno', 'distrito', 'zona'],
        'cidade' => ['cidade', 'cidade_aluno', 'municipio', 'localidade'],
        'estado' => ['estado', 'estado_aluno', 'uf', 'sigla_estado'],
        'telefone_fixo' => ['telefone_fixo', 'telefone', 'tel', 'telefone_casa', 'telefone_residencial'],
        'telefone_celular' => ['telefone_celular', 'celular', 'cel', 'mobile', 'telefone_movel'],
        'email' => ['email', 'e_mail', 'email_aluno', 'correio_eletronico'],
        
        // Familiares
        'nome_pai' => ['nome_pai', 'pai', 'nome_do_pai', 'pai_aluno'],
        'nome_mae' => ['nome_mae', 'mae', 'nome_da_mae', 'mae_aluno'],
        
        // Dados Acadêmicos
        'modalidade' => ['modalidade', 'tipo_ensino', 'ensino', 'nivel_ensino', 'tipo_curso'],
        'curso' => ['curso', 'nome_curso', 'curso_nome', 'disciplina', 'area_estudo'],
        'ultima_serie' => ['ultima_serie', 'serie', 'ano_escolar', 'nivel_escolar', 'classe'],
        'ano_conclusao' => ['ano_conclusao', 'conclusao', 'ano_termino', 'ano_fim', 'conclusao_ano'],
        'escola_origem' => ['escola_origem', 'escola', 'instituicao_anterior', 'escola_anterior'],
        
        // Status e Configuração
        'status' => ['status', 'situacao', 'estado_matricula', 'condicao', 'status_aluno'],
        'escola_parceira' => ['escola_parceira', 'parceira', 'parceiro_escola', 'escola_parceiro'],
        'parceiro_id' => ['parceiro_id', 'parceiro', 'id_parceiro', 'codigo_parceiro', 'parceiro_codigo'],
        
        // Pagamento
        'forma_pagamento' => ['forma_pagamento', 'pagamento', 'tipo_pagamento', 'metodo_pagamento'],
        'tipo_boleto' => ['tipo_boleto', 'boleto', 'tipo_cobranca', 'cobranca'],
        'valor_total_curso' => ['valor_total_curso', 'valor_total', 'total', 'valor_curso', 'preco_total'],
        'valor_matricula' => ['valor_matricula', 'matricula_valor', 'taxa_matricula', 'inscricao_valor'],
        'valor_mensalidade' => ['valor_mensalidade', 'mensalidade', 'valor_mensal', 'preco_mensal'],
        'numero_parcelas' => ['numero_parcelas', 'parcelas', 'quantidade_parcelas', 'parcelas_total'],
        'dia_vencimento' => ['dia_vencimento', 'vencimento', 'dia_pagamento', 'data_vencimento'],
        'forma_pagamento_mensalidade' => ['forma_pagamento_mensalidade', 'pagamento_mensal', 'tipo_pagamento_mensal'],
        'parcelas_ativas' => ['parcelas_ativas', 'parcelas_ativas_total', 'parcelas_em_aberto'],
        'parcelas_geradas' => ['parcelas_geradas', 'parcelas_criadas', 'total_parcelas_criadas'],
        'parcelas_pagas' => ['parcelas_pagas', 'parcelas_quitadas', 'parcelas_pagas_total'],
        'percentual_juros' => ['percentual_juros', 'juros', 'taxa_juros', 'juros_percentual'],
        'desconto' => ['desconto', 'valor_desconto', 'desconto_valor', 'reducao'],
        
        // Documentos
        'doc_rg_cpf' => ['doc_rg_cpf', 'documentos_rg_cpf', 'rg_cpf', 'documentos_identidade'],
        'doc_comprovante' => ['doc_comprovante', 'comprovante', 'comprovante_residencia', 'comprovante_endereco'],
        'doc_historico' => ['doc_historico', 'historico', 'historico_escolar', 'certidao_escolar'],
        'doc_certificado' => ['doc_certificado', 'certificado', 'diploma', 'certificado_anterior'],
        'doc_outros' => ['doc_outros', 'outros_documentos', 'documentos_adicionais', 'documentos_extras'],
        
        // Google Drive
        'google_drive_folder_id' => ['google_drive_folder_id', 'google_drive', 'drive_id', 'pasta_google', 'google_folder'],
        
        // Observações
        'observacoes' => ['observacoes', 'observacao', 'comentarios', 'notas', 'observacoes_gerais']
    ];

    /**
     * Analisar arquivo CSV e detectar colunas
     */
    public function analyzeCsvFile(string $filePath): array
    {
        try {
            $csvContent = file_get_contents($filePath);
            $lines = explode("\n", $csvContent);
            
            if (empty($lines)) {
                return [
                    'headers' => [],
                    'sample_data' => [],
                    'total_rows' => 0,
                    'detected_columns' => [],
                    'missing_columns' => [],
                    'unmapped_columns' => []
                ];
            }

            // Obter cabeçalhos (primeira linha)
            $headers = str_getcsv($lines[0]);
            $headers = array_map('trim', $headers);
            
            // Obter dados de exemplo (segunda linha se existir)
            $sampleData = [];
            if (isset($lines[1]) && !empty(trim($lines[1]))) {
                $sampleData = str_getcsv($lines[1]);
                $sampleData = array_map('trim', $sampleData);
            }

            // Contar total de linhas (excluindo cabeçalho)
            $totalRows = count(array_filter($lines, fn($line) => !empty(trim($line)))) - 1;

            // Analisar colunas detectadas e mapear
            $analysis = $this->analyzeColumns($headers);

            return [
                'headers' => $headers,
                'sample_data' => $sampleData,
                'total_rows' => max(0, $totalRows),
                'detected_columns' => $analysis['detected'],
                'missing_columns' => $analysis['missing'],
                'unmapped_columns' => $analysis['unmapped'],
                'mapping_suggestions' => $analysis['suggestions']
            ];

        } catch (\Exception $e) {
            return [
                'headers' => [],
                'sample_data' => [],
                'total_rows' => 0,
                'detected_columns' => [],
                'missing_columns' => [],
                'unmapped_columns' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Analisar colunas e fazer mapeamento inteligente
     */
    private function analyzeColumns(array $headers): array
    {
        $detected = [];
        $missing = [];
        $unmapped = [];
        $suggestions = [];

        // Mapear colunas detectadas
        foreach ($headers as $index => $header) {
            $headerLower = Str::lower(trim($header));
            $mapped = false;

            foreach (self::EXPECTED_COLUMNS as $expectedColumn => $variations) {
                foreach ($variations as $variation) {
                    if ($this->isColumnMatch($headerLower, $variation)) {
                        $detected[$expectedColumn] = [
                            'index' => $index,
                            'header' => $header,
                            'confidence' => $this->calculateConfidence($headerLower, $variation)
                        ];
                        $mapped = true;
                        break 2;
                    }
                }
            }

            if (!$mapped) {
                $unmapped[] = [
                    'index' => $index,
                    'header' => $header,
                    'suggestions' => $this->suggestColumnMapping($headerLower)
                ];
            }
        }

        // Identificar colunas faltantes
        foreach (self::EXPECTED_COLUMNS as $expectedColumn => $variations) {
            if (!isset($detected[$expectedColumn])) {
                $missing[] = [
                    'column' => $expectedColumn,
                    'variations' => $variations,
                    'description' => $this->getColumnDescription($expectedColumn)
                ];
            }
        }

        // Gerar sugestões de mapeamento para colunas não mapeadas
        foreach ($unmapped as $unmappedColumn) {
            $suggestions[$unmappedColumn['header']] = $unmappedColumn['suggestions'];
        }

        return [
            'detected' => $detected,
            'missing' => $missing,
            'unmapped' => $unmapped,
            'suggestions' => $suggestions
        ];
    }

    /**
     * Verificar se uma coluna corresponde a uma variação esperada
     */
    private function isColumnMatch(string $header, string $variation): bool
    {
        // Correspondência exata
        if ($header === $variation) {
            return true;
        }

        // Correspondência com acentos removidos
        $headerNormalized = $this->normalizeString($header);
        $variationNormalized = $this->normalizeString($variation);
        
        if ($headerNormalized === $variationNormalized) {
            return true;
        }

        // Correspondência parcial (para casos como "nome_aluno" vs "nome")
        if (Str::contains($header, $variation) || Str::contains($variation, $header)) {
            return true;
        }

        // Correspondência com sinônimos comuns
        $synonyms = [
            'nome' => ['name', 'nome_aluno', 'aluno'],
            'cpf' => ['documento', 'cpf_aluno', 'identidade'],
            'email' => ['e_mail', 'correio', 'correio_eletronico'],
            'telefone' => ['tel', 'phone', 'fone'],
            'endereco' => ['address', 'local', 'localizacao'],
            'curso' => ['course', 'disciplina', 'materia'],
            'valor' => ['price', 'custo', 'preco'],
            'data' => ['date', 'fecha', 'dia'],
            'status' => ['situacao', 'estado', 'condicao']
        ];

        foreach ($synonyms as $main => $synonymList) {
            if (in_array($header, $synonymList) && in_array($variation, $synonymList)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalizar string removendo acentos e caracteres especiais
     */
    private function normalizeString(string $str): string
    {
        $str = Str::lower($str);
        $str = preg_replace('/[^a-z0-9_]/', '', $str);
        return $str;
    }

    /**
     * Calcular confiança da correspondência
     */
    private function calculateConfidence(string $header, string $variation): float
    {
        $headerNormalized = $this->normalizeString($header);
        $variationNormalized = $this->normalizeString($variation);

        if ($headerNormalized === $variationNormalized) {
            return 1.0;
        }

        if (Str::contains($headerNormalized, $variationNormalized) || Str::contains($variationNormalized, $headerNormalized)) {
            return 0.8;
        }

        // Calcular similaridade usando algoritmo de Levenshtein
        $distance = levenshtein($headerNormalized, $variationNormalized);
        $maxLength = max(strlen($headerNormalized), strlen($variationNormalized));
        
        if ($maxLength === 0) {
            return 0.0;
        }

        return max(0.0, 1.0 - ($distance / $maxLength));
    }

    /**
     * Sugerir mapeamento para coluna não reconhecida
     */
    private function suggestColumnMapping(string $header): array
    {
        $suggestions = [];
        $headerNormalized = $this->normalizeString($header);

        foreach (self::EXPECTED_COLUMNS as $expectedColumn => $variations) {
            $maxSimilarity = 0;
            
            foreach ($variations as $variation) {
                $variationNormalized = $this->normalizeString($variation);
                $similarity = $this->calculateConfidence($headerNormalized, $variationNormalized);
                
                if ($similarity > $maxSimilarity) {
                    $maxSimilarity = $similarity;
                }
            }

            if ($maxSimilarity > 0.3) { // Threshold mínimo de similaridade
                $suggestions[] = [
                    'column' => $expectedColumn,
                    'confidence' => $maxSimilarity,
                    'description' => $this->getColumnDescription($expectedColumn)
                ];
            }
        }

        // Ordenar por confiança
        usort($suggestions, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return array_slice($suggestions, 0, 3); // Retornar apenas as 3 melhores sugestões
    }

    /**
     * Obter descrição de uma coluna
     */
    private function getColumnDescription(string $column): string
    {
        $descriptions = [
            'id' => 'Identificador único da matrícula',
            'inscricao_id' => 'ID da inscrição relacionada',
            'numero_matricula' => 'Número da matrícula',
            'nome_completo' => 'Nome completo do aluno',
            'data_nascimento' => 'Data de nascimento',
            'cpf' => 'CPF do aluno',
            'rg' => 'RG do aluno',
            'orgao_emissor' => 'Órgão emissor do RG',
            'sexo' => 'Sexo (M/F)',
            'estado_civil' => 'Estado civil',
            'nacionalidade' => 'Nacionalidade',
            'naturalidade' => 'Naturalidade',
            'cep' => 'CEP do endereço',
            'logradouro' => 'Logradouro',
            'numero' => 'Número do endereço',
            'complemento' => 'Complemento do endereço',
            'bairro' => 'Bairro',
            'cidade' => 'Cidade',
            'estado' => 'Estado',
            'telefone_fixo' => 'Telefone fixo',
            'telefone_celular' => 'Telefone celular',
            'email' => 'E-mail',
            'nome_pai' => 'Nome do pai',
            'nome_mae' => 'Nome da mãe',
            'modalidade' => 'Modalidade de ensino',
            'curso' => 'Nome do curso',
            'ultima_serie' => 'Última série cursada',
            'ano_conclusao' => 'Ano de conclusão',
            'escola_origem' => 'Escola de origem',
            'status' => 'Status da matrícula',
            'escola_parceira' => 'Se é escola parceira',
            'parceiro_id' => 'ID do parceiro',
            'forma_pagamento' => 'Forma de pagamento',
            'tipo_boleto' => 'Tipo de boleto',
            'valor_total_curso' => 'Valor total do curso',
            'valor_matricula' => 'Valor da matrícula',
            'valor_mensalidade' => 'Valor da mensalidade',
            'numero_parcelas' => 'Número de parcelas',
            'dia_vencimento' => 'Dia de vencimento',
            'forma_pagamento_mensalidade' => 'Forma de pagamento da mensalidade',
            'parcelas_ativas' => 'Parcelas ativas',
            'parcelas_geradas' => 'Parcelas geradas',
            'parcelas_pagas' => 'Parcelas pagas',
            'percentual_juros' => 'Percentual de juros',
            'desconto' => 'Desconto aplicado',
            'doc_rg_cpf' => 'Documentos RG/CPF',
            'doc_comprovante' => 'Comprovante',
            'doc_historico' => 'Histórico escolar',
            'doc_certificado' => 'Certificado',
            'doc_outros' => 'Outros documentos',
            'google_drive_folder_id' => 'ID da pasta no Google Drive',
            'observacoes' => 'Observações gerais'
        ];

        return $descriptions[$column] ?? 'Coluna para dados específicos';
    }

    /**
     * Gerar mapeamento automático de colunas
     */
    public function generateAutomaticMapping(array $headers): array
    {
        $mapping = [];
        
        foreach ($headers as $index => $header) {
            $headerLower = Str::lower(trim($header));
            
            foreach (self::EXPECTED_COLUMNS as $expectedColumn => $variations) {
                foreach ($variations as $variation) {
                    if ($this->isColumnMatch($headerLower, $variation)) {
                        $mapping[$expectedColumn] = $index;
                        break 2;
                    }
                }
            }
        }

        return $mapping;
    }

    /**
     * Validar mapeamento de colunas
     */
    public function validateColumnMapping(array $mapping, array $headers): array
    {
        $errors = [];
        $warnings = [];

        // Verificar se colunas obrigatórias estão mapeadas
        $requiredColumns = ['nome_completo', 'cpf', 'email'];
        foreach ($requiredColumns as $required) {
            if (!isset($mapping[$required])) {
                $errors[] = "Coluna obrigatória '{$required}' não está mapeada";
            }
        }

        // Verificar se índices são válidos
        foreach ($mapping as $column => $index) {
            if (!isset($headers[$index])) {
                $errors[] = "Índice inválido para coluna '{$column}': {$index}";
            }
        }

        // Verificar duplicatas
        $usedIndexes = array_values($mapping);
        $duplicates = array_diff_assoc($usedIndexes, array_unique($usedIndexes));
        if (!empty($duplicates)) {
            $warnings[] = "Algumas colunas estão mapeadas para o mesmo índice: " . implode(', ', $duplicates);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
}
