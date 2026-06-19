<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AiLab;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\AiLab\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class AiLabThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'ai-lab';

    public static string $packageName = 'capell-app/theme-ai-lab';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'AI Lab',
            description: 'A multimodal foundation model with a 200k-token context window, built for long-horizon reasoning, tool use, and grounded answers. Run it in our hosted API or evaluate it against your own benchmark suite today.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/ai-lab.jpg',
            tags: ['AI', 'Research', 'Dark', 'Technical', 'Frontier'],
            bestFit: ['AI research labs', 'Foundation model providers', 'Deep-tech R&D teams', 'ML infrastructure companies'],
            includedSections: ['navigation', 'hero', 'model-cards', 'benchmarks', 'features', 'research-index', 'playground', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'AI Lab',
                    description: 'AI Lab visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/ai-lab.jpg',
                    values: [
                        'primaryColor' => '#6366f1',
                        'accentColor' => '#22d3ee',
                        'neutralColor' => '#18181b',
                        'surfaceColor' => '#0a0a0b',
                        'foregroundColor' => '#f4f4f5',
                        'headingFont' => 'space-grotesk',
                        'bodyFont' => 'inter',
                        'spacing' => 'airy',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'sm',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/ai-lab.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-ai-lab');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-ai-lab');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-ai-lab::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-ai-lab.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-ai-lab::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-ai-lab::sections.hero', failLoudly: true),
            'model-cards' => new ViewSectionRenderer(self::THEME_KEY, 'model-cards', 'capell-theme-ai-lab::sections.model-cards', failLoudly: true),
            'benchmarks' => new ViewSectionRenderer(self::THEME_KEY, 'benchmarks', 'capell-theme-ai-lab::sections.benchmarks', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-ai-lab::sections.features', failLoudly: true),
            'research-index' => new ViewSectionRenderer(self::THEME_KEY, 'research-index', 'capell-theme-ai-lab::sections.research-index', failLoudly: true),
            'playground' => new ViewSectionRenderer(self::THEME_KEY, 'playground', 'capell-theme-ai-lab::sections.playground', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-ai-lab::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-ai-lab::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-ai-lab::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-ai-lab::sections.footer', failLoudly: true),
        ];
    }
}
