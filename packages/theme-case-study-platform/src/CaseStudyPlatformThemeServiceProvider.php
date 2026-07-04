<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CaseStudyPlatform;

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
use Capell\ThemeStudio\CaseStudyPlatform\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class CaseStudyPlatformThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'case-study-platform';

    public static string $packageName = 'capell-app/theme-case-study-platform';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Case Study Platform',
            description: 'Creative case study platform theme for rich project feeds, talent discovery, discipline filters, appreciations, media stacks, credits, tools, process notes, and hiring paths.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/case-study-platform.jpg',
            tags: ['Case Studies', 'Portfolio', 'Creative Talent', 'Project Feed', 'Hiring'],
            bestFit: ['Creative portfolio platforms', 'Case study archives', 'Talent discovery sites', 'Agency project libraries', 'Multidiscipline creator networks'],
            includedSections: ['navigation', 'hero', 'creator-hero', 'discipline-filters', 'project-feed', 'process-notes', 'related-projects', 'credits-tools', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Case Study Platform',
                    description: 'Case Study Platform visual preset for light neutral canvases, wide editorial project grids, bold creator headlines, discipline filters, appreciation metrics, long media detail pages, credits, process notes, and hiring CTAs.',
                    previewImage: '/vendor/capell/themes/case-study-platform.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#1769ff',
                        'neutralColor' => '#111827',
                        'surfaceColor' => '#f4f4f1',
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
                    key: 'midnight-atelier',
                    name: 'Midnight Atelier',
                    description: 'A darker, quieter counterpart for studios who want their case-study feed to feel like a late-night design atelier rather than a bright editorial wall.',
                    previewImage: '/vendor/capell/themes/case-study-platform.jpg',
                    values: [
                        'primaryColor' => '#f5f2ea',
                        'accentColor' => '#e8853e',
                        'neutralColor' => '#d8d2c4',
                        'surfaceColor' => '#14120f',
                        'foregroundColor' => '#f5f2ea',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'none',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/case-study-platform.css'],
            runtime: FrontendRuntime::Blade,
            frontend: [
                'sectionVariants' => [
                    'hero' => ['default', 'split'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-case-study-platform');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-case-study-platform');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-case-study-platform::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-case-study-platform.css',
            packageName: self::$packageName,
            condition: 'theme-css:case-study-platform',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-case-study-platform::sections.navigation', failLoudly: true),
            'hero' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'hero',
                baseView: 'capell-theme-case-study-platform::sections.hero',
                variantViews: ['split' => 'capell-theme-case-study-platform::sections.hero--split'],
                failLoudly: true,
            ),
            'creator-hero' => new ViewSectionRenderer(self::THEME_KEY, 'creator-hero', 'capell-theme-case-study-platform::sections.creator-hero', failLoudly: true),
            'discipline-filters' => new ViewSectionRenderer(self::THEME_KEY, 'discipline-filters', 'capell-theme-case-study-platform::sections.discipline-filters', failLoudly: true),
            'project-feed' => new ViewSectionRenderer(self::THEME_KEY, 'project-feed', 'capell-theme-case-study-platform::sections.project-feed', failLoudly: true),
            'process-notes' => new ViewSectionRenderer(self::THEME_KEY, 'process-notes', 'capell-theme-case-study-platform::sections.process-notes', failLoudly: true),
            'related-projects' => new ViewSectionRenderer(self::THEME_KEY, 'related-projects', 'capell-theme-case-study-platform::sections.related-projects', failLoudly: true),
            'credits-tools' => new ViewSectionRenderer(self::THEME_KEY, 'credits-tools', 'capell-theme-case-study-platform::sections.credits-tools', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-case-study-platform::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-case-study-platform::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-case-study-platform::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-case-study-platform::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-case-study-platform::sections.footer', failLoudly: true),
        ];
    }
}
