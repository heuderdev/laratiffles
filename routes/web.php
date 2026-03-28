<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ExemploTesteController;
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
});

require __DIR__ . '/auth.php';
