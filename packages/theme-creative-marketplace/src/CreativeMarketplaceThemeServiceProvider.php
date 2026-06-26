<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CreativeMarketplace;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\CreativeMarketplace\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class CreativeMarketplaceThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'creative-marketplace';

    public static string $packageName = 'capell-app/theme-creative-marketplace';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Creative Marketplace',
            description: 'Creative marketplace theme for hiring designers, browsing visual work, service categories, profiles, briefs, shots, agencies, pricing snippets, social signals, availability, and prominent search.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/creative-marketplace.jpg',
            tags: ['Marketplace', 'Creative Talent', 'Designers', 'Hiring', 'Shots'],
            bestFit: ['Creative marketplaces', 'Designer hiring platforms', 'Visual work galleries', 'Agency directories', 'Service marketplaces'],
            includedSections: ['navigation', 'hero', 'service-hero', 'category-chips', 'shot-grid', 'briefs-pricing', 'agencies-services', 'profile-availability', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Creative Marketplace',
                    description: 'Creative Marketplace visual preset for bright friendly interfaces, rounded thumbnails, expressive category chips, profile avatars, service categories, shot grids, briefs, agencies, pricing snippets, saves, likes, views, availability, search, and hiring CTAs.',
                    previewImage: '/vendor/capell/themes/creative-marketplace.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#ea4c89',
                        'neutralColor' => '#1f1722',
                        'surfaceColor' => '#fff7fb',
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
            assets: ['css' => 'vendor/capell/themes/creative-marketplace.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-creative-marketplace');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-creative-marketplace');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-creative-marketplace::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_CREATIVE_MARKETPLACE_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-creative-marketplace.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-creative-marketplace::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-creative-marketplace::sections.hero', failLoudly: true),
            'service-hero' => new ViewSectionRenderer(self::THEME_KEY, 'service-hero', 'capell-theme-creative-marketplace::sections.service-hero', failLoudly: true),
            'category-chips' => new ViewSectionRenderer(self::THEME_KEY, 'category-chips', 'capell-theme-creative-marketplace::sections.category-chips', failLoudly: true),
            'shot-grid' => new ViewSectionRenderer(self::THEME_KEY, 'shot-grid', 'capell-theme-creative-marketplace::sections.shot-grid', failLoudly: true),
            'briefs-pricing' => new ViewSectionRenderer(self::THEME_KEY, 'briefs-pricing', 'capell-theme-creative-marketplace::sections.briefs-pricing', failLoudly: true),
            'agencies-services' => new ViewSectionRenderer(self::THEME_KEY, 'agencies-services', 'capell-theme-creative-marketplace::sections.agencies-services', failLoudly: true),
            'profile-availability' => new ViewSectionRenderer(self::THEME_KEY, 'profile-availability', 'capell-theme-creative-marketplace::sections.profile-availability', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-creative-marketplace::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-creative-marketplace::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-creative-marketplace::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-creative-marketplace::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-creative-marketplace::sections.footer', failLoudly: true),
        ];
    }
}
