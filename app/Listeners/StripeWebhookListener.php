<?php

namespace App\Listeners;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;

class StripeWebhookListener
{
    public function handle(WebhookReceived $event): void
    {
        $type = (string) data_get($event->payload, 'type', '');
        $data = (array) data_get($event->payload, 'data.object', []);

        Log::info('[stripe:webhook] received', [
            'type' => $type,
            'id' => data_get($event->payload, 'id'),
        ]);

        match ($type) {
            'checkout.session.completed'        => $this->handleCheckoutSessionCompleted($data),
            'customer.subscription.created'    => $this->handleSubscriptionCreated($data),
            'customer.subscription.updated'    => $this->handleSubscriptionUpdated($data),
            'customer.subscription.deleted'    => $this->handleSubscriptionDeleted($data),
            'invoice.payment_succeeded'        => $this->handleInvoicePaymentSucceeded($data),
            'invoice.payment_action_required'  => $this->handleInvoicePaymentActionRequired($data),
            default                            => null,
        };
    }

    private function handleCheckoutSessionCompleted(array $session): void
    {
        if (($session['mode'] ?? null) !== 'subscription') {
            return;
        }

        $customerId = (string) ($session['customer'] ?? '');
        $subscriptionId = (string) ($session['subscription'] ?? '');

        if ($customerId === '') {
            Log::warning('[stripe:webhook] checkout.session.completed sem customer', [
                'session_id' => $session['id'] ?? null,
            ]);
            return;
        }

        $tenant = Tenant::query()->where('stripe_id', $customerId)->first();

        if (! $tenant) {
            Log::warning('[stripe:webhook] tenant não encontrado no checkout.session.completed', [
                'stripe_id' => $customerId,
                'session_id' => $session['id'] ?? null,
            ]);
            return;
        }

        DB::transaction(function () use ($tenant, $session, $subscriptionId): void {
            $tenant->forceFill([
                'is_free' => false,
                'subscription_ends_at' => null,
            ])->save();

            Log::info('[stripe:webhook] checkout.session.completed processado', [
                'tenant_id' => $tenant->id,
                'stripe_id' => $tenant->stripe_id,
                'stripe_subscription_id' => $subscriptionId,
                'session_id' => $session['id'] ?? null,
            ]);
        });
    }

    private function handleSubscriptionCreated(array $subscription): void
    {
        $tenant = $this->findTenantByStripeCustomer($subscription);

        if (! $tenant) {
            return;
        }

        $tenant->forceFill([
            'is_free' => false,
            'subscription_ends_at' => null,
        ])->save();

        Log::info('[stripe:webhook] subscription created', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription['id'] ?? null,
            'status' => $subscription['status'] ?? null,
        ]);
    }

    private function handleSubscriptionUpdated(array $subscription): void
    {
        $tenant = $this->findTenantByStripeCustomer($subscription);

        if (! $tenant) {
            return;
        }

        $status = (string) ($subscription['status'] ?? '');
        $cancelAtPeriodEnd = (bool) ($subscription['cancel_at_period_end'] ?? false);

        $subscriptionEndsAt = null;

        if ($cancelAtPeriodEnd && ! empty($subscription['current_period_end'])) {
            $subscriptionEndsAt = now()->createFromTimestamp((int) $subscription['current_period_end']);
        }

        $tenant->forceFill([
            'is_free' => false,
            'subscription_ends_at' => $subscriptionEndsAt,
        ])->save();

        Log::info('[stripe:webhook] subscription updated', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription['id'] ?? null,
            'status' => $status,
            'cancel_at_period_end' => $cancelAtPeriodEnd,
            'subscription_ends_at' => optional($subscriptionEndsAt)?->toDateTimeString(),
        ]);
    }

    private function handleSubscriptionDeleted(array $subscription): void
    {
        $tenant = $this->findTenantByStripeCustomer($subscription);

        if (! $tenant) {
            return;
        }

        $tenant->forceFill([
            'subscription_ends_at' => now(),
        ])->save();

        Log::warning('[stripe:webhook] subscription deleted', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription['id'] ?? null,
        ]);
    }

    private function handleInvoicePaymentSucceeded(array $invoice): void
    {
        $tenant = $this->findTenantByStripeCustomer($invoice);

        if (! $tenant) {
            return;
        }

        $tenant->forceFill([
            'is_free' => false,
            'subscription_ends_at' => null,
        ])->save();

        Log::info('[stripe:webhook] invoice payment succeeded', [
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice['id'] ?? null,
            'subscription_id' => $invoice['subscription'] ?? null,
        ]);
    }

    private function handleInvoicePaymentActionRequired(array $invoice): void
    {
        $tenant = $this->findTenantByStripeCustomer($invoice);

        if (! $tenant) {
            return;
        }

        Log::warning('[stripe:webhook] invoice payment action required', [
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice['id'] ?? null,
            'subscription_id' => $invoice['subscription'] ?? null,
        ]);
    }

    private function findTenantByStripeCustomer(array $payload): ?Tenant
    {
        $customerId = (string) ($payload['customer'] ?? '');

        if ($customerId === '') {
            Log::warning('[stripe:webhook] payload sem customer', [
                'payload_id' => $payload['id'] ?? null,
            ]);

            return null;
        }

        $tenant = Tenant::query()->where('stripe_id', $customerId)->first();

        if (! $tenant) {
            Log::warning('[stripe:webhook] tenant não encontrado por stripe_id', [
                'stripe_id' => $customerId,
                'payload_id' => $payload['id'] ?? null,
            ]);
        }

        return $tenant;
    }
}
