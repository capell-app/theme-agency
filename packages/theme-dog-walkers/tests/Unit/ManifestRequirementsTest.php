<?php

declare(strict_types=1);

use Capell\ThemeStudio\DogWalkers\DogWalkersThemeServiceProvider;

it('declares the required first-party theme manifest boundaries', function (): void {
    $manifest = dogWalkersThemeManifest();
    $composer = dogWalkersThemeComposer();
    $database = $manifest['database'] ?? null;
    $providers = $manifest['providers'] ?? null;
    $marketplace = $manifest['marketplace'] ?? null;

    throw_unless(is_array($database), RuntimeException::class, 'Theme Dog Walkers database manifest data must be an array.');

    throw_unless(is_array($providers), RuntimeException::class, 'Theme Dog Walkers providers manifest data must be an array.');

    throw_unless(is_array($marketplace), RuntimeException::class, 'Theme Dog Walkers marketplace manifest data must be an array.');

    $runtimeProviders = $providers['runtime'] ?? null;

    throw_unless(is_array($runtimeProviders), RuntimeException::class, 'Theme Dog Walkers runtime providers must be an array.');

    expect($manifest['themeKey'])->toBe('dog-walkers')
        ->and($manifest['extends'])->toBe('default')
        ->and(data_get($manifest, 'dependencies.requires'))->toContain('capell-app/frontend')
        ->and($manifest['surfaces'])->toBe(['frontend', 'console'])
        ->and($database['migrations'])->toBeFalse()
        ->and($runtimeProviders)->toContain(DogWalkersThemeServiceProvider::class)
        ->and($marketplace['summary'])->toBe('A trust-led Capell theme for dog walkers and pet-care teams, built around walk enquiries, service-area confidence, safety proof, and warm neighbourhood presentation.')
        ->and($marketplace['description'])->toContain('Theme Dog Walkers helps independent walkers, sitters, and small pet-care teams turn reassured pet parents into enquiries.')
        ->and($composer['description'])->toBe($marketplace['summary']);
});

it('declares only marketplace screenshots that exist in the package', function (): void {
    $manifest = dogWalkersThemeManifest();
    $marketplace = $manifest['marketplace'] ?? null;

    throw_unless(is_array($marketplace), RuntimeException::class, 'Theme Dog Walkers marketplace manifest data must be an array.');

    $screenshots = $marketplace['screenshots'] ?? null;

    throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Dog Walkers marketplace screenshots must be an array.');

    foreach ($screenshots as $screenshot) {
        throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Dog Walkers marketplace screenshots must define string paths.');

        expect(file_exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});

/**
 * @return array<string, mixed>
 */
function dogWalkersThemeManifest(): array
{
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Theme Dog Walkers manifest must decode to an array.');

    return $manifest;
}

/**
 * @return array<string, mixed>
 */
function dogWalkersThemeComposer(): array
{
    $composer = json_decode(
        (string) file_get_contents(__DIR__ . '/../../composer.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($composer), RuntimeException::class, 'Theme Dog Walkers composer data must decode to an array.');

    return $composer;
}
