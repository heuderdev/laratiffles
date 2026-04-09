<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ExemploTesteController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');
Route::middleware(['auth', 'tenant'])->get("/demostracao", [ExemploTesteController::class, 'simalarUpload'])->name('demostracao');


Route::middleware(['auth', 'tenant'])->group(function () {
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

Route::middleware(['auth', 'tenant', 'tenant.billing.owner', 'tenant.employee.active'])->group(function () {
    Route::get('/atendimentos', function () {
        return  'atendimentos';
    })->name('attendances.index');
});


Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/importar-clientes', [\App\Http\Controllers\Web\ImportadorClienteController::class, 'index'])->name('importar_clientes.index');
    Route::post('/importar-clientes', [\App\Http\Controllers\Web\ImportadorClienteController::class, 'handle'])->name('importar_clientes.handle');
    Route::post('/importar-clientes/form', [\App\Http\Controllers\Web\ImportadorClienteController::class, 'handleForm'])->name('importar_clientes.handle_form');
});

Route::middleware(['auth', 'tenant', 'tenant.billing.owner', 'tenant.employee.active'])->group(function () {
    Route::get('/cnab-itau', [\App\Http\Controllers\Web\CNABItau400Controller::class, 'index'])->name('cnab_itau.index');
});

require __DIR__ . '/auth.php';
