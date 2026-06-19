<?php

declare(strict_types=1);

use Capell\ThemeStudio\AiAgent\AiAgentThemeServiceProvider;
use Capell\ThemeStudio\AiAgent\Health\ThemeAiAgentHealthCheck;

it('defines the ai-agent renderer contract', function (): void {
    $definition = AiAgentThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-ai-agent')
        ->and($definition->key)->toBe(AiAgentThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('AI Agent')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('ai-agent')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeAiAgentHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
