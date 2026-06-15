<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EstateAgents;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\EstateAgents\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class EstateAgentsThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'estate-agents';

    public static string $packageName = 'capell-app/theme-estate-agents';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Estate Agents',
            description: 'Property search theme for agencies, valuations, featured listings, local guides, agent proof, and viewing requests.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/estate-agents.jpg',
            tags: ['Property', 'Valuations', 'Local guides'],
            bestFit: ['Estate agencies', 'Lettings teams', 'Property consultants'],
            includedSections: [
                'navigation',
                'hero',
                'property-search',
                'featured-properties',
                'valuation-cta',
                'local-guide',
                'agent-team',
                'viewing-request',
                'market-proof',
                'features',
                'proof',
                'content-listing',
                'cta',
                'footer',
            ],
            presets: [
                new ThemePresetData(
                    key: 'estate-agents',
                    name: 'Estate Agents',
                    description: 'Architectural property preset with search, valuation, agent, and area-guide surfaces.',
                    previewImage: '/vendor/capell/themes/estate-agents.jpg',
                    values: [
                        'primaryColor' => '#174c3f',
                        'accentColor' => '#c7f464',
                        'neutralColor' => '#1a1c1f',
                        'surfaceColor' => '#f6f8f4',
                        'foregroundColor' => '#1a1c1f',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'editorial',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'sm',
                        'headingScale' => 'confident',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/estate-agents.css'],
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

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-estate-agents');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-estate-agents');
        $this->loadScreenshotFixtureRoutes();
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-estate-agents::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-estate-agents.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(env('CAPELL_THEME_ESTATE_AGENTS_SCREENSHOT_FIXTURES_ENABLED', false), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        $optionalIntegrations = [
            'property-search' => [
                'searchAvailable' => CapellCore::isPackageInstalled('capell-app/search'),
            ],
            'valuation-cta' => [
                'formBuilderAvailable' => CapellCore::isPackageInstalled('capell-app/form-builder'),
            ],
            'local-guide' => [
                'addressAvailable' => CapellCore::isPackageInstalled('capell-app/address'),
            ],
            'viewing-request' => [
                'formBuilderAvailable' => CapellCore::isPackageInstalled('capell-app/form-builder'),
            ],
        ];

        $renderers = [];

        foreach (self::definition()->includedSections as $sectionKey) {
            $view = 'capell-theme-estate-agents::sections.' . $sectionKey;

            if (! view()->exists($view)) {
                continue;
            }

            $renderers[$sectionKey] = new ViewSectionRenderer(
                self::THEME_KEY,
                $sectionKey,
                $view,
                true,
                $optionalIntegrations[$sectionKey] ?? [],
            );
        }

        return $renderers;
    }
}
