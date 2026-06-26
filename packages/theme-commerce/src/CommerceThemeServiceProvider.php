<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Commerce;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Commerce\Console\Commands\DemoCommand;
use Capell\ThemeStudio\Commerce\Rendering\BlogTeaserSectionRenderer;
use Capell\ThemeStudio\Commerce\Rendering\CatalogSectionRenderer;
use Illuminate\Support\ServiceProvider;
use Override;

class CommerceThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'commerce';

    public const string BUILD_ASSET_PATH = 'vendor/capell-theme-commerce';

    public const string BUILD_ASSET_FILE = 'resources/js/theme-commerce.js';

    public static string $packageName = 'capell-app/theme-commerce';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Editorial Commerce',
            description: 'Editorial Commerce image-led retail layouts with catalog discovery, product comparison, proof, and resource-ready conversion content.',
            package: 'capell-app/theme-commerce',
            previewImage: '/vendor/capell/themes/commerce.jpg',
            tags: ['Commerce', 'Catalog', 'Conversion'],
            bestFit: ['Retail catalogs', 'DTC brands', 'Product-led publishers'],
            includedSections: ['navigation', 'hero', 'features', 'content-listing', 'product-finder', 'collections', 'product-grid', 'product-detail', 'mini-basket', 'comparison', 'catalog', 'lookbook', 'promotion', 'campaign', 'search', 'store-event', 'newsletter', 'buying-guide', 'proof', 'blog-teaser', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'commerce',
                    name: 'Editorial Commerce',
                    description: 'Warm editorial retail direction with deep ink, forest green merchandising, and coral action accents.',
                    previewImage: '/vendor/capell/themes/commerce.jpg',
                    values: [
                        'primaryColor' => '#1f5f4a',
                        'accentColor' => '#b94735',
                        'neutralColor' => '#17211c',
                        'surfaceColor' => '#fffaf3',
                        'foregroundColor' => '#17211c',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/commerce.css'],
            runtime: FrontendRuntime::Blade,
            // Capell Frontend registers the built-in "default" runtime inheritance key.
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-commerce');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-commerce');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-commerce::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_COMMERCE_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::buildAsset(
                path: self::BUILD_ASSET_PATH,
                file: self::BUILD_ASSET_FILE,
                packageName: self::$packageName,
            ),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-commerce.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        $blogAvailable = CapellCore::isPackageInstalled('capell-app/blog');
        $campaignStudioAvailable = CapellCore::isPackageInstalled('capell-app/campaign-studio');
        $mediaLibraryAvailable = CapellCore::isPackageInstalled('capell-app/media-library');
        $shopifyAvailable = CapellCore::isPackageInstalled('capell-app/shopify-commerce');

        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-commerce::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-commerce::sections.hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-commerce::sections.product-grid', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-commerce::sections.collections', failLoudly: true),
            'product-finder' => new ViewSectionRenderer(self::THEME_KEY, 'product-finder', 'capell-theme-commerce::sections.product-finder', failLoudly: true),
            'collections' => new ViewSectionRenderer(self::THEME_KEY, 'collections', 'capell-theme-commerce::sections.collections', failLoudly: true),
            'product-grid' => new ViewSectionRenderer(self::THEME_KEY, 'product-grid', 'capell-theme-commerce::sections.product-grid', failLoudly: true),
            'product-detail' => new ViewSectionRenderer(self::THEME_KEY, 'product-detail', 'capell-theme-commerce::sections.product-detail', failLoudly: true),
            'mini-basket' => new ViewSectionRenderer(self::THEME_KEY, 'mini-basket', 'capell-theme-commerce::sections.mini-basket', failLoudly: true),
            'comparison' => new ViewSectionRenderer(self::THEME_KEY, 'comparison', 'capell-theme-commerce::sections.comparison', failLoudly: true),
            'catalog' => new CatalogSectionRenderer(self::THEME_KEY, $shopifyAvailable, failLoudly: true),
            'lookbook' => new ViewSectionRenderer(self::THEME_KEY, 'lookbook', 'capell-theme-commerce::sections.lookbook', true, ['mediaLibraryAvailable' => $mediaLibraryAvailable]),
            'promotion' => new ViewSectionRenderer(self::THEME_KEY, 'promotion', 'capell-theme-commerce::sections.promotion', true, ['campaignStudioAvailable' => $campaignStudioAvailable]),
            'campaign' => new ViewSectionRenderer(self::THEME_KEY, 'campaign', 'capell-theme-commerce::sections.campaign', true, ['campaignStudioAvailable' => $campaignStudioAvailable]),
            'search' => new ViewSectionRenderer(self::THEME_KEY, 'search', 'capell-theme-commerce::sections.search', failLoudly: true),
            'store-event' => new ViewSectionRenderer(self::THEME_KEY, 'store-event', 'capell-theme-commerce::sections.store-event', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-commerce::sections.newsletter', failLoudly: true),
            'buying-guide' => new ViewSectionRenderer(self::THEME_KEY, 'buying-guide', 'capell-theme-commerce::sections.buying-guide', true, ['blogAvailable' => $blogAvailable]),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-commerce::sections.proof', failLoudly: true),
            'blog-teaser' => new BlogTeaserSectionRenderer(self::THEME_KEY, $blogAvailable, failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-commerce::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-commerce::sections.footer', failLoudly: true),
        ];
    }
}
