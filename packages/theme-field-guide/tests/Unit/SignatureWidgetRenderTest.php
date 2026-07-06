<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

/*
 * Wave 4c render coverage: each of field-guide's five tessellation-grid
 * signature widgets (taxonomy-grid-browser, latest-designs-showcase,
 * editor-picks-curated, faq-archives-accordion, collection-cta-browse)
 * renders without error for both of its declared variants, using the same
 * view names FieldGuideThemeServiceProvider::sectionRenderers() wires into
 * VariantViewSectionRenderer.
 */

beforeEach(function (): void {
    View::addNamespace('capell-theme-field-guide', dirname(__DIR__, 2) . '/resources/views');
});

it('renders the taxonomy-grid-browser default view without error', function (): void {
    $section = [
        'pageSeed' => 'unit-test-seed',
        'heading' => 'Browse the index, filtered your way',
        'summary' => 'A dense reflow grid.',
        'facets' => [
            ['facet' => 'type', 'label' => 'Type'],
            ['facet' => 'style', 'label' => 'Style'],
        ],
        'items' => [
            ['id' => 'a', 'title' => 'Fintech pricing page', 'summary' => 'Three tiers, one anchor.', 'tags' => [['label' => 'Pricing', 'facet' => 'type']]],
            ['id' => 'b', 'title' => 'Docs hub with search', 'summary' => 'Search-first front door.', 'tags' => [['label' => 'Docs', 'facet' => 'type']]],
        ],
    ];

    $html = view('capell-theme-field-guide::sections.taxonomy-grid-browser', ['section' => $section])->render();

    expect($html)
        ->toContain('taxonomy-grid-browser')
        ->toContain('fga-tessellation-grid')
        ->toContain('data-grid-browser-seed="unit-test-seed"')
        ->toContain('Fintech pricing page')
        ->toContain('data-grid-browser-facets=');
});

it('renders the taxonomy-grid-browser compact variant without error', function (): void {
    $section = [
        'pageSeed' => 'compact-seed',
        'heading' => 'Twelve thousand captures behind four facets',
        'items' => [
            ['id' => 'a', 'title' => 'Ecommerce catalogue', 'tags' => [['label' => 'Ecommerce', 'facet' => 'industry']]],
        ],
    ];

    $html = view('capell-theme-field-guide::sections.taxonomy-grid-browser--compact', ['section' => $section])->render();

    expect($html)
        ->toContain('data-variant="compact"')
        ->toContain('fga-tessellation-grid-compact')
        ->toContain('Ecommerce catalogue');
});

it('caps taxonomy-grid-browser items at 50 per the §0.3 payload guardrail', function (): void {
    $items = array_map(
        static fn (int $index): array => ['id' => "item-{$index}", 'title' => "Capture {$index}"],
        range(1, 60),
    );

    $html = view('capell-theme-field-guide::sections.taxonomy-grid-browser', [
        'section' => ['pageSeed' => 'overflow-seed', 'heading' => 'Overflow test', 'items' => $items],
    ])->render();

    expect(substr_count($html, 'data-grid-browser-item'))->toBeLessThanOrEqual(50);
});

it('produces the same deterministic order for the same page seed across renders', function (): void {
    $section = [
        'pageSeed' => 'deterministic-seed',
        'heading' => 'Determinism check',
        'items' => [
            ['id' => 'a', 'title' => 'Alpha capture'],
            ['id' => 'b', 'title' => 'Beta capture'],
            ['id' => 'c', 'title' => 'Gamma capture'],
        ],
    ];

    $first = view('capell-theme-field-guide::sections.taxonomy-grid-browser', ['section' => $section])->render();
    $second = view('capell-theme-field-guide::sections.taxonomy-grid-browser', ['section' => $section])->render();

    expect($first)->toBe($second);
});

it('renders the latest-designs-showcase default view without error', function (): void {
    $section = [
        'heading' => 'Fresh into the index',
        'summary' => 'Newest captures first.',
        'items' => [
            ['title' => 'Health onboarding in four quiet steps', 'meta' => 'today', 'tags' => [['label' => 'Onboarding', 'facet' => 'type']]],
        ],
    ];

    $html = view('capell-theme-field-guide::sections.latest-designs', ['section' => $section])->render();

    expect($html)
        ->toContain('latest-designs-showcase')
        ->toContain('Health onboarding in four quiet steps');
});

it('renders the latest-designs-showcase showcase-wide variant without error', function (): void {
    $section = [
        'heading' => 'Fresh into the index',
        'summary' => 'Newest captures first.',
        'items' => [
            ['title' => 'Lead capture of the week', 'meta' => 'today', 'tags' => [['label' => 'Landing page', 'facet' => 'type']]],
            ['title' => 'Second capture', 'meta' => 'yesterday'],
        ],
    ];

    $html = view('capell-theme-field-guide::sections.latest-designs--showcase-wide', ['section' => $section])->render();

    expect($html)
        ->toContain('data-variant="showcase-wide"')
        ->toContain('fga-showcase-lead')
        ->toContain('Lead capture of the week');
});

it('renders the editor-picks-curated default view without error', function (): void {
    $section = [
        'heading' => 'Pinned by the curation desk this week',
        'summary' => 'A short stack of captures.',
        'items' => [
            ['title' => 'Fintech pricing with one anchor tier', 'summary' => 'Three tiers, one anchor.'],
        ],
    ];

    $html = view('capell-theme-field-guide::sections.editor-picks', ['section' => $section])->render();

    expect($html)
        ->toContain('editor-picks-curated')
        ->toContain('Fintech pricing with one anchor tier');
});

it('renders the editor-picks-curated alternating variant with curator notes on alternating sides', function (): void {
    $section = [
        'heading' => 'Pinned by the curation desk this week',
        'summary' => 'A short stack of captures.',
        'items' => [
            ['title' => 'First pick', 'curatorNote' => 'A calm pricing page.'],
            ['title' => 'Second pick', 'curatorNote' => 'A dense catalogue that still breathes.'],
        ],
    ];

    $html = view('capell-theme-field-guide::sections.editor-picks--alternating', ['section' => $section])->render();

    expect($html)
        ->toContain('data-variant="alternating"')
        ->toContain('fga-curator-row-note-start')
        ->toContain('fga-curator-row-note-end')
        ->toContain('A calm pricing page.')
        ->toContain('A dense catalogue that still breathes.');
});

it('renders the faq-archives-accordion default view using the shared accordion-toggle.js contract', function (): void {
    $section = [
        'heading' => 'How the archive works',
        'summary' => 'The rules the curation desk works to.',
        'items' => [
            ['title' => 'How does a site get into the index?', 'summary' => 'Submit a URL.'],
            ['title' => 'What do the four facets mean?', 'summary' => 'Type, style, colour, industry.'],
        ],
    ];

    $html = view('capell-theme-field-guide::sections.faq-archives', ['section' => $section])->render();

    expect($html)
        ->toContain('faq-archives-accordion')
        ->toContain('data-accordion')
        ->toContain('data-accordion-mode="single"')
        ->toContain('data-accordion-trigger')
        ->toContain('data-accordion-panel')
        ->toContain('aria-expanded="true"')
        ->toContain('aria-expanded="false"')
        ->toContain('aria-controls="faq-archives-panel-0"')
        ->toContain('id="faq-archives-panel-0"')
        ->toContain('How does a site get into the index?');
});

it('renders the faq-archives-accordion two-column variant with two independent accordion containers', function (): void {
    $section = [
        'heading' => 'How the archive works',
        'items' => [
            ['title' => 'Question one', 'summary' => 'Answer one.'],
            ['title' => 'Question two', 'summary' => 'Answer two.'],
            ['title' => 'Question three', 'summary' => 'Answer three.'],
            ['title' => 'Question four', 'summary' => 'Answer four.'],
        ],
    ];

    $html = view('capell-theme-field-guide::sections.faq-archives--two-column', ['section' => $section])->render();

    expect($html)
        ->toContain('data-variant="two-column"')
        ->toContain('data-accordion-mode="multi"')
        ->toContain('Question one')
        ->toContain('Question four');

    expect(substr_count($html, 'data-accordion-mode="multi"'))->toBe(2);
});

it('renders the collection-cta-browse default view without error', function (): void {
    $section = [
        'heading' => 'Seen a site the index should hold?',
        'summary' => 'Submissions go through the same intake.',
        'actions' => [
            ['label' => 'Submit a site', 'url' => '/theme-field-guide-contact', 'style' => 'primary'],
        ],
    ];

    $html = view('capell-theme-field-guide::sections.cta', ['section' => $section])->render();

    expect($html)
        ->toContain('collection-cta-browse')
        ->toContain('Seen a site the index should hold?');
});

it('renders the collection-cta-browse browse variant with facet shortcuts', function (): void {
    $section = [
        'heading' => 'Or send the index something first',
        'summary' => 'Submit the site you keep showing people.',
        'actions' => [
            ['label' => 'Submit a site', 'url' => '/theme-field-guide-contact', 'style' => 'primary'],
        ],
        'collections' => [
            ['label' => 'Browse by type', 'url' => '#taxonomy-navigation'],
            ['label' => 'Browse by industry', 'url' => '#taxonomy-navigation'],
        ],
    ];

    $html = view('capell-theme-field-guide::sections.cta--browse', ['section' => $section])->render();

    expect($html)
        ->toContain('data-variant="browse"')
        ->toContain('fga-cta-browse-row')
        ->toContain('Browse by type')
        ->toContain('Browse by industry');
});
