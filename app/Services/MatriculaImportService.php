<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\MatriculaImportData;
use App\Jobs\ProcessMatriculaImport;
use App\Repositories\MatriculaRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MatriculaImportService
{
    public function __construct(
        private readonly MatriculaRepository $matriculaRepository,
        private readonly CsvParserService $csvParserService
    ) {}

    /**
     * Processar importação de matrículas
     */
    public function processImport(MatriculaImportData $importData): array
    {
        try {
            // Fazer upload do arquivo
            $filePath = $this->uploadFile($importData->importFile);
            
            // Validar arquivo CSV
            $validationResult = $this->validateCsvFile($filePath);
            
            if (!$validationResult['valid']) {
                $this->cleanupFile($filePath);
                return [
                    'success' => false,
                    'message' => 'Arquivo CSV inválido',
                    'errors' => $validationResult['errors']
                ];
            }

            // Se for dry run, processar imediatamente
            if ($importData->dryRun) {
                return $this->processDryRun($filePath, $importData);
            }

            // Disparar job para processamento assíncrono
            $jobId = $this->dispatchImportJob($filePath, $importData);

            Log::info('Job de importação disparado', [
                'user_id' => $importData->userId,
                'file_path' => $filePath,
                'job_id' => $jobId,
                'options' => $importData->toArray()
            ]);

            return [
                'success' => true,
                'message' => 'Importação iniciada com sucesso! Você receberá uma notificação quando concluir.',
                'job_id' => $jobId,
                'redirect_url' => route('admin.matriculas.index')
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao processar importação', [
                'user_id' => $importData->userId,
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
     * Fazer upload do arquivo
     */
    private function uploadFile($file): string
    {
        $fileName = sprintf(
            'import_matriculas_%s_%s.csv',
            now()->format('Y-m-d_H-i-s'),
            Str::random(8)
        );

        return $file->storeAs('imports', $fileName, 'local');
    }

    /**
     * Validar arquivo CSV
     */
    private function validateCsvFile(string $filePath): array
    {
        try {
            $data = $this->csvParserService->parse($filePath);
            
            if (empty($data) || count($data) < 2) {
                return [
                    'valid' => false,
                    'errors' => ['O arquivo deve conter pelo menos um cabeçalho e uma linha de dados']
                ];
            }

            $headers = $data[0];
            $requiredHeaders = $this->getRequiredHeaders();
            $missingHeaders = array_diff($requiredHeaders, $headers);

            if (!empty($missingHeaders)) {
                return [
                    'valid' => false,
                    'errors' => [
                        'Cabeçalhos obrigatórios ausentes: ' . implode(', ', $missingHeaders)
                    ]
                ];
            }

            return ['valid' => true, 'errors' => []];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['Erro ao ler arquivo CSV: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Processar simulação (dry run)
     */
    private function processDryRun(string $filePath, MatriculaImportData $importData): array
    {
        try {
            $data = $this->csvParserService->parse($filePath);
            $headers = array_shift($data); // Remove cabeçalho
            
            $stats = [
                'total_processed' => count($data),
                'valid_records' => 0,
                'invalid_records' => 0,
                'duplicates' => 0,
                'errors' => []
            ];

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 porque removemos o cabeçalho e arrays começam em 0
                
                $validationResult = $this->validateMatriculaRow($row, $headers, $rowNumber);
                
                if ($validationResult['valid']) {
                    $stats['valid_records']++;
                    
                    // Verificar duplicatas
                    if ($this->matriculaRepository->existsByCpf($row['cpf'])) {
                        $stats['duplicates']++;
                    }
                } else {
                    $stats['invalid_records']++;
                    $stats['errors'][] = [
                        'row' => $rowNumber,
                        'errors' => $validationResult['errors']
                    ];
                }
            }

            $this->cleanupFile($filePath);

            return [
                'success' => true,
                'message' => 'Simulação de importação concluída com sucesso!',
                'summary' => $stats,
                'redirect_url' => route('admin.matriculas.index')
            ];

        } catch (\Exception $e) {
            $this->cleanupFile($filePath);
            throw $e;
        }
    }

    /**
     * Disparar job de importação
     */
    private function dispatchImportJob(string $filePath, MatriculaImportData $importData): string
    {
        $job = new ProcessMatriculaImport($filePath, $importData);
        
        if ($importData->notificationEmail) {
            $job->onQueue('imports')->delay(now()->addSeconds(5));
        } else {
            $job->onQueue('imports');
        }

        dispatch($job);

        return $job->getJobId();
    }

    /**
     * Validar linha de matrícula
     */
    private function validateMatriculaRow(array $row, array $headers, int $rowNumber): array
    {
        $errors = [];
        
        // Validar campos obrigatórios
        $requiredFields = ['nome_completo', 'cpf', 'email'];
        
        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                $errors[] = "Campo '{$field}' é obrigatório";
            }
        }

        // Validar CPF
        if (!empty($row['cpf']) && !$this->validateCpf($row['cpf'])) {
            $errors[] = 'CPF inválido';
        }

        // Validar e-mail
        if (!empty($row['email']) && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'E-mail inválido';
        }

        // Validar valores numéricos
        $numericFields = ['valor_total_curso', 'valor_matricula', 'valor_mensalidade'];
        foreach ($numericFields as $field) {
            if (!empty($row[$field]) && !is_numeric($row[$field])) {
                $errors[] = "Campo '{$field}' deve ser numérico";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validar CPF
     */
    private function validateCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verificar se todos os dígitos são iguais
        if (preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }

        // Validar dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obter cabeçalhos obrigatórios
     */
    private function getRequiredHeaders(): array
    {
        return [
            'nome_completo',
            'cpf',
            'email',
            'telefone_celular'
        ];
    }

    /**
     * Limpar arquivo temporário
     */
    private function cleanupFile(string $filePath): void
    {
        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }
    }
}
