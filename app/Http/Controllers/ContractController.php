<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Matricula;
use App\Mail\ContractMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractController extends Controller
{
    /**
     * Middleware para rotas administrativas
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['access', 'show', 'verify', 'sign']);
        $this->middleware('permission:contratos.index')->only(['index', 'generate', 'send', 'sendWhatsApp', 'cancel', 'regenerateLink', 'viewSigned', 'downloadPdf', 'updateVariables']);
    }

    /**
     * Listar contratos (admin)
     */
    public function index(Request $request)
    {
        $query = Contract::with(['matricula', 'creator']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period')) {
            $period = $request->period;
            switch ($period) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'quarter':
                    $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('student_email', 'like', "%{$search}%")
                  ->orWhereHas('matricula', function($mq) use ($search) {
                      $mq->where('nome_completo', 'like', "%{$search}%")
                        ->orWhere('numero_matricula', 'like', "%{$search}%");
                  });
            });
        }

        $contracts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.contracts.index', compact('contracts'));
    }

    /**
     * Gerar contrato para uma matrícula
     */
    public function generate(Request $request, Matricula $matricula)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'nullable|exists:contract_templates,id',
            'custom_variables' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Buscar template
            $template = $request->template_id 
                ? ContractTemplate::findOrFail($request->template_id)
                : ContractTemplate::getDefault();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum template de contrato encontrado. Configure um template padrão.'
                ], 400);
            }

            // Gerar contrato
            $contract = $template->generateContract($matricula, $request->custom_variables ?? []);

            return response()->json([
                'success' => true,
                'contract' => $contract,
                'message' => 'Contrato gerado com sucesso!',
                'access_link' => $contract->getAccessLink()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar contrato: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar contrato para o aluno
     */
    public function send(Contract $contract)
    {
        if (!in_array($contract->status, ['draft', 'expired'])) {
            return response()->json([
                'success' => false,
                'message' => 'Contrato não pode ser enviado no status atual.'
            ], 400);
        }

        try {
            // Atualizar status
            $contract->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Enviar email
            Mail::to($contract->student_email)->send(new ContractMail($contract));

            return response()->json([
                'success' => true,
                'message' => 'Contrato enviado com sucesso! O aluno receberá um email com o link de acesso.',
                'access_link' => $contract->getAccessLink()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar contrato: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar contrato via WhatsApp
     */
    public function sendWhatsApp(Contract $contract)
    {
        if (!in_array($contract->status, ['draft', 'sent', 'viewed', 'expired'])) {
            return response()->json([
                'success' => false,
                'message' => 'Contrato não pode ser enviado no status atual.'
            ], 400);
        }

        try {
            // Verificar se o aluno tem telefone celular
            if (!$contract->matricula->telefone_celular) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aluno não possui telefone celular cadastrado.'
                ], 400);
            }

            // Atualizar status se for rascunho
            if ($contract->status === 'draft') {
                $contract->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }

            // Enviar via WhatsApp
            $whatsappService = app(\App\Services\WhatsAppService::class);
            
            if (!$whatsappService->hasValidSettings()) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp não está configurado. Configure em Configurações > WhatsApp.'
                ], 400);
            }

            $whatsappService->sendContractNotification($contract);

            return response()->json([
                'success' => true,
                'message' => 'Contrato enviado via WhatsApp com sucesso!',
                'access_link' => $contract->getAccessLink()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar contrato via WhatsApp: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Acesso público ao contrato (via token)
     */
    public function access($token)
    {
        $contract = Contract::where('access_token', $token)->first();

        if (!$contract) {
            return view('contracts.error', [
                'title' => 'Contrato não encontrado',
                'message' => 'O link de acesso ao contrato não é válido ou expirou.'
            ]);
        }

        // Verificar expiração
        $contract->checkExpiration();

        if (!$contract->isTokenValid()) {
            return view('contracts.error', [
                'title' => 'Link expirado',
                'message' => 'O link de acesso ao contrato expirou. Entre em contato com a instituição.'
            ]);
        }

        return view('contracts.access', compact('contract'));
    }

    /**
     * Verificar email do aluno
     */
    public function verify(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $contract = Contract::where('access_token', $token)->first();

        if (!$contract || !$contract->isTokenValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Contrato não encontrado ou link expirado.'
            ], 404);
        }

        if (strtolower($contract->student_email) !== strtolower($request->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Email não confere com o cadastro do aluno.'
            ], 400);
        }

        // Verificar email e marcar como visualizado
        $contract->verifyEmail();
        $contract->markAsViewed();

        return response()->json([
            'success' => true,
            'message' => 'Email verificado com sucesso!',
            'contract' => [
                'id' => $contract->id,
                'title' => $contract->title,
                'content' => $contract->processContent(),
                'student_name' => $contract->matricula->nome_completo,
                'can_sign' => $contract->canBeSigned(),
            ]
        ]);
    }

    /**
     * Visualizar contrato
     */
    public function show($token)
    {
        $contract = Contract::where('access_token', $token)->first();

        if (!$contract || !$contract->isTokenValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Contrato não encontrado ou link expirado.'
            ], 404);
        }

        if (!$contract->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email não verificado.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'contract' => [
                'id' => $contract->id,
                'title' => $contract->title,
                'content' => $contract->processContent(),
                'status' => $contract->status,
                'student_name' => $contract->matricula->nome_completo,
                'can_sign' => $contract->canBeSigned(),
                'signed_at' => $contract->signed_at,
            ]
        ]);
    }

    /**
     * Assinar contrato
     */
    public function sign(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'signature' => 'required|string',
            'user_agent' => 'nullable|string',
            'screen_resolution' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $contract = Contract::where('access_token', $token)->first();

        if (!$contract || !$contract->canBeSigned()) {
            return response()->json([
                'success' => false,
                'message' => 'Contrato não pode ser assinado.'
            ], 400);
        }

        if (!$contract->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email não verificado.'
            ], 400);
        }

        try {
            $metadata = [
                'user_agent' => $request->user_agent ?? $request->header('User-Agent'),
                'screen_resolution' => $request->screen_resolution,
                'timestamp' => now()->toISOString(),
            ];

            $contract->sign($request->signature, $request->ip(), $metadata);

            return response()->json([
                'success' => true,
                'message' => 'Contrato assinado com sucesso!',
                'signed_at' => $contract->signed_at->format('d/m/Y H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao assinar contrato: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar contrato
     */
    public function cancel(Contract $contract)
    {
        if ($contract->status === 'signed') {
            return response()->json([
                'success' => false,
                'message' => 'Contrato assinado não pode ser cancelado.'
            ], 400);
        }

        $contract->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Contrato cancelado com sucesso!'
        ]);
    }

    /**
     * Gerar novo link de acesso
     */
    public function regenerateLink(Contract $contract)
    {
        if ($contract->status === 'signed') {
            return response()->json([
                'success' => false,
                'message' => 'Contrato já foi assinado.'
            ], 400);
        }

        $contract->generateNewAccessToken();

        return response()->json([
            'success' => true,
            'message' => 'Novo link gerado com sucesso!',
            'access_link' => $contract->getAccessLink()
        ]);
    }

    /**
     * Visualizar contrato assinado (admin)
     */
    public function viewSigned(Contract $contract)
    {
        if ($contract->status !== 'signed') {
            return redirect()->back()->with('error', 'Contrato não foi assinado.');
        }

        return view('admin.contracts.signed', compact('contract'));
    }

    /**
     * Download do contrato em PDF
     */
    public function downloadPdf(Contract $contract)
    {
        if ($contract->status !== 'signed') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas contratos assinados podem ser baixados.'
            ], 400);
        }

        try {
            // Gerar PDF
            $pdf = Pdf::loadView('contracts.pdf', compact('contract'));
            
            // Configurações do PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
            
            $filename = "contrato_{$contract->contract_number}_{$contract->matricula->nome_completo}.pdf";
            $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download público do contrato em PDF (via token)
     */
    public function downloadPublicPdf($token)
    {
        $contract = Contract::where('access_token', $token)->first();

        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Contrato não encontrado.'
            ], 404);
        }

        if ($contract->status !== 'signed') {
            return response()->json([
                'success' => false,
                'message' => 'Apenas contratos assinados podem ser baixados.'
            ], 400);
        }

        try {
            // Gerar PDF
            $pdf = Pdf::loadView('contracts.pdf', compact('contract'));
            
            // Configurações do PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
            
            $filename = "contrato_{$contract->contract_number}_{$contract->matricula->nome_completo}.pdf";
            $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar variáveis do contrato com dados atuais da matrícula
     */
    public function updateVariables(Contract $contract)
    {
        try {
            if ($contract->updateVariables()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Variáveis do contrato atualizadas com sucesso!',
                    'variables' => [
                        'enrollment_value' => $contract->variables['enrollment_value'] ?? 'N/A',
                        'tuition_value' => $contract->variables['tuition_value'] ?? 'N/A',
                        'payment_method' => $contract->variables['payment_method'] ?? 'N/A',
                        'student_name' => $contract->variables['student_name'] ?? 'N/A',
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar variáveis do contrato.'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar variáveis: ' . $e->getMessage()
            ], 500);
        }
    }
}
