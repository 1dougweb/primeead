<?php

namespace App\Http\Controllers;

use App\Models\ContractTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContractTemplateController extends Controller
{
    /**
     * Middleware para rotas administrativas
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('permission:contract-templates.index')->only(['index', 'show']);
        $this->middleware('permission:contract-templates.create')->only(['create', 'store']);
        $this->middleware('permission:contract-templates.edit')->only(['edit', 'update']);
        $this->middleware('permission:contract-templates.delete')->only(['destroy']);
        $this->middleware('permission:contract-templates.generate-ai')->only(['generateWithAi']);
    }

    /**
     * Listar templates
     */
    public function index()
    {
        $templates = ContractTemplate::withCount('contracts')
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.contracts.templates.index', compact('templates'));
    }

    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        $systemVariables = ContractTemplate::getSystemVariables();
        
        return view('admin.contracts.templates.create', compact('systemVariables'));
    }

    /**
     * Armazenar novo template
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'validity_days' => 'required|integer|min:1|max:365',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $template = ContractTemplate::create([
                'name' => $request->name,
                'description' => $request->description,
                'content' => $request->content,
                'available_variables' => ContractTemplate::getSystemVariables(),
                'validity_days' => $request->validity_days,
                'is_active' => $request->boolean('is_active', true),
                'is_default' => $request->boolean('is_default', false),
            ]);

            return redirect()->route('admin.contracts.templates.index')
                ->with('success', 'Template criado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao criar template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar template específico
     */
    public function show(ContractTemplate $template)
    {
        $template->load(['contracts' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        return view('admin.contracts.templates.show', compact('template'));
    }

    /**
     * Mostrar formulário de edição
     */
    public function edit(ContractTemplate $template)
    {
        $systemVariables = ContractTemplate::getSystemVariables();
        
        return view('admin.contracts.templates.edit', compact('template', 'systemVariables'));
    }

    /**
     * Atualizar template
     */
    public function update(Request $request, ContractTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'validity_days' => 'required|integer|min:1|max:365',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $template->update([
                'name' => $request->name,
                'description' => $request->description,
                'content' => $request->content,
                'validity_days' => $request->validity_days,
                'is_active' => $request->boolean('is_active'),
                'is_default' => $request->boolean('is_default'),
            ]);

            return redirect()->route('admin.contracts.templates.index')
                ->with('success', 'Template atualizado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao atualizar template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Excluir template
     */
    public function destroy(ContractTemplate $template)
    {
        try {
            // Verificar se o template está sendo usado
            if ($template->contracts()->exists()) {
                return redirect()->back()
                    ->with('error', 'Não é possível excluir um template que possui contratos associados.');
            }

            $template->delete();

            return redirect()->route('admin.contracts.templates.index')
                ->with('success', 'Template excluído com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao excluir template: ' . $e->getMessage());
        }
    }

    /**
     * Definir template como padrão
     */
    public function setDefault(ContractTemplate $template)
    {
        try {
            // Remover padrão de todos os outros templates
            ContractTemplate::where('is_default', true)->update(['is_default' => false]);
            
            // Definir este como padrão
            $template->update(['is_default' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Template definido como padrão com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao definir template como padrão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ativar/desativar template
     */
    public function toggleActive(ContractTemplate $template)
    {
        try {
            $template->update(['is_active' => !$template->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Status do template atualizado com sucesso!',
                'is_active' => $template->is_active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicar template
     */
    public function duplicate(ContractTemplate $template)
    {
        try {
            $newTemplate = $template->replicate();
            $newTemplate->name = $template->name . ' (Cópia)';
            $newTemplate->is_default = false;
            $newTemplate->save();

            return redirect()->route('admin.contracts.templates.edit', $newTemplate)
                ->with('success', 'Template duplicado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao duplicar template: ' . $e->getMessage());
        }
    }

    /**
     * Visualizar preview do template
     */
    public function preview(ContractTemplate $template)
    {
        // Dados de exemplo para preview
        $sampleData = [
            'student_name' => 'João da Silva Santos',
            'student_email' => 'joao@exemplo.com',
            'student_cpf' => '123.456.789-00',
            'student_rg' => '12.345.678-9',
            'student_phone' => '(11) 99999-9999',
            'student_address' => 'Rua das Flores, 123, Centro, São Paulo - SP',
            'student_birth_date' => '01/01/1990',
            'student_nationality' => 'Brasileira',
            'student_civil_status' => 'Solteiro(a)',
            'student_mother_name' => 'Maria da Silva',
            'student_father_name' => 'José Santos',
            'course_name' => 'Ensino Médio EJA',
            'course_modality' => 'Presencial',
            'course_shift' => 'Noturno',
            'tuition_value' => 'R$ 250,00',
            'enrollment_value' => 'R$ 100,00',
            'enrollment_number' => '2024001',
            'enrollment_date' => '15/01/2024',
            'due_date' => '10',
            'payment_method' => 'Boleto Bancário',
            'school_name' => 'EJA Supletivo',
            'current_date' => now()->format('d/m/Y'),
            'current_year' => now()->year,
            'contract_date' => now()->format('d/m/Y'),
        ];

        // Processar conteúdo com dados de exemplo
        $content = $template->content;
        foreach ($sampleData as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return view('admin.contracts.templates.preview', compact('template', 'content'));
    }

    /**
     * Obter variáveis disponíveis (API)
     */
    public function getVariables()
    {
        return response()->json([
            'variables' => ContractTemplate::getSystemVariables()
        ]);
    }

    /**
     * Gerar template usando ChatGPT
     */
    public function generateWithAi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'objective' => 'required|string|max:500',
            'contract_type' => 'required|string|in:educacional,matricula,curso,supletivo,eja,tecnico,superior',
            'additional_instructions' => 'nullable|string|max:1000',
            'reference_content' => 'nullable|string|max:50000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Dados inválidos: ' . $validator->errors()->first()
            ], 422);
        }

        try {
            // Verificar se as configurações de AI estão ativas
            $aiSettings = \App\Models\SystemSetting::getAiSettings();
            if (!$aiSettings['is_active'] || empty($aiSettings['api_key'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'ChatGPT não está configurado ou ativo. Configure a API key nas configurações do sistema.'
                ], 400);
            }

            $chatGptService = new \App\Services\ChatGptService();
            
            $result = $chatGptService->generateContractTemplate(
                $request->objective,
                $request->contract_type,
                $request->additional_instructions ?? '',
                $request->reference_content ?? null
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'template' => [
                        'name' => $result['title'],
                        'description' => $result['description'],
                        'content' => $result['content'],
                        'validity_days' => 30, // Padrão
                        'is_active' => true,
                        'is_default' => false
                    ],
                    'message' => 'Template gerado com sucesso!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload e processamento de arquivo de referência
     */
    public function uploadReference(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:5120|mimes:docx,doc,pdf,txt',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Arquivo inválido. Formatos aceitos: DOCX, DOC, PDF, TXT (máx. 5MB)'
            ], 422);
        }

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $content = '';

            switch (strtolower($extension)) {
                case 'txt':
                    $content = file_get_contents($file->getPathname());
                    break;
                    
                case 'docx':
                    $content = $this->extractDocxContent($file);
                    break;
                    
                case 'doc':
                    $content = $this->extractDocContent($file);
                    break;
                    
                case 'pdf':
                    $content = $this->extractPdfContent($file);
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de arquivo não suportado'
                    ], 422);
            }

            // Limitar tamanho do conteúdo
            if (strlen($content) > 50000) {
                $content = substr($content, 0, 50000) . '...';
            }

            // Log do conteúdo extraído para debug
            \Log::info('Conteúdo extraído do arquivo de referência', [
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension,
                'size' => strlen($content),
                'preview' => substr($content, 0, 500) . '...'
            ]);

            return response()->json([
                'success' => true,
                'content' => $content,
                'message' => 'Arquivo processado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extrair conteúdo de arquivo DOCX
     */
    private function extractDocxContent($file)
    {
        $zip = new \ZipArchive();
        $content = '';
        
        if ($zip->open($file->getPathname()) === TRUE) {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml) {
                $dom = new \DOMDocument();
                $dom->loadXML($xml);
                
                // Processar o XML preservando estrutura
                $content = $this->processDocxXml($dom);
            }
            $zip->close();
        }
        
        return $content;
    }

    /**
     * Processar XML do DOCX preservando estrutura
     */
    private function processDocxXml($dom)
    {
        $content = '';
        $xpath = new \DOMXPath($dom);
        
        // Registrar namespace
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        
        // Extrair todo o conteúdo do documento preservando estrutura
        $body = $xpath->query('//w:body')->item(0);
        
        if ($body) {
            $content = $this->processDocxElement($xpath, $body);
        }
        
        return trim($content);
    }

    /**
     * Processar elemento do DOCX recursivamente
     */
    private function processDocxElement($xpath, $element)
    {
        $content = '';
        
        foreach ($element->childNodes as $child) {
            switch ($child->nodeName) {
                case 'w:p': // Parágrafo
                    $paragraphText = $this->extractTextFromElement($xpath, $child);
                    
                    // Verificar se é um título/cabeçalho
                    $styleNodes = $xpath->query('.//w:pStyle', $child);
                    $isHeading = false;
                    foreach ($styleNodes as $styleNode) {
                        $styleVal = $styleNode->getAttribute('w:val');
                        if (strpos($styleVal, 'Heading') !== false || strpos($styleVal, 'Title') !== false) {
                            $isHeading = true;
                            break;
                        }
                    }
                    
                    if (!empty($paragraphText)) {
                        if ($isHeading) {
                            $content .= "\n=== " . $paragraphText . " ===\n\n";
                        } else {
                            $content .= $paragraphText . "\n\n";
                        }
                    } else {
                        $content .= "\n"; // Parágrafo vazio para espaçamento
                    }
                    break;
                    
                case 'w:tbl': // Tabela
                    $content .= $this->processDocxTable($xpath, $child);
                    break;
                    
                case 'w:sectPr': // Propriedades de seção
                    $content .= "\n[NOVA_SEÇÃO]\n\n";
                    break;
            }
        }
        
        return $content;
    }

    /**
     * Extrair texto de um elemento preservando formatação
     */
    private function extractTextFromElement($xpath, $element)
    {
        $text = '';
        $runs = $xpath->query('.//w:r', $element);
        
        foreach ($runs as $run) {
            $runText = '';
            
            // Verificar formatação
            $bold = $xpath->query('.//w:b', $run)->length > 0;
            $italic = $xpath->query('.//w:i', $run)->length > 0;
            $underline = $xpath->query('.//w:u', $run)->length > 0;
            
            // Extrair texto
            $textNodes = $xpath->query('.//w:t', $run);
            foreach ($textNodes as $textNode) {
                $runText .= $textNode->nodeValue;
            }
            
            // Aplicar marcadores de formatação
            if ($bold) $runText = "**{$runText}**";
            if ($italic) $runText = "*{$runText}*";
            if ($underline) $runText = "_{$runText}_";
            
            $text .= $runText;
        }
        
        return trim($text);
    }

    /**
     * Processar tabela do DOCX
     */
    private function processDocxTable($xpath, $table)
    {
        $content = "\n[INÍCIO_TABELA]\n";
        $rows = $xpath->query('.//w:tr', $table);
        
        foreach ($rows as $rowIndex => $row) {
            $cells = $xpath->query('.//w:tc', $row);
            $rowText = '';
            
            foreach ($cells as $cellIndex => $cell) {
                $cellText = '';
                $cellParagraphs = $xpath->query('.//w:p', $cell);
                
                foreach ($cellParagraphs as $cellParagraph) {
                    $paragraphText = $this->extractTextFromElement($xpath, $cellParagraph);
                    if (!empty($paragraphText)) {
                        $cellText .= $paragraphText . ' ';
                    }
                }
                
                $cellText = trim($cellText);
                $rowText .= $cellText;
                
                if ($cellIndex < $cells->length - 1) {
                    $rowText .= ' | ';
                }
            }
            
            if (!empty(trim($rowText))) {
                $content .= $rowText . "\n";
            }
        }
        
        $content .= "[FIM_TABELA]\n\n";
        return $content;
    }

    /**
     * Extrair conteúdo de arquivo DOC (limitado)
     */
    private function extractDocContent($file)
    {
        try {
            // Para arquivos DOC, tentamos uma extração mais robusta
            $content = file_get_contents($file->getPathname());
            
            // Tentar diferentes encodings
            $encodings = ['UTF-8', 'Windows-1252', 'ISO-8859-1', 'CP1252'];
            $decodedContent = '';
            
            foreach ($encodings as $encoding) {
                $converted = @mb_convert_encoding($content, 'UTF-8', $encoding);
                if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
                    $decodedContent = $converted;
                    break;
                }
            }
            
            if (empty($decodedContent)) {
                $decodedContent = $content;
            }
            
            // Limpar caracteres de controle mas preservar quebras de linha
            $decodedContent = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $decodedContent);
            
            // Preservar estrutura de parágrafos
            $decodedContent = preg_replace('/\r\n|\r|\n/', "\n", $decodedContent);
            $decodedContent = preg_replace('/\n{3,}/', "\n\n", $decodedContent);
            
            // Limpar espaços extras mas preservar quebras de linha
            $lines = explode("\n", $decodedContent);
            $cleanedLines = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $cleanedLines[] = $line;
                } else {
                    $cleanedLines[] = '';
                }
            }
            
            $content = implode("\n", $cleanedLines);
            
            // Remover sequências de caracteres não imprimíveis
            $content = preg_replace('/[^\x20-\x7E\x0A\x0D\u00A0-\uFFFF]/u', '', $content);
            
            return trim($content);
            
        } catch (\Exception $e) {
            // Fallback para método básico
            $content = file_get_contents($file->getPathname());
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
            $content = preg_replace('/[^\x20-\x7E\x0A\x0D]/', '', $content);
            $content = preg_replace('/\s+/', ' ', $content);
            
            return trim($content);
        }
    }

    /**
     * Extrair conteúdo de arquivo PDF (requer biblioteca externa)
     */
    private function extractPdfContent($file)
    {
        // Implementação básica - idealmente usar uma biblioteca como smalot/pdfparser
        return 'Conteúdo PDF extraído (implementação básica)';
    }
} 