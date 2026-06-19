<?php

declare(strict_types=1);

use Capell\ThemeStudio\InteractiveBuilder\Health\ThemeInteractiveBuilderHealthCheck;
use Capell\ThemeStudio\InteractiveBuilder\InteractiveBuilderThemeServiceProvider;

it('defines the interactive-builder renderer contract', function (): void {
    $definition = InteractiveBuilderThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-interactive-builder')
        ->and($definition->key)->toBe(InteractiveBuilderThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Interactive Builder')
        ->and($definition->description)->toContain('Interactive Builder theme')
        ->and($definition->tags)->toContain('Builder', 'Creative Tool', 'SaaS', 'Templates', 'Collaboration')
        ->and($definition->bestFit)->toContain('Visual builder products', 'Creative tools', 'No-code platforms')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'canvas-workspace',
            'builder-capabilities',
            'templates',
            'collaboration-comments',
            'community-showcase',
            'publishing-controls',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('interactive-builder')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeInteractiveBuilderHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
