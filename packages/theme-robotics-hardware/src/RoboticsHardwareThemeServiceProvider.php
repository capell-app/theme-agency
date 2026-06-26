<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\RoboticsHardware;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\RoboticsHardware\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class RoboticsHardwareThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'robotics-hardware';

    public static string $packageName = 'capell-app/theme-robotics-hardware';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Robotics Hardware',
            description: 'Robotics Hardware gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/robotics-hardware.jpg',
            tags: ['Robotics', 'Hardware', 'Deep Tech', 'Graphite', 'Pre-order'],
            bestFit: ['Robotics products', 'Hardware and deep-tech devices', 'Pre-order launches', 'Engineering-led product reveals'],
            includedSections: ['navigation', 'video-hero', 'spec-sheet', 'capabilities', 'features', 'tech-deep-dive', 'preorder-cta', 'proof', 'content-listing', 'cta', 'footer', 'hero'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Robotics Hardware',
                    description: 'Robotics Hardware visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/robotics-hardware.jpg',
                    values: [
                        'primaryColor' => '#111827',
                        'accentColor' => '#f97316',
                        'neutralColor' => '#1c1917',
                        'surfaceColor' => '#f5f5f4',
                        'foregroundColor' => '#111827',
                        'headingFont' => 'space-grotesk',
                        'bodyFont' => 'inter',
                        'spacing' => 'airy',
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
            assets: ['css' => 'vendor/capell/themes/robotics-hardware.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-robotics-hardware');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-robotics-hardware');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-robotics-hardware::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_ROBOTICS_HARDWARE_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-robotics-hardware.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-robotics-hardware::sections.navigation', failLoudly: true),
            'video-hero' => new ViewSectionRenderer(self::THEME_KEY, 'video-hero', 'capell-theme-robotics-hardware::sections.video-hero', failLoudly: true),
            'spec-sheet' => new ViewSectionRenderer(self::THEME_KEY, 'spec-sheet', 'capell-theme-robotics-hardware::sections.spec-sheet', failLoudly: true),
            'capabilities' => new ViewSectionRenderer(self::THEME_KEY, 'capabilities', 'capell-theme-robotics-hardware::sections.capabilities', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-robotics-hardware::sections.features', failLoudly: true),
            'tech-deep-dive' => new ViewSectionRenderer(self::THEME_KEY, 'tech-deep-dive', 'capell-theme-robotics-hardware::sections.tech-deep-dive', failLoudly: true),
            'preorder-cta' => new ViewSectionRenderer(self::THEME_KEY, 'preorder-cta', 'capell-theme-robotics-hardware::sections.preorder-cta', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-robotics-hardware::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-robotics-hardware::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-robotics-hardware::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-robotics-hardware::sections.footer', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-robotics-hardware::sections.hero', failLoudly: true),
        ];
    }
}
