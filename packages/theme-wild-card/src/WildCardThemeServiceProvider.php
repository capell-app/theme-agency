<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\WildCard;

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
use Capell\ThemeStudio\WildCard\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class WildCardThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'wild-card';

    public static string $packageName = 'capell-app/theme-wild-card';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Wild Card',
            description: 'An award-show directory with an experimental streak — submission galleries, bold type, rules made to bend. For creative industries that hate looking corporate.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/wild-card.jpg',
            tags: ['Wild Card', 'Creative Awards', 'Portfolio', 'Collections', 'Submissions'],
            bestFit: ['Creative industry directories', 'Agency and studio showcases', 'Portfolio-heavy CMS sites', 'Architecture and artist indexes', 'Award-style submission galleries'],
            includedSections: ['navigation', 'hero', 'featured-today-banner', 'metadata-facet-wall', 'card-shuffle-grid', 'winners-ledger-table', 'submission-pulse', 'infinite-scroll-depth-pressure', 'profiles-resources', 'sponsor-modules', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Wild Card',
                    description: 'Wild Card visual preset for pale neutral creative directories with tight grid rules, oversized project titles, compact uppercase metadata, status badges, large image feature blocks, latest submissions, winners, collections, profiles, resources, sponsor modules, and tasteful hover reveals.',
                    previewImage: '/vendor/capell/themes/wild-card.jpg',
                    values: [
                        'primaryColor' => '#050505',
                        'accentColor' => '#d44a1f',
                        'neutralColor' => '#55524d',
                        'surfaceColor' => '#eee9df',
                        'foregroundColor' => '#050505',
                        'headingFont' => 'condensed',
                        'bodyFont' => 'system',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'sharp',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'feature-slab',
                        'radius' => 'none',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'dense',
                    ],
                ),
                new ThemePresetData(
                    key: 'jury-panel',
                    name: 'Jury Panel',
                    description: 'Jury Panel visual preset for a deep-ink shortlist review room with a plum-teal accent, chalk-white surface, tighter grid rules, oversized project titles, compact uppercase metadata, status badges, large image feature blocks, latest submissions, winners, collections, profiles, resources, sponsor modules, and brisk hover reveals.',
                    previewImage: '/vendor/capell/themes/wild-card.jpg',
                    values: [
                        'primaryColor' => '#161821',
                        'accentColor' => '#5b3b6b',
                        'neutralColor' => '#6f6a72',
                        'surfaceColor' => '#f6f4f0',
                        'foregroundColor' => '#161821',
                        'headingFont' => 'condensed',
                        'bodyFont' => 'system',
                        'spacing' => 'tight',
                        'alignment' => 'left',
                        'cardStyle' => 'sharp',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'energetic',
                        'mediaTreatment' => 'feature-slab',
                        'radius' => 'none',
                        'headingScale' => 'oversized',
                        'cardDensity' => 'dense',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/wild-card.css'],
            runtime: FrontendRuntime::Blade,
            extends: 'default',
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
                'sectionVariants' => [
                    'card-shuffle-grid' => ['default', 'compact'],
                    'metadata-facet-wall' => ['default', 'dense'],
                    'featured-today-banner' => ['default', 'split'],
                    'winners-ledger-table' => ['default', 'collections'],
                    'submission-pulse' => ['default', 'compact'],
                    'infinite-scroll-depth-pressure' => ['default', 'rows'],
                ],
            ],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-wild-card');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-wild-card');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-wild-card::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-wild-card.css',
            packageName: self::$packageName,
            condition: 'theme-css:wild-card',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-wild-card::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-wild-card::sections.hero', failLoudly: true),
            'featured-today-banner' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'featured-today-banner',
                baseView: 'capell-theme-wild-card::sections.featured-today-banner',
                variantViews: ['split' => 'capell-theme-wild-card::sections.featured-today-banner--split'],
                failLoudly: true,
            ),
            'metadata-facet-wall' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'metadata-facet-wall',
                baseView: 'capell-theme-wild-card::sections.metadata-facet-wall',
                variantViews: ['dense' => 'capell-theme-wild-card::sections.metadata-facet-wall--dense'],
                failLoudly: true,
            ),
            'card-shuffle-grid' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'card-shuffle-grid',
                baseView: 'capell-theme-wild-card::sections.card-shuffle-grid',
                variantViews: ['compact' => 'capell-theme-wild-card::sections.card-shuffle-grid--compact'],
                failLoudly: true,
            ),
            'winners-ledger-table' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'winners-ledger-table',
                baseView: 'capell-theme-wild-card::sections.winners-ledger-table',
                variantViews: ['collections' => 'capell-theme-wild-card::sections.winners-ledger-table--collections'],
                failLoudly: true,
            ),
            'submission-pulse' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'submission-pulse',
                baseView: 'capell-theme-wild-card::sections.submission-pulse',
                variantViews: ['compact' => 'capell-theme-wild-card::sections.submission-pulse--compact'],
                failLoudly: true,
            ),
            'infinite-scroll-depth-pressure' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'infinite-scroll-depth-pressure',
                baseView: 'capell-theme-wild-card::sections.infinite-scroll-depth-pressure',
                variantViews: ['rows' => 'capell-theme-wild-card::sections.infinite-scroll-depth-pressure--rows'],
                failLoudly: true,
            ),
            'profiles-resources' => new ViewSectionRenderer(self::THEME_KEY, 'profiles-resources', 'capell-theme-wild-card::sections.profiles-resources', failLoudly: true),
            'sponsor-modules' => new ViewSectionRenderer(self::THEME_KEY, 'sponsor-modules', 'capell-theme-wild-card::sections.sponsor-modules', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-wild-card::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-wild-card::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-wild-card::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-wild-card::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-wild-card::sections.footer', failLoudly: true),
        ];
    }
}
