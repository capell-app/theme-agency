<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuietWebGallery;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\QuietWebGallery\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class QuietWebGalleryThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'quiet-web-gallery';

    public static string $packageName = 'capell-app/theme-quiet-web-gallery';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Quiet Web Gallery',
            description: 'Quiet Web Gallery theme for calm web-design galleries with light grey backgrounds, modest typography, practical browse panels, understated image grids, latest showcase entries, sponsor space, category archives, random picks, best-of lists, and simple editorial posts.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/quiet-web-gallery.jpg',
            tags: ['Web Gallery', 'Quiet', 'Inspiration', 'Archives', 'Editorial'],
            bestFit: ['Calm web design galleries', 'Style and category archives', 'Website inspiration sites', 'Best-of collections', 'Design blog libraries'],
            includedSections: ['navigation', 'hero', 'browse-panels', 'style-type-categories', 'latest-showcase', 'sponsor-space', 'random-best-of', 'editorial-posts', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Quiet Web Gallery',
                    description: 'Quiet Web Gallery visual preset for light grey gallery pages with modest sans typography, small serif accents, utilitarian browse panels, understated thumbnail grids, sponsor space, category archives, random picks, best-of lists, and simple editorial posts.',
                    previewImage: '/vendor/capell/themes/quiet-web-gallery.jpg',
                    values: [
                        'primaryColor' => '#252525',
                        'accentColor' => '#6f7d6a',
                        'neutralColor' => '#6b6b66',
                        'surfaceColor' => '#f3f3f0',
                        'foregroundColor' => '#252525',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'understated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'quiet-image-grid',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'balanced',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/quiet-web-gallery.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-quiet-web-gallery');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-quiet-web-gallery');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-quiet-web-gallery::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-quiet-web-gallery.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-quiet-web-gallery::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-quiet-web-gallery::sections.hero', failLoudly: true),
            'browse-panels' => new ViewSectionRenderer(self::THEME_KEY, 'browse-panels', 'capell-theme-quiet-web-gallery::sections.browse-panels', failLoudly: true),
            'style-type-categories' => new ViewSectionRenderer(self::THEME_KEY, 'style-type-categories', 'capell-theme-quiet-web-gallery::sections.style-type-categories', failLoudly: true),
            'latest-showcase' => new ViewSectionRenderer(self::THEME_KEY, 'latest-showcase', 'capell-theme-quiet-web-gallery::sections.latest-showcase', failLoudly: true),
            'sponsor-space' => new ViewSectionRenderer(self::THEME_KEY, 'sponsor-space', 'capell-theme-quiet-web-gallery::sections.sponsor-space', failLoudly: true),
            'random-best-of' => new ViewSectionRenderer(self::THEME_KEY, 'random-best-of', 'capell-theme-quiet-web-gallery::sections.random-best-of', failLoudly: true),
            'editorial-posts' => new ViewSectionRenderer(self::THEME_KEY, 'editorial-posts', 'capell-theme-quiet-web-gallery::sections.editorial-posts', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-quiet-web-gallery::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-quiet-web-gallery::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-quiet-web-gallery::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-quiet-web-gallery::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-quiet-web-gallery::sections.footer', failLoudly: true),
        ];
    }
}
