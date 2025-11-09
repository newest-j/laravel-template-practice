<?php



// this is for both the mobile and the spa where the shared route  can be call_user_method
// it it is token or crsf and session with the auth:sanctum

// for the web route this is for the spa where it only invoves session cookies and the crsf 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->only(['id', 'name', 'email', 'email_verified_at']);
});
