<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FieldGuide;

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
use Capell\ThemeStudio\FieldGuide\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class FieldGuideThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'field-guide';

    public static string $packageName = 'capell-app/theme-field-guide';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Field Guide',
            description: 'A reference library you can actually navigate: multi-taxonomy filters, dense grids, thousands of entries kept findable. Inspiration at catalogue scale.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/field-guide.jpg',
            tags: ['Inspiration Library', 'Filters', 'Gallery', 'Taxonomies', 'Search'],
            bestFit: ['Large website inspiration libraries', 'Design reference archives', 'Template discovery sites', 'Multi-taxonomy galleries', 'Curated product directories'],
            includedSections: ['navigation', 'hero', 'filter-hero', 'taxonomy-navigation', 'taxonomy-grid-browser', 'editor-picks', 'latest-designs', 'blog-mission', 'faq-archives', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Field Guide',
                    description: 'Field Guide visual preset for high-density inspiration libraries with a clean white canvas, crisp sans typography, expandable filter groups, selected chips, saved views, category mega menus, editor picks, latest designs, blog modules, mission content, FAQ, quick actions, and scalable pagination.',
                    previewImage: '/vendor/capell/themes/field-guide.jpg',
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
                    previewImage: '/vendor/capell/themes/field-guide.jpg',
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
                'sectionVariants' => [
                    'taxonomy-grid-browser' => ['default', 'compact'],
                    'latest-designs' => ['default', 'showcase-wide'],
                    'editor-picks' => ['default', 'alternating'],
                    'faq-archives' => ['default', 'two-column'],
                    'cta' => ['default', 'browse'],
                ],
            ],
            assets: ['css' => 'vendor/capell/themes/field-guide.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-field-guide');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-field-guide');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-field-guide::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-field-guide.css',
            packageName: self::$packageName,
            condition: 'theme-css:field-guide',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-field-guide::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-field-guide::sections.hero', failLoudly: true),
            'filter-hero' => new ViewSectionRenderer(self::THEME_KEY, 'filter-hero', 'capell-theme-field-guide::sections.filter-hero', failLoudly: true),
            'taxonomy-navigation' => new ViewSectionRenderer(self::THEME_KEY, 'taxonomy-navigation', 'capell-theme-field-guide::sections.taxonomy-navigation', failLoudly: true),
            'taxonomy-grid-browser' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'taxonomy-grid-browser',
                baseView: 'capell-theme-field-guide::sections.taxonomy-grid-browser',
                variantViews: ['compact' => 'capell-theme-field-guide::sections.taxonomy-grid-browser--compact'],
                failLoudly: true,
            ),
            'editor-picks' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'editor-picks',
                baseView: 'capell-theme-field-guide::sections.editor-picks',
                variantViews: ['alternating' => 'capell-theme-field-guide::sections.editor-picks--alternating'],
                failLoudly: true,
            ),
            'latest-designs' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'latest-designs',
                baseView: 'capell-theme-field-guide::sections.latest-designs',
                variantViews: ['showcase-wide' => 'capell-theme-field-guide::sections.latest-designs--showcase-wide'],
                failLoudly: true,
            ),
            'blog-mission' => new ViewSectionRenderer(self::THEME_KEY, 'blog-mission', 'capell-theme-field-guide::sections.blog-mission', failLoudly: true),
            'faq-archives' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'faq-archives',
                baseView: 'capell-theme-field-guide::sections.faq-archives',
                variantViews: ['two-column' => 'capell-theme-field-guide::sections.faq-archives--two-column'],
                failLoudly: true,
            ),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-field-guide::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-field-guide::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-field-guide::sections.newsletter', failLoudly: true),
            'cta' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'cta',
                baseView: 'capell-theme-field-guide::sections.cta',
                variantViews: ['browse' => 'capell-theme-field-guide::sections.cta--browse'],
                failLoudly: true,
            ),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-field-guide::sections.footer', failLoudly: true),
        ];
    }
}
