<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebhookTestController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\BoletoSecondViaController;
use App\Http\Controllers\MercadoPagoPaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rota para verificar se um contato já existe
Route::post('/check-existing-contact', [ContactController::class, 'checkExistingContact']);

// Rotas do chat
Route::prefix('chat')->group(function () {
    Route::post('process-message', [ChatController::class, 'processMessage']);
    Route::get('history', [ChatController::class, 'getHistory']);
    Route::post('close-conversation', [ChatController::class, 'closeConversation']);
    Route::get('generate-session', [ChatController::class, 'generateSessionId']);
    Route::get('test-connection', [ChatController::class, 'testConnection']);
});

// Rotas para segunda via de boleto
Route::prefix('boleto')->group(function () {
    Route::post('check-eligibility', [BoletoSecondViaController::class, 'checkEligibility']);
    Route::post('generate-second-via', [BoletoSecondViaController::class, 'generateSecondVia']);
    Route::get('vias-history', [BoletoSecondViaController::class, 'getViasHistory']);
    Route::get('stats', [BoletoSecondViaController::class, 'getStats']);
    Route::post('cancel-via', [BoletoSecondViaController::class, 'cancelVia']);
    Route::post('reactivate-via', [BoletoSecondViaController::class, 'reactivateVia']);
});

// Rotas para links de pagamento do Mercado Pago
Route::prefix('mercadopago')->group(function () {
    Route::get('payment-link', [MercadoPagoPaymentController::class, 'generatePaymentLink']);
});

// Google Drive Sync Routes foram movidas para web.php para funcionar com autenticação de sessão


 