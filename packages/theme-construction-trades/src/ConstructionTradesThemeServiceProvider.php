<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ConstructionTrades;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\ConstructionTrades\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class ConstructionTradesThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'construction-trades';

    public static string $packageName = 'capell-app/theme-construction-trades';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Construction Trades',
            description: 'Construction Trades gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/construction-trades.jpg',
            tags: ['Construction', 'Trades', 'Rugged', 'Quote-led', 'Slate & amber'],
            bestFit: ['Builders & contractors', 'Construction firms', 'Extensions & renovations', 'Commercial fit-out'],
            includedSections: ['navigation', 'hero', 'project-portfolio', 'services', 'accreditations', 'process', 'service-areas', 'quote-cta', 'content-listing', 'footer', 'features', 'proof', 'cta'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Construction Trades',
                    description: 'Construction Trades visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/construction-trades.jpg',
                    values: [
                        'primaryColor' => '#334155',
                        'accentColor' => '#f59e0b',
                        'neutralColor' => '#1c1917',
                        'surfaceColor' => '#f4f4f5',
                        'foregroundColor' => '#1c1917',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/construction-trades.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-construction-trades');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-construction-trades');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-construction-trades::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-construction-trades.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-foundation-theme::theme.chrome.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-construction-trades::sections.hero', failLoudly: true),
            'project-portfolio' => new ViewSectionRenderer(self::THEME_KEY, 'project-portfolio', 'capell-theme-construction-trades::sections.project-portfolio', failLoudly: true),
            'services' => new ViewSectionRenderer(self::THEME_KEY, 'services', 'capell-theme-construction-trades::sections.services', failLoudly: true),
            'accreditations' => new ViewSectionRenderer(self::THEME_KEY, 'accreditations', 'capell-theme-construction-trades::sections.accreditations', failLoudly: true),
            'process' => new ViewSectionRenderer(self::THEME_KEY, 'process', 'capell-theme-construction-trades::sections.process', failLoudly: true),
            'service-areas' => new ViewSectionRenderer(self::THEME_KEY, 'service-areas', 'capell-theme-construction-trades::sections.service-areas', failLoudly: true),
            'quote-cta' => new ViewSectionRenderer(self::THEME_KEY, 'quote-cta', 'capell-theme-construction-trades::sections.quote-cta', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-construction-trades::sections.content-listing', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-foundation-theme::theme.chrome.footer', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-construction-trades::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-construction-trades::sections.proof', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-construction-trades::sections.cta', failLoudly: true),
        ];
    }
}
