<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WhatsAppTemplateController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:whatsapp.templates');
    }

    /**
     * Display a listing of the templates.
     */
    public function index()
    {
        $templates = WhatsAppTemplate::orderBy('name')->get();
        return view('admin.whatsapp.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        return view('admin.whatsapp.templates.create');
    }

    /**
     * Store a newly created template in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:whatsapp_templates',
            'description' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|max:255',
            'variables' => 'nullable|array',
            'variables.*' => 'string|max:255',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()
                ->route('admin.whatsapp.templates.create')
                ->withErrors($validator)
                ->withInput();
        }

        $template = WhatsAppTemplate::create($request->all());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'id' => $template->id,
                'message' => 'Template criado com sucesso!'
            ]);
        }

        return redirect()
            ->route('admin.whatsapp.templates.index')
            ->with('success', 'Template criado com sucesso!');
    }

    /**
     * Display the specified template.
     */
    public function show(WhatsAppTemplate $template)
    {
        return response()->json([
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'content' => $template->content,
            'category' => $template->category,
            'variables' => $template->variables ?? [],
            'active' => $template->active
        ]);
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(WhatsAppTemplate $template)
    {
        return view('admin.whatsapp.templates.edit', compact('template'));
    }

    /**
     * Update the specified template in storage.
     */
    public function update(Request $request, WhatsAppTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:whatsapp_templates,name,' . $template->id,
            'description' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|max:255',
            'variables' => 'nullable|array',
            'variables.*' => 'string|max:255',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.whatsapp.templates.edit', $template)
                ->withErrors($validator)
                ->withInput();
        }

        $template->update($request->all());

        return redirect()
            ->route('admin.whatsapp.templates.index')
            ->with('success', 'Template atualizado com sucesso!');
    }

    /**
     * Remove the specified template from storage.
     */
    public function destroy(WhatsAppTemplate $template)
    {
        $template->delete();

        return redirect()
            ->route('admin.whatsapp.templates.index')
            ->with('success', 'Template excluído com sucesso!');
    }

    /**
     * Test the specified template.
     */
    public function test(Request $request, WhatsAppTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'test_data' => 'required|array',
            'test_phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Validar se todas as variáveis necessárias estão presentes
            if (!$template->validateVariables($request->test_data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados de teste insuficientes. Verifique se todas as variáveis foram fornecidas.'
                ], 422);
            }

            // Substituir variáveis
            $message = $template->replaceVariables($request->test_data);

            // Enviar mensagem de teste
            app(\App\Services\WhatsAppService::class)->sendMessage(
                $request->test_phone,
                $message
            );

            return response()->json([
                'success' => true,
                'message' => 'Mensagem de teste enviada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar mensagem de teste: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate template with AI
     */
    public function generateWithAi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'objective' => 'required|string|max:500',
            'target_audience' => 'nullable|string|max:100',
            'tone' => 'nullable|string|max:50',
            'additional_instructions' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar se as configurações de AI estão ativas
            $aiSettings = \App\Models\SystemSetting::getAiSettings();
            if (!$aiSettings['is_active'] || empty($aiSettings['api_key'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'ChatGPT não está configurado ou ativo. Configure nas configurações do sistema.'
                ], 400);
            }

            // Instanciar o serviço ChatGPT
            $chatGptService = new \App\Services\ChatGptService();

            // Construir instruções adicionais baseadas no tom
            $toneInstructions = $this->getToneInstructions($request->tone);
            $additionalInstructions = $toneInstructions;
            
            if ($request->additional_instructions) {
                $additionalInstructions .= "\n" . $request->additional_instructions;
            }

            // Gerar template
            $result = $chatGptService->generateWhatsAppTemplate(
                $request->objective,
                $request->target_audience ?? 'estudantes',
                $additionalInstructions
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Não foi possível gerar o template. Tente novamente.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'content' => $result['content'],
                'message' => 'Template gerado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tone-specific instructions
     */
    private function getToneInstructions($tone)
    {
        $instructions = [
            'formal' => 'Use linguagem formal e respeitosa, evite gírias e seja direto.',
            'amigavel' => 'Use linguagem amigável e calorosa, pode incluir emojis moderadamente.',
            'motivacional' => 'Use linguagem inspiradora e motivacional, destaque benefícios e conquistas.',
            'urgente' => 'Crie senso de urgência, use palavras como "agora", "último dia", "não perca".',
            'informativo' => 'Seja claro e informativo, foque em transmitir informações importantes.'
        ];

        return $instructions[$tone] ?? '';
    }
} 