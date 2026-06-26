<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DenseNewsAnalysis;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\DenseNewsAnalysis\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class DenseNewsAnalysisThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'dense-news-analysis';

    public static string $packageName = 'capell-app/theme-dense-news-analysis';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Dense News Analysis',
            description: 'Dense news and analysis theme for serious editorial hierarchy, live labels, top stories, opinion, video, topic navigation, missed-it sections, and newsletter inserts.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/dense-news-analysis.jpg',
            tags: ['News', 'Analysis', 'Opinion', 'Video', 'Live'],
            bestFit: ['News publishers', 'Policy journals', 'Analysis desks', 'Regional newspapers', 'Editorial membership sites'],
            includedSections: ['navigation', 'hero', 'top-stories', 'live-brief', 'topic-navigation', 'opinion-analysis', 'video-row', 'missed-it', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Dense News Analysis',
                    description: 'Dense News Analysis visual preset for serious news hierarchy, compact story grids, live labels, topic navigation, opinion blocks, video rows, missed-it recaps, and newsletter inserts.',
                    previewImage: '/vendor/capell/themes/dense-news-analysis.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#b21f2d',
                        'neutralColor' => '#111111',
                        'surfaceColor' => '#ffffff',
                        'foregroundColor' => '#111111',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'photographic',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/dense-news-analysis.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-dense-news-analysis');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-dense-news-analysis');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-dense-news-analysis::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_DENSE_NEWS_ANALYSIS_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-dense-news-analysis.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-dense-news-analysis::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-dense-news-analysis::sections.hero', failLoudly: true),
            'top-stories' => new ViewSectionRenderer(self::THEME_KEY, 'top-stories', 'capell-theme-dense-news-analysis::sections.top-stories', failLoudly: true),
            'live-brief' => new ViewSectionRenderer(self::THEME_KEY, 'live-brief', 'capell-theme-dense-news-analysis::sections.live-brief', failLoudly: true),
            'topic-navigation' => new ViewSectionRenderer(self::THEME_KEY, 'topic-navigation', 'capell-theme-dense-news-analysis::sections.topic-navigation', failLoudly: true),
            'opinion-analysis' => new ViewSectionRenderer(self::THEME_KEY, 'opinion-analysis', 'capell-theme-dense-news-analysis::sections.opinion-analysis', failLoudly: true),
            'video-row' => new ViewSectionRenderer(self::THEME_KEY, 'video-row', 'capell-theme-dense-news-analysis::sections.video-row', failLoudly: true),
            'missed-it' => new ViewSectionRenderer(self::THEME_KEY, 'missed-it', 'capell-theme-dense-news-analysis::sections.missed-it', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-dense-news-analysis::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-dense-news-analysis::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-dense-news-analysis::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-dense-news-analysis::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-dense-news-analysis::sections.footer', failLoudly: true),
        ];
    }
}
