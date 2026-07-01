<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Restaurant;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Restaurant\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class RestaurantThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'restaurant';

    public static string $packageName = 'capell-app/theme-restaurant';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Restaurant',
            description: 'Hospitality theme for menus, reservations, private dining, events, hours, and location-led restaurant journeys.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/restaurant.jpg',
            tags: ['Hospitality', 'Reservations', 'Menus'],
            bestFit: ['Restaurants', 'Bars', 'Private dining teams'],
            includedSections: [
                'navigation',
                'hero',
                'menu-highlights',
                'reservation-panel',
                'private-dining',
                'events-calendar',
                'opening-hours',
                'location-guide',
                'chef-story',
                'features',
                'proof',
                'content-listing',
                'cta',
                'footer',
            ],
            presets: [
                new ThemePresetData(
                    key: 'restaurant',
                    name: 'Restaurant',
                    description: 'Editorial hospitality preset with menu-led rhythm, reservation CTAs, and venue proof.',
                    previewImage: '/vendor/capell/themes/restaurant.jpg',
                    values: [
                        'primaryColor' => '#0f3d2e',
                        'accentColor' => '#b45309',
                        'neutralColor' => '#171312',
                        'surfaceColor' => '#f7fbf7',
                        'foregroundColor' => '#171312',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'generous',
                        'cardStyle' => 'editorial',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'full-bleed',
                        'radius' => 'sm',
                        'headingScale' => 'confident',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/restaurant.css'],
            runtime: FrontendRuntime::Blade,
            extends: 'default',
        );
    }

    #[Override]
    public function register(): void {}

    public function boot(ThemeRegistry $registry): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([DemoCommand::class]);
        }

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-restaurant');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-restaurant');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-restaurant::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-restaurant.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        $optionalIntegrations = [
            'reservation-panel' => [
                'bookingsAvailable' => CapellCore::isPackageInstalled('capell-app/bookings'),
                'formBuilderAvailable' => CapellCore::isPackageInstalled('capell-app/form-builder'),
            ],
            'events-calendar' => [
                'eventsAvailable' => CapellCore::isPackageInstalled('capell-app/events'),
            ],
            'content-listing' => [
                'blogAvailable' => CapellCore::isPackageInstalled('capell-app/blog'),
            ],
        ];

        $renderers = [];

        foreach (self::definition()->includedSections as $sectionKey) {
            $view = 'capell-theme-restaurant::sections.' . $sectionKey;

            if (! view()->exists($view)) {
                continue;
            }

            $renderers[$sectionKey] = new ViewSectionRenderer(
                self::THEME_KEY,
                $sectionKey,
                $view,
                true,
                $optionalIntegrations[$sectionKey] ?? [],
            );
        }

        return $renderers;
    }
}
