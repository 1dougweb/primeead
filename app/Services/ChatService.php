<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Matricula;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Services\BoletoSecondViaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatService
{
    protected $chatGptService;
    protected $boletoService;
    protected $supportPrompt;
    protected $apiKey;
    protected $model;

    public function __construct(ChatGptService $chatGptService, BoletoSecondViaService $boletoService)
    {
        $this->chatGptService = $chatGptService;
        $this->boletoService = $boletoService;
        
        // Obter configurações do sistema
        $aiSettings = SystemSetting::getAiSettings();
        $this->apiKey = $aiSettings['api_key'];
        $this->model = $aiSettings['model'];
        $this->supportPrompt = SystemSetting::get('ai_support_prompt', $this->getDefaultSupportPrompt());
    }

    /**
     * Processar mensagem do usuário e gerar resposta
     */
    public function processMessage(string $sessionId, string $userMessage, ?string $userEmail = null, ?string $userName = null): array
    {
        try {
            // Obter ou criar conversa
            $conversation = $this->getOrCreateConversation($sessionId, $userEmail, $userName);
            
            // Salvar mensagem do usuário
            $userMessageModel = $this->saveMessage($conversation->id, 'user', $userMessage);
            
            // Verificar cache para respostas rápidas
            $cachedResponse = $this->getCachedResponse($userMessage, $userEmail);
            if ($cachedResponse) {
                return [
                    'success' => true,
                    'response' => $cachedResponse,
                    'conversation_id' => $conversation->id,
                    'message_id' => $userMessageModel->id,
                    'tokens_used' => 0,
                    'response_time_ms' => 0,
                    'second_via_results' => [],
                    'cached' => true
                ];
            }
            
            // Gerar contexto otimizado baseado na mensagem do usuário
            $context = $this->generateOptimizedContext($userMessage, $userEmail);
            
            // Gerar resposta do ChatGPT
            $startTime = microtime(true);
            $assistantResponse = $this->generateChatResponse($conversation, $userMessage, $context);
            $responseTime = round((microtime(true) - $startTime) * 1000);
            
            $finalResponse = $assistantResponse['content'];
            
            // Cache da resposta para uso futuro
            $this->cacheResponse($userMessage, $userEmail, $finalResponse);
            
            // Salvar resposta final do assistente
            $assistantMessageModel = $this->saveMessage(
                $conversation->id, 
                'assistant', 
                $finalResponse,
                $assistantResponse['tokens_used'] ?? null, // tokens_used
                $responseTime
            );
            
            return [
                'success' => true,
                'response' => $finalResponse,
                'conversation_id' => $conversation->id,
                'message_id' => $assistantMessageModel->id,
                'tokens_used' => $assistantResponse['tokens_used'] ?? null,
                'response_time_ms' => $responseTime,
                'second_via_results' => []
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar mensagem do chat', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Desculpe, ocorreu um erro ao processar sua mensagem. Tente novamente em alguns instantes.',
                'conversation_id' => null
            ];
        }
    }

    /**
     * Verificar cache para respostas rápidas
     */
    protected function getCachedResponse(string $userMessage, ?string $userEmail): ?string
    {
        $cacheKey = $this->generateCacheKey($userMessage, $userEmail);
        
        // Cache em memória para respostas instantâneas
        if (isset($this->memoryCache[$cacheKey])) {
            return $this->memoryCache[$cacheKey];
        }
        
        // Cache no banco para respostas persistentes
        $cached = \Cache::get($cacheKey);
        if ($cached) {
            $this->memoryCache[$cacheKey] = $cached;
            return $cached;
        }
        
        return null;
    }
    
    /**
     * Cache de resposta para uso futuro
     */
    protected function cacheResponse(string $userMessage, ?string $userEmail, string $response): void
    {
        $cacheKey = $this->generateCacheKey($userMessage, $userEmail);
        
        // Cache em memória (instantâneo)
        $this->memoryCache[$cacheKey] = $response;
        
        // Cache no banco (persistente, 1 hora)
        \Cache::put($cacheKey, $response, now()->addHour());
    }
    
    /**
     * Gerar chave de cache única
     */
    protected function generateCacheKey(string $userMessage, ?string $userEmail): string
    {
        $normalizedMessage = strtolower(trim($userMessage));
        $email = $userEmail ? strtolower(trim($userEmail)) : 'anonymous';
        
        return 'chat_response_' . md5($normalizedMessage . '_' . $email);
    }
    
    /**
     * Cache em memória para respostas instantâneas
     */
    protected $memoryCache = [];
    
    /**
     * Obter ou criar uma nova conversa
     */
    protected function getOrCreateConversation(string $sessionId, ?string $userEmail, ?string $userName): ChatConversation
    {
        // Verificar se é um ID de sessão local (não sincronizado com servidor)
        if (str_starts_with($sessionId, 'local_')) {
            // Para sessões locais, criar uma conversa temporária
            return ChatConversation::create([
                'session_id' => $sessionId,
                'user_email' => $userEmail,
                'user_name' => $userName,
                'status' => 'active',
                'metadata' => [
                    'created_at' => now()->toISOString(),
                    'user_agent' => request()->userAgent(),
                    'ip_address' => request()->ip(),
                    'is_local_session' => true
                ]
            ]);
        }
        
        // Para sessões do servidor, buscar ou criar
        $conversation = ChatConversation::where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();
            
        if (!$conversation) {
            $conversation = ChatConversation::create([
                'session_id' => $sessionId,
                'user_email' => $userEmail,
                'user_name' => $userName,
                'status' => 'active',
                'metadata' => [
                    'created_at' => now()->toISOString(),
                    'user_agent' => request()->userAgent(),
                    'ip_address' => request()->ip(),
                    'is_local_session' => false
                ]
            ]);
        }
        
        return $conversation;
    }

    /**
     * Salvar mensagem no banco
     */
    protected function saveMessage(int $conversationId, string $role, string $content, ?int $tokensUsed = null, ?int $responseTime = null): ChatMessage
    {
        return ChatMessage::create([
            'conversation_id' => $conversationId,
            'role' => $role,
            'content' => $content,
            'tokens_used' => $tokensUsed,
            'response_time_ms' => $responseTime,
            'metadata' => [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Gerar contexto otimizado baseado na mensagem do usuário
     */
    protected function generateOptimizedContext(string $userMessage, ?string $userEmail): array
    {
        $messageLower = strtolower($userMessage);
        
        // Contexto mínimo para respostas rápidas
        $context = [
            'user_message' => $userMessage,
            'user_email' => $userEmail,
            'timestamp' => now()->toISOString(),
            'system_info' => [
                'platform' => 'EJA Supletivo',
                'version' => '1.0'
            ]
        ];
        
        // SEMPRE incluir dados da matrícula se houver email
        if ($userEmail) {
            $enrollmentData = $this->getEnrollmentContext($userEmail);
            $paymentData = $this->getPaymentContext($userEmail);
            
            $context['enrollment_data'] = $enrollmentData;
            $context['payment_data'] = $paymentData;
            
            // Log para debug
            \Log::info('Contexto gerado para ChatGPT', [
                'email' => $userEmail,
                'enrollment_data' => $enrollmentData,
                'payment_data' => $paymentData
            ]);
        }
        
        return $context;
    }
    
    /**
     * Gerar contexto baseado na mensagem do usuário (método original mantido para compatibilidade)
     */
    protected function generateContext(string $userMessage, ?string $userEmail): array
    {
        $context = [];
        
        // Se o usuário forneceu email, buscar informações relacionadas
        if ($userEmail) {
            $context['matriculas'] = $this->getMatriculasByEmail($userEmail);
            $context['payments'] = $this->getPaymentsByEmail($userEmail);
        }
        
        // Buscar informações gerais sobre o sistema
        $context['system_info'] = $this->getSystemInfo();
        
        return $context;
    }

    /**
     * Obter matrículas por email
     */
    protected function getMatriculasByEmail(string $email): array
    {
        $matriculas = Matricula::where('email', $email)
            ->with(['payments' => function($query) {
                $query->orderBy('data_vencimento', 'desc');
            }])
            ->get();
            
        return $matriculas->map(function($matricula) {
            return [
                'id' => $matricula->id,
                'nome_completo' => $matricula->nome_completo,
                'curso' => $matricula->curso,
                'status' => $matricula->status,
                'data_matricula' => $matricula->data_matricula?->format('d/m/Y'),
                'payments_count' => $matricula->payments->count(),
                'payments_pending' => $matricula->payments->where('status', 'pending')->count(),
                'payments_overdue' => $matricula->payments->where('data_vencimento', '<', now())->where('status', 'pending')->count()
            ];
        })->toArray();
    }

    /**
     * Obter contexto de pagamentos otimizado
     */
    protected function getPaymentContext(?string $userEmail): array
    {
        if (!$userEmail) {
            return ['message' => 'Email não fornecido'];
        }
        
        try {
            $payments = Payment::with('matricula')
                ->whereHas('matricula', function($query) use ($userEmail) {
                    $query->where('email', $userEmail);
                })
                ->where('status', 'pending')
                ->get()
                ->take(5); // Aumentar para 5 pagamentos
            
            $overduePayments = $payments->where('data_vencimento', '<', now());
            
            return [
                'count' => $payments->count(),
                'overdue_count' => $overduePayments->count(),
                'total_amount' => $payments->sum('valor'),
                'has_payment_links' => $payments->whereNotNull('payment_link')->count() > 0,
                'payments' => $payments->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'valor' => $payment->valor,
                        'descricao' => $payment->descricao,
                        'data_vencimento' => $payment->data_vencimento->format('d/m/Y'),
                        'is_overdue' => $payment->data_vencimento < now(),
                        'payment_link' => $payment->payment_link
                    ];
                })->toArray()
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro ao buscar pagamentos'];
        }
    }
    
    /**
     * Obter contexto de matrícula otimizado
     */
    protected function getEnrollmentContext(?string $userEmail): array
    {
        if (!$userEmail) {
            return ['message' => 'Email não fornecido'];
        }
        
        try {
            $matricula = Matricula::where('email', $userEmail)->first();
            
            if (!$matricula) {
                return ['message' => 'Matrícula não encontrada'];
            }
            
            return [
                'id' => $matricula->id,
                'nome_completo' => $matricula->nome_completo,
                'status' => $matricula->status,
                'curso' => $matricula->curso ?? 'Não informado',
                'data_matricula' => $matricula->created_at?->format('d/m/Y'),
                'active' => $matricula->status === 'active',
                'cpf' => $matricula->cpf ?? 'Não informado',
                'telefone' => $matricula->telefone ?? 'Não informado'
            ];
        } catch (\Exception $e) {
            return ['error' => 'Erro ao buscar matrícula'];
        }
    }
    
    /**
     * Obter pagamentos por email
     */
    protected function getPaymentsByEmail(string $email): array
    {
        $payments = Payment::whereHas('matricula', function($query) use ($email) {
            $query->where('email', $email);
        })->with('matricula')
        ->orderBy('data_vencimento', 'desc')
        ->get();
        
        return $payments->map(function($payment) {
            $isOverdue = $payment->data_vencimento < now() && $payment->status === 'pending';
            $paymentLink = $this->generatePaymentLink($payment);
            
            return [
                'id' => $payment->id,
                'valor' => 'R$ ' . number_format($payment->valor, 2, ',', '.'),
                'data_vencimento' => $payment->data_vencimento->format('d/m/Y'),
                'data_vencimento_iso' => $payment->data_vencimento->toISOString(), // Formato ISO para Carbon
                'status' => $payment->status,
                'descricao' => $payment->descricao,
                'forma_pagamento' => $payment->forma_pagamento,
                'matricula_curso' => $payment->matricula->curso ?? 'N/A',
                'is_overdue' => $isOverdue,
                'payment_link' => $paymentLink,
                'days_overdue' => $isOverdue ? now()->diffInDays($payment->data_vencimento) : 0
            ];
        })->toArray();
    }

    /**
     * Gerar link de pagamento para um pagamento específico
     */
    protected function generatePaymentLink($payment): ?string
    {
        try {
            // Verificar se o pagamento está vencido e pendente
            if ($payment->data_vencimento >= now() || $payment->status !== 'pending') {
                return null;
            }
            
            // Gerar link de pagamento baseado na forma de pagamento
            switch ($payment->forma_pagamento) {
                case 'boleto':
                    // Link direto do Mercado Pago para boleto
                    return $this->generateMercadoPagoBoletoLink($payment);
                    
                case 'pix':
                    // Link direto do Mercado Pago para PIX
                    return $this->generateMercadoPagoPixLink($payment);
                    
                case 'cartao':
                    // Link direto do Mercado Pago para cartão
                    return $this->generateMercadoPagoCardLink($payment);
                    
                default:
                    // Link genérico do Mercado Pago
                    return $this->generateMercadoPagoGenericLink($payment);
            }
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Gerar link do Mercado Pago para boleto
     */
    protected function generateMercadoPagoBoletoLink($payment): string
    {
        try {
            // Chamar a API para gerar o link real do Mercado Pago
            $response = Http::get(config('app.url') . '/api/mercadopago/payment-link', [
                'payment_id' => $payment->id,
                'email' => $payment->matricula->email
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['payment_link'] ?? 'Link não disponível';
            }
            
            throw new \Exception('Erro ao gerar link do Mercado Pago');
            
        } catch (\Exception $e) {
            // SEMPRE retornar link do Mercado Pago, nunca fallback para plataforma
            return config('app.url') . '/api/mercadopago/payment-link?payment_id=' . $payment->id . '&email=' . urlencode($payment->matricula->email);
        }
    }
    
    /**
     * Gerar link do Mercado Pago para PIX
     */
    protected function generateMercadoPagoPixLink($payment): string
    {
        try {
            // Chamar a API para gerar o link real do Mercado Pago
            $response = Http::get(config('app.url') . '/api/mercadopago/payment-link', [
                'payment_id' => $payment->id,
                'email' => $payment->matricula->email
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['payment_link'] ?? 'Link não disponível';
            }
            
            throw new \Exception('Erro ao gerar link do Mercado Pago');
            
        } catch (\Exception $e) {
            // SEMPRE retornar link do Mercado Pago, nunca fallback para plataforma
            return config('app.url') . '/api/mercadopago/payment-link?payment_id=' . $payment->id . '&email=' . urlencode($payment->matricula->email);
        }
    }
    
    /**
     * Gerar link do Mercado Pago para cartão
     */
    protected function generateMercadoPagoCardLink($payment): string
    {
        try {
            // Chamar a API para gerar o link real do Mercado Pago
            $response = Http::get(config('app.url') . '/api/mercadopago/payment-link', [
                'payment_id' => $payment->id,
                'email' => $payment->matricula->email
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['payment_link'] ?? 'Link não disponível';
            }
            
            throw new \Exception('Erro ao gerar link do Mercado Pago');
            
        } catch (\Exception $e) {
            // SEMPRE retornar link do Mercado Pago, nunca fallback para plataforma
            return config('app.url') . '/api/mercadopago/payment-link?payment_id=' . $payment->id . '&email=' . urlencode($payment->matricula->email);
        }
    }
    
    /**
     * Gerar link genérico do Mercado Pago
     */
    protected function generateMercadoPagoGenericLink($payment): string
    {
        try {
            // Chamar a API para gerar o link real do Mercado Pago
            $response = Http::get(config('app.url') . '/api/mercadopago/payment-link', [
                'payment_id' => $payment->id,
                'email' => $payment->matricula->email
            ]);
            
            if ($response->json()) {
                $data = $response->json();
                return $data['payment_link'] ?? 'Link não disponível';
            }
            
            throw new \Exception('Erro ao gerar link do Mercado Pago');
            
        } catch (\Exception $e) {
            // SEMPRE retornar link do Mercado Pago, nunca fallback para plataforma
            return config('app.url') . '/api/mercadopago/payment-link?payment_id=' . $payment->id . '&email=' . urlencode($payment->matricula->email);
        }
    }

    /**
     * Obter informações gerais do sistema
     */
    protected function getSystemInfo(): array
    {
        return [
            'company_name' => 'Ensino Certo',
            'support_email' => SystemSetting::get('mail_from_address', 'contato@ensinocerto.com.br'),
            'support_phone' => SystemSetting::get('thank_you_contact_phone', ''),
            'business_hours' => SystemSetting::get('thank_you_contact_hours', 'Segunda a Sexta, 8h às 18h'),
            'website' => config('app.url'),
            'mec_authorization' => SystemSetting::get('landing_mec_authorization_file', ''),
            'mec_address' => SystemSetting::get('landing_mec_address', '')
        ];
    }

    /**
     * Gerar resposta do ChatGPT
     */
    protected function generateChatResponse(ChatConversation $conversation, string $userMessage, array $context): array
    {
        // Construir mensagens para o ChatGPT
        $messages = [
            [
                'role' => 'system',
                'content' => $this->buildSystemPrompt($context)
            ]
        ];
        
        // Adicionar histórico da conversa (últimas 15 mensagens para contexto)
        $recentMessages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->reverse();
            
        foreach ($recentMessages as $message) {
            $messages[] = [
                'role' => $message->role,
                'content' => $message->content
            ];
        }
        
        // Adicionar mensagem atual do usuário
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];
        
        try {
            $response = Http::timeout(60)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                $content = $result['choices'][0]['message']['content'] ?? '';
                $usage = $result['usage'] ?? [];
                
                return [
                    'content' => $content,
                    'tokens_used' => $usage['total_tokens'] ?? null
                ];
            }
            
            throw new \Exception('Erro na API do ChatGPT: ' . $response->status());
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar resposta do ChatGPT', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversation->id
            ]);
            
            // Retornar resposta padrão em caso de erro
            return [
                'content' => $this->getFallbackResponse($userMessage, $context),
                'tokens_used' => null
            ];
        }
    }

    /**
     * Construir prompt do sistema para o ChatGPT
     */
    protected function buildSystemPrompt(array $context): string
    {
        $basePrompt = $this->supportPrompt;
        
        // Adicionar contexto específico se disponível
        if (!empty($context['enrollment_data'])) {
            $enrollment = $context['enrollment_data'];
            if (!isset($enrollment['error'])) {
                $basePrompt .= "\n\nINFORMAÇÕES DO ALUNO EJA:\n";
                $basePrompt .= "- Nome: {$enrollment['nome_completo']}\n";
                $basePrompt .= "- Curso: {$enrollment['curso']}\n";
                $basePrompt .= "- Status: {$enrollment['status']}\n";
                $basePrompt .= "- Data de Matrícula: {$enrollment['data_matricula']}\n";
                $basePrompt .= "- CPF: {$enrollment['cpf']}\n";
                $basePrompt .= "- Telefone: {$enrollment['telefone']}\n";
            }
        }
        
        if (!empty($context['payment_data'])) {
            $paymentData = $context['payment_data'];
            if (!isset($paymentData['error'])) {
                $basePrompt .= "\n\nSITUAÇÃO FINANCEIRA:\n";
                $basePrompt .= "- Total de pagamentos pendentes: {$paymentData['count']}\n";
                $basePrompt .= "- Pagamentos vencidos: {$paymentData['overdue_count']}\n";
                $basePrompt .= "- Valor total pendente: R$ " . number_format($paymentData['total_amount'], 2, ',', '.') . "\n";
                
                if (!empty($paymentData['payments'])) {
                    $basePrompt .= "\nDETALHES DOS PAGAMENTOS:\n";
                    foreach ($paymentData['payments'] as $payment) {
                        $statusIcon = $payment['is_overdue'] ? '🔴' : '🟠';
                        $overdueInfo = $payment['is_overdue'] ? " (VENCIDO)" : "";
                        $basePrompt .= "- {$payment['descricao']}: R$ " . number_format($payment['valor'], 2, ',', '.') . " (Vence: {$payment['data_vencimento']}) {$statusIcon}{$overdueInfo}\n";
                    }
                }
            }
        }
        
        $basePrompt .= "\n\nINFORMAÇÕES DA INSTITUIÇÃO:\n";
        $basePrompt .= "- Nome: Ensino Certo\n";
        $basePrompt .= "- Email de suporte: contato@ensinocerto.com.br\n";
        $basePrompt .= "- Telefone: (11) 91701-2033\n";
        $basePrompt .= "- Horário de atendimento: Segunda a Sexta, 8h às 18h\n";
        $basePrompt .= "- Website: www.ensinocerto.com.br\n";
        
        if (!empty($context['system_info']['mec_authorization'])) {
            $basePrompt .= "- Autorização MEC: {$context['system_info']['mec_authorization']}\n";
        }
        
        if (!empty($context['system_info']['mec_address'])) {
            $basePrompt .= "- Endereço MEC: {$context['system_info']['mec_address']}\n";
        }
        
        // Adicionar resultados de comandos de segunda via se disponíveis
        if (!empty($context['second_via_results'])) {
            $basePrompt .= "\n\nRESULTADOS DE COMANDOS EXECUTADOS:\n";
            foreach ($context['second_via_results'] as $result) {
                if ($result['success']) {
                    $basePrompt .= "✅ {$result['command']['type']} executado com sucesso\n";
                    if (isset($result['result']['message'])) {
                        $basePrompt .= "   Mensagem: {$result['result']['message']}\n";
                    }
                    if (isset($result['result']['boleto_via'])) {
                        $basePrompt .= "   Nova via: {$result['result']['boleto_via']->via_number_formatted}\n";
                        $basePrompt .= "   URL: {$result['result']['boleto_via']->boleto_url}\n";
                    }
                } else {
                    $basePrompt .= "❌ {$result['command']['type']} falhou: {$result['error']}\n";
                }
            }
        }
        
        $basePrompt .= "\n\nINSTRUÇÕES ESPECÍFICAS PARA EJA SUPLETIVO:\n";
        $basePrompt .= "1. SEMPRE verifique os dados reais da plataforma antes de responder\n";
        $basePrompt .= "2. Use as informações do aluno para personalizar suas respostas\n";
        $basePrompt .= "3. Explique processos educacionais de forma clara e acessível\n";
        $basePrompt .= "4. Oriente sobre documentação e prazos específicos\n";
        $basePrompt .= "5. Mantenha o contexto da conversa para respostas mais relevantes\n";
        $basePrompt .= "6. Seja empático e motivador, reconhecendo as dificuldades do aluno EJA\n";
        
        $basePrompt .= "\n\nFORMATAÇÃO DE MENSAGENS:\n";
        $basePrompt .= "1. Use **NEGRITO** para status importantes (ex: **ATIVA**, **PENDENTE**, **APROVADA**)\n";
        $basePrompt .= "2. Use 🟢 VERDE para valores pagos e situações positivas\n";
        $basePrompt .= "3. Use 🟠 LARANJA para valores pendentes e situações que precisam de atenção\n";
        $basePrompt .= "4. Use 🔴 VERMELHO para valores vencidos e situações críticas\n";
        $basePrompt .= "5. Use 📱 para indicar contato via WhatsApp\n";
        $basePrompt .= "6. Use 💰 para valores monetários\n";
        $basePrompt .= "7. Use 📅 para datas e prazos\n";
        
        $basePrompt .= "\n\nEXEMPLOS DE FORMATAÇÃO:\n";
        $basePrompt .= "- Status da matrícula: **ATIVA** 🟢\n";
        $basePrompt .= "- Mensalidade paga: 💰 R$ 150,00 🟢\n";
        $basePrompt .= "- Mensalidade pendente: 💰 R$ 150,00 🟠 (Vence: 📅 15/08/2024)\n";
        $basePrompt .= "- Mensalidade vencida: 💰 R$ 150,00 🔴 (Venceu: 📅 15/07/2024)\n";
        $basePrompt .= "- Para atendimento via WhatsApp: 📱 Clique no botão abaixo\n";
        
        $basePrompt .= "\n\nBOTÃO DE WHATSAPP:\n";
        $basePrompt .= "Quando o usuário solicitar atendimento via WhatsApp, sempre inclua:\n";
        $basePrompt .= "📱 **Atendimento via WhatsApp**\n";
        $basePrompt .= "Clique no botão abaixo para conversar diretamente com nossa equipe:\n";
        $basePrompt .= "[BOTÃO_WHATSAPP]\n";
        $basePrompt .= "Nossa equipe está pronta para ajudá-lo de forma mais personalizada!";
        
        $basePrompt .= "\n\nFUNCIONALIDADE DE SEGUNDA VIA DE BOLETO:\n";
        $basePrompt .= "Quando o usuário solicitar segunda via de boleto, você pode:\n";
        $basePrompt .= "1. Verificar se o pagamento está elegível para segunda via\n";
        $basePrompt .= "2. Explicar o processo de geração\n";
        $basePrompt .= "3. Informar sobre limites e regras\n";
        $basePrompt .= "4. Orientar sobre datas de vencimento\n";
        $basePrompt .= "5. **IMPORTANTE**: Use comandos especiais para ações:\n";
        $basePrompt .= "   - [VERIFICAR_ELEGIBILIDADE:ID_PAGAMENTO] - Para verificar se pode gerar\n";
        $basePrompt .= "   - [GERAR_SEGUNDA_VIA:ID_PAGAMENTO] - Para gerar segunda via\n";
        $basePrompt .= "6. **REGRAS DE ELEGIBILIDADE**:\n";
        $basePrompt .= "   - Pagamento não pode estar pago\n";
        $basePrompt .= "   - Boleto não pode ter vencido\n";
        $basePrompt .= "   - Máximo de 3 vias por pagamento\n";
        $basePrompt .= "   - Deve estar vinculado a uma matrícula\n";
        
        $basePrompt .= "\n\nPAGAMENTOS VENCIDOS E LINKS DE PAGAMENTO:\n";
        $basePrompt .= "Quando o usuário tiver pagamentos vencidos:\n";
        $basePrompt .= "1. **RESPOSTA ULTRA CURTA** - Máximo 1 frase\n";
        $basePrompt .= "2. **Forneça o link de pagamento** para cada parcela vencida\n";
        $basePrompt .= "3. **SEM explicações** - Apenas o link\n";
        $basePrompt .= "4. **Use formatação especial**:\n";
        $basePrompt .= "   - 🔴 para pagamentos vencidos\n";
        $basePrompt .= "   - 🔗 para links de pagamento\n";
        $basePrompt .= "   - ⚠️ para alertas importantes\n";
        $basePrompt .= "5. **Exemplo de resposta ULTRA CURTA**:\n";
        $basePrompt .= "   '⚠️ **ATENÇÃO**: Você tem X parcela(s) vencida(s)! 🔗 [CLIQUE AQUI](LINK_AQUI)'";
        
        $basePrompt .= "\n\nFLUXO CONVERSACIONAL INTELIGENTE:\n";
        $basePrompt .= "**SEJA DIRETO E RÁPIDO** - Respostas imediatas sem processamento desnecessário\n";
        $basePrompt .= "\n**EXEMPLOS DE FLUXO DIRETO:**\n";
        $basePrompt .= "Usuário: 'segunda via boleto'\n";
        $basePrompt .= "Resposta: 'Vou gerar a segunda via: [GERAR_SEGUNDA_VIA:ID]'\n";
        $basePrompt .= "\nUsuário: 'boleto'\n";
        $basePrompt .= "Resposta: 'Quer gerar segunda via? [GERAR_SEGUNDA_VIA:ID]'\n";
        $basePrompt .= "\n**REGRAS DO FLUXO:**\n";
        $basePrompt .= "- **RESPOSTA IMEDIATA** - Sem verificações desnecessárias\n";
        $basePrompt .= "- **COMANDOS DIRETOS** - Execute ações quando solicitado\n";
        $basePrompt .= "- **SEM PROCESSAMENTO EXTRA** - Apenas o essencial\n";
        $basePrompt .= "- **VELOCIDADE MÁXIMA** - Resposta em segundos";
        
        $basePrompt .= "\n\nRESULTADOS DE COMANDOS DE SEGUNDA VIA:\n";
        $basePrompt .= "Se houver resultados de comandos de segunda via no contexto, use-os para:\n";
        $basePrompt .= "1. Confirmar ações realizadas\n";
        $basePrompt .= "2. Explicar resultados de verificações\n";
        $basePrompt .= "3. Fornecer informações sobre vias geradas\n";
        $basePrompt .= "4. Orientar sobre próximos passos\n";
        
        return $basePrompt;
    }

    /**
     * Resposta de fallback em caso de erro
     */
    protected function getFallbackResponse(string $userMessage, array $context): string
    {
        $supportEmail = $context['system_info']['support_email'] ?? 'contato@ensinocerto.com.br';
        $supportPhone = $context['system_info']['support_phone'] ?? '';
        
        $response = "Desculpe, estou enfrentando dificuldades técnicas no momento.\n\n";
        $response .= "Para um atendimento mais rápido, entre em contato conosco:\n";
        $response .= "📧 Email: {$supportEmail}\n";
        
        if ($supportPhone) {
            $response .= "📞 Telefone: {$supportPhone}\n";
        }
        
        $response .= "\nNossa equipe estará pronta para ajudá-lo!";
        
        return $response;
    }

    /**
     * Prompt padrão para suporte (OTIMIZADO PARA VELOCIDADE)
     */
    protected function getDefaultSupportPrompt(): string
    {
        return "Você é um assistente virtual especializado em EJA Supletivo da plataforma Ensino Certo. Sua função é ajudar alunos e interessados com informações sobre matrículas, pagamentos, cursos e processos educacionais.

**REGRAS PARA VELOCIDADE MÁXIMA:**
- **RESPOSTAS ULTRA-CURTAS** - Máximo 1 frase
- **COMANDOS IMEDIATOS** - Use comandos especiais sem delay
- **SEM CONTEXTO DESNECESSÁRIO** - Apenas o essencial
- **AÇÃO DIRETA** - Execute ações quando solicitado

**COMANDOS ESPECIAIS (USAR IMEDIATAMENTE):**
- `[GERAR_SEGUNDA_VIA:ID]` - Para segunda via de boleto
- `[VERIFICAR_ELEGIBILIDADE:ID]` - Para status de pagamento

**RESPOSTAS INSTANTÂNEAS:**
- 'segunda via' → '📱 **WhatsApp**: (11) 91701-2033 para segunda via'
- 'boleto' → '📱 **WhatsApp**: (11) 91701-2033 para segunda via'
- 'pagamento' → `[VERIFICAR_ELEGIBILIDADE:68]` (sem explicação)
- 'status' → Use dados reais: 'Você tem X vencidas, Y pendentes'
- 'matricula' → Use dados reais: 'Sua matrícula está ATIVA no curso X'
- 'curso' → Use dados reais: 'Você está matriculado em X'

**IMPORTANTE:** SEMPRE use os dados reais da matrícula e pagamentos fornecidos no contexto. Seja específico com valores, datas e status reais.

**EXEMPLOS DE USO DOS DADOS REAIS:**
- Se o usuário perguntar sobre matrícula: Use o nome real, curso real e status real
- Se o usuário perguntar sobre pagamentos: Use valores reais, datas reais e status real
- Se o usuário perguntar sobre segunda via: Use IDs reais dos pagamentos vencidos
- SEMPRE seja específico: \"Douglas, sua matrícula está em PRÉ-MATRÍCULA no curso EJA\"
- SEMPRE use valores reais: \"Você tem 1 pagamento vencido de R$ 1.199,88\"

**FORMATAÇÃO RÁPIDA:**
- **negrito** para informações importantes
- 🟢 valores pagos, 🟠 pendentes, 🔴 vencidos
- 💰 valores monetários, 📅 datas

**OBJETIVO:** Resposta em 1 frase + comando especial quando aplicável. Sempre priorizar velocidade.";
    }

    /**
     * Obter histórico de conversa
     */
    public function getConversationHistory(string $sessionId, int $limit = 50): array
    {
        $conversation = ChatConversation::where('session_id', $sessionId)->first();
        
        if (!$conversation) {
            return [];
        }
        
        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'timestamp' => $message->created_at->format('H:i'),
                    'is_user' => $message->isUserMessage(),
                    'created_at' => $message->created_at->toISOString()
                ];
            })
            ->toArray();
    }

    /**
     * Encerrar conversa
     */
    public function closeConversation(string $sessionId): bool
    {
        $conversation = ChatConversation::where('session_id', $sessionId)->first();
        
        if ($conversation) {
            $conversation->close();
            return true;
        }
        
        return false;
    }

    /**
     * Limpar conversas antigas (manutenção)
     */
    public function cleanupOldConversations(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        $oldConversations = ChatConversation::where('created_at', '<', $cutoffDate)
            ->where('status', 'closed')
            ->get();
        
        $deletedCount = 0;
        
        foreach ($oldConversations as $conversation) {
            try {
                $conversation->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                Log::error('Erro ao deletar conversa antiga', [
                    'conversation_id' => $conversation->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $deletedCount;
    }

    /**
     * Obter estatísticas do chat
     */
    public function getChatStats(): array
    {
        $totalConversations = ChatConversation::count();
        $activeConversations = ChatConversation::where('status', 'active')->count();
        $totalMessages = ChatMessage::count();
        $todayMessages = ChatMessage::whereDate('created_at', today())->count();
        
        return [
            'total_conversations' => $totalConversations,
            'active_conversations' => $activeConversations,
            'total_messages' => $totalMessages,
            'today_messages' => $todayMessages,
            'avg_messages_per_conversation' => $totalConversations > 0 ? round($totalMessages / $totalConversations, 2) : 0
        ];
    }

    /**
     * RESPOSTA DIRETA E RÁPIDA - Sem processamento complexo
     */
    protected function getDirectResponse(string $userMessage, ?string $userEmail): string
    {
        $messageLower = strtolower($userMessage);
        
        // Segunda via de boleto
        if (str_contains($messageLower, 'segunda via') || str_contains($messageLower, 'boleto')) {
            if (!$userEmail) {
                return "Digite seu email para gerar a segunda via do boleto.";
            }
            
            // Buscar pagamentos vencidos
            $payments = Payment::with('matricula')
                ->whereHas('matricula', function($query) use ($userEmail) {
                    $query->where('email', $userEmail);
                })
                ->where('status', 'pending')
                ->where('data_vencimento', '<', now())
                ->first();
            
            if ($payments) {
                // Se tem link salvo, usar ele
                if ($payments->payment_link) {
                    return "🔗 **Segunda via do boleto:** [CLIQUE AQUI]({$payments->payment_link})";
                }
                
                // Se não tem link, gerar um novo
                return "🔗 **Gerar segunda via:** [CLIQUE AQUI](/api/mercadopago/payment-link?payment_id={$payments->id}&email=" . urlencode($userEmail) . ")";
            }
            
            return "Não há boletos vencidos para gerar segunda via.";
        }
        
        // Pagamentos vencidos
        if (str_contains($messageLower, 'vencido') || str_contains($messageLower, 'atrasado')) {
            if (!$userEmail) {
                return "Digite seu email para verificar pagamentos.";
            }
            
            // Buscar pagamentos vencidos
            $payments = Payment::with('matricula')
                ->whereHas('matricula', function($query) use ($userEmail) {
                    $query->where('email', $userEmail);
                })
                ->where('status', 'pending')
                ->where('data_vencimento', '<', now())
                ->get();
            
            if ($payments->count() > 0) {
                $count = $payments->count();
                $firstPayment = $payments->first();
                
                // Se tem link salvo, usar ele
                if ($firstPayment->payment_link) {
                    return "⚠️ **ATENÇÃO**: Você tem $count parcela(s) vencida(s)! 🔗 [CLIQUE AQUI]({$firstPayment->payment_link})";
                }
                
                // Se não tem link, gerar um novo
                return "⚠️ **ATENÇÃO**: Você tem $count parcela(s) vencida(s)! 🔗 [CLIQUE AQUI](/api/mercadopago/payment-link?payment_id={$firstPayment->id}&email=" . urlencode($userEmail) . ")";
            }
            
            return "Não há pagamentos vencidos.";
        }
        
        // Status de pagamento
        if (str_contains($messageLower, 'status') || str_contains($messageLower, 'situação')) {
            if (!$userEmail) {
                return "Digite seu email para verificar o status.";
            }
            
            // Buscar status real dos pagamentos
            $payments = Payment::with('matricula')
                ->whereHas('matricula', function($query) use ($userEmail) {
                    $query->where('email', $userEmail);
                })
                ->get();
            
            $pendingCount = $payments->where('status', 'pending')->count();
            $paidCount = $payments->where('status', 'approved')->count();
            
            return "📊 **Status**: $pendingCount pendente(s), $paidCount pago(s)";
        }
        
        // Resposta padrão
        return "Digite 'segunda via', 'vencido' ou 'status' + seu email.";
    }

    /**
     * Processar comandos especiais de segunda via
     */
    protected function processSecondViaCommands(string $message, ?string $userEmail): array
    {
        $commands = [];
        
        // Verificar comandos explícitos de segunda via
        if (preg_match('/\[GERAR_SEGUNDA_VIA:(\d+)\]/', $message, $matches)) {
            $paymentId = $matches[1];
            $commands[] = [
                'type' => 'generate_second_via',
                'payment_id' => $paymentId,
                'action' => 'generate'
            ];
        }
        
        if (preg_match('/\[VERIFICAR_ELEGIBILIDADE:(\d+)\]/', $message, $matches)) {
            $paymentId = $matches[1];
            $commands[] = [
                'type' => 'check_eligibility',
                'payment_id' => $matches[1],
                'action' => 'check'
            ];
        }
        
        // Verificar se a mensagem contém palavras-chave relacionadas a segunda via (OTIMIZADO)
        $messageLower = strtolower($message);
        
        if (str_contains($messageLower, 'segunda via') || str_contains($messageLower, 'boleto')) {
            // Se não conseguiu extrair ID, criar comando genérico para verificar elegibilidade
            $commands[] = [
                'type' => 'check_eligibility_generic',
                'payment_id' => null,
                'action' => 'check_generic'
            ];
        }
        
        return $commands;
    }

    /**
     * Executar comandos de segunda via
     */
    protected function executeSecondViaCommands(array $commands, ?string $userEmail): array
    {
        $results = [];
        
        foreach ($commands as $command) {
            try {
                switch ($command['type']) {
                    case 'generate_second_via':
                        $payment = Payment::with('matricula')->find($command['payment_id']);
                        if ($payment && $this->canAccessPayment($payment, $userEmail)) {
                            $result = $this->boletoService->generateSecondVia($payment);
                            $results[] = [
                                'command' => $command,
                                'success' => true,
                                'result' => $result
                            ];
                        } else {
                            $results[] = [
                                'command' => $command,
                                'success' => false,
                                'error' => 'Pagamento não encontrado ou acesso não autorizado'
                            ];
                        }
                        break;
                        
                    case 'check_eligibility':
                        $payment = Payment::with('matricula')->find($command['payment_id']);
                        if ($payment && $this->canAccessPayment($payment, $userEmail)) {
                            $eligibility = $this->boletoService->canGenerateSecondVia($payment);
                            $results[] = [
                                'command' => $command,
                                'success' => true,
                                'result' => $eligibility
                            ];
                        } else {
                            $results[] = [
                                'command' => $command,
                                'success' => false,
                                'error' => 'Pagamento não encontrado ou acesso não autorizado'
                            ];
                        }
                        break;
                        
                    case 'check_eligibility_generic':
                        // Verificar todos os pagamentos elegíveis do usuário
                        if ($userEmail) {
                            $payments = Payment::with('matricula')
                                ->whereHas('matricula', function($query) use ($userEmail) {
                                    $query->where('email', $userEmail);
                                })
                                ->where('status', '!=', 'paid')
                                ->where('data_vencimento', '>', now())
                                ->get();
                            
                            $eligibilityResults = [];
                            foreach ($payments as $payment) {
                                $eligibility = $this->boletoService->canGenerateSecondVia($payment);
                                if ($eligibility['can_generate']) {
                                    $eligibilityResults[] = [
                                        'payment_id' => $payment->id,
                                        'descricao' => $payment->descricao,
                                        'valor' => $payment->valor,
                                        'data_vencimento' => $payment->data_vencimento->format('d/m/Y'),
                                        'eligibility' => $eligibility
                                    ];
                                }
                            }
                            
                            $results[] = [
                                'command' => $command,
                                'success' => true,
                                'result' => [
                                    'type' => 'generic_eligibility',
                                    'payments' => $eligibilityResults,
                                    'message' => 'Verificação de elegibilidade para todos os pagamentos'
                                ]
                            ];
                        } else {
                            $results[] = [
                                'command' => $command,
                                'success' => false,
                                'error' => 'Email do usuário não fornecido para verificação genérica'
                            ];
                        }
                        break;
                }
            } catch (\Exception $e) {
                $results[] = [
                    'command' => $command,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Processar comandos da resposta do ChatGPT
     */
    protected function processChatGptCommands(string $response, ?string $userEmail, array $context): string
    {
        $maxIterations = 3; // Máximo de 3 iterações para evitar loop infinito
        $currentResponse = $response;
        $iteration = 0;
        
        while ($iteration < $maxIterations) {
            $iteration++;
            
            // Verificar se há comandos na resposta atual
            $commands = $this->processSecondViaCommands($currentResponse, $userEmail);
            
            if (empty($commands)) {
                // Não há mais comandos, retornar resposta final
                break;
            }
            
            // Executar comandos
            $results = $this->executeSecondViaCommands($commands, $userEmail);
            
            // Atualizar contexto com resultados
            $context['second_via_results'] = $results;
            
            // Gerar nova resposta do ChatGPT com os resultados
            try {
                $newResponse = $this->generateChatGptResponseWithResults($currentResponse, $context, $results);
                $currentResponse = $newResponse;
            } catch (\Exception $e) {
                // Se falhar, adicionar resultados diretamente à resposta
                $currentResponse .= "\n\n📋 **RESULTADOS DOS COMANDOS EXECUTADOS:**\n";
                foreach ($results as $result) {
                    if ($result['success']) {
                        $currentResponse .= "✅ " . $this->formatCommandResult($result) . "\n";
                    } else {
                        $currentResponse .= "❌ " . $result['error'] . "\n";
                    }
                }
                break;
            }
        }
        
        return $currentResponse;
    }
    
    /**
     * Gerar nova resposta do ChatGPT com os resultados dos comandos
     */
    protected function generateChatGptResponseWithResults(string $originalResponse, array $context, array $results): string
    {
        // Construir prompt para processar resultados
        $prompt = "A resposta anterior do ChatGPT foi:\n\n{$originalResponse}\n\n";
        $prompt .= "Os seguintes comandos foram executados com sucesso:\n";
        
        foreach ($results as $result) {
            if ($result['success']) {
                $prompt .= "- {$result['command']['type']}: " . $this->formatCommandResult($result) . "\n";
            }
        }
        
        $prompt .= "\nPor favor, atualize a resposta para incluir os resultados dos comandos executados. ";
        $prompt .= "Substitua os placeholders como [VERIFICAR_ELEGIBILIDADE:ID] pelos resultados reais. ";
        $prompt .= "Mantenha o tom amigável e informativo.";
        
        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um assistente que atualiza respostas com base em resultados de comandos executados.'
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
                return $result['choices'][0]['message']['content'] ?? $originalResponse;
            }
            
            return $originalResponse;
            
        } catch (\Exception $e) {
            return $originalResponse;
        }
    }
    
    /**
     * Formatar resultado de comando para exibição
     */
    protected function formatCommandResult(array $result): string
    {
        switch ($result['command']['type']) {
            case 'check_eligibility':
                if (isset($result['result']['can_generate']) && $result['result']['can_generate']) {
                    return "Pagamento elegível para segunda via ✅";
                } else {
                    return "Pagamento não elegível: " . implode(', ', $result['result']['reasons'] ?? []);
                }
                
            case 'generate_second_via':
                if (isset($result['result']['boleto_via'])) {
                    return "Segunda via gerada com sucesso! Via: {$result['result']['boleto_via']->via_number_formatted}";
                } else {
                    return "Segunda via gerada com sucesso!";
                }
                
            case 'check_eligibility_generic':
                if (isset($result['result']['payments']) && !empty($result['result']['payments'])) {
                    $count = count($result['result']['payments']);
                    $overdueCount = 0;
                    $paymentLinks = [];
                    
                    foreach ($result['result']['payments'] as $payment) {
                        if (isset($payment['is_overdue']) && $payment['is_overdue']) {
                            $overdueCount++;
                            if (isset($payment['payment_link'])) {
                                $paymentLinks[] = "🔗 [Pagar {$payment['descricao']}]({$payment['payment_link']})";
                            }
                        }
                    }
                    
                    $response = "Encontrados {$count} pagamento(s) elegível(is) para segunda via";
                    
                    if ($overdueCount > 0) {
                        $response .= "\n⚠️ **ATENÇÃO**: {$overdueCount} parcela(s) vencida(s)!";
                        if (!empty($paymentLinks)) {
                            $response .= "\n" . implode("\n", $paymentLinks);
                        }
                    }
                    
                    return $response;
                } else {
                    return "Nenhum pagamento elegível encontrado";
                }
                
            default:
                return "Comando executado com sucesso";
        }
    }

    /**
     * Verificar se o usuário pode acessar o pagamento
     */
    protected function canAccessPayment(Payment $payment, ?string $userEmail): bool
    {
        if (!$userEmail) {
            return false;
        }
        
        // Verificar se o pagamento pertence ao usuário
        if ($payment->matricula && $payment->matricula->email === $userEmail) {
            return true;
        }
        
        return false;
    }
}
