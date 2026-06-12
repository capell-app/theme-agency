<?php

declare(strict_types=1);

use Capell\Newsletter\Http\Controllers\ConfirmSubscriptionController;
use Capell\Newsletter\Http\Controllers\OneClickUnsubscribeController;
use Capell\Newsletter\Http\Controllers\ProviderWebhookController;
use Capell\Newsletter\Http\Controllers\ShowPreferenceCenterController;
use Capell\Newsletter\Http\Controllers\SubscribeController;
use Capell\Newsletter\Http\Controllers\UnsubscribeController;
use Capell\Newsletter\Http\Controllers\UpdatePreferenceCenterController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::prefix('newsletter')
    ->name('capell-newsletter.')
    ->group(function (): void {
        Route::post('subscribe', SubscribeController::class)
            ->middleware('throttle:30,1')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('subscribe');
        Route::get('confirm/{token}', ConfirmSubscriptionController::class)->name('confirm');
        Route::get('unsubscribe/{token}', UnsubscribeController::class)->name('unsubscribe');
        Route::post('unsubscribe/{token}/one-click', OneClickUnsubscribeController::class)
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('unsubscribe.one-click');
        Route::get('preferences/{token}', ShowPreferenceCenterController::class)->name('preferences.show');
        Route::post('preferences/{token}', UpdatePreferenceCenterController::class)->name('preferences.update');
        Route::post('providers/{providerConnection}/webhook', ProviderWebhookController::class)
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('provider-webhook');
    });
