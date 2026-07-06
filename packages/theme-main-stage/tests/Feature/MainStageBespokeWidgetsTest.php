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
use Capell\ThemeStudio\MainStage\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\MainStage\MainStageThemeServiceProvider;
use Capell\ThemeStudio\MainStage\Support\Demo\MainStageDemoContent;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

use function Pest\Laravel\get;

/*
|--------------------------------------------------------------------------
| Main Stage bespoke widgets, header/footer chrome seam
|--------------------------------------------------------------------------
|
| Mirrors NightShiftBespokeWidgetsTest exactly. This file does NOT call
| layoutNativeDisableThemeChrome() — the whole point here is to exercise the
| real header/footer chrome path (MainStageThemeInterceptor's
| meta.header_file / meta.footer_file defaults, consumed by
| x-capell::layout.index's <x-dynamic-component> fallback).
|
*/

function bootMainStageThemeForBespokeWidgetTests(): void
{
    CapellCore::forcePackageInstalled(MainStageThemeServiceProvider::$packageName);

    View::addNamespace('capell-theme-main-stage', dirname(__DIR__, 2) . '/resources/views');
    Lang::addNamespace('capell-theme-main-stage', dirname(__DIR__, 2) . '/resources/lang');

    $registry = resolve(ThemeRegistry::class);
    $provider = new MainStageThemeServiceProvider(app());
    $provider->register();
    $provider->boot($registry);
}

/**
 * @param  list<array<string, mixed>>  $sections
 * @return array<string, mixed>
 */
function mainStageSectionOfType(array $sections, string $type): array
{
    $section = collect($sections)->firstWhere('type', $type);

    throw_unless(is_array($section), RuntimeException::class, sprintf('Expected a [%s] section in the demo copy under test.', $type));

    return $section;
}

it('sets header_file and footer_file defaults on a seeded Main Stage Theme via MainStageThemeInterceptor', function (): void {
    bootMainStageThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Main Stage Chrome Test'],
            languageCodes: ['en'],
            baseUrl: 'https://main-stage.chrome-test.test',
        ),
        themeKey: MainStageThemeServiceProvider::THEME_KEY,
        themeName: 'Main Stage',
        contentProvider: new MainStageDemoContent,
    );

    $theme = Theme::query()->where('key', MainStageThemeServiceProvider::THEME_KEY)->firstOrFail();

    expect($theme->meta['header_file'] ?? null)->toBe('capell-theme-main-stage::header.index')
        ->and($theme->meta['footer_file'] ?? null)->toBe('capell-theme-main-stage::footer');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('renders real header and footer chrome on the seeded homepage through the header_file/footer_file seam', function (): void {
    bootMainStageThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Main Stage Chrome Render Test'],
            languageCodes: ['en'],
            baseUrl: 'https://main-stage.chrome-render-test.test',
        ),
        themeKey: MainStageThemeServiceProvider::THEME_KEY,
        themeName: 'Main Stage',
        contentProvider: new MainStageDemoContent,
    );

    $homepage = Page::query()
        ->where('meta->theme_demo->theme_key', MainStageThemeServiceProvider::THEME_KEY)
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
        ->toContain('Main Stage Chrome Render Test')
        ->not->toContain('capell-app/theme-main-stage')
        ->not->toContain('authoring');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('renders each bespoke widget view with real seeded copy and expected content fragments', function (): void {
    bootMainStageThemeForBespokeWidgetTests();

    $demoContent = new MainStageDemoContent;
    $homepageCopy = $demoContent->sectionCopy('homepage');

    $agendaSection = mainStageSectionOfType($homepageCopy, 'agenda-grid-days-tracks-rooms');
    $speakerSection = mainStageSectionOfType($homepageCopy, 'speaker-wall-hover-bios');
    $ticketSection = mainStageSectionOfType($homepageCopy, 'ticket-tier-comparison');
    $countdownSection = mainStageSectionOfType($homepageCopy, 'countdown-band');
    $venueSection = mainStageSectionOfType($homepageCopy, 'venue-travel-panels');
    $sponsorSection = mainStageSectionOfType($homepageCopy, 'sponsor-tier-walls');
    $liveStateSection = mainStageSectionOfType($homepageCopy, 'live-now-replay-state');
    $archiveSection = mainStageSectionOfType($homepageCopy, 'past-editions-archive');

    $widgetCreator = resolve(WidgetCreator::class);

    $agendaWidget = $widgetCreator->bespokeContentWidget('main-stage-agenda-render-test-1', 'Agenda render test', WidgetComponentEnum::AgendaGrid->value, $agendaSection);
    $speakerWidget = $widgetCreator->bespokeContentWidget('main-stage-speaker-render-test-1', 'Speaker render test', WidgetComponentEnum::SpeakerWall->value, $speakerSection);
    $ticketWidget = $widgetCreator->bespokeContentWidget('main-stage-ticket-render-test-1', 'Ticket render test', WidgetComponentEnum::TicketTierComparison->value, $ticketSection);
    $countdownWidget = $widgetCreator->bespokeContentWidget('main-stage-countdown-render-test-1', 'Countdown render test', WidgetComponentEnum::CountdownBand->value, $countdownSection);
    $venueWidget = $widgetCreator->bespokeContentWidget('main-stage-venue-render-test-1', 'Venue render test', WidgetComponentEnum::VenueTravelPanels->value, $venueSection);
    $sponsorWidget = $widgetCreator->bespokeContentWidget('main-stage-sponsor-render-test-1', 'Sponsor render test', WidgetComponentEnum::SponsorTierWalls->value, $sponsorSection);
    $liveStateWidget = $widgetCreator->bespokeContentWidget('main-stage-live-state-render-test-1', 'Live state render test', WidgetComponentEnum::LiveNowReplayState->value, $liveStateSection);
    $archiveWidget = $widgetCreator->bespokeContentWidget('main-stage-archive-render-test-1', 'Archive render test', WidgetComponentEnum::PastEditionsArchive->value, $archiveSection);

    $agendaHtml = view('capell-theme-main-stage::widget.agenda-grid-days-tracks-rooms', ['widget' => $agendaWidget])->render();
    $speakerHtml = view('capell-theme-main-stage::widget.speaker-wall-hover-bios', ['widget' => $speakerWidget])->render();
    $ticketHtml = view('capell-theme-main-stage::widget.ticket-tier-comparison', ['widget' => $ticketWidget])->render();
    $countdownHtml = view('capell-theme-main-stage::widget.countdown-band', ['widget' => $countdownWidget])->render();
    $venueHtml = view('capell-theme-main-stage::widget.venue-travel-panels', ['widget' => $venueWidget])->render();
    $sponsorHtml = view('capell-theme-main-stage::widget.sponsor-tier-walls', ['widget' => $sponsorWidget])->render();
    $liveStateHtml = view('capell-theme-main-stage::widget.live-now-replay-state', ['widget' => $liveStateWidget])->render();
    $archiveHtml = view('capell-theme-main-stage::widget.past-editions-archive', ['widget' => $archiveWidget])->render();

    expect($agendaHtml)
        ->toContain('Three days, six tracks, one main stage')
        ->toContain('Opening keynote: Building in public')
        ->toContain('data-starts-at')
        ->not->toContain('capell-app/theme-main-stage');

    expect($speakerHtml)
        ->toContain('Ninety speakers taking the stage')
        ->toContain('Mara Okonkwo')
        ->not->toContain('capell-app/theme-main-stage');

    expect($ticketHtml)
        ->toContain('Pick your ticket tier')
        ->toContain('General')
        ->toContain('VIP')
        ->not->toContain('capell-app/theme-main-stage');

    expect($countdownHtml)
        ->toContain('Early-bird tickets close soon')
        ->toContain('data-deadline')
        ->not->toContain('capell-app/theme-main-stage');

    expect($venueHtml)
        ->toContain('Venue and travel')
        ->toContain('Fábrica do Braço de Prata')
        ->not->toContain('capell-app/theme-main-stage');

    expect($sponsorHtml)
        ->toContain('Thanks to our sponsors')
        ->toContain('Fieldform Systems')
        ->not->toContain('capell-app/theme-main-stage');

    expect($liveStateHtml)
        ->toContain('Doors open October 14')
        ->not->toContain('capell-app/theme-main-stage');

    expect($archiveHtml)
        ->toContain('Past editions')
        ->toContain('Nightcast Summit 2025')
        ->not->toContain('capell-app/theme-main-stage');

    CapellCore::clearPackages();
});

it('creates distinctly-keyed sponsor-tier-walls widgets per surface so copy does not clobber across surfaces', function (): void {
    bootMainStageThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Main Stage Widget Keys Test'],
            languageCodes: ['en'],
            baseUrl: 'https://main-stage.widget-keys-test.test',
        ),
        themeKey: MainStageThemeServiceProvider::THEME_KEY,
        themeName: 'Main Stage',
        contentProvider: new MainStageDemoContent,
    );

    // `homepage` (LayoutEnum::Home) and `contact` (LayoutEnum::System) are
    // used deliberately here rather than `detail`/`empty`/`cta` (all
    // LayoutEnum::Default): every Default-keyed Layout row is eagerly
    // seeded with starter `page-content`-only containers by
    // `Capell\LayoutBuilder\Support\Interceptors\Layouts\DefaultLayoutInterceptor::afterCreatedOrUpdated()`
    // the moment the Layout row is created — before
    // `ThemeDemoPageInstaller::installLayoutContainers()` ever checks
    // `hadContainers`, so every Default-layout surface after the very first
    // page creation already sees `hadContainers === true` and is skipped,
    // regardless of iteration order. `homepage` and `contact` sit on their
    // own dedicated Home/System layout rows respectively, so both reliably
    // get their own bespoke widgets seeded.
    $homepageSponsorTiers = Widget::query()->where('key', 'main-stage-sponsor-tier-walls-homepage-1')->firstOrFail();
    $contactSponsorTiers = Widget::query()->where('key', 'main-stage-sponsor-tier-walls-contact-1')->firstOrFail();

    expect($homepageSponsorTiers->meta['heading'] ?? null)->toBe('Thanks to our sponsors')
        ->and($contactSponsorTiers->meta['heading'] ?? null)->toBe('Sponsor Nightcast Summit')
        ->and($homepageSponsorTiers->getKey())->not->toBe($contactSponsorTiers->getKey());

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('throws a clear error naming the missing key when a widget references an unregistered bespoke component', function (): void {
    bootMainStageThemeForBespokeWidgetTests();

    $registry = resolve(RenderableRegistry::class);

    expect($registry->get('layout-widget', WidgetComponentEnum::CountdownBand->value)->blade)
        ->toBe('capell-theme-main-stage::widget.countdown-band');

    expect(fn () => $registry->get('layout-widget', 'capell.widget.main-stage.does-not-exist'))
        ->toThrow(InvalidArgumentException::class, 'Renderable [capell.widget.main-stage.does-not-exist] of type [layout-widget] is not registered.');

    CapellCore::clearPackages();
});

it('seeds real Layout containers through ThemeDemoPageInstaller', function (): void {
    bootMainStageThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Main Stage Definition Test'],
            languageCodes: ['en'],
            baseUrl: 'https://main-stage.definition-test.test',
        ),
        themeKey: MainStageThemeServiceProvider::THEME_KEY,
        themeName: 'Main Stage',
        contentProvider: new MainStageDemoContent,
    );

    $homepage = Page::query()
        ->where('meta->theme_demo->theme_key', MainStageThemeServiceProvider::THEME_KEY)
        ->where('meta->theme_demo->surface', 'homepage')
        ->firstOrFail();

    $layout = $homepage->layout;

    throw_unless($layout instanceof Layout, RuntimeException::class, 'Expected the seeded homepage to have a Layout.');

    expect($layout->containers['main']['widgets'][0] ?? null)->toBe(['widget_key' => 'page-content', 'occurrence' => 1]);

    $agendaWidget = Widget::query()->where('key', 'main-stage-agenda-grid-days-tracks-rooms-homepage-1')->firstOrFail();

    expect($agendaWidget->component)->toBe('capell.widget.main-stage.agenda-grid-days-tracks-rooms')
        ->and($agendaWidget->meta['heading'] ?? null)->toBe('Three days, six tracks, one main stage');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});
