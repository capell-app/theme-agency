<?php

declare(strict_types=1);

use Capell\ThemeStudio\EstateAgents\EstateAgentsThemeServiceProvider;
use Illuminate\Support\Facades\File;

it('declares the required first-party estate agents theme manifest boundaries', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $composer = capell_json_file_array(__DIR__ . '/../../composer.json');

    $overviewContents = file_get_contents(__DIR__ . '/../../docs/overview.md');
    $overview = $overviewContents === false ? '' : $overviewContents;
    $readmeContents = file_get_contents(__DIR__ . '/../../README.md');
    $readme = $readmeContents === false ? '' : $readmeContents;

    expect(data_get($manifest, 'themeKey'))->toBe('estate-agents')
        ->and(data_get($manifest, 'product.group'))->toBe('Capell Themes')
        ->and(data_get($manifest, 'product.tier'))->toBe('premium')
        ->and(data_get($manifest, 'commercial.proposedLicense'))->toBe('paid')
        ->and($overview)->toContain('Capell Themes')
        ->and($readme)->toContain('Capell Themes')
        ->and(data_get($manifest, 'extends'))->toBe('default')
        ->and(data_get($manifest, 'dependencies.requires'))->toContain('capell-app/foundation-theme')
        ->and(data_get($manifest, 'dependencies.requires'))->toContain('capell-app/frontend')
        ->and(data_get($composer, 'require'))->toHaveKey('capell-app/foundation-theme')
        ->and(EstateAgentsThemeServiceProvider::definition()->extends)->toBe('default')
        ->and($overview)->toContain('runtime inheritance uses `extends: default`')
        ->and($overview)->toContain('requires `capell-app/foundation-theme`')
        ->and($overview)->toContain('capell-app/frontend')
        ->and($readme)->toContain('extends: default')
        ->and(data_get($manifest, 'database.migrations'))->toBeFalse()
        ->and(data_get($manifest, 'providers.runtime'))->toContain(EstateAgentsThemeServiceProvider::class)
        ->and(data_get($manifest, 'commands.demo'))->toBe('capell:theme-estate-agents-demo')
        ->and(data_get($manifest, 'marketplace.summary'))->toBe('A premium property theme for estate agencies, lettings teams, valuations, local guides, and viewing-led enquiry journeys.')
        ->and(data_get($manifest, 'marketplace.description'))->toBe(data_get($manifest, 'description'))
        ->and(data_get($composer, 'description'))->toBe(data_get($manifest, 'marketplace.summary'));
});

it('declares only estate agents marketplace screenshots that exist in the package', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');
    $screenshots = data_get($manifest, 'marketplace.screenshots', []);

    throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Estate Agents screenshots must be an array.');

    expect($screenshots)->toHaveCount(6);

    foreach ($screenshots as $screenshot) {
        throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Estate Agents screenshot path must be a string.');

        expect(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});
