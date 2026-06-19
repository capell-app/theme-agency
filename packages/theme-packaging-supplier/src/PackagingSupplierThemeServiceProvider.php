<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PackagingSupplier;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\PackagingSupplier\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class PackagingSupplierThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'packaging-supplier';

    public static string $packageName = 'capell-app/theme-packaging-supplier';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Packaging Supplier',
            description: 'Packaging Supplier gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/packaging-supplier.jpg',
            tags: ['Packaging', 'Sustainable', 'B2B', 'Materials', 'Food-grade'],
            bestFit: ['Sustainable packaging suppliers', 'Food & produce packaging', 'Eco physical-product B2B', 'Materials & recyclability brands'],
            includedSections: ['navigation', 'hero', 'product-range', 'materials', 'sustainability', 'features', 'industries', 'sample-request', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Packaging Supplier',
                    description: 'Packaging Supplier visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/packaging-supplier.jpg',
                    values: [
                        'primaryColor' => '#16a34a',
                        'accentColor' => '#b45309',
                        'neutralColor' => '#1c2a1f',
                        'surfaceColor' => '#f7f6f0',
                        'foregroundColor' => '#1c2a1f',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/packaging-supplier.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-packaging-supplier');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-packaging-supplier');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-packaging-supplier::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-packaging-supplier.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-packaging-supplier::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-packaging-supplier::sections.hero', failLoudly: true),
            'product-range' => new ViewSectionRenderer(self::THEME_KEY, 'product-range', 'capell-theme-packaging-supplier::sections.product-range', failLoudly: true),
            'materials' => new ViewSectionRenderer(self::THEME_KEY, 'materials', 'capell-theme-packaging-supplier::sections.materials', failLoudly: true),
            'sustainability' => new ViewSectionRenderer(self::THEME_KEY, 'sustainability', 'capell-theme-packaging-supplier::sections.sustainability', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-packaging-supplier::sections.features', failLoudly: true),
            'industries' => new ViewSectionRenderer(self::THEME_KEY, 'industries', 'capell-theme-packaging-supplier::sections.industries', failLoudly: true),
            'sample-request' => new ViewSectionRenderer(self::THEME_KEY, 'sample-request', 'capell-theme-packaging-supplier::sections.sample-request', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-packaging-supplier::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-packaging-supplier::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-packaging-supplier::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-packaging-supplier::sections.footer', failLoudly: true),
        ];
    }
}
