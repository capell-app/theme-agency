<?php

declare(strict_types=1);

use Capell\ThemeStudio\FarField\FarFieldThemeServiceProvider;
use Capell\ThemeStudio\FarField\Health\ThemeFarFieldHealthCheck;

it('defines the far-field renderer contract', function (): void {
    $definition = FarFieldThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-far-field')
        ->and($definition->key)->toBe(FarFieldThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Far Field')
        ->and($definition->description)->toContain('Dispatches, city guides, columnists')
        ->and($definition->tags)->toContain('Magazine', 'Global Affairs', 'Travel', 'Culture', 'Audio')
        ->and($definition->bestFit)->toContain('Global magazines', 'Culture publishers', 'City guide brands')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'lead-dispatch',
            'radio-audio',
            'city-guides',
            'photo-essay',
            'cultural-dispatch-timeline',
            'travel-culture',
            'shop-books',
            'columnists',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->frontend['sectionVariants'])->toBe([
            'radio-audio' => ['default', 'immersive'],
            'city-guides' => ['default', 'tabs'],
            'photo-essay' => ['default', 'reveal'],
            'cultural-dispatch-timeline' => ['default', 'compact'],
            'columnists' => ['default', 'roster'],
        ])
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('far-field')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeFarFieldHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
