<?php

declare(strict_types=1);

use Capell\ThemeStudio\LocalServices\LocalServicesThemeServiceProvider;

it('declares the required first-party theme manifest boundaries', function (): void {
    $manifest = localServicesThemeManifest();
    $composer = localServicesThemeComposer();
    $database = $manifest['database'] ?? null;
    $providers = $manifest['providers'] ?? null;
    $marketplace = $manifest['marketplace'] ?? null;

    throw_unless(is_array($database), RuntimeException::class, 'Theme Local Services database manifest data must be an array.');

    throw_unless(is_array($providers), RuntimeException::class, 'Theme Local Services providers manifest data must be an array.');

    throw_unless(is_array($marketplace), RuntimeException::class, 'Theme Local Services marketplace manifest data must be an array.');

    $runtimeProviders = $providers['runtime'] ?? null;

    throw_unless(is_array($runtimeProviders), RuntimeException::class, 'Theme Local Services runtime providers must be an array.');

    expect($manifest['themeKey'])->toBe('local-services')
        ->and($manifest['extends'])->toBe('default')
        ->and(data_get($manifest, 'dependencies.requires'))->toContain('capell-app/frontend')
        ->and($manifest['surfaces'])->toBe(['frontend', 'console'])
        ->and($database['migrations'])->toBeFalse()
        ->and($runtimeProviders)->toContain(LocalServicesThemeServiceProvider::class)
        ->and($marketplace['summary'])->toBe('A conversion-first Capell theme for local trades, clinics, and service businesses — built around quote requests, service-area coverage, and click-to-call trust.')
        ->and($marketplace['description'])->toBe('Theme Local Services turns visitors into booked jobs. It ships hero, services, service-area, locality-proof, quote-estimator, case-study, and contact sections tuned for plumbers, electricians, salons, cleaners, and clinics, with a teal/amber palette and a quote desk front-and-centre. Optional Form Builder and Blog integrations upgrade the enquiry form and resources feed when those packages are installed, and the theme inherits built-in default navigation, footer, and SEO fallbacks. Drop in your services and coverage areas and launch a credible local-business site in minutes.')
        ->and($composer['description'])->toBe($marketplace['summary']);
});

it('declares only marketplace screenshots that exist in the package', function (): void {
    $manifest = localServicesThemeManifest();
    $marketplace = $manifest['marketplace'] ?? null;

    throw_unless(is_array($marketplace), RuntimeException::class, 'Theme Local Services marketplace manifest data must be an array.');

    $screenshots = $marketplace['screenshots'] ?? null;

    throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Local Services marketplace screenshots must be an array.');

    foreach ($screenshots as $screenshot) {
        throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Local Services marketplace screenshots must define string paths.');

        expect(file_exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});

/**
 * @return array<string, mixed>
 */
function localServicesThemeManifest(): array
{
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Theme Local Services manifest must decode to an array.');

    return $manifest;
}

/**
 * @return array<string, mixed>
 */
function localServicesThemeComposer(): array
{
    $composer = json_decode(
        (string) file_get_contents(__DIR__ . '/../../composer.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($composer), RuntimeException::class, 'Theme Local Services composer data must decode to an array.');

    return $composer;
}
