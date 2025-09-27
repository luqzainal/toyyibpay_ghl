<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\GHLController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Homepage
Route::get('/', function () {
    return view('homepage');
});

// Configuration pages
Route::get('/config', function () {
    // Try both parameter formats (location_id and locationid)
    $locationId = request('location_id') ?? request('locationid') ?? request('locationId');

    // If no location_id is provided, show error
    if (!$locationId) {
        return response()->view('config', [
            'location_id' => null,
            'error' => 'No location ID provided. This page must be accessed from GHL Sub-Account.'
        ], 400);
    }

    return view('config', ['location_id' => $locationId]);
});

// Installation status pages
Route::get('/install-success', function () {
    $locationId = session('location_id', request('location_id'));
    $message = session('message', 'Integration installed successfully!');
    return view('install-success', compact('locationId', 'message'));
});

Route::get('/install-failure', function () {
    $error = session('error', request('error', 'Installation failed'));
    $locationId = session('location_id', request('location_id'));
    return view('install-failure', compact('error', 'locationId'));
});

// GHL OAuth callback (new simplified URL)
Route::get('/oauth/callback', [GHLController::class, 'handleOAuthCallback']);

// GHL Integration endpoints
Route::get('/ghl/integration/config', [GHLController::class, 'getIntegrationConfig']);
Route::post('/ghl/integration/register', [GHLController::class, 'autoRegisterIntegration']);
Route::get('/ghl/integration/register', [GHLController::class, 'autoRegisterIntegration']);

// Payment pages
Route::get('/payment/{billCode}', [PaymentController::class, 'showPaymentPage']);
Route::get('/payment/{billCode}/status', [PaymentController::class, 'showPaymentStatus']);
Route::get('/payment/{billCode}/success', [PaymentController::class, 'handlePaymentReturn']);
Route::get('/payment/{billCode}/failed', [PaymentController::class, 'handlePaymentReturn']);
Route::get('/payment/return', [PaymentController::class, 'handlePaymentReturn']);
