<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Services\Itau\Cnab240Cnab400ItauParserService;
use App\Services\Itau\ItauParserCnab400Service;
use App\Services\TenantBillingService;
use App\Services\TenantContextService;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Cnab240Cnab400ItauParserService::class);
        $this->app->singleton(ItauParserCnab400Service::class);
        $this->app->singleton(TenantContextService::class);
        $this->app->singleton(TenantBillingService::class);
    }

    public function boot(): void
    {
        Cashier::useCustomerModel(Tenant::class);
    }
}
