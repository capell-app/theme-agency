<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DenseNewsAnalysis;

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
                        'primaryColor' => '#17140f',
                        'accentColor' => '#b3261e',
                        'neutralColor' => '#17140f',
                        'surfaceColor' => '#faf7f1',
                        'foregroundColor' => '#17140f',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'none',
                        'headingScale' => 'compact',
                        'cardDensity' => 'compact',
                    ],
                ),
                new ThemePresetData(
                    key: 'wire-desk',
                    name: 'Wire Desk',
                    description: 'Starker teletype-inspired preset with a colder ink palette, tighter grids, and near-static motion for high-density wire and analysis desks.',
                    previewImage: '/vendor/capell/themes/dense-news-analysis.jpg',
                    values: [
                        'primaryColor' => '#0b0d10',
                        'accentColor' => '#1f6feb',
                        'neutralColor' => '#0b0d10',
                        'surfaceColor' => '#eef1f4',
                        'foregroundColor' => '#0b0d10',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'tight',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'none',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'none',
                        'headingScale' => 'compact',
                        'cardDensity' => 'tight',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/dense-news-analysis.css'],
            runtime: FrontendRuntime::Blade,
            extends: 'default',
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-dense-news-analysis');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-dense-news-analysis');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-dense-news-analysis::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-dense-news-analysis.css',
            packageName: self::$packageName,
            condition: 'theme-css:dense-news-analysis',
        ));
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
