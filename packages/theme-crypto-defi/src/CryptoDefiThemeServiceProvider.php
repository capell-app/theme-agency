<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CryptoDefi;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\CryptoDefi\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class CryptoDefiThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'crypto-defi';

    public static string $packageName = 'capell-app/theme-crypto-defi';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Crypto DeFi',
            description: 'Helix is a permissionless lending market where deposits earn yield and borrowers tap instant liquidity — settled in seconds, secured by audited contracts.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/crypto-defi.jpg',
            tags: ['Crypto', 'DeFi', 'Web3', 'Neon', 'Dark'],
            bestFit: ['DeFi protocols', 'On-chain lending markets', 'Web3 product launches', 'Token / protocol sites'],
            includedSections: ['navigation', 'hero', 'protocol-stats', 'token-metrics', 'features', 'how-it-works', 'audit-badges', 'wallet-cta', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Crypto DeFi',
                    description: 'Crypto DeFi visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/crypto-defi.jpg',
                    values: [
                        'primaryColor' => '#8b5cf6',
                        'accentColor' => '#22d3ee',
                        'neutralColor' => '#1e1b3a',
                        'surfaceColor' => '#0a0118',
                        'foregroundColor' => '#ede9fe',
                        'headingFont' => 'space-grotesk',
                        'bodyFont' => 'inter',
                        'spacing' => 'airy',
                        'alignment' => 'center',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'expressive',
                        'mediaTreatment' => 'duotone',
                        'radius' => 'lg',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/crypto-defi.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-crypto-defi');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-crypto-defi');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-crypto-defi::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_CRYPTO_DEFI_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-crypto-defi.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-foundation-theme::theme.chrome.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-crypto-defi::sections.hero', failLoudly: true),
            'protocol-stats' => new ViewSectionRenderer(self::THEME_KEY, 'protocol-stats', 'capell-theme-crypto-defi::sections.protocol-stats', failLoudly: true),
            'token-metrics' => new ViewSectionRenderer(self::THEME_KEY, 'token-metrics', 'capell-theme-crypto-defi::sections.token-metrics', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-crypto-defi::sections.features', failLoudly: true),
            'how-it-works' => new ViewSectionRenderer(self::THEME_KEY, 'how-it-works', 'capell-theme-crypto-defi::sections.how-it-works', failLoudly: true),
            'audit-badges' => new ViewSectionRenderer(self::THEME_KEY, 'audit-badges', 'capell-theme-crypto-defi::sections.audit-badges', failLoudly: true),
            'wallet-cta' => new ViewSectionRenderer(self::THEME_KEY, 'wallet-cta', 'capell-theme-crypto-defi::sections.wallet-cta', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-crypto-defi::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-crypto-defi::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-crypto-defi::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-foundation-theme::theme.chrome.footer', failLoudly: true),
        ];
    }
}
