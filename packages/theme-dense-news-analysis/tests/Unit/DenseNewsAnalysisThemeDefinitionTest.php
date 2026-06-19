<?php

declare(strict_types=1);

use Capell\ThemeStudio\DenseNewsAnalysis\DenseNewsAnalysisThemeServiceProvider;
use Capell\ThemeStudio\DenseNewsAnalysis\Health\ThemeDenseNewsAnalysisHealthCheck;

it('defines the dense-news-analysis renderer contract', function (): void {
    $definition = DenseNewsAnalysisThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-dense-news-analysis')
        ->and($definition->key)->toBe(DenseNewsAnalysisThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Dense News Analysis')
        ->and($definition->description)->toContain('Dense news and analysis theme')
        ->and($definition->tags)->toContain('News', 'Analysis', 'Opinion', 'Video', 'Live')
        ->and($definition->bestFit)->toContain('News publishers', 'Policy journals', 'Analysis desks')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'top-stories',
            'live-brief',
            'topic-navigation',
            'opinion-analysis',
            'video-row',
            'missed-it',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('dense-news-analysis')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeDenseNewsAnalysisHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
