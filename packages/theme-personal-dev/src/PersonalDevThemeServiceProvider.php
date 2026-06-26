<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PersonalDev;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\PersonalDev\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class PersonalDevThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'personal-dev';

    public static string $packageName = 'capell-app/theme-personal-dev';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Personal Dev',
            description: 'Personal Dev gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/personal-dev.jpg',
            tags: ['Minimal', 'Typography', 'Writing', 'Free', 'Personal'],
            bestFit: ['Developer personal sites', 'Writers & bloggers', 'Engineers\' homepages', 'Indie hackers'],
            includedSections: ['navigation', 'about-intro', 'writing-index', 'now', 'projects', 'newsletter-inline', 'content-listing', 'footer', 'hero', 'features', 'proof', 'cta'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Personal Dev',
                    description: 'Personal Dev visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/personal-dev.jpg',
                    values: [
                        'primaryColor' => '#111827',
                        'accentColor' => '#6366f1',
                        'neutralColor' => '#1f2937',
                        'surfaceColor' => '#fcfcfc',
                        'foregroundColor' => '#111827',
                        'headingFont' => 'inter',
                        'bodyFont' => 'inter',
                        'spacing' => 'airy',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'none',
                        'mediaTreatment' => 'flat',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/personal-dev.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-personal-dev');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-personal-dev');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-personal-dev::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_PERSONAL_DEV_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-personal-dev.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-personal-dev::sections.navigation', failLoudly: true),
            'about-intro' => new ViewSectionRenderer(self::THEME_KEY, 'about-intro', 'capell-theme-personal-dev::sections.about-intro', failLoudly: true),
            'writing-index' => new ViewSectionRenderer(self::THEME_KEY, 'writing-index', 'capell-theme-personal-dev::sections.writing-index', failLoudly: true),
            'now' => new ViewSectionRenderer(self::THEME_KEY, 'now', 'capell-theme-personal-dev::sections.now', failLoudly: true),
            'projects' => new ViewSectionRenderer(self::THEME_KEY, 'projects', 'capell-theme-personal-dev::sections.projects', failLoudly: true),
            'newsletter-inline' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter-inline', 'capell-theme-personal-dev::sections.newsletter-inline', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-personal-dev::sections.content-listing', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-personal-dev::sections.footer', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-personal-dev::sections.hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-personal-dev::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-personal-dev::sections.proof', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-personal-dev::sections.cta', failLoudly: true),
        ];
    }
}
