<?php

declare(strict_types=1);

use Capell\Newsletter\Http\Controllers\ConfirmSubscriptionController;
use Capell\Newsletter\Http\Controllers\ProviderWebhookController;
use Capell\Newsletter\Http\Controllers\UnsubscribeController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::prefix('newsletter')
    ->name('capell-newsletter.')
    ->group(function (): void {
        Route::get('confirm/{token}', ConfirmSubscriptionController::class)->name('confirm');
        Route::get('unsubscribe/{token}', UnsubscribeController::class)->name('unsubscribe');
        Route::post('providers/{providerConnection}/webhook', ProviderWebhookController::class)
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('provider-webhook');
    });
