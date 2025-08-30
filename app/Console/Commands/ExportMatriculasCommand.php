<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DTOs\MatriculaExportData;
use App\Services\MatriculaExportService;
use Illuminate\Console\Command;

class ExportMatriculasCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'matriculas:export 
                            {format : Formato de exportação (csv, excel, json, pdf)}
                            {--filters= : Filtros em formato JSON}
                            {--columns= : Colunas separadas por vírgula}
                            {--sort-by= : Campo para ordenação}
                            {--sort-direction=asc : Direção da ordenação (asc/desc)}
                            {--limit=1000 : Limite de registros}
                            {--include-headers : Incluir cabeçalhos}
                            {--user-id=1 : ID do usuário que está executando a exportação}
                            {--output= : Caminho de saída personalizado}';

    /**
     * The console command description.
     */
    protected $description = 'Exportar matrículas com filtros e opções configuráveis';

    /**
     * Execute the console command.
     */
    public function handle(MatriculaExportService $exportService): int
    {
        $format = $this->argument('format');
        $filters = $this->parseFilters();
        $columns = $this->parseColumns();
        $sortBy = $this->option('sort-by');
        $sortDirection = $this->option('sort-direction');
        $limit = (int) $this->option('limit');
        $includeHeaders = $this->option('include-headers');
        $userId = (int) $this->option('user-id');

        // Validar formato
        if (!in_array($format, ['csv', 'excel', 'json', 'pdf'])) {
            $this->error("❌ Formato inválido: {$format}");
            $this->info("Formatos válidos: csv, excel, json, pdf");
            return 1;
        }

        $this->info("🚀 Iniciando exportação de matrículas...");
        $this->info("📊 Formato: " . strtoupper($format));
        $this->info("🔍 Filtros: " . ($this->hasActiveFilters($filters) ? 'Sim' : 'Não'));
        $this->info("📋 Colunas: " . implode(', ', $columns));
        $this->info("📦 Limite: {$limit}");
        $this->info("📝 Cabeçalhos: " . ($includeHeaders ? 'Sim' : 'Não'));

        if ($sortBy) {
            $this->info("🔄 Ordenação: {$sortBy} ({$sortDirection})");
        }

        try {
            // Criar DTO de exportação
            $exportData = new MatriculaExportData(
                format: $format,
                filters: $filters,
                columns: $columns,
                sortBy: $sortBy,
                sortDirection: $sortDirection,
                limit: $limit,
                includeHeaders: $includeHeaders,
                notificationEmail: null,
                userId: $userId
            );

            // Processar exportação
            $result = $exportService->processExport($exportData);

            if ($result['success']) {
                $this->info("✅ Exportação concluída com sucesso!");
                
                if (isset($result['count'])) {
                    $this->info("📊 Total de registros: {$result['count']}");
                }
                
                if (isset($result['file_path'])) {
                    $this->info("📁 Arquivo gerado: {$result['file_path']}");
                }
                
                if (isset($result['download_url'])) {
                    $this->info("🔗 URL de download: {$result['download_url']}");
                }
                
                return 0;
            } else {
                $this->error("❌ Erro na exportação: " . $result['message']);
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Erro inesperado: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Parsear filtros do JSON
     */
    private function parseFilters(): array
    {
        $filtersJson = $this->option('filters');
        
        if (empty($filtersJson)) {
            return [];
        }

        try {
            $filters = json_decode($filtersJson, true, 512, JSON_THROW_ON_ERROR);
            
            if (!is_array($filters)) {
                $this->warn("⚠️ Filtros inválidos, ignorando...");
                return [];
            }

            return $filters;

        } catch (\JsonException $e) {
            $this->warn("⚠️ Erro ao parsear filtros JSON: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Parsear colunas da string
     */
    private function parseColumns(): array
    {
        $columnsString = $this->option('columns');
        
        if (empty($columnsString)) {
            return ['nome_completo', 'cpf', 'email', 'curso', 'status'];
        }

        $columns = array_map('trim', explode(',', $columnsString));
        
        // Validar colunas
        $validColumns = [
            'id', 'inscricao_id', 'numero_matricula', 'nome_completo', 'data_nascimento',
            'cpf', 'rg', 'orgao_emissor', 'sexo', 'estado_civil', 'nacionalidade',
            'naturalidade', 'cep', 'logradouro', 'numero', 'complemento', 'bairro',
            'cidade', 'estado', 'telefone_fixo', 'telefone_celular', 'email',
            'nome_pai', 'nome_mae', 'modalidade', 'curso', 'ultima_serie',
            'ano_conclusao', 'escola_origem', 'status', 'escola_parceira',
            'parceiro_id', 'forma_pagamento', 'tipo_boleto', 'valor_total_curso',
            'valor_matricula', 'valor_mensalidade', 'numero_parcelas', 'dia_vencimento',
            'forma_pagamento_mensalidade', 'parcelas_ativas', 'parcelas_geradas',
            'parcelas_pagas', 'percentual_juros', 'desconto', 'doc_rg_cpf',
            'doc_comprovante', 'doc_historico', 'doc_certificado', 'doc_outros',
            'google_drive_folder_id', 'observacoes', 'created_at', 'updated_at',
            'deleted_at', 'created_by', 'updated_by'
        ];

        $invalidColumns = array_diff($columns, $validColumns);
        
        if (!empty($invalidColumns)) {
            $this->warn("⚠️ Colunas inválidas ignoradas: " . implode(', ', $invalidColumns));
            $columns = array_intersect($columns, $validColumns);
        }

        if (empty($columns)) {
            $this->warn("⚠️ Nenhuma coluna válida, usando padrão");
            $columns = ['nome_completo', 'cpf', 'email', 'curso', 'status'];
        }

        return $columns;
    }

    /**
     * Verificar se há filtros ativos
     */
    private function hasActiveFilters(array $filters): bool
    {
        return !empty(array_filter($filters, fn($value) => !is_null($value) && $value !== ''));
    }

    /**
     * Exibir ajuda adicional
     */
    public function getHelp(): string
    {
        return <<<HELP
Exemplos de uso:

1. Exportação básica CSV:
   php artisan matriculas:export csv

2. Exportação com filtros:
   php artisan matriculas:export csv --filters='{"status":"ativo","parceiro_id":1}'

3. Exportação com colunas específicas:
   php artisan matriculas:export excel --columns="nome_completo,cpf,email,curso"

4. Exportação com ordenação:
   php artisan matriculas:export json --sort-by="nome_completo" --sort-direction="asc"

5. Exportação com limite:
   php artisan matriculas:export pdf --limit=500

6. Exportação completa:
   php artisan matriculas:export csv --filters='{"status":"ativo"}' --columns="nome_completo,cpf,email" --sort-by="created_at" --sort-direction="desc" --limit=2000

Filtros disponíveis:
- status: ativo, inativo, pendente, cancelado
- parceiro_id: ID do parceiro
- data_inicio: Data de início (YYYY-MM-DD)
- data_fim: Data de fim (YYYY-MM-DD)
- curso: Nome do curso (busca parcial)
- modalidade: ensino-medio, ensino-superior, curso-tecnico, curso-livre
- valor_min: Valor mínimo
- valor_max: Valor máximo

Colunas disponíveis:
- Identificação: id, inscricao_id, numero_matricula
- Dados Pessoais: nome_completo, data_nascimento, cpf, rg, orgao_emissor, sexo, estado_civil, nacionalidade, naturalidade
- Contato: cep, logradouro, numero, complemento, bairro, cidade, estado, telefone_fixo, telefone_celular, email
- Familiares: nome_pai, nome_mae
- Dados Acadêmicos: modalidade, curso, ultima_serie, ano_conclusao, escola_origem
- Status: status, escola_parceira, parceiro_id
- Pagamento: forma_pagamento, tipo_boleto, valor_total_curso, valor_matricula, valor_mensalidade, numero_parcelas, dia_vencimento, forma_pagamento_mensalidade, parcelas_ativas, parcelas_geradas, parcelas_pagas, percentual_juros, desconto
- Documentos: doc_rg_cpf, doc_comprovante, doc_historico, doc_certificado, doc_outros
- Google Drive: google_drive_folder_id
- Observações: observacoes
- Metadados: created_at, updated_at, deleted_at, created_by, updated_by
HELP;
    }
}
