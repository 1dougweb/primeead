<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

class MatriculaImportData
{
    public function __construct(
        public readonly UploadedFile $importFile,
        public readonly bool $updateExisting = false,
        public readonly bool $ignoreDuplicates = true,
        public readonly int $batchSize = 100,
        public readonly bool $dryRun = false,
        public readonly ?int $parceiroId = null,
        public readonly ?string $notificationEmail = null,
        public readonly array $columnMapping = [],
        public readonly bool $autoDetectColumns = true,
        public readonly int $userId,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            importFile: $data['import_file'],
            updateExisting: $data['update_existing'] ?? false,
            ignoreDuplicates: $data['ignore_duplicates'] ?? true,
            batchSize: $data['batch_size'] ?? 100,
            dryRun: $data['dry_run'] ?? false,
            parceiroId: $data['parceiro_id'] ?? null,
            notificationEmail: $data['notification_email'] ?? null,
            columnMapping: $data['column_mapping'] ?? [],
            autoDetectColumns: $data['auto_detect_columns'] ?? true,
            userId: $userId,
        );
    }

    public function toArray(): array
    {
        return [
            'import_file' => $this->importFile->getClientOriginalName(),
            'update_existing' => $this->updateExisting,
            'ignore_duplicates' => $this->ignoreDuplicates,
            'batch_size' => $this->batchSize,
            'dry_run' => $this->dryRun,
            'parceiro_id' => $this->parceiroId,
            'notification_email' => $this->notificationEmail,
            'column_mapping' => $this->columnMapping,
            'auto_detect_columns' => $this->autoDetectColumns,
            'user_id' => $this->userId,
        ];
    }
}
