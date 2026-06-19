<?php

declare(strict_types=1);

use Capell\ThemeStudio\CreatorNewsletter\CreatorNewsletterThemeServiceProvider;
use Capell\ThemeStudio\CreatorNewsletter\Health\ThemeCreatorNewsletterHealthCheck;

it('defines the creator-newsletter renderer contract', function (): void {
    $definition = CreatorNewsletterThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-creator-newsletter')
        ->and($definition->key)->toBe(CreatorNewsletterThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Creator Newsletter')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('creator-newsletter')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeCreatorNewsletterHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
