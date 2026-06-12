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
    ->middleware(['web'])
    ->group(function (): void {
        Route::post('subscribe', SubscribeController::class)
            ->middleware('throttle:capell-newsletter-subscribe')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('subscribe');
        Route::get('confirm/{token}', ConfirmSubscriptionController::class)->name('confirm');
        Route::get('unsubscribe/{token}', UnsubscribeController::class)->name('unsubscribe');
        Route::post('unsubscribe/{token}/one-click', OneClickUnsubscribeController::class)
            ->middleware('throttle:capell-newsletter-one-click-unsubscribe')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('unsubscribe.one-click');
        Route::get('preferences/{token}', ShowPreferenceCenterController::class)->name('preferences.show');
        Route::post('preferences/{token}', UpdatePreferenceCenterController::class)
            ->middleware('throttle:capell-newsletter-preferences')
            ->name('preferences.update');
        Route::post('providers/{providerConnection}/webhook', ProviderWebhookController::class)
            ->middleware('throttle:capell-newsletter-provider-webhook')
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('provider-webhook');
    });
