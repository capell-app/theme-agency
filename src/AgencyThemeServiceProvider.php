<?php

declare(strict_types=1);

namespace Capell\ThemeAgency;

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
use Capell\FoundationTheme\Rendering\VariantViewSectionRenderer;
use Capell\FoundationTheme\Support\Editor\StandardThemeEditorSchema;
use Capell\ThemeAgency\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class AgencyThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'agency';

    public static string $packageName = 'capell-app/theme-agency';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Agency',
            description: 'Awarded portfolios and design-education picks with a best-seat-in-the-house presentation. Includes the playful curated "Hand Picked" preset for lighter, taste-led indexes.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/agency.png',
            tags: ['Portfolio', 'Directory', 'Awards', 'Creators', 'Gallery'],
            bestFit: ['Portfolio directories', 'Creative award sites', 'Freelancer showcases', 'Studio indexes', 'Design education hubs'],
            includedSections: ['navigation', 'hero', 'featured-portfolios', 'filter-taxonomies', 'portfolio-grid', 'awarded-profiles', 'creator-directory', 'education-upsell', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Agency',
                    description: 'Agency visual preset for awards-style grids, large preview cards, filters, status labels, creator metadata, newest entries, awarded profiles, and education modules.',
                    previewImage: '/vendor/capell/themes/agency.png',
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
                        'cardDensity' => 'spacious',
                    ],
                ),
                new ThemePresetData(
                    key: 'gilded-archive',
                    name: 'Gilded Archive',
                    description: 'A charcoal-and-gold counterpart evoking a private awards archive, with denser bordered cards and a slower, more ceremonial motion feel.',
                    previewImage: '/vendor/capell/themes/agency.png',
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
                        'headingScale' => 'expressive',
                        'cardDensity' => 'compact',
                    ],
                ),
                new ThemePresetData(
                    key: 'hand-picked',
                    name: 'Hand Picked',
                    description: 'A playful, curated counterpart with a violet accent on a soft lilac surface — for lighter, taste-led indexes.',
                    previewImage: '/vendor/capell/themes/agency.png',
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
                        'cardDensity' => 'spacious',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/agency.css'],
            runtime: FrontendRuntime::Blade,
            frontend: [
                'sectionVariants' => [
                    'featured-portfolios' => ['default', 'parallax'],
                    'filter-taxonomies' => ['default', 'grid'],
                    'portfolio-grid' => ['default', 'gallery-wall'],
                    'awarded-profiles' => ['default', 'spotlight'],
                    'education-upsell' => ['default', 'cta'],
                ],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-agency');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-agency');

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-agency::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-agency.css',
            packageName: self::$packageName,
            condition: 'theme-css:agency',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-agency::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-agency::sections.hero', failLoudly: true),
            'featured-portfolios' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'featured-portfolios',
                baseView: 'capell-theme-agency::sections.featured-portfolios',
                variantViews: ['parallax' => 'capell-theme-agency::sections.featured-portfolios--parallax'],
                failLoudly: true,
            ),
            'filter-taxonomies' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'filter-taxonomies',
                baseView: 'capell-theme-agency::sections.filter-taxonomies',
                variantViews: ['grid' => 'capell-theme-agency::sections.filter-taxonomies--grid'],
                failLoudly: true,
            ),
            'portfolio-grid' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'portfolio-grid',
                baseView: 'capell-theme-agency::sections.portfolio-grid',
                variantViews: ['gallery-wall' => 'capell-theme-agency::sections.portfolio-grid--gallery-wall'],
                failLoudly: true,
            ),
            'awarded-profiles' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'awarded-profiles',
                baseView: 'capell-theme-agency::sections.awarded-profiles',
                variantViews: ['spotlight' => 'capell-theme-agency::sections.awarded-profiles--spotlight'],
                failLoudly: true,
            ),
            'creator-directory' => new ViewSectionRenderer(self::THEME_KEY, 'creator-directory', 'capell-theme-agency::sections.creator-directory', failLoudly: true),
            'education-upsell' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'education-upsell',
                baseView: 'capell-theme-agency::sections.education-upsell',
                variantViews: ['cta' => 'capell-theme-agency::sections.education-upsell--cta'],
                failLoudly: true,
            ),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-agency::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-agency::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-agency::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-agency::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-agency::sections.footer', failLoudly: true),
        ];
    }
}
