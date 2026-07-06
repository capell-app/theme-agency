<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CallOut;

use Capell\Core\Data\RenderableDefinitionData;
use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Theme;
use Capell\Core\Support\Renderables\RenderableRegistry;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Support\Editor\StandardThemeEditorSchema;
use Capell\FoundationTheme\Support\Providers\RegistersLayoutNativeThemeDefaults;
use Capell\ThemeStudio\CallOut\Console\Commands\CallOutDemoCommand;
use Capell\ThemeStudio\CallOut\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\CallOut\Support\Interceptors\Themes\CallOutThemeInterceptor;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Override;

/**
 * Definition-only, layout-native theme provider for Call Out — the
 * service-business (quote-led trades and local services) vertical theme,
 * modelled on `Capell\ThemeStudio\NightShift\NightShiftThemeServiceProvider`.
 * Registers a `ThemeDefinitionData`, the shared `header`/`footer` layout
 * areas, and eight bespoke `capell.widget.call-out.*` widgets (Part 2 §E).
 * It registers no `ThemeRenderer` or section renderers, so every public
 * surface renders through the shared `x-capell::layout` + layout-builder
 * container pipeline instead of a theme-owned page shell.
 *
 * Two Theme Studio presets (Wave 5 spec): `call-out` (high-vis accent, bold
 * headings, bordered cards, light surface) and `after-hours` (dark dispatch
 * console — the "closed"/emergency-hours counterpart). Layout-native themes
 * have no `VariantViewSectionRenderer` sidecar-view seam (see
 * `ArtPaperThemeServiceProvider::sectionRenderers()` for that classic-pattern
 * mechanism, which does not apply here), so each signature widget declares
 * its >= 2 variants by branching on a payload `variant` key instead — see
 * each `resources/views/widget/*.blade.php` file's own docblock for its
 * specific variant pair, mirroring `LiquidGlassThemeServiceProvider`'s
 * documented approach for the same constraint.
 */
final class CallOutThemeServiceProvider extends ServiceProvider
{
    use RegistersLayoutNativeThemeDefaults;

    public const string THEME_KEY = 'call-out';

    public static string $packageName = 'capell-app/theme-call-out';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Call Out',
            description: 'Bold, high-visibility conversion pages for quote-led trades and local-service businesses — plumbers, electricians, clinics, and salons. State-driven emergency-availability urgency, before/after proof, and a clear numbered path to a quote.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/call-out.jpg',
            tags: ['Trades', 'Local Services', 'Service Business', 'Conversion', 'Emergency Callout'],
            bestFit: ['Plumbers and electricians', 'Local clinics and salons', 'Home-service contractors', 'Emergency call-out trades', 'Field-service businesses'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Call Out',
                    description: 'Call Out visual preset: a high-visibility amber accent on a clean white surface, bold headings, and bordered cards — built for quote-led trades and local-service conversion.',
                    previewImage: '/vendor/capell/themes/call-out.jpg',
                    values: [
                        'primaryColor' => '#1c2530',
                        'accentColor' => '#d97706',
                        'neutralColor' => '#1c2530',
                        'surfaceColor' => '#ffffff',
                        'foregroundColor' => '#1c2530',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'natural',
                        'radius' => 'md',
                        'headingScale' => 'bold',
                        'cardDensity' => 'comfortable',
                    ],
                ),
                new ThemePresetData(
                    key: 'after-hours',
                    name: 'After Hours',
                    description: 'A dark "dispatch console" counterpart for emergency/after-hours call-out branding — near-black surface, the same amber accent turned into a glowing beacon, bordered cards.',
                    previewImage: '/vendor/capell/themes/call-out.jpg',
                    values: [
                        'primaryColor' => '#f4f1ea',
                        'accentColor' => '#f59e0b',
                        'neutralColor' => '#11151b',
                        'surfaceColor' => '#0d1117',
                        'foregroundColor' => '#f4f1ea',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'natural',
                        'radius' => 'md',
                        'headingScale' => 'bold',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/call-out.css'],
            runtime: FrontendRuntime::Blade,
            extends: 'default',
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
        );
    }

    #[Override]
    public function register(): void {}

    public function boot(ThemeRegistry $registry): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([CallOutDemoCommand::class]);
        }

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-call-out');

        // See RegistersLayoutNativeThemeDefaults::registerThemeViewNamespace()'s
        // docblock for why this theme needs both a plain view namespace AND
        // an anonymous-component namespace registered for the same views —
        // mirrors NightShiftThemeServiceProvider's identical registration.
        $this->registerThemeViewNamespace('capell-theme-call-out', __DIR__ . '/../resources/views');

        // Class-based Blade components (QuoteRequestPath, BlogTipsRail) live
        // under their own PHP namespace and need a *component* namespace
        // registration (resolves `<x-capell-theme-call-out::quote-request-path>`
        // to `Capell\ThemeStudio\CallOut\View\Components\QuoteRequestPath`),
        // distinct from the anonymous-view-component namespace above —
        // mirrors FoundationThemeServiceProvider's
        // `Blade::componentNamespace('Capell\FoundationTheme\View\Components', 'capell-theme-foundation')`
        // registration for the same reason.
        Blade::componentNamespace('Capell\\ThemeStudio\\CallOut\\View\\Components', 'capell-theme-call-out');

        $this->registerVendorCssAssets();
        $this->registerLayoutAreas();
        $this->registerBespokeWidgetRenderables();
        $this->registerModelInterceptors();

        // Definition-only registration: this theme ships no ThemeRenderer or
        // section renderers. Public pages render through the shared
        // `x-capell::layout` + layout-builder container pipeline instead of a
        // theme-owned page shell.
        $registry->register(definition: self::definition());
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-call-out.css',
            packageName: self::$packageName,
            condition: 'theme-css:call-out',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * Registers this theme's own layout-builder areas: `header` and
     * `footer`, via `RegistersLayoutNativeThemeDefaults::registerStandardLayoutAreas()`.
     * Call Out renders through Foundation's own shared header/footer chrome
     * — no bespoke chrome views of its own, unlike Night Shift; its
     * differentiation lives in the eight bespoke widgets and the
     * state-driven urgency mechanic instead. `registerModelInterceptors()`
     * still points `header_file`/`footer_file` at Foundation's default
     * components explicitly, since `x-capell::layout.index`'s fallback to
     * those defaults only fires when a freshly-seeded layout-native Theme's
     * `header`/`footer` layout meta is null, which is not guaranteed
     * without this seam (see `CallOutThemeInterceptor`).
     */
    private function registerLayoutAreas(): void
    {
        $this->registerStandardLayoutAreas();
    }

    /**
     * Sets `header_file`/`footer_file` on this theme's `Theme` row so public
     * pages render Foundation's default header/footer chrome, mirroring
     * `NightShiftThemeServiceProvider::registerModelInterceptors()`.
     */
    private function registerModelInterceptors(): void
    {
        CapellCore::registerModelInterceptor(Theme::class, interceptorClass: CallOutThemeInterceptor::class, key: self::THEME_KEY);
    }

    /**
     * Registers Call Out's eight bespoke layout-builder widget component
     * keys (`capell.widget.call-out.*`) against the shared
     * `RenderableRegistry`, mirroring
     * `NightShiftThemeServiceProvider::registerBespokeWidgetRenderables()`.
     *
     * Defensive registration, NOT graceful degradation at render time: each
     * blade target is only registered if `view()->exists()` for it.
     */
    private function registerBespokeWidgetRenderables(): void
    {
        $registry = resolve(RenderableRegistry::class);

        $blade = [
            WidgetComponentEnum::ServiceAreaMapGrid->value => 'capell-theme-call-out::widget.service-area-map-grid',
            WidgetComponentEnum::BeforeAfterComparison->value => 'capell-theme-call-out::widget.before-after-comparison',
            WidgetComponentEnum::EmergencyAvailabilityBanner->value => 'capell-theme-call-out::widget.emergency-availability-banner',
            WidgetComponentEnum::QuotePathStepper->value => 'capell-theme-call-out::widget.quote-path-stepper',
            WidgetComponentEnum::AccreditationInsuranceStrips->value => 'capell-theme-call-out::widget.accreditation-insurance-strips',
            WidgetComponentEnum::ReviewProofWall->value => 'capell-theme-call-out::widget.review-proof-wall',
            WidgetComponentEnum::PricingGuideTable->value => 'capell-theme-call-out::widget.pricing-guide-table',
            WidgetComponentEnum::TeamOnTheRoadCards->value => 'capell-theme-call-out::widget.team-on-the-road-cards',
        ];

        foreach (WidgetComponentEnum::cases() as $widgetComponent) {
            $bladeView = $blade[$widgetComponent->value];

            if (! view()->exists($bladeView)) {
                continue;
            }

            $registry->register(new RenderableDefinitionData(
                key: $widgetComponent->value,
                type: 'layout-widget',
                blade: $bladeView,
            ));
        }
    }
}
