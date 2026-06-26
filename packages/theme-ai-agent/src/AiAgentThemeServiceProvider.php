<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AiAgent;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\AiAgent\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class AiAgentThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'ai-agent';

    public static string $packageName = 'capell-app/theme-ai-agent';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'AI Agent',
            description: 'Aria reads the ticket, gathers context from your tools, takes the action, and closes the loop — autonomously. Your team handles the hard 49%; Aria handles the rest.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/ai-agent.jpg',
            tags: ['AI', 'Automation', 'Support', 'Outcomes', 'Light'],
            bestFit: ['AI support agents', 'Ops automation products', 'Customer service AI', 'RevOps tooling'],
            includedSections: ['navigation', 'hero', 'outcome-metrics', 'agent-in-action', 'features', 'integrations-grid', 'roi-calculator', 'use-cases', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'AI Agent',
                    description: 'AI Agent visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/ai-agent.jpg',
                    values: [
                        'primaryColor' => '#0ea5e9',
                        'accentColor' => '#22c55e',
                        'neutralColor' => '#0f172a',
                        'surfaceColor' => '#f8fafc',
                        'foregroundColor' => '#0f172a',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'lg',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/ai-agent.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-ai-agent');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-ai-agent');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-ai-agent::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_AI_AGENT_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-ai-agent.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-ai-agent::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-ai-agent::sections.hero', failLoudly: true),
            'outcome-metrics' => new ViewSectionRenderer(self::THEME_KEY, 'outcome-metrics', 'capell-theme-ai-agent::sections.outcome-metrics', failLoudly: true),
            'agent-in-action' => new ViewSectionRenderer(self::THEME_KEY, 'agent-in-action', 'capell-theme-ai-agent::sections.agent-in-action', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-ai-agent::sections.features', failLoudly: true),
            'integrations-grid' => new ViewSectionRenderer(self::THEME_KEY, 'integrations-grid', 'capell-theme-ai-agent::sections.integrations-grid', failLoudly: true),
            'roi-calculator' => new ViewSectionRenderer(self::THEME_KEY, 'roi-calculator', 'capell-theme-ai-agent::sections.roi-calculator', failLoudly: true),
            'use-cases' => new ViewSectionRenderer(self::THEME_KEY, 'use-cases', 'capell-theme-ai-agent::sections.use-cases', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-ai-agent::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-ai-agent::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-ai-agent::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-ai-agent::sections.footer', failLoudly: true),
        ];
    }
}
