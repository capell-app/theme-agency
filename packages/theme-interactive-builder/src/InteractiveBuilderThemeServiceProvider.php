<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InteractiveBuilder;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\InteractiveBuilder\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class InteractiveBuilderThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'interactive-builder';

    public static string $packageName = 'capell-app/theme-interactive-builder';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Interactive Builder',
            description: 'Interactive Builder theme for visual tools with dark hero systems, high-contrast canvas mockups, floating layers, asset panels, breakpoints, publishing controls, comments, AI helpers, templates, collaboration, and community showcases.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/interactive-builder.jpg',
            tags: ['Builder', 'Creative Tool', 'SaaS', 'Templates', 'Collaboration'],
            bestFit: ['Visual builder products', 'Creative tools', 'No-code platforms', 'Template marketplaces', 'Design collaboration products'],
            includedSections: ['navigation', 'hero', 'canvas-workspace', 'builder-capabilities', 'templates', 'collaboration-comments', 'community-showcase', 'publishing-controls', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Interactive Builder',
                    description: 'Interactive Builder visual preset for dark heroes, high-contrast UI mockups, floating canvas panels, colorful media strips, layers, assets, breakpoints, publishing controls, comments, AI helper panels, templates, collaboration, community, and live preview states.',
                    previewImage: '/vendor/capell/themes/interactive-builder.jpg',
                    values: [
                        'primaryColor' => '#0b0b12',
                        'accentColor' => '#46f0ff',
                        'neutralColor' => '#11111b',
                        'surfaceColor' => '#0f1018',
                        'foregroundColor' => '#f7f8ff',
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
            assets: ['css' => 'vendor/capell/themes/interactive-builder.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-interactive-builder');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-interactive-builder');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-interactive-builder::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-interactive-builder.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-interactive-builder::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-interactive-builder::sections.hero', failLoudly: true),
            'canvas-workspace' => new ViewSectionRenderer(self::THEME_KEY, 'canvas-workspace', 'capell-theme-interactive-builder::sections.canvas-workspace', failLoudly: true),
            'builder-capabilities' => new ViewSectionRenderer(self::THEME_KEY, 'builder-capabilities', 'capell-theme-interactive-builder::sections.builder-capabilities', failLoudly: true),
            'templates' => new ViewSectionRenderer(self::THEME_KEY, 'templates', 'capell-theme-interactive-builder::sections.templates', failLoudly: true),
            'collaboration-comments' => new ViewSectionRenderer(self::THEME_KEY, 'collaboration-comments', 'capell-theme-interactive-builder::sections.collaboration-comments', failLoudly: true),
            'community-showcase' => new ViewSectionRenderer(self::THEME_KEY, 'community-showcase', 'capell-theme-interactive-builder::sections.community-showcase', failLoudly: true),
            'publishing-controls' => new ViewSectionRenderer(self::THEME_KEY, 'publishing-controls', 'capell-theme-interactive-builder::sections.publishing-controls', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-interactive-builder::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-interactive-builder::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-interactive-builder::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-interactive-builder::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-interactive-builder::sections.footer', failLoudly: true),
        ];
    }
}
