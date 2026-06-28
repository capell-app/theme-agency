<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\TravelTourism;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\TravelTourism\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class TravelTourismThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'travel-tourism';

    public static string $packageName = 'capell-app/theme-travel-tourism';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Travel & Tourism',
            description: 'We design small-group and tailor-made journeys for travellers who want more than a checklist — local guides, honest pacing, and time to actually be somewhere. Tell us where you\'re dreaming of and we\'ll build the trip aro',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/travel-tourism.jpg',
            tags: ['Travel', 'Tourism', 'Itineraries', 'Immersive', 'Adventure'],
            bestFit: ['Tour operators', 'Travel agencies', 'Destination brands', 'Boutique adventure travel', 'Honeymoon & luxury travel'],
            includedSections: ['navigation', 'hero', 'destination-grid', 'itinerary-builder', 'guide-profiles', 'trip-inclusions', 'enquiry-panel', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Travel & Tourism',
                    description: 'Travel & Tourism visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/travel-tourism.jpg',
                    values: [
                        'primaryColor' => '#0d9488',
                        'accentColor' => '#f59e0b',
                        'neutralColor' => '#0f1f1c',
                        'surfaceColor' => '#f8faf9',
                        'foregroundColor' => '#0f1f1c',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'lg',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/travel-tourism.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-travel-tourism');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-travel-tourism');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-travel-tourism::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-travel-tourism.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-foundation-theme::theme.chrome.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-travel-tourism::sections.hero', failLoudly: true),
            'destination-grid' => new ViewSectionRenderer(self::THEME_KEY, 'destination-grid', 'capell-theme-travel-tourism::sections.destination-grid', failLoudly: true),
            'itinerary-builder' => new ViewSectionRenderer(self::THEME_KEY, 'itinerary-builder', 'capell-theme-travel-tourism::sections.itinerary-builder', failLoudly: true),
            'guide-profiles' => new ViewSectionRenderer(self::THEME_KEY, 'guide-profiles', 'capell-theme-travel-tourism::sections.guide-profiles', failLoudly: true),
            'trip-inclusions' => new ViewSectionRenderer(self::THEME_KEY, 'trip-inclusions', 'capell-theme-travel-tourism::sections.trip-inclusions', failLoudly: true),
            'enquiry-panel' => new ViewSectionRenderer(self::THEME_KEY, 'enquiry-panel', 'capell-theme-travel-tourism::sections.enquiry-panel', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-travel-tourism::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-travel-tourism::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-travel-tourism::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-travel-tourism::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-foundation-theme::theme.chrome.footer', failLoudly: true),
        ];
    }
}
