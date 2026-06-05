<?php

declare(strict_types=1);

use Capell\ThemeStudio\Portfolio\PortfolioThemeServiceProvider;

it('declares the required first-party theme manifest boundaries', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $composer = capell_json_file_array(__DIR__ . '/../../composer.json');
    $overviewContents = file_get_contents(__DIR__ . '/../../docs/overview.md');
    $overview = $overviewContents === false ? '' : $overviewContents;
    $readmeContents = file_get_contents(__DIR__ . '/../../README.md');
    $readme = $readmeContents === false ? '' : $readmeContents;

    expect($manifest['themeKey'])->toBe('portfolio')
        ->and(data_get($manifest, 'product.group'))->toBe('Capell Themes')
        ->and($overview)->toContain('Product group:' . PHP_EOL . '**Capell Themes**')
        ->and($readme)->toContain('- Product group: `Capell Themes`')
        ->and($manifest['extends'])->toBe('capell-app/foundation-theme')
        ->and(PortfolioThemeServiceProvider::definition()->extends)->toBe('default')
        ->and($overview)->toContain('runtime inheritance uses `extends: default`')
        ->and($overview)->toContain('records `capell-app/foundation-theme` as the installable Foundation')
        ->and($readme)->toContain('- Manifest extends: `capell-app/foundation-theme`')
        ->and($readme)->toContain('- Runtime extends: `default`')
        ->and($manifest['database']['migrations'])->toBeFalse()
        ->and($manifest['providers']['runtime'])->toContain(PortfolioThemeServiceProvider::class)
        ->and($manifest['marketplace']['summary'])->toBe('A premium portfolio theme for creators and consultants — turn selected work into outcome-driven case studies, sell your services and media kit, and grow your audience from one polished site.')
        ->and($manifest['marketplace']['description'])->toBe('Theme Portfolio gives independent creators, consultants, and studios a credibility-first website built around proof, not just pretty pictures. Lead with a work-led hero, walk visitors through outcome-rich case studies (scope, role, timeline, measurable results), and present your services, process, and media kit as things people can actually buy. Connect Media Library for real project imagery, Content Sections for deep case studies, and Newsletter for audience capture — every section degrades gracefully when an integration is not installed. Where an agency theme sells a team and campaigns, Theme Portfolio sells _you_: your work, your authority, and your availability.')
        ->and($composer['description'])->toBe(data_get($manifest, 'marketplace.summary'));
});

it('declares only marketplace screenshots that exist in the package', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $screenshots = data_get($manifest, 'marketplace.screenshots', []);

    throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Portfolio screenshots must be an array.');

    foreach ($screenshots as $screenshot) {
        throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Portfolio screenshot path must be a string.');

        expect(is_file(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});
