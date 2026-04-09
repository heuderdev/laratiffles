<?php

use App\Http\Middleware\EnsureTenantEmployeeHasActiveTenant;
use App\Http\Middleware\EnsureTenantOwnerHasActiveBilling;
use App\Http\Middleware\SetCurrentTenant;
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
            'tenant' => SetCurrentTenant::class,
            'tenant.billing.owner' => EnsureTenantOwnerHasActiveBilling::class,
            'tenant.employee.active' => EnsureTenantEmployeeHasActiveTenant::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {})->create();
