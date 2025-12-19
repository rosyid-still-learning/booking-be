<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /**
         * =====================================================
         * ✅ WAJIB: AKTIFKAN CORS (UNTUK FRONTEND → RAILWAY)
         * =====================================================
         */
        $middleware->append(
            \Illuminate\Http\Middleware\HandleCors::class
        );

        /**
         * =====================================================
         * ✅ ALIAS MIDDLEWARE CUSTOM
         * =====================================================
         */
        $middleware->alias([
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
