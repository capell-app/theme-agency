<?php

declare(strict_types=1);

use Capell\ThemeStudio\RoboticsHardware\Health\ThemeRoboticsHardwareHealthCheck;
use Capell\ThemeStudio\RoboticsHardware\RoboticsHardwareThemeServiceProvider;

it('defines the robotics-hardware renderer contract', function (): void {
    $definition = RoboticsHardwareThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-robotics-hardware')
        ->and($definition->key)->toBe(RoboticsHardwareThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Robotics Hardware')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('robotics-hardware')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeRoboticsHardwareHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
