<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

/*
 * Wave 4c render coverage: one-take's "dossier pages" headline mechanic
 * (document metaphor, scroll-snap page navigation, view-transitions as a
 * progressive enhancement, §A) is implemented across five signature
 * widgets, each with a base view plus a `--` sidecar variant resolved by
 * VariantViewSectionRenderer per section['variant'] (Wave 2.2 convention):
 * showcase-hero-dossier (default / paginated), category-tabs-pagination
 * (default / tablist), one-page-grid-showcase (default / dense),
 * tools-sponsors-sidebar (default / rail), build-resources-cta
 * (default / split). Each renders without error given a minimal sample
 * payload, in both its base view and its declared variant view.
 *
 * The `capell-theme-one-take::` view namespace is normally registered by
 * OneTakeThemeServiceProvider::boot() once the package is marked installed;
 * this suite renders views directly without booting the full theme
 * registry, so it registers the namespace itself.
 */
beforeEach(function (): void {
    View::addNamespace('capell-theme-one-take', dirname(__DIR__, 2) . '/resources/views');
    Lang::addNamespace('capell-theme-one-take', dirname(__DIR__, 2) . '/resources/lang');
});

it('renders showcase-hero-dossier with folio numbers and margin notes', function (): void {
    $section = [
        'heading' => 'This week\'s featured one-pagers',
        'summary' => 'Hand-picked launches that nail the single-scroll story.',
        'items' => [
            ['title' => 'Harbor', 'summary' => 'A weekend launch page.', 'note' => 'Ships in a weekend.', 'url' => '#harbor'],
            ['title' => 'Cadence', 'summary' => 'A waitlist page.', 'note' => 'Four thousand signups.'],
        ],
    ];

    $html = view('capell-theme-one-take::sections.showcase-hero', ['section' => $section])->render();

    expect($html)->toContain('ops-dossier-folio')
        ->and($html)->toContain('ops-dossier-margin-note')
        ->and($html)->toContain('Page 01 of 02')
        ->and($html)->toContain('Ships in a weekend.')
        ->and($html)->toContain('Harbor')
        ->and($html)->not->toContain('Math.random');
});

it('renders the showcase-hero-dossier "paginated" variant as a scroll-snap page track with view-transition names', function (): void {
    $section = [
        'items' => [
            ['title' => 'Harbor', 'summary' => 'A weekend launch page.'],
            ['title' => 'Cadence', 'summary' => 'A waitlist page.'],
        ],
    ];

    $html = view('capell-theme-one-take::sections.showcase-hero--paginated', ['section' => $section])->render();

    expect($html)->toContain('ops-dossier-track')
        ->and($html)->toContain('ops-dossier-track-page')
        ->and($html)->toContain('view-transition-name: ops-dossier-folio-1')
        ->and($html)->toContain('view-transition-name: ops-dossier-folio-2')
        ->and($html)->not->toContain('Math.random');
});

it('renders category-tabs-pagination default variant with page-range labels', function (): void {
    $section = [
        'heading' => 'Browse by what you are building',
        'items' => [
            ['title' => 'Launch pages', 'summary' => 'Ship on day one.', 'range' => 'pp. 01-06', 'url' => '#one-page-grid'],
            ['title' => 'Portfolios', 'summary' => 'Work does the talking.', 'range' => 'pp. 07-11'],
        ],
    ];

    $html = view('capell-theme-one-take::sections.category-tabs', ['section' => $section])->render();

    expect($html)->toContain('ops-dossier-tab-range')
        ->and($html)->toContain('pp. 01-06')
        ->and($html)->toContain('pp. 07-11')
        ->and($html)->toContain('Launch pages');
});

it('renders the category-tabs-pagination "tablist" variant as a real ARIA tablist wired for the shared tabs.js module', function (): void {
    $section = [
        'items' => [
            ['title' => 'Launch pages', 'summary' => 'Ship on day one.', 'range' => 'pp. 01-06'],
            ['title' => 'Portfolios', 'summary' => 'Work does the talking.', 'range' => 'pp. 07-11'],
        ],
    ];

    $html = view('capell-theme-one-take::sections.category-tabs--tablist', ['section' => $section])->render();

    expect($html)->toContain('role="tablist"')
        ->and($html)->toContain('role="tab"')
        ->and($html)->toContain('role="tabpanel"')
        ->and($html)->toContain('aria-selected="true"')
        ->and($html)->toContain('aria-controls="ops-dossier-panel-0"')
        ->and($html)->toContain('aria-labelledby="ops-dossier-tab-0"')
        ->and($html)->toContain('hidden');
});

it('renders one-page-grid-showcase capped at fifty items', function (): void {
    $items = collect(range(1, 55))->map(static fn (int $index): array => [
        'title' => "Example {$index}",
        'summary' => "Summary {$index}",
        'image' => 'https://example.test/example.jpg',
        'imageAlt' => "Example {$index}",
    ])->all();

    $html = view('capell-theme-one-take::sections.one-page-grid', [
        'section' => ['heading' => 'Fresh from the gallery', 'items' => $items],
    ])->render();

    expect($html)->toContain('ops-dossier-folio')
        ->and($html)->toContain('Example 1')
        ->and($html)->not->toContain('Example 51')
        ->and($html)->not->toContain('Math.random');
});

it('renders the one-page-grid-showcase "dense" variant as a numbered dossier index, also capped at fifty', function (): void {
    $items = collect(range(1, 55))->map(static fn (int $index): array => [
        'title' => "Example {$index}",
        'summary' => "Summary {$index}",
        'meta' => 'Launch page',
    ])->all();

    $html = view('capell-theme-one-take::sections.one-page-grid--dense', [
        'section' => ['heading' => 'Fresh from the gallery', 'items' => $items],
    ])->render();

    expect($html)->toContain('ops-dossier-index')
        ->and($html)->toContain('ops-dossier-index-row')
        ->and($html)->toContain('Example 1')
        ->and($html)->not->toContain('Example 51');
});

it('renders tools-sponsors-sidebar default variant as a bound appendix list', function (): void {
    $section = [
        'heading' => 'Tools the makers actually used',
        'items' => [
            ['title' => 'Framer', 'summary' => 'A no-code builder.', 'meta' => 'Builder'],
            ['title' => 'Cal.com', 'summary' => 'Scheduling.', 'meta' => 'Booking'],
        ],
    ];

    $html = view('capell-theme-one-take::sections.tools-sponsors', ['section' => $section])->render();

    expect($html)->toContain('ops-dossier-appendix')
        ->and($html)->toContain('ops-dossier-appendix-mark')
        ->and($html)->toContain('Framer')
        ->and($html)->toContain('Appx. A')
        ->and($html)->toContain('Appx. B');
});

it('renders the tools-sponsors-sidebar "rail" variant as a sticky rail beside a lead note', function (): void {
    $section = [
        'heading' => 'Tools the makers actually used',
        'items' => [
            ['title' => 'Framer', 'summary' => 'A no-code builder.', 'meta' => 'Builder'],
        ],
    ];

    $html = view('capell-theme-one-take::sections.tools-sponsors--rail', ['section' => $section])->render();

    expect($html)->toContain('ops-dossier-rail-grid')
        ->and($html)->toContain('ops-dossier-appendix-rail')
        ->and($html)->toContain('Framer');
});

it('renders build-resources-cta default variant as a colophon chapter index', function (): void {
    $section = [
        'heading' => 'Build resources & guides',
        'items' => [
            ['title' => 'Anatomy of a converting one-pager', 'summary' => 'The five blocks.', 'url' => '#guide-one'],
            ['title' => 'Writing a hero that earns the scroll', 'summary' => 'State one promise.'],
        ],
        'ctaUrl' => '#build-resources',
        'ctaLabel' => 'Read every guide',
    ];

    $html = view('capell-theme-one-take::sections.build-resources', ['section' => $section])->render();

    expect($html)->toContain('ops-dossier-colophon')
        ->and($html)->toContain('Ch. 1')
        ->and($html)->toContain('Ch. 2')
        ->and($html)->toContain('Read every guide');
});

it('renders the build-resources-cta "split" variant with a promoted lead chapter card', function (): void {
    $section = [
        'heading' => 'Build resources & guides',
        'items' => [
            ['title' => 'Anatomy of a converting one-pager', 'summary' => 'The five blocks.', 'url' => '#guide-one'],
            ['title' => 'Writing a hero that earns the scroll', 'summary' => 'State one promise.'],
            ['title' => 'One CTA, said three ways', 'summary' => 'Repeat without nagging.'],
        ],
    ];

    $html = view('capell-theme-one-take::sections.build-resources--split', ['section' => $section])->render();

    expect($html)->toContain('ops-dossier-colophon-lead-card')
        ->and($html)->toContain('Anatomy of a converting one-pager')
        ->and($html)->toContain('Ch. 2')
        ->and($html)->toContain('Ch. 3')
        ->and($html)->not->toContain('ops-dossier-colophon-lead-card ops-dossier-colophon-lead-card');
});
