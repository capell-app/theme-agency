<?php

declare(strict_types=1);

use Capell\ThemeStudio\OutdoorMission\Health\ThemeOutdoorMissionHealthCheck;
use Capell\ThemeStudio\OutdoorMission\OutdoorMissionThemeServiceProvider;

it('defines the outdoor-mission renderer contract', function (): void {
    $definition = OutdoorMissionThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-outdoor-mission')
        ->and($definition->key)->toBe(OutdoorMissionThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Outdoor Mission')
        ->and($definition->description)->toContain('mission-commerce')
        ->and($definition->tags)->toContain('Outdoor', 'Commerce', 'Activism', 'Field Stories', 'Repair')
        ->and($definition->bestFit)->toContain('Outdoor retailers', 'Environmental campaign teams', 'Repair-led commerce')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'seasonal-essentials',
            'sport-categories',
            'features',
            'repair-reuse',
            'environmental-campaign',
            'field-stories',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('outdoor-mission')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeOutdoorMissionHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
