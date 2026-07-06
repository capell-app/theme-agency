<?php

declare(strict_types=1);

use Capell\ThemeStudio\InkPress\Health\ThemeInkPressHealthCheck;
use Capell\ThemeStudio\InkPress\InkPressThemeServiceProvider;

it('defines the ink-press renderer contract', function (): void {
    $definition = InkPressThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-ink-press')
        ->and($definition->key)->toBe(InkPressThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Ink Press')
        ->and($definition->description)->toContain('newsroom front page')
        ->and($definition->tags)->toContain('News', 'Analysis', 'Opinion', 'Video', 'Live')
        ->and($definition->bestFit)->toContain('News publishers', 'Policy journals', 'Analysis desks')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'top-stories',
            'breaking-news-ribbon',
            'live-event-timeline',
            'reading-progress-with-markers',
            'topic-navigation',
            'news-web-topology',
            'author-credibility-inline',
            'opinion-grid-with-bylines',
            'video-row',
            'missed-it',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('ink-press')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeInkPressHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('declares at least two variants for each Wave 4a signature widget', function (): void {
    $definition = InkPressThemeServiceProvider::definition();
    $rawSectionVariants = $definition->frontend['sectionVariants'] ?? [];

    expect($rawSectionVariants)->toBeArray();

    /** @var array<string, array<int, string>> $sectionVariants */
    $sectionVariants = is_array($rawSectionVariants) ? $rawSectionVariants : [];

    foreach ([
        'breaking-news-ribbon',
        'live-event-timeline',
        'reading-progress-with-markers',
        'news-web-topology',
        'author-credibility-inline',
        'opinion-grid-with-bylines',
    ] as $signatureSection) {
        $variants = $sectionVariants[$signatureSection] ?? null;

        expect($sectionVariants)->toHaveKey($signatureSection)
            ->and($variants)->toBeArray()
            ->and(count($variants ?? []))->toBeGreaterThanOrEqual(2);
    }
});
