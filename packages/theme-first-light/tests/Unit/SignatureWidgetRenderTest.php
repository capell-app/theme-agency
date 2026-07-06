<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

/*
 * Wave 4c render coverage: first-light's "lightbox reel" signature widgets --
 * curation-feed-grid (contact-sheet grid, default + compact variants),
 * lightbox-carousel-viewer (the shared lightbox.js Alpine dialog, default +
 * minimal variants), best-of-views-carousel (shared carousel.js, default +
 * carousel variants), source-metadata-credits (default + credits variants),
 * and next-item-lightbox-cta (default + lightbox-preview variants) -- render
 * without error using the same view names
 * FirstLightThemeServiceProvider::sectionRenderers() wires into
 * VariantViewSectionRenderer. Mirrors
 * packages/theme-off-grid/tests/Unit/SignatureWidgetRenderTest.php.
 */

beforeEach(function (): void {
    View::addNamespace('capell-theme-first-light', dirname(__DIR__, 2) . '/resources/views');
});

it('renders the curation-feed-grid default contact sheet with lightbox triggers', function (): void {
    $section = [
        'heading' => 'Every capture, at a glance',
        'summary' => 'The reel, laid out as a contact sheet.',
        'items' => [
            ['title' => 'Driftwork\'s empty states', 'image' => '/img/one.jpg', 'meta' => 'App · iOS'],
            ['title' => 'Verda\'s product page', 'image' => '/img/two.jpg', 'meta' => 'Website · SaaS'],
        ],
    ];

    $html = view('capell-theme-first-light::sections.curation-feed-grid', ['section' => $section])->render();

    expect($html)
        ->toContain('data-widget="curation-feed-grid"')
        ->toContain('data-variant="default"')
        ->toContain('class="lightbox mcf-contact-trigger"')
        ->toContain('data-lightbox="/img/one.jpg"')
        ->toContain('data-group="curation-feed"')
        ->toContain('data-type="image"')
        ->toContain('data-title="Driftwork&#039;s empty states"')
        ->toContain('Verda&#039;s product page');
});

it('caps curation-feed-grid entries at 50 per the §0.3 payload guardrail', function (): void {
    $items = array_map(
        static fn (int $index): array => ['title' => "Entry {$index}", 'image' => "/img/{$index}.jpg"],
        range(1, 60),
    );

    $html = view('capell-theme-first-light::sections.curation-feed-grid', [
        'section' => ['heading' => 'Overflow test', 'items' => $items],
    ])->render();

    expect(substr_count($html, 'mcf-contact-frame'))->toBeLessThanOrEqual(50 * 2);
    expect(substr_count($html, 'data-lightbox='))->toBeLessThanOrEqual(50);
});

it('renders the curation-feed-grid compact variant with an overlay caption instead of a caption line', function (): void {
    $section = [
        'heading' => 'Every capture, at a glance',
        'items' => [
            ['title' => 'Northline\'s icon grid', 'image' => '/img/three.jpg'],
        ],
    ];

    $html = view('capell-theme-first-light::sections.curation-feed-grid--compact', ['section' => $section])->render();

    expect($html)
        ->toContain('data-variant="compact"')
        ->toContain('mcf-contact-sheet-compact')
        ->toContain('mcf-contact-caption-overlay')
        ->toContain('Northline&#039;s icon grid');
});

it('renders the lightbox-carousel-viewer default dialog wired to the shared lightbox.js Alpine contract', function (): void {
    $html = view('capell-theme-first-light::sections.lightbox-carousel-viewer', ['section' => []])->render();

    expect($html)
        ->toContain('data-widget="lightbox-carousel-viewer"')
        ->toContain('data-variant="default"')
        ->toContain('x-data="lightbox"')
        ->toContain('@lightbox.window="lightbox(event)"')
        ->toContain('loadPrevious()')
        ->toContain('loadNext()')
        ->toContain('role="dialog"')
        ->toContain('aria-modal="true"');
});

it('renders the lightbox-carousel-viewer minimal variant frameless', function (): void {
    $html = view('capell-theme-first-light::sections.lightbox-carousel-viewer--minimal', ['section' => []])->render();

    expect($html)
        ->toContain('data-variant="minimal"')
        ->toContain('mcf-lightbox-dialog-minimal')
        ->toContain('x-data="lightbox"')
        ->not->toContain('mcf-lightbox-frame');
});

it('renders the best-of-views-carousel variant with the shared carousel.js data-carousel-* contract', function (): void {
    $section = [
        'heading' => 'What readers keep coming back to, in order',
        'items' => [
            ['title' => 'Driftwork\'s empty states', 'image' => '/img/one.jpg', 'meta' => '18.2k views'],
            ['title' => 'Lumen\'s three-sentence pricing', 'image' => '/img/two.jpg', 'meta' => '14.9k views'],
        ],
    ];

    $html = view('capell-theme-first-light::sections.best-of-views--carousel', ['section' => $section])->render();

    expect($html)
        ->toContain('data-widget="best-of-views-carousel"')
        ->toContain('data-variant="carousel"')
        ->toContain('class="swiper mcf-best-of-carousel"')
        ->toContain('data-carousel-id="first-light-best-of"')
        ->toContain('data-carousel-navigation="true"')
        ->toContain('data-carousel-pagination="true"')
        ->toContain('data-lightbox="/img/one.jpg"')
        ->toContain('data-group="curation-feed"');
});

it('caps best-of-views-carousel entries at 20 per the §0.3 payload guardrail', function (): void {
    $items = array_map(
        static fn (int $index): array => ['title' => "Entry {$index}", 'image' => "/img/{$index}.jpg"],
        range(1, 30),
    );

    $html = view('capell-theme-first-light::sections.best-of-views--carousel', [
        'section' => ['heading' => 'Overflow test', 'items' => $items],
    ])->render();

    expect(substr_count($html, 'swiper-slide mcf-best-of-slide'))->toBeLessThanOrEqual(20);
});

it('renders the source-metadata-credits variant as an inline credit strip', function (): void {
    $section = [
        'heading' => 'Every capture, credited',
        'items' => [
            ['title' => 'Maker', 'summary' => 'Driftwork — an independent two-person studio.'],
            ['title' => 'Source', 'summary' => 'driftwork.example, captured 14 June.'],
        ],
    ];

    $html = view('capell-theme-first-light::sections.source-metadata--credits', ['section' => $section])->render();

    expect($html)
        ->toContain('data-widget="source-metadata-credits"')
        ->toContain('data-variant="credits"')
        ->toContain('mcf-credit-strip')
        ->toContain('mcf-credit-item')
        ->toContain('Driftwork — an independent two-person studio.');
});

it('renders the next-item-lightbox-cta variant pairing CTA copy with a lightbox-opening preview', function (): void {
    $section = [
        'heading' => 'Tomorrow\'s capture is already queued',
        'summary' => 'Follow along in the feed.',
        'actions' => [
            ['label' => 'Get the daily email', 'url' => '#newsletter', 'style' => 'primary'],
        ],
        'nextImage' => '/img/next.jpg',
        'nextTitle' => 'Tomorrow\'s capture, queued for the morning edition',
    ];

    $html = view('capell-theme-first-light::sections.cta--lightbox-preview', ['section' => $section])->render();

    expect($html)
        ->toContain('data-widget="next-item-lightbox-cta"')
        ->toContain('data-variant="lightbox-preview"')
        ->toContain('class="lightbox mcf-cta-next-trigger"')
        ->toContain('data-lightbox="/img/next.jpg"')
        ->toContain('data-group="curation-feed"')
        ->toContain('Tomorrow&#039;s capture, queued for the morning edition')
        ->toContain('Get the daily email');
});

it('falls back to the standard CTA button when no next-item image is supplied', function (): void {
    $section = [
        'heading' => 'Tomorrow\'s capture is already queued',
    ];

    $html = view('capell-theme-first-light::sections.cta--lightbox-preview', ['section' => $section])->render();

    expect($html)
        ->not->toContain('mcf-cta-next-trigger')
        ->toContain('mcf-button');
});
