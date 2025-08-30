<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DTOs\MatriculaImportData;
use App\Services\MatriculaImportService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImportMatriculasCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'matriculas:import 
                            {file : Caminho para o arquivo CSV}
                            {--dry-run : Executar sem salvar no banco}
                            {--update-existing : Atualizar matrículas existentes}
                            {--ignore-duplicates : Ignorar matrículas duplicadas (padrão)}
                            {--batch-size=100 : Tamanho do lote para processamento}
                            {--parceiro-id= : ID do parceiro para associar às matrículas}
                            {--user-id=1 : ID do usuário que está executando a importação}';

    /**
     * The console command description.
     */
    protected $description = 'Importar matrículas de arquivo CSV com validação e tratamento de duplicatas';

    /**
     * Execute the console command.
     */
    public function handle(MatriculaImportService $importService): int
    {
        $filePath = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $updateExisting = $this->option('update-existing');
        $ignoreDuplicates = $this->option('ignore-duplicates');
        $batchSize = (int) $this->option('batch-size');
        $parceiroId = $this->option('parceiro-id') ? (int) $this->option('parceiro-id') : null;
        $userId = (int) $this->option('user-id');

        // Validar arquivo
        if (!file_exists($filePath)) {
            $this->error("❌ Arquivo não encontrado: {$filePath}");
            return 1;
        }

        $this->info("🚀 Iniciando importação de matrículas...");
        $this->info("📁 Arquivo: {$filePath}");
        $this->info("🔍 Modo: " . ($dryRun ? 'Simulação' : 'Importação Real'));
        $this->info("🔄 Atualizar existentes: " . ($updateExisting ? 'Sim' : 'Não'));
        $this->info("🚫 Ignorar duplicatas: " . ($ignoreDuplicates ? 'Sim' : 'Não'));
        $this->info("📦 Tamanho do lote: {$batchSize}");

        if ($parceiroId) {
            $this->info("🤝 Parceiro ID: {$parceiroId}");
        }

        try {
            // Criar arquivo temporário para simular UploadedFile
            $tempFile = $this->createTempFile($filePath);
            
            // Criar DTO de importação
            $importData = new MatriculaImportData(
                importFile: $tempFile,
                updateExisting: $updateExisting,
                ignoreDuplicates: $ignoreDuplicates,
                batchSize: $batchSize,
                dryRun: $dryRun,
                parceiroId: $parceiroId,
                notificationEmail: null,
                userId: $userId
            );

            // Processar importação
            $result = $importService->processImport($importData);

            if ($result['success']) {
                $this->info("✅ Importação concluída com sucesso!");
                
                if (isset($result['summary'])) {
                    $this->displaySummary($result['summary']);
                }
                
                return 0;
            } else {
                $this->error("❌ Erro na importação: " . $result['message']);
                
                if (isset($result['errors'])) {
                    $this->displayErrors($result['errors']);
                }
                
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Erro inesperado: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Criar arquivo temporário para simular UploadedFile
     */
    private function createTempFile(string $filePath): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'import_');
        copy($filePath, $tempPath);
        
        return new UploadedFile(
            $tempPath,
            basename($filePath),
            mime_content_type($filePath),
            null,
            true
        );
    }

    /**
     * Exibir resumo da importação
     */
    private function displaySummary(array $summary): void
    {
        $this->newLine();
        $this->info("📊 Resumo da Importação:");
        
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total Processados', $summary['total_processed'] ?? 0],
                ['Registros Válidos', $summary['valid_records'] ?? 0],
                ['Registros Inválidos', $summary['invalid_records'] ?? 0],
                ['Duplicatas', $summary['duplicates'] ?? 0],
            ]
        );

        if (isset($summary['duration'])) {
            $this->info("⏱️ Tempo de processamento: " . gmdate('H:i:s', $summary['duration']));
        }
    }

    /**
     * Exibir erros encontrados
     */
    private function displayErrors(array $errors): void
    {
        if (empty($errors)) {
            return;
        }

        $this->newLine();
        $this->warn("⚠️ Erros encontrados:");

        foreach (array_slice($errors, 0, 10) as $error) {
            $this->line("Linha {$error['row']}: " . implode(', ', $error['errors']));
        }

        if (count($errors) > 10) {
            $this->line("... e mais " . (count($errors) - 10) . " erros.");
        }
    }
}
