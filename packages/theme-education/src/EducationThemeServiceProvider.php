<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Education;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Education\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class EducationThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'education';

    public static string $packageName = 'capell-app/theme-education';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Education',
            description: 'Course and school theme for education providers, training teams, and learning programmes.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/education.jpg',
            tags: ['Education', 'Courses', 'Enrolment'],
            bestFit: ['Schools', 'Course providers', 'Training teams'],
            includedSections: ['navigation', 'hero', 'course-catalog', 'instructors', 'events', 'enrolment-cta', 'resources', 'faq', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'education',
                    name: 'Education',
                    description: 'Course and school theme for education providers, training teams, and learning programmes.',
                    previewImage: '/vendor/capell/themes/education.jpg',
                    values: [
                        'primaryColor' => '#4338ca',
                        'accentColor' => '#14b8a6',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/education.css'],
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

        if (! CapellCore::hasPackage(self::$packageName)) {
            return;
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-education');

        CapellCore::registerVendorAsset(new VendorAssetData(
            package: self::$packageName,
            path: 'resources/css/theme-education.css',
            type: 'css',
        ));

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-education::page',
                sectionRenderers: [],
            ),
            sectionRenderers: array_map(
                fn (string $sectionKey): ViewSectionRenderer => new ViewSectionRenderer(
                    themeKey: self::THEME_KEY,
                    sectionKey: $sectionKey,
                    view: 'capell-theme-education::sections.' . $sectionKey,
                ),
                self::definition()->includedSections,
            ),
        );
    }
}
