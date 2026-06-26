<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CharacterPortfolioIndex;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\CharacterPortfolioIndex\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class CharacterPortfolioIndexThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'character-portfolio-index';

    public static string $packageName = 'capell-app/theme-character-portfolio-index';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Character Portfolio Index',
            description: 'Characterful portfolio index theme for curated personal, company, design, development, and video portfolios with editorial blurbs, standout notes, creator summaries, and related recommendations.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/character-portfolio-index.jpg',
            tags: ['Portfolio', 'Curation', 'Creators', 'Directory', 'Recommendations'],
            bestFit: ['Portfolio indexes', 'Creator directories', 'Design inspiration sites', 'Personal site galleries', 'Studio recommendation lists'],
            includedSections: ['navigation', 'hero', 'showcase-headline', 'category-tabs', 'curated-grid', 'standout-notes', 'related-recommendations', 'creator-summary', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Character Portfolio Index',
                    description: 'Character Portfolio Index visual preset for soft editorial curation, compact category tabs, long curated grids, playful card accents, standout notes, creator summaries, and related recommendations.',
                    previewImage: '/vendor/capell/themes/character-portfolio-index.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#7b4ee6',
                        'neutralColor' => '#21182b',
                        'surfaceColor' => '#f7f2ff',
                        'foregroundColor' => '#111111',
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
            ],
            assets: ['css' => 'vendor/capell/themes/character-portfolio-index.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-character-portfolio-index');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-character-portfolio-index');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-character-portfolio-index::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_CHARACTER_PORTFOLIO_INDEX_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-character-portfolio-index.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-character-portfolio-index::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-character-portfolio-index::sections.hero', failLoudly: true),
            'showcase-headline' => new ViewSectionRenderer(self::THEME_KEY, 'showcase-headline', 'capell-theme-character-portfolio-index::sections.showcase-headline', failLoudly: true),
            'category-tabs' => new ViewSectionRenderer(self::THEME_KEY, 'category-tabs', 'capell-theme-character-portfolio-index::sections.category-tabs', failLoudly: true),
            'curated-grid' => new ViewSectionRenderer(self::THEME_KEY, 'curated-grid', 'capell-theme-character-portfolio-index::sections.curated-grid', failLoudly: true),
            'standout-notes' => new ViewSectionRenderer(self::THEME_KEY, 'standout-notes', 'capell-theme-character-portfolio-index::sections.standout-notes', failLoudly: true),
            'related-recommendations' => new ViewSectionRenderer(self::THEME_KEY, 'related-recommendations', 'capell-theme-character-portfolio-index::sections.related-recommendations', failLoudly: true),
            'creator-summary' => new ViewSectionRenderer(self::THEME_KEY, 'creator-summary', 'capell-theme-character-portfolio-index::sections.creator-summary', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-character-portfolio-index::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-character-portfolio-index::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-character-portfolio-index::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-character-portfolio-index::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-character-portfolio-index::sections.footer', failLoudly: true),
        ];
    }
}
