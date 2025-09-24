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
    $locationId = request('location_id');
    return view('config', compact('locationId'));
});

// Installation status pages
Route::get('/install-success', function () {
    $locationId = request('location_id');
    return view('install-success', compact('locationId'));
});

Route::get('/install-failure', function () {
    $error = request('error', 'Installation failed');
    $locationId = request('location_id');
    return view('install-failure', compact('error', 'locationId'));
});

// GHL OAuth callback (new simplified URL)
Route::get('/oauth/callback', [GHLController::class, 'handleOAuthCallback']);

// Payment pages
Route::get('/payment/{billCode}', [PaymentController::class, 'showPaymentPage']);
Route::get('/payment/{billCode}/status', [PaymentController::class, 'showPaymentStatus']);
Route::get('/payment/{billCode}/success', [PaymentController::class, 'handlePaymentReturn']);
Route::get('/payment/{billCode}/failed', [PaymentController::class, 'handlePaymentReturn']);
Route::get('/payment/return', [PaymentController::class, 'handlePaymentReturn']);
