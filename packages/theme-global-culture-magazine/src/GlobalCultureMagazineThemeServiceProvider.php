<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\GlobalCultureMagazine;

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
use Capell\ThemeStudio\GlobalCultureMagazine\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class GlobalCultureMagazineThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'global-culture-magazine';

    public static string $packageName = 'capell-app/theme-global-culture-magazine';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Global Culture Magazine',
            description: 'Premium global culture magazine theme for lead dispatches, radio and audio, city guides, travel, culture, books, shop modules, columnists, and newsletter conversion.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/global-culture-magazine.jpg',
            tags: ['Magazine', 'Global Affairs', 'Travel', 'Culture', 'Audio'],
            bestFit: ['Global magazines', 'Culture publishers', 'Travel editorial teams', 'City guide brands', 'Premium media shops'],
            includedSections: ['navigation', 'hero', 'lead-dispatch', 'radio-audio', 'city-guides', 'travel-culture', 'shop-books', 'columnists', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Global Culture Magazine',
                    description: 'Warm paper-and-ink editorial preset with serif display headlines, gilded plate artwork, lead dispatches, compact audio rows, city guides, travel and culture sections, books, shop cards, columnists, and newsletter signup.',
                    previewImage: '/vendor/capell/themes/global-culture-magazine.jpg',
                    values: [
                        'primaryColor' => '#241d17',
                        'accentColor' => '#b88a2e',
                        'neutralColor' => '#241d17',
                        'surfaceColor' => '#f6f1e7',
                        'foregroundColor' => '#241d17',
                        'headingFont' => 'playfair',
                        'bodyFont' => 'newsreader',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
                new ThemePresetData(
                    key: 'nightflight',
                    name: 'Nightflight',
                    description: 'Deep indigo night-travel preset with brass-gold accents against ink-blue surfaces, built for red-eye dispatches, late-night radio, and after-dark city guides.',
                    previewImage: '/vendor/capell/themes/global-culture-magazine.jpg',
                    values: [
                        'primaryColor' => '#e8e2d4',
                        'accentColor' => '#c79a3d',
                        'neutralColor' => '#9aa3b5',
                        'surfaceColor' => '#161b2c',
                        'foregroundColor' => '#e8e2d4',
                        'headingFont' => 'playfair',
                        'bodyFont' => 'newsreader',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'expressive',
                        'mediaTreatment' => 'framed',
                        'radius' => 'sm',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'dense',
                    ],
                ),
            ],
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
            assets: ['css' => 'vendor/capell/themes/global-culture-magazine.css'],
            runtime: FrontendRuntime::Blade,
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-global-culture-magazine');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-global-culture-magazine');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-global-culture-magazine::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-global-culture-magazine.css',
            packageName: self::$packageName,
            condition: 'theme-css:global-culture-magazine',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-global-culture-magazine::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-global-culture-magazine::sections.hero', failLoudly: true),
            'lead-dispatch' => new ViewSectionRenderer(self::THEME_KEY, 'lead-dispatch', 'capell-theme-global-culture-magazine::sections.lead-dispatch', failLoudly: true),
            'radio-audio' => new ViewSectionRenderer(self::THEME_KEY, 'radio-audio', 'capell-theme-global-culture-magazine::sections.radio-audio', failLoudly: true),
            'city-guides' => new ViewSectionRenderer(self::THEME_KEY, 'city-guides', 'capell-theme-global-culture-magazine::sections.city-guides', failLoudly: true),
            'travel-culture' => new ViewSectionRenderer(self::THEME_KEY, 'travel-culture', 'capell-theme-global-culture-magazine::sections.travel-culture', failLoudly: true),
            'shop-books' => new ViewSectionRenderer(self::THEME_KEY, 'shop-books', 'capell-theme-global-culture-magazine::sections.shop-books', failLoudly: true),
            'columnists' => new ViewSectionRenderer(self::THEME_KEY, 'columnists', 'capell-theme-global-culture-magazine::sections.columnists', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-global-culture-magazine::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-global-culture-magazine::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-global-culture-magazine::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-global-culture-magazine::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-global-culture-magazine::sections.footer', failLoudly: true),
        ];
    }
}
