<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CreditsCheckoutRequest;
use App\Http\Requests\Billing\OnceCheckoutRequest;
use App\Http\Requests\Billing\SubscriptionCheckoutRequest;
use App\Http\Requests\Billing\SwapPlanRequest;
use App\Services\TenantBillingService;
use App\Services\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(
        protected TenantBillingService $billing,
        protected TenantContextService $tenantContext
    ) {
        dd('x');
    }



    // GET /api/billing/status
    public function status(): JsonResponse
    {
        $tenant = $this->tenantContext->currentTenant();

        return response()->json([
            'is_active'       => $this->billing->isActive($tenant),
            'is_on_grace'     => $this->billing->isOnGracePeriod($tenant),
            'is_canceled'     => $this->billing->isCanceled($tenant),
            'credits_balance' => $this->billing->creditsBalance($tenant),
            'subscription'    => $tenant->subscription('default'),
        ]);
    }

    // POST /api/billing/subscription/checkout
    public function subscriptionCheckout(SubscriptionCheckoutRequest  $request)
    {
        $tenant   = $this->tenantContext->currentTenant();
        $checkout = $this->billing->checkoutSubscription(
            $tenant,
            $request->price_id,
            route('billing.success'),
            route('billing.cancel')
        );

        return redirect($checkout->url);
    }

    // POST /api/billing/subscription/cancel
    public function cancelSubscription(): JsonResponse
    {
        $tenant  = $this->tenantContext->currentTenant();
        $canceled = $this->billing->cancelSubscription($tenant);

        if (!$canceled) {
            return response()->json(['message' => 'Nenhuma assinatura ativa.'], 422);
        }

        return response()->json(['message' => 'Assinatura cancelada ao fim do período.']);
    }

    // POST /api/billing/subscription/resume
    public function resumeSubscription(): JsonResponse
    {
        $tenant  = $this->tenantContext->currentTenant();
        $resumed = $this->billing->resumeSubscription($tenant);

        if (!$resumed) {
            return response()->json(['message' => 'Nenhuma assinatura para reativar.'], 422);
        }

        return response()->json(['message' => 'Assinatura reativada com sucesso.']);
    }

    // POST /api/billing/subscription/swap
    public function swapPlan(SwapPlanRequest  $request): JsonResponse
    {
        $tenant       = $this->tenantContext->currentTenant();
        $subscription = $this->billing->swapPlan($tenant, $request->price_id);

        return response()->json([
            'message'      => 'Plano alterado com sucesso.',
            'subscription' => $subscription,
        ]);
    }

    // POST /api/billing/credits/checkout
    public function creditsCheckout(CreditsCheckoutRequest  $request): JsonResponse
    {
        $tenant   = $this->tenantContext->currentTenant();
        $checkout = $this->billing->checkoutCredits(
            $tenant,
            $request->amount,
            route('billing.success'),
            route('billing.cancel')
        );

        return response()->json(['url' => $checkout->url]);
    }

    // POST /api/billing/once/checkout
    public function onceCheckout(OnceCheckoutRequest  $request): JsonResponse
    {
        $tenant   = $this->tenantContext->currentTenant();
        $checkout = $this->billing->checkoutOnce(
            $tenant,
            $request->price_id,
            $request->integer('quantity', 1),
            route('billing.success'),
            route('billing.cancel')
        );


        return response()->json(['url' => $checkout->url]);
    }

    // GET /api/billing/portal
    public function portal(Request $request): JsonResponse
    {
        $tenant = $this->tenantContext->currentTenant();
        $url    = $this->billing->billingPortalUrl(
            $tenant,
            $request->get('return_url', route('dashboard'))
        );

        return response()->json(['url' => $url]);
    }

    // GET /api/billing/invoices
    public function invoices(): JsonResponse
    {
        $tenant = $this->tenantContext->currentTenant();

        return response()->json([
            'invoices' => $this->billing->invoices($tenant),
        ]);
    }

    // GET /api/billing/invoices/{invoice}/download
    public function downloadInvoice(string $invoiceId): mixed
    {
        $tenant = $this->tenantContext->currentTenant();

        return $this->billing->downloadInvoice($tenant, $invoiceId);
    }
}
