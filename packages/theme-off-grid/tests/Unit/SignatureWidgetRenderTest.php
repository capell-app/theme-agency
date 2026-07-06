<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

/*
 * Wave 4b render coverage: off-grid's field-station signature widgets --
 * irregular-index-grid (now genuinely seeded, default + scattered variants),
 * archive-dates' new calendar variant realising the archive-calendar
 * mechanic, and the new cross-archive time-capsule-browser (default +
 * stacked variants) -- render without error using the same view names
 * OffGridThemeServiceProvider::sectionRenderers() wires into
 * VariantViewSectionRenderer. Mirrors
 * packages/theme-foundation/tests/Unit/SectionVariantRenderTest.php and
 * theme-art-paper's SignatureWidgetRenderTest.php.
 */

beforeEach(function (): void {
    View::addNamespace('capell-theme-off-grid', dirname(__DIR__, 2) . '/resources/views');
});

it('renders the irregular-index-grid default view with a deterministic seeded stagger', function (): void {
    $section = [
        'heading' => 'Controlled inconsistency keeps a wall alive',
        'summary' => 'Long rows, short rows, sizes stay uneven, metadata stays exact.',
        'items' => [
            ['title' => 'Poster archive from an independent venue', 'summary' => 'A rough entry.'],
            ['title' => 'Interview with an experimental publisher', 'summary' => 'A text-forward entry.'],
        ],
    ];

    $firstRenderHtml = view('capell-theme-off-grid::sections.irregular-index', ['section' => $section])->render();
    $secondRenderHtml = view('capell-theme-off-grid::sections.irregular-index', ['section' => $section])->render();

    expect($firstRenderHtml)
        ->toContain('irregular-index-grid')
        ->toContain('data-layout-seed=')
        ->toContain('--rwi-seed-shift:')
        ->toContain('--rwi-seed-rotate:')
        ->toContain('Poster archive from an independent venue')
        // §0.1 determinism: the same payload must produce byte-identical
        // seeded output on every render -- never Math.random() at render.
        ->toBe($secondRenderHtml);
});

it('renders the irregular-index-grid scattered variant with the same deterministic seed source', function (): void {
    $section = [
        'heading' => 'Controlled inconsistency keeps a wall alive',
        'summary' => 'Long rows, short rows.',
        'items' => [
            ['title' => 'Poster archive from an independent venue', 'summary' => 'A rough entry.'],
        ],
    ];

    $html = view('capell-theme-off-grid::sections.irregular-index--scattered', ['section' => $section])->render();

    expect($html)
        ->toContain('data-variant="scattered"')
        ->toContain('rwi-index-list-scattered')
        ->toContain('data-layout-seed=')
        ->toContain('Poster archive from an independent venue');
});

it('caps irregular-index-grid entries at 50 per the §0.3 payload guardrail', function (): void {
    $items = array_map(
        static fn (int $index): array => ['title' => "Entry {$index}", 'summary' => 'Sample entry.'],
        range(1, 60),
    );

    $html = view('capell-theme-off-grid::sections.irregular-index', [
        'section' => ['heading' => 'Overflow test', 'items' => $items],
    ])->render();

    expect(substr_count($html, 'rwi-index-row'))->toBeLessThanOrEqual(50);
});

it('renders the archive-dates default ledger view without a calendar grid', function (): void {
    $section = [
        'heading' => 'The ledger, by month',
        'summary' => 'Browse the wall by month and year.',
        'items' => [
            ['title' => 'April 2026', 'meta' => 'Posters. Releases.', 'summary' => 'Eleven entries.'],
        ],
    ];

    $html = view('capell-theme-off-grid::sections.archive-dates', ['section' => $section])->render();

    expect($html)
        ->toContain('rwi-ledger')
        ->toContain('April 2026')
        ->not->toContain('rwi-calendar');
});

it('renders the archive-calendar variant as a genuine month-grid', function (): void {
    $section = [
        'heading' => 'The ledger, by month',
        'summary' => 'Browse the wall by month and year.',
        'items' => [
            ['title' => 'April 2026', 'meta' => 'Posters. Releases.', 'summary' => 'Eleven entries.'],
            ['title' => 'March 2026', 'meta' => 'Zines. Interviews.', 'summary' => 'Nine entries.'],
        ],
    ];

    $html = view('capell-theme-off-grid::sections.archive-dates--calendar', ['section' => $section])->render();

    expect($html)
        ->toContain('data-widget="archive-calendar"')
        ->toContain('data-variant="calendar"')
        ->toContain('rwi-calendar-week')
        ->toContain('rwi-calendar-grid')
        ->toContain('rwi-calendar-cell')
        ->toContain('April 2026')
        ->toContain('March 2026');
});

it('renders the time-capsule-browser default view as a 3D-perspective capsule rack', function (): void {
    $section = [
        'heading' => 'Eras of the wall, stacked in depth',
        'summary' => 'Each era is a sealed capsule.',
        'items' => [
            [
                'title' => 'Founding issues, 2018-2020',
                'summary' => 'The first photocopied runs.',
                'items' => [
                    ['title' => 'Issue 01: the founding manifesto'],
                    ['title' => 'Issue 02: basement show poster set'],
                ],
            ],
        ],
    ];

    $html = view('capell-theme-off-grid::sections.time-capsule-browser', ['section' => $section])->render();

    expect($html)
        ->toContain('data-widget="time-capsule-browser"')
        ->toContain('rwi-capsule-rack')
        ->toContain('rwi-capsule-toggle')
        ->toContain('type="radio"')
        ->toContain('rwi-capsule-preview')
        ->toContain('Founding issues, 2018-2020')
        ->toContain('Issue 01: the founding manifesto')
        ->toContain('checked');
});

it('renders the time-capsule-browser stacked variant without the perspective rack', function (): void {
    $section = [
        'heading' => 'Eras of the wall, stacked in depth',
        'summary' => 'Each era is a sealed capsule.',
        'items' => [
            ['title' => 'Current wall, 2024-present', 'summary' => 'The current shape.', 'items' => [['title' => 'Time capsule browser opened']]],
        ],
    ];

    $html = view('capell-theme-off-grid::sections.time-capsule-browser--stacked', ['section' => $section])->render();

    expect($html)
        ->toContain('data-variant="stacked"')
        ->toContain('rwi-capsule-rack-stacked')
        ->toContain('Current wall, 2024-present');
});

it('caps time-capsule-browser eras at 6 and preview items at 5 per the §0.3 payload guardrail', function (): void {
    $eras = array_map(
        static fn (int $eraIndex): array => [
            'title' => "Era {$eraIndex}",
            'summary' => 'Sample era.',
            'items' => array_map(
                static fn (int $itemIndex): array => ['title' => "Era {$eraIndex} item {$itemIndex}"],
                range(1, 9),
            ),
        ],
        range(1, 10),
    );

    $html = view('capell-theme-off-grid::sections.time-capsule-browser', [
        'section' => ['heading' => 'Overflow test', 'items' => $eras],
    ])->render();

    expect(substr_count($html, 'rwi-capsule-face'))->toBeLessThanOrEqual(6)
        ->and(substr_count($html, 'Era 1 item'))->toBeLessThanOrEqual(5);
});

it('bakes the seeded rotation and capsule depth in as static composition under the none motion tier, never as absence', function (): void {
    $css = file_get_contents(dirname(__DIR__, 2) . '/resources/css/theme-off-grid.css');

    expect($css)->not->toBeFalse();

    // programme §0.6: "none" must still look intentional via depth/scale/
    // composition. Assert the static (non-animated) composed rules exist --
    // rotation and layered shadow persist, only the transition duration
    // collapses to 0ms -- rather than asserting an absence of styling.
    expect($css)
        ->toContain("[data-motion-intensity='none'] .rwi-index-row")
        ->toContain('transform: rotate(var(--rwi-seed-rotate, 0deg));')
        ->toContain("[data-motion-intensity='none'] .rwi-capsule-face")
        ->toContain('transition-duration: 0ms;')
        ->toContain('box-shadow:')
        ->toContain('0.4rem 0.4rem 0 var(--rwi-line-soft)')
        ->toContain('perspective: 60rem;');
});
