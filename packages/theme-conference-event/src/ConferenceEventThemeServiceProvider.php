<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ConferenceEvent;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\ConferenceEvent\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class ConferenceEventThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'conference-event';

    public static string $packageName = 'capell-app/theme-conference-event';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Conference Event',
            description: 'Conference Event gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/conference-event.jpg',
            tags: ['Conference', 'Event', 'Summit', 'Agenda', 'Tickets'],
            bestFit: ['Conferences and summits', 'Multi-track events', 'Ticketed industry events', 'Product launches with agendas'],
            includedSections: ['navigation', 'event-hero', 'agenda', 'speakers', 'ticket-tiers', 'features', 'sponsors', 'venue', 'proof', 'content-listing', 'cta', 'footer', 'hero'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Conference Event',
                    description: 'Conference Event visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/conference-event.jpg',
                    values: [
                        'primaryColor' => '#6d28d9',
                        'accentColor' => '#f472b6',
                        'neutralColor' => '#1e1b2e',
                        'surfaceColor' => '#faf5ff',
                        'foregroundColor' => '#1e1b2e',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'expressive',
                        'mediaTreatment' => 'framed',
                        'radius' => 'lg',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/conference-event.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-conference-event');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-conference-event');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-conference-event::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_CONFERENCE_EVENT_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-conference-event.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-foundation-theme::theme.chrome.navigation', failLoudly: true),
            'event-hero' => new ViewSectionRenderer(self::THEME_KEY, 'event-hero', 'capell-theme-conference-event::sections.event-hero', failLoudly: true),
            'agenda' => new ViewSectionRenderer(self::THEME_KEY, 'agenda', 'capell-theme-conference-event::sections.agenda', failLoudly: true),
            'speakers' => new ViewSectionRenderer(self::THEME_KEY, 'speakers', 'capell-theme-conference-event::sections.speakers', failLoudly: true),
            'ticket-tiers' => new ViewSectionRenderer(self::THEME_KEY, 'ticket-tiers', 'capell-theme-conference-event::sections.ticket-tiers', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-conference-event::sections.features', failLoudly: true),
            'sponsors' => new ViewSectionRenderer(self::THEME_KEY, 'sponsors', 'capell-theme-conference-event::sections.sponsors', failLoudly: true),
            'venue' => new ViewSectionRenderer(self::THEME_KEY, 'venue', 'capell-theme-conference-event::sections.venue', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-conference-event::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-conference-event::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-conference-event::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-foundation-theme::theme.chrome.footer', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-conference-event::sections.hero', failLoudly: true),
        ];
    }
}
