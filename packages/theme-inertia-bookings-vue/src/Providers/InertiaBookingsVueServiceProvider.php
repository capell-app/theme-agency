<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookingsVue\Providers;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Override;
use Spatie\LaravelPackageTools\Package;

class InertiaBookingsVueServiceProvider extends AbstractPackageServiceProvider
{
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

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/js/**/*.vue', self::$packageName),
        );
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(static::$packageName);
    }
}
