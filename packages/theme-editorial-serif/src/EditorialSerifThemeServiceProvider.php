<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EditorialSerif;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\EditorialSerif\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class EditorialSerifThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'editorial-serif';

    public static string $packageName = 'capell-app/theme-editorial-serif';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Editorial Serif',
            description: 'Editorial Serif gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/editorial-serif.jpg',
            tags: ['Editorial', 'Serif', 'Typography', 'Style', 'Print'],
            bestFit: ['Publications & journals', 'Writers & essayists', 'Design studios', 'Editorial brands', 'Any site wanting a print-grade serif voice'],
            includedSections: ['navigation', 'hero', 'essay-index', 'issue-archive', 'author-profiles', 'subscription-panel', 'editorial-statement', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Editorial Serif',
                    description: 'Editorial Serif visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/editorial-serif.jpg',
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
            ],
            assets: ['css' => 'vendor/capell/themes/editorial-serif.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-editorial-serif');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-editorial-serif');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-editorial-serif::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-editorial-serif.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-foundation::theme.chrome.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-editorial-serif::sections.hero', failLoudly: true),
            'essay-index' => new ViewSectionRenderer(self::THEME_KEY, 'essay-index', 'capell-theme-editorial-serif::sections.essay-index', failLoudly: true),
            'issue-archive' => new ViewSectionRenderer(self::THEME_KEY, 'issue-archive', 'capell-theme-editorial-serif::sections.issue-archive', failLoudly: true),
            'author-profiles' => new ViewSectionRenderer(self::THEME_KEY, 'author-profiles', 'capell-theme-editorial-serif::sections.author-profiles', failLoudly: true),
            'subscription-panel' => new ViewSectionRenderer(self::THEME_KEY, 'subscription-panel', 'capell-theme-editorial-serif::sections.subscription-panel', failLoudly: true),
            'editorial-statement' => new ViewSectionRenderer(self::THEME_KEY, 'editorial-statement', 'capell-theme-editorial-serif::sections.editorial-statement', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-editorial-serif::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-editorial-serif::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-editorial-serif::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-editorial-serif::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-foundation::theme.chrome.footer', failLoudly: true),
        ];
    }
}
