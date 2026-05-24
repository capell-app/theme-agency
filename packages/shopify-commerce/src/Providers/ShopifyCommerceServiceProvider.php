<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Providers;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Core\Support\Settings\SettingsGroupMetadata;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\ShopifyCommerce\Filament\Settings\ShopifyCommerceSettingsSchema;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyOAuthState;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Models\ShopifyProductVariant;
use Capell\ShopifyCommerce\Settings\ShopifyCommerceSettings;
use Filament\Support\Icons\Heroicon;
use Spatie\LaravelPackageTools\Package;

final class ShopifyCommerceServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-shopify-commerce';

    public static string $packageName = 'capell-app/shopify-commerce';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasConfigFile()
            ->hasRoute('oauth')
            ->hasViews(self::$name)
            ->hasTranslations()
            ->hasMigrations([
                '2026_05_22_000001_create_shopify_connections_table',
                '2026_05_22_000002_create_shopify_oauth_states_table',
                '2026_05_22_000003_create_shopify_products_table',
                '2026_05_22_000004_create_shopify_product_variants_table',
            ]);
    }

    public function registeringPackage(): void
    {
        if (config('capell-shopify-commerce.enabled', true) === true) {
            $this->app->register(AdminServiceProvider::class);
        }
    }

    public function packageRegistered(): void
    {
        $this->app->booted(function (): void {
            if (! CapellCore::isPackageInstalled(self::$packageName)) {
                return;
            }

            $this->registerModels()
                ->registerSettings()
                ->registerProtectedTables();
        });
    }

    private function registerModels(): self
    {
        CapellCore::registerModels([
            ShopifyConnection::class,
            ShopifyOAuthState::class,
            ShopifyProduct::class,
            ShopifyProductVariant::class,
        ]);

        return $this;
    }

    private function registerSettings(): self
    {
        /** @var SettingsSchemaRegistry $registry */
        $registry = $this->app->make(SettingsSchemaRegistry::class);

        $registry->registerSettingsClass(ShopifyCommerceSettings::group(), ShopifyCommerceSettings::class);
        $registry->registerMetadata(new SettingsGroupMetadata(
            group: ShopifyCommerceSettings::group(),
            label: 'capell-shopify-commerce::capell-shopify-commerce.settings.title',
            icon: Heroicon::OutlinedShoppingBag,
            navigationGroup: 'capell-admin::navigation.group_integrations',
            navigationSort: 80,
            packageName: self::$packageName,
        ));
        $registry->register(ShopifyCommerceSettings::group(), ShopifyCommerceSettingsSchema::class);

        return $this;
    }

    private function registerProtectedTables(): self
    {
        CapellCore::registerProtectedTable('shopify_connections');
        CapellCore::registerProtectedTable('shopify_oauth_states');
        CapellCore::registerProtectedTable('shopify_products');
        CapellCore::registerProtectedTable('shopify_product_variants');

        return $this;
    }
}
