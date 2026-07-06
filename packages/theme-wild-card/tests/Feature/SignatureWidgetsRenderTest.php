<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

/*
 * Wave 4b render coverage: each wild-card "arcade cabinet" signature widget
 * renders without error given a minimal sample payload, in both its base
 * view and its declared sidecar variant view (Wave 2.2
 * `<section>--<variant>.blade.php` convention, resolved by
 * VariantViewSectionRenderer per section['variant']).
 *
 * The `capell-theme-wild-card::` view namespace is normally registered by
 * WildCardThemeServiceProvider::boot() once the package is marked installed;
 * this suite renders views directly without booting the full theme
 * registry, so it registers the namespace itself.
 */
beforeEach(function (): void {
    View::addNamespace('capell-theme-wild-card', dirname(__DIR__, 2) . '/resources/views');
});

it('renders card-shuffle-grid with deterministic, page-seeded card order capped at twenty items', function (): void {
    $items = collect(range(1, 25))->map(static fn (int $index): array => [
        'id' => "card-{$index}",
        'title' => "Project {$index}",
        'summary' => "Summary {$index}",
        'meta' => "Studio {$index}",
    ])->all();

    $firstRender = view('capell-theme-wild-card::sections.card-shuffle-grid', [
        'section' => ['heading' => 'The deck', 'items' => $items, 'pageSeed' => 'theme-wild-card'],
    ])->render();

    $secondRender = view('capell-theme-wild-card::sections.card-shuffle-grid', [
        'section' => ['heading' => 'The deck', 'items' => $items, 'pageSeed' => 'theme-wild-card'],
    ])->render();

    expect($firstRender)->toBe($secondRender)
        ->and($firstRender)->toContain('data-shuffle-seed="theme-wild-card"')
        ->and($firstRender)->toContain('Project 1')
        ->and($firstRender)->not->toContain('Project 21');

    $differentSeed = view('capell-theme-wild-card::sections.card-shuffle-grid', [
        'section' => ['heading' => 'The deck', 'items' => $items, 'pageSeed' => 'theme-wild-card-detail'],
    ])->render();

    expect($differentSeed)->not->toBe($firstRender);

    $compact = view('capell-theme-wild-card::sections.card-shuffle-grid--compact', [
        'section' => ['heading' => 'The deck', 'items' => $items, 'pageSeed' => 'theme-wild-card'],
    ])->render();

    expect($compact)->toContain('exd-shuffle-deck-list');
});

it('renders metadata-facet-wall with checkbox facet options capped at fifty chips per group', function (): void {
    $chips = collect(range(1, 55))->map(static fn (int $index): array => ['label' => "Chip {$index}", 'count' => $index])->all();

    $html = view('capell-theme-wild-card::sections.metadata-facet-wall', [
        'section' => ['heading' => 'Filter the index', 'items' => [['title' => 'Medium', 'chips' => $chips]]],
    ])->render();

    expect($html)->toContain('data-metadata-facet-wall')
        ->and($html)->toContain('type="checkbox"')
        ->and($html)->toContain('Chip 1')
        ->and($html)->not->toContain('Chip 51');

    $dense = view('capell-theme-wild-card::sections.metadata-facet-wall--dense', [
        'section' => ['heading' => 'Filter the index', 'items' => [['title' => 'Medium', 'chips' => $chips]]],
    ])->render();

    expect($dense)->toContain('exd-facet-wall-dense');
});

it('renders featured-today-banner with a data-deadline countdown for the next rotation', function (): void {
    $html = view('capell-theme-wild-card::sections.featured-today-banner', [
        'section' => [
            'heading' => 'Featured today',
            'pick' => ['title' => 'Tidal Atlas', 'summary' => 'A coastal identity system.'],
            'nextRotationAt' => '2026-07-06T12:00:00+00:00',
        ],
    ])->render();

    expect($html)->toContain('data-deadline="2026-07-06T12:00:00+00:00"')
        ->and($html)->toContain('Tidal Atlas')
        ->and($html)->toContain('data-deadline-value');

    $split = view('capell-theme-wild-card::sections.featured-today-banner--split', [
        'section' => [
            'heading' => 'Featured today',
            'pick' => ['title' => 'Tidal Atlas', 'summary' => 'A coastal identity system.'],
            'upNext' => [['title' => 'Tidal Atlas'], ['title' => 'Northwind Type', 'meta' => 'Typeface']],
            'nextRotationAt' => '2026-07-06T12:00:00+00:00',
        ],
    ])->render();

    expect($split)->toContain('exd-featured-banner-split')
        ->and($split)->toContain('Northwind Type');
});

it('renders winners-ledger-table using the shared responsive table-to-cards primitive, capped at one hundred rows', function (): void {
    $items = collect(range(1, 105))->map(static fn (int $index): array => [
        'title' => "Winner {$index}",
        'studio' => "Studio {$index}",
        'category' => 'Brand',
        'cycle' => '2025',
    ])->all();

    $html = view('capell-theme-wild-card::sections.winners-ledger-table', [
        'section' => ['heading' => 'Winners', 'items' => $items],
    ])->render();

    expect($html)->toContain('Winner 1')
        ->and($html)->not->toContain('Winner 101')
        ->and($html)->toContain('<table');

    $collections = view('capell-theme-wild-card::sections.winners-ledger-table--collections', [
        'section' => ['heading' => 'Winners', 'items' => $items],
    ])->render();

    expect($collections)->toContain('exd-winners-ledger-collections');
});

it('renders submission-pulse as a historical CSS chart, not a live-polled counter', function (): void {
    $html = view('capell-theme-wild-card::sections.submission-pulse', [
        'section' => [
            'heading' => 'Submission pulse',
            'stats' => [['value' => '12', 'label' => 'Last 24 hours']],
            'bars' => [['label' => 'Mon', 'value' => 6], ['label' => 'Tue', 'value' => 12]],
        ],
    ])->render();

    expect($html)->toContain('data-submission-pulse')
        ->and($html)->toContain('--exd-pulse-bar-height: 50%')
        ->and($html)->toContain('--exd-pulse-bar-height: 100%')
        ->and($html)->not->toContain('refreshInterval')
        ->and($html)->not->toContain('wire:poll');

    $compact = view('capell-theme-wild-card::sections.submission-pulse--compact', [
        'section' => ['heading' => 'Submission pulse', 'stats' => [['value' => '12', 'label' => 'Last 24 hours']]],
    ])->render();

    expect($compact)->toContain('exd-pulse-stat-row-compact');
});

it('renders infinite-scroll-depth-pressure as a static grid with no pagination logic, capped at fifty items', function (): void {
    $items = collect(range(1, 55))->map(static fn (int $index): array => ['title' => "Entry {$index}", 'summary' => "Summary {$index}"])->all();

    $html = view('capell-theme-wild-card::sections.infinite-scroll-depth-pressure', [
        'section' => ['heading' => 'Deeper into the archive', 'items' => $items],
    ])->render();

    expect($html)->toContain('data-infinite-scroll-depth-pressure')
        ->and($html)->toContain('Entry 1')
        ->and($html)->not->toContain('Entry 51')
        ->and($html)->not->toContain('IntersectionObserver');

    $rows = view('capell-theme-wild-card::sections.infinite-scroll-depth-pressure--rows', [
        'section' => ['heading' => 'Deeper into the archive', 'items' => $items],
    ])->render();

    expect($rows)->toContain('exd-depth-rows');
});

it('renders time-capsule-browser as cartridges with :has()-expandable previews, capped at twenty cycles and five entries', function (): void {
    $cycles = collect(range(1, 25))->map(static fn (int $index): array => [
        'title' => "Cycle {$index}",
        'summary' => "Summary {$index}",
        'previewItems' => collect(range(1, 7))->map(static fn (int $entry): array => ['title' => "Cycle {$index} entry {$entry}"])->all(),
    ])->all();

    $html = view('capell-theme-wild-card::sections.time-capsule-browser', [
        'section' => ['heading' => 'Past cycles', 'items' => $cycles],
    ])->render();

    expect($html)->toContain('data-time-capsule-browser')
        ->and($html)->toContain('type="radio"')
        ->and($html)->toContain('Cycle 1')
        ->and($html)->not->toContain('Cycle 21')
        ->and($html)->toContain('Cycle 1 entry 5')
        ->and($html)->not->toContain('Cycle 1 entry 6')
        ->and($html)->not->toContain('IntersectionObserver')
        ->and($html)->not->toContain('wire:poll');

    $cabinet = view('capell-theme-wild-card::sections.time-capsule-browser--cabinet', [
        'section' => ['heading' => 'Past cycles', 'items' => $cycles],
    ])->render();

    expect($cabinet)->toContain('exd-time-capsule-cabinet');
});
