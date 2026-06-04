<?php

declare(strict_types=1);

use Capell\ThemeStudio\Portfolio\PortfolioThemeServiceProvider;

it('declares the required first-party theme manifest boundaries', function (): void {
    $contents = file_get_contents(__DIR__ . '/../../capell.json');
    $manifest = json_decode($contents === false ? '{}' : $contents, true, flags: JSON_THROW_ON_ERROR);
    $composerContents = file_get_contents(__DIR__ . '/../../composer.json');
    $composer = json_decode($composerContents === false ? '{}' : $composerContents, true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['themeKey'])->toBe('portfolio')
        ->and($manifest['extends'])->toBe('capell-app/foundation-theme')
        ->and($manifest['database']['migrations'])->toBeFalse()
        ->and($manifest['providers']['runtime'])->toContain(PortfolioThemeServiceProvider::class)
        ->and($manifest['marketplace']['summary'])->toBe('A premium portfolio theme for creators and consultants — turn selected work into outcome-driven case studies, sell your services and media kit, and grow your audience from one polished site.')
        ->and($manifest['marketplace']['description'])->toBe('Theme Portfolio gives independent creators, consultants, and studios a credibility-first website built around proof, not just pretty pictures. Lead with a work-led hero, walk visitors through outcome-rich case studies (scope, role, timeline, measurable results), and present your services, process, and media kit as things people can actually buy. Connect Media Library for real project imagery, Content Sections for deep case studies, and Newsletter for audience capture — every section degrades gracefully when an integration is not installed. Where an agency theme sells a team and campaigns, Theme Portfolio sells _you_: your work, your authority, and your availability.')
        ->and($composer['description'])->toBe($manifest['marketplace']['summary']);
});

it('declares only marketplace screenshots that exist in the package', function (): void {
    $contents = file_get_contents(__DIR__ . '/../../capell.json');
    $manifest = json_decode($contents === false ? '{}' : $contents, true, flags: JSON_THROW_ON_ERROR);

    foreach ($manifest['marketplace']['screenshots'] as $screenshot) {
        expect(is_file(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});
