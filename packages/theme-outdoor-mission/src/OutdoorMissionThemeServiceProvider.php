<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OutdoorMission;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\OutdoorMission\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class OutdoorMissionThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'outdoor-mission';

    public static string $packageName = 'capell-app/theme-outdoor-mission';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Outdoor Mission',
            description: 'Rugged mission-commerce theme for outdoor retailers, field sports brands, repair-led product teams, and environmental campaigns.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/outdoor-mission.jpg',
            tags: ['Outdoor', 'Commerce', 'Activism', 'Field Stories', 'Repair'],
            bestFit: ['Outdoor retailers', 'Adventure gear brands', 'Environmental campaign teams', 'Repair-led commerce', 'Sport communities'],
            includedSections: ['navigation', 'hero', 'seasonal-essentials', 'sport-categories', 'features', 'repair-reuse', 'environmental-campaign', 'field-stories', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Outdoor Mission',
                    description: 'Outdoor Mission visual preset for field-tested commerce, practical navigation, campaign storytelling, and repair-first product language.',
                    previewImage: '/vendor/capell/themes/outdoor-mission.jpg',
                    values: [
                        'primaryColor' => '#2f5d3a',
                        'accentColor' => '#b4512a',
                        'neutralColor' => '#172018',
                        'surfaceColor' => '#f4f1e8',
                        'foregroundColor' => '#172018',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/outdoor-mission.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-outdoor-mission');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-outdoor-mission');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-outdoor-mission::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_OUTDOOR_MISSION_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-outdoor-mission.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-outdoor-mission::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-outdoor-mission::sections.hero', failLoudly: true),
            'seasonal-essentials' => new ViewSectionRenderer(self::THEME_KEY, 'seasonal-essentials', 'capell-theme-outdoor-mission::sections.seasonal-essentials', failLoudly: true),
            'sport-categories' => new ViewSectionRenderer(self::THEME_KEY, 'sport-categories', 'capell-theme-outdoor-mission::sections.sport-categories', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-outdoor-mission::sections.features', failLoudly: true),
            'repair-reuse' => new ViewSectionRenderer(self::THEME_KEY, 'repair-reuse', 'capell-theme-outdoor-mission::sections.repair-reuse', failLoudly: true),
            'environmental-campaign' => new ViewSectionRenderer(self::THEME_KEY, 'environmental-campaign', 'capell-theme-outdoor-mission::sections.environmental-campaign', failLoudly: true),
            'field-stories' => new ViewSectionRenderer(self::THEME_KEY, 'field-stories', 'capell-theme-outdoor-mission::sections.field-stories', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-outdoor-mission::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-outdoor-mission::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-outdoor-mission::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-outdoor-mission::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-outdoor-mission::sections.footer', failLoudly: true),
        ];
    }
}
