<?php

declare(strict_types=1);

use Capell\ThemeStudio\ConferenceEvent\ConferenceEventThemeServiceProvider;
use Capell\ThemeStudio\ConferenceEvent\Health\ThemeConferenceEventHealthCheck;

it('defines the conference-event renderer contract', function (): void {
    $definition = ConferenceEventThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-conference-event')
        ->and($definition->key)->toBe(ConferenceEventThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Conference Event')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('conference-event')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeConferenceEventHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
