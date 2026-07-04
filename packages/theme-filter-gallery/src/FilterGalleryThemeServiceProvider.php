<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FilterGallery;

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
use Capell\ThemeStudio\FilterGallery\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class FilterGalleryThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'filter-gallery';

    public static string $packageName = 'capell-app/theme-filter-gallery';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Filter Gallery',
            description: 'Filter Gallery theme for broad inspiration libraries with a clean white canvas, dense taxonomy filters, selected-filter chips, saved views, editor picks, latest designs, blog modules, mission content, FAQ, category mega menus, consistent thumbnails, quick actions, and pagination at scale.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/filter-gallery.jpg',
            tags: ['Inspiration Library', 'Filters', 'Gallery', 'Taxonomies', 'Search'],
            bestFit: ['Large website inspiration libraries', 'Design reference archives', 'Template discovery sites', 'Multi-taxonomy galleries', 'Curated product directories'],
            includedSections: ['navigation', 'hero', 'filter-hero', 'taxonomy-navigation', 'editor-picks', 'latest-designs', 'blog-mission', 'faq-archives', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Filter Gallery',
                    description: 'Filter Gallery visual preset for high-density inspiration libraries with a clean white canvas, crisp sans typography, expandable filter groups, selected chips, saved views, category mega menus, editor picks, latest designs, blog modules, mission content, FAQ, quick actions, and scalable pagination.',
                    previewImage: '/vendor/capell/themes/filter-gallery.jpg',
                    values: [
                        'primaryColor' => '#111827',
                        'accentColor' => '#0ea5e9',
                        'neutralColor' => '#475569',
                        'surfaceColor' => '#ffffff',
                        'foregroundColor' => '#111827',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'thumbnail-grid',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'dense',
                    ],
                ),
                new ThemePresetData(
                    key: 'archive-noir',
                    name: 'Archive Noir',
                    description: 'Archive Noir visual preset for the same high-density inspiration library reimagined as a moody after-hours archive, with a charcoal canvas, ember-orange accents, tighter card rhythm, and punchier motion for browsing at night.',
                    previewImage: '/vendor/capell/themes/filter-gallery.jpg',
                    values: [
                        'primaryColor' => '#f8fafc',
                        'accentColor' => '#fb923c',
                        'neutralColor' => '#94a3b8',
                        'surfaceColor' => '#0b1120',
                        'foregroundColor' => '#e2e8f0',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'energetic',
                        'mediaTreatment' => 'thumbnail-grid',
                        'radius' => 'sm',
                        'headingScale' => 'bold',
                        'cardDensity' => 'dense',
                    ],
                ),
            ],
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
            assets: ['css' => 'vendor/capell/themes/filter-gallery.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-filter-gallery');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-filter-gallery');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-filter-gallery::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-filter-gallery.css',
            packageName: self::$packageName,
            condition: 'theme-css:filter-gallery',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-filter-gallery::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-filter-gallery::sections.hero', failLoudly: true),
            'filter-hero' => new ViewSectionRenderer(self::THEME_KEY, 'filter-hero', 'capell-theme-filter-gallery::sections.filter-hero', failLoudly: true),
            'taxonomy-navigation' => new ViewSectionRenderer(self::THEME_KEY, 'taxonomy-navigation', 'capell-theme-filter-gallery::sections.taxonomy-navigation', failLoudly: true),
            'editor-picks' => new ViewSectionRenderer(self::THEME_KEY, 'editor-picks', 'capell-theme-filter-gallery::sections.editor-picks', failLoudly: true),
            'latest-designs' => new ViewSectionRenderer(self::THEME_KEY, 'latest-designs', 'capell-theme-filter-gallery::sections.latest-designs', failLoudly: true),
            'blog-mission' => new ViewSectionRenderer(self::THEME_KEY, 'blog-mission', 'capell-theme-filter-gallery::sections.blog-mission', failLoudly: true),
            'faq-archives' => new ViewSectionRenderer(self::THEME_KEY, 'faq-archives', 'capell-theme-filter-gallery::sections.faq-archives', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-filter-gallery::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-filter-gallery::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-filter-gallery::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-filter-gallery::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-filter-gallery::sections.footer', failLoudly: true),
        ];
    }
}
