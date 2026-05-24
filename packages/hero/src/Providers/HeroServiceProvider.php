<?php

declare(strict_types=1);

namespace Capell\Hero\Providers;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\PackageTypeEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Hero\Console\Commands\SetupCommand;
use Capell\Hero\Filament\Extenders\HeroBackgroundBlockAssetSchemaExtender;
use Capell\Hero\Filament\Extenders\HeroBackgroundBlockSchemaExtender;
use Capell\Hero\Filament\Extenders\HeroBackgroundThemeSchemaExtender;
use Capell\Hero\View\Components\Block\Hero;
use Capell\LayoutBuilder\Contracts\Extenders\BlockAssetSchemaExtender;
use Capell\LayoutBuilder\Contracts\Extenders\BlockSchemaExtender;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;

final class HeroServiceProvider extends AbstractPackageServiceProvider
{
    public static string $name = 'capell-hero';

    public static string $packageName = 'capell-app/hero';

    public static PackageTypeEnum $type = PackageTypeEnum::Package;

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasTranslations()
            ->hasCommands([
                SetupCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        $this->registerTailwindSources();
        $this->registerViews();
        $this->registerBladeComponents();
        $this->registerSchemaExtenders();

        if (! $this->isPackageInstalled()) {
        }
    }

    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(self::$packageName);
    }

    private function registerViews(): void
    {
        resolve(ViewFactory::class)->addNamespace(
            'capell-hero',
            __DIR__ . '/../../resources/views',
        );
    }

    private function registerBladeComponents(): void
    {
        Blade::anonymousComponentPath(__DIR__ . '/../../resources/views/components', 'capell-hero');
        Blade::component(Hero::class, 'capell::block.hero');
    }

    private function registerTailwindSources(): void
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/hero.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );
    }

    private function registerSchemaExtenders(): void
    {
        $this->app->tag(HeroBackgroundThemeSchemaExtender::class, HeroBackgroundThemeSchemaExtender::TAG);
        $this->app->tag(HeroBackgroundBlockSchemaExtender::class, BlockSchemaExtender::TAG);
        $this->app->tag(HeroBackgroundBlockAssetSchemaExtender::class, BlockAssetSchemaExtender::TAG);
    }
}
