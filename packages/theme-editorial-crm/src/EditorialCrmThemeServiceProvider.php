<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EditorialCrm;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\EditorialCrm\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class EditorialCrmThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'editorial-crm';

    public static string $packageName = 'capell-app/theme-editorial-crm';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Editorial CRM',
            description: 'Editorial CRM theme for modern revenue and operations platforms with refined product dashboards, relationship records, workflow automation, collaboration, integrations, reporting, customer stories, and flexible dashboards.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/editorial-crm.jpg',
            tags: ['CRM', 'SaaS', 'Operations', 'Automation', 'Dashboards'],
            bestFit: ['CRM platforms', 'Revenue operations products', 'Customer data platforms', 'Workflow automation tools', 'Modern SaaS products'],
            includedSections: ['navigation', 'hero', 'product-dashboard', 'data-model', 'workflow-automation', 'collaboration', 'integrations-reporting', 'customer-stories', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Editorial CRM',
                    description: 'Editorial CRM visual preset for refined typography, generous whitespace, neutral product fields, crisp interface modules, relationship records, pipeline notes, automation flows, integrations, reporting, customer stories, and flexible dashboards.',
                    previewImage: '/vendor/capell/themes/editorial-crm.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#5f6df2',
                        'neutralColor' => '#182033',
                        'surfaceColor' => '#f7f5f0',
                        'foregroundColor' => '#111111',
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
            assets: ['css' => 'vendor/capell/themes/editorial-crm.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-editorial-crm');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-editorial-crm');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-editorial-crm::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-editorial-crm.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-editorial-crm::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-editorial-crm::sections.hero', failLoudly: true),
            'product-dashboard' => new ViewSectionRenderer(self::THEME_KEY, 'product-dashboard', 'capell-theme-editorial-crm::sections.product-dashboard', failLoudly: true),
            'data-model' => new ViewSectionRenderer(self::THEME_KEY, 'data-model', 'capell-theme-editorial-crm::sections.data-model', failLoudly: true),
            'workflow-automation' => new ViewSectionRenderer(self::THEME_KEY, 'workflow-automation', 'capell-theme-editorial-crm::sections.workflow-automation', failLoudly: true),
            'collaboration' => new ViewSectionRenderer(self::THEME_KEY, 'collaboration', 'capell-theme-editorial-crm::sections.collaboration', failLoudly: true),
            'integrations-reporting' => new ViewSectionRenderer(self::THEME_KEY, 'integrations-reporting', 'capell-theme-editorial-crm::sections.integrations-reporting', failLoudly: true),
            'customer-stories' => new ViewSectionRenderer(self::THEME_KEY, 'customer-stories', 'capell-theme-editorial-crm::sections.customer-stories', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-editorial-crm::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-editorial-crm::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-editorial-crm::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-editorial-crm::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-editorial-crm::sections.footer', failLoudly: true),
        ];
    }
}
