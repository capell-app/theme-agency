<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OpenStudio;

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
use Capell\ThemeStudio\OpenStudio\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class OpenStudioThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'open-studio';

    public static string $packageName = 'capell-app/theme-open-studio';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Open Studio',
            description: 'Put the work centre-stage: rich case studies with credits, process notes, media stacks, and a hire-me CTA. Built for studios and creators who win clients by showing, not telling.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/open-studio.jpg',
            tags: ['Case Studies', 'Portfolio', 'Creative Talent', 'Project Feed', 'Hiring'],
            bestFit: ['Creative portfolio platforms', 'Case study archives', 'Talent discovery sites', 'Agency project libraries', 'Multidiscipline creator networks'],
            includedSections: ['navigation', 'hero', 'creator-hero', 'discipline-filters', 'project-feed', 'process-notes', 'related-projects', 'credits-tools', 'proof', 'content-listing', 'newsletter', 'cta', 'footer', 'filmstrip-project-showcase', 'discipline-carousel-browse', 'process-notes-timeline', 'credits-grid-roster', 'next-project-cta'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Open Studio',
                    description: 'Open Studio visual preset for light neutral canvases, wide editorial project grids, bold creator headlines, discipline filters, appreciation metrics, long media detail pages, credits, process notes, and hiring CTAs.',
                    previewImage: '/vendor/capell/themes/open-studio.jpg',
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
                    previewImage: '/vendor/capell/themes/open-studio.jpg',
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
            assets: ['css' => 'vendor/capell/themes/open-studio.css'],
            runtime: FrontendRuntime::Blade,
            frontend: [
                'sectionVariants' => [
                    'hero' => ['default', 'split'],
                    'filmstrip-project-showcase' => ['default', 'compact'],
                    'discipline-carousel-browse' => ['default', 'sticky'],
                    'process-notes-timeline' => ['default', 'compact'],
                    'credits-grid-roster' => ['default', 'rows'],
                    'next-project-cta' => ['default', 'stacked'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-open-studio');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-open-studio');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-open-studio::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-open-studio.css',
            packageName: self::$packageName,
            condition: 'theme-css:open-studio',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-open-studio::sections.navigation', failLoudly: true),
            'hero' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'hero',
                baseView: 'capell-theme-open-studio::sections.hero',
                variantViews: ['split' => 'capell-theme-open-studio::sections.hero--split'],
                failLoudly: true,
            ),
            'creator-hero' => new ViewSectionRenderer(self::THEME_KEY, 'creator-hero', 'capell-theme-open-studio::sections.creator-hero', failLoudly: true),
            'discipline-filters' => new ViewSectionRenderer(self::THEME_KEY, 'discipline-filters', 'capell-theme-open-studio::sections.discipline-filters', failLoudly: true),
            'project-feed' => new ViewSectionRenderer(self::THEME_KEY, 'project-feed', 'capell-theme-open-studio::sections.project-feed', failLoudly: true),
            'process-notes' => new ViewSectionRenderer(self::THEME_KEY, 'process-notes', 'capell-theme-open-studio::sections.process-notes', failLoudly: true),
            'related-projects' => new ViewSectionRenderer(self::THEME_KEY, 'related-projects', 'capell-theme-open-studio::sections.related-projects', failLoudly: true),
            'credits-tools' => new ViewSectionRenderer(self::THEME_KEY, 'credits-tools', 'capell-theme-open-studio::sections.credits-tools', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-open-studio::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-open-studio::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-open-studio::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-open-studio::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-open-studio::sections.footer', failLoudly: true),
            'filmstrip-project-showcase' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'filmstrip-project-showcase',
                baseView: 'capell-theme-open-studio::sections.filmstrip-project-showcase',
                variantViews: ['compact' => 'capell-theme-open-studio::sections.filmstrip-project-showcase--compact'],
                failLoudly: true,
            ),
            'discipline-carousel-browse' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'discipline-carousel-browse',
                baseView: 'capell-theme-open-studio::sections.discipline-carousel-browse',
                variantViews: ['sticky' => 'capell-theme-open-studio::sections.discipline-carousel-browse--sticky'],
                failLoudly: true,
            ),
            'process-notes-timeline' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'process-notes-timeline',
                baseView: 'capell-theme-open-studio::sections.process-notes-timeline',
                variantViews: ['compact' => 'capell-theme-open-studio::sections.process-notes-timeline--compact'],
                failLoudly: true,
            ),
            'credits-grid-roster' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'credits-grid-roster',
                baseView: 'capell-theme-open-studio::sections.credits-grid-roster',
                variantViews: ['rows' => 'capell-theme-open-studio::sections.credits-grid-roster--rows'],
                failLoudly: true,
            ),
            'next-project-cta' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'next-project-cta',
                baseView: 'capell-theme-open-studio::sections.next-project-cta',
                variantViews: ['stacked' => 'capell-theme-open-studio::sections.next-project-cta--stacked'],
                failLoudly: true,
            ),
        ];
    }
}
