<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Agency;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Agency\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

class AgencyThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'agency';

    public static string $packageName = 'capell-app/theme-agency';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Agency',
            description: 'Expressive layouts with bold rhythm, immersive media, and confident calls to action.',
            package: 'capell-app/theme-agency',
            previewImage: '/vendor/capell/themes/agency-signal.jpg',
            tags: ['Expressive', 'Portfolio', 'Creative'],
            bestFit: ['Studios', 'Agencies', 'Brand-led teams'],
            includedSections: ['navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'signal',
                    name: 'Signal',
                    description: 'Sharp contrast, strong statements, and energetic section pacing.',
                    previewImage: '/vendor/capell/themes/agency-signal.jpg',
                    values: [
                        'primaryColor' => '#ff5a7e',
                        'accentColor' => '#3b82f6',
                        'headingFont' => 'sora',
                        'spacing' => 'spacious',
                        'cardStyle' => 'layered',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'expressive',
                    ],
                ),
                new ThemePresetData(
                    key: 'gallery',
                    name: 'Gallery',
                    description: 'Media-forward presentation with calmer motion and framed project surfaces.',
                    previewImage: '/vendor/capell/themes/agency-gallery.jpg',
                    values: [
                        'primaryColor' => '#7c3aed',
                        'accentColor' => '#fb7185',
                        'headingFont' => 'manrope',
                        'spacing' => 'spacious',
                        'cardStyle' => 'elevated',
                        'mediaTreatment' => 'framed',
                        'layoutPresentation' => 'editorial',
                    ],
                ),
                new ThemePresetData(
                    key: 'atelier',
                    name: 'Atelier',
                    description: 'Editorial studio feel with soft neutrals and refined proof.',
                    previewImage: '/vendor/capell/themes/agency-atelier.jpg',
                    values: [
                        'primaryColor' => '#be123c',
                        'accentColor' => '#f97316',
                        'headingFont' => 'playfair',
                        'spacing' => 'balanced',
                        'cardStyle' => 'subtle',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                    ],
                ),
                new ThemePresetData(
                    key: 'zenith',
                    name: 'Zenith',
                    description: 'Wellness editorial direction from the Stitch Zenith Yoga concept, with quiet space and organic imagery.',
                    previewImage: '/vendor/capell/themes/agency-zenith.jpg',
                    values: [
                        'primaryColor' => '#516447',
                        'accentColor' => '#bb957f',
                        'neutralColor' => '#2c2e2b',
                        'surfaceColor' => '#fafaf5',
                        'foregroundColor' => '#1a1c19',
                        'headingFont' => 'playfair',
                        'bodyFont' => 'inter',
                        'spacing' => 'spacious',
                        'cardStyle' => 'subtle',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'natural',
                        'radius' => 'xl',
                        'headingScale' => 'expressive',
                        'cardDensity' => 'spacious',
                    ],
                ),
                new ThemePresetData(
                    key: 'northstar',
                    name: 'Northstar',
                    description: 'Polished consultancy and brand-lab direction from the Stitch Northstar homepage.',
                    previewImage: '/vendor/capell/themes/agency-northstar.jpg',
                    values: [
                        'primaryColor' => '#155e75',
                        'accentColor' => '#f97316',
                        'neutralColor' => '#111827',
                        'surfaceColor' => '#f8fafc',
                        'foregroundColor' => '#111827',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'spacious',
                        'cardStyle' => 'layered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'expressive',
                        'mediaTreatment' => 'framed',
                        'radius' => 'lg',
                        'headingScale' => 'expressive',
                        'cardDensity' => 'comfortable',
                    ],
                ),
                new ThemePresetData(
                    key: 'motion-studio',
                    name: 'Motion Studio',
                    description: 'High-contrast portfolio rhythm for the more visual Stitch agency concepts.',
                    previewImage: '/vendor/capell/themes/agency-motion-studio.jpg',
                    values: [
                        'primaryColor' => '#e11d48',
                        'accentColor' => '#22d3ee',
                        'neutralColor' => '#09090b',
                        'surfaceColor' => '#fafafa',
                        'foregroundColor' => '#18181b',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'spacious',
                        'cardStyle' => 'layered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'expressive',
                        'mediaTreatment' => 'framed',
                        'radius' => 'xl',
                        'headingScale' => 'expressive',
                        'cardDensity' => 'spacious',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/agency.css'],
            runtime: FrontendRuntime::Blade,
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-agency');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-agency');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-agency::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-agency.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );
    }

    /**
     * @return array<string, ViewSectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-agency::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-agency::sections.hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-agency::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-agency::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-agency::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-agency::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-agency::sections.footer', failLoudly: true),
        ];
    }
}
