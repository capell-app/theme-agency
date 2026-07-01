<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PremiumPortfolioCollection;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\PremiumPortfolioCollection\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class PremiumPortfolioCollectionThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'premium-portfolio-collection';

    public static string $packageName = 'capell-app/theme-premium-portfolio-collection';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Premium Portfolio Collection',
            description: 'Premium portfolio collection theme for featured portfolios, newest entries, award labels, creator directories, taxonomy filters, gallery details, and educational upsells.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/premium-portfolio-collection.jpg',
            tags: ['Portfolio', 'Directory', 'Awards', 'Creators', 'Gallery'],
            bestFit: ['Portfolio directories', 'Creative award sites', 'Freelancer showcases', 'Studio indexes', 'Design education hubs'],
            includedSections: ['navigation', 'hero', 'featured-portfolios', 'filter-taxonomies', 'portfolio-grid', 'awarded-profiles', 'creator-directory', 'education-upsell', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Premium Portfolio Collection',
                    description: 'Premium Portfolio Collection visual preset for awards-style grids, large preview cards, filters, status labels, creator metadata, newest entries, awarded profiles, and education modules.',
                    previewImage: '/vendor/capell/themes/premium-portfolio-collection.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#1f6feb',
                        'neutralColor' => '#121212',
                        'surfaceColor' => '#f7f7f2',
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
            assets: ['css' => 'vendor/capell/themes/premium-portfolio-collection.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-premium-portfolio-collection');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-premium-portfolio-collection');

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-premium-portfolio-collection::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-premium-portfolio-collection.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-premium-portfolio-collection::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-premium-portfolio-collection::sections.hero', failLoudly: true),
            'featured-portfolios' => new ViewSectionRenderer(self::THEME_KEY, 'featured-portfolios', 'capell-theme-premium-portfolio-collection::sections.featured-portfolios', failLoudly: true),
            'filter-taxonomies' => new ViewSectionRenderer(self::THEME_KEY, 'filter-taxonomies', 'capell-theme-premium-portfolio-collection::sections.filter-taxonomies', failLoudly: true),
            'portfolio-grid' => new ViewSectionRenderer(self::THEME_KEY, 'portfolio-grid', 'capell-theme-premium-portfolio-collection::sections.portfolio-grid', failLoudly: true),
            'awarded-profiles' => new ViewSectionRenderer(self::THEME_KEY, 'awarded-profiles', 'capell-theme-premium-portfolio-collection::sections.awarded-profiles', failLoudly: true),
            'creator-directory' => new ViewSectionRenderer(self::THEME_KEY, 'creator-directory', 'capell-theme-premium-portfolio-collection::sections.creator-directory', failLoudly: true),
            'education-upsell' => new ViewSectionRenderer(self::THEME_KEY, 'education-upsell', 'capell-theme-premium-portfolio-collection::sections.education-upsell', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-premium-portfolio-collection::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-premium-portfolio-collection::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-premium-portfolio-collection::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-premium-portfolio-collection::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-premium-portfolio-collection::sections.footer', failLoudly: true),
        ];
    }
}
