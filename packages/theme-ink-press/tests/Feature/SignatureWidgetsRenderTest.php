<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

/*
 * Wave 4a render coverage: each ink-press "live-velocity journalism"
 * signature widget renders without error given a minimal sample payload, in
 * both its base view and its declared sidecar variant view (Wave 2.2
 * `<section>--<variant>.blade.php` convention, resolved by
 * VariantViewSectionRenderer per section['variant']).
 *
 * The `capell-theme-ink-press::` view namespace is normally registered by
 * InkPressThemeServiceProvider::boot() once the package is marked installed;
 * this suite renders views directly without booting the full theme
 * registry, so it registers the namespace itself.
 */
beforeEach(function (): void {
    View::addNamespace('capell-theme-ink-press', dirname(__DIR__, 2) . '/resources/views');
});

it('renders breaking-news-ribbon only when the editorial state is active', function (): void {
    $hidden = view('capell-theme-ink-press::sections.breaking-news-ribbon', [
        'section' => ['state' => 'hidden', 'heading' => 'Quiet day at the desk'],
    ])->render();

    expect($hidden)->not->toContain('Quiet day at the desk');

    $active = view('capell-theme-ink-press::sections.breaking-news-ribbon', [
        'section' => ['state' => 'active', 'heading' => 'Government resigns overnight', 'url' => '#top-stories'],
    ])->render();

    expect($active)->toContain('Government resigns overnight')
        ->and($active)->toContain('data-state="active"');

    $compact = view('capell-theme-ink-press::sections.breaking-news-ribbon--compact', [
        'section' => ['state' => 'active', 'heading' => 'Government resigns overnight'],
    ])->render();

    expect($compact)->toContain('Government resigns overnight')
        ->and($compact)->toContain('dnews-ribbon-compact');
});

it('renders live-event-timeline with a scroll-progress rail and capped dispatch list', function (): void {
    $items = collect(range(1, 60))->map(static fn (int $index): array => [
        'title' => "Dispatch {$index}",
        'summary' => "Summary {$index}",
        'meta' => "12:{$index}",
    ])->all();

    $html = view('capell-theme-ink-press::sections.live-event-timeline', [
        'section' => ['heading' => 'Live: the story develops', 'items' => $items],
    ])->render();

    expect($html)->toContain('data-scroll-progress')
        ->and($html)->toContain('Dispatch 1')
        ->and($html)->not->toContain('Dispatch 51');

    $compact = view('capell-theme-ink-press::sections.live-event-timeline--compact', [
        'section' => ['heading' => 'Live: the story develops', 'items' => $items],
    ])->render();

    expect($compact)->toContain('dnews-timeline-compact');
});

it('renders reading-progress-with-markers with scroll-spy markup', function (): void {
    $html = view('capell-theme-ink-press::sections.reading-progress-with-markers', [
        'section' => ['markers' => [['label' => 'Live coverage', 'anchor' => 'live-event-timeline']]],
    ])->render();

    expect($html)->toContain('data-scroll-spy')
        ->and($html)->toContain('Live coverage');

    $minimal = view('capell-theme-ink-press::sections.reading-progress-with-markers--minimal', [
        'section' => ['markers' => [['label' => 'Live coverage', 'anchor' => 'live-event-timeline']]],
    ])->render();

    expect($minimal)->toContain('dnews-reading-progress-minimal');
});

it('renders news-web-topology with deterministic relevance-derived custom properties, capped at twenty satellites', function (): void {
    $satellites = collect(range(1, 25))->map(static fn (int $index): array => [
        'title' => "Related story {$index}",
        'relevance' => 100 - $index,
    ])->all();

    $html = view('capell-theme-ink-press::sections.news-web-topology', [
        'section' => [
            'center' => ['title' => 'Lead story', 'summary' => 'Summary'],
            'satellites' => $satellites,
        ],
    ])->render();

    expect($html)->toContain('--dnews-topology-relevance: 99')
        ->and($html)->toContain('Related story 1')
        ->and($html)->not->toContain('Related story 21')
        ->and($html)->toContain('dnews-topology-fallback-list');

    $list = view('capell-theme-ink-press::sections.news-web-topology--list', [
        'section' => [
            'center' => ['title' => 'Lead story', 'summary' => 'Summary'],
            'satellites' => $satellites,
        ],
    ])->render();

    expect($list)->toContain('dnews-topology-fallback-list-always');
});

it('renders author-credibility-inline with credentials and a target-linkable id', function (): void {
    $html = view('capell-theme-ink-press::sections.author-credibility-inline', [
        'section' => [
            'name' => 'Priya Nair',
            'role' => 'Political correspondent',
            'credentials' => ['Reporting since 2014'],
        ],
    ])->render();

    expect($html)->toContain('id="author-credibility-inline"')
        ->and($html)->toContain('Priya Nair')
        ->and($html)->toContain('Reporting since 2014');

    $byline = view('capell-theme-ink-press::sections.author-credibility-inline--byline', [
        'section' => ['name' => 'Priya Nair', 'role' => 'Political correspondent'],
    ])->render();

    expect($byline)->toContain('dnews-credibility-byline');
});

it('renders opinion-grid-with-bylines with byline metadata, capped at fifty items', function (): void {
    $items = collect(range(1, 55))->map(static fn (int $index): array => [
        'title' => "Opinion {$index}",
        'byline' => "Author {$index}",
    ])->all();

    $html = view('capell-theme-ink-press::sections.opinion-grid-with-bylines', [
        'section' => ['heading' => 'Beyond the headlines', 'items' => $items],
    ])->render();

    expect($html)->toContain('Author 1')
        ->and($html)->not->toContain('Author 51');

    $compact = view('capell-theme-ink-press::sections.opinion-grid-with-bylines--compact', [
        'section' => ['heading' => 'Beyond the headlines', 'items' => $items],
    ])->render();

    expect($compact)->toContain('dnews-opinion-compact-list');
});
