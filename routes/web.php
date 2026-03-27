<?php


use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\ExemploTesteController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'default.tenant', 'verify.assinatura'])->group(function () {

    Route::view('dashboard', 'dashboard')
        ->middleware(['auth', 'verified'])
        ->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/status',                 [BillingController::class, 'status'])->name('status');
        Route::get('/portal',                 [BillingController::class, 'portal'])->name('portal');
        Route::get('/invoices',               [BillingController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{invoice}/download', [BillingController::class, 'downloadInvoice'])->name('invoices.download');

        Route::get('/subscription/checkout', [BillingController::class, 'subscriptionCheckout'])->name('subscription.checkout');
        Route::get('/subscription/resume',   [BillingController::class, 'resumeSubscription'])->name('subscription.resume');
        Route::get('/subscription/swap',     [BillingController::class, 'swapPlan'])->name('subscription.swap');

        Route::get('/credits/checkout',      [BillingController::class, 'creditsCheckout'])->name('credits.checkout');
        Route::get('/once/checkout',         [BillingController::class, 'onceCheckout'])->name('once.checkout');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::get("/demostracao", [ExemploTesteController::class, 'simalarUpload'])->name('demostracao');


Route::get('/billing/success', fn() => view('billing.success'))->name('billing.success');
Route::get('/billing/cancel',  fn() => view('billing.cancel'))->name('billing.cancel');

require __DIR__ . '/auth.php';
