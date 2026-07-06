<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

/*
 * Wave 4b render coverage: each of reel-room's cinema-archive signature
 * widgets (video-preview-grid, date-filter-rail, featured-project-showcase,
 * jury-score-matrix, archive-wall-index, time-capsule-browser) renders
 * without error for both of its declared variants, using the same view
 * names ReelRoomThemeServiceProvider::sectionRenderers() wires into
 * VariantViewSectionRenderer. Mirrors
 * packages/theme-art-paper/tests/Unit/SignatureWidgetRenderTest.php.
 */

beforeEach(function (): void {
    View::addNamespace('capell-theme-reel-room', dirname(__DIR__, 2) . '/resources/views');
});

it('renders the video-preview-grid content-listing default view without error', function (): void {
    $section = [
        'heading' => 'The full winner archive',
        'summary' => 'Every gold, silver, and commendation filed since the first cycle.',
        'items' => [
            ['title' => 'Glasshouse', 'category' => 'Gold · Craft & technique', 'summary' => 'A macro-photography title sequence.', 'image' => '/images/glasshouse.jpg', 'videoSrc' => '/videos/glasshouse.mp4', 'url' => '#archive-1'],
            ['title' => 'Concrete Choir', 'category' => 'Silver · Motion direction', 'summary' => 'An architectural ident.', 'image' => '/images/concrete-choir.jpg'],
        ],
    ];

    $html = view('capell-theme-reel-room::sections.content-listing', ['section' => $section])->render();

    expect($html)
        ->toContain('video-preview-grid')
        ->toContain('mva-preview-grid')
        ->toContain('data-hover-video-poster')
        ->toContain('/videos/glasshouse.mp4')
        ->toContain('Glasshouse')
        ->toContain('Concrete Choir');
});

it('renders the video-preview-grid content-listing rows variant without error', function (): void {
    $section = [
        'heading' => 'The full winner archive',
        'items' => [
            ['title' => 'Loom', 'category' => 'Gold · Interactive & real-time', 'summary' => 'A generative installation.', 'image' => '/images/loom.jpg', 'videoSrc' => '/videos/loom.mp4'],
        ],
    ];

    $html = view('capell-theme-reel-room::sections.content-listing--rows', ['section' => $section])->render();

    expect($html)
        ->toContain('video-preview-grid')
        ->toContain('data-variant="rows"')
        ->toContain('mva-index')
        ->toContain('data-hover-video-poster')
        ->toContain('Loom');
});

it('caps video-preview-grid items at 50 per the §0.3 payload guardrail', function (): void {
    $items = array_map(
        static fn (int $index): array => ['title' => "Entry {$index}", 'summary' => 'Sample entry.'],
        range(1, 60),
    );

    $html = view('capell-theme-reel-room::sections.content-listing', [
        'section' => ['heading' => 'Overflow test', 'items' => $items],
    ])->render();

    expect(substr_count($html, 'class="mva-preview-card"'))->toBeLessThanOrEqual(50);
});

it('renders the date-filter-rail default view without error', function (): void {
    $section = [
        'heading' => 'Filter winners by year and category',
        'summary' => 'Pick a cycle on the left rail.',
        'items' => [
            ['title' => '2025 cycle', 'summary' => 'The current jury cycle.', 'url' => '#winner-list'],
        ],
    ];

    $html = view('capell-theme-reel-room::sections.date-filter-rail', ['section' => $section])->render();

    expect($html)
        ->toContain('mva-rail')
        ->toContain('2025 cycle')
        ->not->toContain('data-variant="compact"');
});

it('renders the date-filter-rail compact variant without error', function (): void {
    $section = [
        'heading' => 'Filter winners by year and category',
        'items' => [
            ['title' => '2025 cycle', 'summary' => 'The current jury cycle.', 'url' => '#winner-list'],
        ],
    ];

    $html = view('capell-theme-reel-room::sections.date-filter-rail--compact', ['section' => $section])->render();

    expect($html)
        ->toContain('date-filter-rail')
        ->toContain('data-variant="compact"')
        ->toContain('mva-rail-chip')
        ->toContain('2025 cycle');
});

it('renders the featured-project-showcase default view with inline credits and no error', function (): void {
    $section = [
        'heading' => 'Featured project: Tidal States',
        'summary' => 'A long-form title sequence.',
        'items' => [
            ['title' => 'Synopsis', 'summary' => 'A coastal documentary opener.'],
        ],
        'credits' => [
            ['role' => 'Direction', 'name' => 'Mara Quinteros'],
            ['role' => 'Studio', 'name' => 'Atlas Motion'],
        ],
    ];

    $html = view('capell-theme-reel-room::sections.featured-project', ['section' => $section])->render();

    expect($html)
        ->toContain('featured-project-showcase')
        ->toContain('mva-spotlight-credits')
        ->toContain('Mara Quinteros')
        ->toContain('Atlas Motion')
        ->not->toContain('data-variant="stacked"');
});

it('renders the featured-project-showcase stacked variant without error', function (): void {
    $section = [
        'heading' => 'Featured project: Tidal States',
        'summary' => 'A long-form title sequence.',
        'items' => [
            ['title' => 'Synopsis', 'summary' => 'A coastal documentary opener.'],
        ],
        'credits' => [
            ['role' => 'Direction', 'name' => 'Mara Quinteros'],
        ],
    ];

    $html = view('capell-theme-reel-room::sections.featured-project--stacked', ['section' => $section])->render();

    expect($html)
        ->toContain('featured-project-showcase')
        ->toContain('data-variant="stacked"')
        ->toContain('mva-spotlight-still-stacked')
        ->toContain('Mara Quinteros');
});

it('renders the jury-score-matrix default view without error', function (): void {
    $section = [
        'heading' => 'How the jury scored it',
        'summary' => 'Every winner carries the panel\'s reasoning.',
        'items' => [
            ['meta' => 'Axis 01 · weighted 30%', 'title' => 'Direction', 'summary' => 'Clear authorial voice.'],
        ],
    ];

    $html = view('capell-theme-reel-room::sections.jury-score-explainer', ['section' => $section])->render();

    expect($html)
        ->toContain('jury-score-matrix')
        ->toContain('mva-axes')
        ->toContain('Direction')
        ->not->toContain('data-variant="matrix"');
});

it('renders the jury-score-matrix matrix variant without error and clamps scores', function (): void {
    $section = [
        'heading' => 'Comparing this cycle\'s finalists',
        'summary' => 'The four scoring axes side by side.',
        'axes' => [
            ['title' => 'Direction'],
            ['title' => 'Craft'],
        ],
        'rows' => [
            ['title' => 'Tidal States', 'scores' => [96, 93]],
            ['title' => 'Signal Drift', 'scores' => [150, 40]],
        ],
    ];

    $html = view('capell-theme-reel-room::sections.jury-score-explainer--matrix', ['section' => $section])->render();

    expect($html)
        ->toContain('jury-score-matrix')
        ->toContain('data-variant="matrix"')
        ->toContain('mva-matrix')
        ->toContain('Tidal States')
        ->toContain('96%')
        // Payload clamp guard: a raw score above 100 is clamped, never
        // rendered raw.
        ->toContain('100%')
        ->not->toContain('150%');
});

it('renders the archive-wall-index default view without error and seeds a deterministic layout', function (): void {
    $section = [
        'heading' => 'The archive wall',
        'summary' => 'Every filed still in one contact-sheet grid.',
        'items' => [
            ['title' => 'Tidal States', 'image' => '/images/tidal.jpg'],
            ['title' => 'Signal Drift', 'image' => '/images/signal.jpg'],
        ],
    ];

    $htmlOne = view('capell-theme-reel-room::sections.archive-wall-index', ['section' => $section])->render();
    $htmlTwo = view('capell-theme-reel-room::sections.archive-wall-index', ['section' => $section])->render();

    expect($htmlOne)
        ->toContain('archive-wall-index')
        ->toContain('mva-wall')
        ->toContain('data-layout-seed=')
        ->toContain('Tidal States')
        ->and($htmlOne)->toBe($htmlTwo);
});

it('renders the archive-wall-index compact variant without error', function (): void {
    $section = [
        'heading' => 'The archive wall',
        'items' => [
            ['title' => 'Loom', 'image' => '/images/loom.jpg'],
        ],
    ];

    $html = view('capell-theme-reel-room::sections.archive-wall-index--compact', ['section' => $section])->render();

    expect($html)
        ->toContain('archive-wall-index')
        ->toContain('data-variant="compact"')
        ->toContain('mva-wall-compact')
        ->toContain('Loom');
});

it('caps archive-wall-index items at 50 per the §0.3 payload guardrail', function (): void {
    $items = array_map(
        static fn (int $index): array => ['title' => "Still {$index}"],
        range(1, 60),
    );

    $html = view('capell-theme-reel-room::sections.archive-wall-index', [
        'section' => ['heading' => 'Overflow test', 'items' => $items],
    ])->render();

    expect(substr_count($html, 'mva-wall-tile-caption'))->toBeLessThanOrEqual(50);
});

it('renders the time-capsule-browser default view without error', function (): void {
    $section = [
        'heading' => 'Step into an earlier cycle',
        'summary' => 'Each award cycle is filed as its own capsule.',
        'items' => [
            [
                'title' => '2025 cycle',
                'summary' => 'The current jury cycle.',
                'previewItems' => [
                    ['title' => 'Tidal States'],
                    ['title' => 'Signal Drift'],
                ],
            ],
        ],
    ];

    $html = view('capell-theme-reel-room::sections.time-capsule-browser', ['section' => $section])->render();

    expect($html)
        ->toContain('time-capsule-browser')
        ->toContain('mva-capsule-rack')
        ->toContain('type="radio"')
        ->toContain('2025 cycle')
        ->toContain('Tidal States')
        ->not->toContain('data-variant="void"');
});

it('renders the time-capsule-browser void variant without error', function (): void {
    $section = [
        'heading' => 'Step into an earlier cycle',
        'items' => [
            ['title' => '2021 · Founding cycle', 'summary' => 'The first verdicts.', 'previewItems' => [['title' => 'Founding cycle reel']]],
        ],
    ];

    $html = view('capell-theme-reel-room::sections.time-capsule-browser--void', ['section' => $section])->render();

    expect($html)
        ->toContain('time-capsule-browser')
        ->toContain('data-variant="void"')
        ->toContain('mva-capsule-rack-void')
        ->toContain('Founding cycle reel');
});

it('caps time-capsule-browser eras at 20 and preview items at 5 per the §0.3 payload guardrail', function (): void {
    $eras = array_map(
        static fn (int $index): array => [
            'title' => "Era {$index}",
            'previewItems' => array_map(static fn (int $previewIndex): array => ['title' => "Project {$index}-{$previewIndex}"], range(1, 8)),
        ],
        range(1, 25),
    );

    $html = view('capell-theme-reel-room::sections.time-capsule-browser', [
        'section' => ['heading' => 'Overflow test', 'items' => $eras],
    ])->render();

    expect(substr_count($html, 'mva-capsule-era'))->toBeLessThanOrEqual(20)
        ->and(substr_count($html, 'mva-capsule-preview-item'))->toBeLessThanOrEqual(20 * 5);
});
