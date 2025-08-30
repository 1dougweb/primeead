<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Matricula;
use App\Models\SystemSetting;
use App\Services\MercadoPagoService;
use App\Services\PaymentNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['matricula'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('forma_pagamento')) {
            if ($request->forma_pagamento === 'manual') {
                // Filtrar pagamentos de outros bancos (não Mercado Pago)
                $query->whereHas('matricula', function($q) {
                    $q->whereIn('payment_gateway', ['asas', 'infiny_pay', 'cora']);
                });
            } else {
                $query->where('forma_pagamento', $request->forma_pagamento);
            }
        }

        if ($request->filled('gateway')) {
            $query->whereHas('matricula', function($q) use ($request) {
                $q->where('payment_gateway', $request->gateway);
            });
        }

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('data_vencimento', [$request->data_inicio, $request->data_fim]);
        }

        if ($request->filled('overdue') && $request->overdue == '1') {
            $query->where('data_vencimento', '<', now())
                  ->whereIn('status', ['pending', 'processing']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('matricula', function($q) use ($search) {
                $q->where('nome_completo', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        $payments = $query->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    public function dashboard()
    {
        $stats = [
            'total_payments' => Payment::count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'paid_payments' => Payment::where('status', 'paid')->count(),
            'overdue_payments' => Payment::where('data_vencimento', '<', now())
                                         ->whereIn('status', ['pending', 'processing'])
                                         ->count(),
            'total_amount' => Payment::where('status', 'paid')->sum('valor'),
            'pending_amount' => Payment::whereIn('status', ['pending', 'processing'])->sum('valor'),
            'overdue_amount' => Payment::where('data_vencimento', '<', now())
                                      ->whereIn('status', ['pending', 'processing'])
                                      ->sum('valor'),
        ];

        // Comparação com mês anterior
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();

        $stats['monthly_comparison'] = [
            'current' => Payment::where('status', 'paid')
                               ->where('data_pagamento', '>=', $currentMonth)
                               ->sum('valor') ?? 0,
            'previous' => Payment::where('status', 'paid')
                                ->where('data_pagamento', '>=', $previousMonth)
                                ->where('data_pagamento', '<', $currentMonth)
                                ->sum('valor') ?? 0,
        ];

        $recentPayments = Payment::with(['matricula'])
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();

        return view('admin.payments.dashboard', compact('stats', 'recentPayments'));
    }

    public function create(Request $request)
    {
        $matriculas = Matricula::all();
        $selectedMatricula = null;
        
        if ($request->filled('matricula_id')) {
            $selectedMatricula = Matricula::find($request->matricula_id);
        }
        
        return view('admin.payments.create', compact('matriculas', 'selectedMatricula'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'matricula_id' => 'required|exists:matriculas,id',
            'valor' => 'required|numeric|min:0.01',
            'forma_pagamento' => 'required|in:pix,cartao_credito,boleto',
            'data_vencimento' => 'required|date',
            'numero_parcela' => 'nullable|integer|min:1',
            'total_parcelas' => 'nullable|integer|min:1',
        ]);

        // Validação específica para boletos - valor mínimo R$ 3,00
        if ($request->forma_pagamento === 'boleto' && $request->valor < 3.00) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['valor' => 'O valor mínimo para boletos é R$ 3,00']);
        }

        $payment = Payment::create([
            'matricula_id' => $request->matricula_id,
            'valor' => $request->valor,
            'forma_pagamento' => $request->forma_pagamento,
            'data_vencimento' => $request->data_vencimento,
            'numero_parcela' => $request->numero_parcela ?? 1,
            'total_parcelas' => $request->total_parcelas ?? 1,
            'status' => 'pending',
        ]);

        // Integrar com Mercado Pago se configurado
        $paymentSettings = SystemSetting::getPaymentSettings();
        if ($paymentSettings['mercadopago_enabled']) {
            try {
                $mercadoPagoService = new MercadoPagoService();
                $mercadoPagoPayment = $mercadoPagoService->createPayment($payment);
                
                $payment->update([
                    'mercadopago_id' => $mercadoPagoPayment['id'],
                    'mercadopago_status' => $mercadoPagoPayment['status'],
                    'mercadopago_data' => $mercadoPagoPayment['full_response']
                ]);

                // Adicionar mensagem de sucesso específica para Mercado Pago
                session()->flash('mercadopago_success', 'Pagamento criado com sucesso no Mercado Pago! ID: ' . $mercadoPagoPayment['id']);

            } catch (\Exception $e) {
                Log::error('Erro ao criar pagamento no Mercado Pago: ' . $e->getMessage());
                session()->flash('mercadopago_error', 'Erro ao processar pagamento no Mercado Pago: ' . $e->getMessage());
            }
        }

        // Enviar notificações de pagamento criado
        try {
            $notificationService = app(PaymentNotificationService::class);
            $notificationService->sendPaymentCreatedNotifications($payment);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificações de pagamento manual', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('admin.payments.index')
                        ->with('success', 'Pagamento criado com sucesso!');
    }

    public function show(Payment $payment)
    {
        $payment->load(['matricula']);
        return view('admin.payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $matriculas = Matricula::all();
        return view('admin.payments.edit', compact('payment', 'matriculas'));
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'valor' => 'required|numeric|min:0.01',
            'forma_pagamento' => 'required|in:pix,cartao_credito,boleto',
            'data_vencimento' => 'required|date',
            'status' => 'required|in:pending,processing,paid,failed,cancelled',
            'data_pagamento' => 'nullable|date',
        ]);

        $updateData = $request->only([
            'valor', 'forma_pagamento', 'data_vencimento', 'status'
        ]);

        // Se status mudou para 'paid' e não há data_pagamento, definir agora
        if ($request->status === 'paid' && !$payment->data_pagamento) {
            $updateData['data_pagamento'] = $request->data_pagamento ?? now();
            
            // Verificar se o pagamento está vencido e aplicar juros se necessário
            // Apenas se o valor não foi explicitamente modificado pelo usuário
            if ($payment->isOverdue() && $request->valor == $payment->valor) {
                $valorAtualizado = $payment->getValorAtualizado();
                $valorJuros = $payment->getValorJurosMora();
                
                Log::info('Aplicando juros de mora em pagamento vencido (via update)', [
                    'payment_id' => $payment->id,
                    'valor_original' => $payment->valor,
                    'valor_atualizado' => $valorAtualizado,
                    'valor_juros' => $valorJuros,
                    'dias_atraso' => $payment->getDaysOverdue()
                ]);
                
                // Atualizar o valor com os juros
                $updateData['valor'] = $valorAtualizado;
                $updateData['observacoes'] = ($payment->observacoes ? $payment->observacoes . "\n" : '') . 
                                           "Juros de mora aplicados: R$ " . number_format($valorJuros, 2, ',', '.') . 
                                           " (" . $payment->getDaysOverdue() . " dias de atraso)";
            }
        }

        $payment->update($updateData);

        return redirect()->route('admin.payments.index')
                        ->with('success', 'Pagamento atualizado com sucesso!');
    }

    public function destroy(Payment $payment)
    {
        // Cancelar no Mercado Pago se necessário
        if ($payment->mercadopago_id) {
            try {
                $mercadoPagoService = new MercadoPagoService();
                $result = $mercadoPagoService->cancelPayment($payment->mercadopago_id);
                
                // Registrar resultado da tentativa de cancelamento
                if ($result['success']) {
                    Log::info('Pagamento cancelado no Mercado Pago com sucesso', [
                        'payment_id' => $payment->id,
                        'mercadopago_id' => $payment->mercadopago_id,
                        'status' => $result['status'] ?? 'cancelled'
                    ]);
                    
                    // Atualizar status antes de excluir
                    $payment->update([
                        'status' => 'cancelled',
                        'mercadopago_status' => $result['status'] ?? 'cancelled'
                    ]);
                } else {
                    Log::warning('Falha ao cancelar pagamento no Mercado Pago', [
                        'payment_id' => $payment->id,
                        'mercadopago_id' => $payment->mercadopago_id,
                        'message' => $result['message'] ?? 'Erro desconhecido'
                    ]);
                    
                    // Ainda assim, marcar como cancelado localmente
                    $payment->update(['status' => 'cancelled']);
                }
            } catch (\Exception $e) {
                Log::error('Erro ao cancelar pagamento no Mercado Pago: ' . $e->getMessage(), [
                    'payment_id' => $payment->id,
                    'mercadopago_id' => $payment->mercadopago_id
                ]);
                
                // Marcar como cancelado localmente mesmo com erro
                $payment->update(['status' => 'cancelled']);
            }
        } else {
            // Se não tem ID do Mercado Pago, apenas marcar como cancelado
            $payment->update(['status' => 'cancelled']);
        }

        // Excluir o pagamento
        $payment->delete();

        return redirect()->route('admin.payments.index')
                        ->with('success', 'Pagamento excluído com sucesso!');
    }

    public function markAsPaid(Payment $payment)
    {
        // Verificar se o usuário tem permissão para marcar pagamentos como pagos
        if (!auth()->user()->hasPermissionTo('pagamentos.edit')) {
            return redirect()->back()->with('error', 'Você não tem permissão para marcar pagamentos como pagos.');
        }

        // 🚨 PROTEÇÃO: Impedir marcar como pago se for Mercado Pago
        $gateway = $payment->matricula->payment_gateway ?? 'mercado_pago';
        if ($gateway === 'mercado_pago') {
            return redirect()->back()->with('error', 'Pagamentos do Mercado Pago são atualizados automaticamente via webhook. Não é possível marcar manualmente.');
        }

        try {
            // Verificar se o pagamento está vencido e aplicar juros se necessário
            if ($payment->isOverdue()) {
                $valorAtualizado = $payment->getValorAtualizado();
                $valorJuros = $payment->getValorJurosMora();
                
                Log::info('Aplicando juros de mora em pagamento vencido', [
                    'payment_id' => $payment->id,
                    'valor_original' => $payment->valor,
                    'valor_atualizado' => $valorAtualizado,
                    'valor_juros' => $valorJuros,
                    'dias_atraso' => $payment->getDaysOverdue()
                ]);
                
                // Atualizar o valor com os juros
                $payment->update([
                    'status' => 'paid',
                    'data_pagamento' => now(),
                    'valor' => $valorAtualizado,
                    'observacoes' => ($payment->observacoes ? $payment->observacoes . "\n" : '') . 
                                     "Juros de mora aplicados: R$ " . number_format($valorJuros, 2, ',', '.') . 
                                     " (" . $payment->getDaysOverdue() . " dias de atraso)"
                ]);
            } else {
                // Se não está vencido, apenas marcar como pago
                $payment->update([
                    'status' => 'paid',
                    'data_pagamento' => now()
                ]);
            }

            return redirect()->back()->with('success', 'Pagamento marcado como pago com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao marcar pagamento como pago', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Erro ao marcar pagamento como pago: ' . $e->getMessage());
        }
    }

    public function testConnection(Request $request)
    {
        try {
            $accessToken = $request->input('access_token');
            $sandbox = $request->input('sandbox', false);
            
            if (empty($accessToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access token é obrigatório'
                ], 400);
            }
            
            $mercadoPagoService = new MercadoPagoService();
            $result = $mercadoPagoService->testConnection($accessToken, $sandbox);
            
            // Return the result from the service directly
            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 500);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reenviar notificações de pagamento
     */
    public function resendNotifications(Payment $payment)
    {
        try {
            $notificationService = app(PaymentNotificationService::class);
            
            // Reenviar notificações com base no status do pagamento
            if ($payment->status === 'paid') {
                $notificationService->sendPaymentApprovedNotifications($payment);
                $message = 'Notificações de pagamento aprovado reenviadas com sucesso!';
            } else {
                // Para pagamentos pendentes, verificar se tem dados do Mercado Pago
                if (!$payment->mercadopago_data) {
                    Log::info('Tentando gerar dados do Mercado Pago para pagamento sem dados', [
                        'payment_id' => $payment->id
                    ]);
                    
                    // Tentar gerar os dados do Mercado Pago
                    $mercadoPagoService = app(MercadoPagoService::class);
                    
                    try {
                        $mpResponse = $mercadoPagoService->createPayment($payment);
                        
                        if ($mpResponse && isset($mpResponse['id'])) {
                            $payment->mercadopago_id = $mpResponse['id'];
                            $payment->mercadopago_data = $mpResponse;
                            $payment->save();
                            
                            Log::info('Dados do Mercado Pago gerados com sucesso', [
                                'payment_id' => $payment->id,
                                'mercadopago_id' => $mpResponse['id']
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Erro ao gerar dados do Mercado Pago durante reenvio', [
                            'payment_id' => $payment->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Reenviar notificações de criação (inclui links de pagamento automaticamente)
                $notificationService->sendPaymentCreatedNotifications($payment);
                
                $message = 'Notificações de pagamento reenviadas com sucesso!';
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Erro ao reenviar notificações de pagamento', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Erro ao reenviar notificações: ' . $e->getMessage());
        }
    }

    /**
     * Download do PDF do boleto
     */
    public function downloadBoleto(Payment $payment)
    {
        try {
            // Verificar se o boleto existe
            if (!$payment->hasBoleto()) {
                return redirect()->back()->with('error', 'Boleto não encontrado para este pagamento.');
            }

            $filePath = $payment->getBoletoPath();
            
            if (!file_exists($filePath)) {
                return redirect()->back()->with('error', 'Arquivo do boleto não encontrado.');
            }

            // Gerar nome do arquivo para download
            $matricula = $payment->matricula;
            $downloadName = 'boleto_' . $payment->id . '_' . ($matricula ? $matricula->nome_completo : 'pagamento') . '.pdf';
            
            // Limpar nome do arquivo
            $downloadName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $downloadName);

            Log::info('Download de boleto solicitado', [
                'payment_id' => $payment->id,
                'user_id' => auth()->id(),
                'file_path' => $filePath
            ]);

            return response()->download($filePath, $downloadName);

        } catch (\Exception $e) {
            Log::error('Erro ao fazer download do boleto', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Erro ao baixar boleto: ' . $e->getMessage());
        }
    }
} 