<?php

declare(strict_types=1);

use Capell\ThemeStudio\Restaurant\Health\ThemeRestaurantHealthCheck;

it('reports the Restaurant theme package as healthy', function (): void {
    expect(ThemeRestaurantHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(ThemeRestaurantHealthCheck::passed())->toBeTrue();
});

it('requires Foundation Theme in the manifest health check', function (): void {
    $check = new ThemeRestaurantHealthCheck(__DIR__ . '/../Fixtures/missing-foundation-dependency');

    expect($check->marketplaceManifestIssues())
        ->toContain('Manifest must require Foundation Theme for the inherited default renderer and demo installer.');
});

it('reports missing package files and definition sections as health failures', function (): void {
    $check = new ThemeRestaurantHealthCheck(__DIR__ . '/../Fixtures/missing-foundation-dependency');

    expect($check->missingRequiredPackageFiles())->toContain('resources/views/page.blade.php')
        ->and($check->packageFilesCheck()->passed)->toBeFalse()
        ->and((new ThemeRestaurantHealthCheck)->missingDefinitionSections())->toBe([]);
});
