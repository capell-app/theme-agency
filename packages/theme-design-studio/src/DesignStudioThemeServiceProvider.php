<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DesignStudio;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\DesignStudio\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class DesignStudioThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'design-studio';

    public static string $packageName = 'capell-app/theme-design-studio';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Design Studio',
            description: 'Design Studio gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/design-studio.jpg',
            tags: ['Editorial', 'Serif', 'Portfolio', 'Minimal', 'Photography'],
            bestFit: ['Interior design studios', 'Architecture practices', 'Brand & spatial design', 'Hospitality design'],
            includedSections: ['navigation', 'hero', 'project-gallery', 'lookbook', 'studio-services', 'awards', 'studio-statement', 'content-listing', 'cta', 'footer', 'features', 'proof'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Design Studio',
                    description: 'Design Studio visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/design-studio.jpg',
                    values: [
                        'primaryColor' => '#1c1917',
                        'accentColor' => '#c2683f',
                        'neutralColor' => '#292524',
                        'surfaceColor' => '#faf7f2',
                        'foregroundColor' => '#1c1917',
                        'headingFont' => 'fraunces',
                        'bodyFont' => 'inter',
                        'spacing' => 'airy',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'none',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/design-studio.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-design-studio');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-design-studio');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-design-studio::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_DESIGN_STUDIO_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-design-studio.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-foundation-theme::theme.chrome.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-design-studio::sections.hero', failLoudly: true),
            'project-gallery' => new ViewSectionRenderer(self::THEME_KEY, 'project-gallery', 'capell-theme-design-studio::sections.project-gallery', failLoudly: true),
            'lookbook' => new ViewSectionRenderer(self::THEME_KEY, 'lookbook', 'capell-theme-design-studio::sections.lookbook', failLoudly: true),
            'studio-services' => new ViewSectionRenderer(self::THEME_KEY, 'studio-services', 'capell-theme-design-studio::sections.studio-services', failLoudly: true),
            'awards' => new ViewSectionRenderer(self::THEME_KEY, 'awards', 'capell-theme-design-studio::sections.awards', failLoudly: true),
            'studio-statement' => new ViewSectionRenderer(self::THEME_KEY, 'studio-statement', 'capell-theme-design-studio::sections.studio-statement', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-design-studio::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-design-studio::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-foundation-theme::theme.chrome.footer', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-design-studio::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-design-studio::sections.proof', failLoudly: true),
        ];
    }
}
