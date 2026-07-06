<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

/*
 * Wave 4c render coverage: each open-studio "filmstrip scrubbing" signature
 * widget renders without error given a minimal sample payload, in both its
 * base view and its declared sidecar variant view (Wave 2.2
 * `<section>--<variant>.blade.php` convention, resolved by
 * VariantViewSectionRenderer per section['variant']).
 *
 * The `capell-theme-open-studio::` view namespace is normally registered by
 * OpenStudioThemeServiceProvider::boot() once the package is marked
 * installed; this suite renders views directly without booting the full
 * theme registry, so it registers the namespace itself.
 */
beforeEach(function (): void {
    View::addNamespace('capell-theme-open-studio', dirname(__DIR__, 2) . '/resources/views');
});

it('renders filmstrip-project-showcase as a scrub bar plus thumbnail timeline, capped at twenty frames', function (): void {
    $frames = collect(range(1, 25))->map(static fn (int $index): array => [
        'title' => "Frame {$index}",
        'caption' => "Caption {$index}",
    ])->all();

    $html = view('capell-theme-open-studio::sections.filmstrip-project-showcase', [
        'section' => ['heading' => 'Scrub the build', 'frames' => $frames],
    ])->render();

    expect($html)->toContain('data-filmstrip-project-showcase')
        ->and($html)->toContain('type="range"')
        ->and($html)->toContain('data-filmstrip-scrub')
        ->and($html)->toContain('Frame 1')
        ->and($html)->not->toContain('Frame 21')
        ->and($html)->not->toContain('Math.random');

    $compact = view('capell-theme-open-studio::sections.filmstrip-project-showcase--compact', [
        'section' => ['heading' => 'Scrub the build', 'frames' => $frames],
    ])->render();

    expect($compact)->toContain('csp-filmstrip-compact')
        ->and($compact)->toContain('data-filmstrip-scrub');
});

it('renders discipline-carousel-browse as a tablist matching the shared tabs.js role/aria contract', function (): void {
    $disciplines = [
        ['title' => 'Product & UX', 'items' => [['title' => 'Atlas Ledger', 'url' => '#project-feed']]],
        ['title' => 'Brand & Identity', 'items' => [['title' => 'Verde', 'url' => '#project-feed']]],
    ];

    $html = view('capell-theme-open-studio::sections.discipline-carousel-browse', [
        'section' => ['heading' => 'Browse by discipline', 'disciplines' => $disciplines],
    ])->render();

    expect($html)->toContain('role="tablist"')
        ->and($html)->toContain('role="tab"')
        ->and($html)->toContain('role="tabpanel"')
        ->and($html)->toContain('aria-selected="true"')
        ->and($html)->toContain('Atlas Ledger');

    $sticky = view('capell-theme-open-studio::sections.discipline-carousel-browse--sticky', [
        'section' => ['heading' => 'Browse by discipline', 'disciplines' => $disciplines],
    ])->render();

    expect($sticky)->toContain('csp-discipline-tablist-sticky')
        ->and($sticky)->toContain('role="tablist"');
});

it('renders process-notes-timeline as a scroll-drawn spine, capped at fifty entries', function (): void {
    $items = collect(range(1, 55))->map(static fn (int $index): array => [
        'title' => "Step {$index}",
        'summary' => "Summary {$index}",
    ])->all();

    $html = view('capell-theme-open-studio::sections.process-notes-timeline', [
        'section' => ['heading' => 'The build, in order', 'items' => $items],
    ])->render();

    expect($html)->toContain('data-process-notes-timeline')
        ->and($html)->toContain('Step 1')
        ->and($html)->not->toContain('Step 51');

    $compact = view('capell-theme-open-studio::sections.process-notes-timeline--compact', [
        'section' => ['heading' => 'The build, in order', 'items' => $items],
    ])->render();

    expect($compact)->toContain('csp-process-spine-compact');
});

it('renders credits-grid-roster as a full crew grid, capped at fifty entries', function (): void {
    $items = collect(range(1, 55))->map(static fn (int $index): array => [
        'name' => "Creator {$index}",
        'role' => "Role {$index}",
    ])->all();

    $html = view('capell-theme-open-studio::sections.credits-grid-roster', [
        'section' => ['heading' => 'Full credits', 'items' => $items],
    ])->render();

    expect($html)->toContain('Creator 1')
        ->and($html)->not->toContain('Creator 51');

    $rows = view('capell-theme-open-studio::sections.credits-grid-roster--rows', [
        'section' => ['heading' => 'Full credits', 'items' => $items],
    ])->render();

    expect($rows)->toContain('csp-roster-rows')
        ->and($rows)->toContain('Creator 1');
});

it('renders next-project-cta with a single next-project link, in both layout variants', function (): void {
    $html = view('capell-theme-open-studio::sections.next-project-cta', [
        'section' => [
            'title' => 'Verde — coffee brand system',
            'summary' => 'A rebrand for an independent roaster.',
            'url' => '#project-feed',
        ],
    ])->render();

    expect($html)->toContain('id="next-project-cta"')
        ->and($html)->toContain('Verde — coffee brand system')
        ->and($html)->toContain('href="#project-feed"');

    $stacked = view('capell-theme-open-studio::sections.next-project-cta--stacked', [
        'section' => [
            'title' => 'Verde — coffee brand system',
            'summary' => 'A rebrand for an independent roaster.',
            'url' => '#project-feed',
        ],
    ])->render();

    expect($stacked)->toContain('csp-next-project-stacked');
});
