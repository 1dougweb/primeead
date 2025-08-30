<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Statement;

class CsvParserService
{
    /**
     * Parsear arquivo CSV
     */
    public function parse(string $filePath): array
    {
        try {
            $fileContent = Storage::disk('local')->get($filePath);
            
            if (!$fileContent) {
                throw new \Exception('Não foi possível ler o arquivo');
            }

            // Criar CSV Reader
            $csv = Reader::createFromString($fileContent);
            $csv->setHeaderOffset(0); // Primeira linha como cabeçalho
            $csv->setDelimiter(',');

            // Converter para array
            $records = iterator_to_array($csv->getRecords());
            
            return $records;

        } catch (\Exception $e) {
            throw new \Exception('Erro ao processar arquivo CSV: ' . $e->getMessage());
        }
    }

    /**
     * Parsear CSV com validação de estrutura
     */
    public function parseWithValidation(string $filePath, array $requiredHeaders): array
    {
        $data = $this->parse($filePath);
        
        if (empty($data)) {
            throw new \Exception('Arquivo CSV vazio');
        }

        // Verificar se todos os cabeçalhos obrigatórios estão presentes
        $firstRecord = reset($data);
        $headers = array_keys($firstRecord);
        
        $missingHeaders = array_diff($requiredHeaders, $headers);
        
        if (!empty($missingHeaders)) {
            throw new \Exception(
                'Cabeçalhos obrigatórios ausentes: ' . implode(', ', $missingHeaders)
            );
        }

        return $data;
    }

    /**
     * Parsear CSV em lotes para arquivos grandes
     */
    public function parseInBatches(string $filePath, int $batchSize = 1000): \Generator
    {
        try {
            $fileContent = Storage::disk('local')->get($filePath);
            
            if (!$fileContent) {
                throw new \Exception('Não foi possível ler o arquivo');
            }

            $csv = Reader::createFromString($fileContent);
            $csv->setHeaderOffset(0);
            $csv->setDelimiter(',');

            $totalRecords = count($csv);
            $offset = 0;

            while ($offset < $totalRecords) {
                $stmt = Statement::create()
                    ->offset($offset)
                    ->limit($batchSize);

                $records = iterator_to_array($stmt->process($csv));
                
                yield $records;
                
                $offset += $batchSize;
            }

        } catch (\Exception $e) {
            throw new \Exception('Erro ao processar arquivo CSV em lotes: ' . $e->getMessage());
        }
    }

    /**
     * Validar formato CSV
     */
    public function validateFormat(string $filePath): array
    {
        try {
            $fileContent = Storage::disk('local')->get($filePath);
            
            if (!$fileContent) {
                return ['valid' => false, 'error' => 'Arquivo vazio'];
            }

            // Verificar se é um CSV válido
            $lines = explode("\n", trim($fileContent));
            
            if (count($lines) < 2) {
                return ['valid' => false, 'error' => 'Arquivo deve ter pelo menos cabeçalho e uma linha de dados'];
            }

            // Verificar se a primeira linha tem vírgulas (indicando CSV)
            $firstLine = $lines[0];
            if (strpos($firstLine, ',') === false) {
                return ['valid' => false, 'error' => 'Arquivo não parece ser um CSV válido'];
            }

            // Verificar se todas as linhas têm o mesmo número de colunas
            $expectedColumns = count(explode(',', $firstLine));
            
            foreach (array_slice($lines, 1) as $lineNumber => $line) {
                if (empty(trim($line))) continue; // Pular linhas vazias
                
                $columns = count(explode(',', $line));
                if ($columns !== $expectedColumns) {
                    return [
                        'valid' => false, 
                        'error' => "Linha " . ($lineNumber + 2) . " tem {$columns} colunas, esperado {$expectedColumns}"
                    ];
                }
            }

            return ['valid' => true, 'columns' => $expectedColumns, 'rows' => count($lines) - 1];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => 'Erro ao validar arquivo: ' . $e->getMessage()];
        }
    }

    /**
     * Gerar template CSV
     */
    public function generateTemplate(array $headers, array $sampleData = []): string
    {
        $csv = Reader::createFromString('');
        
        // Adicionar cabeçalhos
        $csv->insertOne($headers);
        
        // Adicionar dados de exemplo se fornecidos
        if (!empty($sampleData)) {
            $csv->insertOne($sampleData);
        }

        return $csv->toString();
    }

    /**
     * Limpar dados CSV
     */
    public function cleanData(array $data): array
    {
        $cleaned = [];
        
        foreach ($data as $row) {
            $cleanedRow = [];
            
            foreach ($row as $key => $value) {
                // Remover espaços em branco
                $cleanedValue = trim($value);
                
                // Converter valores vazios para null
                if ($cleanedValue === '') {
                    $cleanedValue = null;
                }
                
                $cleanedRow[$key] = $cleanedValue;
            }
            
            $cleaned[] = $cleanedRow;
        }
        
        return $cleaned;
    }
}
