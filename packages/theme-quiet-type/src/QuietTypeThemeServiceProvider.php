<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuietType;

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
use Capell\ThemeStudio\QuietType\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class QuietTypeThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'quiet-type';

    public static string $packageName = 'capell-app/theme-quiet-type';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Quiet Type',
            description: 'Print-voice serif typography, generous measure, nothing shouting. For essayists and journals where the words are the design.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/quiet-type.jpg',
            tags: ['Editorial', 'Serif', 'Typography', 'Style', 'Print'],
            bestFit: ['Publications & journals', 'Writers & essayists', 'Design studios', 'Editorial brands', 'Any site wanting a print-grade serif voice'],
            includedSections: ['navigation', 'hero', 'essay-index', 'issue-archive', 'author-profiles', 'subscription-panel', 'editorial-statement', 'serialized-chapters', 'issue-contents', 'quote-context', 'contextual-glossary', 'scroll-position-menu', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Quiet Type',
                    description: 'Quiet Type visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/quiet-type.jpg',
                    values: [
                        'primaryColor' => '#7c2d12',
                        'accentColor' => '#166534',
                        'neutralColor' => '#292524',
                        'surfaceColor' => '#faf8f4',
                        'foregroundColor' => '#1a1a1a',
                        'headingFont' => 'fraunces',
                        'bodyFont' => 'newsreader',
                        'spacing' => 'airy',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'none',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'comfortable',
                    ],
                ),
                new ThemePresetData(
                    key: 'midnight',
                    name: 'Midnight Edition',
                    description: 'A warm, inverted counterpart for late-night reading — cream ink on near-black with a warmed ember accent, keeping the serif, dramatic, airy, flat-card identity intact.',
                    previewImage: '/vendor/capell/themes/quiet-type.jpg',
                    values: [
                        'primaryColor' => '#e6b17a',
                        'accentColor' => '#c2703d',
                        'neutralColor' => '#d9d2c7',
                        'surfaceColor' => '#141110',
                        'foregroundColor' => '#f3ede2',
                        'headingFont' => 'fraunces',
                        'bodyFont' => 'newsreader',
                        'spacing' => 'airy',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'none',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/quiet-type.css'],
            runtime: FrontendRuntime::Blade,
            extends: 'default',
            frontend: [
                'sectionVariants' => [
                    'essay-index' => ['default', 'marginalia'],
                    'author-profiles' => ['default', 'bibliography'],
                    'serialized-chapters' => ['default', 'compact'],
                    'issue-contents' => ['default', 'numbered'],
                    'quote-context' => ['default', 'medallion-left'],
                    'contextual-glossary' => ['default', 'compact'],
                    'scroll-position-menu' => ['default', 'left-rail'],
                ],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-quiet-type');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-quiet-type');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-quiet-type::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-quiet-type.css',
            packageName: self::$packageName,
            condition: 'theme-css:quiet-type',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-foundation::theme.chrome.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-quiet-type::sections.hero', failLoudly: true),
            'essay-index' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'essay-index',
                baseView: 'capell-theme-quiet-type::sections.essay-index',
                variantViews: ['marginalia' => 'capell-theme-quiet-type::sections.essay-index--marginalia'],
                failLoudly: true,
            ),
            'issue-archive' => new ViewSectionRenderer(self::THEME_KEY, 'issue-archive', 'capell-theme-quiet-type::sections.issue-archive', failLoudly: true),
            'author-profiles' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'author-profiles',
                baseView: 'capell-theme-quiet-type::sections.author-profiles',
                variantViews: ['bibliography' => 'capell-theme-quiet-type::sections.author-profiles--bibliography'],
                failLoudly: true,
            ),
            'subscription-panel' => new ViewSectionRenderer(self::THEME_KEY, 'subscription-panel', 'capell-theme-quiet-type::sections.subscription-panel', failLoudly: true),
            'editorial-statement' => new ViewSectionRenderer(self::THEME_KEY, 'editorial-statement', 'capell-theme-quiet-type::sections.editorial-statement', failLoudly: true),
            'serialized-chapters' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'serialized-chapters',
                baseView: 'capell-theme-quiet-type::sections.serialized-chapters',
                variantViews: ['compact' => 'capell-theme-quiet-type::sections.serialized-chapters--compact'],
                failLoudly: true,
            ),
            'issue-contents' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'issue-contents',
                baseView: 'capell-theme-quiet-type::sections.issue-contents',
                variantViews: ['numbered' => 'capell-theme-quiet-type::sections.issue-contents--numbered'],
                failLoudly: true,
            ),
            'quote-context' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'quote-context',
                baseView: 'capell-theme-quiet-type::sections.quote-context',
                variantViews: ['medallion-left' => 'capell-theme-quiet-type::sections.quote-context--medallion-left'],
                failLoudly: true,
            ),
            'contextual-glossary' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'contextual-glossary',
                baseView: 'capell-theme-quiet-type::sections.contextual-glossary',
                variantViews: ['compact' => 'capell-theme-quiet-type::sections.contextual-glossary--compact'],
                failLoudly: true,
            ),
            'scroll-position-menu' => new VariantViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: 'scroll-position-menu',
                baseView: 'capell-theme-quiet-type::sections.scroll-position-menu',
                variantViews: ['left-rail' => 'capell-theme-quiet-type::sections.scroll-position-menu--left-rail'],
                failLoudly: true,
            ),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-quiet-type::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-quiet-type::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-quiet-type::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-quiet-type::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-foundation::theme.chrome.footer', failLoudly: true),
        ];
    }
}
