<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.set' => \App\Http\Middleware\SetCurrentTenant::class,
            'tenant.active' => \App\Http\Middleware\EnsureTenantSubscriptionIsActive::class,
            'default.tenant' => \App\Http\Middleware\EnsureUserHasDefaultTenant::class,
            'verify.assinatura' => \App\Http\Middleware\EnsureTenantOwnerAndPaid::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/stripe/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {})->create();
