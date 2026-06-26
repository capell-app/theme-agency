<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ProductStudio;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\ProductStudio\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class ProductStudioThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'product-studio';

    public static string $packageName = 'capell-app/theme-product-studio';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Product Studio',
            description: 'Product Studio gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/product-studio.jpg',
            tags: ['Engineering', 'Structured', 'Case studies', 'Technical', 'B2B'],
            bestFit: ['Web & product studios', 'Engineering agencies', 'Dev shops', 'Fractional product teams'],
            includedSections: ['navigation', 'hero', 'tech-stack', 'case-studies', 'process', 'engagement-models', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Product Studio',
                    description: 'Product Studio visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/product-studio.jpg',
                    values: [
                        'primaryColor' => '#2563eb',
                        'accentColor' => '#14b8a6',
                        'neutralColor' => '#0f172a',
                        'surfaceColor' => '#ffffff',
                        'foregroundColor' => '#0f172a',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'flat',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/product-studio.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-product-studio');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-product-studio');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-product-studio::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_PRODUCT_STUDIO_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-product-studio.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-product-studio::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-product-studio::sections.hero', failLoudly: true),
            'tech-stack' => new ViewSectionRenderer(self::THEME_KEY, 'tech-stack', 'capell-theme-product-studio::sections.tech-stack', failLoudly: true),
            'case-studies' => new ViewSectionRenderer(self::THEME_KEY, 'case-studies', 'capell-theme-product-studio::sections.case-studies', failLoudly: true),
            'process' => new ViewSectionRenderer(self::THEME_KEY, 'process', 'capell-theme-product-studio::sections.process', failLoudly: true),
            'engagement-models' => new ViewSectionRenderer(self::THEME_KEY, 'engagement-models', 'capell-theme-product-studio::sections.engagement-models', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-product-studio::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-product-studio::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-product-studio::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-product-studio::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-product-studio::sections.footer', failLoudly: true),
        ];
    }
}
