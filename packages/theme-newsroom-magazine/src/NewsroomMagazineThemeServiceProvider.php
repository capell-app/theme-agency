<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\NewsroomMagazine;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\NewsroomMagazine\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class NewsroomMagazineThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'newsroom-magazine';

    public static string $packageName = 'capell-app/theme-newsroom-magazine';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Newsroom Magazine',
            description: 'Newsroom Magazine gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/newsroom-magazine.jpg',
            tags: ['Newsroom', 'Magazine', 'Editorial', 'Serif', 'Journalism'],
            bestFit: ['News and magazine publications', 'Editorial and opinion sites', 'Multi-author blogs at scale', 'Topic-driven journalism'],
            includedSections: ['navigation', 'featured-story', 'category-nav', 'story-grid', 'most-read', 'newsletter-signup', 'contributors', 'features', 'proof', 'content-listing', 'cta', 'footer', 'hero'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Newsroom Magazine',
                    description: 'Newsroom Magazine visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/newsroom-magazine.jpg',
                    values: [
                        'primaryColor' => '#b91c1c',
                        'accentColor' => '#1d4ed8',
                        'neutralColor' => '#171717',
                        'surfaceColor' => '#fbfaf8',
                        'foregroundColor' => '#171717',
                        'headingFont' => 'fraunces',
                        'bodyFont' => 'inter',
                        'spacing' => 'airy',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'flat',
                        'radius' => 'none',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/newsroom-magazine.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-newsroom-magazine');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-newsroom-magazine');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-newsroom-magazine::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_NEWSROOM_MAGAZINE_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-newsroom-magazine.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-foundation-theme::theme.chrome.navigation', failLoudly: true),
            'featured-story' => new ViewSectionRenderer(self::THEME_KEY, 'featured-story', 'capell-theme-newsroom-magazine::sections.featured-story', failLoudly: true),
            'category-nav' => new ViewSectionRenderer(self::THEME_KEY, 'category-nav', 'capell-theme-newsroom-magazine::sections.category-nav', failLoudly: true),
            'story-grid' => new ViewSectionRenderer(self::THEME_KEY, 'story-grid', 'capell-theme-newsroom-magazine::sections.story-grid', failLoudly: true),
            'most-read' => new ViewSectionRenderer(self::THEME_KEY, 'most-read', 'capell-theme-newsroom-magazine::sections.most-read', failLoudly: true),
            'newsletter-signup' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter-signup', 'capell-theme-newsroom-magazine::sections.newsletter-signup', failLoudly: true),
            'contributors' => new ViewSectionRenderer(self::THEME_KEY, 'contributors', 'capell-theme-newsroom-magazine::sections.contributors', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-newsroom-magazine::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-newsroom-magazine::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-newsroom-magazine::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-newsroom-magazine::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-foundation-theme::theme.chrome.footer', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-newsroom-magazine::sections.hero', failLoudly: true),
        ];
    }
}
