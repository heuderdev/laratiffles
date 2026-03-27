<?php


use App\Http\Controllers\ExemploTesteController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth'])->group(function () {

    Route::view('dashboard', 'dashboard')
        ->middleware(['auth', 'verified'])
        ->name('dashboard');
});


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::get("/demostracao", [ExemploTesteController::class, 'simalarUpload'])->name('demostracao');


Route::get('/billing/success', fn() => view('billing.success'))->name('billing.success');
Route::get('/billing/cancel',  fn() => view('billing.cancel'))->name('billing.cancel');

require __DIR__ . '/auth.php';
