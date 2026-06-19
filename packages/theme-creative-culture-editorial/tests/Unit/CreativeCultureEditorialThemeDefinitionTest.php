<?php

declare(strict_types=1);

use Capell\ThemeStudio\CreativeCultureEditorial\CreativeCultureEditorialThemeServiceProvider;
use Capell\ThemeStudio\CreativeCultureEditorial\Health\ThemeCreativeCultureEditorialHealthCheck;

it('defines the creative-culture-editorial renderer contract', function (): void {
    $definition = CreativeCultureEditorialThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-creative-culture-editorial')
        ->and($definition->key)->toBe(CreativeCultureEditorialThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Creative Culture Editorial')
        ->and($definition->description)->toContain('Creative culture editorial theme')
        ->and($definition->tags)->toContain('Creative Editorial', 'Culture', 'Projects', 'Opinion', 'Events')
        ->and($definition->bestFit)->toContain('Creative magazines', 'Design journals', 'Arts organisations')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'must-reads',
            'discipline-browsing',
            'project-stories',
            'opinion-block',
            'events-tags',
            'advice-culture',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('creative-culture-editorial')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeCreativeCultureEditorialHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
