<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\RawIndex;

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
use Capell\ThemeStudio\RawIndex\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class RawIndexThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'raw-index';

    public static string $packageName = 'capell-app/theme-raw-index';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Raw Index',
            description: 'Raw Index theme for art, music, underground culture, experimental publishing, and independent archives with monospace typography, stark links, plain grey surfaces, controlled irregular image sizing, submission links, interview markers, archive dates, annotations, and accessible roughness.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/raw-index.jpg',
            tags: ['Raw Index', 'Archive', 'Experimental', 'Culture', 'Monospace'],
            bestFit: ['Independent culture archives', 'Experimental publishing sites', 'Art and music indexes', 'Underground zines', 'Submission-led directories'],
            includedSections: ['navigation', 'hero', 'archive-wall', 'rough-links', 'irregular-index', 'submission-markers', 'archive-dates', 'zine-annotations', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Raw Index',
                    description: 'Raw Index visual preset for a brutalist zine archive: monospace type, high-contrast black and white surfaces, one harsh accent, hard borders, visible focus outlines, irregular-but-controlled media, submission markers, interview labels, archive dates, language notes, and zine annotations.',
                    previewImage: '/vendor/capell/themes/raw-index.jpg',
                    values: [
                        'primaryColor' => '#0a0a0a',
                        'accentColor' => '#ff2b00',
                        'neutralColor' => '#4b4b4b',
                        'surfaceColor' => '#f4f3ef',
                        'foregroundColor' => '#0a0a0a',
                        'headingFont' => 'sans',
                        'bodyFont' => 'mono',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'raw',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'irregular-index',
                        'radius' => 'none',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'dense',
                    ],
                ),
                new ThemePresetData(
                    key: 'negative',
                    name: 'Negative',
                    description: 'Negative visual preset for an inverted photocopy-of-a-photocopy zine archive: near-black surfaces, bone-white foreground, a single hyperlink-blue accent, monospace type throughout, zero radius, and tighter, denser spacing for a scanned-in-the-dark reading room mood.',
                    previewImage: '/vendor/capell/themes/raw-index.jpg',
                    values: [
                        'primaryColor' => '#f2f0e8',
                        'accentColor' => '#3355ff',
                        'neutralColor' => '#8a8a86',
                        'surfaceColor' => '#0d0d0c',
                        'foregroundColor' => '#f2f0e8',
                        'headingFont' => 'mono',
                        'bodyFont' => 'mono',
                        'spacing' => 'tight',
                        'alignment' => 'left',
                        'cardStyle' => 'raw',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'none',
                        'mediaTreatment' => 'irregular-index',
                        'radius' => 'none',
                        'headingScale' => 'compact',
                        'cardDensity' => 'dense',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/raw-index.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-raw-index');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-raw-index');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-raw-index::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-raw-index.css',
            packageName: self::$packageName,
            condition: 'theme-css:raw-index',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-raw-index::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-raw-index::sections.hero', failLoudly: true),
            'archive-wall' => new ViewSectionRenderer(self::THEME_KEY, 'archive-wall', 'capell-theme-raw-index::sections.archive-wall', failLoudly: true),
            'rough-links' => new ViewSectionRenderer(self::THEME_KEY, 'rough-links', 'capell-theme-raw-index::sections.rough-links', failLoudly: true),
            'irregular-index' => new ViewSectionRenderer(self::THEME_KEY, 'irregular-index', 'capell-theme-raw-index::sections.irregular-index', failLoudly: true),
            'submission-markers' => new ViewSectionRenderer(self::THEME_KEY, 'submission-markers', 'capell-theme-raw-index::sections.submission-markers', failLoudly: true),
            'archive-dates' => new ViewSectionRenderer(self::THEME_KEY, 'archive-dates', 'capell-theme-raw-index::sections.archive-dates', failLoudly: true),
            'zine-annotations' => new ViewSectionRenderer(self::THEME_KEY, 'zine-annotations', 'capell-theme-raw-index::sections.zine-annotations', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-raw-index::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-raw-index::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-raw-index::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-raw-index::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-raw-index::sections.footer', failLoudly: true),
        ];
    }
}
