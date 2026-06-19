<?php

declare(strict_types=1);

use Capell\ThemeStudio\RecruitmentJobs\Health\ThemeRecruitmentJobsHealthCheck;
use Capell\ThemeStudio\RecruitmentJobs\RecruitmentJobsThemeServiceProvider;

it('defines the recruitment-jobs renderer contract', function (): void {
    $definition = RecruitmentJobsThemeServiceProvider::definition();

    expect($definition->package)->toBe('capell-app/theme-recruitment-jobs')
        ->and($definition->key)->toBe(RecruitmentJobsThemeServiceProvider::THEME_KEY)
        ->and($definition->name)->toBe('Recruitment & Jobs')
        ->and($definition->includedSections)->toContain('navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('recruitment-jobs')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->extends)->toBe('default')
        ->and(ThemeRecruitmentJobsHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
