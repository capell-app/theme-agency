<?php

declare(strict_types=1);

use Capell\CampaignStudio\Http\Controllers\CampaignConversionBeaconController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

$routePrefix = trim((string) config('capell-campaign-studio.tracking.route_prefix', 'capell/campaigns'), '/');

Route::prefix($routePrefix)
    ->middleware(['web'])
    ->group(function (): void {
        Route::post('conversions', CampaignConversionBeaconController::class)
            ->middleware(['throttle:60,1'])
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('capell-campaigns.conversions');
    });
