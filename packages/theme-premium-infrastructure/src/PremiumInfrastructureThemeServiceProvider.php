<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PremiumInfrastructure;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\PremiumInfrastructure\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class PremiumInfrastructureThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'premium-infrastructure';

    public static string $packageName = 'capell-app/theme-premium-infrastructure';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Premium Infrastructure',
            description: 'Premium Infrastructure theme for complex B2B SaaS products with layered product UI mockups, solutions, global scale, developer tools, metrics, customer proof, code/product splits, news, trust, and compliance modules.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/premium-infrastructure.jpg',
            tags: ['Infrastructure', 'B2B SaaS', 'Developer Tools', 'Global Scale', 'Trust'],
            bestFit: ['Infrastructure platforms', 'Complex B2B SaaS', 'Developer tool companies', 'Global fintech products', 'Enterprise API platforms'],
            includedSections: ['navigation', 'hero', 'product-panels', 'solutions', 'global-scale', 'developer-tools', 'case-studies-news', 'trust-compliance', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Premium Infrastructure',
                    description: 'Premium Infrastructure visual preset for confident display type, layered product UI mockups, restrained accent color, metrics strips, code/product split sections, global map cues, developer tools, case studies, news, trust, and compliance.',
                    previewImage: '/vendor/capell/themes/premium-infrastructure.jpg',
                    values: [
                        'primaryColor' => '#101828',
                        'accentColor' => '#12b8a6',
                        'neutralColor' => '#172033',
                        'surfaceColor' => '#f7fbff',
                        'foregroundColor' => '#101828',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/premium-infrastructure.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-premium-infrastructure');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-premium-infrastructure');

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-premium-infrastructure::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-premium-infrastructure.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-premium-infrastructure::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-premium-infrastructure::sections.hero', failLoudly: true),
            'product-panels' => new ViewSectionRenderer(self::THEME_KEY, 'product-panels', 'capell-theme-premium-infrastructure::sections.product-panels', failLoudly: true),
            'solutions' => new ViewSectionRenderer(self::THEME_KEY, 'solutions', 'capell-theme-premium-infrastructure::sections.solutions', failLoudly: true),
            'global-scale' => new ViewSectionRenderer(self::THEME_KEY, 'global-scale', 'capell-theme-premium-infrastructure::sections.global-scale', failLoudly: true),
            'developer-tools' => new ViewSectionRenderer(self::THEME_KEY, 'developer-tools', 'capell-theme-premium-infrastructure::sections.developer-tools', failLoudly: true),
            'case-studies-news' => new ViewSectionRenderer(self::THEME_KEY, 'case-studies-news', 'capell-theme-premium-infrastructure::sections.case-studies-news', failLoudly: true),
            'trust-compliance' => new ViewSectionRenderer(self::THEME_KEY, 'trust-compliance', 'capell-theme-premium-infrastructure::sections.trust-compliance', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-premium-infrastructure::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-premium-infrastructure::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-premium-infrastructure::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-premium-infrastructure::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-premium-infrastructure::sections.footer', failLoudly: true),
        ];
    }
}
