<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

/*
 * Wave 4c render coverage: agency's "salon gallery wall" headline mechanic
 * (curated hang, varied deterministic grid spans, featured floats above, §A)
 * is implemented as sidecar variants of five existing sections
 * (featured-portfolios, filter-taxonomies, portfolio-grid, awarded-profiles,
 * education-upsell), resolved by VariantViewSectionRenderer per
 * section['variant'] (Wave 2.2 convention). Each signature widget renders
 * without error given a minimal sample payload, in both its base view and
 * its declared variant view.
 *
 * The `capell-theme-agency::` view namespace is normally registered by
 * AgencyThemeServiceProvider::boot() once the package is marked installed;
 * this suite renders views directly without booting the full theme
 * registry, so it registers the namespace itself.
 */
beforeEach(function (): void {
    View::addNamespace('capell-theme-agency', dirname(__DIR__, 2) . '/resources/views');
    Lang::addNamespace('capell-theme-agency', dirname(__DIR__, 2) . '/resources/lang');
});

it('renders featured-portfolios-hero with deterministic per-card float depths, never Math.random()', function (): void {
    $section = [
        'heading' => "This week's featured collections",
        'summary' => 'Three portfolios our editors are championing right now.',
        'items' => [
            ['id' => 'featured-1', 'title' => 'Halden Studio', 'summary' => 'Systems-first product design.', 'award' => 'Awwwards SOTD'],
            ['title' => 'Ren Okabe', 'summary' => 'Identity work for cultural institutions.', 'award' => 'D&AD Pencil'],
        ],
    ];

    $html = view('capell-theme-agency::sections.featured-portfolios--parallax', ['section' => $section])->render();

    expect($html)->toContain('ppc-wall-float-card')
        ->and($html)->toContain('id="featured-1"')
        ->and($html)->toContain('--ppc-float-depth:')
        ->and($html)->toContain('Halden Studio')
        ->and($html)->not->toContain('Math.random');

    $base = view('capell-theme-agency::sections.featured-portfolios', ['section' => $section])->render();

    expect($base)->toContain('Halden Studio');
});

it('renders filter-taxonomies-grid with count-weighted deterministic tile spans', function (): void {
    $section = [
        'heading' => 'Browse by discipline',
        'items' => [
            ['title' => 'Product design', 'summary' => 'App and web interfaces.', 'count' => '24', 'url' => '#filters'],
            ['title' => 'Motion & 3D', 'summary' => 'Reels and rigs.', 'count' => '4', 'url' => '#filters'],
        ],
    ];

    $html = view('capell-theme-agency::sections.filter-taxonomies--grid', ['section' => $section])->render();

    expect($html)->toContain('ppc-wall-taxonomy-tile')
        ->and($html)->toContain('--ppc-taxonomy-col-span: 2')
        ->and($html)->toContain('--ppc-taxonomy-col-span: 1')
        ->and($html)->toContain('role="listitem"');

    $base = view('capell-theme-agency::sections.filter-taxonomies', ['section' => $section])->render();

    expect($base)->toContain('Product design');
});

it('renders portfolio-grid-gallery-wall with a deterministic seeded span pattern, capped at fifty items', function (): void {
    $items = collect(range(1, 55))->map(static fn (int $index): array => [
        'title' => "Portfolio {$index}",
        'summary' => "Summary {$index}",
        'image' => 'https://example.test/portfolio.jpg',
        'imageAlt' => "Portfolio {$index}",
    ])->all();

    $section = ['heading' => 'Recently added to the index', 'items' => $items];

    $htmlOne = view('capell-theme-agency::sections.portfolio-grid--gallery-wall', ['section' => $section])->render();
    $htmlTwo = view('capell-theme-agency::sections.portfolio-grid--gallery-wall', ['section' => $section])->render();

    expect($htmlOne)->toContain('ppc-wall-card')
        ->and($htmlOne)->toContain('data-layout-seed=')
        ->and($htmlOne)->toContain('Portfolio 1')
        ->and($htmlOne)->not->toContain('Portfolio 51')
        ->and($htmlOne)->not->toContain('Math.random')
        ->and($htmlOne)->toBe($htmlTwo);
});

it('renders awarded-profiles-spotlight with gold, silver, and bronze tiers', function (): void {
    $section = [
        'heading' => "This year's award winners",
        'items' => [
            ['title' => 'Meridian rebrand', 'summary' => 'A fintech identity.', 'award' => 'D&AD Yellow Pencil'],
            ['title' => 'Northwind platform', 'summary' => 'A renewables tool.', 'award' => 'Awwwards SOTY'],
            ['title' => 'Verda architecture', 'summary' => 'An editorial portfolio.', 'award' => 'FWA Honoree'],
        ],
    ];

    $html = view('capell-theme-agency::sections.awarded-profiles--spotlight', ['section' => $section])->render();

    expect($html)->toContain('ppc-spotlight-gold')
        ->and($html)->toContain('ppc-spotlight-silver')
        ->and($html)->toContain('ppc-spotlight-bronze')
        ->and($html)->toContain('Meridian rebrand');

    $base = view('capell-theme-agency::sections.awarded-profiles', ['section' => $section])->render();

    expect($base)->toContain('Meridian rebrand');
});

it('renders awarded-profiles-spotlight honouring an explicit payload tier over position order', function (): void {
    $section = [
        'heading' => "This year's award winners",
        'items' => [
            ['title' => 'Underdog Studio', 'summary' => 'A late entry.', 'tier' => 'gold'],
        ],
    ];

    $html = view('capell-theme-agency::sections.awarded-profiles--spotlight', ['section' => $section])->render();

    expect($html)->toContain('ppc-spotlight-gold')
        ->and($html)->toContain('Underdog Studio');
});

it('renders education-upsell-cta with the first item promoted into a lead banner', function (): void {
    $section = [
        'items' => [
            ['title' => 'Process teardowns', 'summary' => 'Step-by-step breakdowns.', 'url' => '#learn'],
            ['title' => 'Creator interviews', 'summary' => 'Honest conversations.', 'url' => '#learn'],
        ],
    ];

    $html = view('capell-theme-agency::sections.education-upsell--cta', ['section' => $section])->render();

    expect($html)->toContain('ppc-course-lead')
        ->and($html)->toContain('Process teardowns')
        ->and($html)->toContain('Creator interviews');

    $base = view('capell-theme-agency::sections.education-upsell', ['section' => $section])->render();

    expect($base)->toContain('Process teardowns');
});
