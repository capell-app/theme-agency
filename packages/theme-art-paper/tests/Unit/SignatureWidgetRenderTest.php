<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

/*
 * Wave 4a render coverage: each of art-paper's five kinetic-design-curation
 * signature widgets (kinetic-image-sequence, designer-profile-card-cluster,
 * material-swatch-studies, trend-forecast-infographic,
 * process-documentation-timeline) renders without error for both of its
 * declared variants, using the same view names
 * ArtPaperThemeServiceProvider::sectionRenderers() wires into
 * VariantViewSectionRenderer. Mirrors
 * packages/theme-foundation/tests/Unit/SectionVariantRenderTest.php.
 */

beforeEach(function (): void {
    View::addNamespace('capell-theme-art-paper', dirname(__DIR__, 2) . '/resources/views');
});

it('renders the kinetic-image-sequence gallery-feature default view without error', function (): void {
    $section = [
        'heading' => 'The coastal house, plate by plate',
        'summary' => 'A photo essay from our lead feature.',
        'items' => [
            ['title' => 'The approach from the headland', 'summary' => 'Photographed at first light.', 'image' => '/images/plate-1.jpg', 'imageAlt' => 'The headland at dawn'],
            ['title' => 'The corridor of glazing', 'summary' => 'Photograph by Mara Lindqvist.'],
        ],
    ];

    $html = view('capell-theme-art-paper::sections.gallery-feature', ['section' => $section])->render();

    expect($html)
        ->toContain('kinetic-image-sequence')
        ->toContain('dlm-kinetic-grid')
        ->toContain('The coastal house, plate by plate')
        ->toContain('data-layout-seed=');
});

it('renders the designer-profile-card-cluster gallery-feature cluster variant without error', function (): void {
    $section = [
        'heading' => 'Who made this issue',
        'summary' => 'The photographer, the makers, and the editor.',
        'items' => [
            ['name' => 'Mara Lindqvist', 'discipline' => 'Photography', 'summary' => 'Shoots every lead feature on location.'],
            ['name' => 'Halden Workshop', 'discipline' => 'Joinery & materials', 'summary' => 'The oak and granite credited across this issue.'],
        ],
    ];

    $html = view('capell-theme-art-paper::sections.gallery-feature--cluster', ['section' => $section])->render();

    expect($html)
        ->toContain('designer-profile-card-cluster')
        ->toContain('dlm-designer-cluster')
        ->toContain('Mara Lindqvist')
        ->toContain('Photography');
});

it('renders the material-swatch-studies product-credits default view without error', function (): void {
    $section = [
        'heading' => 'Sourced from this issue',
        'items' => [
            ['title' => 'Lighting and objects', 'summary' => 'The brass pendant.', 'finish' => 'gloss', 'weight' => 'light', 'swatchColor' => '#b08d57'],
            ['title' => 'Material notes', 'summary' => 'Lime plaster and fumed oak.', 'finish' => 'matte', 'weight' => 'heavy', 'beforeImage' => '/images/before.jpg', 'afterImage' => '/images/after.jpg'],
        ],
    ];

    $html = view('capell-theme-art-paper::sections.product-credits', ['section' => $section])->render();

    expect($html)
        ->toContain('material-swatch-studies')
        ->toContain('dlm-swatch-grid')
        ->toContain('Lighting and objects')
        ->toContain('data-compare-slider')
        ->toContain('role="slider"')
        ->toContain('Gloss')
        ->toContain('Light');
});

it('renders the trend-forecast-infographic product-credits trend-forecast variant without error', function (): void {
    $section = [
        'heading' => 'The season in swatches',
        'items' => [
            ['title' => 'Warm mineral plaster', 'summary' => 'Ochre and terracotta ranges.', 'palette' => ['#b08d57', '#8a5a3c'], 'share' => 42],
            ['title' => 'Fumed and reclaimed oak', 'summary' => 'Darker, smoke-treated timber.', 'palette' => ['#3d2f24'], 'share' => 133],
        ],
    ];

    $html = view('capell-theme-art-paper::sections.product-credits--trend-forecast', ['section' => $section])->render();

    expect($html)
        ->toContain('trend-forecast-infographic')
        ->toContain('dlm-trend-forecast')
        ->toContain('Warm mineral plaster')
        ->toContain('42%')
        // Payload cap / clamp guard: a share above 100 is clamped, never
        // rendered raw, so the widget can never draw an overflowing bar.
        ->toContain('100%')
        ->not->toContain('133%');
});

it('renders the process-documentation-timeline default view without error', function (): void {
    $section = [
        'heading' => 'How the plate reaches the page',
        'summary' => 'The stages every feature passes through before it runs.',
        'items' => [
            ['title' => 'Location scouting', 'summary' => 'Visited at least twice before commissioning photography.'],
            ['title' => 'Photography', 'summary' => 'Shot on location, timed to first or last light.'],
        ],
    ];

    $html = view('capell-theme-art-paper::sections.process-documentation-timeline', ['section' => $section])->render();

    expect($html)
        ->toContain('process-documentation-timeline')
        ->toContain('dlm-process-timeline')
        ->toContain('Location scouting')
        ->not->toContain('dlm-process-timeline-compact');
});

it('renders the process-documentation-timeline compact variant without error', function (): void {
    $section = [
        'heading' => 'How the plate reaches the page',
        'summary' => 'The stages every feature passes through before it runs.',
        'items' => [
            ['title' => 'Location scouting', 'summary' => 'Visited at least twice before commissioning photography.'],
        ],
    ];

    $html = view('capell-theme-art-paper::sections.process-documentation-timeline--compact', ['section' => $section])->render();

    expect($html)
        ->toContain('dlm-process-timeline-compact')
        ->toContain('data-variant="compact"')
        ->toContain('Location scouting');
});

it('caps gallery-feature plates at 50 per the §0.3 payload guardrail', function (): void {
    $items = array_map(
        static fn (int $index): array => ['title' => "Plate {$index}", 'summary' => 'Sample plate.'],
        range(1, 60),
    );

    $html = view('capell-theme-art-paper::sections.gallery-feature', [
        'section' => ['heading' => 'Overflow test', 'items' => $items],
    ])->render();

    expect(substr_count($html, 'dlm-kinetic-plate'))->toBeLessThanOrEqual(50);
});
