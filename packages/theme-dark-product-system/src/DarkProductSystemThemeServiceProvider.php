<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DarkProductSystem;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\DarkProductSystem\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class DarkProductSystemThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'dark-product-system';

    public static string $packageName = 'capell-app/theme-dark-product-system';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Dark Product System',
            description: 'Dark Product System theme for product-led SaaS, teams, workflows, technical operations, and AI-assisted work with inboxes, issues, roadmaps, reviews, automation, activity, integrations, customer proof, and security modules.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/dark-product-system.jpg',
            tags: ['Dark SaaS', 'Product System', 'Workflows', 'Automation', 'Security'],
            bestFit: ['Product-led SaaS', 'Workflow platforms', 'Technical operations tools', 'AI-assisted work products', 'Team planning systems'],
            includedSections: ['navigation', 'hero', 'system-hero', 'workflow-rails', 'agents-automation', 'planning-roadmap', 'changelog-integrations', 'security-proof', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Dark Product System',
                    description: 'Dark Product System visual preset for near-black surfaces, refined grey borders, soft functional glows, detailed product UI shells, inboxes, issues, roadmaps, reviews, automation, activity, planning, changelog, integrations, customer proof, and security.',
                    previewImage: '/vendor/capell/themes/dark-product-system.jpg',
                    values: [
                        'primaryColor' => '#07080d',
                        'accentColor' => '#8b9cff',
                        'neutralColor' => '#11131c',
                        'surfaceColor' => '#090a10',
                        'foregroundColor' => '#f4f6ff',
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
            assets: ['css' => 'vendor/capell/themes/dark-product-system.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-dark-product-system');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-dark-product-system');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-dark-product-system::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-dark-product-system.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-dark-product-system::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-dark-product-system::sections.hero', failLoudly: true),
            'system-hero' => new ViewSectionRenderer(self::THEME_KEY, 'system-hero', 'capell-theme-dark-product-system::sections.system-hero', failLoudly: true),
            'workflow-rails' => new ViewSectionRenderer(self::THEME_KEY, 'workflow-rails', 'capell-theme-dark-product-system::sections.workflow-rails', failLoudly: true),
            'agents-automation' => new ViewSectionRenderer(self::THEME_KEY, 'agents-automation', 'capell-theme-dark-product-system::sections.agents-automation', failLoudly: true),
            'planning-roadmap' => new ViewSectionRenderer(self::THEME_KEY, 'planning-roadmap', 'capell-theme-dark-product-system::sections.planning-roadmap', failLoudly: true),
            'changelog-integrations' => new ViewSectionRenderer(self::THEME_KEY, 'changelog-integrations', 'capell-theme-dark-product-system::sections.changelog-integrations', failLoudly: true),
            'security-proof' => new ViewSectionRenderer(self::THEME_KEY, 'security-proof', 'capell-theme-dark-product-system::sections.security-proof', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-dark-product-system::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-dark-product-system::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-dark-product-system::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-dark-product-system::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-dark-product-system::sections.footer', failLoudly: true),
        ];
    }
}
