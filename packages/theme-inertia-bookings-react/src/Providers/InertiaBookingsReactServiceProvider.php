<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookingsReact\Providers;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Override;
use Spatie\LaravelPackageTools\Package;

class InertiaBookingsReactServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-theme-inertia-bookings-react';

    public static string $packageName = 'capell-app/theme-inertia-bookings-react';

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
            VendorAssetData::tailwindSource('resources/js/**/*.jsx', self::$packageName),
        );
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(static::$packageName);
    }
}
