<?php

declare(strict_types=1);

use Capell\ThemeStudio\Education\EducationThemeServiceProvider;

it('declares the required first-party theme manifest boundaries', function (): void {
    $manifest = educationThemeManifest();
    $database = $manifest['database'] ?? null;
    $providers = $manifest['providers'] ?? null;

    throw_unless(is_array($database), RuntimeException::class, 'Theme Education database manifest data must be an array.');

    throw_unless(is_array($providers), RuntimeException::class, 'Theme Education providers manifest data must be an array.');

    $runtimeProviders = $providers['runtime'] ?? null;

    throw_unless(is_array($runtimeProviders), RuntimeException::class, 'Theme Education runtime providers must be an array.');

    expect($manifest['themeKey'])->toBe('education')
        ->and($manifest['extends'])->toBe('capell-app/foundation-theme')
        ->and($manifest['surfaces'])->toBe(['frontend', 'console'])
        ->and(data_get($manifest, 'dependencies.requires'))->toContain('capell-app/foundation-theme')
        ->and($database['migrations'])->toBeFalse()
        ->and($runtimeProviders)->toContain(EducationThemeServiceProvider::class);
});

it('uses buyer-facing marketplace copy and committed real preview assets', function (): void {
    $manifest = educationThemeManifest();
    $marketplace = $manifest['marketplace'] ?? null;

    throw_unless(is_array($marketplace), RuntimeException::class, 'Theme Education marketplace manifest data must be an array.');

    $screenshots = $marketplace['screenshots'] ?? null;

    throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Education marketplace screenshots must be an array.');

    $screenshotPaths = [];

    foreach ($screenshots as $screenshot) {
        throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Education marketplace screenshots must define string paths.');

        $screenshotPaths[] = $screenshot['path'];
    }

    expect($marketplace['summary'])->toBe('A polished, course-first theme for schools, academies, and training providers — turning programme discovery, faculty trust, open days, and enrolment into one coherent learner journey.')
        ->and($marketplace['description'])->toBe("Theme Education gives schools, course providers, and training teams a complete learning-pathway frontend without commissioning a custom build. Purpose-shaped sections cover course catalogues, instructor and mentor profiles, learning outcomes, open days, resources, FAQs, and a guided enrolment call-to-action. It integrates optionally with Capell Events for open-day calendars, Form Builder for applications and enquiries, and Blog for learning resources — degrading gracefully when those aren't installed. Built on Foundation Theme with brand-token theming, an accessible skip link and focus states, and zero database impact, so editors compose education pages through the normal Layout Builder workflow.")
        ->and($screenshotPaths)->toBe([
            'docs/assets/marketplace/extension-card.jpg',
            'docs/assets/marketplace/hero-desktop.jpg',
            'docs/assets/marketplace/hero-mobile.jpg',
        ]);

    foreach ($screenshotPaths as $screenshotPath) {
        expect(file_exists(__DIR__ . '/../../' . $screenshotPath))->toBeTrue();
    }
});

/**
 * @return array<string, mixed>
 */
function educationThemeManifest(): array
{
    return capell_json_file_array(__DIR__ . '/../../capell.json');
}
