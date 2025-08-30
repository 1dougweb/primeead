<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Matricula;
use App\Models\Inscricao;
use App\Models\Parceiro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImportMatriculas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matriculas:importar 
                            {file : Caminho para o arquivo CSV/Excel}
                            {--dry-run : Executar sem salvar no banco}
                            {--update-existing : Atualizar matrículas existentes}
                            {--ignore-duplicates : Ignorar matrículas duplicadas (padrão)}
                            {--batch-size=100 : Tamanho do lote para processamento}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importar matrículas de arquivo CSV/Excel com validação e tratamento de duplicatas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $updateExisting = $this->option('update-existing');
        $ignoreDuplicates = $this->option('ignore-duplicates');
        $batchSize = (int) $this->option('batch-size');

        if (!file_exists($filePath)) {
            $this->error("❌ Arquivo não encontrado: {$filePath}");
            return 1;
        }

        $this->info("🚀 Iniciando importação de matrículas...");
        $this->info("📁 Arquivo: {$filePath}");
        $this->info("🔍 Modo: " . ($dryRun ? 'Simulação' : 'Importação Real'));
        $this->info("🔄 Atualizar existentes: " . ($updateExisting ? 'Sim' : 'Não'));
        $this->info("🚫 Ignorar duplicatas: " . ($ignoreDuplicates ? 'Sim' : 'Não'));

        try {
            $data = $this->readFile($filePath);
            
            if (empty($data)) {
                $this->error("❌ Nenhum dado encontrado no arquivo");
                return 1;
            }

            $this->info("📊 Total de registros encontrados: " . count($data));

            // Validar estrutura do arquivo
            $this->validateFileStructure($data[0]);

            // Processar em lotes
            $totalProcessed = 0;
            $totalCreated = 0;
            $totalUpdated = 0;
            $totalSkipped = 0;
            $totalErrors = 0;

            $chunks = array_chunk($data, $batchSize);

            foreach ($chunks as $index => $chunk) {
                $this->info("📦 Processando lote " . ($index + 1) . " de " . count($chunks) . " (" . count($chunk) . " registros)");
                
                $result = $this->processBatch($chunk, $dryRun, $updateExisting, $ignoreDuplicates);
                
                $totalProcessed += $result['processed'];
                $totalCreated += $result['created'];
                $totalUpdated += $result['updated'];
                $totalSkipped += $result['skipped'];
                $totalErrors += $result['errors'];
            }

            // Resumo final
            $this->newLine();
            $this->info("✅ Importação concluída!");
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Total Processados', $totalProcessed],
                    ['Criados', $totalCreated],
                    ['Atualizados', $totalUpdated],
                    ['Ignorados', $totalSkipped],
                    ['Erros', $totalErrors],
                ]
            );

            if ($totalErrors > 0) {
                $this->warn("⚠️  {$totalErrors} registros com erros. Verifique os logs para detalhes.");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro durante a importação: " . $e->getMessage());
            Log::error('Erro na importação de matrículas', [
                'file' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Ler arquivo CSV/Excel
     */
    private function readFile($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if ($extension === 'csv') {
            return $this->readCsvFile($filePath);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            return $this->readExcelFile($filePath);
        } else {
            throw new \Exception("Formato de arquivo não suportado. Use CSV, XLSX ou XLS.");
        }
    }

    /**
     * Ler arquivo CSV
     */
    private function readCsvFile($filePath)
    {
        $data = [];
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            throw new \Exception("Não foi possível abrir o arquivo CSV");
        }

        // Ler cabeçalho
        $headers = fgetcsv($handle);
        if (!$headers) {
            throw new \Exception("Arquivo CSV vazio ou inválido");
        }

        // Normalizar cabeçalhos
        $headers = array_map(function($header) {
            return strtolower(trim(str_replace([' ', '-', '_'], '_', $header)));
        }, $headers);

        $this->info("📋 Cabeçalhos encontrados: " . implode(', ', $headers));

        // Ler linhas de dados
        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            // Verificar se a linha não está vazia
            if (array_filter($row)) {
                $data[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $data;
    }

    /**
     * Ler arquivo Excel
     */
    private function readExcelFile($filePath)
    {
        // Para arquivos Excel, você precisaria instalar o pacote PhpSpreadsheet
        // Por enquanto, vamos retornar erro
        throw new \Exception("Importação de arquivos Excel não implementada. Use arquivos CSV.");
    }

    /**
     * Validar estrutura do arquivo
     */
    private function validateFileStructure($firstRow)
    {
        $requiredFields = [
            'nome_completo',
            'cpf',
            'email',
            'data_nascimento',
            'modalidade',
            'curso'
        ];

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $firstRow)) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new \Exception("Campos obrigatórios ausentes: " . implode(', ', $missingFields));
        }

        $this->info("✅ Estrutura do arquivo validada com sucesso");
    }

    /**
     * Processar lote de registros
     */
    private function processBatch($chunk, $dryRun, $updateExisting, $ignoreDuplicates)
    {
        $processed = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($chunk as $row) {
            try {
                $processed++;
                
                // Normalizar dados
                $normalizedData = $this->normalizeData($row);
                
                // Validar dados
                $validation = $this->validateRow($normalizedData);
                if (!$validation['valid']) {
                    $this->warn("⚠️  Linha {$processed}: " . implode(', ', $validation['errors']));
                    $errors++;
                    continue;
                }

                // Verificar se já existe
                $existingMatricula = $this->findExistingMatricula($normalizedData);
                
                if ($existingMatricula) {
                    if ($ignoreDuplicates && !$updateExisting) {
                        $this->line("⏭️  Matrícula existente ignorada: {$normalizedData['cpf']} - {$normalizedData['nome_completo']}");
                        $skipped++;
                        continue;
                    }
                    
                    if ($updateExisting) {
                        if (!$dryRun) {
                            $this->updateMatricula($existingMatricula, $normalizedData);
                        }
                        $this->line("🔄 Matrícula atualizada: {$normalizedData['cpf']} - {$normalizedData['nome_completo']}");
                        $updated++;
                    }
                } else {
                    if (!$dryRun) {
                        $this->createMatricula($normalizedData);
                    }
                    $this->line("✅ Nova matrícula: {$normalizedData['cpf']} - {$normalizedData['nome_completo']}");
                    $created++;
                }

            } catch (\Exception $e) {
                $this->error("❌ Erro na linha {$processed}: " . $e->getMessage());
                $errors++;
                Log::error('Erro ao processar linha da importação', [
                    'row' => $row,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * Normalizar dados da linha
     */
    private function normalizeData($row)
    {
        $normalized = [];
        
        // Mapear campos
        $fieldMapping = [
            'nome_completo' => ['nome_completo', 'nome', 'nome_completo_aluno'],
            'cpf' => ['cpf', 'cpf_aluno', 'documento'],
            'rg' => ['rg', 'rg_aluno', 'identidade'],
            'orgao_emissor' => ['orgao_emissor', 'orgao', 'emissor'],
            'sexo' => ['sexo', 'genero'],
            'estado_civil' => ['estado_civil', 'civil'],
            'nacionalidade' => ['nacionalidade', 'nacionalidade_aluno'],
            'naturalidade' => ['naturalidade', 'naturalidade_aluno'],
            'data_nascimento' => ['data_nascimento', 'nascimento', 'data_nasc', 'nasc'],
            'cep' => ['cep', 'cep_aluno'],
            'logradouro' => ['logradouro', 'endereco', 'rua'],
            'numero' => ['numero', 'numero_endereco'],
            'complemento' => ['complemento', 'complemento_endereco'],
            'bairro' => ['bairro', 'bairro_aluno'],
            'cidade' => ['cidade', 'cidade_aluno'],
            'estado' => ['estado', 'uf', 'estado_aluno'],
            'telefone_fixo' => ['telefone_fixo', 'telefone', 'tel_fixo'],
            'telefone_celular' => ['telefone_celular', 'celular', 'cel', 'telefone_cel'],
            'email' => ['email', 'e_mail', 'email_aluno'],
            'nome_pai' => ['nome_pai', 'pai'],
            'nome_mae' => ['nome_mae', 'mae'],
            'modalidade' => ['modalidade', 'tipo_ensino', 'nivel'],
            'curso' => ['curso', 'curso_aluno'],
            'ultima_serie' => ['ultima_serie', 'serie', 'ano_escolar'],
            'ano_conclusao' => ['ano_conclusao', 'ano_conclusao_escola'],
            'escola_origem' => ['escola_origem', 'escola_anterior', 'instituicao_anterior'],
            'forma_pagamento' => ['forma_pagamento', 'pagamento'],
            'valor_total_curso' => ['valor_total_curso', 'valor_curso', 'valor_total'],
            'valor_matricula' => ['valor_matricula', 'valor_mat'],
            'valor_mensalidade' => ['valor_mensalidade', 'valor_mensal'],
            'numero_parcelas' => ['numero_parcelas', 'parcelas', 'qtd_parcelas'],
            'dia_vencimento' => ['dia_vencimento', 'vencimento'],
            'observacoes' => ['observacoes', 'obs', 'observacao'],
            'parceiro_id' => ['parceiro_id', 'parceiro', 'id_parceiro'],
        ];

        foreach ($fieldMapping as $targetField => $sourceFields) {
            foreach ($sourceFields as $sourceField) {
                if (isset($row[$sourceField]) && !empty(trim($row[$sourceField]))) {
                    $normalized[$targetField] = trim($row[$sourceField]);
                    break;
                }
            }
        }

        // Valores padrão
        $defaults = [
            'status' => 'pre_matricula',
            'escola_parceira' => false,
            'parcelas_ativas' => false,
            'parcelas_geradas' => 0,
            'parcelas_pagas' => 0,
        ];

        foreach ($defaults as $field => $defaultValue) {
            if (!isset($normalized[$field])) {
                $normalized[$field] = $defaultValue;
            }
        }

        return $normalized;
    }

    /**
     * Validar dados da linha
     */
    private function validateRow($data)
    {
        // Normalizar data de nascimento
        if (isset($data['data_nascimento'])) {
            $data['data_nascimento'] = $this->normalizeDate($data['data_nascimento']);
        }

        $rules = [
            'nome_completo' => 'required|string|max:255',
            'cpf' => 'required|string|max:14',
            'email' => 'required|email|max:255',
            'data_nascimento' => 'required|date',
            'modalidade' => 'required|string|max:255',
            'curso' => 'required|string|max:255',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all()
            ];
        }

        return ['valid' => true, 'errors' => []];
    }

    /**
     * Normalizar formato de data
     */
    private function normalizeDate($dateString)
    {
        $dateString = trim($dateString);
        
        // Se já está no formato Y-m-d, retornar como está
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }
        
        // Tentar formato brasileiro d/m/Y
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateString, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }
        
        // Tentar formato brasileiro d-m-Y
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $dateString, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }
        
        // Tentar formato brasileiro d.m.Y
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $dateString, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }
        
        // Se não conseguir normalizar, retornar original para validação falhar
        return $dateString;
    }

    /**
     * Buscar matrícula existente
     */
    private function findExistingMatricula($data)
    {
        // Buscar por CPF (mais confiável)
        if (isset($data['cpf'])) {
            $cpf = preg_replace('/[^0-9]/', '', $data['cpf']);
            return Matricula::where('cpf', 'like', "%{$cpf}%")->first();
        }

        // Buscar por email
        if (isset($data['email'])) {
            return Matricula::where('email', $data['email'])->first();
        }

        // Buscar por nome completo (menos confiável)
        if (isset($data['nome_completo'])) {
            return Matricula::where('nome_completo', 'like', "%{$data['nome_completo']}%")->first();
        }

        return null;
    }

    /**
     * Criar nova matrícula
     */
    private function createMatricula($data)
    {
        DB::beginTransaction();
        
        try {
            // Gerar número de matrícula
            $currentYear = date('Y');
            $lastNumber = Matricula::whereYear('created_at', $currentYear)->count() + 1;
            $data['numero_matricula'] = $currentYear . str_pad($lastNumber, 6, '0', STR_PAD_LEFT);
            
            // Adicionar dados de auditoria
            $data['created_by'] = 1; // Usuário admin padrão
            $data['updated_by'] = 1;
            
            // Criar matrícula
            $matricula = Matricula::create($data);
            
            DB::commit();
            
            Log::info('Matrícula criada via importação', [
                'matricula_id' => $matricula->id,
                'numero_matricula' => $matricula->numero_matricula,
                'cpf' => $matricula->cpf
            ]);
            
            return $matricula;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Atualizar matrícula existente
     */
    private function updateMatricula($matricula, $data)
    {
        DB::beginTransaction();
        
        try {
            // Remover campos que não devem ser atualizados
            unset($data['numero_matricula'], $data['created_by']);
            
            // Adicionar dados de auditoria
            $data['updated_by'] = 1;
            
            // Atualizar matrícula
            $matricula->update($data);
            
            DB::commit();
            
            Log::info('Matrícula atualizada via importação', [
                'matricula_id' => $matricula->id,
                'cpf' => $matricula->cpf
            ]);
            
            return $matricula;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
