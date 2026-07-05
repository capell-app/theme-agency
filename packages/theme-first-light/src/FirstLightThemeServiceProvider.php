<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FirstLight;

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
use Capell\ThemeStudio\FirstLight\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class FirstLightThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'first-light';

    public static string $packageName = 'capell-app/theme-first-light';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'First Light',
            description: 'A calm daily feed of screenshots and finds: hairline borders, tiny labels, loose masonry, zero noise. Curation that respects the reader\'s morning.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/first-light.jpg',
            tags: ['Curation Feed', 'Minimal', 'Screenshots', 'Apps', 'References'],
            bestFit: ['Daily inspiration feeds', 'Product reference libraries', 'Screenshot curation sites', 'App and website directories', 'Icon inspiration archives'],
            includedSections: ['navigation', 'hero', 'feed-hero', 'category-tabs', 'curation-feed', 'best-of-views', 'app-website-icons', 'source-metadata', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'First Light',
                    description: 'First Light visual preset for calm white feeds with system sans typography, hairline borders, tiny labels, loose masonry columns, screenshot cards, maker/source/rating/platform metadata, live update indicators, category tabs, search, newsletter signup, best-of lists, latest, apps, websites, and icons.',
                    previewImage: '/vendor/capell/themes/first-light.jpg',
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
                    previewImage: '/vendor/capell/themes/first-light.jpg',
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
            assets: ['css' => 'vendor/capell/themes/first-light.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-first-light');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-first-light');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-first-light::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-first-light.css',
            packageName: self::$packageName,
            condition: 'theme-css:first-light',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-first-light::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-first-light::sections.hero', failLoudly: true),
            'feed-hero' => new ViewSectionRenderer(self::THEME_KEY, 'feed-hero', 'capell-theme-first-light::sections.feed-hero', failLoudly: true),
            'category-tabs' => new ViewSectionRenderer(self::THEME_KEY, 'category-tabs', 'capell-theme-first-light::sections.category-tabs', failLoudly: true),
            'curation-feed' => new ViewSectionRenderer(self::THEME_KEY, 'curation-feed', 'capell-theme-first-light::sections.curation-feed', failLoudly: true),
            'best-of-views' => new ViewSectionRenderer(self::THEME_KEY, 'best-of-views', 'capell-theme-first-light::sections.best-of-views', failLoudly: true),
            'app-website-icons' => new ViewSectionRenderer(self::THEME_KEY, 'app-website-icons', 'capell-theme-first-light::sections.app-website-icons', failLoudly: true),
            'source-metadata' => new ViewSectionRenderer(self::THEME_KEY, 'source-metadata', 'capell-theme-first-light::sections.source-metadata', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-first-light::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-first-light::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-first-light::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-first-light::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-first-light::sections.footer', failLoudly: true),
        ];
    }
}
