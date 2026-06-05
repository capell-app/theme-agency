<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookingsVue\Providers;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Assets\VendorAssetConditionRegistry;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Override;
use Spatie\LaravelPackageTools\Package;

class InertiaBookingsVueServiceProvider extends AbstractPackageServiceProvider
{
    public const string ADAPTER_KEY = 'vue';

    public const string BUILD_PATH = 'vendor/capell/theme-inertia-bookings-vue';

    public const string ENTRYPOINT = 'resources/js/app.js';

    public const string VENDOR_ASSET_CONDITION = 'capell-theme-inertia-bookings-vue';

    public static string $name = 'capell-theme-inertia-bookings-vue';

    public static string $packageName = 'capell-app/theme-inertia-bookings-vue';

    public function configurePackage(Package $package): void
    {
        $package->name(self::$name);
    }

    public function packageBooted(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->registerVendorAssetConditions();
        $this->registerVendorAssets();
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(static::$packageName);
    }

    private function registerVendorAssets(): void
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/js/**/*.vue', self::$packageName),
        );

        CapellCore::registerVendorAsset(VendorAssetData::buildAsset(
            path: self::BUILD_PATH,
            file: self::ENTRYPOINT,
            packageName: self::$packageName,
            condition: self::VENDOR_ASSET_CONDITION,
        ));
    }

    private function registerVendorAssetConditions(): void
    {
        resolve(VendorAssetConditionRegistry::class)->register(
            self::VENDOR_ASSET_CONDITION,
            fn (mixed $context): bool => ($context->runtime->usesInertia ?? false)
                && config('capell-inertia.adapter', self::ADAPTER_KEY) === self::ADAPTER_KEY,
        );
    }
}
