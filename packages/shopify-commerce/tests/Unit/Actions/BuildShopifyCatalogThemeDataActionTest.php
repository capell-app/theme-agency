<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\Catalog\BuildShopifyCatalogThemeDataAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Models\ShopifyProductVariant;

it('builds safe theme catalog data from synced active products', function (): void {
    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'retail.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
    ]);

    $product = ShopifyProduct::query()->create([
        'connection_id' => $connection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/100',
        'handle' => 'field-tote',
        'title' => 'Field Tote',
        'search_text' => ShopifyProduct::searchableText('Field Tote', 'field-tote'),
        'status' => 'active',
        'options' => [],
        'featured_image' => [
            'url' => 'https://cdn.example/field-tote.jpg',
            'altText' => 'Field tote in moss canvas',
            'width' => 1600,
        ],
        'raw_snapshot' => ['admin_graphql_api_id' => 'gid://shopify/Product/100'],
        'synced_at' => now()->subMinute(),
    ]);

    ShopifyProductVariant::query()->create([
        'product_id' => $product->getKey(),
        'shopify_gid' => 'gid://shopify/ProductVariant/200',
        'title' => 'Moss / Standard',
        'price_amount' => '128.000000',
        'price_currency' => 'GBP',
        'available_for_sale' => true,
        'selected_options' => [
            ['name' => 'Colour', 'value' => 'Moss'],
            ['name' => 'Size', 'value' => 'Standard'],
        ],
    ]);

    ShopifyProductVariant::query()->create([
        'product_id' => $product->getKey(),
        'shopify_gid' => 'gid://shopify/ProductVariant/201',
        'title' => 'Clay / Standard',
        'price_amount' => '132.500000',
        'price_currency' => 'EUR',
        'available_for_sale' => false,
        'selected_options' => [
            ['name' => 'Colour', 'value' => 'Clay'],
        ],
    ]);

    ShopifyProduct::query()->create([
        'connection_id' => $connection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/101',
        'handle' => 'draft-sample',
        'title' => 'Draft Sample',
        'search_text' => ShopifyProduct::searchableText('Draft Sample', 'draft-sample'),
        'status' => 'draft',
        'options' => [],
        'featured_image' => null,
        'raw_snapshot' => [],
        'synced_at' => now(),
    ]);

    $payload = BuildShopifyCatalogThemeDataAction::run($connection)->toThemeArray();
    $firstProduct = $payload['items'][0] ?? [];
    $firstVariant = is_array($firstProduct['variants'] ?? null) ? ($firstProduct['variants'][0] ?? []) : [];
    $encodedPayload = json_encode($payload, JSON_THROW_ON_ERROR);

    expect($payload['items'])->toHaveCount(1)
        ->and($firstProduct['title'] ?? null)->toBe('Field Tote')
        ->and($firstProduct['handle'] ?? null)->toBe('field-tote')
        ->and($firstProduct['url'] ?? null)->toBe('/products/field-tote')
        ->and($firstProduct['featuredImage'] ?? null)->toBe([
            'url' => 'https://cdn.example/field-tote.jpg',
            'altText' => 'Field tote in moss canvas',
        ])
        ->and($firstProduct['priceAmount'] ?? null)->toBe('128.00')
        ->and($firstProduct['priceCurrency'] ?? null)->toBe('GBP')
        ->and($firstProduct['presentmentCurrency'] ?? null)->toBe('GBP')
        ->and($firstProduct['availableForSale'] ?? null)->toBeTrue()
        ->and($firstVariant['selectedOptions'] ?? null)->toBe([
            ['name' => 'Colour', 'value' => 'Moss'],
            ['name' => 'Size', 'value' => 'Standard'],
        ])
        ->and($payload['shopifySummary']['productsSynced'] ?? null)->toBe(1)
        ->and($payload['shopifySummary']['variantsSynced'] ?? null)->toBe(2)
        ->and($payload['shopifySummary']['availableStock'] ?? null)->toBe(1)
        ->and($payload['shopifySummary']['presentmentCurrencies'] ?? null)->toBe(['EUR', 'GBP'])
        ->and($payload['shopifySummary']['presentmentCurrency'] ?? null)->toBe('EUR')
        ->and($payload['shopifySummary']['syncedAt'] ?? null)->toBeString()
        ->and($encodedPayload)->not->toContain('gid://shopify')
        ->and($encodedPayload)->not->toContain('admin-token')
        ->and($encodedPayload)->not->toContain('raw_snapshot')
        ->and($encodedPayload)->not->toContain('admin_graphql_api_id');
});

it('filters storefront catalog data by handle and search term', function (): void {
    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'retail.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
    ]);

    foreach (['field-tote' => 'Field Tote', 'linen-wrap' => 'Linen Wrap'] as $handle => $title) {
        ShopifyProduct::query()->create([
            'connection_id' => $connection->getKey(),
            'shopify_gid' => 'gid://shopify/Product/' . $handle,
            'handle' => $handle,
            'title' => $title,
            'search_text' => ShopifyProduct::searchableText($title, $handle),
            'status' => 'active',
            'options' => [],
            'featured_image' => null,
            'raw_snapshot' => [],
            'synced_at' => now(),
        ]);
    }

    $payload = BuildShopifyCatalogThemeDataAction::run(
        connection: $connection,
        searchTerm: 'linen',
        handles: ['field-tote', 'linen-wrap', 123],
    )->toThemeArray();

    expect($payload['items'])->toHaveCount(1)
        ->and($payload['items'][0]['handle'] ?? null)->toBe('linen-wrap');
});

it('returns an empty storefront payload for inactive connections', function (): void {
    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'retail.myshopify.com',
        'status' => ShopifyConnectionStatus::Revoked,
        'access_token' => null,
        'scopes' => ['read_products'],
    ]);

    ShopifyProduct::query()->create([
        'connection_id' => $connection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/100',
        'handle' => 'field-tote',
        'title' => 'Field Tote',
        'search_text' => ShopifyProduct::searchableText('Field Tote', 'field-tote'),
        'status' => 'active',
        'options' => [],
        'featured_image' => null,
        'raw_snapshot' => [],
        'synced_at' => now(),
    ]);

    $payload = BuildShopifyCatalogThemeDataAction::run($connection)->toThemeArray();

    expect($payload['items'])->toBe([])
        ->and($payload['shopifySummary'])->toBe([
            'productsSynced' => 0,
            'variantsSynced' => 0,
            'availableStock' => 0,
            'presentmentCurrency' => null,
            'presentmentCurrencies' => [],
            'syncedAt' => null,
        ]);
});
