<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ArtPaper;

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
use Capell\ThemeStudio\ArtPaper\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class ArtPaperThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'art-paper';

    public static string $packageName = 'capell-app/theme-art-paper';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Art Paper',
            description: 'Photography-led features on a gallery-white page — editor picks, trend lists, and product credits. Two presets: cool photographic gloss, or the warm illustrated "Warm Ink" voice.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/art-paper.jpg',
            tags: ['Magazine', 'Design', 'Architecture', 'Interiors', 'Culture'],
            bestFit: ['Design magazines', 'Architecture publishers', 'Interiors studios', 'Art and culture journals', 'Fashion editorial teams'],
            includedSections: ['navigation', 'hero', 'lead-story', 'vertical-categories', 'editor-picks', 'gallery-feature', 'product-credits', 'trend-list', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Art Paper',
                    description: 'Art Paper visual preset for refined editorial navigation, large photography-led leads, editor picks, vertical category rows, gallery features, product credits, trend lists, and calm newsletter conversion.',
                    previewImage: '/vendor/capell/themes/art-paper.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#9b2f2f',
                        'neutralColor' => '#1d1b18',
                        'surfaceColor' => '#f7f4ee',
                        'foregroundColor' => '#111111',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'photographic',
                        'radius' => 'none',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
                new ThemePresetData(
                    key: 'showroom',
                    name: 'Showroom',
                    description: 'A cooler, gallery-white counterpart with graphite ink and a slate accent for design portfolios that read like a showroom walkthrough rather than a warm print magazine.',
                    previewImage: '/vendor/capell/themes/art-paper.jpg',
                    values: [
                        'primaryColor' => '#1a1a1e',
                        'accentColor' => '#3d5a73',
                        'neutralColor' => '#2c2c30',
                        'surfaceColor' => '#f4f5f7',
                        'foregroundColor' => '#1a1a1e',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'spacious',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'minimal',
                        'mediaTreatment' => 'photographic',
                        'radius' => 'small',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'spacious',
                    ],
                ),
                new ThemePresetData(
                    key: 'warm-ink',
                    name: 'Warm Ink',
                    description: 'A warmer, illustrated counterpart with a vermilion accent on a cream surface — for creative-culture voices that favour bold labels and illustrated media over photographic gloss.',
                    previewImage: '/vendor/capell/themes/art-paper.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#e4512f',
                        'neutralColor' => '#201719',
                        'surfaceColor' => '#fff8ec',
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
            assets: ['css' => 'vendor/capell/themes/art-paper.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-art-paper');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-art-paper');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-art-paper::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-art-paper.css',
            packageName: self::$packageName,
            condition: 'theme-css:art-paper',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-art-paper::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-art-paper::sections.hero', failLoudly: true),
            'lead-story' => new ViewSectionRenderer(self::THEME_KEY, 'lead-story', 'capell-theme-art-paper::sections.lead-story', failLoudly: true),
            'vertical-categories' => new ViewSectionRenderer(self::THEME_KEY, 'vertical-categories', 'capell-theme-art-paper::sections.vertical-categories', failLoudly: true),
            'editor-picks' => new ViewSectionRenderer(self::THEME_KEY, 'editor-picks', 'capell-theme-art-paper::sections.editor-picks', failLoudly: true),
            'gallery-feature' => new ViewSectionRenderer(self::THEME_KEY, 'gallery-feature', 'capell-theme-art-paper::sections.gallery-feature', failLoudly: true),
            'product-credits' => new ViewSectionRenderer(self::THEME_KEY, 'product-credits', 'capell-theme-art-paper::sections.product-credits', failLoudly: true),
            'trend-list' => new ViewSectionRenderer(self::THEME_KEY, 'trend-list', 'capell-theme-art-paper::sections.trend-list', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-art-paper::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-art-paper::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-art-paper::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-art-paper::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-art-paper::sections.footer', failLoudly: true),
        ];
    }
}
