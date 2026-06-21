<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

it('ships Laravel Boost guidelines for every package and skills only where useful', function (): void {
    $packagesWithSkills = [
        'address',
        'insights',
        'ai-orchestrator',
        'login-audit',
        'backup',
        'blog',
        'campaign-studio',
        'foundation-theme',
        'deployments',
        'diagnostics',
        'form-builder',
        'agent-bridge',
        'media-library',
        'migration-assistant',
        'layout-builder',
        'navigation',
        'redirects',
        'seo-suite',
        'search',
        'tags',
        'publishing-studio',
    ];

    $packageComposerFiles = Finder::create()
        ->files()
        ->in(dirname(__DIR__, 2) . '/packages')
        ->depth(1)
        ->name('composer.json')
        ->sortByName();

    foreach ($packageComposerFiles as $packageComposerFile) {
        $packagePath = $packageComposerFile->getPath();
        $packageName = basename($packagePath);
        $boostPath = $packagePath . '/resources/boost';
        $packageSkillFiles = glob($packagePath . '/resources/boost/skills/*/SKILL.md');
        $packageSkillFiles = $packageSkillFiles !== false ? $packageSkillFiles : [];

        if (in_array($packageName, $packagesWithSkills, true)) {
            expect($packagePath . '/resources/boost/guidelines/core.blade.php')->toBeFile();
            expect($packageSkillFiles)->not->toBeEmpty();

            continue;
        }

        if (! is_dir($boostPath)) {
            continue;
        }

        expect($packagePath . '/resources/boost/guidelines/core.blade.php')->toBeFile();
        expect($packageSkillFiles)->toBeEmpty();
    }
});
