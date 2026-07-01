<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\BoldSportCommerce;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\BoldSportCommerce\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class BoldSportCommerceThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'bold-sport-commerce';

    public static string $packageName = 'capell-app/theme-bold-sport-commerce';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Bold Sport Commerce',
            description: 'High-energy sport commerce campaign theme for seasonal drops, fast category jumps, bold product cards, training stories, and community offers.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/bold-sport-commerce.jpg',
            tags: ['Sport', 'Commerce', 'Campaigns', 'Drops', 'Training'],
            bestFit: ['Sport retailers', 'Athletic apparel brands', 'Team stores', 'Event merch', 'Training communities'],
            includedSections: ['navigation', 'hero', 'campaign-launch', 'category-jumps', 'features', 'community-offer', 'training-stories', 'fit-specs', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Bold Sport Commerce',
                    description: 'Bold Sport Commerce visual preset for high-contrast campaigns, fast mobile categories, product colorways, sale states, badges, and athlete story modules.',
                    previewImage: '/vendor/capell/themes/bold-sport-commerce.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#e23824',
                        'neutralColor' => '#111111',
                        'surfaceColor' => '#f7f5ef',
                        'foregroundColor' => '#111111',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'campaign',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/bold-sport-commerce.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-bold-sport-commerce');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-bold-sport-commerce');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-bold-sport-commerce::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-bold-sport-commerce.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-bold-sport-commerce::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-bold-sport-commerce::sections.hero', failLoudly: true),
            'campaign-launch' => new ViewSectionRenderer(self::THEME_KEY, 'campaign-launch', 'capell-theme-bold-sport-commerce::sections.campaign-launch', failLoudly: true),
            'category-jumps' => new ViewSectionRenderer(self::THEME_KEY, 'category-jumps', 'capell-theme-bold-sport-commerce::sections.category-jumps', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-bold-sport-commerce::sections.features', failLoudly: true),
            'community-offer' => new ViewSectionRenderer(self::THEME_KEY, 'community-offer', 'capell-theme-bold-sport-commerce::sections.community-offer', failLoudly: true),
            'training-stories' => new ViewSectionRenderer(self::THEME_KEY, 'training-stories', 'capell-theme-bold-sport-commerce::sections.training-stories', failLoudly: true),
            'fit-specs' => new ViewSectionRenderer(self::THEME_KEY, 'fit-specs', 'capell-theme-bold-sport-commerce::sections.fit-specs', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-bold-sport-commerce::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-bold-sport-commerce::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-bold-sport-commerce::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-bold-sport-commerce::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-bold-sport-commerce::sections.footer', failLoudly: true),
        ];
    }
}
