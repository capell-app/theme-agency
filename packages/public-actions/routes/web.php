<?php

declare(strict_types=1);

use Capell\PublicActions\Http\Controllers\ShowPublicActionController;
use Capell\PublicActions\Http\Controllers\SubmitPublicActionController;
use Capell\PublicActions\Http\Controllers\SubmitTrustedPublicActionController;
use Capell\PublicActions\Http\Controllers\Zapier\ListZapierPublicActionsController;
use Capell\PublicActions\Http\Controllers\Zapier\ListZapierPublicActionSubmissionsController;
use Capell\PublicActions\Http\Controllers\Zapier\ShowZapierAccountController;
use Capell\PublicActions\Http\Controllers\Zapier\SubmitZapierPublicActionController;
use Capell\PublicActions\Http\Middleware\PublicActionZapierAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->prefix(config('capell-public-actions.route_prefix', 'actions'))
    ->as('capell-public-actions.')
    ->group(function (): void {
        Route::get('/{action}', ShowPublicActionController::class)
            ->name('show');

        Route::post('/{action}', SubmitPublicActionController::class)
            ->middleware('throttle:public-actions-submit')
            ->name('submit');
    });

Route::middleware(['api', PublicActionZapierAuthMiddleware::class, 'throttle:public-actions-api'])
    ->prefix(config('capell-public-actions.api_route_prefix', 'api/public-actions') . '/zapier')
    ->as('capell-public-actions.zapier.')
    ->group(function (): void {
        Route::get('/me', ShowZapierAccountController::class)->name('me');
        Route::get('/actions', ListZapierPublicActionsController::class)->name('actions');
        Route::post('/actions/{action}/submissions', SubmitZapierPublicActionController::class)->name('actions.submit');
        Route::get('/submissions', ListZapierPublicActionSubmissionsController::class)->name('submissions');
    });

Route::middleware(['api', 'throttle:public-actions-submit'])
    ->prefix(config('capell-public-actions.api_route_prefix', 'api/public-actions') . '/trusted')
    ->as('capell-public-actions.trusted.')
    ->group(function (): void {
        Route::post('/actions/{action}/submissions', SubmitTrustedPublicActionController::class)
            ->name('actions.submit');
    });
