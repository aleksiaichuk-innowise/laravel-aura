<?php

use Aura\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('aura.path', 'aura'))
    ->middleware(['web', \Aura\Http\Middleware\AuraShieldMiddleware::class])
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('aura.dashboard');
        Route::get('/api/metrics/{type}', [DashboardController::class, 'metrics'])->name('aura.api.metrics');
    });
