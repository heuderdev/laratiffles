<?php


use App\Http\Controllers\ExemploTesteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::get("/demostracao", [ExemploTesteController::class, 'simalarUpload'])->name('demostracao');

require __DIR__ . '/dashboard/web.php';
require __DIR__ . '/profile/web.php';
require __DIR__ . '/billing/web.php';
require __DIR__ . '/auth.php';


Route::middleware('auth')->get('/checkout', function (Request $request) {
    $stripePriceId = 'price_1TDeXeFY6BloK9fXeKKsrnu1';

    $tenant = $request->user()->defaultTenant;

    abort_unless($tenant, 404, 'Tenant não encontrado.');

    if ($tenant->hasActiveBilling()) {
        return redirect()->route('portal');
    }

    $tenant->createOrGetStripeCustomer();
    $tenant->syncStripeCustomerDetails();

    return $tenant
        ->newSubscription('default', $stripePriceId)
        ->trialDays(1)
        ->allowPromotionCodes()
        ->checkout([
            'success_url' => route('billing.success'),
            'cancel_url' => route('billing.cancel'),
            'locale' => 'pt-BR',
        ]);
})->name('checkout');


Route::middleware('auth')->get('/portal', function (Request $request) {
    $tenant = $request->user()->defaultTenant;

    abort_unless($tenant, 404, 'Tenant não encontrado.');

    $tenant->createOrGetStripeCustomer();
    $tenant->syncStripeCustomerDetails();

    return $tenant->redirectToBillingPortal(route('billing.success'));
})->name('portal');
