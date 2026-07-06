<?php

declare(strict_types=1);

use Capell\ThemeStudio\CallOut\Support\Demo\CallOutDemoContent;

/**
 * Asserts CallOutDemoContent's per-surface section ordering/types are
 * stable and every surface uses one consistent fictional brand — the same
 * consistency guarantee `NightShiftDemoContentCopyPreservationTest` checks
 * for the pre-existing Night Shift conversion, adapted here for a
 * brand-new theme (no pre-conversion literals to preserve, so this test
 * locks in the current shape instead of a historical one).
 */
it('keeps sectionCopy() ordering and types stable for the homepage surface', function (): void {
    $content = new CallOutDemoContent;

    expect(array_column($content->sectionCopy('homepage'), 'type'))->toBe([
        'emergency-availability-banner',
        'service-area-map-grid',
        'before-after-comparison',
        'quote-path-stepper',
        'accreditation-insurance-strips',
        'review-proof-wall',
        'team-on-the-road-cards',
    ]);
});

it('keeps sectionCopy() ordering and types stable for every other surface', function (): void {
    $content = new CallOutDemoContent;

    expect(array_column($content->sectionCopy('directory'), 'type'))->toBe([
        'service-area-map-grid', 'pricing-guide-table', 'accreditation-insurance-strips',
    ]);

    expect(array_column($content->sectionCopy('detail'), 'type'))->toBe([
        'before-after-comparison', 'team-on-the-road-cards', 'review-proof-wall',
    ]);

    expect(array_column($content->sectionCopy('contact'), 'type'))->toBe([
        'emergency-availability-banner', 'quote-path-stepper', 'accreditation-insurance-strips',
    ]);

    expect(array_column($content->sectionCopy('empty'), 'type'))->toBe([
        'service-area-map-grid', 'quote-path-stepper',
    ]);

    expect(array_column($content->sectionCopy('not-found'), 'type'))->toBe([
        'emergency-availability-banner',
    ]);

    expect(array_column($content->sectionCopy('cta'), 'type'))->toBe([
        'quote-path-stepper', 'review-proof-wall',
    ]);

    expect($content->sectionCopy('unknown-surface'))->toBe([]);
});

it('uses one consistent fictional brand name across every surface', function (): void {
    $content = new CallOutDemoContent;

    $definitions = $content->definitions('call-out', 'Call Out', 'https://call-out.test');

    $brandNames = collect($definitions)
        ->map(fn ($definition) => data_get($definition->renderData, 'navigation.brandName'))
        ->unique()
        ->values()
        ->all();

    expect($brandNames)->toBe(['Rapid Response Plumbing & Electrical']);
});

it('declares the three emergency-availability-banner states with distinct copy', function (): void {
    $content = new CallOutDemoContent;

    $homepageState = collect($content->sectionCopy('homepage'))->firstWhere('type', 'emergency-availability-banner');
    $contactState = collect($content->sectionCopy('contact'))->firstWhere('type', 'emergency-availability-banner');
    $notFoundState = collect($content->sectionCopy('not-found'))->firstWhere('type', 'emergency-availability-banner');

    expect(data_get($homepageState, 'state'))->toBe('open')
        ->and(data_get($contactState, 'state'))->toBe('after-hours')
        ->and(data_get($notFoundState, 'state'))->toBe('open');
});
