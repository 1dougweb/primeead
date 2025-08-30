<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MatriculaExportRequest;
use App\Services\MatriculaExportService;
use App\Models\Parceiro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MatriculaExportController extends Controller
{
    public function __construct(
        private readonly MatriculaExportService $exportService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:matriculas.index');
    }

    /**
     * Exibir formulário de exportação
     */
    public function index()
    {
        $parceiros = Parceiro::aprovados()
            ->orderBy('nome_fantasia')
            ->orderBy('razao_social')
            ->get()
            ->filter(function($parceiro) {
                return !empty($parceiro->nome_exibicao);
            });

        $statusOptions = [
            'ativo' => 'Ativo',
            'inativo' => 'Inativo',
            'pendente' => 'Pendente',
            'cancelado' => 'Cancelado'
        ];

        $modalidadeOptions = [
            'ensino-medio' => 'Ensino Médio',
            'ensino-superior' => 'Ensino Superior',
            'curso-tecnico' => 'Curso Técnico',
            'curso-livre' => 'Curso Livre'
        ];

        $columnOptions = [
            // Identificação
            'id' => 'ID',
            'inscricao_id' => 'ID da Inscrição',
            'numero_matricula' => 'Número da Matrícula',
            
            // Dados Pessoais
            'nome_completo' => 'Nome Completo',
            'data_nascimento' => 'Data de Nascimento',
            'cpf' => 'CPF',
            'rg' => 'RG',
            'orgao_emissor' => 'Órgão Emissor',
            'sexo' => 'Sexo',
            'estado_civil' => 'Estado Civil',
            'nacionalidade' => 'Nacionalidade',
            'naturalidade' => 'Naturalidade',
            
            // Contato
            'cep' => 'CEP',
            'logradouro' => 'Logradouro',
            'numero' => 'Número',
            'complemento' => 'Complemento',
            'bairro' => 'Bairro',
            'cidade' => 'Cidade',
            'estado' => 'Estado',
            'telefone_fixo' => 'Telefone Fixo',
            'telefone_celular' => 'Telefone Celular',
            'email' => 'E-mail',
            
            // Familiares
            'nome_pai' => 'Nome do Pai',
            'nome_mae' => 'Nome da Mãe',
            
            // Dados Acadêmicos
            'modalidade' => 'Modalidade',
            'curso' => 'Curso',
            'ultima_serie' => 'Última Série',
            'ano_conclusao' => 'Ano de Conclusão',
            'escola_origem' => 'Escola de Origem',
            
            // Status e Configuração
            'status' => 'Status',
            'escola_parceira' => 'Escola Parceira',
            'parceiro_id' => 'ID do Parceiro',
            
            // Pagamento
            'forma_pagamento' => 'Forma de Pagamento',
            'tipo_boleto' => 'Tipo de Boleto',
            'valor_total_curso' => 'Valor Total do Curso',
            'valor_matricula' => 'Valor da Matrícula',
            'valor_mensalidade' => 'Valor da Mensalidade',
            'numero_parcelas' => 'Número de Parcelas',
            'dia_vencimento' => 'Dia de Vencimento',
            'forma_pagamento_mensalidade' => 'Forma de Pagamento da Mensalidade',
            'parcelas_ativas' => 'Parcelas Ativas',
            'parcelas_geradas' => 'Parcelas Geradas',
            'parcelas_pagas' => 'Parcelas Pagas',
            'percentual_juros' => 'Percentual de Juros',
            'desconto' => 'Desconto',
            
            // Documentos
            'doc_rg_cpf' => 'Documentos RG/CPF',
            'doc_comprovante' => 'Comprovante',
            'doc_historico' => 'Histórico',
            'doc_certificado' => 'Certificado',
            'doc_outros' => 'Outros Documentos',
            
            // Google Drive
            'google_drive_folder_id' => 'ID da Pasta Google Drive',
            
            // Observações e Metadados
            'observacoes' => 'Observações',
            'created_at' => 'Data de Criação',
            'updated_at' => 'Data de Atualização',
            'deleted_at' => 'Data de Exclusão',
            'created_by' => 'Criado por',
            'updated_by' => 'Atualizado por',
        ];

        return view('admin.matriculas.exportar', compact(
            'parceiros',
            'statusOptions',
            'modalidadeOptions',
            'columnOptions'
        ));
    }

    /**
     * Processar exportação
     */
    public function store(MatriculaExportRequest $request): JsonResponse
    {
        try {
            $exportData = \App\DTOs\MatriculaExportData::fromRequest(
                $request->validated(),
                auth()->id()
            );

            $result = $this->exportService->processExport($exportData);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            Log::error('Erro na exportação de matrículas', [
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
     * Download do arquivo exportado
     */
    public function download(string $file): Response
    {
        try {
            $filePath = "exports/{$file}";
            
            if (!Storage::disk('local')->exists($filePath)) {
                abort(404, 'Arquivo não encontrado');
            }

            $fileName = $file;
            $mimeType = $this->getMimeType($file);
            $content = Storage::disk('local')->get($filePath);

            return response($content)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                ->header('Content-Length', strlen($content));

        } catch (\Exception $e) {
            Log::error('Erro ao fazer download do arquivo de exportação', [
                'file' => $file,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            abort(500, 'Erro ao fazer download do arquivo');
        }
    }

    /**
     * Verificar status da exportação
     */
    public function status(): JsonResponse
    {
        try {
            $exportFiles = Storage::disk('local')->files('exports');
            
            $status = [
                'has_exports' => !empty($exportFiles),
                'export_count' => count($exportFiles),
                'last_export' => null,
                'total_size' => 0
            ];

            if (!empty($exportFiles)) {
                $lastFile = end($exportFiles);
                $fileSize = Storage::disk('local')->size($lastFile);
                
                $status['last_export'] = [
                    'file' => basename($lastFile),
                    'size' => $fileSize,
                    'size_formatted' => $this->formatFileSize($fileSize),
                    'modified' => Storage::disk('local')->lastModified($lastFile)
                ];

                // Calcular tamanho total
                foreach ($exportFiles as $file) {
                    $status['total_size'] += Storage::disk('local')->size($file);
                }
                $status['total_size_formatted'] = $this->formatFileSize($status['total_size']);
            }

            return response()->json($status);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar status da exportação', [
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
     * Limpar arquivos de exportação antigos
     */
    public function cleanup(): JsonResponse
    {
        try {
            $deletedCount = $this->exportService->cleanupOldExports();

            Log::info('Limpeza de arquivos de exportação concluída', [
                'user_id' => auth()->id(),
                'deleted_count' => $deletedCount
            ]);

            return response()->json([
                'success' => true,
                'message' => "Limpeza concluída. {$deletedCount} arquivos removidos.",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na limpeza de arquivos de exportação', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro na limpeza: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter MIME type do arquivo
     */
    private function getMimeType(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        return match($extension) {
            'csv' => 'text/csv',
            'xlsx', 'xls' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'json' => 'application/json',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            default => 'application/octet-stream'
        };
    }

    /**
     * Formatar tamanho do arquivo
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor(log($bytes, 1024));
        
        return number_format($bytes / pow(1024, $factor), 2) . ' ' . $units[$factor];
    }
}
