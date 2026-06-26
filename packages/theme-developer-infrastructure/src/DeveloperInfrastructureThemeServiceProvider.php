<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DeveloperInfrastructure;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\DeveloperInfrastructure\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class DeveloperInfrastructureThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'developer-infrastructure';

    public static string $packageName = 'capell-app/theme-developer-infrastructure';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Developer Infrastructure',
            description: 'Developer Infrastructure theme for platform products with minimal white foundations, mono labels, sharp dividers, technical pillars, command blocks, deploy timelines, docs sidebars, changelog entries, integrations, architecture diagrams, and enterprise CTAs.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/developer-infrastructure.jpg',
            tags: ['Developer Platform', 'Infrastructure', 'Docs', 'Deployments', 'Enterprise'],
            bestFit: ['Developer platforms', 'Infrastructure products', 'API products', 'Deployment tools', 'Technical SaaS'],
            includedSections: ['navigation', 'hero', 'platform-pillars', 'command-blocks', 'deploy-timeline', 'docs-changelog', 'integrations', 'architecture-enterprise', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Developer Infrastructure',
                    description: 'Developer Infrastructure visual preset for white foundations, black typography, mono labels, sharp dividers, command blocks, deploy timelines, technical feature lists, docs sidebars, changelog entries, integrations, architecture diagrams, and enterprise CTAs.',
                    previewImage: '/vendor/capell/themes/developer-infrastructure.jpg',
                    values: [
                        'primaryColor' => '#000000',
                        'accentColor' => '#0066ff',
                        'neutralColor' => '#111111',
                        'surfaceColor' => '#ffffff',
                        'foregroundColor' => '#000000',
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
            assets: ['css' => 'vendor/capell/themes/developer-infrastructure.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-developer-infrastructure');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-developer-infrastructure');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-developer-infrastructure::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_DEVELOPER_INFRASTRUCTURE_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-developer-infrastructure.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-developer-infrastructure::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-developer-infrastructure::sections.hero', failLoudly: true),
            'platform-pillars' => new ViewSectionRenderer(self::THEME_KEY, 'platform-pillars', 'capell-theme-developer-infrastructure::sections.platform-pillars', failLoudly: true),
            'command-blocks' => new ViewSectionRenderer(self::THEME_KEY, 'command-blocks', 'capell-theme-developer-infrastructure::sections.command-blocks', failLoudly: true),
            'deploy-timeline' => new ViewSectionRenderer(self::THEME_KEY, 'deploy-timeline', 'capell-theme-developer-infrastructure::sections.deploy-timeline', failLoudly: true),
            'docs-changelog' => new ViewSectionRenderer(self::THEME_KEY, 'docs-changelog', 'capell-theme-developer-infrastructure::sections.docs-changelog', failLoudly: true),
            'integrations' => new ViewSectionRenderer(self::THEME_KEY, 'integrations', 'capell-theme-developer-infrastructure::sections.integrations', failLoudly: true),
            'architecture-enterprise' => new ViewSectionRenderer(self::THEME_KEY, 'architecture-enterprise', 'capell-theme-developer-infrastructure::sections.architecture-enterprise', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-developer-infrastructure::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-developer-infrastructure::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-developer-infrastructure::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-developer-infrastructure::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-developer-infrastructure::sections.footer', failLoudly: true),
        ];
    }
}
