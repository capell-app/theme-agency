<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Nonprofit;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Nonprofit\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class NonprofitThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'nonprofit';

    public static string $packageName = 'capell-app/theme-nonprofit';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Nonprofit',
            description: 'Impact-led civic and charity theme for campaigns, donations, volunteering, and community stories.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/nonprofit.jpg',
            tags: ['Impact', 'Campaigns', 'Donations'],
            bestFit: ['Charities', 'Civic organisations', 'Campaign teams'],
            includedSections: ['navigation', 'hero', 'features', 'proof', 'content-listing', 'impact', 'donation-impact', 'campaigns', 'volunteer-donate', 'volunteer-shifts', 'annual-report-proof', 'events', 'stories', 'contact', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'nonprofit',
                    name: 'Nonprofit',
                    description: 'Impact-led civic and charity theme for campaigns, donations, volunteering, and community stories.',
                    previewImage: '/vendor/capell/themes/nonprofit.jpg',
                    values: [
                        'primaryColor' => '#166534',
                        'accentColor' => '#eab308',
                        'neutralColor' => '#132016',
                        'surfaceColor' => '#f7fbf5',
                        'foregroundColor' => '#132016',
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
            assets: ['css' => 'vendor/capell/themes/nonprofit.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-nonprofit');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-nonprofit');

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-nonprofit.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );

        $campaignStudioAvailable = CapellCore::isPackageInstalled('capell-app/campaign-studio');
        $formBuilderAvailable = CapellCore::isPackageInstalled('capell-app/form-builder');
        $eventsAvailable = CapellCore::isPackageInstalled('capell-app/events');
        $blogAvailable = CapellCore::isPackageInstalled('capell-app/blog');

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-nonprofit::page',
                sectionRenderers: [],
            ),
            sectionRenderers: collect(self::definition()->includedSections)
                ->map(fn (string $sectionKey): ?ViewSectionRenderer => $this->sectionRenderer(
                    $sectionKey,
                    $this->optionalSectionIntegrations($campaignStudioAvailable, $formBuilderAvailable, $eventsAvailable, $blogAvailable),
                ))
                ->filter()
                ->values()
                ->all(),
        );
    }

    /**
     * @param  array<string, array<string, bool>>  $optionalIntegrations
     */
    private function sectionRenderer(string $sectionKey, array $optionalIntegrations): ?ViewSectionRenderer
    {
        if ($this->isFoundationSection($sectionKey)) {
            return null;
        }

        $view = 'capell-theme-nonprofit::sections.' . $sectionKey;

        if (! view()->exists($view)) {
            return null;
        }

        if (array_key_exists($sectionKey, $optionalIntegrations)) {
            return new ViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: $sectionKey,
                view: $view,
                failLoudly: true,
                extraViewData: $optionalIntegrations[$sectionKey],
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
    private function optionalSectionIntegrations(bool $campaignStudioAvailable, bool $formBuilderAvailable, bool $eventsAvailable, bool $blogAvailable): array
    {
        return [
            'campaigns' => ['campaignStudioAvailable' => $campaignStudioAvailable],
            'donation-impact' => ['campaignStudioAvailable' => $campaignStudioAvailable],
            'volunteer-donate' => ['formBuilderAvailable' => $formBuilderAvailable],
            'volunteer-shifts' => ['formBuilderAvailable' => $formBuilderAvailable, 'eventsAvailable' => $eventsAvailable],
            'events' => ['eventsAvailable' => $eventsAvailable],
            'stories' => ['blogAvailable' => $blogAvailable],
        ];
    }
}
