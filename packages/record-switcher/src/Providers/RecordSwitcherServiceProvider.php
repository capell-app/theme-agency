<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Providers;

use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;

class RecordSwitcherServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-record-switcher';

    public static string $packageName = 'capell-app/record-switcher';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasViews();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('record-switcher', __DIR__ . '/../../resources/css/components/record-switcher.css'),
            AlpineComponent::make('record-switcher', __DIR__ . '/../../resources/dist/record-switcher.js'),
        ], package: self::$name);
    }
}
