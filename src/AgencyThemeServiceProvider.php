<?php

declare(strict_types=1);

namespace Capell\ThemeAgency;

use Capell\Core\Data\RenderableDefinitionData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Support\Renderables\RenderableRegistry;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemeFrontendBuildAssetsData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Support\Editor\StandardThemeEditorSchema;
use Capell\FoundationTheme\Support\Providers\RegistersLayoutNativeThemeDefaults;
use Capell\ThemeAgency\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class AgencyThemeServiceProvider extends ServiceProvider
{
    use RegistersLayoutNativeThemeDefaults;

    public const string THEME_KEY = 'agency';

    public const string CSS_SOURCE = 'resources/css/theme-agency.css';

    public const string CSS_BUILD_INPUT = 'resources/css/capell/themes/agency.css';

    public const string CSS_CONDITION = 'theme-css:agency';

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
            includedSections: ['navigation', 'hero', 'featured-portfolios', 'filter-taxonomies', 'portfolio-grid', 'awarded-profiles', 'creator-directory', 'education-upsell', 'proof', 'content-listing', 'form', 'newsletter', 'cta', 'footer'],
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
            assets: [],
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
                'assets' => new ThemeFrontendBuildAssetsData(
                    cssSource: self::CSS_SOURCE,
                    cssBuildInput: self::CSS_BUILD_INPUT,
                    condition: self::CSS_CONDITION,
                ),
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

        $this->bootLayoutNativeThemeDefaults(
            registry: $registry,
            packageName: self::$packageName,
            translationNamespace: 'capell-theme-agency',
            translationsPath: __DIR__ . '/../resources/lang',
            viewNamespace: 'capell-theme-agency',
            viewsPath: __DIR__ . '/../resources/views',
            cssSource: self::CSS_SOURCE,
            cssCondition: self::CSS_CONDITION,
            definition: self::definition(),
            registerThemeRenderables: function (): void {
                $this->registerThemeRenderables();
            },
        );
    }

    /**
     * Register only Agency-owned component keys. Shared widget keys remain
     * untouched, so booting Agency cannot change another theme's output.
     */
    private function registerThemeRenderables(): void
    {
        $views = [
            'navigation', 'hero', 'featured-portfolios', 'filter-taxonomies',
            'portfolio-grid', 'awarded-profiles', 'creator-directory',
            'education-upsell', 'proof', 'content-listing', 'newsletter', 'cta', 'footer',
        ];

        $registry = resolve(RenderableRegistry::class);

        foreach ($views as $section) {
            $registry->register(new RenderableDefinitionData(
                key: "capell.widget.agency.{$section}",
                type: 'layout-widget',
                blade: 'capell-theme-agency::widget.section',
            ));
        }
    }
}
