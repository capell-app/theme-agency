<?php

declare(strict_types=1);

use Capell\ThemeStudio\ArtPaper\ArtPaperThemeServiceProvider;
use Capell\ThemeStudio\ArtPaper\Health\ThemeArtPaperHealthCheck;

it('defines the art-paper renderer contract', function (): void {
    $definition = ArtPaperThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-art-paper')
        ->and($definition->key)->toBe(ArtPaperThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Art Paper')
        ->and($definition->description)->toContain('Photography-led features')
        ->and($definition->tags)->toContain('Magazine', 'Design', 'Architecture', 'Interiors', 'Culture')
        ->and($definition->bestFit)->toContain('Design magazines', 'Architecture publishers', 'Art and culture journals')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'lead-story',
            'vertical-categories',
            'editor-picks',
            'gallery-feature',
            'product-credits',
            'trend-list',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(3)
        ->and($definition->presets[0]->key)->toBe('art-paper')
        ->and($definition->presets[2]->key)->toBe('warm-ink')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeArtPaperHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
