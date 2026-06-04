<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Health\ShopifyCommerceHealthCheck;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Config::set('capell-shopify-commerce.client_id', 'client-id');
    Config::set('capell-shopify-commerce.client_secret', 'client-secret');
});

it('reports a compatible capell api version', function (): void {
    expect(ShopifyCommerceHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = ShopifyCommerceHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(4)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when storage, app credentials, connection tokens, and sync state are healthy', function (): void {
    $results = ShopifyCommerceHealthCheck::runDiagnostics();

    expect(ShopifyCommerceHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the storage table check when a shopify commerce table is missing', function (): void {
    Schema::drop('shopify_products');

    $check = new ShopifyCommerceHealthCheck;

    expect($check->missingTables())->toContain('shopify_products')
        ->and($check->storageTablesCheck()->passed)->toBeFalse()
        ->and(ShopifyCommerceHealthCheck::passed())->toBeFalse();
});

it('fails the app credentials check when oauth configuration is missing', function (): void {
    Config::set('capell-shopify-commerce.client_secret');

    $check = new ShopifyCommerceHealthCheck;

    expect($check->missingAppCredentialKeys())->toContain('capell-shopify-commerce.client_secret')
        ->and($check->shopifyAppCredentialsCheck()->passed)->toBeFalse()
        ->and(ShopifyCommerceHealthCheck::passed())->toBeFalse();
});

it('fails connection credentials without leaking stored admin api tokens', function (): void {
    $secretToken = 'shpat_secret_token';

    ShopifyConnection::query()->create([
        'shop_domain' => 'healthy.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => $secretToken,
        'scopes' => ['read_products'],
    ]);

    ShopifyConnection::query()->create([
        'shop_domain' => 'missing-token.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => null,
        'scopes' => ['read_products'],
    ]);

    $check = new ShopifyCommerceHealthCheck;
    $serializedDiagnostics = json_encode(ShopifyCommerceHealthCheck::runDiagnostics()->toArray(), JSON_THROW_ON_ERROR);

    expect($check->activeConnectionsMissingTokenCount())->toBe(1)
        ->and($check->connectionCredentialsCheck()->passed)->toBeFalse()
        ->and($serializedDiagnostics)->not->toContain($secretToken)
        ->and(ShopifyCommerceHealthCheck::passed())->toBeFalse();
});

it('fails the sync state check when queued or running work appears stale', function (): void {
    ShopifyConnection::query()->create([
        'shop_domain' => 'stale.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
        'sync_status' => 'running',
        'last_sync_started_at' => now()->subHour(),
        'bulk_operation_id' => 'gid://shopify/BulkOperation/1',
    ]);

    ShopifyConnection::query()->create([
        'shop_domain' => 'fresh.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
        'sync_status' => 'queued',
        'last_sync_queued_at' => now(),
    ]);

    $check = new ShopifyCommerceHealthCheck;

    expect($check->staleSyncOperationCount())->toBe(1)
        ->and($check->syncStateCheck()->passed)->toBeFalse()
        ->and(ShopifyCommerceHealthCheck::passed())->toBeFalse();
});
