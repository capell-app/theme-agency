<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LiquidGlass;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Rendering\ChromeSplitBladeThemeRenderer;
use Capell\FoundationTheme\Support\Editor\StandardThemeEditorSchema;
use Capell\ThemeStudio\LiquidGlass\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

class LiquidGlassThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'liquid-glass';

    public const string PUBLIC_PREVIEW_IMAGE = '/vendor/capell/themes/liquid-glass.svg';

    public static string $packageName = 'capell-app/theme-liquid-glass';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Liquid Glass',
            description: 'A free modern glass interface with translucent panels, crisp content rhythm, and warm accent actions.',
            package: self::$packageName,
            previewImage: self::PUBLIC_PREVIEW_IMAGE,
            tags: ['Glass', 'Modern', 'Launch'],
            bestFit: ['Modern service sites', 'Product launches', 'Design-led teams'],
            includedSections: ['navigation', 'hero', 'features', 'showcase', 'presets', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'clarity',
                    name: 'Clarity',
                    description: 'Bright glass surfaces, teal structure, and warm action accents.',
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
                    values: [
                        'primaryColor' => '#0f766e',
                        'accentColor' => '#f97316',
                        'neutralColor' => '#24313a',
                        'surfaceColor' => '#f3fbfa',
                        'foregroundColor' => '#10202a',
                        'headingFont' => 'manrope',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'standard',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'xl',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                        'overlayTreatment' => 'subtle',
                    ],
                ),
                new ThemePresetData(
                    key: 'prism',
                    name: 'Prism',
                    description: 'Fresh cyan glass, rose highlights, and spacious editorial cards.',
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
                    values: [
                        'primaryColor' => '#0e7490',
                        'accentColor' => '#db2777',
                        'neutralColor' => '#1f2933',
                        'surfaceColor' => '#edf7fb',
                        'foregroundColor' => '#111827',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'spacious',
                        'cardStyle' => 'layered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'xl',
                        'headingScale' => 'expressive',
                        'cardDensity' => 'spacious',
                        'overlayTreatment' => 'subtle',
                    ],
                ),
                new ThemePresetData(
                    key: 'graphite',
                    name: 'Graphite',
                    description: 'Dark glass panels with amber contrast and compact information density.',
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
                    values: [
                        'primaryColor' => '#334155',
                        'accentColor' => '#f59e0b',
                        'neutralColor' => '#111827',
                        'surfaceColor' => '#151917',
                        'foregroundColor' => '#f7faf7',
                        'headingFont' => 'manrope',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'lg',
                        'headingScale' => 'compact',
                        'cardDensity' => 'compact',
                        'overlayTreatment' => 'strong',
                    ],
                ),
            ],
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
            assets: ['css' => 'vendor/capell/themes/liquid-glass.css'],
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

            $this->publishes([
                __DIR__ . '/../docs/assets/marketplace/extension-card.svg' => public_path(ltrim(self::PUBLIC_PREVIEW_IMAGE, '/')),
            ], 'capell-theme-liquid-glass-assets');
        }

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-liquid-glass');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-liquid-glass');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-liquid-glass::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(
            new VendorAssetData(
                type: VendorAssetEnum::TailwindImport,
                value: 'resources/css/theme-liquid-glass.css',
                packageName: self::$packageName,
                condition: 'theme-css:liquid-glass',
            ),
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
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-liquid-glass::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-liquid-glass::sections.hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-liquid-glass::sections.features', failLoudly: true),
            'showcase' => new ViewSectionRenderer(self::THEME_KEY, 'showcase', 'capell-theme-liquid-glass::sections.showcase', failLoudly: true),
            'presets' => new ViewSectionRenderer(self::THEME_KEY, 'presets', 'capell-theme-liquid-glass::sections.presets', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-liquid-glass::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-liquid-glass::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-liquid-glass::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-liquid-glass::sections.footer', failLoudly: true),
        ];
    }
}
