<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\SoftFocus;

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
use Capell\ThemeStudio\SoftFocus\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class SoftFocusThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'soft-focus';

    public static string $packageName = 'capell-app/theme-soft-focus';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Soft Focus',
            description: 'An understated inspiration gallery — light grey pages, modest type, honest image grids. Web design worth a long, quiet look.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/soft-focus.jpg',
            tags: ['Web Gallery', 'Quiet', 'Inspiration', 'Archives', 'Editorial'],
            bestFit: ['Calm web design galleries', 'Style and category archives', 'Website inspiration sites', 'Best-of collections', 'Design blog libraries'],
            includedSections: ['navigation', 'hero', 'browse-panels', 'style-type-categories', 'latest-showcase', 'sponsor-space', 'random-best-of', 'editorial-posts', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Soft Focus',
                    description: 'Soft Focus visual preset for light grey gallery pages with modest sans typography, small serif accents, utilitarian browse panels, understated thumbnail grids, sponsor space, category archives, random picks, best-of lists, and simple editorial posts.',
                    previewImage: '/vendor/capell/themes/soft-focus.jpg',
                    values: [
                        'primaryColor' => '#252525',
                        'accentColor' => '#6f7d6a',
                        'neutralColor' => '#6b6b66',
                        'surfaceColor' => '#f3f3f0',
                        'foregroundColor' => '#252525',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'understated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'quiet-image-grid',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'balanced',
                    ],
                ),
                new ThemePresetData(
                    key: 'hushed-clay',
                    name: 'Hushed Clay',
                    description: 'A softer counterpart preset that trades sage-on-stone for a muted dusty-blue and clay palette, keeping the same calm, understated gallery mood at a slightly airier density.',
                    previewImage: '/vendor/capell/themes/soft-focus.jpg',
                    values: [
                        'primaryColor' => '#2f2b28',
                        'accentColor' => '#8c7b6a',
                        'neutralColor' => '#7a7570',
                        'surfaceColor' => '#f2ede6',
                        'foregroundColor' => '#2f2b28',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'relaxed',
                        'alignment' => 'left',
                        'cardStyle' => 'understated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'minimal',
                        'mediaTreatment' => 'quiet-image-grid',
                        'radius' => 'lg',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/soft-focus.css'],
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

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-soft-focus');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-soft-focus');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-soft-focus::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-soft-focus.css',
            packageName: self::$packageName,
            condition: 'theme-css:soft-focus',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-soft-focus::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-soft-focus::sections.hero', failLoudly: true),
            'browse-panels' => new ViewSectionRenderer(self::THEME_KEY, 'browse-panels', 'capell-theme-soft-focus::sections.browse-panels', failLoudly: true),
            'style-type-categories' => new ViewSectionRenderer(self::THEME_KEY, 'style-type-categories', 'capell-theme-soft-focus::sections.style-type-categories', failLoudly: true),
            'latest-showcase' => new ViewSectionRenderer(self::THEME_KEY, 'latest-showcase', 'capell-theme-soft-focus::sections.latest-showcase', failLoudly: true),
            'sponsor-space' => new ViewSectionRenderer(self::THEME_KEY, 'sponsor-space', 'capell-theme-soft-focus::sections.sponsor-space', failLoudly: true),
            'random-best-of' => new ViewSectionRenderer(self::THEME_KEY, 'random-best-of', 'capell-theme-soft-focus::sections.random-best-of', failLoudly: true),
            'editorial-posts' => new ViewSectionRenderer(self::THEME_KEY, 'editorial-posts', 'capell-theme-soft-focus::sections.editorial-posts', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-soft-focus::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-soft-focus::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-soft-focus::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-soft-focus::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-soft-focus::sections.footer', failLoudly: true),
        ];
    }
}
