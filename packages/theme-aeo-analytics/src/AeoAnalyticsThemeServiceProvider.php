<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AeoAnalytics;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\AeoAnalytics\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class AeoAnalyticsThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'aeo-analytics';

    public static string $packageName = 'capell-app/theme-aeo-analytics';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'AEO Analytics',
            description: 'Track every time ChatGPT, Perplexity, Gemini, and Copilot mention, cite, or recommend you — and see exactly where you\'re winning and where you\'re invisible.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/aeo-analytics.jpg',
            tags: ['Analytics', 'Dashboard', 'AI Search', 'Data', 'SaaS'],
            bestFit: ['AEO/GEO analytics SaaS', 'Brand visibility tools', 'AI search monitoring', 'Marketing analytics'],
            includedSections: ['navigation', 'hero', 'dashboard-preview', 'metric-cards', 'features', 'coverage-map', 'integrations-grid', 'report-gallery', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'AEO Analytics',
                    description: 'AEO Analytics visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/aeo-analytics.jpg',
                    values: [
                        'primaryColor' => '#7c3aed',
                        'accentColor' => '#84cc16',
                        'neutralColor' => '#1e1b2e',
                        'surfaceColor' => '#ffffff',
                        'foregroundColor' => '#1e1b2e',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/aeo-analytics.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-aeo-analytics');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-aeo-analytics');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-aeo-analytics::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_AEO_ANALYTICS_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-aeo-analytics.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-aeo-analytics::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-aeo-analytics::sections.hero', failLoudly: true),
            'dashboard-preview' => new ViewSectionRenderer(self::THEME_KEY, 'dashboard-preview', 'capell-theme-aeo-analytics::sections.dashboard-preview', failLoudly: true),
            'metric-cards' => new ViewSectionRenderer(self::THEME_KEY, 'metric-cards', 'capell-theme-aeo-analytics::sections.metric-cards', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-aeo-analytics::sections.features', failLoudly: true),
            'coverage-map' => new ViewSectionRenderer(self::THEME_KEY, 'coverage-map', 'capell-theme-aeo-analytics::sections.coverage-map', failLoudly: true),
            'integrations-grid' => new ViewSectionRenderer(self::THEME_KEY, 'integrations-grid', 'capell-theme-aeo-analytics::sections.integrations-grid', failLoudly: true),
            'report-gallery' => new ViewSectionRenderer(self::THEME_KEY, 'report-gallery', 'capell-theme-aeo-analytics::sections.report-gallery', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-aeo-analytics::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-aeo-analytics::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-aeo-analytics::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-aeo-analytics::sections.footer', failLoudly: true),
        ];
    }
}
