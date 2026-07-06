<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

/*
 * Wave 4b render coverage: each gold-rush "assay office" signature widget
 * renders without error given a minimal sample payload, in both its base
 * view and its declared sidecar variant view (Wave 2.2
 * `<section>--<variant>.blade.php` convention, resolved by
 * VariantViewSectionRenderer per section['variant']).
 *
 * The `capell-theme-gold-rush::` view namespace is normally registered by
 * GoldRushThemeServiceProvider::boot() once the package is marked installed;
 * this suite renders views directly without booting the full theme
 * registry, so it registers the namespace itself.
 */
beforeEach(function (): void {
    View::addNamespace('capell-theme-gold-rush', dirname(__DIR__, 2) . '/resources/views');
    Lang::addNamespace('capell-theme-gold-rush', dirname(__DIR__, 2) . '/resources/lang');
});

it('renders score-criteria with weighted criteria in both the bar and compact table variants', function (): void {
    $section = [
        'heading' => 'How every entry is scored',
        'items' => [
            ['title' => 'User interface', 'weight' => 35, 'summary' => 'Visual craft and hierarchy.'],
            ['title' => 'User experience', 'weight' => 30, 'summary' => 'Flow and clarity.'],
        ],
    ];

    $html = view('capell-theme-gold-rush::sections.score-criteria', ['section' => $section])->render();

    expect($html)->toContain('User interface')
        ->and($html)->toContain('35%')
        ->and($html)->toContain('sbs-criteria-bar-fill');

    $compact = view('capell-theme-gold-rush::sections.score-criteria--compact', ['section' => $section])->render();

    expect($compact)->toContain('sbs-criteria-table')
        ->and($compact)->toContain('<table')
        ->and($compact)->toContain('User interface');
});

it('renders voting-status with a server-computed conic-gradient gauge and a data-deadline countdown, never client-polled', function (): void {
    $section = [
        'heading' => 'Today\'s vote is open',
        'deadline' => '2026-07-06T23:59:59+00:00',
        'closed' => false,
        'items' => [
            ['meta' => '2,481 votes', 'title' => 'Meridian Console', 'summary' => 'Leading the public vote.'],
            ['meta' => '1,940 votes', 'title' => 'Harbour Atlas', 'summary' => 'Second on the public vote.'],
        ],
    ];

    $html = view('capell-theme-gold-rush::sections.voting-status', ['section' => $section])->render();

    expect($html)->toContain('sbs-vote-gauge')
        ->and($html)->toContain('--sbs-gauge-gradient:')
        ->and($html)->toContain('data-deadline="2026-07-06T23:59:59+00:00"')
        ->and($html)->toContain('data-deadline-value')
        ->and($html)->not->toContain('wire:poll')
        ->and($html)->not->toContain('refreshInterval')
        ->and($html)->not->toContain('Math.random');

    $compact = view('capell-theme-gold-rush::sections.voting-status--compact', ['section' => $section])->render();

    expect($compact)->toContain('sbs-vote-gauge-small')
        ->and($compact)->toContain('data-deadline="2026-07-06T23:59:59+00:00"');
});

it('renders voting-status as closed with no countdown markup when the payload state is editorially closed', function (): void {
    $section = [
        'heading' => 'Voting has closed',
        'deadline' => '2026-07-06T23:59:59+00:00',
        'closed' => true,
        'items' => [
            ['meta' => '2,481 votes', 'title' => 'Meridian Console', 'summary' => 'Winner of the public vote.'],
        ],
    ];

    $html = view('capell-theme-gold-rush::sections.voting-status', ['section' => $section])->render();

    expect($html)->not->toContain('data-deadline=')
        ->and($html)->toContain('Closed');
});

it('renders newest-nominees on the shared carousel.js data-carousel-* contract, capped at twenty items', function (): void {
    $items = collect(range(1, 25))->map(static fn (int $index): array => [
        'title' => "Nominee {$index}",
        'summary' => "Summary {$index}",
        'meta' => "Category {$index}",
    ])->all();

    $html = view('capell-theme-gold-rush::sections.newest-nominees--carousel', [
        'section' => ['heading' => 'Newest nominees', 'items' => $items],
    ])->render();

    expect($html)->toContain('swiper sbs-nominee-carousel')
        ->and($html)->toContain('data-carousel-id="gold-rush-nominees"')
        ->and($html)->toContain('Nominee 1')
        ->and($html)->not->toContain('Nominee 21');
});

it('renders previous-winners using the shared responsive table-to-cards primitive, capped at one hundred rows', function (): void {
    $items = collect(range(1, 105))->map(static fn (int $index): array => [
        'title' => "Winner {$index} — 9.0",
        'summary' => "Summary {$index}",
    ])->all();

    $html = view('capell-theme-gold-rush::sections.previous-winners--compact', [
        'section' => ['heading' => 'Previous winners', 'items' => $items],
    ])->render();

    expect($html)->toContain('Winner 1')
        ->and($html)->not->toContain('Winner 101')
        ->and($html)->toContain('<table');
});

it('renders nominee-heat-map with deterministic colour intensity from the payload score, capped at fifty cells', function (): void {
    $items = collect(range(1, 55))->map(static fn (int $index): array => [
        'title' => "Nominee {$index}",
        'score' => 7.5,
    ])->all();

    $firstRender = view('capell-theme-gold-rush::sections.nominee-heat-map', [
        'section' => ['heading' => 'Heat map', 'items' => $items],
    ])->render();

    $secondRender = view('capell-theme-gold-rush::sections.nominee-heat-map', [
        'section' => ['heading' => 'Heat map', 'items' => $items],
    ])->render();

    expect($firstRender)->toBe($secondRender)
        ->and($firstRender)->toContain('--sbs-heat-intensity: 0.75')
        ->and($firstRender)->toContain('Nominee 1')
        ->and($firstRender)->not->toContain('Nominee 51');

    $compact = view('capell-theme-gold-rush::sections.nominee-heat-map--compact', [
        'section' => ['heading' => 'Heat map', 'items' => $items],
    ])->render();

    expect($compact)->toContain('sbs-heat-grid-compact');
});

it('renders award-countdown-ticker with a server-rendered deadline target and an editorial closed state', function (): void {
    $open = view('capell-theme-gold-rush::sections.award-countdown-ticker', [
        'section' => ['heading' => 'Round closes soon', 'deadline' => '2026-07-06T18:00:00+00:00', 'closed' => false],
    ])->render();

    expect($open)->toContain('data-deadline="2026-07-06T18:00:00+00:00"')
        ->and($open)->toContain('data-deadline-value')
        ->and($open)->not->toContain('wire:poll');

    $closed = view('capell-theme-gold-rush::sections.award-countdown-ticker', [
        'section' => ['heading' => 'Round closed', 'deadline' => '2026-07-06T18:00:00+00:00', 'closed' => true],
    ])->render();

    expect($closed)->not->toContain('data-deadline=')
        ->and($closed)->toContain('Closed');

    $compact = view('capell-theme-gold-rush::sections.award-countdown-ticker--compact', [
        'section' => ['heading' => 'Round closes soon', 'deadline' => '2026-07-06T18:00:00+00:00', 'closed' => false],
    ])->render();

    expect($compact)->toContain('sbs-countdown-compact-inner');
});

it('renders time-capsule-browser as native details elements with a working non-JS expand/collapse fallback', function (): void {
    $section = [
        'heading' => 'Browse the scoreboard through the years',
        'items' => [
            [
                'title' => '2024',
                'meta' => '412 entries scored',
                'items' => [
                    ['title' => 'Meridian Console', 'meta' => '9.4'],
                    ['title' => 'Lumen Dashboard', 'meta' => '8.9'],
                    ['title' => 'Kindred Lending', 'meta' => '8.7'],
                    ['title' => 'Foundry System', 'meta' => '8.6'],
                    ['title' => 'Studio Verda', 'meta' => '9.3'],
                    ['title' => 'Atlas Festival', 'meta' => '9.1'],
                ],
            ],
        ],
    ];

    $html = view('capell-theme-gold-rush::sections.time-capsule-browser', ['section' => $section])->render();

    expect($html)->toContain('<details')
        ->and($html)->toContain('2024')
        ->and($html)->toContain('Meridian Console')
        ->and($html)->not->toContain('Atlas Festival');

    $compact = view('capell-theme-gold-rush::sections.time-capsule-browser--compact', ['section' => $section])->render();

    expect($compact)->toContain('sbs-capsule-rail-compact')
        ->and($compact)->toContain('Meridian Console')
        ->and($compact)->not->toContain('Foundry System');
});
