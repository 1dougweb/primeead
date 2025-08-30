<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MatriculaImportRequest;
use App\Services\MatriculaImportService;
use App\Services\CsvParserService;
use App\Services\ColumnMappingService;
use App\Models\SystemSetting;
use App\Models\Parceiro;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MatriculaImportController extends Controller
{
    public function __construct(
        private readonly MatriculaImportService $importService,
        private readonly CsvParserService $csvParserService,
        private readonly ColumnMappingService $columnMappingService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:matriculas.create');
    }

    /**
     * Exibir formulário de importação
     */
    public function index()
    {
        $formSettings = SystemSetting::getFormSettings();
        
        $parceiros = Parceiro::aprovados()
            ->orderBy('nome_fantasia')
            ->orderBy('razao_social')
            ->orderBy('nome_completo')
            ->get()
            ->filter(function($parceiro) {
                return !empty($parceiro->nome_exibicao);
            });

        return view('admin.matriculas.importar', compact('formSettings', 'parceiros'));
    }

    /**
     * Processar upload e importação
     */
    public function store(MatriculaImportRequest $request): JsonResponse
    {
        try {
            $importData = \App\DTOs\MatriculaImportData::fromRequest(
                $request->validated(),
                auth()->id()
            );

            $result = $this->importService->processImport($importData);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            Log::error('Erro na importação de matrículas', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download do template CSV
     */
    public function downloadTemplate()
    {
        $headers = [
            // Identificação
            'inscricao_id',
            'numero_matricula',
            
            // Dados Pessoais
            'nome_completo',
            'data_nascimento',
            'cpf',
            'rg',
            'orgao_emissor',
            'sexo',
            'estado_civil',
            'nacionalidade',
            'naturalidade',
            
            // Contato
            'cep',
            'logradouro',
            'numero',
            'complemento',
            'bairro',
            'cidade',
            'estado',
            'telefone_fixo',
            'telefone_celular',
            'email',
            
            // Familiares
            'nome_pai',
            'nome_mae',
            
            // Dados Acadêmicos
            'modalidade',
            'curso',
            'ultima_serie',
            'ano_conclusao',
            'escola_origem',
            
            // Status e Configuração
            'status',
            'escola_parceira',
            'parceiro_id',
            
            // Pagamento
            'forma_pagamento',
            'tipo_boleto',
            'valor_total_curso',
            'valor_matricula',
            'valor_mensalidade',
            'numero_parcelas',
            'dia_vencimento',
            'forma_pagamento_mensalidade',
            'parcelas_ativas',
            'parcelas_geradas',
            'parcelas_pagas',
            'percentual_juros',
            'desconto',
            
            // Documentos
            'doc_rg_cpf',
            'doc_comprovante',
            'doc_historico',
            'doc_certificado',
            'doc_outros',
            
            // Google Drive
            'google_drive_folder_id',
            
            // Observações
            'observacoes'
        ];

        $sampleData = [
            // Identificação
            '1',
            '2024000001',
            
            // Dados Pessoais
            'João Silva Santos',
            '1990-05-15',
            '123.456.789-00',
            '12.345.678-9',
            'SSP',
            'M',
            'solteiro',
            'Brasileira',
            'São Paulo',
            
            // Contato
            '01234-567',
            'Rua das Flores',
            '123',
            'Apto 45',
            'Centro',
            'São Paulo',
            'SP',
            '(11) 3333-4444',
            '(11) 99999-8888',
            'joao.silva@email.com',
            
            // Familiares
            'José Silva Santos',
            'Maria Silva Santos',
            
            // Dados Acadêmicos
            'ensino-medio',
            'Supletivo Ensino Médio',
            '2º ano',
            '2020',
            'Escola Estadual',
            
            // Status e Configuração
            'pre_matricula',
            '1',
            '1',
            
            // Pagamento
            'boleto',
            'mensal',
            '1200.00',
            '200.00',
            '100.00',
            '10',
            '15',
            'boleto',
            '10',
            '10',
            '0',
            '2.5',
            '0.00',
            
            // Documentos
            'documento1.pdf,documento2.pdf',
            'comprovante.pdf',
            'historico.pdf',
            'certificado.pdf',
            'outros.pdf',
            
            // Google Drive
            '1Bxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            
            // Observações
            'Observações sobre o aluno'
        ];

        $csvContent = $this->csvParserService->generateTemplate($headers, $sampleData);

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="template_importacao_matriculas.csv"');
    }

    /**
     * Analisar arquivo CSV e detectar colunas
     */
    public function analyzeFile(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'import_file' => 'required|file|mimes:csv,txt|max:10240'
            ]);

            $file = $request->file('import_file');
            $tempPath = $file->getRealPath();
            
            // Analisar arquivo
            $analysis = $this->columnMappingService->analyzeCsvFile($tempPath);
            
            // Gerar mapeamento automático
            $autoMapping = $this->columnMappingService->generateAutomaticMapping($analysis['headers']);
            
            // Validar mapeamento
            $validation = $this->columnMappingService->validateColumnMapping($autoMapping, $analysis['headers']);
            
            return response()->json([
                'success' => true,
                'analysis' => $analysis,
                'auto_mapping' => $autoMapping,
                'validation' => $validation,
                'file_info' => [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao analisar arquivo CSV', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao analisar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar status da importação
     */
    public function status(): JsonResponse
    {
        try {
            $importFiles = Storage::disk('local')->files('imports');
            
            $status = [
                'has_imports' => !empty($importFiles),
                'import_count' => count($importFiles),
                'last_import' => null
            ];

            if (!empty($importFiles)) {
                $lastFile = end($importFiles);
                $status['last_import'] = [
                    'file' => basename($lastFile),
                    'size' => Storage::disk('local')->size($lastFile),
                    'modified' => Storage::disk('local')->lastModified($lastFile)
                ];
            }

            return response()->json($status);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar status da importação', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar arquivos de importação antigos
     */
    public function cleanup(): JsonResponse
    {
        try {
            $importFiles = Storage::disk('local')->files('imports');
            $deletedCount = 0;

            foreach ($importFiles as $file) {
                // Deletar arquivos mais antigos que 7 dias
                if (Storage::disk('local')->lastModified($file) < now()->subDays(7)->timestamp) {
                    Storage::disk('local')->delete($file);
                    $deletedCount++;
                }
            }

            Log::info('Limpeza de arquivos de importação concluída', [
                'user_id' => auth()->id(),
                'deleted_count' => $deletedCount
            ]);

            return response()->json([
                'success' => true,
                'message' => "Limpeza concluída. {$deletedCount} arquivos removidos.",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na limpeza de arquivos de importação', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro na limpeza: ' . $e->getMessage()
            ], 500);
        }
    }
}
