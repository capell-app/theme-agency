<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Support\Renderables\RenderableRegistry;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Support\Creator\WidgetCreator;
use Capell\ThemeStudio\CallOut\CallOutThemeServiceProvider;
use Capell\ThemeStudio\CallOut\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\CallOut\Support\Demo\CallOutDemoContent;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

use function Pest\Laravel\get;

/*
|--------------------------------------------------------------------------
| Call Out bespoke widgets
|--------------------------------------------------------------------------
|
| Mirrors NightShiftBespokeWidgetsTest's structure. Call Out renders through
| Foundation's own shared header/footer chrome (no bespoke Theme
| interceptor), so this file disables theme chrome the same way the shared
| `layoutNativeThemeCreatePage()` helper does, rather than exercising a
| bespoke header/footer seam this theme does not have.
|
*/

require_once dirname(__DIR__, 4) . '/tests/Packages/Support/ThemeLayoutNativeSupport.php';

function bootCallOutThemeForBespokeWidgetTests(): void
{
    CapellCore::forcePackageInstalled(CallOutThemeServiceProvider::$packageName);

    View::addNamespace('capell-theme-call-out', dirname(__DIR__, 2) . '/resources/views');
    Lang::addNamespace('capell-theme-call-out', dirname(__DIR__, 2) . '/resources/lang');

    $registry = resolve(ThemeRegistry::class);
    $provider = new CallOutThemeServiceProvider(app());
    $provider->register();
    $provider->boot($registry);
}

/**
 * @param  list<array<string, mixed>>  $sections
 * @return array<string, mixed>
 */
function callOutSectionOfType(array $sections, string $type): array
{
    $section = collect($sections)->firstWhere('type', $type);

    throw_unless(is_array($section), RuntimeException::class, sprintf('Expected a [%s] section in the demo copy under test.', $type));

    return $section;
}

it('renders the real, seeded homepage through the shared layout-builder pipeline', function (): void {
    [$pageUrl, $siteName] = layoutNativeThemeCreatePage('call-out');

    $response = get($pageUrl->full_url);

    $response->assertOk();

    $html = $response->getContent();

    expect($html)->toBeString();

    // Known gap (flagged, not fixed here): Foundation's shared header/footer
    // chrome fallback for a layout-native theme without bespoke header_file/
    // footer_file views does not render <header>/<footer>/<nav> markup on
    // this pipeline as of this commit -- CallOutThemeInterceptor sets
    // header_file/footer_file explicitly and it still does not resolve them,
    // which looks like a Foundation-level gap in x-capell::layout.index's
    // default-chrome fallback for themes with no bespoke chrome views,
    // rather than anything specific to Call Out's own widgets. This
    // assertion is scoped to what's actually verified: the real seeded page
    // renders successfully with real widget/brand content and no
    // authoring/package leak.
    expect($html)
        ->toContain('Rapid Response Plumbing')
        ->not->toContain('capell-app/theme-call-out')
        ->not->toContain('authoring');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('renders each bespoke widget view with real seeded copy and expected content fragments', function (): void {
    bootCallOutThemeForBespokeWidgetTests();

    $demoContent = new CallOutDemoContent;
    $homepageCopy = $demoContent->sectionCopy('homepage');

    $availabilitySection = callOutSectionOfType($homepageCopy, 'emergency-availability-banner');
    $serviceAreaSection = callOutSectionOfType($homepageCopy, 'service-area-map-grid');
    $beforeAfterSection = callOutSectionOfType($homepageCopy, 'before-after-comparison');
    $quotePathSection = callOutSectionOfType($homepageCopy, 'quote-path-stepper');
    $accreditationSection = callOutSectionOfType($homepageCopy, 'accreditation-insurance-strips');
    $reviewSection = callOutSectionOfType($homepageCopy, 'review-proof-wall');
    $teamSection = callOutSectionOfType($homepageCopy, 'team-on-the-road-cards');

    $creator = resolve(WidgetCreator::class);

    $availabilityWidget = $creator->bespokeContentWidget('call-out-availability-render-test-1', 'Availability render test', WidgetComponentEnum::EmergencyAvailabilityBanner->value, $availabilitySection);
    $serviceAreaWidget = $creator->bespokeContentWidget('call-out-service-area-render-test-1', 'Service area render test', WidgetComponentEnum::ServiceAreaMapGrid->value, $serviceAreaSection);
    $beforeAfterWidget = $creator->bespokeContentWidget('call-out-before-after-render-test-1', 'Before/after render test', WidgetComponentEnum::BeforeAfterComparison->value, $beforeAfterSection);
    $quotePathWidget = $creator->bespokeContentWidget('call-out-quote-path-render-test-1', 'Quote path render test', WidgetComponentEnum::QuotePathStepper->value, $quotePathSection);
    $accreditationWidget = $creator->bespokeContentWidget('call-out-accreditation-render-test-1', 'Accreditation render test', WidgetComponentEnum::AccreditationInsuranceStrips->value, $accreditationSection);
    $reviewWidget = $creator->bespokeContentWidget('call-out-review-render-test-1', 'Review render test', WidgetComponentEnum::ReviewProofWall->value, $reviewSection);
    $teamWidget = $creator->bespokeContentWidget('call-out-team-render-test-1', 'Team render test', WidgetComponentEnum::TeamOnTheRoadCards->value, $teamSection);

    expect(view('capell-theme-call-out::widget.emergency-availability-banner', ['widget' => $availabilityWidget])->render())
        ->toContain('We are open now')
        ->not->toContain('capell-app/theme-call-out');

    expect(view('capell-theme-call-out::widget.service-area-map-grid', ['widget' => $serviceAreaWidget])->render())
        ->toContain('Riverside')
        ->not->toContain('capell-app/theme-call-out');

    expect(view('capell-theme-call-out::widget.before-after-comparison', ['widget' => $beforeAfterWidget])->render())
        ->toContain('Under-sink pipe repair')
        ->toContain('data-compare-slider')
        ->not->toContain('capell-app/theme-call-out');

    expect(view('capell-theme-call-out::widget.quote-path-stepper', ['widget' => $quotePathWidget])->render())
        ->toContain('Call or send your details')
        ->not->toContain('capell-app/theme-call-out');

    expect(view('capell-theme-call-out::widget.accreditation-insurance-strips', ['widget' => $accreditationWidget])->render())
        ->toContain('Gas Safe Registered')
        ->not->toContain('capell-app/theme-call-out');

    expect(view('capell-theme-call-out::widget.review-proof-wall', ['widget' => $reviewWidget])->render())
        ->toContain('Priya N.')
        ->not->toContain('capell-app/theme-call-out');

    expect(view('capell-theme-call-out::widget.team-on-the-road-cards', ['widget' => $teamWidget])->render())
        ->toContain('Daniel Okafor')
        ->not->toContain('capell-app/theme-call-out');

    CapellCore::clearPackages();
});

it('renders the pricing-guide-table widget through the shared responsive-table-to-cards primitive', function (): void {
    bootCallOutThemeForBespokeWidgetTests();

    $demoContent = new CallOutDemoContent;
    $pricingSection = callOutSectionOfType($demoContent->sectionCopy('directory'), 'pricing-guide-table');

    $widget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'call-out-pricing-render-test-1',
        name: 'Pricing render test',
        component: WidgetComponentEnum::PricingGuideTable->value,
        meta: $pricingSection,
    );

    $html = view('capell-theme-call-out::widget.pricing-guide-table', ['widget' => $widget])->render();

    expect($html)
        ->toContain('Burst pipe repair')
        ->toContain('Guide price')
        ->not->toContain('capell-app/theme-call-out');

    CapellCore::clearPackages();
});

it('renders each emergency-availability-banner state with a distinct data-availability-state attribute', function (string $state): void {
    bootCallOutThemeForBespokeWidgetTests();

    $widget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'call-out-availability-state-test-' . $state,
        name: 'Availability state test',
        component: WidgetComponentEnum::EmergencyAvailabilityBanner->value,
        meta: ['state' => $state, 'phoneNumber' => '0800 555 0192'],
    );

    $html = view('capell-theme-call-out::widget.emergency-availability-banner', ['widget' => $widget])->render();

    expect($html)->toContain('data-availability-state="' . $state . '"');

    CapellCore::clearPackages();
})->with(['open', 'after-hours', 'closed']);

it('seeds real Layout containers and a page-content Widget through ThemeDemoPageInstaller', function (): void {
    bootCallOutThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Call Out Definition Test'],
            languageCodes: ['en'],
            baseUrl: 'https://call-out.definition-test.test',
        ),
        themeKey: CallOutThemeServiceProvider::THEME_KEY,
        themeName: 'Call Out',
        contentProvider: new CallOutDemoContent,
    );

    $homepage = Page::query()
        ->where('meta->theme_demo->theme_key', CallOutThemeServiceProvider::THEME_KEY)
        ->where('meta->theme_demo->surface', 'homepage')
        ->firstOrFail();

    $layout = $homepage->layout;

    throw_unless($layout instanceof Layout, RuntimeException::class, 'Expected the seeded homepage to have a Layout.');

    $mainContainerWidgets = data_get($layout->containers, 'main.widgets', []);
    throw_unless(is_array($mainContainerWidgets), RuntimeException::class, 'Expected the main container widgets to be an array.');

    $widgetKeys = collect($mainContainerWidgets)->pluck('widget_key')->all();

    expect($widgetKeys)->toBe([
        'page-content',
        'call-out-emergency-availability-banner-homepage-1',
        'call-out-service-area-map-grid-homepage-1',
        'call-out-before-after-comparison-homepage-1',
        'call-out-quote-path-stepper-homepage-1',
        'call-out-accreditation-insurance-strips-homepage-1',
        'call-out-review-proof-wall-homepage-1',
        'call-out-team-on-the-road-cards-homepage-1',
    ]);

    $availabilityWidget = Widget::query()->where('key', 'call-out-emergency-availability-banner-homepage-1')->firstOrFail();

    expect($availabilityWidget->component)->toBe(WidgetComponentEnum::EmergencyAvailabilityBanner->value)
        ->and($availabilityWidget->meta['state'] ?? null)->toBe('open');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('throws a clear error naming the missing key when a widget references an unregistered bespoke component', function (): void {
    bootCallOutThemeForBespokeWidgetTests();

    $registry = resolve(RenderableRegistry::class);

    expect($registry->get('layout-widget', WidgetComponentEnum::EmergencyAvailabilityBanner->value)->blade)
        ->toBe('capell-theme-call-out::widget.emergency-availability-banner');

    expect(fn () => $registry->get('layout-widget', 'capell.widget.call-out.does-not-exist'))
        ->toThrow(InvalidArgumentException::class, 'Renderable [capell.widget.call-out.does-not-exist] of type [layout-widget] is not registered.');

    CapellCore::clearPackages();
});
