<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\BoletoSecondViaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class BoletoSecondViaController extends Controller
{
    protected $boletoService;

    public function __construct(BoletoSecondViaService $boletoService)
    {
        $this->boletoService = $boletoService;
    }

    /**
     * Verificar se pode gerar segunda via
     */
    public function checkEligibility(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'payment_id' => 'required|integer|exists:payments,id'
            ]);

            $payment = Payment::with('matricula')->findOrFail($request->payment_id);
            
            $eligibility = $this->boletoService->canGenerateSecondVia($payment);
            
            return response()->json([
                'success' => true,
                'eligibility' => $eligibility
            ]);
            
        } catch (Exception $e) {
            Log::error('Erro ao verificar elegibilidade para segunda via', [
                'payment_id' => $request->payment_id ?? null,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao verificar elegibilidade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar segunda via do boleto
     */
    public function generateSecondVia(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'payment_id' => 'required|integer|exists:payments,id',
                'new_due_date' => 'nullable|date|after:today'
            ]);

            $payment = Payment::with('matricula')->findOrFail($request->payment_id);
            
            // Verificar elegibilidade
            $eligibility = $this->boletoService->canGenerateSecondVia($payment);
            if (!$eligibility['can_generate']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Não é possível gerar segunda via: ' . implode(', ', $eligibility['reasons']),
                    'eligibility' => $eligibility
                ], 400);
            }
            
            // Nova data de vencimento (opcional)
            $newDueDate = null;
            if ($request->new_due_date) {
                $newDueDate = \Carbon\Carbon::parse($request->new_due_date);
            }
            
            // Gerar segunda via
            $result = $this->boletoService->generateSecondVia($payment, $newDueDate);
            
            return response()->json([
                'success' => true,
                'message' => 'Segunda via gerada com sucesso!',
                'boleto_via' => $result['boleto_via'],
                'boleto_data' => $result['boleto_data'],
                'via_number' => $result['via_number']
            ]);
            
        } catch (Exception $e) {
            Log::error('Erro ao gerar segunda via do boleto', [
                'payment_id' => $request->payment_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao gerar segunda via: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter histórico de vias de um pagamento
     */
    public function getViasHistory(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'payment_id' => 'required|integer|exists:payments,id'
            ]);

            $payment = Payment::findOrFail($request->payment_id);
            
            $history = $this->boletoService->getBoletoViasHistory($payment);
            
            return response()->json([
                'success' => true,
                'history' => $history,
                'payment_info' => [
                    'id' => $payment->id,
                    'descricao' => $payment->descricao,
                    'valor' => $payment->valor,
                    'status' => $payment->status,
                    'boleto_vias_count' => $payment->boleto_vias_count,
                    'max_boleto_vias' => $payment->max_boleto_vias
                ]
            ]);
            
        } catch (Exception $e) {
            Log::error('Erro ao obter histórico de vias', [
                'payment_id' => $request->payment_id ?? null,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao obter histórico: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas de vias de boleto
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->boletoService->getBoletoViasStats();
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            Log::error('Erro ao obter estatísticas de vias', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar uma via específica
     */
    public function cancelVia(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'via_id' => 'required|integer|exists:boleto_vias,id'
            ]);

            $boletoVia = \App\Models\BoletoVia::findOrFail($request->via_id);
            
            // Verificar se pode cancelar
            if (!$boletoVia->isActive()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Não é possível cancelar uma via que não está ativa'
                ], 400);
            }
            
            // Cancelar a via
            $boletoVia->cancel();
            
            return response()->json([
                'success' => true,
                'message' => 'Via cancelada com sucesso!',
                'via' => [
                    'id' => $boletoVia->id,
                    'via_number' => $boletoVia->via_number_formatted,
                    'status' => $boletoVia->status_formatted
                ]
            ]);
            
        } catch (Exception $e) {
            Log::error('Erro ao cancelar via', [
                'via_id' => $request->via_id ?? null,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao cancelar via: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reativar uma via cancelada
     */
    public function reactivateVia(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'via_id' => 'required|integer|exists:boleto_vias,id'
            ]);

            $boletoVia = \App\Models\BoletoVia::findOrFail($request->via_id);
            
            // Verificar se pode reativar
            if (!$boletoVia->isCancelled()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Apenas vias canceladas podem ser reativadas'
                ], 400);
            }
            
            // Verificar se não há outras vias ativas
            $activeVias = \App\Models\BoletoVia::where('payment_id', $boletoVia->payment_id)
                ->where('status', 'active')
                ->count();
                
            if ($activeVias > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Não é possível reativar: já existe uma via ativa para este pagamento'
                ], 400);
            }
            
            // Reativar a via
            $boletoVia->update(['status' => 'active']);
            
            return response()->json([
                'success' => true,
                'message' => 'Via reativada com sucesso!',
                'via' => [
                    'id' => $boletoVia->id,
                    'via_number' => $boletoVia->via_number_formatted,
                    'status' => $boletoVia->status_formatted
                ]
            ]);
            
        } catch (Exception $e) {
            Log::error('Erro ao reativar via', [
                'via_id' => $request->via_id ?? null,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao reativar via: ' . $e->getMessage()
            ], 500);
        }
    }
}
