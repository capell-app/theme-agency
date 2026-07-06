<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

/*
 * Wave 4c render coverage: launch-pad's "stagger launch sequence" headline
 * mechanic (scroll-staged reveal + momentum scroll-snap, §A) is implemented
 * as `--sequence` sidecar variants of five existing sections (hero,
 * category-navigation, website-examples, paid-templates, cta), resolved by
 * VariantViewSectionRenderer per section['variant'] (Wave 2.2 convention).
 * Each signature widget renders without error given a minimal sample
 * payload, in both its base view and its declared `sequence` variant view.
 *
 * The `capell-theme-launch-pad::` view namespace is normally registered by
 * LaunchPadThemeServiceProvider::boot() once the package is marked
 * installed; this suite renders views directly without booting the full
 * theme registry, so it registers the namespace itself.
 */
beforeEach(function (): void {
    View::addNamespace('capell-theme-launch-pad', dirname(__DIR__, 2) . '/resources/views');
    Lang::addNamespace('capell-theme-launch-pad', dirname(__DIR__, 2) . '/resources/lang');
});

it('renders launch-sequence-hero with deterministic per-step delays, never Math.random()', function (): void {
    $section = [
        'kicker' => 'Landing-page inspiration gallery',
        'heading' => 'A landing-page gallery worth saving',
        'summary' => 'Browse hundreds of high-converting landing pages.',
        'mediaUrl' => 'https://example.test/hero.jpg',
        'mediaAlt' => 'A SaaS landing page',
        'actions' => [
            ['label' => 'Browse the gallery', 'url' => '#website-examples', 'style' => 'primary'],
        ],
    ];

    $html = view('capell-theme-launch-pad::sections.hero--sequence', ['section' => $section])->render();

    expect($html)->toContain('lga-sequence-hero')
        ->and($html)->toContain('lga-sequence-step')
        ->and($html)->toContain('--lga-step: 0')
        ->and($html)->toContain('--lga-step: 4')
        ->and($html)->not->toContain('Math.random');

    $base = view('capell-theme-launch-pad::sections.hero', ['section' => $section])->render();

    expect($base)->toContain('A landing-page gallery worth saving');
});

it('renders launch-sequence-hero panel rows staggered when no media is supplied', function (): void {
    $section = [
        'heading' => 'A landing-page gallery worth saving',
        'summary' => 'Browse hundreds of high-converting landing pages.',
    ];

    $html = view('capell-theme-launch-pad::sections.hero--sequence', ['section' => $section])->render();

    expect($html)->toContain('lga-hero-panel-row lga-sequence-step')
        ->and($html)->toContain('--lga-step: 5')
        ->and($html)->toContain('--lga-step: 6');
});

it('renders category-navigation-grid on a momentum scroll-snap rail with staggered chips', function (): void {
    $section = [
        'heading' => 'Browse by what you are building',
        'items' => [
            ['title' => 'SaaS & software', 'summary' => 'Product launches.', 'url' => '#website-examples'],
            ['title' => 'Ecommerce & DTC', 'summary' => 'Storefronts.', 'url' => '#website-examples'],
        ],
    ];

    $html = view('capell-theme-launch-pad::sections.category-navigation--sequence', ['section' => $section])->render();

    expect($html)->toContain('lga-chip-rail')
        ->and($html)->toContain('--lga-step: 0')
        ->and($html)->toContain('--lga-step: 1')
        ->and($html)->toContain('role="list"')
        ->and($html)->toContain('role="listitem"');

    $base = view('capell-theme-launch-pad::sections.category-navigation', ['section' => $section])->render();

    expect($base)->toContain('SaaS &amp; software');
});

it('renders website-examples-grid as a momentum scroll-snap rail, capped at fifty items', function (): void {
    $items = collect(range(1, 55))->map(static fn (int $index): array => [
        'title' => "Example {$index}",
        'summary' => "Summary {$index}",
        'image' => 'https://example.test/example.jpg',
        'imageAlt' => "Example {$index}",
    ])->all();

    $html = view('capell-theme-launch-pad::sections.website-examples--sequence', [
        'section' => ['heading' => 'Latest landing pages', 'items' => $items],
    ])->render();

    expect($html)->toContain('lga-grid-rail')
        ->and($html)->toContain('lga-rail-card')
        ->and($html)->toContain('Example 1')
        ->and($html)->not->toContain('Example 51')
        ->and($html)->not->toContain('Math.random');
});

it('renders paid-templates-upsell with deterministic stagger delays', function (): void {
    $section = [
        'heading' => 'Paid templates ready to ship',
        'items' => [
            ['title' => 'Launch Kit — SaaS', 'summary' => 'A six-section launch page.', 'price' => '$79', 'framework' => 'Webflow'],
            ['title' => 'Storefront — Ecommerce', 'summary' => 'A product-led page.', 'free' => true, 'framework' => 'Shopify'],
        ],
    ];

    $html = view('capell-theme-launch-pad::sections.paid-templates--sequence', ['section' => $section])->render();

    expect($html)->toContain('--lga-step: 0')
        ->and($html)->toContain('--lga-step: 1')
        ->and($html)->toContain('Launch Kit — SaaS')
        ->and($html)->toContain('lga-price-badge-free');
});

it('renders launch-cta-sequence with the kicker, heading, lede, and button staggered together', function (): void {
    $section = [
        'heading' => 'Find your next landing page in minutes',
        'summary' => 'Start browsing the curated gallery free.',
        'label' => 'Browse the gallery',
        'url' => '#website-examples',
    ];

    $html = view('capell-theme-launch-pad::sections.cta--sequence', ['section' => $section])->render();

    expect($html)->toContain('lga-sequence-hero')
        ->and($html)->toContain('--lga-step: 0')
        ->and($html)->toContain('--lga-step: 3')
        ->and($html)->toContain('Find your next landing page in minutes')
        ->and($html)->toContain('Browse the gallery');
});
