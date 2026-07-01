<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuietLuxuryRetail;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\QuietLuxuryRetail\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class QuietLuxuryRetailThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'quiet-luxury-retail';

    public static string $packageName = 'capell-app/theme-quiet-luxury-retail';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Quiet Luxury Retail',
            description: 'Restrained luxury retail theme for guided consultation, editorial product detail, ingredient notes, rituals, store assistance, and considered cross-sells.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/quiet-luxury-retail.jpg',
            tags: ['Luxury Retail', 'Skincare', 'Fragrance', 'Hospitality', 'Consultation'],
            bestFit: ['Skincare retailers', 'Fragrance houses', 'Boutique hospitality retail', 'Design-led product catalogues', 'Apothecary brands'],
            includedSections: ['navigation', 'hero', 'ritual-guide', 'product-families', 'features', 'store-consultation', 'ingredient-notes', 'usage-guidance', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Quiet Luxury Retail',
                    description: 'Quiet Luxury Retail visual preset for warm restraint, guided consultation, precise product copy, ingredient notes, rituals, and store assistance.',
                    previewImage: '/vendor/capell/themes/quiet-luxury-retail.jpg',
                    values: [
                        'primaryColor' => '#33281f',
                        'accentColor' => '#8a765f',
                        'neutralColor' => '#2a2520',
                        'surfaceColor' => '#f3eee6',
                        'foregroundColor' => '#2a2520',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/quiet-luxury-retail.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-quiet-luxury-retail');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-quiet-luxury-retail');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-quiet-luxury-retail::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-quiet-luxury-retail.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-quiet-luxury-retail::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-quiet-luxury-retail::sections.hero', failLoudly: true),
            'ritual-guide' => new ViewSectionRenderer(self::THEME_KEY, 'ritual-guide', 'capell-theme-quiet-luxury-retail::sections.ritual-guide', failLoudly: true),
            'product-families' => new ViewSectionRenderer(self::THEME_KEY, 'product-families', 'capell-theme-quiet-luxury-retail::sections.product-families', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-quiet-luxury-retail::sections.features', failLoudly: true),
            'store-consultation' => new ViewSectionRenderer(self::THEME_KEY, 'store-consultation', 'capell-theme-quiet-luxury-retail::sections.store-consultation', failLoudly: true),
            'ingredient-notes' => new ViewSectionRenderer(self::THEME_KEY, 'ingredient-notes', 'capell-theme-quiet-luxury-retail::sections.ingredient-notes', failLoudly: true),
            'usage-guidance' => new ViewSectionRenderer(self::THEME_KEY, 'usage-guidance', 'capell-theme-quiet-luxury-retail::sections.usage-guidance', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-quiet-luxury-retail::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-quiet-luxury-retail::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-quiet-luxury-retail::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-quiet-luxury-retail::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-quiet-luxury-retail::sections.footer', failLoudly: true),
        ];
    }
}
