<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OnePageShowcase;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Rendering\ChromeSplitBladeThemeRenderer;
use Capell\FoundationTheme\Support\Editor\StandardThemeEditorSchema;
use Capell\ThemeStudio\OnePageShowcase\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class OnePageShowcaseThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'one-page-showcase';

    public static string $packageName = 'capell-app/theme-one-page-showcase';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'One Page Showcase',
            description: 'One Page Showcase theme for curated shipped one-pagers with warm text, spacious cards, compact tags, count proof, submit CTAs, category tabs, templates, sections, tools, sponsors, and build resources.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/one-page-showcase.jpg',
            tags: ['One Page', 'Showcase', 'Gallery', 'Templates', 'Resources'],
            bestFit: ['One-page website galleries', 'Landing page showcases', 'Template directories', 'Startup inspiration sites', 'Marketing resource hubs'],
            includedSections: ['navigation', 'hero', 'showcase-hero', 'category-tabs', 'one-page-grid', 'templates-sections', 'tools-sponsors', 'build-resources', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'One Page Showcase',
                    description: 'One Page Showcase visual preset for warm text colors, spacious cards, compact tags, count proof, submit CTAs, category tabs, screenshot grids, templates, sections, tools, sponsor recommendations, and build-a-one-pager resources.',
                    previewImage: '/vendor/capell/themes/one-page-showcase.jpg',
                    values: [
                        'primaryColor' => '#2f1d12',
                        'accentColor' => '#ff7a3d',
                        'neutralColor' => '#3a271c',
                        'surfaceColor' => '#fff8ef',
                        'foregroundColor' => '#2f1d12',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
                new ThemePresetData(
                    key: 'blueprint',
                    name: 'Blueprint',
                    description: 'Blueprint visual preset for stark ink-black text on paper-white surfaces with a single acid-lime accent, tight grids, and dense one-pager listings.',
                    previewImage: '/vendor/capell/themes/one-page-showcase.jpg',
                    values: [
                        'primaryColor' => '#0a0a0a',
                        'accentColor' => '#c6ff2f',
                        'neutralColor' => '#1c1c1c',
                        'surfaceColor' => '#ffffff',
                        'foregroundColor' => '#0a0a0a',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'outlined',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'grid',
                        'motionIntensity' => 'none',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'none',
                        'headingScale' => 'condensed',
                        'cardDensity' => 'dense',
                    ],
                ),
            ],
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
            assets: ['css' => 'vendor/capell/themes/one-page-showcase.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-one-page-showcase');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-one-page-showcase');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-one-page-showcase::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-one-page-showcase.css',
            packageName: self::$packageName,
            condition: 'theme-css:one-page-showcase',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-one-page-showcase::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-one-page-showcase::sections.hero', failLoudly: true),
            'showcase-hero' => new ViewSectionRenderer(self::THEME_KEY, 'showcase-hero', 'capell-theme-one-page-showcase::sections.showcase-hero', failLoudly: true),
            'category-tabs' => new ViewSectionRenderer(self::THEME_KEY, 'category-tabs', 'capell-theme-one-page-showcase::sections.category-tabs', failLoudly: true),
            'one-page-grid' => new ViewSectionRenderer(self::THEME_KEY, 'one-page-grid', 'capell-theme-one-page-showcase::sections.one-page-grid', failLoudly: true),
            'templates-sections' => new ViewSectionRenderer(self::THEME_KEY, 'templates-sections', 'capell-theme-one-page-showcase::sections.templates-sections', failLoudly: true),
            'tools-sponsors' => new ViewSectionRenderer(self::THEME_KEY, 'tools-sponsors', 'capell-theme-one-page-showcase::sections.tools-sponsors', failLoudly: true),
            'build-resources' => new ViewSectionRenderer(self::THEME_KEY, 'build-resources', 'capell-theme-one-page-showcase::sections.build-resources', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-one-page-showcase::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-one-page-showcase::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-one-page-showcase::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-one-page-showcase::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-one-page-showcase::sections.footer', failLoudly: true),
        ];
    }
}
