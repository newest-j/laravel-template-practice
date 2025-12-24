<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Signinwith\SocialAuthController;
use App\Http\Controllers\Payments\FlutterwaveController;


// sign in wtith
Route::get('/auth/redirect/{mode}', [SocialAuthController::class, 'redirectToGoogle'])->where('mode', 'login|signup');
Route::get('/auth/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// payment
// the name here is the name route and is only used by the backend 
// instead of calling the url you call the route name
Route::post('/pay', [FlutterwaveController::class, 'initialize'])->name('flutterwave.pay');
Route::get('/callback', [FlutterwaveController::class, 'callback'])->name('flutterwave.callback');
Route::get('/transaction', [FlutterwaveController::class, 'getUserTransactionDetails']);
// webhook (Flutterwave event notifications)
// Route::post('/webhook/flutterwave', [FlutterwaveController::class, 'webhook'])->name('flutterwave.webhook');
