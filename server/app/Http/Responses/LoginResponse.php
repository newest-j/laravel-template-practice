<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // Session ID is already rotated by Fortify for login; 
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $request->user()->only(['id', 'name', 'email', 'email_verified_at']),
        ], 200);
    }
}
