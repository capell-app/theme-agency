<?php

declare(strict_types=1);

use Capell\ThemeStudio\AiLab\AiLabThemeServiceProvider;
use Capell\ThemeStudio\AiLab\Health\ThemeAiLabHealthCheck;

it('defines the ai-lab renderer contract', function (): void {
    $definition = AiLabThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-ai-lab')
        ->and($definition->key)->toBe(AiLabThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('AI Lab')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('ai-lab')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeAiLabHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
