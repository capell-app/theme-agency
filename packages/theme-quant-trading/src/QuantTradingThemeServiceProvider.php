<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuantTrading;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\QuantTrading\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class QuantTradingThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'quant-trading';

    public static string $packageName = 'capell-app/theme-quant-trading';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Quant Trading',
            description: 'Meridian runs a diversified book of systematic strategies across equities, futures, and FX — backed by transparent reporting and hard risk limits. Figures shown are illustrative.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/quant-trading.jpg',
            tags: ['Quant', 'Trading', 'Finance', 'Dark', 'Data'],
            bestFit: ['Quant trading firms', 'Algorithmic strategy products', 'Systematic funds', 'Trading research teams'],
            includedSections: ['navigation', 'hero', 'performance-chart', 'metric-cards', 'strategy-cards', 'features', 'track-record-table', 'risk-disclosure', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Quant Trading',
                    description: 'Quant Trading visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/quant-trading.jpg',
                    values: [
                        'primaryColor' => '#2dd4bf',
                        'accentColor' => '#f43f5e',
                        'neutralColor' => '#161b22',
                        'surfaceColor' => '#0b0e14',
                        'foregroundColor' => '#e6edf3',
                        'headingFont' => 'space-grotesk',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/quant-trading.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-quant-trading');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-quant-trading');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-quant-trading::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_QUANT_TRADING_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-quant-trading.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-quant-trading::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-quant-trading::sections.hero', failLoudly: true),
            'performance-chart' => new ViewSectionRenderer(self::THEME_KEY, 'performance-chart', 'capell-theme-quant-trading::sections.performance-chart', failLoudly: true),
            'metric-cards' => new ViewSectionRenderer(self::THEME_KEY, 'metric-cards', 'capell-theme-quant-trading::sections.metric-cards', failLoudly: true),
            'strategy-cards' => new ViewSectionRenderer(self::THEME_KEY, 'strategy-cards', 'capell-theme-quant-trading::sections.strategy-cards', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-quant-trading::sections.features', failLoudly: true),
            'track-record-table' => new ViewSectionRenderer(self::THEME_KEY, 'track-record-table', 'capell-theme-quant-trading::sections.track-record-table', failLoudly: true),
            'risk-disclosure' => new ViewSectionRenderer(self::THEME_KEY, 'risk-disclosure', 'capell-theme-quant-trading::sections.risk-disclosure', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-quant-trading::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-quant-trading::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-quant-trading::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-quant-trading::sections.footer', failLoudly: true),
        ];
    }
}
