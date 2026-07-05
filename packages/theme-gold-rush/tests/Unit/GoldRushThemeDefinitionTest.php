<?php

declare(strict_types=1);

use Capell\ThemeStudio\GoldRush\GoldRushThemeServiceProvider;
use Capell\ThemeStudio\GoldRush\Health\ThemeGoldRushHealthCheck;

it('defines the gold-rush renderer contract', function (): void {
    $definition = GoldRushThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-gold-rush')
        ->and($definition->key)->toBe(GoldRushThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Gold Rush')
        ->and($definition->description)->toContain('awards scoreboard')
        ->and($definition->tags)->toContain('Awards', 'Scoreboard', 'Nominations', 'Judging', 'Showcase')
        ->and($definition->bestFit)->toContain('Design awards sites', 'Nominee showcases', 'Voting galleries')
        ->and($definition->includedSections)->toContain(
            'navigation',
            'hero',
            'winner-hero',
            'score-criteria',
            'newest-nominees',
            'previous-winners',
            'voting-status',
            'creator-credits',
            'proof',
            'content-listing',
            'newsletter',
            'cta',
            'footer',
        )
        ->and($definition->presets)->toHaveCount(2)
        ->and($definition->presets[0]->key)->toBe('gold-rush')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeGoldRushHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
