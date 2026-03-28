<?php

namespace App\Services;

use Illuminate\Http\RedirectResponse;
use Laravel\Cashier\Http\RedirectToCheckoutResponse;
use Throwable;

class StripeBillingService
{
    public function startCheckout(object $tenant): RedirectToCheckoutResponse|RedirectResponse
    {
        $priceId = config('services.stripe.price_id');

        abort_unless($priceId, 500, 'Stripe Price ID não configurado.');
        abort_unless($tenant, 404, 'Tenant não encontrado.');

        if ($tenant->hasActiveBilling()) {
            return redirect()->route('portal');
        }

        $this->prepareCustomer($tenant);

        return $tenant
            ->newSubscription('default', $priceId)
            ->trialDays(1)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success'),
                'cancel_url' => route('billing.cancel'),
                'locale' => 'pt-BR',
            ]);
    }

    public function redirectToPortal(object $tenant): RedirectResponse
    {
        abort_unless($tenant, 404, 'Tenant não encontrado.');

        $this->prepareCustomer($tenant);

        return $tenant->redirectToBillingPortal(route('billing.success'));
    }

    private function prepareCustomer(object $tenant): void
    {
        try {
            $tenant->createOrGetStripeCustomer();
            $tenant->syncStripeCustomerDetails();
        } catch (Throwable $e) {
            report($e);

            abort(500, 'Não foi possível sincronizar o cliente com o Stripe.');
        }
    }
}
