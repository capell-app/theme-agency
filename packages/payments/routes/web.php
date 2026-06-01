<?php

declare(strict_types=1);

use Capell\CustomerPortal\Actions\ResolveAuthenticatedPortalAccountAction;
use Capell\FormBuilder\Models\Submission;
use Capell\Payments\Http\Controllers\CreateFormPaymentCheckoutController;
use Capell\Payments\Http\Controllers\CreatePortalBillingSessionController;
use Capell\Payments\Http\Controllers\DownloadPaidDownloadController;
use Capell\Payments\Http\Controllers\StripeWebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::prefix('capell/payments')
    ->name('capell-payments.')
    ->middleware(['web'])
    ->group(function (): void {
        Route::post('stripe/webhook', StripeWebhookController::class)
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('stripe-webhook');

        Route::get('downloads/{entitlement}', DownloadPaidDownloadController::class)
            ->middleware('signed')
            ->name('paid-downloads.show');
    });

if (class_exists(Submission::class)) {
    Route::prefix('capell/payments/forms')
        ->name('capell-payments.form-builder.')
        ->middleware(['web', 'signed'])
        ->group(function (): void {
            Route::get('{submission}/checkout', CreateFormPaymentCheckoutController::class)
                ->name('checkout');
        });
}

if (class_exists(ResolveAuthenticatedPortalAccountAction::class)) {
    $portalMiddleware = config('capell-payments.portal.middleware', ['web', 'auth']);

    Route::prefix('capell/payments/portal')
        ->name('capell-payments.portal.')
        ->middleware(is_array($portalMiddleware) ? $portalMiddleware : ['web', 'auth'])
        ->group(function (): void {
            Route::get('billing', CreatePortalBillingSessionController::class)
                ->name('billing');
        });
}
