<?php

declare(strict_types=1);

use Capell\Admin\Support\Extensions\ExtensionManagementSurfaceRegistry;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\ShopifyCommerce\Filament\Settings\ShopifyCommerceSettingsSchema;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyCustomer;
use Capell\ShopifyCommerce\Models\ShopifyOAuthState;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Models\ShopifyProductVariant;
use Capell\ShopifyCommerce\Providers\ShopifyCommerceServiceProvider;
use Capell\ShopifyCommerce\Settings\ShopifyCommerceSettings;

it('registers shopify commerce settings and extension settings surface', function (): void {
    $settingsRegistry = resolve(SettingsSchemaRegistry::class);

    expect($settingsRegistry->getSettingsClass(ShopifyCommerceSettings::group()))
        ->toBe(ShopifyCommerceSettings::class)
        ->and($settingsRegistry->getSchemas(ShopifyCommerceSettings::group()))
        ->toContain(ShopifyCommerceSettingsSchema::class);

    $surfaces = resolve(ExtensionManagementSurfaceRegistry::class)
        ->surfacesForPackage(ShopifyCommerceServiceProvider::$packageName);

    expect($surfaces[0]->settingsGroup ?? null)->toBe(ShopifyCommerceSettings::group())
        ->and(CapellCore::getModels())->toContain(
            ShopifyConnection::class,
            ShopifyCustomer::class,
            ShopifyOAuthState::class,
            ShopifyProduct::class,
            ShopifyProductVariant::class,
        );
});
