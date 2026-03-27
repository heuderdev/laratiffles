<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Services\Itau\Cnab240Cnab400ItauParserService;
use App\Services\Itau\ItauParserCnab400Service;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Cnab240Cnab400ItauParserService::class);
        $this->app->singleton(ItauParserCnab400Service::class);
    }

    public function boot(): void
    {
        Cashier::useCustomerModel(Tenant::class);
        // Cashier::calculateTaxes();
    }
}
