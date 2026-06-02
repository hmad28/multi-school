<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Platform\DashboardController;
use App\Http\Controllers\Platform\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('platform.dashboard')
        : redirect()->route('platform.login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'platform.admin'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('tenants/{school}', [TenantController::class, 'show'])->name('tenants.show');
    Route::patch('tenants/{school}/status', [TenantController::class, 'updateStatus'])->name('tenants.status');
    Route::post('tenants/{school}/reset-password', [TenantController::class, 'resetPassword'])->name('tenants.reset-password');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
