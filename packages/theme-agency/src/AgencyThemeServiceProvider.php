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

    public const string GENERATED_FRONTEND_CSS = 'resources/css/capell/frontend.css';

    public const string PUBLIC_PREVIEW_IMAGE = '/vendor/capell/themes/agency.jpg';

    public const string TAILWIND_IMPORT = 'resources/css/theme-agency.css';

    public const string TAILWIND_SOURCE = 'resources/views/**/*.blade.php';

    public const string BUILD_ASSET_PATH = 'vendor/capell-theme-agency';

    public const string BUILD_ASSET_FILE = 'resources/js/theme-agency.js';

    public static string $packageName = 'capell-app/theme-agency';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Agency',
            description: 'Expressive layouts with bold rhythm, immersive media, and confident calls to action.',
            package: self::$packageName,
            previewImage: self::PUBLIC_PREVIEW_IMAGE,
            tags: ['Expressive', 'Portfolio', 'Creative'],
            bestFit: ['Studios', 'Agencies', 'Brand-led teams'],
            includedSections: ['navigation', 'hero', 'features', 'proof', 'content-listing', 'project-showcase', 'case-study', 'team', 'services', 'client-logos', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'signal',
                    name: 'Signal',
                    description: 'Sharp contrast, strong statements, and energetic section pacing.',
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
                    values: [
                        'primaryColor' => '#ff5a7e',
                        'accentColor' => '#3b82f6',
                        'neutralColor' => '#09090b',
                        'surfaceColor' => '#09090b',
                        'foregroundColor' => '#f8fafc',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
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
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
                    values: [
                        'primaryColor' => '#7c3aed',
                        'accentColor' => '#fb7185',
                        'neutralColor' => '#111827',
                        'surfaceColor' => '#111827',
                        'foregroundColor' => '#f8fafc',
                        'headingFont' => 'manrope',
                        'bodyFont' => 'inter',
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
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
                    values: [
                        'primaryColor' => '#be123c',
                        'accentColor' => '#f97316',
                        'neutralColor' => '#2c2e2b',
                        'surfaceColor' => '#fafaf5',
                        'foregroundColor' => '#1a1c19',
                        'headingFont' => 'playfair',
                        'bodyFont' => 'inter',
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
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
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
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
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
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
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
            assets: ['css' => self::GENERATED_FRONTEND_CSS],
            runtime: FrontendRuntime::Blade,
            // Theme Studio inherits section fallbacks from the runtime default; capell.json no longer declares package-level inheritance because the default fallback lives in capell-app/frontend.
            extends: 'default',
        );
    }

    #[Override]
    public function register(): void {}

    public function boot(ThemeRegistry $registry): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([DemoCommand::class]);

            $this->publishes([
                __DIR__ . '/../docs/assets/marketplace/extension-card.jpg' => public_path(ltrim(self::PUBLIC_PREVIEW_IMAGE, '/')),
            ], 'capell-theme-agency-assets');
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
            VendorAssetData::buildAsset(
                path: self::BUILD_ASSET_PATH,
                file: self::BUILD_ASSET_FILE,
                packageName: self::$packageName,
            ),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport(self::TAILWIND_IMPORT, self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource(self::TAILWIND_SOURCE, self::$packageName),
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
            'project-showcase' => new ViewSectionRenderer(self::THEME_KEY, 'project-showcase', 'capell-theme-agency::sections.project-showcase', failLoudly: true),
            'case-study' => new ViewSectionRenderer(self::THEME_KEY, 'case-study', 'capell-theme-agency::sections.case-study', failLoudly: true),
            'team' => new ViewSectionRenderer(self::THEME_KEY, 'team', 'capell-theme-agency::sections.team', failLoudly: true),
            'services' => new ViewSectionRenderer(self::THEME_KEY, 'services', 'capell-theme-agency::sections.services', failLoudly: true),
            'client-logos' => new ViewSectionRenderer(self::THEME_KEY, 'client-logos', 'capell-theme-agency::sections.client-logos', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-agency::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-agency::sections.footer', failLoudly: true),
        ];
    }
}
