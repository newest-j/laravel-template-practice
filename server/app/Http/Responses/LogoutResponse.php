<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

// i do not need to distroy the seesion fortify does that
class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Logged out',
        ], 200);
    }
}
