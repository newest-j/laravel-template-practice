<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        // Do the rotation here, after Fortify logs the user in.
        // regenerate session here fortify doesn't do it for register
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'user' => $request->user()->only(['id', 'name', 'email', 'email_verified_at']),
        ], 201);
    }
}
