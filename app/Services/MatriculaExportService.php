<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\MatriculaExportData;
use App\Jobs\ProcessMatriculaExport;
use App\Repositories\MatriculaRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MatriculaExportService
{
    public function __construct(
        private readonly MatriculaRepository $matriculaRepository
    ) {}

    /**
     * Processar exportação de matrículas
     */
    public function processExport(MatriculaExportData $exportData): array
    {
        try {
            // Validar se há dados para exportar
            $count = $this->getMatriculasCount($exportData);
            
            if ($count === 0) {
                return [
                    'success' => false,
                    'message' => 'Nenhuma matrícula encontrada com os filtros especificados',
                    'count' => 0
                ];
            }

            // Se for uma exportação pequena, processar imediatamente
            if ($count <= 1000 && $exportData->format !== 'pdf') {
                return $this->processImmediateExport($exportData);
            }

            // Disparar job para exportação assíncrona
            $jobId = $this->dispatchExportJob($exportData);

            Log::info('Job de exportação disparado', [
                'user_id' => $exportData->userId,
                'format' => $exportData->format,
                'count' => $count,
                'job_id' => $jobId,
                'options' => $exportData->toArray()
            ]);

            return [
                'success' => true,
                'message' => "Exportação iniciada com sucesso! {$count} matrículas serão processadas. Você receberá uma notificação quando concluir.",
                'job_id' => $jobId,
                'count' => $count,
                'format' => $exportData->format
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao processar exportação', [
                'user_id' => $exportData->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obter contagem de matrículas com filtros
     */
    private function getMatriculasCount(MatriculaExportData $exportData): int
    {
        $query = $this->buildQuery($exportData);
        return $query->count();
    }

    /**
     * Processar exportação imediata para arquivos pequenos
     */
    private function processImmediateExport(MatriculaExportData $exportData): array
    {
        try {
            $matriculas = $this->getMatriculas($exportData);
            
            if ($matriculas->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Nenhuma matrícula encontrada'
                ];
            }

            $filePath = $this->generateExportFile($matriculas, $exportData);
            
            if (!$filePath) {
                return [
                    'success' => false,
                    'message' => 'Erro ao gerar arquivo de exportação'
                ];
            }

            return [
                'success' => true,
                'message' => 'Exportação concluída com sucesso!',
                'file_path' => $filePath,
                'file_name' => $exportData->getFileName(),
                'count' => $matriculas->count(),
                'download_url' => route('admin.matriculas.export.download', ['file' => basename($filePath)])
            ];

        } catch (\Exception $e) {
            throw new \Exception('Erro na exportação imediata: ' . $e->getMessage());
        }
    }

    /**
     * Disparar job de exportação
     */
    private function dispatchExportJob(MatriculaExportData $exportData): string
    {
        $job = new ProcessMatriculaExport($exportData);
        
        if ($exportData->notificationEmail) {
            $job->onQueue('exports')->delay(now()->addSeconds(5));
        } else {
            $job->onQueue('exports');
        }

        dispatch($job);

        return $job->getJobId();
    }

    /**
     * Construir query baseada nos filtros
     */
    private function buildQuery(MatriculaExportData $exportData)
    {
        $query = $this->matriculaRepository->getQueryBuilder();

        // Aplicar filtros
        $this->applyFilters($query, $exportData->filters);

        // Aplicar ordenação
        if ($exportData->sortBy) {
            $query->orderBy($exportData->sortBy, $exportData->sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Aplicar limite
        if ($exportData->limit > 0) {
            $query->limit($exportData->limit);
        }

        return $query;
    }

    /**
     * Aplicar filtros à query
     */
    private function applyFilters($query, array $filters): void
    {
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['parceiro_id']) && !empty($filters['parceiro_id'])) {
            $query->where('parceiro_id', $filters['parceiro_id']);
        }

        if (isset($filters['data_inicio']) && !empty($filters['data_inicio'])) {
            $query->whereDate('created_at', '>=', $filters['data_inicio']);
        }

        if (isset($filters['data_fim']) && !empty($filters['data_fim'])) {
            $query->whereDate('created_at', '<=', $filters['data_fim']);
        }

        if (isset($filters['curso']) && !empty($filters['curso'])) {
            $query->where('curso', 'like', '%' . $filters['curso'] . '%');
        }

        if (isset($filters['modalidade']) && !empty($filters['modalidade'])) {
            $query->where('modalidade', $filters['modalidade']);
        }

        if (isset($filters['valor_min']) && !empty($filters['valor_min'])) {
            $query->where('valor_total_curso', '>=', $filters['valor_min']);
        }

        if (isset($filters['valor_max']) && !empty($filters['valor_max'])) {
            $query->where('valor_total_curso', '<=', $filters['valor_max']);
        }
    }

    /**
     * Obter matrículas com filtros aplicados
     */
    private function getMatriculas(MatriculaExportData $exportData)
    {
        $query = $this->buildQuery($exportData);
        
        // Selecionar apenas as colunas necessárias
        $columns = array_merge(['id'], $exportData->columns);
        $query->select($columns);
        
        // Incluir relacionamentos necessários
        if (in_array('parceiro_id', $exportData->columns)) {
            $query->with('parceiro:id,nome_fantasia,razao_social');
        }
        
        // Incluir relacionamentos do Google Drive se necessário
        if (in_array('google_drive_folder_id', $exportData->columns)) {
            $query->with('googleDriveFolder:id,name,file_id,web_view_link');
        }
        
        // Incluir relacionamentos de usuários se necessário
        if (in_array('created_by', $exportData->columns) || in_array('updated_by', $exportData->columns)) {
            $query->with('createdBy:id,name,email', 'updatedBy:id,name,email');
        }

        return $query->get();
    }

    /**
     * Gerar arquivo de exportação
     */
    private function generateExportFile($matriculas, MatriculaExportData $exportData): ?string
    {
        try {
            $fileName = $exportData->getFileName();
            $filePath = "exports/{$fileName}";

            switch ($exportData->format) {
                case 'csv':
                    $this->generateCsvFile($matriculas, $exportData, $filePath);
                    break;
                case 'excel':
                    $this->generateExcelFile($matriculas, $exportData, $filePath);
                    break;
                case 'json':
                    $this->generateJsonFile($matriculas, $exportData, $filePath);
                    break;
                default:
                    throw new \Exception("Formato de exportação não suportado: {$exportData->format}");
            }

            return $filePath;

        } catch (\Exception $e) {
            Log::error('Erro ao gerar arquivo de exportação', [
                'error' => $e->getMessage(),
                'format' => $exportData->format
            ]);
            return null;
        }
    }

    /**
     * Gerar arquivo CSV
     */
    private function generateCsvFile($matriculas, MatriculaExportData $exportData, string $filePath): void
    {
        $columns = $exportData->getSelectedColumnsWithLabels();
        $data = [];

        // Adicionar cabeçalhos se solicitado
        if ($exportData->includeHeaders) {
            $data[] = array_values($columns);
        }

        // Adicionar dados
        foreach ($matriculas as $matricula) {
            $row = [];
            foreach (array_keys($columns) as $column) {
                $row[] = $this->formatColumnValue($matricula, $column);
            }
            $data[] = $row;
        }

        $csvContent = $this->arrayToCsv($data);
        Storage::disk('local')->put($filePath, $csvContent);
    }

    /**
     * Gerar arquivo Excel
     */
    private function generateExcelFile($matriculas, MatriculaExportData $exportData, string $filePath): void
    {
        // Para Excel, vamos usar CSV por enquanto (pode ser expandido para usar PhpSpreadsheet)
        $this->generateCsvFile($matriculas, $exportData, $filePath);
    }

    /**
     * Gerar arquivo JSON
     */
    private function generateJsonFile($matriculas, MatriculaExportData $exportData, string $filePath): void
    {
        $columns = $exportData->getSelectedColumnsWithLabels();
        $data = [];

        foreach ($matriculas as $matricula) {
            $row = [];
            foreach (array_keys($columns) as $column) {
                $row[$column] = $this->formatColumnValue($matricula, $column);
            }
            $data[] = $row;
        }

        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        Storage::disk('local')->put($filePath, $jsonContent);
    }

    /**
     * Formatar valor da coluna
     */
    private function formatColumnValue($matricula, string $column): string
    {
        $value = $matricula->{$column} ?? '';

        // Formatações específicas
        switch ($column) {
            case 'created_at':
            case 'updated_at':
            case 'deleted_at':
                return $value ? $value->format('d/m/Y H:i:s') : '';
            case 'data_nascimento':
                return $value ? $value->format('d/m/Y') : '';
            case 'valor_total_curso':
            case 'valor_matricula':
            case 'valor_mensalidade':
            case 'percentual_juros':
            case 'desconto':
                return $value ? 'R$ ' . number_format($value, 2, ',', '.') : '';
            case 'parceiro_id':
                if (isset($matricula->parceiro)) {
                    return $matricula->parceiro->nome_fantasia ?: $matricula->parceiro->razao_social ?: $value;
                }
                return $value;
            case 'google_drive_folder_id':
                if (isset($matricula->googleDriveFolder)) {
                    $folder = $matricula->googleDriveFolder;
                    return $folder->name ? "{$folder->name} ({$folder->file_id})" : $folder->file_id;
                }
                return $value;
            case 'created_by':
                if (isset($matricula->createdBy)) {
                    return $matricula->createdBy->name ?: $matricula->createdBy->email ?: $value;
                }
                return $value;
            case 'updated_by':
                if (isset($matricula->updatedBy)) {
                    return $matricula->updatedBy->name ?: $matricula->updatedBy->email ?: $value;
                }
                return $value;
            case 'doc_rg_cpf':
            case 'doc_outros':
                if (is_array($value)) {
                    return implode(', ', array_filter($value));
                }
                return (string) $value;
            case 'escola_parceira':
                return $value ? 'Sim' : 'Não';
            default:
                return (string) $value;
        }
    }

    /**
     * Converter array para CSV
     */
    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    /**
     * Limpar arquivos de exportação antigos
     */
    public function cleanupOldExports(): int
    {
        try {
            $exportFiles = Storage::disk('local')->files('exports');
            $deletedCount = 0;

            foreach ($exportFiles as $file) {
                // Deletar arquivos mais antigos que 7 dias
                if (Storage::disk('local')->lastModified($file) < now()->subDays(7)->timestamp) {
                    Storage::disk('local')->delete($file);
                    $deletedCount++;
                }
            }

            Log::info('Limpeza de arquivos de exportação concluída', [
                'deleted_count' => $deletedCount
            ]);

            return $deletedCount;

        } catch (\Exception $e) {
            Log::error('Erro na limpeza de arquivos de exportação', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
