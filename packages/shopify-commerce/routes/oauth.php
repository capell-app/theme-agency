<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Http\Controllers\OAuth\ShopifyCallbackController;
use Capell\ShopifyCommerce\Http\Controllers\OAuth\ShopifyInstallController;
use Capell\ShopifyCommerce\Http\Controllers\Webhooks\ShopifyWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('capell/oauth/shopify')->group(function (): void {
    Route::get('/install', ShopifyInstallController::class)->name('capell-shopify-commerce.oauth.install');
    Route::get('/callback', ShopifyCallbackController::class)->name('capell-shopify-commerce.oauth.callback');
});

Route::post('/capell/webhooks/shopify', ShopifyWebhookController::class)
    ->middleware('throttle:capell-shopify-commerce-webhooks')
    ->name('capell-shopify-commerce.webhooks.shopify');
