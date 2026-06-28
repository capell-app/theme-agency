<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FintechTrust;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\FintechTrust\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class FintechTrustThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'fintech-trust';

    public static string $packageName = 'capell-app/theme-fintech-trust';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Fintech Trust',
            description: 'Verify a business in seconds — ownership, registration, sanctions, and risk — with an audit trail your compliance team and your auditors both trust.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/fintech-trust.jpg',
            tags: ['Fintech', 'Compliance', 'Trust', 'Security', 'B2B'],
            bestFit: ['Fintech infrastructure', 'Identity verification / KYB', 'Compliance & RegTech', 'Fraud prevention'],
            includedSections: ['navigation', 'hero', 'compliance-badges', 'features', 'security-architecture', 'verification-flow', 'metric-cards', 'coverage-map', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Fintech Trust',
                    description: 'Fintech Trust visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/fintech-trust.jpg',
                    values: [
                        'primaryColor' => '#1e3a8a',
                        'accentColor' => '#14b8a6',
                        'neutralColor' => '#0f172a',
                        'surfaceColor' => '#f8fafc',
                        'foregroundColor' => '#0f172a',
                        'headingFont' => 'inter',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/fintech-trust.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-fintech-trust');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-fintech-trust');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-fintech-trust::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-fintech-trust.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-foundation-theme::theme.chrome.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-fintech-trust::sections.hero', failLoudly: true),
            'compliance-badges' => new ViewSectionRenderer(self::THEME_KEY, 'compliance-badges', 'capell-theme-fintech-trust::sections.compliance-badges', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-fintech-trust::sections.features', failLoudly: true),
            'security-architecture' => new ViewSectionRenderer(self::THEME_KEY, 'security-architecture', 'capell-theme-fintech-trust::sections.security-architecture', failLoudly: true),
            'verification-flow' => new ViewSectionRenderer(self::THEME_KEY, 'verification-flow', 'capell-theme-fintech-trust::sections.verification-flow', failLoudly: true),
            'metric-cards' => new ViewSectionRenderer(self::THEME_KEY, 'metric-cards', 'capell-theme-fintech-trust::sections.metric-cards', failLoudly: true),
            'coverage-map' => new ViewSectionRenderer(self::THEME_KEY, 'coverage-map', 'capell-theme-fintech-trust::sections.coverage-map', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-fintech-trust::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-fintech-trust::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-fintech-trust::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-foundation-theme::theme.chrome.footer', failLoudly: true),
        ];
    }
}
