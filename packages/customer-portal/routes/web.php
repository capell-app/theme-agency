<?php

declare(strict_types=1);

use Capell\CustomerPortal\Http\Controllers\ShowCustomerPortalController;
use Capell\CustomerPortal\Http\Controllers\StorePortalSupportRequestController;
use Capell\CustomerPortal\Http\Controllers\StorePortalSupportRequestReplyController;
use Capell\CustomerPortal\Http\Controllers\UpdatePortalPreferencesController;
use Illuminate\Support\Facades\Route;

$middleware = config('capell-customer-portal.middleware', ['web', 'auth']);

Route::middleware(is_array($middleware) ? $middleware : ['web', 'auth'])
    ->prefix(config('capell-customer-portal.route_prefix', 'portal'))
    ->as('capell-customer-portal.')
    ->group(function (): void {
        Route::get('/', ShowCustomerPortalController::class)->name('dashboard');
        Route::post('/preferences', UpdatePortalPreferencesController::class)->name('preferences.update');
        Route::post('/support', StorePortalSupportRequestController::class)
            ->middleware('throttle:12,1')
            ->name('support.store');
        Route::post('/support/{supportRequest}/replies', StorePortalSupportRequestReplyController::class)
            ->middleware('throttle:12,1')
            ->name('support.replies.store');
    });
