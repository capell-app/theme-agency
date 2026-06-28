<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DevtoolOss;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\DevtoolOss\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class DevtoolOssThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'devtool-oss';

    public static string $packageName = 'capell-app/theme-devtool-oss';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Devtool OSS',
            description: 'Devtool OSS gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/devtool-oss.jpg',
            tags: ['Open Source', 'Developer', 'GitHub', 'Self-host', 'Light + Dark'],
            bestFit: ['Open-source developer tools', 'Self-host + cloud products', 'OSS projects with paid hosting', 'Developer libraries and SDKs'],
            includedSections: ['navigation', 'install-hero', 'github-proof', 'self-host-vs-cloud', 'features', 'sdk-grid', 'changelog', 'contributors', 'proof', 'content-listing', 'cta', 'footer', 'hero'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Devtool OSS',
                    description: 'Devtool OSS visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/devtool-oss.jpg',
                    values: [
                        'primaryColor' => '#4f46e5',
                        'accentColor' => '#06b6d4',
                        'neutralColor' => '#0f172a',
                        'surfaceColor' => '#ffffff',
                        'foregroundColor' => '#0f172a',
                        'headingFont' => 'inter',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/devtool-oss.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-devtool-oss');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-devtool-oss');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-devtool-oss::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-devtool-oss.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-foundation-theme::theme.chrome.navigation', failLoudly: true),
            'install-hero' => new ViewSectionRenderer(self::THEME_KEY, 'install-hero', 'capell-theme-devtool-oss::sections.install-hero', failLoudly: true),
            'github-proof' => new ViewSectionRenderer(self::THEME_KEY, 'github-proof', 'capell-theme-devtool-oss::sections.github-proof', failLoudly: true),
            'self-host-vs-cloud' => new ViewSectionRenderer(self::THEME_KEY, 'self-host-vs-cloud', 'capell-theme-devtool-oss::sections.self-host-vs-cloud', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-devtool-oss::sections.features', failLoudly: true),
            'sdk-grid' => new ViewSectionRenderer(self::THEME_KEY, 'sdk-grid', 'capell-theme-devtool-oss::sections.sdk-grid', failLoudly: true),
            'changelog' => new ViewSectionRenderer(self::THEME_KEY, 'changelog', 'capell-theme-devtool-oss::sections.changelog', failLoudly: true),
            'contributors' => new ViewSectionRenderer(self::THEME_KEY, 'contributors', 'capell-theme-devtool-oss::sections.contributors', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-devtool-oss::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-devtool-oss::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-devtool-oss::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-foundation-theme::theme.chrome.footer', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-devtool-oss::sections.hero', failLoudly: true),
        ];
    }
}
