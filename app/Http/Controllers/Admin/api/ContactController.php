<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inscricao;

class ContactController extends Controller
{
    /**
     * Verifica se um contato já existe com o mesmo email ou telefone
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkExistingContact(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string'
        ]);

        $email = $request->email;
        $phone = preg_replace('/\D/', '', $request->phone);

        // Verificar se existe inscrição com o mesmo email
        $existingEmail = Inscricao::where('email', $email)->first();
        if ($existingEmail) {
            return response()->json([
                'exists' => true,
                'field' => 'email',
                'message' => 'Este email já está cadastrado em nosso sistema. Um de nossos consultores entrará em contato em breve.'
            ]);
        }

        // Verificar se existe inscrição com o mesmo telefone (limpo de caracteres não numéricos)
        $existingPhone = Inscricao::where('telefone', 'like', '%' . $phone . '%')->first();
        if ($existingPhone) {
            return response()->json([
                'exists' => true,
                'field' => 'phone',
                'message' => 'Este telefone já está cadastrado em nosso sistema. Um de nossos consultores entrará em contato em breve.'
            ]);
        }

        // Se não existe, retornar false
        return response()->json([
            'exists' => false
        ]);
    }
} 