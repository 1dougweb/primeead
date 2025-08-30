<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscricao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function checkExistingContact(Request $request)
    {
        // Validar os dados de entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exists' => false,
                'error' => 'Dados inválidos'
            ], 400);
        }

        $email = $request->input('email');
        $phone = $request->input('phone');

        // Verificar se já existe uma inscrição com este email ou telefone
        $existingContact = Inscricao::where('email', $email)
            ->orWhere('telefone', $phone)
            ->first();

        if ($existingContact) {
            // Determinar qual campo está duplicado
            $duplicateField = '';
            $message = '';
            
            if ($existingContact->email === $email && $existingContact->telefone === $phone) {
                $duplicateField = 'both';
                $message = 'Você já se inscreveu em breve entraremos em contato';
            } elseif ($existingContact->email === $email) {
                $duplicateField = 'email';
                $message = 'Este email já está cadastrado em nosso sistema.';
            } else {
                $duplicateField = 'phone';
                $message = 'Este telefone já está cadastrado em nosso sistema.';
            }

            return response()->json([
                'exists' => true,
                'field' => $duplicateField,
                'message' => $message,
                'contact' => [
                    'nome' => $existingContact->nome,
                    'email' => $existingContact->email,
                    'telefone' => $existingContact->telefone,
                    'curso' => $existingContact->curso,
                    'modalidade' => $existingContact->modalidade,
                    'created_at' => $existingContact->created_at->format('d/m/Y H:i')
                ]
            ]);
        }

        return response()->json([
            'exists' => false,
            'message' => 'Contato disponível para inscrição'
        ]);
    }
} 