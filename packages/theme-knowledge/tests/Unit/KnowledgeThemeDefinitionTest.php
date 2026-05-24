<?php

declare(strict_types=1);

use Capell\ThemeStudio\Knowledge\KnowledgeThemeServiceProvider;

it('defines the Knowledge theme contract', function (): void {
    $definition = KnowledgeThemeServiceProvider::definition();

    expect($definition->key)->toBe('knowledge')
        ->and($definition->package)->toBe('capell-app/theme-knowledge')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});
