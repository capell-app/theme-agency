<?php

declare(strict_types=1);

use Capell\PrivacyCenter\Http\Controllers\ShowConsentPreferencesController;
use Capell\PrivacyCenter\Http\Controllers\StoreConsentPreferencesController;
use Illuminate\Support\Facades\Route;

Route::prefix('privacy/consent')
    ->name('capell-privacy-center.consent.')
    ->middleware('web')
    ->group(function (): void {
        Route::get('/', ShowConsentPreferencesController::class)->name('show');
        Route::post('/', StoreConsentPreferencesController::class)
            ->middleware('throttle:capell-privacy-center-consent')
            ->name('store');
    });
