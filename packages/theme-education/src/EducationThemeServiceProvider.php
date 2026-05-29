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
use Capell\ThemeStudio\Education\Rendering\PackageAwareSectionRenderer;
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
            includedSections: ['navigation', 'hero', 'features', 'proof', 'content-listing', 'course-catalog', 'instructors', 'events', 'enrolment-cta', 'resources', 'faq', 'cta', 'footer'],
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

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-education');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-education');

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-education.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );

        $eventsAvailable = CapellCore::isPackageInstalled('capell-app/events');
        $formBuilderAvailable = CapellCore::isPackageInstalled('capell-app/form-builder');
        $blogAvailable = CapellCore::isPackageInstalled('capell-app/blog');

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-education::page',
                sectionRenderers: [],
            ),
            sectionRenderers: collect(self::definition()->includedSections)
                ->map(fn (string $sectionKey): ViewSectionRenderer|PackageAwareSectionRenderer|null => $this->sectionRenderer(
                    $sectionKey,
                    $this->optionalSectionIntegrations($eventsAvailable, $formBuilderAvailable, $blogAvailable),
                ))
                ->filter()
                ->values()
                ->all(),
        );
    }

    /**
     * @param  array<string, array<string, bool>>  $optionalIntegrations
     */
    private function sectionRenderer(string $sectionKey, array $optionalIntegrations): ViewSectionRenderer|PackageAwareSectionRenderer|null
    {
        if ($this->isFoundationSection($sectionKey)) {
            return null;
        }

        $view = 'capell-theme-education::sections.' . $sectionKey;

        if (! view()->exists($view)) {
            return null;
        }

        if (array_key_exists($sectionKey, $optionalIntegrations)) {
            return new PackageAwareSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: $sectionKey,
                view: $view,
                integrations: $optionalIntegrations[$sectionKey],
                failLoudly: true,
            );
        }

        return new ViewSectionRenderer(
            themeKey: self::THEME_KEY,
            sectionKey: $sectionKey,
            view: $view,
            failLoudly: true,
        );
    }

    private function isFoundationSection(string $sectionKey): bool
    {
        return in_array($sectionKey, ['navigation', 'footer'], true);
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function optionalSectionIntegrations(bool $eventsAvailable, bool $formBuilderAvailable, bool $blogAvailable): array
    {
        return [
            'events' => ['eventsAvailable' => $eventsAvailable],
            'enrolment-cta' => ['formBuilderAvailable' => $formBuilderAvailable],
            'resources' => ['blogAvailable' => $blogAvailable],
        ];
    }
}
