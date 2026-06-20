<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MotionArchive;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\MotionArchive\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class MotionArchiveThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'motion-archive';

    public static string $packageName = 'capell-app/theme-motion-archive';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Motion Archive',
            description: 'Motion Archive theme for digital-awards archives with muted grey pages, black typography, condensed headings, left-side month and year filters, featured project panels, winner lists, jury and score explainers, sharp media frames, technical labels, credits, technology stacks, awards, galleries, and historical links.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/motion-archive.jpg',
            tags: ['Motion Archive', 'Awards', 'Digital Projects', 'Video', 'Credits'],
            bestFit: ['Digital awards archives', 'Motion preview galleries', 'Interactive project directories', 'Jury score showcases', 'Historical winner archives'],
            includedSections: ['navigation', 'hero', 'archive-hero', 'date-filter-rail', 'winner-list', 'featured-project', 'jury-score-explainer', 'media-credits', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Motion Archive',
                    description: 'Motion Archive visual preset for industrial grey archives with condensed headings, left-side date filter rail, winner lists, featured project panels, jury score explanations, sharp rectangular media frames, small numeric counters, pill-free technical labels, credits, technology stacks, awards, and restrained preview motion.',
                    previewImage: '/vendor/capell/themes/motion-archive.jpg',
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
            ],
            assets: ['css' => 'vendor/capell/themes/motion-archive.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-motion-archive');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-motion-archive');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-motion-archive::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-motion-archive.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-motion-archive::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-motion-archive::sections.hero', failLoudly: true),
            'archive-hero' => new ViewSectionRenderer(self::THEME_KEY, 'archive-hero', 'capell-theme-motion-archive::sections.archive-hero', failLoudly: true),
            'date-filter-rail' => new ViewSectionRenderer(self::THEME_KEY, 'date-filter-rail', 'capell-theme-motion-archive::sections.date-filter-rail', failLoudly: true),
            'winner-list' => new ViewSectionRenderer(self::THEME_KEY, 'winner-list', 'capell-theme-motion-archive::sections.winner-list', failLoudly: true),
            'featured-project' => new ViewSectionRenderer(self::THEME_KEY, 'featured-project', 'capell-theme-motion-archive::sections.featured-project', failLoudly: true),
            'jury-score-explainer' => new ViewSectionRenderer(self::THEME_KEY, 'jury-score-explainer', 'capell-theme-motion-archive::sections.jury-score-explainer', failLoudly: true),
            'media-credits' => new ViewSectionRenderer(self::THEME_KEY, 'media-credits', 'capell-theme-motion-archive::sections.media-credits', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-motion-archive::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-motion-archive::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-motion-archive::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-motion-archive::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-motion-archive::sections.footer', failLoudly: true),
        ];
    }
}
