<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DeepBench;

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
use Capell\ThemeStudio\DeepBench\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class DeepBenchThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'deep-bench';

    public static string $packageName = 'capell-app/theme-deep-bench';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Deep Bench',
            description: 'A who\'s-who of designers and developers: browsable portfolio listings plus career resources. The roster people check before they hire.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/deep-bench.jpg',
            tags: ['Portfolio', 'Directory', 'Designers', 'Developers', 'Resources'],
            bestFit: ['Portfolio directories', 'Designer galleries', 'Developer portfolios', 'Studio showcases', 'Career resource hubs'],
            includedSections: ['navigation', 'hero', 'directory-hero', 'role-filters', 'portfolio-grid', 'resume-resources', 'curated-lists', 'profile-detail', 'proof', 'content-listing', 'newsletter', 'cta', 'footer', 'directory-hero-roster', 'role-filters-toolbar', 'portfolio-grid-cards', 'resume-resources-sidebar', 'curated-lists-cta'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Deep Bench',
                    description: 'Deep Bench visual preset for clean white galleries, energetic accent color, large preview tiles, simple role and medium filters, featured portfolios, resume resources, articles, curated lists, gallery details, social links, and related portfolios.',
                    previewImage: '/vendor/capell/themes/deep-bench.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#ff6b35',
                        'neutralColor' => '#141414',
                        'surfaceColor' => '#ffffff',
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
                    key: 'studio-noir',
                    name: 'Studio Noir',
                    description: 'Studio Noir visual preset for a professional dark gallery with deep-teal accents, compact filters, dense preview tiles, and a confident, understated career-resources section.',
                    previewImage: '/vendor/capell/themes/deep-bench.jpg',
                    values: [
                        'primaryColor' => '#e6f2f0',
                        'accentColor' => '#0f766e',
                        'neutralColor' => '#0b1615',
                        'surfaceColor' => '#0f1e1c',
                        'foregroundColor' => '#e6f2f0',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'none',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'sm',
                        'headingScale' => 'compact',
                        'cardDensity' => 'dense',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/deep-bench.css'],
            runtime: FrontendRuntime::Blade,
            frontend: [
                'sectionVariants' => [
                    'directory-hero-roster' => ['default', 'compact'],
                    'role-filters-toolbar' => ['default', 'sticky'],
                    'portfolio-grid-cards' => ['default', 'rows'],
                    'resume-resources-sidebar' => ['default', 'stacked'],
                    'curated-lists-cta' => ['default', 'band'],
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

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-deep-bench');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-deep-bench');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-deep-bench::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-deep-bench.css',
            packageName: self::$packageName,
            condition: 'theme-css:deep-bench',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-deep-bench::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-deep-bench::sections.hero', failLoudly: true),
            'directory-hero' => new ViewSectionRenderer(self::THEME_KEY, 'directory-hero', 'capell-theme-deep-bench::sections.directory-hero', failLoudly: true),
            'role-filters' => new ViewSectionRenderer(self::THEME_KEY, 'role-filters', 'capell-theme-deep-bench::sections.role-filters', failLoudly: true),
            'portfolio-grid' => new ViewSectionRenderer(self::THEME_KEY, 'portfolio-grid', 'capell-theme-deep-bench::sections.portfolio-grid', failLoudly: true),
            'resume-resources' => new ViewSectionRenderer(self::THEME_KEY, 'resume-resources', 'capell-theme-deep-bench::sections.resume-resources', failLoudly: true),
            'curated-lists' => new ViewSectionRenderer(self::THEME_KEY, 'curated-lists', 'capell-theme-deep-bench::sections.curated-lists', failLoudly: true),
            'profile-detail' => new ViewSectionRenderer(self::THEME_KEY, 'profile-detail', 'capell-theme-deep-bench::sections.profile-detail', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-deep-bench::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-deep-bench::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-deep-bench::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-deep-bench::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-deep-bench::sections.footer', failLoudly: true),
            'directory-hero-roster' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'directory-hero-roster',
                baseView: 'capell-theme-deep-bench::sections.directory-hero-roster',
                variantViews: ['compact' => 'capell-theme-deep-bench::sections.directory-hero-roster--compact'],
                failLoudly: true,
            ),
            'role-filters-toolbar' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'role-filters-toolbar',
                baseView: 'capell-theme-deep-bench::sections.role-filters-toolbar',
                variantViews: ['sticky' => 'capell-theme-deep-bench::sections.role-filters-toolbar--sticky'],
                failLoudly: true,
            ),
            'portfolio-grid-cards' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'portfolio-grid-cards',
                baseView: 'capell-theme-deep-bench::sections.portfolio-grid-cards',
                variantViews: ['rows' => 'capell-theme-deep-bench::sections.portfolio-grid-cards--rows'],
                failLoudly: true,
            ),
            'resume-resources-sidebar' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'resume-resources-sidebar',
                baseView: 'capell-theme-deep-bench::sections.resume-resources-sidebar',
                variantViews: ['stacked' => 'capell-theme-deep-bench::sections.resume-resources-sidebar--stacked'],
                failLoudly: true,
            ),
            'curated-lists-cta' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'curated-lists-cta',
                baseView: 'capell-theme-deep-bench::sections.curated-lists-cta',
                variantViews: ['band' => 'capell-theme-deep-bench::sections.curated-lists-cta--band'],
                failLoudly: true,
            ),
        ];
    }
}
