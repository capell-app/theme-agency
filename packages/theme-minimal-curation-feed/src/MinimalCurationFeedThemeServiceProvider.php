<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MinimalCurationFeed;

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
use Capell\ThemeStudio\MinimalCurationFeed\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class MinimalCurationFeedThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'minimal-curation-feed';

    public static string $packageName = 'capell-app/theme-minimal-curation-feed';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Minimal Curation Feed',
            description: 'Minimal Curation Feed theme for daily curated links, screenshots, apps, product references, and inspiration feeds with white pages, system sans typography, hairline borders, tiny labels, category tabs, search, newsletter signup, metadata cards, best-of views, latest feeds, apps, websites, and icons.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/minimal-curation-feed.jpg',
            tags: ['Curation Feed', 'Minimal', 'Screenshots', 'Apps', 'References'],
            bestFit: ['Daily inspiration feeds', 'Product reference libraries', 'Screenshot curation sites', 'App and website directories', 'Icon inspiration archives'],
            includedSections: ['navigation', 'hero', 'feed-hero', 'category-tabs', 'curation-feed', 'best-of-views', 'app-website-icons', 'source-metadata', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Minimal Curation Feed',
                    description: 'Minimal Curation Feed visual preset for calm white feeds with system sans typography, hairline borders, tiny labels, loose masonry columns, screenshot cards, maker/source/rating/platform metadata, live update indicators, category tabs, search, newsletter signup, best-of lists, latest, apps, websites, and icons.',
                    previewImage: '/vendor/capell/themes/minimal-curation-feed.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#2563eb',
                        'neutralColor' => '#6b7280',
                        'surfaceColor' => '#ffffff',
                        'foregroundColor' => '#111111',
                        'headingFont' => 'system',
                        'bodyFont' => 'system',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'hairline',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'curation-feed',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'dense',
                    ],
                ),
                new ThemePresetData(
                    key: 'night-feed',
                    name: 'Night Feed',
                    description: 'Night Feed visual preset for a near-black curation feed with system sans typography, hairline borders in low-contrast graphite, tight masonry columns, and a cool blue accent for links and live update indicators.',
                    previewImage: '/vendor/capell/themes/minimal-curation-feed.jpg',
                    values: [
                        'primaryColor' => '#f5f5f5',
                        'accentColor' => '#60a5fa',
                        'neutralColor' => '#9ca3af',
                        'surfaceColor' => '#0b0b0d',
                        'foregroundColor' => '#f5f5f5',
                        'headingFont' => 'system',
                        'bodyFont' => 'system',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'hairline',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'minimal',
                        'mediaTreatment' => 'curation-feed',
                        'radius' => 'xs',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'dense',
                    ],
                ),
            ],
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
            assets: ['css' => 'vendor/capell/themes/minimal-curation-feed.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-minimal-curation-feed');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-minimal-curation-feed');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-minimal-curation-feed::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-minimal-curation-feed.css',
            packageName: self::$packageName,
            condition: 'theme-css:minimal-curation-feed',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-minimal-curation-feed::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-minimal-curation-feed::sections.hero', failLoudly: true),
            'feed-hero' => new ViewSectionRenderer(self::THEME_KEY, 'feed-hero', 'capell-theme-minimal-curation-feed::sections.feed-hero', failLoudly: true),
            'category-tabs' => new ViewSectionRenderer(self::THEME_KEY, 'category-tabs', 'capell-theme-minimal-curation-feed::sections.category-tabs', failLoudly: true),
            'curation-feed' => new ViewSectionRenderer(self::THEME_KEY, 'curation-feed', 'capell-theme-minimal-curation-feed::sections.curation-feed', failLoudly: true),
            'best-of-views' => new ViewSectionRenderer(self::THEME_KEY, 'best-of-views', 'capell-theme-minimal-curation-feed::sections.best-of-views', failLoudly: true),
            'app-website-icons' => new ViewSectionRenderer(self::THEME_KEY, 'app-website-icons', 'capell-theme-minimal-curation-feed::sections.app-website-icons', failLoudly: true),
            'source-metadata' => new ViewSectionRenderer(self::THEME_KEY, 'source-metadata', 'capell-theme-minimal-curation-feed::sections.source-metadata', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-minimal-curation-feed::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-minimal-curation-feed::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-minimal-curation-feed::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-minimal-curation-feed::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-minimal-curation-feed::sections.footer', failLoudly: true),
        ];
    }
}
