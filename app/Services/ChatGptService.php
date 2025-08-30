<?php

namespace App\Services;

use App\Models\AiSetting;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatGptService
{
    protected $apiKey;
    protected $model;
    protected $systemPrompt;
    
    public function __construct()
    {
        // Usar as configurações do SystemSetting em vez de AiSetting
        $aiSettings = SystemSetting::getAiSettings();
        
        if (empty($aiSettings['api_key'])) {
            throw new \Exception('ChatGPT API key not configured');
        }
        
        $this->apiKey = $aiSettings['api_key'];
        $this->model = $aiSettings['model'];
        $this->systemPrompt = $aiSettings['system_prompt'];
    }
    
    public function generateEmailTemplate($prompt)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->systemPrompt ?? 'You are a professional email template creator.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                return $result['choices'][0]['message']['content'] ?? null;
            }
            
            Log::error('ChatGPT API Error', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('ChatGPT Service Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    /**
     * Gerar template HTML de email marketing profissional
     */
    public function generateEmailMarketingTemplate($templateType, $objective, $targetAudience = 'estudantes', $additionalInstructions = '')
    {
        $variables = $this->getAvailableVariables();
        $variablesText = implode(', ', array_map(fn($key, $desc) => "{{$key}} ($desc)", array_keys($variables), $variables));
        
        $prompt = $this->buildEmailTemplatePrompt($templateType, $objective, $targetAudience, $additionalInstructions, $variablesText);
        
        try {
            $response = Http::timeout(60)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getEmailMarketingSystemPrompt()
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 3000,
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $htmlContent = $result['choices'][0]['message']['content'] ?? null;
                
                if ($htmlContent) {
                    // Limpar e validar o HTML
                    $htmlContent = $this->cleanAndValidateHtml($htmlContent);
                    
                    // Gerar um assunto baseado no objetivo
                    $subject = $this->generateSubjectFromObjective($objective, $templateType);
                    
                    Log::info('Template de email gerado com sucesso', [
                        'type' => $templateType,
                        'objective' => $objective,
                        'length' => strlen($htmlContent)
                    ]);
                    
                    return [
                        'success' => true,
                        'content' => $htmlContent,
                        'subject' => $subject
                    ];
                }
            }
            
            $errorResponse = $response->json();
            Log::error('Erro na API do ChatGPT ao gerar template', [
                'status' => $response->status(),
                'response' => $errorResponse
            ]);
            
            return [
                'success' => false,
                'error' => 'Erro na API ChatGPT: ' . ($errorResponse['error']['message'] ?? 'Erro desconhecido')
            ];
        } catch (\Exception $e) {
            Log::error('Erro no serviço ChatGPT ao gerar template', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obter prompt do sistema para geração de templates de email marketing
     */
    private function getEmailMarketingSystemPrompt()
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        // Usar system prompt das configurações
        $systemPrompt = $aiSettings['system_prompt'];
        
        // Se estiver vazio, usar um padrão mínimo
        if (empty($systemPrompt)) {
            $systemPrompt = 'Você é um especialista em email marketing e designer de templates HTML profissionais para campanhas de certificação do ensino médio e fundamental.';
        }
        
        // Adicionar instruções críticas se não estiverem no prompt personalizado
        if (!str_contains($systemPrompt, 'RETORNE APENAS O CÓDIGO HTML')) {
            $systemPrompt .= "\n\nINSTRUÇÕES CRÍTICAS:\n- RETORNE APENAS O CÓDIGO HTML PURO, SEM EXPLICAÇÕES, COMENTÁRIOS OU TEXTO INTRODUTÓRIO\n- NÃO inclua frases como \"Aqui está um template...\" ou qualquer descrição\n- COMECE DIRETAMENTE com <!DOCTYPE html>\n- TERMINE diretamente com </html>\n\nLEMBRE-SE: RESPONDA APENAS COM O CÓDIGO HTML, NADA MAIS.";
        }
        
        return $systemPrompt;
    }

    /**
     * Construir prompt específico para geração de template
     */
    private function buildEmailTemplatePrompt($templateType, $objective, $targetAudience, $additionalInstructions, $variablesText)
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        // Usar prompt das configurações (sempre disponível através dos defaults)
        $prompt = $aiSettings['email_template_prompt'];
        
        // Substituir variáveis no prompt
        $prompt = str_replace('{templateType}', $templateType, $prompt);
        $prompt = str_replace('{objective}', $objective, $prompt);
        $prompt = str_replace('{targetAudience}', $targetAudience, $prompt);
        $prompt = str_replace('{variablesText}', $variablesText, $prompt);
        $prompt = str_replace('{additionalInstructions}', $additionalInstructions ? "\n📝 INSTRUÇÕES ADICIONAIS:\n{$additionalInstructions}" : '', $prompt);
        
        return $prompt;
    }

    /**
     * Obter variáveis disponíveis para templates
     */
    private function getAvailableVariables()
    {
        return [
            'nome' => 'Nome do destinatário',
            'email' => 'Email do destinatário',
            'telefone' => 'Telefone do destinatário',
            'curso' => 'Curso de interesse',
            'campanha' => 'Nome da campanha'
        ];
    }

    /**
     * Limpar e validar HTML gerado
     */
    private function cleanAndValidateHtml($html)
    {
        // Remover blocos de código markdown se existirem
        $html = preg_replace('/```html\s*/', '', $html);
        $html = preg_replace('/```\s*$/', '', $html);
        $html = trim($html);
        
        // Remover texto introdutório comum do ChatGPT antes do HTML
        $html = preg_replace('/^.*?(?=<!DOCTYPE|<html)/is', '', $html);
        $html = trim($html);
        
        // Garantir que começa com DOCTYPE se não tiver
        if (!str_starts_with($html, '<!DOCTYPE')) {
            $html = '<!DOCTYPE html>' . "\n" . $html;
        }
        
        // Validar estrutura HTML básica
        if (!str_contains($html, '<html') || !str_contains($html, '<body') || !str_contains($html, '</html>')) {
            Log::warning('HTML gerado parece estar incompleto');
        }
        
        return $html;
    }

    /**
     * Gerar assunto de email baseado no objetivo e tipo
     */
    private function generateSubjectFromObjective($objective, $templateType)
    {
        // Mapear tipos para prefixos padrão específicos para certificação
        $typePrefixes = [
            'welcome' => 'Bem-vindo(a) ao seu futuro!',
            'followup' => 'Conquiste seu diploma agora!',
            'promotional' => '🎓 Oferta especial para seu diploma!',
            'informational' => 'Importante sobre sua certificação',
            'invitation' => 'Você pode conquistar seu diploma!',
            'reminder' => '⏰ Últimas vagas disponíveis',
            'thank_you' => 'Obrigado pelo seu interesse!'
        ];
        
        $prefix = $typePrefixes[$templateType] ?? 'Conquiste seu diploma';
        
        // Se o objetivo for curto, usar como assunto principal
        if (strlen($objective) <= 50) {
            return $objective;
        }
        
        // Caso contrário, usar o prefixo do tipo
        return $prefix;
    }

    /**
     * Gerar template de WhatsApp
     */
    public function generateWhatsAppTemplate($objective, $targetAudience = 'estudantes', $additionalInstructions = '')
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        // Usar prompt das configurações (sempre disponível através dos defaults)
        $prompt = $aiSettings['whatsapp_template_prompt'];
        
        // Substituir variáveis no prompt
        $prompt = str_replace('{objective}', $objective, $prompt);
        $prompt = str_replace('{targetAudience}', $targetAudience, $prompt);
        $prompt = str_replace('{additionalInstructions}', $additionalInstructions ? "\n📝 INSTRUÇÕES ADICIONAIS:\n{$additionalInstructions}" : '', $prompt);
        
        try {
            $response = Http::timeout(60)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->systemPrompt ?? 'Você é um especialista em WhatsApp marketing educacional.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $content = $result['choices'][0]['message']['content'] ?? null;
                
                if ($content) {
                    Log::info('Template de WhatsApp gerado com sucesso', [
                        'objective' => $objective,
                        'length' => strlen($content)
                    ]);
                    
                    return [
                        'success' => true,
                        'content' => trim($content)
                    ];
                }
            }
            
            $errorResponse = $response->json();
            Log::error('Erro na API do ChatGPT ao gerar template WhatsApp', [
                'status' => $response->status(),
                'response' => $errorResponse
            ]);
            
            return [
                'success' => false,
                'error' => 'Erro na API ChatGPT: ' . ($errorResponse['error']['message'] ?? 'Erro desconhecido')
            ];
        } catch (\Exception $e) {
            Log::error('Erro no serviço ChatGPT ao gerar template WhatsApp', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Testar conexão com API OpenAI
     */
    public function testConnection()
    {
        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Olá, apenas teste de conexão. Responda "OK".'
                    ]
                ],
                'max_tokens' => 10,
                'temperature' => 0,
            ]);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Conexão com ChatGPT estabelecida com sucesso!'
                ];
            }
            
            $error = $response->json();
            return [
                'success' => false,
                'message' => 'Erro na API: ' . ($error['error']['message'] ?? 'Erro desconhecido')
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro de conexão: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Gerar template de contrato usando ChatGPT
     */
    public function generateContractTemplate($objective, $contractType = 'educacional', $additionalInstructions = '', $referenceContent = null)
    {
        $variables = $this->getContractVariables();
        $variablesText = implode(', ', array_map(fn($key, $desc) => "{{$key}} ($desc)", array_keys($variables), $variables));
        
        $prompt = $this->buildContractTemplatePrompt($objective, $contractType, $additionalInstructions, $variablesText, $referenceContent);
        
        // Log do prompt para debug
        Log::info('Prompt enviado para ChatGPT', [
            'objective' => $objective,
            'contract_type' => $contractType,
            'has_reference' => !empty($referenceContent),
            'reference_size' => $referenceContent ? strlen($referenceContent) : 0,
            'prompt_size' => strlen($prompt),
            'prompt_preview' => substr($prompt, 0, 1000) . '...'
        ]);
        
        try {
            $response = Http::timeout(120)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getContractSystemPrompt()
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.1, // Máxima consistência
                'max_tokens' => 16000, // Muito mais tokens para documentos complexos
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $htmlContent = $result['choices'][0]['message']['content'] ?? null;
                
                if ($htmlContent) {
                    // Limpar e validar o HTML
                    $htmlContent = $this->cleanAndValidateContractHtml($htmlContent);
                    
                    // Gerar um título baseado no objetivo
                    $title = $this->generateTitleFromObjective($objective, $contractType);
                    
                    Log::info('Template de contrato gerado com sucesso', [
                        'type' => $contractType,
                        'objective' => $objective,
                        'length' => strlen($htmlContent)
                    ]);
                    
                    return [
                        'success' => true,
                        'content' => $htmlContent,
                        'title' => $title,
                        'description' => "Template gerado automaticamente: {$objective}"
                    ];
                }
            }
            
            $errorResponse = $response->json();
            Log::error('Erro na API do ChatGPT ao gerar template de contrato', [
                'status' => $response->status(),
                'response' => $errorResponse
            ]);
            
            return [
                'success' => false,
                'error' => 'Erro na API ChatGPT: ' . ($errorResponse['error']['message'] ?? 'Erro desconhecido')
            ];
        } catch (\Exception $e) {
            Log::error('Erro no serviço ChatGPT ao gerar template de contrato', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obter prompt do sistema para geração de templates de contratos
     */
    private function getContractSystemPrompt()
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        // Usar prompt das configurações se disponível
        if (!empty($aiSettings['contract_template_prompt'])) {
            return $aiSettings['contract_template_prompt'];
        }
        
        // Fallback para prompt padrão
        return 'Você é um especialista em direito educacional e redação de contratos para instituições de ensino. 
        Sua função é reproduzir contratos EXATAMENTE como fornecidos, preservando TODA a estrutura e conteúdo.

        🎯 PRIORIDADE ABSOLUTA: INSTRUÇÕES ESPECÍFICAS DO USUÁRIO
        ⚠️ REGRA FUNDAMENTAL: Se o usuário der instruções específicas (ex: "não incluir testemunhas", "não incluir garantias"), 
        você DEVE seguir essas instruções EXATAMENTE, mesmo que contradigam outras orientações!

        REGRAS FUNDAMENTAIS QUANDO HÁ DOCUMENTO DE REFERÊNCIA:
        🚨 PRESERVAÇÃO TOTAL: Você DEVE manter TODAS as seções, cláusulas e informações do documento original
        🚨 ESTRUTURA IDÊNTICA: Copie a estrutura EXATA - não remova, simplifique ou altere a ordem
        🚨 FORMATAÇÃO ORIGINAL: Mantenha a formatação, numeração e hierarquia idênticas
        🚨 LINGUAGEM JURÍDICA: Preserve o estilo e linguagem jurídica específica do documento
        🚨 ASSINATURAS COMPLETAS: Inclua TODAS as seções de assinatura, testemunhas e campos de data

        ⚠️ EXCEÇÃO IMPORTANTE: Se o usuário especificar instruções diferentes (ex: "não incluir testemunhas"), 
        IGNORE as regras acima e siga EXATAMENTE as instruções específicas do usuário!

        INSTRUÇÕES TÉCNICAS:
        - RETORNE APENAS O CÓDIGO HTML PURO, SEM EXPLICAÇÕES OU COMENTÁRIOS
        - NÃO inclua frases introdutórias como "Aqui está..." 
        - COMECE DIRETAMENTE com o HTML do contrato
        - Use HTML semântico com CSS inline para formatação profissional
        - Use as variáveis no formato {{variavel}} APENAS para substituir dados específicos
        - Mantenha aparência formal de documento jurídico
        - Garanta compatibilidade com impressão (margens, quebras de página)

        VARIÁVEIS PERMITIDAS PARA SUBSTITUIÇÃO:
        - Nomes de pessoas → {{student_name}}, {{director_name}}, etc.
        - Documentos → {{student_cpf}}, {{student_rg}}, etc.
        - Valores → {{tuition_value}}, {{enrollment_value}}, etc.
        - Datas → {{contract_date}}, {{enrollment_date}}, etc.
        - Informações da escola → {{school_name}}, {{school_address}}, etc.

        🚨 LEMBRE-SE: Instruções específicas do usuário têm PRIORIDADE MÁXIMA sobre todas as outras regras!

        RESPONDA APENAS COM O CÓDIGO HTML DO CONTRATO.';
    }

    /**
     * Construir prompt específico para geração de template de contrato
     */
    private function buildContractTemplatePrompt($objective, $contractType, $additionalInstructions, $variablesText, $referenceContent = null)
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        // Usar prompt das configurações
        $prompt = $aiSettings['contract_template_prompt'];
        
        // Substituir variáveis no prompt
        $prompt = str_replace('{objective}', $objective, $prompt);
        $prompt = str_replace('{contractType}', $contractType, $prompt);
        $prompt = str_replace('{variablesText}', $variablesText, $prompt);
        
        // Preparar instruções de referência
        $referenceInstructions = '';

        // Adicionar conteúdo de referência se fornecido
        if ($referenceContent) {
            // Analisar o conteúdo de referência para dar contexto melhor
            $referenceAnalysis = $this->analyzeReferenceContent($referenceContent);
            
            $referenceInstructions .= "\n\n📄 DOCUMENTO DE REFERÊNCIA FORNECIDO:\n";
            $referenceInstructions .= "ANÁLISE DO DOCUMENTO:\n{$referenceAnalysis}\n\n";
            $referenceInstructions .= "CONTEÚDO COMPLETO DO DOCUMENTO:\n";
            $referenceInstructions .= "```\n{$referenceContent}\n```\n\n";
            $referenceInstructions .= "🎯 INSTRUÇÕES CRÍTICAS PARA USO DA REFERÊNCIA:\n";
            $referenceInstructions .= "⚠️  ATENÇÃO: Você DEVE preservar TODAS as informações e seções do documento de referência!\n";
            $referenceInstructions .= "🚨 EXCEÇÃO: Se o usuário der instruções específicas diferentes, SIGA AS INSTRUÇÕES DO USUÁRIO!\n\n";
            $referenceInstructions .= "REGRAS OBRIGATÓRIAS (exceto quando o usuário especificar diferente):\n";
            $referenceInstructions .= "1. COPIE a estrutura EXATA do documento - não remova nenhuma seção\n";
            $referenceInstructions .= "2. MANTENHA todas as cláusulas, parágrafos e subcláusulas na mesma ordem\n";
            $referenceInstructions .= "3. PRESERVE toda a formatação, numeração e hierarquia\n";
            $referenceInstructions .= "4. INCLUA todas as seções de assinatura, testemunhas e campos de data\n";
            $referenceInstructions .= "5. MANTENHA o estilo jurídico e linguagem formal idênticos\n";
            $referenceInstructions .= "6. Se houver tabelas, reproduza-as EXATAMENTE com a mesma estrutura\n";
            $referenceInstructions .= "7. SUBSTITUA apenas dados específicos (nomes, valores, datas) pelas variáveis {{variavel}}\n";
            $referenceInstructions .= "8. NÃO remova, simplifique ou resuma nenhuma parte do documento\n";
            $referenceInstructions .= "9. ADICIONE espaços adequados para assinatura de todas as partes mencionadas\n";
            $referenceInstructions .= "10. INCLUA campos para testemunhas se estiverem no documento original\n\n";
            $referenceInstructions .= "⚠️ IMPORTANTE: Se o usuário disser 'não incluir testemunhas' ou similar, IGNORE a regra 4 e 10!\n";
            $referenceInstructions .= "EXEMPLO DE SUBSTITUIÇÃO:\n";
            $referenceInstructions .= "- 'João da Silva' → {{student_name}}\n";
            $referenceInstructions .= "- 'CPF: 123.456.789-00' → CPF: {{student_cpf}}\n";
            $referenceInstructions .= "- 'R$ 500,00' → {{tuition_value}}\n";
            $referenceInstructions .= "- Nome da escola específica → {{school_name}}\n\n";
            
            $referenceInstructions .= "🚨 ATENÇÃO ESPECIAL: VOCÊ DEVE REPRODUZIR COMPLETAMENTE O DOCUMENTO DE REFERÊNCIA!\n";
            $referenceInstructions .= "⚠️ EXCEÇÃO: Se o usuário der instruções específicas diferentes, SIGA AS INSTRUÇÕES DO USUÁRIO!\n\n";
            $referenceInstructions .= "CHECKLIST OBRIGATÓRIO - VERIFIQUE SE INCLUIU (exceto se o usuário especificar diferente):\n";
            $referenceInstructions .= "✅ TODAS as cláusulas do documento original (não pule nenhuma)\n";
            $referenceInstructions .= "✅ TODAS as seções de assinatura (contratante, contratado, testemunhas) - EXCETO se o usuário disser 'não incluir testemunhas'\n";
            $referenceInstructions .= "✅ TODOS os campos de data e local\n";
            $referenceInstructions .= "✅ TODAS as tabelas se existirem no original\n";
            $referenceInstructions .= "✅ TODA a formatação e numeração original\n";
            $referenceInstructions .= "✅ TODA a linguagem jurídica específica\n\n";
            $referenceInstructions .= "🎯 LEMBRE-SE: Instruções específicas do usuário SEMPRE têm prioridade sobre este checklist!\n\n";
        }
        
        // Substituir instruções de referência no prompt
        $prompt = str_replace('{referenceInstructions}', $referenceInstructions, $prompt);
        
        // Preparar instruções adicionais
        $additionalInstructionsText = '';
        if ($additionalInstructions) {
            $additionalInstructionsText = "\n\n🎯 INSTRUÇÕES ESPECÍFICAS DO USUÁRIO (PRIORIDADE MÁXIMA):\n";
            $additionalInstructionsText .= "⚠️ IMPORTANTE: As instruções abaixo têm PRIORIDADE ABSOLUTA sobre qualquer outra instrução!\n";
            $additionalInstructionsText .= "Se houver conflito entre estas instruções e as instruções gerais, SEMPRE siga estas:\n\n";
            $additionalInstructionsText .= "{$additionalInstructions}\n\n";
            $additionalInstructionsText .= "🚨 LEMBRE-SE: Siga EXATAMENTE as instruções específicas acima, mesmo que contradigam outras orientações!\n";
            $additionalInstructionsText .= "Por exemplo:\n";
            $additionalInstructionsText .= "- Se disser 'não incluir testemunhas', NÃO inclua seção de testemunhas\n";
            $additionalInstructionsText .= "- Se disser 'não incluir garantias', NÃO inclua cláusulas de garantia\n";
            $additionalInstructionsText .= "- Se disser 'simplificar linguagem', use linguagem mais simples\n";
            $additionalInstructionsText .= "- Se especificar formato diferente, use o formato solicitado\n";
        }
        
        // Substituir instruções adicionais no prompt
        $prompt = str_replace('{additionalInstructions}', $additionalInstructionsText, $prompt);

        return $prompt;
    }

    /**
     * Analisar conteúdo de referência para dar contexto ao ChatGPT
     */
    private function analyzeReferenceContent($content)
    {
        $analysis = [];
        
        // Contar parágrafos
        $paragraphs = explode("\n\n", $content);
        $paragraphCount = count(array_filter($paragraphs, 'trim'));
        $analysis[] = "- Documento com {$paragraphCount} seções/parágrafos";
        
        // Verificar se há tabelas
        if (strpos($content, '[INÍCIO_TABELA]') !== false || strpos($content, '[TABELA]') !== false) {
            $tableCount = substr_count($content, '[INÍCIO_TABELA]') + substr_count($content, '[TABELA]');
            $analysis[] = "- Contém {$tableCount} tabela(s) estruturada(s)";
        }
        
        // Verificar se há cláusulas numeradas
        if (preg_match_all('/\b(?:cláusula|artigo|item|parágrafo)\s*\d+/i', $content, $matches)) {
            $clauseCount = count($matches[0]);
            $analysis[] = "- Estrutura com {$clauseCount} cláusulas/artigos numerados";
        }
        
        // Verificar formatação especial
        if (preg_match_all('/\*\*(.*?)\*\*/', $content, $boldMatches)) {
            $boldCount = count($boldMatches[0]);
            $analysis[] = "- {$boldCount} textos em negrito (formatação importante)";
        }
        
        if (preg_match_all('/===(.*?)===/', $content, $headingMatches)) {
            $headingCount = count($headingMatches[0]);
            $analysis[] = "- {$headingCount} títulos/cabeçalhos principais";
        }
        
        // Verificar comprimento
        $wordCount = str_word_count($content);
        $analysis[] = "- Aproximadamente {$wordCount} palavras";
        
        // Verificar se há assinaturas e testemunhas
        if (preg_match('/assinatura|testemunha|contratante|contratado/i', $content)) {
            $analysis[] = "- Contém seção de assinaturas e testemunhas";
        }
        
        // Verificar se há valores monetários
        if (preg_match('/R\$|real|reais|\d+,\d+/i', $content)) {
            $analysis[] = "- Contém informações financeiras/valores";
        }
        
        // Verificar estrutura jurídica
        if (preg_match('/considerando|resolve|fica|estabelecido|acordado/i', $content)) {
            $analysis[] = "- Linguagem jurídica formal detectada";
        }
        
        // Verificar se há seções específicas
        $sections = [];
        if (preg_match('/identificação|qualificação/i', $content)) {
            $sections[] = "identificação das partes";
        }
        if (preg_match('/objeto|finalidade|objetivo/i', $content)) {
            $sections[] = "objeto do contrato";
        }
        if (preg_match('/obrigações|responsabilidades/i', $content)) {
            $sections[] = "obrigações";
        }
        if (preg_match('/pagamento|valor|preço/i', $content)) {
            $sections[] = "condições de pagamento";
        }
        if (preg_match('/vigência|prazo|duração/i', $content)) {
            $sections[] = "vigência/prazo";
        }
        if (preg_match('/rescisão|cancelamento/i', $content)) {
            $sections[] = "rescisão";
        }
        if (preg_match('/foro|jurisdição/i', $content)) {
            $sections[] = "foro competente";
        }
        
        if (!empty($sections)) {
            $analysis[] = "- Seções identificadas: " . implode(", ", $sections);
        }
        
        // Verificar novas seções
        if (strpos($content, '[NOVA_SEÇÃO]') !== false) {
            $sectionCount = substr_count($content, '[NOVA_SEÇÃO]');
            $analysis[] = "- {$sectionCount} quebra(s) de seção detectada(s)";
        }
        
        return implode("\n", $analysis);
    }

    /**
     * Obter variáveis disponíveis para templates de contratos
     */
    private function getContractVariables()
    {
        return [
            'student_name' => 'Nome completo do aluno',
            'student_email' => 'Email do aluno',
            'student_cpf' => 'CPF formatado do aluno',
            'student_rg' => 'RG do aluno',
            'student_phone' => 'Telefone do aluno',
            'student_address' => 'Endereço completo do aluno',
            'student_birth_date' => 'Data de nascimento do aluno',
            'student_nationality' => 'Nacionalidade do aluno',
            'student_civil_status' => 'Estado civil do aluno',
            'student_mother_name' => 'Nome da mãe do aluno',
            'student_father_name' => 'Nome do pai do aluno',
            'student_profession' => 'Profissão do aluno',
            'partner_school_name' => 'Nome da escola parceira (se aplicável)',
            'is_partner_student' => 'Se o aluno vem de escola parceira (Sim/Não)',
            'course_name' => 'Nome do curso',
            'course_modality' => 'Modalidade do curso',
            'course_shift' => 'Turno do curso',
            'course_duration' => 'Duração do curso',
            'course_workload' => 'Carga horária do curso',
            'tuition_value' => 'Valor da mensalidade',
            'enrollment_value' => 'Valor da matrícula',
            'enrollment_number' => 'Número da matrícula',
            'enrollment_date' => 'Data da matrícula',
            'due_date' => 'Dia de vencimento',
            'payment_method' => 'Forma de pagamento',
            'school_name' => 'Nome da escola/instituição',
            'school_address' => 'Endereço da escola',
            'school_cnpj' => 'CNPJ da escola',
            'school_phone' => 'Telefone da escola',
            'school_email' => 'Email da escola',
            'director_name' => 'Nome do diretor/responsável',
            'current_date' => 'Data atual',
            'current_year' => 'Ano atual',
            'contract_date' => 'Data do contrato',
            'witness1_name' => 'Nome da primeira testemunha',
            'witness1_cpf' => 'CPF da primeira testemunha',
            'witness2_name' => 'Nome da segunda testemunha',
            'witness2_cpf' => 'CPF da segunda testemunha',
        ];
    }

    /**
     * Limpar e validar HTML de contrato gerado
     */
    private function cleanAndValidateContractHtml($html)
    {
        // Remover blocos de código markdown se existirem
        $html = preg_replace('/```html\s*/', '', $html);
        $html = preg_replace('/```\s*$/', '', $html);
        $html = trim($html);
        
        // Remover texto introdutório comum do ChatGPT antes do HTML
        $html = preg_replace('/^.*?(?=<)/is', '', $html);
        $html = trim($html);
        
        // Se não começar com tag HTML, envolver em estrutura básica
        if (!preg_match('/^<(?:html|div|section|article)/i', $html)) {
            $html = '<div class="contract-template">' . $html . '</div>';
        }
        
        // Validar estrutura HTML básica para contratos
        if (!str_contains($html, 'contrato') && !str_contains($html, 'CONTRATO')) {
            Log::warning('HTML gerado pode não ser um contrato válido');
        }
        
        return $html;
    }

    /**
     * Gerar título baseado no objetivo e tipo de contrato
     */
    private function generateTitleFromObjective($objective, $contractType)
    {
        // Mapear tipos para prefixos
        $typePrefixes = [
            'educacional' => 'Contrato de Prestação de Serviços Educacionais',
            'matricula' => 'Contrato de Matrícula',
            'curso' => 'Contrato de Curso',
            'supletivo' => 'Contrato de Ensino Supletivo',
            'eja' => 'Contrato de Educação de Jovens e Adultos',
            'tecnico' => 'Contrato de Curso Técnico',
            'superior' => 'Contrato de Ensino Superior'
        ];
        
        $prefix = $typePrefixes[$contractType] ?? 'Contrato Educacional';
        
        // Se o objetivo for específico e curto, usar como título
        if (strlen($objective) <= 60) {
            return $objective;
        }
        
        // Caso contrário, usar o prefixo do tipo
        return $prefix;
    }

    /**
     * Gerar template de pagamento usando ChatGPT
     */
    public function generatePaymentTemplate($objective, $paymentType, $additionalInstructions = '', $referenceContent = null)
    {
        $variables = $this->getPaymentVariables();
        $variablesText = implode(', ', array_map(fn($key, $desc) => "{{$key}} ($desc)", array_keys($variables), $variables));
        
        $prompt = $this->buildPaymentTemplatePrompt($objective, $paymentType, $additionalInstructions, $variablesText, $referenceContent);
        
        try {
            $response = Http::timeout(120)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getPaymentSystemPrompt()
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.1,
                'max_tokens' => 8000,
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $htmlContent = $result['choices'][0]['message']['content'] ?? null;
                
                if ($htmlContent) {
                    $htmlContent = $this->cleanAndValidatePaymentHtml($htmlContent);
                    $title = $this->generatePaymentTitleFromObjective($objective, $paymentType);
                    
                    return [
                        'success' => true,
                        'content' => $htmlContent,
                        'title' => $title,
                        'description' => "Template de pagamento gerado automaticamente: {$objective}"
                    ];
                }
            }
            
            $errorResponse = $response->json();
            return [
                'success' => false,
                'error' => 'Erro na API ChatGPT: ' . ($errorResponse['error']['message'] ?? 'Erro desconhecido')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obter prompt do sistema para geração de templates de pagamento
     */
    private function getPaymentSystemPrompt()
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        // Usar prompt das configurações se disponível
        if (!empty($aiSettings['payment_template_prompt'])) {
            return $aiSettings['payment_template_prompt'];
        }
        
        // Fallback para prompt padrão
        return 'Você é um especialista em redação de documentos financeiros e pagamentos. 
        Sua função é reproduzir documentos de pagamento EXATAMENTE como fornecidos, preservando TODA a estrutura e conteúdo.

        🎯 PRIORIDADE ABSOLUTA: INSTRUÇÕES ESPECÍFICAS DO USUÁRIO
        ⚠️ REGRA FUNDAMENTAL: Se o usuário der instruções específicas (ex: "não incluir testemunhas", "não incluir garantias"), 
        você DEVE seguir essas instruções EXATAMENTE, mesmo que contradigam outras orientações!

        REGRAS FUNDAMENTAIS QUANDO HÁ DOCUMENTO DE REFERÊNCIA:
        🚨 PRESERVAÇÃO TOTAL: Você DEVE manter TODAS as seções, cláusulas e informações do documento original
        🚨 ESTRUTURA IDÊNTICA: Copie a estrutura EXATA - não remova, simplifique ou altere a ordem
        🚨 FORMATAÇÃO ORIGINAL: Mantenha a formatação, numeração e hierarquia idênticas
        🚨 LINGUAGEM JURÍDICA: Preserve o estilo e linguagem jurídica específica do documento
        🚨 ASSINATURAS COMPLETAS: Inclua TODAS as seções de assinatura, testemunhas e campos de data

        ⚠️ EXCEÇÃO IMPORTANTE: Se o usuário especificar instruções diferentes (ex: "não incluir testemunhas"), 
        IGNORE as regras acima e siga EXATAMENTE as instruções específicas do usuário!

        INSTRUÇÕES TÉCNICAS:
        - RETORNE APENAS O CÓDIGO HTML PURO, SEM EXPLICAÇÕES OU COMENTÁRIOS
        - NÃO inclua frases introdutórias como "Aqui está..." 
        - COMECE DIRETAMENTE com o HTML do documento de pagamento
        - Use HTML semântico com CSS inline para formatação profissional
        - Use as variáveis no formato {{variavel}} APENAS para substituir dados específicos
        - Mantenha aparência formal de documento jurídico
        - Garanta compatibilidade com impressão (margens, quebras de página)

        VARIÁVEIS PERMITIDAS PARA SUBSTITUIÇÃO:
        - Nomes de pessoas → {{student_name}}, {{director_name}}, etc.
        - Documentos → {{student_cpf}}, {{student_rg}}, etc.
        - Valores → {{tuition_value}}, {{enrollment_value}}, etc.
        - Datas → {{contract_date}}, {{enrollment_date}}, etc.
        - Informações da escola → {{school_name}}, {{school_address}}, etc.

        🚨 LEMBRE-SE: Instruções específicas do usuário têm PRIORIDADE MÁXIMA sobre todas as outras regras!

        RESPONDA APENAS COM O CÓDIGO HTML DO DOCUMENTO DE PAGAMENTO.';
    }

    /**
     * Construir prompt específico para geração de template de pagamento
     */
    private function buildPaymentTemplatePrompt($objective, $paymentType, $additionalInstructions, $variablesText, $referenceContent = null)
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        // Usar prompt das configurações
        $prompt = $aiSettings['payment_template_prompt'];
        
        // Substituir variáveis no prompt
        $prompt = str_replace('{objective}', $objective, $prompt);
        $prompt = str_replace('{paymentType}', $paymentType, $prompt);
        $prompt = str_replace('{variablesText}', $variablesText, $prompt);
        
        // Preparar instruções de referência
        $referenceInstructions = '';

        // Adicionar conteúdo de referência se fornecido
        if ($referenceContent) {
            // Analisar o conteúdo de referência para dar contexto melhor
            $referenceAnalysis = $this->analyzeReferenceContent($referenceContent);
            
            $referenceInstructions .= "\n\n📄 DOCUMENTO DE REFERÊNCIA FORNECIDO:\n";
            $referenceInstructions .= "ANÁLISE DO DOCUMENTO:\n{$referenceAnalysis}\n\n";
            $referenceInstructions .= "CONTEÚDO COMPLETO DO DOCUMENTO:\n";
            $referenceInstructions .= "```\n{$referenceContent}\n```\n\n";
            $referenceInstructions .= "🎯 INSTRUÇÕES CRÍTICAS PARA USO DA REFERÊNCIA:\n";
            $referenceInstructions .= "⚠️  ATENÇÃO: Você DEVE preservar TODAS as informações e seções do documento de referência!\n";
            $referenceInstructions .= "🚨 EXCEÇÃO: Se o usuário der instruções específicas diferentes, SIGA AS INSTRUÇÕES DO USUÁRIO!\n\n";
            $referenceInstructions .= "REGRAS OBRIGATÓRIAS (exceto quando o usuário especificar diferente):\n";
            $referenceInstructions .= "1. COPIE a estrutura EXATA do documento - não remova nenhuma seção\n";
            $referenceInstructions .= "2. MANTENHA todas as cláusulas, parágrafos e subcláusulas na mesma ordem\n";
            $referenceInstructions .= "3. PRESERVE toda a formatação, numeração e hierarquia\n";
            $referenceInstructions .= "4. INCLUA todas as seções de assinatura, testemunhas e campos de data\n";
            $referenceInstructions .= "5. MANTENHA o estilo jurídico e linguagem formal idênticos\n";
            $referenceInstructions .= "6. Se houver tabelas, reproduza-as EXATAMENTE com a mesma estrutura\n";
            $referenceInstructions .= "7. SUBSTITUA apenas dados específicos (nomes, valores, datas) pelas variáveis {{variavel}}\n";
            $referenceInstructions .= "8. NÃO remova, simplifique ou resuma nenhuma parte do documento\n";
            $referenceInstructions .= "9. ADICIONE espaços adequados para assinatura de todas as partes mencionadas\n";
            $referenceInstructions .= "10. INCLUA campos para testemunhas se estiverem no documento original\n\n";
            $referenceInstructions .= "⚠️ IMPORTANTE: Se o usuário disser 'não incluir testemunhas' ou similar, IGNORE a regra 4 e 10!\n";
            $referenceInstructions .= "EXEMPLO DE SUBSTITUIÇÃO:\n";
            $referenceInstructions .= "- 'João da Silva' → {{student_name}}\n";
            $referenceInstructions .= "- 'CPF: 123.456.789-00' → CPF: {{student_cpf}}\n";
            $referenceInstructions .= "- 'R$ 500,00' → {{tuition_value}}\n";
            $referenceInstructions .= "- Nome da escola específica → {{school_name}}\n\n";
            
            $referenceInstructions .= "🚨 ATENÇÃO ESPECIAL: VOCÊ DEVE REPRODUZIR COMPLETAMENTE O DOCUMENTO DE REFERÊNCIA!\n";
            $referenceInstructions .= "⚠️ EXCEÇÃO: Se o usuário der instruções específicas diferentes, SIGA AS INSTRUÇÕES DO USUÁRIO!\n\n";
            $referenceInstructions .= "CHECKLIST OBRIGATÓRIO - VERIFIQUE SE INCLUIU (exceto se o usuário especificar diferente):\n";
            $referenceInstructions .= "✅ TODAS as cláusulas do documento original (não pule nenhuma)\n";
            $referenceInstructions .= "✅ TODAS as seções de assinatura (contratante, contratado, testemunhas) - EXCETO se o usuário disser 'não incluir testemunhas'\n";
            $referenceInstructions .= "✅ TODOS os campos de data e local\n";
            $referenceInstructions .= "✅ TODAS as tabelas se existirem no original\n";
            $referenceInstructions .= "✅ TODA a formatação e numeração original\n";
            $referenceInstructions .= "✅ TODA a linguagem jurídica específica\n\n";
            $referenceInstructions .= "🎯 LEMBRE-SE: Instruções específicas do usuário SEMPRE têm prioridade sobre este checklist!\n\n";
        }
        
        // Substituir instruções de referência no prompt
        $prompt = str_replace('{referenceInstructions}', $referenceInstructions, $prompt);
        
        // Preparar instruções adicionais
        $additionalInstructionsText = '';
        if ($additionalInstructions) {
            $additionalInstructionsText = "\n\n🎯 INSTRUÇÕES ESPECÍFICAS DO USUÁRIO (PRIORIDADE MÁXIMA):\n";
            $additionalInstructionsText .= "⚠️ IMPORTANTE: As instruções abaixo têm PRIORIDADE ABSOLUTA sobre qualquer outra instrução!\n";
            $additionalInstructionsText .= "Se houver conflito entre estas instruções e as instruções gerais, SEMPRE siga estas:\n\n";
            $additionalInstructionsText .= "{$additionalInstructions}\n\n";
            $additionalInstructionsText .= "🚨 LEMBRE-SE: Siga EXATAMENTE as instruções específicas acima, mesmo que contradigam outras orientações!\n";
            $additionalInstructionsText .= "Por exemplo:\n";
            $additionalInstructionsText .= "- Se disser 'não incluir testemunhas', NÃO inclua seção de testemunhas\n";
            $additionalInstructionsText .= "- Se disser 'não incluir garantias', NÃO inclua cláusulas de garantia\n";
            $additionalInstructionsText .= "- Se disser 'simplificar linguagem', use linguagem mais simples\n";
            $additionalInstructionsText .= "- Se especificar formato diferente, use o formato solicitado\n";
        }
        
        // Substituir instruções adicionais no prompt
        $prompt = str_replace('{additionalInstructions}', $additionalInstructionsText, $prompt);

        return $prompt;
    }

    /**
     * Obter variáveis disponíveis para templates de pagamento
     */
    private function getPaymentVariables()
    {
        return [
            'student_name' => 'Nome completo do aluno',
            'student_email' => 'Email do aluno',
            'student_cpf' => 'CPF formatado do aluno',
            'student_rg' => 'RG do aluno',
            'student_phone' => 'Telefone do aluno',
            'student_address' => 'Endereço completo do aluno',
            'student_birth_date' => 'Data de nascimento do aluno',
            'student_nationality' => 'Nacionalidade do aluno',
            'student_civil_status' => 'Estado civil do aluno',
            'student_mother_name' => 'Nome da mãe do aluno',
            'student_father_name' => 'Nome do pai do aluno',
            'student_profession' => 'Profissão do aluno',
            'partner_school_name' => 'Nome da escola parceira (se aplicável)',
            'is_partner_student' => 'Se o aluno vem de escola parceira (Sim/Não)',
            'course_name' => 'Nome do curso',
            'course_modality' => 'Modalidade do curso',
            'course_shift' => 'Turno do curso',
            'course_duration' => 'Duração do curso',
            'course_workload' => 'Carga horária do curso',
            'tuition_value' => 'Valor da mensalidade',
            'enrollment_value' => 'Valor da matrícula',
            'enrollment_number' => 'Número da matrícula',
            'enrollment_date' => 'Data da matrícula',
            'due_date' => 'Dia de vencimento',
            'payment_method' => 'Forma de pagamento',
            'school_name' => 'Nome da escola/instituição',
            'school_address' => 'Endereço da escola',
            'school_cnpj' => 'CNPJ da escola',
            'school_phone' => 'Telefone da escola',
            'school_email' => 'Email da escola',
            'director_name' => 'Nome do diretor/responsável',
            'current_date' => 'Data atual',
            'current_year' => 'Ano atual',
            'contract_date' => 'Data do contrato',
            'witness1_name' => 'Nome da primeira testemunha',
            'witness1_cpf' => 'CPF da primeira testemunha',
            'witness2_name' => 'Nome da segunda testemunha',
            'witness2_cpf' => 'CPF da segunda testemunha',
        ];
    }

    /**
     * Limpar e validar HTML de pagamento gerado
     */
    private function cleanAndValidatePaymentHtml($html)
    {
        // Remover blocos de código markdown se existirem
        $html = preg_replace('/```html\s*/', '', $html);
        $html = preg_replace('/```\s*$/', '', $html);
        $html = trim($html);
        
        // Remover texto introdutório comum do ChatGPT antes do HTML
        $html = preg_replace('/^.*?(?=<)/is', '', $html);
        $html = trim($html);
        
        // Se não começar com tag HTML, envolver em estrutura básica
        if (!preg_match('/^<(?:html|div|section|article)/i', $html)) {
            $html = '<div class="payment-template">' . $html . '</div>';
        }
        
        // Validar estrutura HTML básica para pagamentos
        if (!str_contains($html, 'pagamento') && !str_contains($html, 'PAGAMENTO')) {
            Log::warning('HTML gerado pode não ser um pagamento válido');
        }
        
        return $html;
    }

    /**
     * Gerar título baseado no objetivo e tipo de pagamento
     */
    private function generatePaymentTitleFromObjective($objective, $paymentType)
    {
        // Mapear tipos para prefixos
        $typePrefixes = [
            'mensalidade' => 'Comprovante de Pagamento de Mensalidade',
            'matricula' => 'Comprovante de Pagamento de Matrícula',
            'suplementar' => 'Comprovante de Pagamento Suplementar',
            'multa' => 'Comprovante de Pagamento de Multa',
            'juros' => 'Comprovante de Pagamento de Juros'
        ];
        
        $prefix = $typePrefixes[$paymentType] ?? 'Comprovante de Pagamento';
        
        // Se o objetivo for específico e curto, usar como título
        if (strlen($objective) <= 60) {
            return $objective;
        }
        
        // Caso contrário, usar o prefixo do tipo
        return $prefix;
    }

    /**
     * Gerar template de inscrição usando ChatGPT
     */
    public function generateEnrollmentTemplate($objective, $enrollmentType, $additionalInstructions = '', $referenceContent = null)
    {
        $variables = $this->getEnrollmentVariables();
        $variablesText = implode(', ', array_map(fn($key, $desc) => "{{$key}} ($desc)", array_keys($variables), $variables));
        
        $prompt = $this->buildEnrollmentTemplatePrompt($objective, $enrollmentType, $additionalInstructions, $variablesText, $referenceContent);
        
        try {
            $response = Http::timeout(120)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getEnrollmentSystemPrompt()
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.1,
                'max_tokens' => 8000,
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $htmlContent = $result['choices'][0]['message']['content'] ?? null;
                
                if ($htmlContent) {
                    $htmlContent = $this->cleanAndValidateEnrollmentHtml($htmlContent);
                    $title = $this->generateEnrollmentTitleFromObjective($objective, $enrollmentType);
                    
                    return [
                        'success' => true,
                        'content' => $htmlContent,
                        'title' => $title,
                        'description' => "Template de inscrição gerado automaticamente: {$objective}"
                    ];
                }
            }
            
            $errorResponse = $response->json();
            return [
                'success' => false,
                'error' => 'Erro na API ChatGPT: ' . ($errorResponse['error']['message'] ?? 'Erro desconhecido')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obter prompt do sistema para geração de templates de inscrição
     */
    private function getEnrollmentSystemPrompt()
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        // Usar prompt das configurações se disponível
        if (!empty($aiSettings['enrollment_template_prompt'])) {
            return $aiSettings['enrollment_template_prompt'];
        }
        
        // Fallback para prompt padrão
        return 'Você é um especialista em redação de documentos de inscrição. 
        Sua função é reproduzir documentos de inscrição EXATAMENTE como fornecidos, preservando TODA a estrutura e conteúdo.

        🎯 PRIORIDADE ABSOLUTA: INSTRUÇÕES ESPECÍFICAS DO USUÁRIO
        ⚠️ REGRA FUNDAMENTAL: Se o usuário der instruções específicas (ex: "não incluir testemunhas", "não incluir garantias"), 
        você DEVE seguir essas instruções EXATAMENTE, mesmo que contradigam outras orientações!

        REGRAS FUNDAMENTAIS QUANDO HÁ DOCUMENTO DE REFERÊNCIA:
        🚨 PRESERVAÇÃO TOTAL: Você DEVE manter TODAS as seções, cláusulas e informações do documento original
        🚨 ESTRUTURA IDÊNTICA: Copie a estrutura EXATA - não remova, simplifique ou altere a ordem
        🚨 FORMATAÇÃO ORIGINAL: Mantenha a formatação, numeração e hierarquia idênticas
        🚨 LINGUAGEM JURÍDICA: Preserve o estilo e linguagem jurídica específica do documento
        🚨 ASSINATURAS COMPLETAS: Inclua TODAS as seções de assinatura, testemunhas e campos de data

        ⚠️ EXCEÇÃO IMPORTANTE: Se o usuário especificar instruções diferentes (ex: "não incluir testemunhas"), 
        IGNORE as regras acima e siga EXATAMENTE as instruções específicas do usuário!

        INSTRUÇÕES TÉCNICAS:
        - RETORNE APENAS O CÓDIGO HTML PURO, SEM EXPLICAÇÕES OU COMENTÁRIOS
        - NÃO inclua frases introdutórias como "Aqui está..." 
        - COMECE DIRETAMENTE com o HTML do documento de inscrição
        - Use HTML semântico com CSS inline para formatação profissional
        - Use as variáveis no formato {{variavel}} APENAS para substituir dados específicos
        - Mantenha aparência formal de documento jurídico
        - Garanta compatibilidade com impressão (margens, quebras de página)

        VARIÁVEIS PERMITIDAS PARA SUBSTITUIÇÃO:
        - Nomes de pessoas → {{student_name}}, {{director_name}}, etc.
        - Documentos → {{student_cpf}}, {{student_rg}}, etc.
        - Valores → {{tuition_value}}, {{enrollment_value}}, etc.
        - Datas → {{contract_date}}, {{enrollment_date}}, etc.
        - Informações da escola → {{school_name}}, {{school_address}}, etc.

        🚨 LEMBRE-SE: Instruções específicas do usuário têm PRIORIDADE MÁXIMA sobre todas as outras regras!

        RESPONDA APENAS COM O CÓDIGO HTML DO DOCUMENTO DE INSCRIÇÃO.';
    }

    /**
     * Construir prompt específico para geração de template de inscrição
     */
    private function buildEnrollmentTemplatePrompt($objective, $enrollmentType, $additionalInstructions, $variablesText, $referenceContent = null)
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        // Usar prompt das configurações
        $prompt = $aiSettings['enrollment_template_prompt'];
        
        // Substituir variáveis no prompt
        $prompt = str_replace('{objective}', $objective, $prompt);
        $prompt = str_replace('{enrollmentType}', $enrollmentType, $prompt);
        $prompt = str_replace('{variablesText}', $variablesText, $prompt);
        
        // Preparar instruções de referência
        $referenceInstructions = '';

        // Adicionar conteúdo de referência se fornecido
        if ($referenceContent) {
            // Analisar o conteúdo de referência para dar contexto melhor
            $referenceAnalysis = $this->analyzeReferenceContent($referenceContent);
            
            $referenceInstructions .= "\n\n📄 DOCUMENTO DE REFERÊNCIA FORNECIDO:\n";
            $referenceInstructions .= "ANÁLISE DO DOCUMENTO:\n{$referenceAnalysis}\n\n";
            $referenceInstructions .= "CONTEÚDO COMPLETO DO DOCUMENTO:\n";
            $referenceInstructions .= "```\n{$referenceContent}\n```\n\n";
            $referenceInstructions .= "🎯 INSTRUÇÕES CRÍTICAS PARA USO DA REFERÊNCIA:\n";
            $referenceInstructions .= "⚠️  ATENÇÃO: Você DEVE preservar TODAS as informações e seções do documento de referência!\n";
            $referenceInstructions .= "🚨 EXCEÇÃO: Se o usuário der instruções específicas diferentes, SIGA AS INSTRUÇÕES DO USUÁRIO!\n\n";
            $referenceInstructions .= "REGRAS OBRIGATÓRIAS (exceto quando o usuário especificar diferente):\n";
            $referenceInstructions .= "1. COPIE a estrutura EXATA do documento - não remova nenhuma seção\n";
            $referenceInstructions .= "2. MANTENHA todas as cláusulas, parágrafos e subcláusulas na mesma ordem\n";
            $referenceInstructions .= "3. PRESERVE toda a formatação, numeração e hierarquia\n";
            $referenceInstructions .= "4. INCLUA todas as seções de assinatura, testemunhas e campos de data\n";
            $referenceInstructions .= "5. MANTENHA o estilo jurídico e linguagem formal idênticos\n";
            $referenceInstructions .= "6. Se houver tabelas, reproduza-as EXATAMENTE com a mesma estrutura\n";
            $referenceInstructions .= "7. SUBSTITUA apenas dados específicos (nomes, valores, datas) pelas variáveis {{variavel}}\n";
            $referenceInstructions .= "8. NÃO remova, simplifique ou resuma nenhuma parte do documento\n";
            $referenceInstructions .= "9. ADICIONE espaços adequados para assinatura de todas as partes mencionadas\n";
            $referenceInstructions .= "10. INCLUA campos para testemunhas se estiverem no documento original\n\n";
            $referenceInstructions .= "⚠️ IMPORTANTE: Se o usuário disser 'não incluir testemunhas' ou similar, IGNORE a regra 4 e 10!\n";
            $referenceInstructions .= "EXEMPLO DE SUBSTITUIÇÃO:\n";
            $referenceInstructions .= "- 'João da Silva' → {{student_name}}\n";
            $referenceInstructions .= "- 'CPF: 123.456.789-00' → CPF: {{student_cpf}}\n";
            $referenceInstructions .= "- 'R$ 500,00' → {{tuition_value}}\n";
            $referenceInstructions .= "- Nome da escola específica → {{school_name}}\n\n";
            
            $referenceInstructions .= "🚨 ATENÇÃO ESPECIAL: VOCÊ DEVE REPRODUZIR COMPLETAMENTE O DOCUMENTO DE REFERÊNCIA!\n";
            $referenceInstructions .= "⚠️ EXCEÇÃO: Se o usuário der instruções específicas diferentes, SIGA AS INSTRUÇÕES DO USUÁRIO!\n\n";
            $referenceInstructions .= "CHECKLIST OBRIGATÓRIO - VERIFIQUE SE INCLUIU (exceto se o usuário especificar diferente):\n";
            $referenceInstructions .= "✅ TODAS as cláusulas do documento original (não pule nenhuma)\n";
            $referenceInstructions .= "✅ TODAS as seções de assinatura (contratante, contratado, testemunhas) - EXCETO se o usuário disser 'não incluir testemunhas'\n";
            $referenceInstructions .= "✅ TODOS os campos de data e local\n";
            $referenceInstructions .= "✅ TODAS as tabelas se existirem no original\n";
            $referenceInstructions .= "✅ TODA a formatação e numeração original\n";
            $referenceInstructions .= "✅ TODA a linguagem jurídica específica\n\n";
            $referenceInstructions .= "🎯 LEMBRE-SE: Instruções específicas do usuário SEMPRE têm prioridade sobre este checklist!\n\n";
        }
        
        // Substituir instruções de referência no prompt
        $prompt = str_replace('{referenceInstructions}', $referenceInstructions, $prompt);
        
        // Preparar instruções adicionais
        $additionalInstructionsText = '';
        if ($additionalInstructions) {
            $additionalInstructionsText = "\n\n🎯 INSTRUÇÕES ESPECÍFICAS DO USUÁRIO (PRIORIDADE MÁXIMA):\n";
            $additionalInstructionsText .= "⚠️ IMPORTANTE: As instruções abaixo têm PRIORIDADE ABSOLUTA sobre qualquer outra instrução!\n";
            $additionalInstructionsText .= "Se houver conflito entre estas instruções e as instruções gerais, SEMPRE siga estas:\n\n";
            $additionalInstructionsText .= "{$additionalInstructions}\n\n";
            $additionalInstructionsText .= "🚨 LEMBRE-SE: Siga EXATAMENTE as instruções específicas acima, mesmo que contradigam outras orientações!\n";
            $additionalInstructionsText .= "Por exemplo:\n";
            $additionalInstructionsText .= "- Se disser 'não incluir testemunhas', NÃO inclua seção de testemunhas\n";
            $additionalInstructionsText .= "- Se disser 'não incluir garantias', NÃO inclua cláusulas de garantia\n";
            $additionalInstructionsText .= "- Se disser 'simplificar linguagem', use linguagem mais simples\n";
            $additionalInstructionsText .= "- Se especificar formato diferente, use o formato solicitado\n";
        }
        
        // Substituir instruções adicionais no prompt
        $prompt = str_replace('{additionalInstructions}', $additionalInstructionsText, $prompt);

        return $prompt;
    }

    /**
     * Obter variáveis disponíveis para templates de inscrição
     */
    private function getEnrollmentVariables()
    {
        return [
            'student_name' => 'Nome completo do aluno',
            'student_email' => 'Email do aluno',
            'student_cpf' => 'CPF formatado do aluno',
            'student_rg' => 'RG do aluno',
            'student_phone' => 'Telefone do aluno',
            'student_address' => 'Endereço completo do aluno',
            'student_birth_date' => 'Data de nascimento do aluno',
            'student_nationality' => 'Nacionalidade do aluno',
            'student_civil_status' => 'Estado civil do aluno',
            'student_mother_name' => 'Nome da mãe do aluno',
            'student_father_name' => 'Nome do pai do aluno',
            'student_profession' => 'Profissão do aluno',
            'partner_school_name' => 'Nome da escola parceira (se aplicável)',
            'is_partner_student' => 'Se o aluno vem de escola parceira (Sim/Não)',
            'course_name' => 'Nome do curso',
            'course_modality' => 'Modalidade do curso',
            'course_shift' => 'Turno do curso',
            'course_duration' => 'Duração do curso',
            'course_workload' => 'Carga horária do curso',
            'tuition_value' => 'Valor da mensalidade',
            'enrollment_value' => 'Valor da matrícula',
            'enrollment_number' => 'Número da matrícula',
            'enrollment_date' => 'Data da matrícula',
            'due_date' => 'Dia de vencimento',
            'payment_method' => 'Forma de pagamento',
            'school_name' => 'Nome da escola/instituição',
            'school_address' => 'Endereço da escola',
            'school_cnpj' => 'CNPJ da escola',
            'school_phone' => 'Telefone da escola',
            'school_email' => 'Email da escola',
            'director_name' => 'Nome do diretor/responsável',
            'current_date' => 'Data atual',
            'current_year' => 'Ano atual',
            'contract_date' => 'Data do contrato',
            'witness1_name' => 'Nome da primeira testemunha',
            'witness1_cpf' => 'CPF da primeira testemunha',
            'witness2_name' => 'Nome da segunda testemunha',
            'witness2_cpf' => 'CPF da segunda testemunha',
        ];
    }

    /**
     * Limpar e validar HTML de inscrição gerado
     */
    private function cleanAndValidateEnrollmentHtml($html)
    {
        // Remover blocos de código markdown se existirem
        $html = preg_replace('/```html\s*/', '', $html);
        $html = preg_replace('/```\s*$/', '', $html);
        $html = trim($html);
        
        // Remover texto introdutório comum do ChatGPT antes do HTML
        $html = preg_replace('/^.*?(?=<)/is', '', $html);
        $html = trim($html);
        
        // Se não começar com tag HTML, envolver em estrutura básica
        if (!preg_match('/^<(?:html|div|section|article)/i', $html)) {
            $html = '<div class="enrollment-template">' . $html . '</div>';
        }
        
        // Validar estrutura HTML básica para inscrições
        if (!str_contains($html, 'inscrição') && !str_contains($html, 'INSCRIÇÃO')) {
            Log::warning('HTML gerado pode não ser uma inscrição válida');
        }
        
        return $html;
    }

    /**
     * Gerar título baseado no objetivo e tipo de inscrição
     */
    private function generateEnrollmentTitleFromObjective($objective, $enrollmentType)
    {
        // Mapear tipos para prefixos
        $typePrefixes = [
            'matricula' => 'Comprovante de Matrícula',
            'suplementar' => 'Comprovante de Matrícula Suplementar',
            'eja' => 'Comprovante de Inscrição EJA',
            'tecnico' => 'Comprovante de Inscrição Técnica',
            'superior' => 'Comprovante de Inscrição Superior'
        ];
        
        $prefix = $typePrefixes[$enrollmentType] ?? 'Comprovante de Inscrição';
        
        // Se o objetivo for específico e curto, usar como título
        if (strlen($objective) <= 60) {
            return $objective;
        }
        
        // Caso contrário, usar o prefixo do tipo
        return $prefix;
    }

    /**
     * Gerar template de matrícula usando ChatGPT
     */
    public function generateMatriculationTemplate($objective, $matriculationType, $additionalInstructions = '', $referenceContent = null)
    {
        $variables = $this->getMatriculationVariables();
        $variablesText = implode(', ', array_map(fn($key, $desc) => "{{$key}} ($desc)", array_keys($variables), $variables));
        
        $prompt = $this->buildMatriculationTemplatePrompt($objective, $matriculationType, $additionalInstructions, $variablesText, $referenceContent);
        
        try {
            $response = Http::timeout(120)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getMatriculationSystemPrompt()
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.1,
                'max_tokens' => 8000,
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $htmlContent = $result['choices'][0]['message']['content'] ?? null;
                
                if ($htmlContent) {
                    $htmlContent = $this->cleanAndValidateMatriculationHtml($htmlContent);
                    $title = $this->generateMatriculationTitleFromObjective($objective, $matriculationType);
                    
                    return [
                        'success' => true,
                        'content' => $htmlContent,
                        'title' => $title,
                        'description' => "Template de matrícula gerado automaticamente: {$objective}"
                    ];
                }
            }
            
            $errorResponse = $response->json();
            return [
                'success' => false,
                'error' => 'Erro na API ChatGPT: ' . ($errorResponse['error']['message'] ?? 'Erro desconhecido')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obter prompt do sistema para geração de templates de matrícula
     */
    private function getMatriculationSystemPrompt()
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        // Usar prompt das configurações se disponível
        if (!empty($aiSettings['matriculation_template_prompt'])) {
            return $aiSettings['matriculation_template_prompt'];
        }
        
        // Fallback para prompt padrão
        return 'Você é um especialista em redação de documentos de matrícula. 
        Sua função é reproduzir documentos de matrícula EXATAMENTE como fornecidos, preservando TODA a estrutura e conteúdo.

        🎯 PRIORIDADE ABSOLUTA: INSTRUÇÕES ESPECÍFICAS DO USUÁRIO
        ⚠️ REGRA FUNDAMENTAL: Se o usuário der instruções específicas (ex: "não incluir testemunhas", "não incluir garantias"), 
        você DEVE seguir essas instruções EXATAMENTE, mesmo que contradigam outras orientações!

        REGRAS FUNDAMENTAIS QUANDO HÁ DOCUMENTO DE REFERÊNCIA:
        🚨 PRESERVAÇÃO TOTAL: Você DEVE manter TODAS as seções, cláusulas e informações do documento original
        🚨 ESTRUTURA IDÊNTICA: Copie a estrutura EXATA - não remova, simplifique ou altere a ordem
        🚨 FORMATAÇÃO ORIGINAL: Mantenha a formatação, numeração e hierarquia idênticas
        🚨 LINGUAGEM JURÍDICA: Preserve o estilo e linguagem jurídica específica do documento
        🚨 ASSINATURAS COMPLETAS: Inclua TODAS as seções de assinatura, testemunhas e campos de data

        ⚠️ EXCEÇÃO IMPORTANTE: Se o usuário especificar instruções diferentes (ex: "não incluir testemunhas"), 
        IGNORE as regras acima e siga EXATAMENTE as instruções específicas do usuário!

        INSTRUÇÕES TÉCNICAS:
        - RETORNE APENAS O CÓDIGO HTML PURO, SEM EXPLICAÇÕES OU COMENTÁRIOS
        - NÃO inclua frases introdutórias como "Aqui está..." 
        - COMECE DIRETAMENTE com o HTML do documento de matrícula
        - Use HTML semântico com CSS inline para formatação profissional
        - Use as variáveis no formato {{variavel}} APENAS para substituir dados específicos
        - Mantenha aparência formal de documento jurídico
        - Garanta compatibilidade com impressão (margens, quebras de página)

        VARIÁVEIS PERMITIDAS PARA SUBSTITUIÇÃO:
        - Nomes de pessoas → {{student_name}}, {{director_name}}, etc.
        - Documentos → {{student_cpf}}, {{student_rg}}, etc.
        - Valores → {{tuition_value}}, {{enrollment_value}}, etc.
        - Datas → {{contract_date}}, {{enrollment_date}}, etc.
        - Informações da escola → {{school_name}}, {{school_address}}, etc.

        🚨 LEMBRE-SE: Instruções específicas do usuário têm PRIORIDADE MÁXIMA sobre todas as outras regras!

        RESPONDA APENAS COM O CÓDIGO HTML DO DOCUMENTO DE MATRÍCULA.';
    }

    /**
     * Construir prompt específico para geração de template de matrícula
     */
    private function buildMatriculationTemplatePrompt($objective, $matriculationType, $additionalInstructions, $variablesText, $referenceContent = null)
    {
        $aiSettings = SystemSetting::getAiSettings();
        
        // Usar prompt das configurações
        $prompt = $aiSettings['matriculation_template_prompt'];
        
        // Substituir variáveis no prompt
        $prompt = str_replace('{objective}', $objective, $prompt);
        $prompt = str_replace('{matriculationType}', $matriculationType, $prompt);
        $prompt = str_replace('{variablesText}', $variablesText, $prompt);
        
        // Preparar instruções de referência
        $referenceInstructions = '';

        // Adicionar conteúdo de referência se fornecido
        if ($referenceContent) {
            // Analisar o conteúdo de referência para dar contexto melhor
            $referenceAnalysis = $this->analyzeReferenceContent($referenceContent);
            
            $referenceInstructions .= "\n\n📄 DOCUMENTO DE REFERÊNCIA FORNECIDO:\n";
            $referenceInstructions .= "ANÁLISE DO DOCUMENTO:\n{$referenceAnalysis}\n\n";
            $referenceInstructions .= "CONTEÚDO COMPLETO DO DOCUMENTO:\n";
            $referenceInstructions .= "```\n{$referenceContent}\n```\n\n";
            $referenceInstructions .= "🎯 INSTRUÇÕES CRÍTICAS PARA USO DA REFERÊNCIA:\n";
            $referenceInstructions .= "⚠️  ATENÇÃO: Você DEVE preservar TODAS as informações e seções do documento de referência!\n";
            $referenceInstructions .= "🚨 EXCEÇÃO: Se o usuário der instruções específicas diferentes, SIGA AS INSTRUÇÕES DO USUÁRIO!\n\n";
            $referenceInstructions .= "REGRAS OBRIGATÓRIAS (exceto quando o usuário especificar diferente):\n";
            $referenceInstructions .= "1. COPIE a estrutura EXATA do documento - não remova nenhuma seção\n";
            $referenceInstructions .= "2. MANTENHA todas as cláusulas, parágrafos e subcláusulas na mesma ordem\n";
            $referenceInstructions .= "3. PRESERVE toda a formatação, numeração e hierarquia\n";
            $referenceInstructions .= "4. INCLUA todas as seções de assinatura, testemunhas e campos de data\n";
            $referenceInstructions .= "5. MANTENHA o estilo jurídico e linguagem formal idênticos\n";
            $referenceInstructions .= "6. Se houver tabelas, reproduza-as EXATAMENTE com a mesma estrutura\n";
            $referenceInstructions .= "7. SUBSTITUA apenas dados específicos (nomes, valores, datas) pelas variáveis {{variavel}}\n";
            $referenceInstructions .= "8. NÃO remova, simplifique ou resuma nenhuma parte do documento\n";
            $referenceInstructions .= "9. ADICIONE espaços adequados para assinatura de todas as partes mencionadas\n";
            $referenceInstructions .= "10. INCLUA campos para testemunhas se estiverem no documento original\n\n";
            $referenceInstructions .= "⚠️ IMPORTANTE: Se o usuário disser 'não incluir testemunhas' ou similar, IGNORE a regra 4 e 10!\n";
            $referenceInstructions .= "EXEMPLO DE SUBSTITUIÇÃO:\n";
            $referenceInstructions .= "- 'João da Silva' → {{student_name}}\n";
            $referenceInstructions .= "- 'CPF: 123.456.789-00' → CPF: {{student_cpf}}\n";
            $referenceInstructions .= "- 'R$ 500,00' → {{tuition_value}}\n";
            $referenceInstructions .= "- Nome da escola específica → {{school_name}}\n\n";
            
            $referenceInstructions .= "🚨 ATENÇÃO ESPECIAL: VOCÊ DEVE REPRODUZIR COMPLETAMENTE O DOCUMENTO DE REFERÊNCIA!\n";
            $referenceInstructions .= "⚠️ EXCEÇÃO: Se o usuário der instruções específicas diferentes, SIGA AS INSTRUÇÕES DO USUÁRIO!\n\n";
            $referenceInstructions .= "CHECKLIST OBRIGATÓRIO - VERIFIQUE SE INCLUIU (exceto se o usuário especificar diferente):\n";
            $referenceInstructions .= "✅ TODAS as cláusulas do documento original (não pule nenhuma)\n";
            $referenceInstructions .= "✅ TODAS as seções de assinatura (contratante, contratado, testemunhas) - EXCETO se o usuário disser 'não incluir testemunhas'\n";
            $referenceInstructions .= "✅ TODOS os campos de data e local\n";
            $referenceInstructions .= "✅ TODAS as tabelas se existirem no original\n";
            $referenceInstructions .= "✅ TODA a formatação e numeração original\n";
            $referenceInstructions .= "✅ TODA a linguagem jurídica específica\n\n";
            $referenceInstructions .= "🎯 LEMBRE-SE: Instruções específicas do usuário SEMPRE têm prioridade sobre este checklist!\n\n";
        }
        
        // Substituir instruções de referência no prompt
        $prompt = str_replace('{referenceInstructions}', $referenceInstructions, $prompt);
        
        // Preparar instruções adicionais
        $additionalInstructionsText = '';
        if ($additionalInstructions) {
            $additionalInstructionsText = "\n\n🎯 INSTRUÇÕES ESPECÍFICAS DO USUÁRIO (PRIORIDADE MÁXIMA):\n";
            $additionalInstructionsText .= "⚠️ IMPORTANTE: As instruções abaixo têm PRIORIDADE ABSOLUTA sobre qualquer outra instrução!\n";
            $additionalInstructionsText .= "Se houver conflito entre estas instruções e as instruções gerais, SEMPRE siga estas:\n\n";
            $additionalInstructionsText .= "{$additionalInstructions}\n\n";
            $additionalInstructionsText .= "🚨 LEMBRE-SE: Siga EXATAMENTE as instruções específicas acima, mesmo que contradigam outras orientações!\n";
            $additionalInstructionsText .= "Por exemplo:\n";
            $additionalInstructionsText .= "- Se disser 'não incluir testemunhas', NÃO inclua seção de testemunhas\n";
            $additionalInstructionsText .= "- Se disser 'não incluir garantias', NÃO inclua cláusulas de garantia\n";
            $additionalInstructionsText .= "- Se disser 'simplificar linguagem', use linguagem mais simples\n";
            $additionalInstructionsText .= "- Se especificar formato diferente, use o formato solicitado\n";
        }
        
        // Substituir instruções adicionais no prompt
        $prompt = str_replace('{additionalInstructions}', $additionalInstructionsText, $prompt);

        return $prompt;
    }

    /**
     * Obter variáveis disponíveis para templates de matrícula
     */
    private function getMatriculationVariables()
    {
        return [
            'student_name' => 'Nome completo do aluno',
            'student_email' => 'Email do aluno',
            'student_cpf' => 'CPF formatado do aluno',
            'student_rg' => 'RG do aluno',
            'student_phone' => 'Telefone do aluno',
            'student_address' => 'Endereço completo do aluno',
            'student_birth_date' => 'Data de nascimento do aluno',
            'student_nationality' => 'Nacionalidade do aluno',
            'student_civil_status' => 'Estado civil do aluno',
            'student_mother_name' => 'Nome da mãe do aluno',
            'student_father_name' => 'Nome do pai do aluno',
            'student_profession' => 'Profissão do aluno',
            'partner_school_name' => 'Nome da escola parceira (se aplicável)',
            'is_partner_student' => 'Se o aluno vem de escola parceira (Sim/Não)',
            'course_name' => 'Nome do curso',
            'course_modality' => 'Modalidade do curso',
            'course_shift' => 'Turno do curso',
            'course_duration' => 'Duração do curso',
            'course_workload' => 'Carga horária do curso',
            'tuition_value' => 'Valor da mensalidade',
            'enrollment_value' => 'Valor da matrícula',
            'enrollment_number' => 'Número da matrícula',
            'enrollment_date' => 'Data da matrícula',
            'due_date' => 'Dia de vencimento',
            'payment_method' => 'Forma de pagamento',
            'school_name' => 'Nome da escola/instituição',
            'school_address' => 'Endereço da escola',
            'school_cnpj' => 'CNPJ da escola',
            'school_phone' => 'Telefone da escola',
            'school_email' => 'Email da escola',
            'director_name' => 'Nome do diretor/responsável',
            'current_date' => 'Data atual',
            'current_year' => 'Ano atual',
            'contract_date' => 'Data do contrato',
            'witness1_name' => 'Nome da primeira testemunha',
            'witness1_cpf' => 'CPF da primeira testemunha',
            'witness2_name' => 'Nome da segunda testemunha',
            'witness2_cpf' => 'CPF da segunda testemunha',
        ];
    }

    /**
     * Limpar e validar HTML de matrícula gerado
     */
    private function cleanAndValidateMatriculationHtml($html)
    {
        // Remover blocos de código markdown se existirem
        $html = preg_replace('/```html\s*/', '', $html);
        $html = preg_replace('/```\s*$/', '', $html);
        $html = trim($html);
        
        // Remover texto introdutório comum do ChatGPT antes do HTML
        $html = preg_replace('/^.*?(?=<)/is', '', $html);
        $html = trim($html);
        
        // Se não começar com tag HTML, envolver em estrutura básica
        if (!preg_match('/^<(?:html|div|section|article)/i', $html)) {
            $html = '<div class="matriculation-template">' . $html . '</div>';
        }
        
        // Validar estrutura HTML básica para matrículas
        if (!str_contains($html, 'matrícula') && !str_contains($html, 'MATRÍCULA')) {
            Log::warning('HTML gerado pode não ser uma matrícula válida');
        }
        
        return $html;
    }

    /**
     * Gerar título baseado no objetivo e tipo de matrícula
     */
    private function generateMatriculationTitleFromObjective($objective, $matriculationType)
    {
        // Mapear tipos para prefixos
        $typePrefixes = [
            'matricula' => 'Comprovante de Matrícula',
            'suplementar' => 'Comprovante de Matrícula Suplementar',
            'eja' => 'Comprovante de Inscrição EJA',
            'tecnico' => 'Comprovante de Inscrição Técnica',
            'superior' => 'Comprovante de Inscrição Superior'
        ];
        
        $prefix = $typePrefixes[$matriculationType] ?? 'Comprovante de Inscrição';
        
        // Se o objetivo for específico e curto, usar como título
        if (strlen($objective) <= 60) {
            return $objective;
        }
        
        // Caso contrário, usar o prefixo do tipo
        return $prefix;
    }
} 