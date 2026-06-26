<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PremiumProductStory;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\PremiumProductStory\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class PremiumProductStoryThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'premium-product-story';

    public static string $packageName = 'capell-app/theme-premium-product-story';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Premium Product Story',
            description: 'Premium consumer product storytelling theme for expansive launches, product families, feature highlights, gallery strips, ecosystem content, comparisons, and purchase CTAs.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/premium-product-story.jpg',
            tags: ['Product', 'Consumer Brand', 'Storytelling', 'Comparison', 'Launch'],
            bestFit: ['Consumer electronics brands', 'Premium product companies', 'Hardware startups', 'Lifestyle product lines', 'Design-led ecommerce'],
            includedSections: ['navigation', 'hero', 'product-families', 'feature-highlights', 'features', 'ecosystem-story', 'gallery-strip', 'spec-comparison', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Premium Product Story',
                    description: 'Premium Product Story visual preset for spacious product launches, centered hero copy, alternating light and dark bands, gallery strips, feature tiles, spec tables, and purchase CTAs.',
                    previewImage: '/vendor/capell/themes/premium-product-story.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#0071e3',
                        'neutralColor' => '#1d1d1f',
                        'surfaceColor' => '#f5f5f7',
                        'foregroundColor' => '#111111',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'center',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'product-story',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'immersive',
                        'radius' => 'lg',
                        'headingScale' => 'large',
                        'cardDensity' => 'airy',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/premium-product-story.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-premium-product-story');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-premium-product-story');
        $this->loadScreenshotFixtureRoutes();

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-premium-product-story::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-premium-product-story.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_PREMIUM_PRODUCT_STORY_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-premium-product-story::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-premium-product-story::sections.hero', failLoudly: true),
            'product-families' => new ViewSectionRenderer(self::THEME_KEY, 'product-families', 'capell-theme-premium-product-story::sections.product-families', failLoudly: true),
            'feature-highlights' => new ViewSectionRenderer(self::THEME_KEY, 'feature-highlights', 'capell-theme-premium-product-story::sections.feature-highlights', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-premium-product-story::sections.features', failLoudly: true),
            'ecosystem-story' => new ViewSectionRenderer(self::THEME_KEY, 'ecosystem-story', 'capell-theme-premium-product-story::sections.ecosystem-story', failLoudly: true),
            'gallery-strip' => new ViewSectionRenderer(self::THEME_KEY, 'gallery-strip', 'capell-theme-premium-product-story::sections.gallery-strip', failLoudly: true),
            'spec-comparison' => new ViewSectionRenderer(self::THEME_KEY, 'spec-comparison', 'capell-theme-premium-product-story::sections.spec-comparison', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-premium-product-story::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-premium-product-story::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-premium-product-story::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-premium-product-story::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-premium-product-story::sections.footer', failLoudly: true),
        ];
    }
}
