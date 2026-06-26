<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LawFirm;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\LawFirm\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class LawFirmThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'law-firm';

    public static string $packageName = 'capell-app/theme-law-firm';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Law Firm',
            description: 'Law Firm gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/law-firm.jpg',
            tags: ['Legal', 'Authoritative', 'Serif', 'Professional', 'Navy & gold'],
            bestFit: ['Law firms', 'Barristers\' chambers', 'Legal practices', 'Professional-services firms'],
            includedSections: ['navigation', 'hero', 'practice-areas', 'attorneys', 'case-results', 'credentials', 'consultation-cta', 'content-listing', 'cta', 'footer', 'features', 'proof'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Law Firm',
                    description: 'Law Firm visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/law-firm.jpg',
                    values: [
                        'primaryColor' => '#1e293b',
                        'accentColor' => '#b08d57',
                        'neutralColor' => '#0f172a',
                        'surfaceColor' => '#f7f5f2',
                        'foregroundColor' => '#1e293b',
                        'headingFont' => 'fraunces',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'none',
                        'mediaTreatment' => 'flat',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/law-firm.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-law-firm');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-law-firm');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-law-firm::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_LAW_FIRM_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-law-firm.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-law-firm::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-law-firm::sections.hero', failLoudly: true),
            'practice-areas' => new ViewSectionRenderer(self::THEME_KEY, 'practice-areas', 'capell-theme-law-firm::sections.practice-areas', failLoudly: true),
            'attorneys' => new ViewSectionRenderer(self::THEME_KEY, 'attorneys', 'capell-theme-law-firm::sections.attorneys', failLoudly: true),
            'case-results' => new ViewSectionRenderer(self::THEME_KEY, 'case-results', 'capell-theme-law-firm::sections.case-results', failLoudly: true),
            'credentials' => new ViewSectionRenderer(self::THEME_KEY, 'credentials', 'capell-theme-law-firm::sections.credentials', failLoudly: true),
            'consultation-cta' => new ViewSectionRenderer(self::THEME_KEY, 'consultation-cta', 'capell-theme-law-firm::sections.consultation-cta', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-law-firm::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-law-firm::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-law-firm::sections.footer', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-law-firm::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-law-firm::sections.proof', failLoudly: true),
        ];
    }
}
