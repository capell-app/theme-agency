<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AutomotiveDealer;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\AutomotiveDealer\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class AutomotiveDealerThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'automotive-dealer';

    public static string $packageName = 'capell-app/theme-automotive-dealer';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Automotive Dealer',
            description: 'A hand-picked selection of prestige and performance cars, every one inspected, prepared, and warrantied. Reserve online, view in our showroom, and drive away the same week.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/automotive-dealer.jpg',
            tags: ['Automotive', 'Dealership', 'Inventory', 'Dark', 'Performance'],
            bestFit: ['Car dealerships', 'Used-car retailers', 'Automotive specialists', 'Performance & prestige cars', 'Multi-franchise dealers'],
            includedSections: ['navigation', 'hero', 'inventory-grid', 'vehicle-detail', 'finance-options', 'part-exchange', 'test-drive-panel', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Automotive Dealer',
                    description: 'Automotive Dealer visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/automotive-dealer.jpg',
                    values: [
                        'primaryColor' => '#dc2626',
                        'accentColor' => '#0ea5e9',
                        'neutralColor' => '#18181b',
                        'surfaceColor' => '#0d0d0f',
                        'foregroundColor' => '#f4f4f5',
                        'headingFont' => 'space-grotesk',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/automotive-dealer.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-automotive-dealer');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-automotive-dealer');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-automotive-dealer::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-automotive-dealer.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-automotive-dealer::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-automotive-dealer::sections.hero', failLoudly: true),
            'inventory-grid' => new ViewSectionRenderer(self::THEME_KEY, 'inventory-grid', 'capell-theme-automotive-dealer::sections.inventory-grid', failLoudly: true),
            'vehicle-detail' => new ViewSectionRenderer(self::THEME_KEY, 'vehicle-detail', 'capell-theme-automotive-dealer::sections.vehicle-detail', failLoudly: true),
            'finance-options' => new ViewSectionRenderer(self::THEME_KEY, 'finance-options', 'capell-theme-automotive-dealer::sections.finance-options', failLoudly: true),
            'part-exchange' => new ViewSectionRenderer(self::THEME_KEY, 'part-exchange', 'capell-theme-automotive-dealer::sections.part-exchange', failLoudly: true),
            'test-drive-panel' => new ViewSectionRenderer(self::THEME_KEY, 'test-drive-panel', 'capell-theme-automotive-dealer::sections.test-drive-panel', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-automotive-dealer::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-automotive-dealer::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-automotive-dealer::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-automotive-dealer::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-automotive-dealer::sections.footer', failLoudly: true),
        ];
    }
}
