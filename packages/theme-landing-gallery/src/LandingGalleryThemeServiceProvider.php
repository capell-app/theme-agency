<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LandingGallery;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\LandingGallery\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class LandingGalleryThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'landing-gallery';

    public static string $packageName = 'capell-app/theme-landing-gallery';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Landing Gallery',
            description: 'Landing Gallery theme for polished SaaS, ecommerce, and startup landing-page galleries with premium spacing, rounded screenshot cards, pro/template upsells, category shortcuts, paid templates, partner blocks, curated rows, votes, comments, pricing, and saved-state actions.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/landing-gallery.jpg',
            tags: ['Landing Pages', 'Gallery', 'Templates', 'SaaS', 'Marketplace'],
            bestFit: ['Landing page galleries', 'SaaS inspiration libraries', 'Startup showcase sites', 'Template marketplaces', 'Ecommerce inspiration hubs'],
            includedSections: ['navigation', 'hero', 'utility-hero', 'category-navigation', 'website-examples', 'paid-templates', 'partner-blocks', 'gallery-system', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Landing Gallery',
                    description: 'Landing Gallery visual preset for a warm near-white premium gallery with refined spacing, rounded media cards, soft shadows, search, category shortcuts, pro/template upsell blocks, paid templates, partner panels, curated rows, votes, comments, prices, and saved-state actions.',
                    previewImage: '/vendor/capell/themes/landing-gallery.jpg',
                    values: [
                        'primaryColor' => '#171717',
                        'accentColor' => '#e15b2d',
                        'neutralColor' => '#57534e',
                        'surfaceColor' => '#fffaf3',
                        'foregroundColor' => '#171717',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'rounded-screenshot',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/landing-gallery.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-landing-gallery');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-landing-gallery');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-landing-gallery::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-landing-gallery.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-landing-gallery::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-landing-gallery::sections.hero', failLoudly: true),
            'utility-hero' => new ViewSectionRenderer(self::THEME_KEY, 'utility-hero', 'capell-theme-landing-gallery::sections.utility-hero', failLoudly: true),
            'category-navigation' => new ViewSectionRenderer(self::THEME_KEY, 'category-navigation', 'capell-theme-landing-gallery::sections.category-navigation', failLoudly: true),
            'website-examples' => new ViewSectionRenderer(self::THEME_KEY, 'website-examples', 'capell-theme-landing-gallery::sections.website-examples', failLoudly: true),
            'paid-templates' => new ViewSectionRenderer(self::THEME_KEY, 'paid-templates', 'capell-theme-landing-gallery::sections.paid-templates', failLoudly: true),
            'partner-blocks' => new ViewSectionRenderer(self::THEME_KEY, 'partner-blocks', 'capell-theme-landing-gallery::sections.partner-blocks', failLoudly: true),
            'gallery-system' => new ViewSectionRenderer(self::THEME_KEY, 'gallery-system', 'capell-theme-landing-gallery::sections.gallery-system', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-landing-gallery::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-landing-gallery::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-landing-gallery::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-landing-gallery::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-landing-gallery::sections.footer', failLoudly: true),
        ];
    }
}
