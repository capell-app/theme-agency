<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Healthcare;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Healthcare\Console\Commands\DemoCommand;
use Capell\ThemeStudio\Healthcare\Rendering\BlogTeaserSectionRenderer;
use Capell\ThemeStudio\Healthcare\Rendering\BookingSectionRenderer;
use Capell\ThemeStudio\Healthcare\Rendering\EventPanelSectionRenderer;
use Illuminate\Support\ServiceProvider;
use Override;

class HealthcareThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'healthcare';

    public static string $packageName = 'capell-app/theme-healthcare';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Healthcare',
            description: 'Premium healthcare layouts for appointment-led clinics, service discovery, clinicians, care pathways, events, resources, and location conversion.',
            package: 'capell-app/theme-healthcare',
            previewImage: '/vendor/capell/themes/healthcare.jpg',
            tags: ['Healthcare', 'Appointments', 'Services'],
            bestFit: ['Private clinics', 'Healthcare groups', 'Specialist care providers'],
            includedSections: ['utility-bar', 'navigation', 'hero', 'features', 'content-listing', 'service-finder', 'services', 'care-pathway', 'clinicians', 'clinician-profile', 'conditions-directory', 'booking', 'emergency-escalation', 'locations', 'insurance-trust', 'events', 'proof', 'comparison', 'blog-teaser', 'contact', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'healthcare',
                    name: 'Healthcare',
                    description: 'Clean clinical premium direction with ice surfaces, deep teal leadership, secondary blue links, and warm amber appointment actions.',
                    previewImage: '/vendor/capell/themes/healthcare.jpg',
                    values: [
                        'primaryColor' => '#0f766e',
                        'accentColor' => '#f59e0b',
                        'neutralColor' => '#14323a',
                        'surfaceColor' => '#f6fbfd',
                        'foregroundColor' => '#14323a',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/healthcare.css'],
            runtime: FrontendRuntime::Blade,
            // Capell Frontend registers the built-in "default" runtime inheritance key.
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-healthcare');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-healthcare');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-healthcare::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-healthcare.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        $blogAvailable = CapellCore::isPackageInstalled('capell-app/blog');
        $eventsAvailable = CapellCore::isPackageInstalled('capell-app/events');
        $formBuilderAvailable = CapellCore::isPackageInstalled('capell-app/form-builder');

        return [
            'utility-bar' => new ViewSectionRenderer(self::THEME_KEY, 'utility-bar', 'capell-theme-healthcare::sections.utility-bar', failLoudly: true),
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-healthcare::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-healthcare::sections.hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-healthcare::sections.services', failLoudly: true),
            'content-listing' => new BlogTeaserSectionRenderer(self::THEME_KEY, 'content-listing', $blogAvailable, failLoudly: true),
            'service-finder' => new ViewSectionRenderer(self::THEME_KEY, 'service-finder', 'capell-theme-healthcare::sections.service-finder', failLoudly: true),
            'services' => new ViewSectionRenderer(self::THEME_KEY, 'services', 'capell-theme-healthcare::sections.services', failLoudly: true),
            'care-pathway' => new ViewSectionRenderer(self::THEME_KEY, 'care-pathway', 'capell-theme-healthcare::sections.care-pathway', true, ['formBuilderAvailable' => $formBuilderAvailable]),
            'clinicians' => new ViewSectionRenderer(self::THEME_KEY, 'clinicians', 'capell-theme-healthcare::sections.clinicians', failLoudly: true),
            'clinician-profile' => new ViewSectionRenderer(self::THEME_KEY, 'clinician-profile', 'capell-theme-healthcare::sections.clinician-profile', failLoudly: true),
            'conditions-directory' => new ViewSectionRenderer(self::THEME_KEY, 'conditions-directory', 'capell-theme-healthcare::sections.conditions-directory', failLoudly: true),
            'booking' => new BookingSectionRenderer(self::THEME_KEY, $formBuilderAvailable, failLoudly: true),
            'emergency-escalation' => new ViewSectionRenderer(self::THEME_KEY, 'emergency-escalation', 'capell-theme-healthcare::sections.emergency-escalation', failLoudly: true),
            'locations' => new ViewSectionRenderer(self::THEME_KEY, 'locations', 'capell-theme-healthcare::sections.locations', true, ['eventsAvailable' => $eventsAvailable]),
            'insurance-trust' => new ViewSectionRenderer(self::THEME_KEY, 'insurance-trust', 'capell-theme-healthcare::sections.insurance-trust', failLoudly: true),
            'events' => new EventPanelSectionRenderer(self::THEME_KEY, $eventsAvailable, failLoudly: true),
            'comparison' => new ViewSectionRenderer(self::THEME_KEY, 'comparison', 'capell-theme-healthcare::sections.comparison', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-healthcare::sections.proof', failLoudly: true),
            'blog-teaser' => new BlogTeaserSectionRenderer(self::THEME_KEY, 'blog-teaser', $blogAvailable, failLoudly: true),
            'contact' => new ViewSectionRenderer(self::THEME_KEY, 'contact', 'capell-theme-healthcare::sections.contact', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-healthcare::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-healthcare::sections.footer', failLoudly: true),
        ];
    }
}
