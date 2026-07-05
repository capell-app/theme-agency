<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FrontRow;

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
use Capell\ThemeStudio\FrontRow\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class FrontRowThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'front-row';

    public static string $packageName = 'capell-app/theme-front-row';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Front Row',
            description: 'Awarded portfolios and design-education picks with a best-seat-in-the-house presentation. Includes the playful curated "Hand Picked" preset for lighter, taste-led indexes.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/front-row.jpg',
            tags: ['Portfolio', 'Directory', 'Awards', 'Creators', 'Gallery'],
            bestFit: ['Portfolio directories', 'Creative award sites', 'Freelancer showcases', 'Studio indexes', 'Design education hubs'],
            includedSections: ['navigation', 'hero', 'featured-portfolios', 'filter-taxonomies', 'portfolio-grid', 'awarded-profiles', 'creator-directory', 'education-upsell', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Front Row',
                    description: 'Front Row visual preset for awards-style grids, large preview cards, filters, status labels, creator metadata, newest entries, awarded profiles, and education modules.',
                    previewImage: '/vendor/capell/themes/front-row.jpg',
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
                new ThemePresetData(
                    key: 'gilded-archive',
                    name: 'Gilded Archive',
                    description: 'A charcoal-and-gold counterpart evoking a private awards archive, with denser bordered cards and a slower, more ceremonial motion feel.',
                    previewImage: '/vendor/capell/themes/front-row.jpg',
                    values: [
                        'primaryColor' => '#f5f0e6',
                        'accentColor' => '#c9a24b',
                        'neutralColor' => '#1b1815',
                        'surfaceColor' => '#161311',
                        'foregroundColor' => '#f5f0e6',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'generous',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'minimal',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'sm',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'compact',
                    ],
                ),
                new ThemePresetData(
                    key: 'hand-picked',
                    name: 'Hand Picked',
                    description: 'A playful, curated counterpart with a violet accent on a soft lilac surface — for lighter, taste-led indexes.',
                    previewImage: '/vendor/capell/themes/front-row.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#7b4ee6',
                        'neutralColor' => '#21182b',
                        'surfaceColor' => '#f7f2ff',
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
            assets: ['css' => 'vendor/capell/themes/front-row.css'],
            runtime: FrontendRuntime::Blade,
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-front-row');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-front-row');

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-front-row::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-front-row.css',
            packageName: self::$packageName,
            condition: 'theme-css:front-row',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-front-row::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-front-row::sections.hero', failLoudly: true),
            'featured-portfolios' => new ViewSectionRenderer(self::THEME_KEY, 'featured-portfolios', 'capell-theme-front-row::sections.featured-portfolios', failLoudly: true),
            'filter-taxonomies' => new ViewSectionRenderer(self::THEME_KEY, 'filter-taxonomies', 'capell-theme-front-row::sections.filter-taxonomies', failLoudly: true),
            'portfolio-grid' => new ViewSectionRenderer(self::THEME_KEY, 'portfolio-grid', 'capell-theme-front-row::sections.portfolio-grid', failLoudly: true),
            'awarded-profiles' => new ViewSectionRenderer(self::THEME_KEY, 'awarded-profiles', 'capell-theme-front-row::sections.awarded-profiles', failLoudly: true),
            'creator-directory' => new ViewSectionRenderer(self::THEME_KEY, 'creator-directory', 'capell-theme-front-row::sections.creator-directory', failLoudly: true),
            'education-upsell' => new ViewSectionRenderer(self::THEME_KEY, 'education-upsell', 'capell-theme-front-row::sections.education-upsell', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-front-row::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-front-row::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-front-row::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-front-row::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-front-row::sections.footer', failLoudly: true),
        ];
    }
}
