<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FarField;

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
use Capell\ThemeStudio\FarField\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class FarFieldThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'far-field';

    public static string $packageName = 'capell-app/theme-far-field';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Far Field',
            description: 'Dispatches, city guides, columnists, and radio rows for magazines with passports. Global affairs and culture with audio built in.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/far-field.jpg',
            tags: ['Magazine', 'Global Affairs', 'Travel', 'Culture', 'Audio'],
            bestFit: ['Global magazines', 'Culture publishers', 'Travel editorial teams', 'City guide brands', 'Premium media shops'],
            includedSections: ['navigation', 'hero', 'lead-dispatch', 'radio-audio', 'city-guides', 'travel-culture', 'shop-books', 'columnists', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Far Field',
                    description: 'Warm paper-and-ink editorial preset with serif display headlines, gilded plate artwork, lead dispatches, compact audio rows, city guides, travel and culture sections, books, shop cards, columnists, and newsletter signup.',
                    previewImage: '/vendor/capell/themes/far-field.jpg',
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
                    previewImage: '/vendor/capell/themes/far-field.jpg',
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
            assets: ['css' => 'vendor/capell/themes/far-field.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-far-field');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-far-field');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-far-field::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-far-field.css',
            packageName: self::$packageName,
            condition: 'theme-css:far-field',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-far-field::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-far-field::sections.hero', failLoudly: true),
            'lead-dispatch' => new ViewSectionRenderer(self::THEME_KEY, 'lead-dispatch', 'capell-theme-far-field::sections.lead-dispatch', failLoudly: true),
            'radio-audio' => new ViewSectionRenderer(self::THEME_KEY, 'radio-audio', 'capell-theme-far-field::sections.radio-audio', failLoudly: true),
            'city-guides' => new ViewSectionRenderer(self::THEME_KEY, 'city-guides', 'capell-theme-far-field::sections.city-guides', failLoudly: true),
            'travel-culture' => new ViewSectionRenderer(self::THEME_KEY, 'travel-culture', 'capell-theme-far-field::sections.travel-culture', failLoudly: true),
            'shop-books' => new ViewSectionRenderer(self::THEME_KEY, 'shop-books', 'capell-theme-far-field::sections.shop-books', failLoudly: true),
            'columnists' => new ViewSectionRenderer(self::THEME_KEY, 'columnists', 'capell-theme-far-field::sections.columnists', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-far-field::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-far-field::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-far-field::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-far-field::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-far-field::sections.footer', failLoudly: true),
        ];
    }
}
