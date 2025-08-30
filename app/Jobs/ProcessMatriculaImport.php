<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DTOs\MatriculaImportData;
use App\Mail\MatriculaImportCompleted;
use App\Repositories\MatriculaRepository;
use App\Services\CsvParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProcessMatriculaImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 minutos
    public $tries = 3;
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $filePath,
        private readonly MatriculaImportData $importData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        MatriculaRepository $matriculaRepository,
        CsvParserService $csvParserService
    ): void {
        try {
            Log::info('Iniciando processamento de importação de matrículas', [
                'file_path' => $this->filePath,
                'user_id' => $this->importData->userId,
                'options' => $this->importData->toArray()
            ]);

            // Validar arquivo
            if (!Storage::disk('local')->exists($this->filePath)) {
                throw new \Exception('Arquivo de importação não encontrado');
            }

            // Parsear CSV
            $data = $csvParserService->parse($this->filePath);
            
            if (empty($data)) {
                throw new \Exception('Arquivo CSV vazio ou inválido');
            }

            $headers = array_keys($data[0]);
            $totalRecords = count($data);

            Log::info("Processando {$totalRecords} registros de matrículas");

            // Estatísticas
            $stats = [
                'total_processed' => $totalRecords,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => [],
                'started_at' => now(),
                'completed_at' => null,
            ];

            // Processar em lotes
            $batchSize = $this->importData->batchSize;
            $chunks = array_chunk($data, $batchSize);

            foreach ($chunks as $batchIndex => $chunk) {
                $this->processBatch(
                    $chunk,
                    $headers,
                    $matriculaRepository,
                    $stats,
                    $batchIndex + 1,
                    count($chunks)
                );

                // Verificar se o job foi cancelado
                if ($this->isDeleted()) {
                    Log::info('Job de importação cancelado pelo usuário');
                    return;
                }
            }

            $stats['completed_at'] = now();
            $stats['duration'] = $stats['started_at']->diffInSeconds($stats['completed_at']);

            // Log de conclusão
            Log::info('Importação de matrículas concluída com sucesso', [
                'file_path' => $this->filePath,
                'user_id' => $this->importData->userId,
                'stats' => $stats
            ]);

            // Enviar e-mail de notificação se configurado
            if ($this->importData->notificationEmail) {
                $this->sendNotificationEmail($stats);
            }

            // Limpar arquivo temporário
            $this->cleanupFile();

        } catch (\Exception $e) {
            Log::error('Erro no job de importação de matrículas', [
                'file_path' => $this->filePath,
                'user_id' => $this->importData->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Enviar e-mail de erro se configurado
            if ($this->importData->notificationEmail) {
                $this->sendErrorNotification($e->getMessage());
            }

            // Limpar arquivo em caso de erro
            $this->cleanupFile();

            throw $e;
        }
    }

    /**
     * Processar lote de registros
     */
    private function processBatch(
        array $chunk,
        array $headers,
        MatriculaRepository $matriculaRepository,
        array &$stats,
        int $batchNumber,
        int $totalBatches
    ): void {
        Log::info("Processando lote {$batchNumber} de {$totalBatches}");

        foreach ($chunk as $rowIndex => $row) {
            try {
                $globalRowIndex = ($batchNumber - 1) * $this->importData->batchSize + $rowIndex + 1;
                
                $result = $this->processMatriculaRow($row, $headers, $matriculaRepository, $globalRowIndex);
                
                switch ($result['action']) {
                    case 'created':
                        $stats['created']++;
                        break;
                    case 'updated':
                        $stats['updated']++;
                        break;
                    case 'skipped':
                        $stats['skipped']++;
                        break;
                }

                if (!empty($result['errors'])) {
                    $stats['errors'][] = [
                        'row' => $globalRowIndex,
                        'errors' => $result['errors']
                    ];
                }

            } catch (\Exception $e) {
                Log::error("Erro ao processar linha {$globalRowIndex}", [
                    'error' => $e->getMessage(),
                    'row_data' => $row
                ]);

                $stats['errors'][] = [
                    'row' => $globalRowIndex,
                    'errors' => [$e->getMessage()]
                ];
            }
        }
    }

    /**
     * Processar linha individual de matrícula
     */
    private function processMatriculaRow(
        array $row,
        array $headers,
        MatriculaRepository $matriculaRepository,
        int $rowNumber
    ): array {
        // Validar dados obrigatórios
        $validationResult = $this->validateRow($row, $headers, $rowNumber);
        
        if (!$validationResult['valid']) {
            return [
                'action' => 'skipped',
                'errors' => $validationResult['errors']
            ];
        }

        // Preparar dados para inserção/atualização
        $matriculaData = $this->prepareMatriculaData($row, $headers);

        // Verificar se matrícula já existe
        $existingMatricula = $matriculaRepository->findByCpf($matriculaData['cpf']);

        if ($existingMatricula) {
            if ($this->importData->updateExisting) {
                // Atualizar matrícula existente
                $matriculaRepository->update($existingMatricula, $matriculaData);
                
                return [
                    'action' => 'updated',
                    'matricula_id' => $existingMatricula->id
                ];
            } else {
                // Pular se não deve atualizar
                return [
                    'action' => 'skipped',
                    'errors' => ['Matrícula já existe e atualização não está habilitada']
                ];
            }
        } else {
            // Criar nova matrícula
            $matricula = $matriculaRepository->create($matriculaData);
            
            return [
                'action' => 'created',
                'matricula_id' => $matricula->id
            ];
        }
    }

    /**
     * Validar linha de dados
     */
    private function validateRow(array $row, array $headers, int $rowNumber): array
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
     * Preparar dados da matrícula
     */
    private function prepareMatriculaData(array $row, array $headers): array
    {
        $data = [];

        // Mapear campos do CSV para campos do modelo
        $fieldMapping = [
            'nome_completo' => 'nome_completo',
            'cpf' => 'cpf',
            'rg' => 'rg',
            'orgao_emissor' => 'orgao_emissor',
            'sexo' => 'sexo',
            'estado_civil' => 'estado_civil',
            'nacionalidade' => 'nacionalidade',
            'naturalidade' => 'naturalidade',
            'cep' => 'cep',
            'logradouro' => 'logradouro',
            'numero' => 'numero',
            'complemento' => 'complemento',
            'bairro' => 'bairro',
            'cidade' => 'cidade',
            'estado' => 'estado',
            'telefone_fixo' => 'telefone_fixo',
            'telefone_celular' => 'telefone_celular',
            'email' => 'email',
            'nome_pai' => 'nome_pai',
            'nome_mae' => 'nome_mae',
            'modalidade' => 'modalidade',
            'curso' => 'curso',
            'ultima_serie' => 'ultima_serie',
            'ano_conclusao' => 'ano_conclusao',
            'escola_origem' => 'escola_origem',
            'forma_pagamento' => 'forma_pagamento',
            'valor_total_curso' => 'valor_total_curso',
            'valor_matricula' => 'valor_matricula',
            'valor_mensalidade' => 'valor_mensalidade',
            'numero_parcelas' => 'numero_parcelas',
            'dia_vencimento' => 'dia_vencimento',
            'observacoes' => 'observacoes',
            'parceiro_id' => 'parceiro_id',
        ];

        foreach ($fieldMapping as $csvField => $modelField) {
            if (isset($row[$csvField]) && !empty($row[$csvField])) {
                $data[$modelField] = $row[$csvField];
            }
        }

        // Aplicar parceiro_id se fornecido no importData
        if ($this->importData->parceiroId) {
            $data['parceiro_id'] = $this->importData->parceiroId;
        }

        // Adicionar campos de auditoria
        $data['created_by'] = $this->importData->userId;
        $data['updated_by'] = $this->importData->userId;

        return $data;
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
     * Enviar e-mail de notificação de conclusão
     */
    private function sendNotificationEmail(array $stats): void
    {
        try {
            Mail::to($this->importData->notificationEmail)
                ->send(new MatriculaImportCompleted($stats));
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
            Mail::to($this->importData->notificationEmail)
                ->send(new MatriculaImportCompleted([
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
     * Limpar arquivo temporário
     */
    private function cleanupFile(): void
    {
        if (Storage::disk('local')->exists($this->filePath)) {
            Storage::disk('local')->delete($this->filePath);
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
        Log::error('Job de importação falhou definitivamente', [
            'file_path' => $this->filePath,
            'user_id' => $this->importData->userId,
            'error' => $exception->getMessage()
        ]);

        // Limpar arquivo em caso de falha
        $this->cleanupFile();
    }
}
