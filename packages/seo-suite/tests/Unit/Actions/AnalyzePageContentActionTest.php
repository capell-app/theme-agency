<?php

declare(strict_types=1);

use Capell\SeoSuite\Actions\AnalyzePageContentAction;

it('extracts heading and focus keyword signals from page content', function (): void {
    $analysis = AnalyzePageContentAction::run(
        html: <<<'HTML'
            <h1>Laravel CMS services</h1>
            <p>Laravel CMS teams use Capell to publish search-ready pages.</p>
            <h2>Packages</h2>
            <h3>SEO Suite</h3>
        HTML,
        metaTitle: 'Laravel CMS services for agencies',
        url: '/laravel-cms-services',
        targetKeywords: ['laravel cms'],
    );

    expect($analysis->h1Count)->toBe(1)
        ->and($analysis->headingOrderValid)->toBeTrue()
        ->and($analysis->wordCount)->toBe(15)
        ->and($analysis->focusKeywordOccurrences)->toBe(2)
        ->and($analysis->titleContainsFocusKeyword)->toBeTrue()
        ->and($analysis->urlContainsFocusKeyword)->toBeTrue()
        ->and($analysis->firstParagraphContainsFocusKeyword)->toBeTrue()
        ->and($analysis->headingTags)->toBe(['h1', 'h2', 'h3']);
});

it('detects missing h1s and skipped heading levels', function (): void {
    $analysis = AnalyzePageContentAction::run(
        html: '<h2>Overview</h2><h4>Details</h4><p>Short content.</p>',
    );

    expect($analysis->h1Count)->toBe(0)
        ->and($analysis->headingOrderValid)->toBeFalse()
        ->and($analysis->hasFocusKeyword())->toBeFalse();
});
