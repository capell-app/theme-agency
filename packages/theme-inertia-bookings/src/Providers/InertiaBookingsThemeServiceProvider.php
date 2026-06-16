<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookings\Providers;

use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Packages\AbstractPackageServiceProvider;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\InertiaBookings\Console\Commands\DemoCommand;
use Capell\ThemeStudio\InertiaBookings\Rendering\InertiaBookingsThemeRenderer;
use Capell\ThemeStudio\InertiaBookings\Rendering\InertiaPublicBookingRequestRenderer;
use Override;
use Spatie\LaravelPackageTools\Package;

class InertiaBookingsThemeServiceProvider extends AbstractPackageServiceProvider
{
    public const string THEME_KEY = 'inertia-bookings';

    public static string $name = 'capell-theme-inertia-bookings';

    public static string $packageName = 'capell-app/theme-inertia-bookings';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Inertia Bookings',
            description: 'Premium Inertia booking-business theme for services, clinics, consultants, classes, and appointments.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/inertia-bookings.jpg',
            tags: ['Bookings', 'Inertia', 'Appointments'],
            bestFit: ['Service businesses', 'Clinics', 'Consultants', 'Classes'],
            includedSections: ['navigation', 'hero', 'services', 'proof', 'booking', 'locations', 'faq', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Inertia Bookings',
                    description: 'Crisp booking-first direction with blue-green trust colors and warm appointment actions.',
                    previewImage: '/vendor/capell/themes/inertia-bookings.jpg',
                    values: [
                        'primaryColor' => '#0f766e',
                        'accentColor' => '#f97316',
                        'neutralColor' => '#111827',
                        'surfaceColor' => '#f8fafc',
                        'foregroundColor' => '#111827',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/inertia-bookings.css'],
            runtime: FrontendRuntime::Inertia,
        );
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::$name)
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->app->bind(PublicBookingRequestRenderer::class, InertiaPublicBookingRequestRenderer::class);
        $this->registerTheme();
        $this->registerVendorAssets();

        if ($this->app->runningInConsole()) {
            $this->commands([DemoCommand::class]);
        }
    }

    #[Override]
    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(static::$packageName);
    }

    private function registerTheme(): void
    {
        resolve(ThemeRegistry::class)->register(
            definition: self::definition(),
            themeRenderer: new InertiaBookingsThemeRenderer,
            sectionRenderers: [],
        );
    }

    private function registerVendorAssets(): void
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-inertia-bookings.css', self::$packageName),
        );
    }
}
