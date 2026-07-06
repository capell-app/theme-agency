<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ReelRoom;

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
use Capell\ThemeStudio\ReelRoom\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class ReelRoomThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'reel-room';

    public static string $packageName = 'capell-app/theme-reel-room';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Reel Room',
            description: 'A digital-awards archive with motion previews, jury scores, and sharp media frames on muted grey. Every winner, every credit, every frame accounted for.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/reel-room.jpg',
            tags: ['Reel Room', 'Awards', 'Digital Projects', 'Video', 'Credits'],
            bestFit: ['Digital awards archives', 'Motion preview galleries', 'Interactive project directories', 'Jury score showcases', 'Historical winner archives'],
            includedSections: ['navigation', 'hero', 'archive-hero', 'date-filter-rail', 'winner-list', 'featured-project', 'jury-score-explainer', 'media-credits', 'proof', 'content-listing', 'archive-wall-index', 'time-capsule-browser', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Reel Room',
                    description: 'Reel Room visual preset for industrial grey archives with condensed headings, left-side date filter rail, winner lists, featured project panels, jury score explanations, sharp rectangular media frames, small numeric counters, pill-free technical labels, credits, technology stacks, awards, and restrained preview motion.',
                    previewImage: '/vendor/capell/themes/reel-room.jpg',
                    values: [
                        'primaryColor' => '#050505',
                        'accentColor' => '#ff5c35',
                        'neutralColor' => '#626262',
                        'surfaceColor' => '#d8d8d4',
                        'foregroundColor' => '#050505',
                        'headingFont' => 'condensed',
                        'bodyFont' => 'system',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'sharp',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'motion-preview',
                        'radius' => 'none',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'dense',
                    ],
                ),
                new ThemePresetData(
                    key: 'night-signal',
                    name: 'Night Signal',
                    description: 'Night Signal visual preset for after-hours archive review with near-black surfaces, condensed headings, left-side date filter rail, winner lists, featured project panels, jury score explanations, sharp rectangular media frames, small numeric counters, pill-free technical labels, credits, technology stacks, awards, and lively preview motion lit by an acid-green signal accent.',
                    previewImage: '/vendor/capell/themes/reel-room.jpg',
                    values: [
                        'primaryColor' => '#f5f5f2',
                        'accentColor' => '#9dff5c',
                        'neutralColor' => '#8d8d97',
                        'surfaceColor' => '#121218',
                        'foregroundColor' => '#f5f5f2',
                        'headingFont' => 'condensed',
                        'bodyFont' => 'system',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'sharp',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'lively',
                        'mediaTreatment' => 'motion-preview',
                        'radius' => 'none',
                        'headingScale' => 'compact',
                        'cardDensity' => 'dense',
                    ],
                ),
            ],
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
                // Wave 4b signature-widget variants (theme-bar criterion 3: each
                // signature widget declares >= 2 variants). Guarded by
                // SectionVariantDeclarationTest, which resolves every declared
                // variant to a real sidecar Blade view on disk.
                'sectionVariants' => [
                    'content-listing' => ['default', 'rows'],
                    'date-filter-rail' => ['default', 'compact'],
                    'featured-project' => ['default', 'stacked'],
                    'jury-score-explainer' => ['default', 'matrix'],
                    'archive-wall-index' => ['default', 'compact'],
                    'time-capsule-browser' => ['default', 'void'],
                ],
            ],
            assets: ['css' => 'vendor/capell/themes/reel-room.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-reel-room');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-reel-room');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-reel-room::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-reel-room.css',
            packageName: self::$packageName,
            condition: 'theme-css:reel-room',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-reel-room::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-reel-room::sections.hero', failLoudly: true),
            'archive-hero' => new ViewSectionRenderer(self::THEME_KEY, 'archive-hero', 'capell-theme-reel-room::sections.archive-hero', failLoudly: true),
            'date-filter-rail' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'date-filter-rail',
                baseView: 'capell-theme-reel-room::sections.date-filter-rail',
                variantViews: ['compact' => 'capell-theme-reel-room::sections.date-filter-rail--compact'],
                failLoudly: true,
            ),
            'winner-list' => new ViewSectionRenderer(self::THEME_KEY, 'winner-list', 'capell-theme-reel-room::sections.winner-list', failLoudly: true),
            'featured-project' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'featured-project',
                baseView: 'capell-theme-reel-room::sections.featured-project',
                variantViews: ['stacked' => 'capell-theme-reel-room::sections.featured-project--stacked'],
                failLoudly: true,
            ),
            'jury-score-explainer' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'jury-score-explainer',
                baseView: 'capell-theme-reel-room::sections.jury-score-explainer',
                variantViews: ['matrix' => 'capell-theme-reel-room::sections.jury-score-explainer--matrix'],
                failLoudly: true,
            ),
            'media-credits' => new ViewSectionRenderer(self::THEME_KEY, 'media-credits', 'capell-theme-reel-room::sections.media-credits', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-reel-room::sections.proof', failLoudly: true),
            'content-listing' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'content-listing',
                baseView: 'capell-theme-reel-room::sections.content-listing',
                variantViews: ['rows' => 'capell-theme-reel-room::sections.content-listing--rows'],
                failLoudly: true,
            ),
            'archive-wall-index' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'archive-wall-index',
                baseView: 'capell-theme-reel-room::sections.archive-wall-index',
                variantViews: ['compact' => 'capell-theme-reel-room::sections.archive-wall-index--compact'],
                failLoudly: true,
            ),
            'time-capsule-browser' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'time-capsule-browser',
                baseView: 'capell-theme-reel-room::sections.time-capsule-browser',
                variantViews: ['void' => 'capell-theme-reel-room::sections.time-capsule-browser--void'],
                failLoudly: true,
            ),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-reel-room::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-reel-room::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-reel-room::sections.footer', failLoudly: true),
        ];
    }
}
