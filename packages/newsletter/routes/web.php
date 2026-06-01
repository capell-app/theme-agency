<?php

declare(strict_types=1);

use Capell\Newsletter\Http\Controllers\ConfirmSubscriptionController;
use Capell\Newsletter\Http\Controllers\ProviderWebhookController;
use Capell\Newsletter\Http\Controllers\ShowPreferenceCenterController;
use Capell\Newsletter\Http\Controllers\UnsubscribeController;
use Capell\Newsletter\Http\Controllers\UpdatePreferenceCenterController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::prefix('newsletter')
    ->name('capell-newsletter.')
    ->group(function (): void {
        Route::get('confirm/{token}', ConfirmSubscriptionController::class)->name('confirm');
        Route::get('unsubscribe/{token}', UnsubscribeController::class)->name('unsubscribe');
        Route::get('preferences/{token}', ShowPreferenceCenterController::class)->name('preferences.show');
        Route::post('preferences/{token}', UpdatePreferenceCenterController::class)->name('preferences.update');
        Route::post('providers/{providerConnection}/webhook', ProviderWebhookController::class)
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('provider-webhook');
    });
