<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ExemploTesteController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::get("/demostracao", [ExemploTesteController::class, 'simalarUpload'])->name('demostracao');


Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');
});

Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

Route::get('/billing/success', fn() => view('billing.success'))->name('billing.success');
Route::get('/billing/cancel',  fn() => view('billing.cancel'))->name('billing.cancel');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [BillingController::class, 'checkout'])->name('checkout');
    Route::get('/portal', [BillingController::class, 'portal'])->name('portal');
    Route::get('/revalidate', [BillingController::class, 'revalidate'])->name('billing.revalidate');
});

Route::middleware(['auth', 'tenant.billing.owner'])->group(function () {
    Route::get('/test', function () {
        dd(auth()->user()->toArray());
        return 'test';
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])
        ->name('subscription.cancel');
});

Route::middleware(['auth', 'tenant.billing.owner', 'tenant.employee.active'])->group(function () {
    Route::get('/atendimentos', function () {
        return  'atendimentos';
    })->name('attendances.index');
});

require __DIR__ . '/auth.php';
