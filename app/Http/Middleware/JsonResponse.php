<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        // Força o Accept header para JSON
        $request->headers->set('Accept', 'application/json');

        // Pega a resposta
        $response = $next($request);

        // Se a resposta não é JSON e é um erro, converte para JSON
        if (!$response->headers->has('Content-Type') || 
            !str_contains($response->headers->get('Content-Type'), 'application/json')) {
            
            $status = $response->getStatusCode();
            if ($status >= 400) {
                $content = $response->getContent();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erro interno do servidor',
                    'details' => strip_tags($content)
                ], $status);
            }
        }

        return $response;
    }
} 