<?php

declare(strict_types=1);

use Capell\ThemeStudio\ScoreboardShowcase\Health\ThemeScoreboardShowcaseHealthCheck;
use Capell\ThemeStudio\ScoreboardShowcase\ScoreboardShowcaseThemeServiceProvider;

it('defines the scoreboard-showcase renderer contract', function (): void {
    $definition = ScoreboardShowcaseThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-scoreboard-showcase')
        ->and($definition->key)->toBe(ScoreboardShowcaseThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Scoreboard Showcase')
        ->and($definition->description)->toContain('design-awards rankings')
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
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('scoreboard-showcase')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeScoreboardShowcaseHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
