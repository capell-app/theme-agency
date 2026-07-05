<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LaunchPad;

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
use Capell\ThemeStudio\LaunchPad\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class LaunchPadThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'launch-pad';

    public static string $packageName = 'capell-app/theme-launch-pad';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Launch Pad',
            description: 'A showcase gallery for landing pages and templates — rounded screenshot cards, votes, prices, and pro upsells on warm near-white. Where SaaS goes window-shopping.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/launch-pad.jpg',
            tags: ['Landing Pages', 'Gallery', 'Templates', 'SaaS', 'Marketplace'],
            bestFit: ['Landing page galleries', 'SaaS inspiration libraries', 'Startup showcase sites', 'Template marketplaces', 'Ecommerce inspiration hubs'],
            includedSections: ['navigation', 'hero', 'utility-hero', 'category-navigation', 'website-examples', 'paid-templates', 'partner-blocks', 'gallery-system', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Launch Pad',
                    description: 'Launch Pad visual preset for a warm near-white premium gallery with refined spacing, rounded media cards, soft shadows, search, category shortcuts, pro/template upsell blocks, paid templates, partner panels, curated rows, votes, comments, prices, and saved-state actions.',
                    previewImage: '/vendor/capell/themes/launch-pad.jpg',
                    values: [
                        'primaryColor' => '#171717',
                        'accentColor' => '#e15b2d',
                        'neutralColor' => '#57534e',
                        'surfaceColor' => '#fffaf3',
                        'foregroundColor' => '#171717',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'rounded-screenshot',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
                new ThemePresetData(
                    key: 'voltage',
                    name: 'Voltage',
                    description: 'Voltage visual preset for a high-contrast cool-slate gallery with electric-blue accents, denser cards, and a sharper, faster-feeling motion profile suited to startup and dev-tool template showcases.',
                    previewImage: '/vendor/capell/themes/launch-pad.jpg',
                    values: [
                        'primaryColor' => '#0b1220',
                        'accentColor' => '#2563eb',
                        'neutralColor' => '#475569',
                        'surfaceColor' => '#f1f5f9',
                        'foregroundColor' => '#0b1220',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'energetic',
                        'mediaTreatment' => 'rounded-screenshot',
                        'radius' => 'sm',
                        'headingScale' => 'bold',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
            assets: ['css' => 'vendor/capell/themes/launch-pad.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-launch-pad');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-launch-pad');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-launch-pad::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-launch-pad.css',
            packageName: self::$packageName,
            condition: 'theme-css:launch-pad',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-launch-pad::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-launch-pad::sections.hero', failLoudly: true),
            'utility-hero' => new ViewSectionRenderer(self::THEME_KEY, 'utility-hero', 'capell-theme-launch-pad::sections.utility-hero', failLoudly: true),
            'category-navigation' => new ViewSectionRenderer(self::THEME_KEY, 'category-navigation', 'capell-theme-launch-pad::sections.category-navigation', failLoudly: true),
            'website-examples' => new ViewSectionRenderer(self::THEME_KEY, 'website-examples', 'capell-theme-launch-pad::sections.website-examples', failLoudly: true),
            'paid-templates' => new ViewSectionRenderer(self::THEME_KEY, 'paid-templates', 'capell-theme-launch-pad::sections.paid-templates', failLoudly: true),
            'partner-blocks' => new ViewSectionRenderer(self::THEME_KEY, 'partner-blocks', 'capell-theme-launch-pad::sections.partner-blocks', failLoudly: true),
            'gallery-system' => new ViewSectionRenderer(self::THEME_KEY, 'gallery-system', 'capell-theme-launch-pad::sections.gallery-system', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-launch-pad::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-launch-pad::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-launch-pad::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-launch-pad::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-launch-pad::sections.footer', failLoudly: true),
        ];
    }
}
