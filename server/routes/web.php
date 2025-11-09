<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialAuthController;



Route::get('/auth/redirect/{mode}', [SocialAuthController::class, 'redirectToGoogle'])->where('mode', 'login|signup');
Route::get('/auth/callback', [SocialAuthController::class, 'handleGoogleCallback']);
