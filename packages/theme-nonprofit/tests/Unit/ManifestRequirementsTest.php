<?php

declare(strict_types=1);

use Capell\ThemeStudio\Nonprofit\NonprofitThemeServiceProvider;

it('declares the required first-party theme manifest boundaries', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $composer = capell_json_file_array(__DIR__ . '/../../composer.json');

    expect($manifest['themeKey'])->toBe('nonprofit')
        ->and($manifest['extends'])->toBeNull()
        ->and(data_get($manifest, 'dependencies.requires'))->toContain('capell-app/frontend')
        ->and($manifest['database']['migrations'])->toBeFalse()
        ->and($manifest['providers']['runtime'])->toContain(NonprofitThemeServiceProvider::class)
        ->and($manifest['marketplace']['summary'])->toBe('A premium charity, NGO, and civic theme that moves visitors from your mission to a clear support action — donate, volunteer, or follow — with impact proof, live campaign progress, and transparent annual-report sections built in.')
        ->and($manifest['marketplace']['description'])->toBe('Theme Nonprofit gives charities, NGOs, and civic organisations a calm, trust-building front end engineered around the supporter journey: a campaign-led hero, impact evidence, donation and volunteer paths, events, community stories, and transparency/annual-report sections. It ships as a Capell theme with accessible defaults (skip link, focus-visible outlines, semantic sections) and a route-backed demo you can install in one command to preview a complete cause site. Sections light up automatically when you add Capell Payments (donations), Campaign Studio (live appeals), Events, Blog, and Form Builder — no template surgery required. Premium, first-party, and priority-supported for teams who need a public site that converts interest into sustained giving.')
        ->and(data_get($composer, 'require.php'))->toBe('^8.4')
        ->and($composer['description'])->toBe(data_get($manifest, 'marketplace.summary'));
});

it('declares only marketplace screenshots that exist in the package', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $screenshots = data_get($manifest, 'marketplace.screenshots', []);

    throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Nonprofit screenshots must be an array.');

    foreach ($screenshots as $screenshot) {
        throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Nonprofit screenshot path must be a string.');

        expect(is_file(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});
