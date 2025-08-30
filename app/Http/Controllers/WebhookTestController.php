<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WebhookTestController extends Controller
{
    /**
     * Test webhook endpoint
     */
    public function test(Request $request)
    {
        Log::info('Webhook test endpoint accessed', [
            'method' => $request->method(),
            'url' => $request->url(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all()
        ]);

        return response()->json([
            'status' => 'success',
            'timestamp' => now(),
            'environment' => app()->environment(),
            'message' => 'Webhook test endpoint is working',
            'webhook_url' => url('/webhook/mercadopago'),
            'debug_url' => url('/webhook/debug'),
            'request_info' => [
                'method' => $request->method(),
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => $request->headers->all()
            ]
        ]);
    }

    /**
     * Debug webhook endpoint
     */
    public function debug(Request $request)
    {
        Log::info('Webhook debug endpoint accessed', [
            'method' => $request->method(),
            'url' => $request->url(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ]);

        return response()->json([
            'status' => 'debug',
            'timestamp' => now(),
            'message' => 'Webhook debug endpoint is working',
            'request' => [
                'method' => $request->method(),
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'query' => $request->query(),
                'files' => $request->allFiles()
            ]
        ]);
    }

    /**
     * Test Mercado Pago connectivity
     */
    public function testMercadoPago()
    {
        try {
            // Test basic connectivity to Mercado Pago
            $response = Http::timeout(10)->get('https://api.mercadopago.com/health');
            
            return response()->json([
                'status' => 'success',
                'mercadopago_connectivity' => $response->successful(),
                'mercadopago_status' => $response->status(),
                'mercadopago_response' => $response->body(),
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect to Mercado Pago',
                'error' => $e->getMessage(),
                'timestamp' => now()
            ], 500);
        }
    }
}
