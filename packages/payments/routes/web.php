<?php

declare(strict_types=1);

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

        Route::get('downloads/{entitlement}', 'Capell\\Payments\\Http\\Controllers\\DownloadPaidDownloadController')
            ->middleware('signed')
            ->name('paid-downloads.show');
    });

if (class_exists('Capell\\FormBuilder\\Models\\Submission')) {
    Route::prefix('capell/payments/forms')
        ->name('capell-payments.form-builder.')
        ->middleware(['web', 'signed'])
        ->group(function (): void {
            Route::get('{submission}/checkout', 'Capell\\Payments\\Http\\Controllers\\CreateFormPaymentCheckoutController')
                ->name('checkout');
        });
}

if (class_exists('Capell\\CustomerPortal\\Actions\\ResolveAuthenticatedPortalAccountAction')) {
    $portalMiddleware = config('capell-payments.portal.middleware', ['web', 'auth']);

    Route::prefix('capell/payments/portal')
        ->name('capell-payments.portal.')
        ->middleware(is_array($portalMiddleware) ? $portalMiddleware : ['web', 'auth'])
        ->group(function (): void {
            Route::get('billing', 'Capell\\Payments\\Http\\Controllers\\CreatePortalBillingSessionController')
                ->name('billing');
        });
}
