<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // Broadcasting channels + their JWT-authenticated auth route are
        // registered by App\Providers\BroadcastServiceProvider.
        then: function (): void {
            // Versioned, stateless REST API for the mobile client.
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The API is stateless; JWT carries identity. Force JSON on every API request.
        $middleware->api(prepend: [
            \App\Modules\Shared\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'jwt.auth'    => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.refresh' => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\RefreshToken::class,
            'auth.jwt'    => \App\Modules\Shared\Http\Middleware\JwtAuthenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Every API failure is rendered as a consistent JSON envelope.
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e): bool {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! ($request->is('api/*') || $request->expectsJson())) {
                return null;
            }

            return \App\Modules\Shared\Http\ApiResponse::fromException($e);
        });
    })->create();
