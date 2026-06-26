<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PodcastShow;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\PodcastShow\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class PodcastShowThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'podcast-show';

    public static string $packageName = 'capell-app/theme-podcast-show';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Podcast Show',
            description: 'Podcast Show gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/podcast-show.jpg',
            tags: ['Podcast', 'Audio', 'Show', 'Episodes', 'Editorial'],
            bestFit: ['Podcasts and audio shows', 'Interview series', 'Video/audio shows with episodes', 'Creator-led shows'],
            includedSections: ['navigation', 'hero', 'latest-episode', 'episode-list', 'subscribe-platforms', 'features', 'hosts', 'guests', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Podcast Show',
                    description: 'Podcast Show visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/podcast-show.jpg',
                    values: [
                        'primaryColor' => '#9333ea',
                        'accentColor' => '#fb923c',
                        'neutralColor' => '#2a1e2e',
                        'surfaceColor' => '#fdf7f3',
                        'foregroundColor' => '#2a1e2e',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'lg',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/podcast-show.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-podcast-show');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-podcast-show');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-podcast-show::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_PODCAST_SHOW_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-podcast-show.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-podcast-show::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-podcast-show::sections.hero', failLoudly: true),
            'latest-episode' => new ViewSectionRenderer(self::THEME_KEY, 'latest-episode', 'capell-theme-podcast-show::sections.latest-episode', failLoudly: true),
            'episode-list' => new ViewSectionRenderer(self::THEME_KEY, 'episode-list', 'capell-theme-podcast-show::sections.episode-list', failLoudly: true),
            'subscribe-platforms' => new ViewSectionRenderer(self::THEME_KEY, 'subscribe-platforms', 'capell-theme-podcast-show::sections.subscribe-platforms', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-podcast-show::sections.features', failLoudly: true),
            'hosts' => new ViewSectionRenderer(self::THEME_KEY, 'hosts', 'capell-theme-podcast-show::sections.hosts', failLoudly: true),
            'guests' => new ViewSectionRenderer(self::THEME_KEY, 'guests', 'capell-theme-podcast-show::sections.guests', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-podcast-show::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-podcast-show::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-podcast-show::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-podcast-show::sections.footer', failLoudly: true),
        ];
    }
}
