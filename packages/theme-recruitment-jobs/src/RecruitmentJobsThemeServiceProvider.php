<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\RecruitmentJobs;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\RecruitmentJobs\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class RecruitmentJobsThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'recruitment-jobs';

    public static string $packageName = 'capell-app/theme-recruitment-jobs';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Recruitment & Jobs',
            description: 'We\'re a specialist recruitment team working across Tech, Finance, and Healthcare. We take the time to understand the role and the person — so candidates land somewhere they fit, and employers hire people who stay.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/recruitment-jobs.jpg',
            tags: ['Recruitment', 'Jobs', 'Hiring', 'Two-sided', 'Structured'],
            bestFit: ['Recruitment agencies', 'Job boards', 'Specialist recruiters', 'Exec search', 'In-house talent teams'],
            includedSections: ['navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Recruitment & Jobs',
                    description: 'Recruitment & Jobs visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/recruitment-jobs.jpg',
                    values: [
                        'primaryColor' => '#0f766e',
                        'accentColor' => '#6366f1',
                        'neutralColor' => '#0f172a',
                        'surfaceColor' => '#f8fafc',
                        'foregroundColor' => '#0f172a',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/recruitment-jobs.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-recruitment-jobs');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-recruitment-jobs');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-recruitment-jobs::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-recruitment-jobs.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-recruitment-jobs::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-recruitment-jobs::sections.hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-recruitment-jobs::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-recruitment-jobs::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-recruitment-jobs::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-recruitment-jobs::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-recruitment-jobs::sections.footer', failLoudly: true),
        ];
    }
}
