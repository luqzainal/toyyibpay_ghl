<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| GHL Integration API Routes
|--------------------------------------------------------------------------
|
| These routes handle the integration with GoHighLevel platform including
| OAuth flow, webhook handling, and provider registration.
|
*/

Route::prefix('ghl')->group(function () {
    // Provider Registration Routes (no auth needed for initial setup)
    Route::post('/provider/register', [\App\Http\Controllers\GHLController::class, 'registerProvider']);
    
    // Routes that require GHL authentication
    Route::middleware('ghl.auth')->group(function () {
        Route::post('/connect-keys', [\App\Http\Controllers\GHLController::class, 'connectKeys']);
        Route::post('/webhook/install', [\App\Http\Controllers\GHLController::class, 'handleInstall']);
        Route::post('/webhook/uninstall', [\App\Http\Controllers\GHLController::class, 'handleUninstall']);
        Route::post('/payment/status', [\App\Http\Controllers\GHLController::class, 'updatePaymentStatus']);
        Route::get('/query', [\App\Http\Controllers\GHLController::class, 'queryEndpoint']);
    });
});

/*
|--------------------------------------------------------------------------
| ToyyibPay Integration API Routes
|--------------------------------------------------------------------------
|
| These routes handle ToyyibPay payment processing including webhook
| callbacks and payment status updates.
|
*/

Route::prefix('toyyibpay')->group(function () {
    // Webhook callback from ToyyibPay (no auth needed as it comes from external service)
    Route::post('/webhook/callback', [\App\Http\Controllers\WebhookController::class, 'handleToyyibPayCallback']);
    
    // Payment processing routes
    Route::post('/create-payment', [\App\Http\Controllers\ToyyibPayController::class, 'createPayment']);
    Route::get('/payment-status/{billcode}', [\App\Http\Controllers\ToyyibPayController::class, 'getPaymentStatus']);
    
    // Configuration routes
    Route::post('/validate-key', [\App\Http\Controllers\ToyyibPayController::class, 'validateApiKey']);
    Route::post('/config', [\App\Http\Controllers\ConfigController::class, 'saveConfiguration']);
    Route::get('/config/{locationId}', [\App\Http\Controllers\ConfigController::class, 'getConfiguration']);
});

/*
|--------------------------------------------------------------------------
| Payment Interface Routes
|--------------------------------------------------------------------------
|
| These routes handle payment page interactions and status updates.
|
*/

Route::prefix('payment')->group(function () {
    // Payment status refresh
    Route::post('/status/{billCode}/refresh', [\App\Http\Controllers\PaymentController::class, 'refreshPaymentStatus']);
    Route::get('/status/{billCode}', [\App\Http\Controllers\ToyyibPayController::class, 'getPaymentStatus']);
    
    // Payment tracking (for analytics)
    Route::post('/track', function(\Illuminate\Http\Request $request) {
        // Simple tracking endpoint for payment events
        \Illuminate\Support\Facades\Log::channel('payment_webhooks')->info('Payment event tracked', [
            'event' => $request->input('event'),
            'transaction_id' => $request->input('transaction_id'),
            'bill_code' => $request->input('bill_code'),
            'timestamp' => now()->toISOString(),
        ]);
        
        return response()->json(['success' => true]);
    });
});

/*
|--------------------------------------------------------------------------
| Webhook Testing and Debug Routes
|--------------------------------------------------------------------------
|
| These routes are for testing and debugging webhook functionality.
|
*/

Route::prefix('webhook')->group(function () {
    // Test webhook endpoint
    Route::post('/test', [\App\Http\Controllers\WebhookController::class, 'handleTestWebhook']);
    
    // Webhook logs (for debugging)
    Route::get('/logs', [\App\Http\Controllers\WebhookController::class, 'getWebhookLogs']);
    
    // Health check
    Route::get('/health', [\App\Http\Controllers\WebhookController::class, 'healthCheck']);
});
