<?php

declare(strict_types=1);

use Capell\ThemeStudio\DesignLedMagazine\DesignLedMagazineThemeServiceProvider;
use Capell\ThemeStudio\DesignLedMagazine\Health\ThemeDesignLedMagazineHealthCheck;

it('defines the design-led-magazine renderer contract', function (): void {
    $definition = DesignLedMagazineThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-design-led-magazine')
        ->and($definition->key)->toBe(DesignLedMagazineThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Design Led Magazine')
        ->and($definition->description)->toContain('Design-led magazine theme')
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
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('design-led-magazine')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeDesignLedMagazineHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
