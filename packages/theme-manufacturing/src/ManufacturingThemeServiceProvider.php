<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Manufacturing;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Manufacturing\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class ManufacturingThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'manufacturing';

    public static string $packageName = 'capell-app/theme-manufacturing';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Manufacturing',
            description: 'Manufacturing gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/manufacturing.jpg',
            tags: ['Manufacturing', 'Industrial', 'B2B', 'RFQ', 'Certified'],
            bestFit: ['Industrial manufacturers', 'Smart factories', 'B2B precision suppliers', 'Contract / OEM machining'],
            includedSections: ['navigation', 'hero', 'capabilities-grid', 'certifications', 'facility-stats', 'features', 'case-studies', 'spec-downloads', 'rfq-form', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Manufacturing',
                    description: 'Manufacturing visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/manufacturing.jpg',
                    values: [
                        'primaryColor' => '#1d4ed8',
                        'accentColor' => '#f59e0b',
                        'neutralColor' => '#0f172a',
                        'surfaceColor' => '#f8fafc',
                        'foregroundColor' => '#0f172a',
                        'headingFont' => 'inter',
                        'bodyFont' => 'inter',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'none',
                        'mediaTreatment' => 'flat',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/manufacturing.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-manufacturing');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-manufacturing');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-manufacturing::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-manufacturing.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-manufacturing::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-manufacturing::sections.hero', failLoudly: true),
            'capabilities-grid' => new ViewSectionRenderer(self::THEME_KEY, 'capabilities-grid', 'capell-theme-manufacturing::sections.capabilities-grid', failLoudly: true),
            'certifications' => new ViewSectionRenderer(self::THEME_KEY, 'certifications', 'capell-theme-manufacturing::sections.certifications', failLoudly: true),
            'facility-stats' => new ViewSectionRenderer(self::THEME_KEY, 'facility-stats', 'capell-theme-manufacturing::sections.facility-stats', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-manufacturing::sections.features', failLoudly: true),
            'case-studies' => new ViewSectionRenderer(self::THEME_KEY, 'case-studies', 'capell-theme-manufacturing::sections.case-studies', failLoudly: true),
            'spec-downloads' => new ViewSectionRenderer(self::THEME_KEY, 'spec-downloads', 'capell-theme-manufacturing::sections.spec-downloads', failLoudly: true),
            'rfq-form' => new ViewSectionRenderer(self::THEME_KEY, 'rfq-form', 'capell-theme-manufacturing::sections.rfq-form', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-manufacturing::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-manufacturing::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-manufacturing::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-manufacturing::sections.footer', failLoudly: true),
        ];
    }
}
