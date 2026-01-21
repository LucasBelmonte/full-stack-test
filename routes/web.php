<?php

use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Inertia::render('auth/Login', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function() {
    Route::impersonate();

    Route::prefix('a/admin')
        ->name('admin.')
        ->middleware(\App\Http\Middleware\FeatureAuthorizationMiddleware::class . ':admin')
        ->group(base_path('routes/admin.php'));

    Route::get('dashboard', fn() => inertia('Dashboard'))->name('dashboard');
    Route::get('current-team', fn() => inertia('CurrentTeam'))->name('current-team');

    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    Route::post('invoices/{invoice}/issue', [\App\Http\Controllers\InvoiceController::class, 'issue'])
        ->name('invoices.issue');
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
});

require __DIR__.'/settings.php';
