<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminAccess::class,
            'banned.ip' => \App\Http\Middleware\CheckBannedIp::class,
        ]);

        // Apply security middleware globally
        $middleware->web(append: [
            \App\Http\Middleware\CheckBannedIp::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Trust proxies for HTTPS behind load balancer
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
