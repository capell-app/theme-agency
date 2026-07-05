<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Theme;
use Capell\Core\Support\Renderables\RenderableRegistry;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Support\Creator\WidgetCreator;
use Capell\ThemeStudio\NightShift\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\NightShift\NightShiftThemeServiceProvider;
use Capell\ThemeStudio\NightShift\Support\Demo\NightShiftDemoContent;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

use function Pest\Laravel\get;

/*
|--------------------------------------------------------------------------
| Night Shift bespoke widgets, header/footer chrome seam
|--------------------------------------------------------------------------
|
| Mirrors LiquidGlassBespokeWidgetsTest exactly. This file does NOT call
| layoutNativeDisableThemeChrome() — the whole point here is to exercise the
| real header/footer chrome path (NightShiftThemeInterceptor's
| meta.header_file / meta.footer_file defaults, consumed by
| x-capell::layout.index's <x-dynamic-component> fallback).
|
*/

function bootNightShiftThemeForBespokeWidgetTests(): void
{
    CapellCore::forcePackageInstalled(NightShiftThemeServiceProvider::$packageName);

    View::addNamespace('capell-theme-night-shift', dirname(__DIR__, 2) . '/resources/views');
    Lang::addNamespace('capell-theme-night-shift', dirname(__DIR__, 2) . '/resources/lang');

    $registry = resolve(ThemeRegistry::class);
    $provider = new NightShiftThemeServiceProvider(app());
    $provider->register();
    $provider->boot($registry);
}

/**
 * @param  list<array<string, mixed>>  $sections
 * @return array<string, mixed>
 */
function nightShiftSectionOfType(array $sections, string $type): array
{
    $section = collect($sections)->firstWhere('type', $type);

    throw_unless(is_array($section), RuntimeException::class, sprintf('Expected a [%s] section in the demo copy under test.', $type));

    return $section;
}

it('sets header_file and footer_file defaults on a seeded Night Shift Theme via NightShiftThemeInterceptor', function (): void {
    bootNightShiftThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Night Shift Chrome Test'],
            languageCodes: ['en'],
            baseUrl: 'https://night-shift.chrome-test.test',
        ),
        themeKey: NightShiftThemeServiceProvider::THEME_KEY,
        themeName: 'Night Shift',
        contentProvider: new NightShiftDemoContent,
    );

    $theme = Theme::query()->where('key', NightShiftThemeServiceProvider::THEME_KEY)->firstOrFail();

    expect($theme->meta['header_file'] ?? null)->toBe('capell-theme-night-shift::header.index')
        ->and($theme->meta['footer_file'] ?? null)->toBe('capell-theme-night-shift::footer');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('renders real header and footer chrome on the seeded homepage through the header_file/footer_file seam', function (): void {
    bootNightShiftThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Night Shift Chrome Render Test'],
            languageCodes: ['en'],
            baseUrl: 'https://night-shift.chrome-render-test.test',
        ),
        themeKey: NightShiftThemeServiceProvider::THEME_KEY,
        themeName: 'Night Shift',
        contentProvider: new NightShiftDemoContent,
    );

    $homepage = Page::query()
        ->where('meta->theme_demo->theme_key', NightShiftThemeServiceProvider::THEME_KEY)
        ->where('meta->theme_demo->surface', 'homepage')
        ->firstOrFail();

    $homepage->loadMissing(['pageUrl.siteDomain', 'translations']);

    $pageUrl = $homepage->pageUrl;

    throw_unless($pageUrl instanceof PageUrl, RuntimeException::class, 'Expected the seeded homepage to have a PageUrl.');

    $response = get($pageUrl->full_url);

    $response->assertOk();

    $html = $response->getContent();

    expect($html)->toBeString();

    expect($html)
        ->toContain('<header')
        ->toContain('<footer')
        ->toContain('id="main-content"')
        ->toContain('Night Shift Chrome Render Test')
        ->not->toContain('capell-app/theme-night-shift')
        ->not->toContain('authoring');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('renders each bespoke widget view with real seeded copy and expected content fragments', function (): void {
    bootNightShiftThemeForBespokeWidgetTests();

    $demoContent = new NightShiftDemoContent;
    $homepageCopy = $demoContent->sectionCopy('homepage');
    $changelogSection = nightShiftSectionOfType($homepageCopy, 'changelog-integrations');
    $workflowRailsSection = nightShiftSectionOfType($homepageCopy, 'workflow-rails');
    $securityProofSection = nightShiftSectionOfType($homepageCopy, 'security-proof');

    $changelogWidget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'night-shift-changelog-integrations-render-test-1',
        name: 'Changelog render test',
        component: WidgetComponentEnum::ChangelogIntegrations->value,
        meta: $changelogSection,
    );
    $workflowRailsWidget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'night-shift-workflow-rails-render-test-1',
        name: 'Workflow rails render test',
        component: WidgetComponentEnum::WorkflowRails->value,
        meta: $workflowRailsSection,
    );
    $securityProofWidget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'night-shift-security-proof-render-test-1',
        name: 'Security proof render test',
        component: WidgetComponentEnum::SecurityProof->value,
        meta: $securityProofSection,
    );

    $changelogHtml = view('capell-theme-night-shift::widget.changelog-integrations', ['widget' => $changelogWidget])->render();
    $workflowRailsHtml = view('capell-theme-night-shift::widget.workflow-rails', ['widget' => $workflowRailsWidget])->render();
    $securityProofHtml = view('capell-theme-night-shift::widget.security-proof', ['widget' => $securityProofWidget])->render();

    expect($changelogHtml)
        ->toContain('Changelog and integrations, always in sync')
        ->toContain('Linked to your repos')
        ->not->toContain('capell-app/theme-night-shift');

    expect($workflowRailsHtml)
        ->toContain('Workflows that move work without the busywork')
        ->toContain('Triage rail')
        ->not->toContain('capell-app/theme-night-shift');

    expect($securityProofHtml)
        ->toContain('Security and trust, proven not promised')
        ->toContain('SOC 2 Type II')
        ->not->toContain('capell-app/theme-night-shift');

    CapellCore::clearPackages();
});

it('creates distinctly-keyed security-proof widgets per surface so copy does not clobber across surfaces', function (): void {
    bootNightShiftThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Night Shift Widget Keys Test'],
            languageCodes: ['en'],
            baseUrl: 'https://night-shift.widget-keys-test.test',
        ),
        themeKey: NightShiftThemeServiceProvider::THEME_KEY,
        themeName: 'Night Shift',
        contentProvider: new NightShiftDemoContent,
    );

    // `contact` (LayoutEnum::System) is used deliberately here rather than
    // `cta` (LayoutEnum::Default): `ThemeDemoPageInstaller::installLayoutContainers()`
    // resolves `Layout` rows by `layout_key`, which several demo surfaces
    // deliberately share (mirroring LiquidGlassDemoContent's own layout
    // assignments), and only ever writes `containers` once per shared
    // `Layout` row unless `--force` is set — so a later surface sharing an
    // already-seeded `LayoutEnum::Default` row (here, `detail`, which runs
    // first among the default-layout surfaces) never gets its own
    // bespoke widgets seeded. `contact` is the only `LayoutEnum::System`
    // surface that both carries a bespoke `security-proof` section and runs
    // before `not-found` (its only System-layout sibling, which carries no
    // bespoke widgets), so it reliably gets its own widgets seeded.
    $homepageSecurityProof = Widget::query()->where('key', 'night-shift-security-proof-homepage-1')->firstOrFail();
    $contactSecurityProof = Widget::query()->where('key', 'night-shift-security-proof-contact-1')->firstOrFail();

    expect($homepageSecurityProof->meta['heading'] ?? null)->toBe('Security and trust, proven not promised')
        ->and($contactSecurityProof->meta['heading'] ?? null)->toBe('The security questions, answered first')
        ->and($homepageSecurityProof->getKey())->not->toBe($contactSecurityProof->getKey());

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('throws a clear error naming the missing key when a widget references an unregistered bespoke component', function (): void {
    bootNightShiftThemeForBespokeWidgetTests();

    $registry = resolve(RenderableRegistry::class);

    expect($registry->get('layout-widget', WidgetComponentEnum::ChangelogIntegrations->value)->blade)
        ->toBe('capell-theme-night-shift::widget.changelog-integrations');

    expect(fn () => $registry->get('layout-widget', 'capell.widget.night-shift.does-not-exist'))
        ->toThrow(InvalidArgumentException::class, 'Renderable [capell.widget.night-shift.does-not-exist] of type [layout-widget] is not registered.');

    CapellCore::clearPackages();
});

it('seeds real Layout containers and a page-content Widget through ThemeDemoPageInstaller', function (): void {
    bootNightShiftThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Night Shift Definition Test'],
            languageCodes: ['en'],
            baseUrl: 'https://night-shift.definition-test.test',
        ),
        themeKey: NightShiftThemeServiceProvider::THEME_KEY,
        themeName: 'Night Shift',
        contentProvider: new NightShiftDemoContent,
    );

    $homepage = Page::query()
        ->where('meta->theme_demo->theme_key', NightShiftThemeServiceProvider::THEME_KEY)
        ->where('meta->theme_demo->surface', 'homepage')
        ->firstOrFail();

    $layout = $homepage->layout;

    throw_unless($layout instanceof Layout, RuntimeException::class, 'Expected the seeded homepage to have a Layout.');

    expect($layout->containers)->toBe([
        'main' => [
            'widgets' => [
                ['widget_key' => 'page-content', 'occurrence' => 1],
                ['widget_key' => 'night-shift-workflow-rails-homepage-1', 'occurrence' => 1],
                ['widget_key' => 'night-shift-changelog-integrations-homepage-1', 'occurrence' => 1],
                ['widget_key' => 'night-shift-security-proof-homepage-1', 'occurrence' => 1],
            ],
        ],
    ]);

    $widget = Widget::query()->firstWhere('key', 'page-content');

    expect($widget)->toBeInstanceOf(Widget::class);

    $workflowRailsWidget = Widget::query()->where('key', 'night-shift-workflow-rails-homepage-1')->firstOrFail();

    expect($workflowRailsWidget->component)->toBe('capell.widget.night-shift.workflow-rails')
        ->and($workflowRailsWidget->meta['heading'] ?? null)->toBe('Workflows that move work without the busywork');

    $changelogWidget = Widget::query()->where('key', 'night-shift-changelog-integrations-homepage-1')->firstOrFail();

    expect($changelogWidget->component)->toBe('capell.widget.night-shift.changelog-integrations')
        ->and($changelogWidget->meta['heading'] ?? null)->toBe('Changelog and integrations, always in sync');

    $securityProofWidget = Widget::query()->where('key', 'night-shift-security-proof-homepage-1')->firstOrFail();

    expect($securityProofWidget->component)->toBe('capell.widget.night-shift.security-proof')
        ->and($securityProofWidget->meta['heading'] ?? null)->toBe('Security and trust, proven not promised');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('creates a bespoke content widget through WidgetCreator with caller-supplied key, component, and meta', function (): void {
    bootNightShiftThemeForBespokeWidgetTests();

    $widget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'night-shift-changelog-integrations-standalone-test-1',
        name: 'Standalone changelog test',
        component: WidgetComponentEnum::ChangelogIntegrations->value,
        meta: ['heading' => 'Test heading', 'items' => []],
    );

    expect($widget)->toBeInstanceOf(Widget::class)
        ->and($widget->key)->toBe('night-shift-changelog-integrations-standalone-test-1')
        ->and($widget->component)->toBe(WidgetComponentEnum::ChangelogIntegrations->value)
        ->and($widget->meta['heading'] ?? null)->toBe('Test heading');

    CapellCore::clearPackages();
});
