<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Corporate;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Corporate\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

class CorporateThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'corporate';

    public static string $packageName = 'capell-app/theme-corporate';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Corporate',
            description: 'Trust-led layouts with restrained hierarchy, formal navigation, and structured proof.',
            package: 'capell-app/theme-corporate',
            previewImage: '/vendor/capell/themes/corporate-boardroom.jpg',
            tags: ['Trust', 'Clarity', 'B2B'],
            bestFit: ['Professional services', 'Public sector', 'Established businesses'],
            includedSections: ['navigation', 'hero', 'features', 'proof', 'content-listing', 'locations', 'investor-relations', 'careers', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'boardroom',
                    name: 'Boardroom',
                    description: 'Deep navy, measured spacing, and formal card structure.',
                    previewImage: '/vendor/capell/themes/corporate-boardroom.jpg',
                    values: [
                        'primaryColor' => '#1a2d6d',
                        'accentColor' => '#f59e0b',
                        'headingFont' => 'playfair',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'standard',
                        'layoutPresentation' => 'structured',
                    ],
                ),
                new ThemePresetData(
                    key: 'civic',
                    name: 'Civic',
                    description: 'Accessible contrast, calm typography, and clear information hierarchy.',
                    previewImage: '/vendor/capell/themes/corporate-civic.jpg',
                    values: [
                        'primaryColor' => '#0f766e',
                        'accentColor' => '#facc15',
                        'headingFont' => 'inter',
                        'cardStyle' => 'subtle',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                    ],
                ),
                new ThemePresetData(
                    key: 'advisory',
                    name: 'Advisory',
                    description: 'Editorial trust signals with generous whitespace and refined proof blocks.',
                    previewImage: '/vendor/capell/themes/corporate-advisory.jpg',
                    values: [
                        'primaryColor' => '#312e81',
                        'accentColor' => '#c084fc',
                        'headingFont' => 'manrope',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'editorial',
                    ],
                ),
                new ThemePresetData(
                    key: 'integrity',
                    name: 'Integrity',
                    description: 'Institutional security and board-grade structure from the Stitch Institutional Integrity system.',
                    previewImage: '/vendor/capell/themes/corporate-integrity.jpg',
                    values: [
                        'primaryColor' => '#0f172a',
                        'accentColor' => '#0284c7',
                        'neutralColor' => '#334155',
                        'surfaceColor' => '#f7f9fb',
                        'foregroundColor' => '#191c1e',
                        'headingFont' => 'inter',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'standard',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'natural',
                        'radius' => 'sm',
                        'headingScale' => 'compact',
                        'cardDensity' => 'compact',
                    ],
                ),
                new ThemePresetData(
                    key: 'enterprise-trust',
                    name: 'Enterprise Trust',
                    description: 'Formal trust-led enterprise direction based on the Stitch Enterprise Trust homepage.',
                    previewImage: '/vendor/capell/themes/corporate-enterprise-trust.jpg',
                    values: [
                        'primaryColor' => '#172554',
                        'accentColor' => '#d97706',
                        'neutralColor' => '#1e293b',
                        'surfaceColor' => '#f8fafc',
                        'foregroundColor' => '#0f172a',
                        'headingFont' => 'manrope',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'natural',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
                new ThemePresetData(
                    key: 'public-ledger',
                    name: 'Public Ledger',
                    description: 'Civic-grade information architecture for content-heavy public and regulated sites.',
                    previewImage: '/vendor/capell/themes/corporate-public-ledger.jpg',
                    values: [
                        'primaryColor' => '#0f766e',
                        'accentColor' => '#ca8a04',
                        'neutralColor' => '#263238',
                        'surfaceColor' => '#fbfaf7',
                        'foregroundColor' => '#18201f',
                        'headingFont' => 'inter',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'standard',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'natural',
                        'radius' => 'none',
                        'headingScale' => 'compact',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/corporate.css'],
            runtime: FrontendRuntime::Blade,
            // Theme Studio inherits section fallbacks from the runtime default; capell.json no longer declares package-level inheritance because the default fallback lives in capell-app/frontend.
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-corporate');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-corporate');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-corporate::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_CORPORATE_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::buildAsset(
                path: 'vendor/capell-theme-corporate',
                file: 'resources/js/theme-corporate.js',
                packageName: self::$packageName,
            ),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-corporate.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );
    }

    /**
     * @return array<string, ViewSectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-corporate::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-corporate::sections.hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-corporate::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-corporate::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-corporate::sections.content-listing', failLoudly: true),
            'locations' => new ViewSectionRenderer(self::THEME_KEY, 'locations', 'capell-theme-corporate::sections.locations', failLoudly: true),
            'investor-relations' => new ViewSectionRenderer(self::THEME_KEY, 'investor-relations', 'capell-theme-corporate::sections.investor-relations', failLoudly: true),
            'careers' => new ViewSectionRenderer(self::THEME_KEY, 'careers', 'capell-theme-corporate::sections.careers', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-corporate::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-corporate::sections.footer', failLoudly: true),
        ];
    }
}
