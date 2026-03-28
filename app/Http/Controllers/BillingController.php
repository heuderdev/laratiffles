<?php

namespace App\Http\Controllers;

use App\Services\StripeBillingService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Laravel\Cashier\Checkout;

class BillingController extends Controller
{
    public function __construct(private readonly StripeBillingService $stripeBillingService) {}

    public function checkout(Request $request): Checkout|RedirectResponse
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
    public function revalidate(Request $request): RedirectResponse
    {
        $tenant = $request->user()?->defaultTenant;

        abort_unless($tenant, 404, 'Tenant não encontrado.');

        $isActive = $this->stripeBillingService->revalidateBilling($tenant);

        if ($isActive) {
            return redirect()->route('dashboard')
                ->with('success', 'Assinatura revalidada com sucesso.');
        }

        return redirect()->route('checkout')
            ->with('warning', 'Ainda não foi possível confirmar a assinatura.');
    }
}
