<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Providers;

use Capell\Admin\Data\Extensions\ExtensionManagementSurfaceData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Core\Support\Settings\SettingsGroupMetadata;
use Capell\ShopifyCommerce\Filament\Settings\ShopifyCommerceSettingsSchema;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyCustomer;
use Capell\ShopifyCommerce\Models\ShopifyOAuthState;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Models\ShopifyProductVariant;
use Capell\ShopifyCommerce\Settings\ShopifyCommerceSettings;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
                '2026_06_01_000001_create_shopify_customers_table',
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
                ->registerProtectedTables()
                ->registerScheduledMaintenance()
                ->registerScheduledSync();
        });
    }

    public function bootingPackage(): void
    {
        RateLimiter::for('capell-shopify-commerce-webhooks', static fn (Request $request): Limit => Limit::perMinute(120)
            ->by((string) $request->ip()));
    }

    private function registerModels(): self
    {
        $this->surface()->models([
            ShopifyConnection::class,
            ShopifyCustomer::class,
            ShopifyOAuthState::class,
            ShopifyProduct::class,
            ShopifyProductVariant::class,
        ]);

        return $this;
    }

    private function registerSettings(): self
    {
        $this->surface()->settingsClass(ShopifyCommerceSettings::group(), ShopifyCommerceSettings::class);
        $this->surface()->settingsSchema(ShopifyCommerceSettings::group(), ShopifyCommerceSettingsSchema::class);

        $this->surface()->settingsMetadata(new SettingsGroupMetadata(
            group: ShopifyCommerceSettings::group(),
            label: 'capell-shopify-commerce::capell-shopify-commerce.settings.title',
            icon: Heroicon::OutlinedShoppingBag,
            navigationGroup: 'capell-admin::navigation.group_integrations',
            navigationSort: 80,
            packageName: self::$packageName,
        ));
        CapellAdmin::registerExtensionManagementSurface(ExtensionManagementSurfaceData::settings(
            packageName: self::$packageName,
            label: 'capell-shopify-commerce::capell-shopify-commerce.settings.title',
            settingsGroup: ShopifyCommerceSettings::group(),
            icon: Heroicon::OutlinedShoppingBag,
        ));

        return $this;
    }

    private function registerProtectedTables(): self
    {
        CapellCore::registerProtectedTable('shopify_connections');
        CapellCore::registerProtectedTable('shopify_oauth_states');
        CapellCore::registerProtectedTable('shopify_products');
        CapellCore::registerProtectedTable('shopify_product_variants');
        CapellCore::registerProtectedTable('shopify_customers');

        return $this;
    }

    private function registerScheduledSync(): self
    {
        if (config('capell-shopify-commerce.scheduled_sync_enabled', true) !== true) {
            return $this;
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('capell-shopify-commerce:sync', ['--all' => true])
                ->everyFifteenMinutes()
                ->withoutOverlapping()
                ->onOneServer();
        });

        return $this;
    }

    private function registerScheduledMaintenance(): self
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('capell-shopify-commerce:prune-oauth-states')
                ->hourly()
                ->withoutOverlapping()
                ->onOneServer();
        });

        return $this;
    }
}
