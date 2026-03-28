<?php

namespace App\Http\Controllers;

use App\Services\StripeBillingService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Laravel\Cashier\Http\RedirectToCheckoutResponse;

class BillingController extends Controller
{
    public function __construct(private readonly StripeBillingService $stripeBillingService) {}

    public function checkout(Request $request): RedirectToCheckoutResponse|RedirectResponse
    {
        $tenant = $this->resolveTenant($request);

        return $this->stripeBillingService->startCheckout($tenant);
    }

    public function portal(Request $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request);

        return $this->stripeBillingService->redirectToPortal($tenant);
    }

    private function resolveTenant(Request $request): object
    {
        $tenant = $request->user()?->defaultTenant;

        abort_unless($tenant, 404, 'Tenant não encontrado.');

        return $tenant;
    }
}
