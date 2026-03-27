<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Log as LogModel;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Checkout;

class TenantBillingService
{
    // -------------------------------------------------------
    // SUBSCRIPTION — Recorrência via Stripe Checkout
    // -------------------------------------------------------

    public function checkoutSubscription(
        Tenant $tenant,
        string $priceId,
        string $successUrl,
        string $cancelUrl
    ): Checkout {
        $tenant->createOrGetStripeCustomer();

        return $tenant->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => $successUrl,
                'cancel_url'  => $cancelUrl,
                'metadata'    => [
                    'tenant_id' => $tenant->id,
                    'type'      => 'subscription',
                ],
            ]);
    }

    public function isCanceled(Tenant $tenant): bool
    {
        $subscription = $tenant->subscription('default');

        if (! $subscription) {
            return false;
        }

        return $subscription->canceled();
    }

    // -------------------------------------------------------
    // ONE-TIME CHARGE — Compra Avulsa via Stripe Checkout
    // -------------------------------------------------------

    public function checkoutOnce(
        Tenant $tenant,
        string $priceId,
        int $quantity = 1,
        string $successUrl = '',
        string $cancelUrl = ''
    ): Checkout {
        $tenant->createOrGetStripeCustomer();

        return $tenant->checkout([$priceId => $quantity], [
            'success_url' => $successUrl ?: route('billing.success'),
            'cancel_url'  => $cancelUrl  ?: route('billing.cancel'),
            'metadata'    => [
                'tenant_id' => $tenant->id,
                'type'      => 'one_time',
            ],
        ]);
    }

    // -------------------------------------------------------
    // CRÉDITOS — Valor livre via Stripe Checkout
    // Cria um price dinâmico no Stripe para o valor escolhido
    // -------------------------------------------------------

    public function checkoutCredits(
        Tenant $tenant,
        int $amountInCents,
        string $successUrl = '',
        string $cancelUrl = ''
    ): Checkout {
        if ($amountInCents < 500) {
            throw new \InvalidArgumentException(
                'Valor mínimo para compra de créditos é R$ 5,00.'
            );
        }

        $tenant->createOrGetStripeCustomer();

        $credits = $this->calculateCredits($amountInCents);

        // Cria price dinâmico (ad-hoc) no Stripe
        return $tenant->checkout([
            [
                'price_data' => [
                    'currency'     => config('cashier.currency', 'brl'),
                    'unit_amount'  => $amountInCents,
                    'product_data' => [
                        'name'        => 'Créditos',
                        'description' => "{$credits} créditos para {$tenant->name}",
                    ],
                ],
                'quantity' => 1,
            ],
        ], [
            'success_url' => $successUrl ?: route('billing.success'),
            'cancel_url'  => $cancelUrl  ?: route('billing.cancel'),
            'metadata'    => [
                'tenant_id' => $tenant->id,
                'type'      => 'credits',
                'credits'   => $credits,
                'amount'    => $amountInCents,
            ],
        ]);
    }

    // -------------------------------------------------------
    // PORTAL — Gerenciar assinatura/pagamento pelo Stripe
    // -------------------------------------------------------

    // Redireciona para o portal do Stripe
    // (trocar cartão, cancelar, baixar faturas)
    public function billingPortalUrl(Tenant $tenant, string $returnUrl = ''): string
    {
        $tenant->createOrGetStripeCustomer();

        return $tenant->billingPortalUrl(
            $returnUrl ?: route('dashboard')
        );
    }

    // -------------------------------------------------------
    // WEBHOOK HANDLERS — Chamados pelo StripeWebhookController
    // -------------------------------------------------------

    // checkout.session.completed
    public function handleCheckoutCompleted(array $payload): void
    {
        $session  = $payload['data']['object'];
        $metadata = $session['metadata'] ?? [];
        $tenantId = $metadata['tenant_id'] ?? null;

        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        match ($metadata['type'] ?? '') {
            'credits'      => $this->addCredits($tenant, (int) ($metadata['credits'] ?? 0)),
            'subscription' => Log::info('Subscription checkout completado', ['tenant_id' => $tenantId]),
            'one_time'     => Log::info('Compra avulsa completada', ['tenant_id' => $tenantId]),
            default        => null,
        };
    }

    // customer.subscription.deleted
    public function handleSubscriptionCanceled(array $payload): void
    {
        $stripeId = $payload['data']['object']['customer'] ?? null;
        if (!$stripeId) return;

        $tenant = Tenant::where('stripe_id', $stripeId)->first();
        if (!$tenant) return;

        Log::warning('Assinatura cancelada via webhook', ['tenant_id' => $tenant->id]);
    }

    // -------------------------------------------------------
    // CRÉDITOS — Saldo interno
    // -------------------------------------------------------

    public function addCredits(Tenant $tenant, int $credits): void
    {
        $tenant->increment('credits_balance', $credits);

        Log::info('Créditos adicionados via webhook', [
            'tenant_id' => $tenant->id,
            'credits'   => $credits,
            'balance'   => $tenant->fresh()->credits_balance,
        ]);
    }

    public function useCredits(Tenant $tenant, int $credits, string $reason): bool
    {
        if ($tenant->credits_balance < $credits) {
            throw new \RuntimeException(
                "Saldo insuficiente. Disponível: {$tenant->credits_balance} créditos."
            );
        }

        $tenant->decrement('credits_balance', $credits);

        Log::info('Créditos utilizados', [
            'tenant_id' => $tenant->id,
            'credits'   => $credits,
            'reason'    => $reason,
        ]);

        return true;
    }

    public function creditsBalance(Tenant $tenant): int
    {
        return $tenant->credits_balance ?? 0;
    }

    private function calculateCredits(int $amountInCents): int
    {
        $reais = $amountInCents / 100;

        return match (true) {
            $reais >= 500 => (int) ($reais * 1.30),
            $reais >= 200 => (int) ($reais * 1.20),
            $reais >= 100 => (int) ($reais * 1.10),
            default       => (int) $reais,
        };
    }

    // -------------------------------------------------------
    // STATUS / INVOICES
    // -------------------------------------------------------

    public function isActive(Tenant $tenant): bool
    {
        return $tenant->subscribed('default') || $tenant->onTrial();
    }

    public function isOnGracePeriod(Tenant $tenant): bool
    {
        return $tenant->subscription('default')?->onGracePeriod() ?? false;
    }

    public function invoices(Tenant $tenant)
    {
        return $tenant->invoices();
    }

    public function downloadInvoice(Tenant $tenant, string $invoiceId)
    {
        return $tenant->downloadInvoice($invoiceId, [
            'vendor' => config('app.name'),
        ]);
    }
}
