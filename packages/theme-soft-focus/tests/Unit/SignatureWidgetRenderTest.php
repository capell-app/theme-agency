<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

/*
 * Wave 4c render coverage: soft-focus's five "scatter light table"
 * signature widgets (browse-panels-scatter, style-type-categories-
 * scattered, latest-showcase-organic, sponsor-space-floating,
 * random-best-of-cta) render without error for both their default and
 * scattered/rotated variants, using the same view names
 * SoftFocusThemeServiceProvider::sectionRenderers() wires into
 * VariantViewSectionRenderer. The determinism tests are the most
 * important coverage here per the programme's §0.1 guardrail: the
 * mechanic must never use Math.random(), so the same payload must always
 * render byte-identical scattered HTML.
 */

beforeEach(function (): void {
    View::addNamespace('capell-theme-soft-focus', dirname(__DIR__, 2) . '/resources/views');
});

it('renders the browse-panels-scatter variant without error', function (): void {
    $section = [
        'pageSeed' => 'unit-test-seed',
        'heading' => 'Three quiet ways into the gallery',
        'summary' => 'A scattered light table of ways to browse.',
        'items' => [
            ['title' => 'By style', 'summary' => 'Minimal, editorial, and portfolio hangs.', 'url' => '#style-type-categories'],
            ['title' => 'By type', 'summary' => 'Studio sites, publications, and product pages.', 'url' => '#style-type-categories'],
            ['title' => 'Random', 'summary' => 'One unexpected pick, reshuffled on every visit.', 'url' => '#random-best-of'],
        ],
    ];

    $html = view('capell-theme-soft-focus::sections.browse-panels--scatter', ['section' => $section])->render();

    expect($html)
        ->toContain('browse-panels-scatter')
        ->toContain('data-variant="scatter"')
        ->toContain('qwg-scatter-table')
        ->toContain('data-scatter-seed="unit-test-seed"')
        ->toContain('tabindex="0"')
        ->toContain('By style')
        ->toContain('--qwg-scatter-rotate:')
        ->toContain('--qwg-scatter-z:');
});

it('produces the same deterministic scatter layout for the same page seed across renders', function (): void {
    $section = [
        'pageSeed' => 'deterministic-scatter-seed',
        'heading' => 'Determinism check',
        'items' => [
            ['title' => 'Alpha panel', 'summary' => 'First.'],
            ['title' => 'Beta panel', 'summary' => 'Second.'],
            ['title' => 'Gamma panel', 'summary' => 'Third.'],
        ],
    ];

    $first = view('capell-theme-soft-focus::sections.browse-panels--scatter', ['section' => $section])->render();
    $second = view('capell-theme-soft-focus::sections.browse-panels--scatter', ['section' => $section])->render();

    expect($first)->toBe($second);
});

it('renders the style-type-categories-scattered variant without error', function (): void {
    $section = [
        'pageSeed' => 'categories-seed',
        'heading' => 'Browse by category, quietly',
        'items' => [
            ['title' => 'Minimal', 'summary' => 'Restraint and calm type.', 'count' => '38 sites'],
            ['title' => 'Editorial', 'summary' => 'Reading-first sites.', 'count' => '21 sites'],
        ],
    ];

    $html = view('capell-theme-soft-focus::sections.style-type-categories--scattered', ['section' => $section])->render();

    expect($html)
        ->toContain('style-type-categories-scattered')
        ->toContain('data-variant="scattered"')
        ->toContain('qwg-scatter-table-categories')
        ->toContain('Minimal')
        ->toContain('38 sites');
});

it('produces the same deterministic order for style-type-categories-scattered across renders', function (): void {
    $section = [
        'pageSeed' => 'categories-determinism-seed',
        'items' => [
            ['title' => 'Minimal', 'count' => '38 sites'],
            ['title' => 'Editorial', 'count' => '21 sites'],
            ['title' => 'Portfolio', 'count' => '29 sites'],
        ],
    ];

    $first = view('capell-theme-soft-focus::sections.style-type-categories--scattered', ['section' => $section])->render();
    $second = view('capell-theme-soft-focus::sections.style-type-categories--scattered', ['section' => $section])->render();

    expect($first)->toBe($second);
});

it('renders the latest-showcase-organic variant without error', function (): void {
    $section = [
        'pageSeed' => 'showcase-seed',
        'heading' => 'Recently hung',
        'summary' => 'The newest captures.',
        'items' => [
            ['title' => 'Marlow Studio', 'summary' => 'A restrained studio site.', 'meta' => 'Portfolio'],
            ['title' => 'Tideline Journal', 'summary' => 'A reading-first editorial site.', 'meta' => 'Publication'],
        ],
    ];

    $html = view('capell-theme-soft-focus::sections.latest-showcase--organic', ['section' => $section])->render();

    expect($html)
        ->toContain('latest-showcase-organic')
        ->toContain('data-variant="organic"')
        ->toContain('qwg-scatter-table-showcase')
        ->toContain('Marlow Studio');
});

it('caps latest-showcase-organic items at 50 per the §0.3 payload guardrail', function (): void {
    $items = array_map(
        static fn (int $index): array => ['title' => "Capture {$index}", 'summary' => 'A capture.'],
        range(1, 60),
    );

    $html = view('capell-theme-soft-focus::sections.latest-showcase--organic', [
        'section' => ['pageSeed' => 'overflow-seed', 'heading' => 'Overflow test', 'items' => $items],
    ])->render();

    expect(substr_count($html, 'data-scatter-tile'))->toBeLessThanOrEqual(50);
});

it('produces the same deterministic scatter layout for latest-showcase-organic across renders', function (): void {
    $section = [
        'pageSeed' => 'showcase-determinism-seed',
        'items' => [
            ['title' => 'Marlow Studio'],
            ['title' => 'Tideline Journal'],
        ],
    ];

    $first = view('capell-theme-soft-focus::sections.latest-showcase--organic', ['section' => $section])->render();
    $second = view('capell-theme-soft-focus::sections.latest-showcase--organic', ['section' => $section])->render();

    expect($first)->toBe($second);
});

it('renders the sponsor-space-floating variant without error', function (): void {
    $section = [
        'pageSeed' => 'sponsor-seed',
        'heading' => 'One quiet sponsor placement',
        'items' => [
            ['title' => 'Held Studio', 'summary' => 'Type foundry and small studio.'],
        ],
    ];

    $html = view('capell-theme-soft-focus::sections.sponsor-space--floating', ['section' => $section])->render();

    expect($html)
        ->toContain('sponsor-space-floating')
        ->toContain('data-variant="floating"')
        ->toContain('qwg-scatter-tile-floating')
        ->toContain('Held Studio')
        ->toContain('--qwg-scatter-rotate:');
});

it('produces the same deterministic float for sponsor-space-floating across renders', function (): void {
    $section = [
        'pageSeed' => 'sponsor-determinism-seed',
        'items' => [
            ['title' => 'Held Studio', 'summary' => 'Type foundry.'],
        ],
    ];

    $first = view('capell-theme-soft-focus::sections.sponsor-space--floating', ['section' => $section])->render();
    $second = view('capell-theme-soft-focus::sections.sponsor-space--floating', ['section' => $section])->render();

    expect($first)->toBe($second);
});

it('renders the random-best-of-cta seeded-rotation variant without error', function (): void {
    $section = [
        'heading' => 'One pick, held a little longer',
        'summary' => 'Each visit surfaces a single spotlighted site.',
        'featuredIndex' => 1,
        'items' => [
            ['title' => 'Northglass', 'summary' => 'A quiet product page.'],
            ['title' => 'Held Studio', 'summary' => 'A type foundry site.'],
            ['title' => 'Marlow Studio', 'summary' => 'A restrained studio site.'],
        ],
    ];

    $html = view('capell-theme-soft-focus::sections.random-best-of--seeded-rotation', ['section' => $section])->render();

    expect($html)
        ->toContain('random-best-of-cta')
        ->toContain('data-variant="seeded-rotation"')
        ->toContain('data-rotation-index="1"')
        ->toContain('data-rotation-count="3"')
        ->toContain('Held Studio')
        ->toContain('qwg-rotation-rail')
        ->toContain('is-active');
});

it('rotates the random-best-of-cta featured pick deterministically from the day-of-year seed, never Math.random()', function (): void {
    $section = [
        'heading' => 'One pick, held a little longer',
        'dayOfYear' => 5,
        'items' => [
            ['title' => 'First candidate', 'summary' => 'A.'],
            ['title' => 'Second candidate', 'summary' => 'B.'],
            ['title' => 'Third candidate', 'summary' => 'C.'],
        ],
    ];

    // day-of-year 5 modulo 3 candidates == index 2 ("Third candidate").
    $html = view('capell-theme-soft-focus::sections.random-best-of--seeded-rotation', ['section' => $section])->render();

    expect($html)
        ->toContain('data-rotation-index="2"')
        ->toContain('Third candidate');

    $second = view('capell-theme-soft-focus::sections.random-best-of--seeded-rotation', ['section' => $section])->render();

    expect($html)->toBe($second);
});

it('never emits Math.random or a client-side re-layout script in any scatter widget', function (): void {
    $views = [
        'capell-theme-soft-focus::sections.browse-panels--scatter' => ['section' => ['pageSeed' => 's', 'items' => [['title' => 'A']]]],
        'capell-theme-soft-focus::sections.style-type-categories--scattered' => ['section' => ['pageSeed' => 's', 'items' => [['title' => 'A']]]],
        'capell-theme-soft-focus::sections.latest-showcase--organic' => ['section' => ['pageSeed' => 's', 'items' => [['title' => 'A']]]],
        'capell-theme-soft-focus::sections.sponsor-space--floating' => ['section' => ['pageSeed' => 's', 'items' => [['title' => 'A']]]],
        'capell-theme-soft-focus::sections.random-best-of--seeded-rotation' => ['section' => ['items' => [['title' => 'A']]]],
    ];

    foreach ($views as $view => $data) {
        $html = view($view, $data)->render();

        expect($html)
            ->not->toContain('Math.random')
            ->not->toContain('<script');
    }
});
