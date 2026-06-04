<?php

declare(strict_types=1);

use Capell\ThemeStudio\Education\EducationThemeServiceProvider;

it('declares the required first-party theme manifest boundaries', function (): void {
    $contents = file_get_contents(__DIR__ . '/../../capell.json');
    $manifest = json_decode($contents === false ? '{}' : $contents, true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['themeKey'])->toBe('education')
        ->and($manifest['extends'])->toBe('capell-app/foundation-theme')
        ->and($manifest['database']['migrations'])->toBeFalse()
        ->and($manifest['providers']['runtime'])->toContain(EducationThemeServiceProvider::class);
});

it('uses buyer-facing marketplace copy and committed real preview assets', function (): void {
    $contents = file_get_contents(__DIR__ . '/../../capell.json');
    $manifest = json_decode($contents === false ? '{}' : $contents, true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['marketplace']['summary'])->toBe('A polished, course-first theme for schools, academies, and training providers — turning programme discovery, faculty trust, open days, and enrolment into one coherent learner journey.')
        ->and($manifest['marketplace']['description'])->toBe("Theme Education gives schools, course providers, and training teams a complete learning-pathway frontend without commissioning a custom build. Purpose-shaped sections cover course catalogues, instructor and mentor profiles, learning outcomes, open days, resources, FAQs, and a guided enrolment call-to-action. It integrates optionally with Capell Events for open-day calendars, Form Builder for applications and enquiries, and Blog for learning resources — degrading gracefully when those aren't installed. Built on Foundation Theme with brand-token theming, an accessible skip link and focus states, and zero database impact, so editors compose education pages through the normal Layout Builder workflow.")
        ->and(array_column($manifest['marketplace']['screenshots'], 'path'))->toBe([
            'docs/assets/marketplace/extension-card.jpg',
            'docs/assets/marketplace/hero-desktop.jpg',
            'docs/assets/marketplace/hero-mobile.jpg',
        ]);

    collect($manifest['marketplace']['screenshots'])
        ->each(fn (array $screenshot): mixed => expect(file_exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue());
});
