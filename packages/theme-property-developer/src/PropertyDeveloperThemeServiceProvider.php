<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PropertyDeveloper;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\PropertyDeveloper\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class PropertyDeveloperThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'property-developer';

    public static string $packageName = 'capell-app/theme-property-developer';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Property Developer',
            description: 'We build characterful new homes and apartments in well-connected places, with the specification right and the detail considered. Explore our current developments and register your interest to hear about new releases firs',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/property-developer.jpg',
            tags: ['Property', 'New-build', 'Developer', 'Architectural', 'Editorial'],
            bestFit: ['New-build developers', 'Housebuilders', 'Off-plan apartment schemes', 'Regeneration projects', 'Property marketing teams'],
            includedSections: ['navigation', 'hero', 'development-grid', 'floorplans', 'availability-table', 'location-guide', 'viewing-panel', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Property Developer',
                    description: 'Property Developer visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/property-developer.jpg',
                    values: [
                        'primaryColor' => '#1f2937',
                        'accentColor' => '#a07c4f',
                        'neutralColor' => '#111827',
                        'surfaceColor' => '#f7f5f1',
                        'foregroundColor' => '#1f2937',
                        'headingFont' => 'fraunces',
                        'bodyFont' => 'inter',
                        'spacing' => 'airy',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'sm',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/property-developer.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-property-developer');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-property-developer');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-property-developer::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-property-developer.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-foundation-theme::theme.chrome.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-property-developer::sections.hero', failLoudly: true),
            'development-grid' => new ViewSectionRenderer(self::THEME_KEY, 'development-grid', 'capell-theme-property-developer::sections.development-grid', failLoudly: true),
            'floorplans' => new ViewSectionRenderer(self::THEME_KEY, 'floorplans', 'capell-theme-property-developer::sections.floorplans', failLoudly: true),
            'availability-table' => new ViewSectionRenderer(self::THEME_KEY, 'availability-table', 'capell-theme-property-developer::sections.availability-table', failLoudly: true),
            'location-guide' => new ViewSectionRenderer(self::THEME_KEY, 'location-guide', 'capell-theme-property-developer::sections.location-guide', failLoudly: true),
            'viewing-panel' => new ViewSectionRenderer(self::THEME_KEY, 'viewing-panel', 'capell-theme-property-developer::sections.viewing-panel', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-property-developer::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-property-developer::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-property-developer::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-property-developer::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-foundation-theme::theme.chrome.footer', failLoudly: true),
        ];
    }
}
