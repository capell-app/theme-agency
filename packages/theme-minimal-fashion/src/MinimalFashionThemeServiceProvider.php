<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MinimalFashion;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\MinimalFashion\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class MinimalFashionThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'minimal-fashion';

    public static string $packageName = 'capell-app/theme-minimal-fashion';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Minimal Fashion',
            description: 'Minimal fashion retail theme for quiet collection launches, editorial product storytelling, product care, lookbooks, and sparse conversion paths.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/minimal-fashion.jpg',
            tags: ['Fashion', 'Retail', 'Lookbook', 'Product Care', 'Minimal'],
            bestFit: ['Fashion retailers', 'Boutique clothing brands', 'Skincare and fragrance stores', 'Hospitality retail', 'Design-led product catalogues'],
            includedSections: ['navigation', 'hero', 'seasonal-collections', 'category-paths', 'features', 'lookbook-feature', 'material-notes', 'product-care', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Minimal Fashion',
                    description: 'Minimal Fashion visual preset for restrained collection launches, sparse navigation, calm product grids, material notes, and product-care content.',
                    previewImage: '/vendor/capell/themes/minimal-fashion.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#76716a',
                        'neutralColor' => '#1d1d1b',
                        'surfaceColor' => '#f8f7f3',
                        'foregroundColor' => '#111111',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/minimal-fashion.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-minimal-fashion');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-minimal-fashion');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-minimal-fashion::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_MINIMAL_FASHION_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-minimal-fashion.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-minimal-fashion::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-minimal-fashion::sections.hero', failLoudly: true),
            'seasonal-collections' => new ViewSectionRenderer(self::THEME_KEY, 'seasonal-collections', 'capell-theme-minimal-fashion::sections.seasonal-collections', failLoudly: true),
            'category-paths' => new ViewSectionRenderer(self::THEME_KEY, 'category-paths', 'capell-theme-minimal-fashion::sections.category-paths', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-minimal-fashion::sections.features', failLoudly: true),
            'lookbook-feature' => new ViewSectionRenderer(self::THEME_KEY, 'lookbook-feature', 'capell-theme-minimal-fashion::sections.lookbook-feature', failLoudly: true),
            'material-notes' => new ViewSectionRenderer(self::THEME_KEY, 'material-notes', 'capell-theme-minimal-fashion::sections.material-notes', failLoudly: true),
            'product-care' => new ViewSectionRenderer(self::THEME_KEY, 'product-care', 'capell-theme-minimal-fashion::sections.product-care', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-minimal-fashion::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-minimal-fashion::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-minimal-fashion::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-minimal-fashion::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-minimal-fashion::sections.footer', failLoudly: true),
        ];
    }
}
