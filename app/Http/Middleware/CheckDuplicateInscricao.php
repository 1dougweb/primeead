<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Inscricao;
use Symfony\Component\HttpFoundation\Response;

class CheckDuplicateInscricao
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Só verificar se for uma requisição POST para inscrição
        if ($request->isMethod('POST') && $request->routeIs('inscricao.store')) {
            $email = $request->input('email');
            $telefone = $request->input('telefone');
            
            // Verificar se já existe uma inscrição com este email ou telefone
            $existingInscricao = Inscricao::where('email', $email)
                ->orWhere('telefone', $telefone)
                ->first();
            
            if ($existingInscricao) {
                // Determinar qual campo está duplicado
                $duplicateField = '';
                $message = '';
                
                if ($existingInscricao->email === $email && $existingInscricao->telefone === $phone) {
                    $duplicateField = 'both';
                    $message = 'Você já se inscreveu em breve entraremos em contato';
                } elseif ($existingInscricao->email === $email) {
                    $duplicateField = 'email';
                    $message = 'Você já se inscreveu em breve entraremos em contato';
                } else {
                    $duplicateField = 'telefone';
                    $message = 'Você já se inscreveu em breve entraremos em contato';
                }
                
                // Retornar erro com detalhes da inscrição existente
                return response()->json([
                    'error' => 'Inscrição duplicada',
                    'message' => $message,
                    'field' => $duplicateField,
                    'existing_contact' => [
                        'nome' => $existingInscricao->nome,
                        'email' => $existingInscricao->email,
                        'telefone' => $existingInscricao->telefone,
                        'curso' => $existingInscricao->curso,
                        'modalidade' => $existingInscricao->modalidade,
                        'created_at' => $existingInscricao->created_at->format('d/m/Y H:i')
                    ]
                ], 422);
            }
        }
        
        return $next($request);
    }
}
