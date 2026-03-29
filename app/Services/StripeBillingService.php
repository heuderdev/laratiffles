<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Checkout;
use Stripe\Stripe;
use Throwable;

class StripeBillingService
{
    public function startCheckout(Tenant $tenant): Checkout|RedirectResponse
    {
        $priceId = config('services.stripe.price_id');

        abort_unless($priceId, 500, 'Stripe Price ID não configurado.');

        if ($tenant->hasActiveBilling()) {
            return redirect()->route('portal');
        }

        $this->prepareCustomer($tenant);

        return $tenant
            ->newSubscription(Tenant::SUBSCRIPTION_DEFAULT, $priceId)
            // ->trialDays(1)
            // ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success'),
                'cancel_url' => route('billing.cancel'),
                'locale' => 'pt-BR',
            ]);
    }

    public function redirectToPortal(Tenant $tenant): RedirectResponse
    {
        $this->prepareCustomer($tenant);

        return $tenant->redirectToBillingPortal(route('billing.success'));
    }

    public function revalidateBilling(Tenant $tenant): bool
    {
        try {
            $this->prepareCustomer($tenant);

            $stripeSecret = config('services.stripe.secret');

            abort_unless($stripeSecret, 500, 'Stripe secret não configurado.');

            \Stripe\Stripe::setApiKey($stripeSecret);

            if (! $tenant->stripe_id) {
                Log::warning('Tenant sem stripe_id para revalidação.', [
                    'tenant_id' => $tenant->id,
                ]);

                return false;
            }

            $stripeSubscriptions = \Stripe\Subscription::all([
                'customer' => $tenant->stripe_id,
                'status' => 'all',
                'limit' => 10,
            ]);

            foreach ($stripeSubscriptions->data as $stripeSubscription) {
                $localSubscription = $tenant->subscriptions()
                    ->where('stripe_id', $stripeSubscription->id)
                    ->first();

                if (! $localSubscription) {
                    $localSubscription = $tenant->subscriptions()->create([
                        'type' => Tenant::SUBSCRIPTION_DEFAULT,
                        'stripe_id' => $stripeSubscription->id,
                        'stripe_status' => $stripeSubscription->status,
                        'stripe_price' => $stripeSubscription->items->data[0]->price->id ?? null,
                        'quantity' => $stripeSubscription->items->data[0]->quantity ?? 1,
                        'trial_ends_at' => ! empty($stripeSubscription->trial_end)
                            ? now()->createFromTimestamp($stripeSubscription->trial_end)
                            : null,
                        'ends_at' => ! empty($stripeSubscription->ended_at)
                            ? now()->createFromTimestamp($stripeSubscription->ended_at)
                            : null,
                    ]);
                } else {
                    $localSubscription->update([
                        'stripe_status' => $stripeSubscription->status,
                        'stripe_price' => $stripeSubscription->items->data[0]->price->id ?? $localSubscription->stripe_price,
                        'quantity' => $stripeSubscription->items->data[0]->quantity ?? $localSubscription->quantity,
                        'trial_ends_at' => ! empty($stripeSubscription->trial_end)
                            ? now()->createFromTimestamp($stripeSubscription->trial_end)
                            : null,
                        'ends_at' => ! empty($stripeSubscription->ended_at)
                            ? now()->createFromTimestamp($stripeSubscription->ended_at)
                            : null,
                    ]);
                }

                $this->syncSubscriptionItems($localSubscription, $stripeSubscription);
            }

            $tenant->refresh();

            return $tenant->hasActiveBilling();
        } catch (\Throwable $e) {
            report($e);

            Log::error('Falha ao revalidar billing.', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function syncSubscriptionItems(
        \Laravel\Cashier\Subscription $localSubscription,
        \Stripe\Subscription $stripeSubscription
    ): void {
        $localSubscription->items()->delete();

        foreach ($stripeSubscription->items->data as $item) {
            $localSubscription->items()->create([
                'stripe_id' => $item->id,
                'stripe_product' => $item->price->product ?? null,
                'stripe_price' => $item->price->id ?? null,
                'quantity' => $item->quantity ?? 1,
            ]);
        }
    }

    private function prepareCustomer(Tenant $tenant): void
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
