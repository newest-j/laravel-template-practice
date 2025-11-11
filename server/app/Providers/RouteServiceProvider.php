<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// ...existing code...
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class RouteServiceProvider extends ServiceProvider
{
    // ...existing code...

    public function boot(): void
    {
        // ...existing code...

        // Define the 'api' rate limiter used by the API middleware group
        RateLimiter::for('api', function (Request $request) {
            return [
                Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip()),
            ];
        });
    }
}
