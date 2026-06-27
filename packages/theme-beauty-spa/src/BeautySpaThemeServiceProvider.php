<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\BeautySpa;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\BeautySpa\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class BeautySpaThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'beauty-spa';

    public static string $packageName = 'capell-app/theme-beauty-spa';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Beauty & Spa',
            description: 'A boutique spa in the old town, with skin therapists, restorative massage, and a steam suite. Treat yourself, or someone you love, to an hour that resets everything.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/beauty-spa.jpg',
            tags: ['Spa', 'Beauty', 'Wellness', 'Luxury', 'Editorial'],
            bestFit: ['Day spas', 'Beauty clinics', 'Nail & skincare studios', 'Wellness retreats', 'Salons'],
            includedSections: ['navigation', 'hero', 'treatment-menu', 'therapist-profiles', 'package-grid', 'before-after-proof', 'booking-panel', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Beauty & Spa',
                    description: 'Beauty & Spa visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/beauty-spa.jpg',
                    values: [
                        'primaryColor' => '#9d8567',
                        'accentColor' => '#b9a0b4',
                        'neutralColor' => '#2b2420',
                        'surfaceColor' => '#f6f1ea',
                        'foregroundColor' => '#2b2420',
                        'headingFont' => 'fraunces',
                        'bodyFont' => 'inter',
                        'spacing' => 'airy',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/beauty-spa.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-beauty-spa');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-beauty-spa');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-beauty-spa::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_BEAUTY_SPA_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-beauty-spa.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-foundation-theme::theme.chrome.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-beauty-spa::sections.hero', failLoudly: true),
            'treatment-menu' => new ViewSectionRenderer(self::THEME_KEY, 'treatment-menu', 'capell-theme-beauty-spa::sections.treatment-menu', failLoudly: true),
            'therapist-profiles' => new ViewSectionRenderer(self::THEME_KEY, 'therapist-profiles', 'capell-theme-beauty-spa::sections.therapist-profiles', failLoudly: true),
            'package-grid' => new ViewSectionRenderer(self::THEME_KEY, 'package-grid', 'capell-theme-beauty-spa::sections.package-grid', failLoudly: true),
            'before-after-proof' => new ViewSectionRenderer(self::THEME_KEY, 'before-after-proof', 'capell-theme-beauty-spa::sections.before-after-proof', failLoudly: true),
            'booking-panel' => new ViewSectionRenderer(self::THEME_KEY, 'booking-panel', 'capell-theme-beauty-spa::sections.booking-panel', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-beauty-spa::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-beauty-spa::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-beauty-spa::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-beauty-spa::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-foundation-theme::theme.chrome.footer', failLoudly: true),
        ];
    }
}
