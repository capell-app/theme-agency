<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FinancialAdvisory;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\FinancialAdvisory\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class FinancialAdvisoryThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'financial-advisory';

    public static string $packageName = 'capell-app/theme-financial-advisory';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Financial Advisory',
            description: 'Financial Advisory gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/financial-advisory.jpg',
            tags: ['Finance', 'Trust', 'Advisory', 'Green & gold', 'Professional'],
            bestFit: ['Financial advisors', 'Accountants', 'Wealth management', 'Tax & audit firms'],
            includedSections: ['navigation', 'hero', 'services', 'advisors', 'calculators', 'credentials', 'client-segments', 'content-listing', 'cta', 'footer', 'features', 'proof'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Financial Advisory',
                    description: 'Financial Advisory visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/financial-advisory.jpg',
                    values: [
                        'primaryColor' => '#14532d',
                        'accentColor' => '#b08d57',
                        'neutralColor' => '#14211a',
                        'surfaceColor' => '#f8faf8',
                        'foregroundColor' => '#14211a',
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
            assets: ['css' => 'vendor/capell/themes/financial-advisory.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-financial-advisory');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-financial-advisory');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-financial-advisory::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_FINANCIAL_ADVISORY_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-financial-advisory.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-financial-advisory::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-financial-advisory::sections.hero', failLoudly: true),
            'services' => new ViewSectionRenderer(self::THEME_KEY, 'services', 'capell-theme-financial-advisory::sections.services', failLoudly: true),
            'advisors' => new ViewSectionRenderer(self::THEME_KEY, 'advisors', 'capell-theme-financial-advisory::sections.advisors', failLoudly: true),
            'calculators' => new ViewSectionRenderer(self::THEME_KEY, 'calculators', 'capell-theme-financial-advisory::sections.calculators', failLoudly: true),
            'credentials' => new ViewSectionRenderer(self::THEME_KEY, 'credentials', 'capell-theme-financial-advisory::sections.credentials', failLoudly: true),
            'client-segments' => new ViewSectionRenderer(self::THEME_KEY, 'client-segments', 'capell-theme-financial-advisory::sections.client-segments', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-financial-advisory::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-financial-advisory::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-financial-advisory::sections.footer', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-financial-advisory::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-financial-advisory::sections.proof', failLoudly: true),
        ];
    }
}
