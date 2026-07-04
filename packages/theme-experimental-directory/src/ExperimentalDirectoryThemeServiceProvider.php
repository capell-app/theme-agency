<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ExperimentalDirectory;

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
use Capell\ThemeStudio\ExperimentalDirectory\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class ExperimentalDirectoryThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'experimental-directory';

    public static string $packageName = 'capell-app/theme-experimental-directory';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Experimental Directory',
            description: 'Experimental Directory theme for creative industry directories with a pale neutral canvas, confident black text, tight grid rules, oversized project titles, compact metadata, status badges, large image-led feature blocks, featured-today hero, latest submissions, winners, collections, profiles, resources, and sponsor modules.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/experimental-directory.jpg',
            tags: ['Experimental Directory', 'Creative Awards', 'Portfolio', 'Collections', 'Submissions'],
            bestFit: ['Creative industry directories', 'Agency and studio showcases', 'Portfolio-heavy CMS sites', 'Architecture and artist indexes', 'Award-style submission galleries'],
            includedSections: ['navigation', 'hero', 'featured-today', 'metadata-filters', 'latest-submissions', 'winners-collections', 'profiles-resources', 'sponsor-modules', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Experimental Directory',
                    description: 'Experimental Directory visual preset for pale neutral creative directories with tight grid rules, oversized project titles, compact uppercase metadata, status badges, large image feature blocks, latest submissions, winners, collections, profiles, resources, sponsor modules, and tasteful hover reveals.',
                    previewImage: '/vendor/capell/themes/experimental-directory.jpg',
                    values: [
                        'primaryColor' => '#050505',
                        'accentColor' => '#d44a1f',
                        'neutralColor' => '#55524d',
                        'surfaceColor' => '#eee9df',
                        'foregroundColor' => '#050505',
                        'headingFont' => 'condensed',
                        'bodyFont' => 'system',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'sharp',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'feature-slab',
                        'radius' => 'none',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'dense',
                    ],
                ),
                new ThemePresetData(
                    key: 'jury-panel',
                    name: 'Jury Panel',
                    description: 'Jury Panel visual preset for a deep-ink shortlist review room with a plum-teal accent, chalk-white surface, tighter grid rules, oversized project titles, compact uppercase metadata, status badges, large image feature blocks, latest submissions, winners, collections, profiles, resources, sponsor modules, and brisk hover reveals.',
                    previewImage: '/vendor/capell/themes/experimental-directory.jpg',
                    values: [
                        'primaryColor' => '#161821',
                        'accentColor' => '#5b3b6b',
                        'neutralColor' => '#6f6a72',
                        'surfaceColor' => '#f6f4f0',
                        'foregroundColor' => '#161821',
                        'headingFont' => 'condensed',
                        'bodyFont' => 'system',
                        'spacing' => 'tight',
                        'alignment' => 'left',
                        'cardStyle' => 'sharp',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'energetic',
                        'mediaTreatment' => 'feature-slab',
                        'radius' => 'none',
                        'headingScale' => 'oversized',
                        'cardDensity' => 'dense',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/experimental-directory.css'],
            runtime: FrontendRuntime::Blade,
            extends: 'default',
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-experimental-directory');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-experimental-directory');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-experimental-directory::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-experimental-directory.css',
            packageName: self::$packageName,
            condition: 'theme-css:experimental-directory',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-experimental-directory::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-experimental-directory::sections.hero', failLoudly: true),
            'featured-today' => new ViewSectionRenderer(self::THEME_KEY, 'featured-today', 'capell-theme-experimental-directory::sections.featured-today', failLoudly: true),
            'metadata-filters' => new ViewSectionRenderer(self::THEME_KEY, 'metadata-filters', 'capell-theme-experimental-directory::sections.metadata-filters', failLoudly: true),
            'latest-submissions' => new ViewSectionRenderer(self::THEME_KEY, 'latest-submissions', 'capell-theme-experimental-directory::sections.latest-submissions', failLoudly: true),
            'winners-collections' => new ViewSectionRenderer(self::THEME_KEY, 'winners-collections', 'capell-theme-experimental-directory::sections.winners-collections', failLoudly: true),
            'profiles-resources' => new ViewSectionRenderer(self::THEME_KEY, 'profiles-resources', 'capell-theme-experimental-directory::sections.profiles-resources', failLoudly: true),
            'sponsor-modules' => new ViewSectionRenderer(self::THEME_KEY, 'sponsor-modules', 'capell-theme-experimental-directory::sections.sponsor-modules', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-experimental-directory::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-experimental-directory::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-experimental-directory::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-experimental-directory::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-experimental-directory::sections.footer', failLoudly: true),
        ];
    }
}
