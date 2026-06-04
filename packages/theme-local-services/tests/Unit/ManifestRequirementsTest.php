<?php

declare(strict_types=1);

use Capell\ThemeStudio\LocalServices\LocalServicesThemeServiceProvider;

it('declares the required first-party theme manifest boundaries', function (): void {
    $contents = file_get_contents(__DIR__ . '/../../capell.json');
    $manifest = json_decode($contents === false ? '{}' : $contents, true, flags: JSON_THROW_ON_ERROR);
    $composerContents = file_get_contents(__DIR__ . '/../../composer.json');
    $composer = json_decode($composerContents === false ? '{}' : $composerContents, true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['themeKey'])->toBe('local-services')
        ->and($manifest['extends'])->toBe('capell-app/foundation-theme')
        ->and($manifest['database']['migrations'])->toBeFalse()
        ->and($manifest['providers']['runtime'])->toContain(LocalServicesThemeServiceProvider::class)
        ->and($manifest['marketplace']['summary'])->toBe('A conversion-first Capell theme for local trades, clinics, and service businesses — built around quote requests, service-area coverage, and click-to-call trust.')
        ->and($manifest['marketplace']['description'])->toBe('Theme Local Services turns visitors into booked jobs. It ships hero, services, service-area, locality-proof, quote-estimator, case-study, and contact sections tuned for plumbers, electricians, salons, cleaners, and clinics, with a teal/amber palette and a quote desk front-and-centre. Optional Form Builder and Blog integrations upgrade the enquiry form and resources feed when those packages are installed, and the theme inherits foundation navigation, footer, and SEO. Drop in your services and coverage areas and launch a credible local-business site in minutes.')
        ->and($composer['description'])->toBe($manifest['marketplace']['summary']);
});

it('declares only marketplace screenshots that exist in the package', function (): void {
    $contents = file_get_contents(__DIR__ . '/../../capell.json');
    $manifest = json_decode($contents === false ? '{}' : $contents, true, flags: JSON_THROW_ON_ERROR);

    foreach ($manifest['marketplace']['screenshots'] as $screenshot) {
        expect(is_file(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});
