<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function cancel(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var Tenant|null $tenant */
        $tenant = $user?->defaultTenant;

        abort_unless($tenant, 404, 'Tenant não encontrado.');

        if (! $tenant->isOwner($user)) {
            return redirect()
                ->route('dashboard')
                ->with('warning', 'Apenas o owner pode gerenciar a assinatura.');
        }

        $subscription = $tenant->subscription(Tenant::SUBSCRIPTION_DEFAULT);

        if (! $subscription) {
            return redirect()
                ->route('dashboard')
                ->with('warning', 'Nenhuma assinatura ativa encontrada para cancelar.');
        }

        try {
            // cancelamento IMEDIATO:
            $subscription->cancelNow();

            // se preferir grace period em vez de cancelNow, use:
            // $subscription->cancel();

            Log::info('Assinatura cancelada pelo owner.', [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'stripe_subscription_id' => $subscription->stripe_id,
            ]);

            return redirect()
                ->route('dashboard')
                ->with('success', 'Assinatura cancelada com sucesso.');
        } catch (\Throwable $e) {
            report($e);

            Log::error('Erro ao cancelar assinatura.', [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'subscription_id' => $subscription->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('dashboard')
                ->with('error', 'Não foi possível cancelar a assinatura. Tente novamente em instantes.');
        }
    }
}
