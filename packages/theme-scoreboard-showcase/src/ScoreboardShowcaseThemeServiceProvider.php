<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ScoreboardShowcase;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\ScoreboardShowcase\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class ScoreboardShowcaseThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'scoreboard-showcase';

    public static string $packageName = 'capell-app/theme-scoreboard-showcase';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Scoreboard Showcase',
            description: 'Scoreboard Showcase theme for design-awards rankings, nominations, and judging with warm off-white pages, compact uppercase labels, numeric scores, segmented criteria, winner-of-the-day modules, newest nominees, previous winners, score breakdowns, creator credits, and voting CTAs.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/scoreboard-showcase.jpg',
            tags: ['Awards', 'Scoreboard', 'Nominations', 'Judging', 'Showcase'],
            bestFit: ['Design awards sites', 'Nominee showcases', 'Voting galleries', 'Judged portfolio directories', 'Creative rankings'],
            includedSections: ['navigation', 'hero', 'winner-hero', 'score-criteria', 'newest-nominees', 'previous-winners', 'voting-status', 'creator-credits', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Scoreboard Showcase',
                    description: 'Scoreboard Showcase visual preset for warm off-white rankings with compact uppercase labels, numeric scores, segmented UI/UX/innovation/overall criteria, winner of the day, newest nominees, previous winners, public voting states, creator credits, and precise score cards.',
                    previewImage: '/vendor/capell/themes/scoreboard-showcase.jpg',
                    values: [
                        'primaryColor' => '#241f1b',
                        'accentColor' => '#c2410c',
                        'neutralColor' => '#5f5147',
                        'surfaceColor' => '#fbf3e7',
                        'foregroundColor' => '#241f1b',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'scoreboard',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'score-media',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/scoreboard-showcase.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-scoreboard-showcase');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-scoreboard-showcase');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-scoreboard-showcase::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_SCOREBOARD_SHOWCASE_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-scoreboard-showcase.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-scoreboard-showcase::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-scoreboard-showcase::sections.hero', failLoudly: true),
            'winner-hero' => new ViewSectionRenderer(self::THEME_KEY, 'winner-hero', 'capell-theme-scoreboard-showcase::sections.winner-hero', failLoudly: true),
            'score-criteria' => new ViewSectionRenderer(self::THEME_KEY, 'score-criteria', 'capell-theme-scoreboard-showcase::sections.score-criteria', failLoudly: true),
            'newest-nominees' => new ViewSectionRenderer(self::THEME_KEY, 'newest-nominees', 'capell-theme-scoreboard-showcase::sections.newest-nominees', failLoudly: true),
            'previous-winners' => new ViewSectionRenderer(self::THEME_KEY, 'previous-winners', 'capell-theme-scoreboard-showcase::sections.previous-winners', failLoudly: true),
            'voting-status' => new ViewSectionRenderer(self::THEME_KEY, 'voting-status', 'capell-theme-scoreboard-showcase::sections.voting-status', failLoudly: true),
            'creator-credits' => new ViewSectionRenderer(self::THEME_KEY, 'creator-credits', 'capell-theme-scoreboard-showcase::sections.creator-credits', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-scoreboard-showcase::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-scoreboard-showcase::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-scoreboard-showcase::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-scoreboard-showcase::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-scoreboard-showcase::sections.footer', failLoudly: true),
        ];
    }
}
