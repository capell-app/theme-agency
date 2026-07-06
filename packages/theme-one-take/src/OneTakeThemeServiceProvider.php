<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OneTake;

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
use Capell\FoundationTheme\Rendering\VariantViewSectionRenderer;
use Capell\FoundationTheme\Support\Editor\StandardThemeEditorSchema;
use Capell\ThemeStudio\OneTake\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class OneTakeThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'one-take';

    public static string $packageName = 'capell-app/theme-one-take';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'One Take',
            description: 'Everything in one continuous take — showcase grid, story, CTA on a single confident page — for galleries and templates that don\'t need a second click.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/one-take.jpg',
            tags: ['One Page', 'Showcase', 'Gallery', 'Templates', 'Resources'],
            bestFit: ['One-page website galleries', 'Landing page showcases', 'Template directories', 'Startup inspiration sites', 'Marketing resource hubs'],
            includedSections: ['navigation', 'hero', 'showcase-hero', 'category-tabs', 'one-page-grid', 'templates-sections', 'tools-sponsors', 'build-resources', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'One Take',
                    description: 'One Take visual preset for warm text colors, spacious cards, compact tags, count proof, submit CTAs, category tabs, screenshot grids, templates, sections, tools, sponsor recommendations, and build-a-one-pager resources.',
                    previewImage: '/vendor/capell/themes/one-take.jpg',
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
                    previewImage: '/vendor/capell/themes/one-take.jpg',
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
                'sectionVariants' => [
                    'showcase-hero' => ['default', 'paginated'],
                    'category-tabs' => ['default', 'tablist'],
                    'one-page-grid' => ['default', 'dense'],
                    'tools-sponsors' => ['default', 'rail'],
                    'build-resources' => ['default', 'split'],
                ],
                'editor' => StandardThemeEditorSchema::definition(),
            ],
            assets: ['css' => 'vendor/capell/themes/one-take.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-one-take');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-one-take');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-one-take::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-one-take.css',
            packageName: self::$packageName,
            condition: 'theme-css:one-take',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-one-take::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-one-take::sections.hero', failLoudly: true),
            'showcase-hero' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'showcase-hero',
                baseView: 'capell-theme-one-take::sections.showcase-hero',
                variantViews: ['paginated' => 'capell-theme-one-take::sections.showcase-hero--paginated'],
                failLoudly: true,
            ),
            'category-tabs' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'category-tabs',
                baseView: 'capell-theme-one-take::sections.category-tabs',
                variantViews: ['tablist' => 'capell-theme-one-take::sections.category-tabs--tablist'],
                failLoudly: true,
            ),
            'one-page-grid' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'one-page-grid',
                baseView: 'capell-theme-one-take::sections.one-page-grid',
                variantViews: ['dense' => 'capell-theme-one-take::sections.one-page-grid--dense'],
                failLoudly: true,
            ),
            'templates-sections' => new ViewSectionRenderer(self::THEME_KEY, 'templates-sections', 'capell-theme-one-take::sections.templates-sections', failLoudly: true),
            'tools-sponsors' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'tools-sponsors',
                baseView: 'capell-theme-one-take::sections.tools-sponsors',
                variantViews: ['rail' => 'capell-theme-one-take::sections.tools-sponsors--rail'],
                failLoudly: true,
            ),
            'build-resources' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'build-resources',
                baseView: 'capell-theme-one-take::sections.build-resources',
                variantViews: ['split' => 'capell-theme-one-take::sections.build-resources--split'],
                failLoudly: true,
            ),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-one-take::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-one-take::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-one-take::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-one-take::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-one-take::sections.footer', failLoudly: true),
        ];
    }
}
