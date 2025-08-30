<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DTOs\MatriculaExportData;
use App\Mail\MatriculaExportCompleted;
use App\Repositories\MatriculaRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProcessMatriculaExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 minutos
    public $tries = 3;
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly MatriculaExportData $exportData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MatriculaRepository $matriculaRepository): void
    {
        try {
            Log::info('Iniciando processamento de exportação de matrículas', [
                'user_id' => $this->exportData->userId,
                'format' => $this->exportData->format,
                'options' => $this->exportData->toArray()
            ]);

                    // Obter matrículas com filtros aplicados
        $query = $matriculaRepository->getForExport(
            $this->exportData->filters,
            $this->exportData->sortBy,
            $this->exportData->sortDirection,
            $this->exportData->limit
        );

        // Selecionar apenas as colunas necessárias
        $columns = array_merge(['id'], $this->exportData->columns);
        $query->select($columns);
        
        // Incluir relacionamentos necessários
        if (in_array('parceiro_id', $this->exportData->columns)) {
            $query->with('parceiro:id,nome_fantasia,razao_social');
        }
        
        // Incluir relacionamentos do Google Drive se necessário
        if (in_array('google_drive_folder_id', $this->exportData->columns)) {
            $query->with('googleDriveFolder:id,name,file_id,web_view_link');
        }
        
        // Incluir relacionamentos de usuários se necessário
        if (in_array('created_by', $this->exportData->columns) || in_array('updated_by', $this->exportData->columns)) {
            $query->with('createdBy:id,name,email', 'updatedBy:id,name,email');
        }

        $matriculas = $query->get();
            
            if ($matriculas->isEmpty()) {
                throw new \Exception('Nenhuma matrícula encontrada com os filtros especificados');
            }

            Log::info("Processando exportação de {$matriculas->count()} matrículas");

            // Gerar arquivo de exportação
            $filePath = $this->generateExportFile($matriculas);

            // Estatísticas da exportação
            $stats = [
                'total_exported' => $matriculas->count(),
                'format' => $this->exportData->format,
                'filters_applied' => $this->exportData->hasActiveFilters(),
                'file_path' => $filePath,
                'file_name' => $this->exportData->getFileName(),
                'file_size' => Storage::disk('local')->size($filePath),
                'started_at' => now(),
                'completed_at' => now(),
                'duration' => 0,
            ];

            // Log de conclusão
            Log::info('Exportação de matrículas concluída com sucesso', [
                'user_id' => $this->exportData->userId,
                'stats' => $stats
            ]);

            // Enviar e-mail de notificação se configurado
            if ($this->exportData->notificationEmail) {
                $this->sendNotificationEmail($stats);
            }

        } catch (\Exception $e) {
            Log::error('Erro no job de exportação de matrículas', [
                'user_id' => $this->exportData->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Enviar e-mail de erro se configurado
            if ($this->exportData->notificationEmail) {
                $this->sendErrorNotification($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Gerar arquivo de exportação
     */
    private function generateExportFile($matriculas): string
    {
        $fileName = $this->exportData->getFileName();
        $filePath = "exports/{$fileName}";

        switch ($this->exportData->format) {
            case 'csv':
                $this->generateCsvFile($matriculas, $filePath);
                break;
            case 'excel':
                $this->generateExcelFile($matriculas, $filePath);
                break;
            case 'json':
                $this->generateJsonFile($matriculas, $filePath);
                break;
            case 'pdf':
                $this->generatePdfFile($matriculas, $filePath);
                break;
            default:
                throw new \Exception("Formato de exportação não suportado: {$this->exportData->format}");
        }

        return $filePath;
    }

    /**
     * Gerar arquivo CSV
     */
    private function generateCsvFile($matriculas, string $filePath): void
    {
        $columns = $this->exportData->getSelectedColumnsWithLabels();
        $data = [];

        // Adicionar cabeçalhos se solicitado
        if ($this->exportData->includeHeaders) {
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
    private function generateExcelFile($matriculas, string $filePath): void
    {
        // Para Excel, vamos usar CSV por enquanto (pode ser expandido para usar PhpSpreadsheet)
        $this->generateCsvFile($matriculas, $filePath);
    }

    /**
     * Gerar arquivo JSON
     */
    private function generateJsonFile($matriculas, string $filePath): void
    {
        $columns = $this->exportData->getSelectedColumnsWithLabels();
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
     * Gerar arquivo PDF
     */
    private function generatePdfFile($matriculas, string $filePath): void
    {
        // Para PDF, vamos gerar um arquivo de texto simples por enquanto
        // Pode ser expandido para usar bibliotecas como DomPDF ou TCPDF
        $columns = $this->exportData->getSelectedColumnsWithLabels();
        $content = [];

        // Adicionar cabeçalhos se solicitado
        if ($this->exportData->includeHeaders) {
            $content[] = implode(' | ', array_values($columns));
            $content[] = str_repeat('-', strlen(implode(' | ', array_values($columns))));
        }

        // Adicionar dados
        foreach ($matriculas as $matricula) {
            $row = [];
            foreach (array_keys($columns) as $column) {
                $row[] = $this->formatColumnValue($matricula, $column);
            }
            $content[] = implode(' | ', $row);
        }

        $pdfContent = implode("\n", $content);
        Storage::disk('local')->put($filePath, $pdfContent);
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
     * Enviar e-mail de notificação de conclusão
     */
    private function sendNotificationEmail(array $stats): void
    {
        try {
            Mail::to($this->exportData->notificationEmail)
                ->send(new MatriculaExportCompleted($stats));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar e-mail de notificação', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar e-mail de notificação de erro
     */
    private function sendErrorNotification(string $errorMessage): void
    {
        try {
            Mail::to($this->exportData->notificationEmail)
                ->send(new MatriculaExportCompleted([
                    'success' => false,
                    'error' => $errorMessage
                ]));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar e-mail de notificação de erro', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obter ID do job
     */
    public function getJobId(): string
    {
        return $this->job->getJobId();
    }

    /**
     * Job falhou
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de exportação falhou definitivamente', [
            'user_id' => $this->exportData->userId,
            'error' => $exception->getMessage()
        ]);
    }
}
