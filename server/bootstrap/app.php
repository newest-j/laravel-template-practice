<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // php artisan install:api
        // this is this to install the route.api and the auth:sanctumn in the auth.php

        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Web group (use Illuminate middleware, not App\ stubs)
        $middleware->web([
            // in laravel 12 we do not define our middleware in the kernel.php but here in the bootstap/app.php
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // API group (Sanctum + throttle + bindings)
        $middleware->api([
            // this to ensure that the api route get the session cookie
            // stateful in this auth here is different where it is session cookie and 
            // stateless is token bearer
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            // this is use to turn route parameter into object before controller runs
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $_): void {
        // no-op
    })
    ->create();
