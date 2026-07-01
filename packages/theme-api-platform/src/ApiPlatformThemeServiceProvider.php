<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ApiPlatform;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\ApiPlatform\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class ApiPlatformThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'api-platform';

    public static string $packageName = 'capell-app/theme-api-platform';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'API Platform',
            description: 'API Platform gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/api-platform.jpg',
            tags: ['Developer', 'API', 'Dark', 'Code', 'Infrastructure'],
            bestFit: ['Developer API products', 'Voice/auth/infra platforms', 'SDK-led tools', 'Platform engineering teams'],
            includedSections: ['navigation', 'code-hero', 'quickstart', 'features', 'sdk-grid', 'api-reference-teaser', 'status-uptime', 'proof', 'content-listing', 'cta', 'footer', 'hero'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'API Platform',
                    description: 'API Platform visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/api-platform.jpg',
                    values: [
                        'primaryColor' => '#38bdf8',
                        'accentColor' => '#a78bfa',
                        'neutralColor' => '#1e293b',
                        'surfaceColor' => '#0b1020',
                        'foregroundColor' => '#e2e8f0',
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
            assets: ['css' => 'vendor/capell/themes/api-platform.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-api-platform');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-api-platform');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-api-platform::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-api-platform.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-foundation-theme::theme.chrome.navigation', failLoudly: true),
            'code-hero' => new ViewSectionRenderer(self::THEME_KEY, 'code-hero', 'capell-theme-api-platform::sections.code-hero', failLoudly: true),
            'quickstart' => new ViewSectionRenderer(self::THEME_KEY, 'quickstart', 'capell-theme-api-platform::sections.quickstart', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-api-platform::sections.features', failLoudly: true),
            'sdk-grid' => new ViewSectionRenderer(self::THEME_KEY, 'sdk-grid', 'capell-theme-api-platform::sections.sdk-grid', failLoudly: true),
            'api-reference-teaser' => new ViewSectionRenderer(self::THEME_KEY, 'api-reference-teaser', 'capell-theme-api-platform::sections.api-reference-teaser', failLoudly: true),
            'status-uptime' => new ViewSectionRenderer(self::THEME_KEY, 'status-uptime', 'capell-theme-api-platform::sections.status-uptime', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-api-platform::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-api-platform::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-api-platform::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-foundation-theme::theme.chrome.footer', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-api-platform::sections.hero', failLoudly: true),
        ];
    }
}
