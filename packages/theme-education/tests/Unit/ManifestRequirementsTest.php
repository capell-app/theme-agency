<?php

declare(strict_types=1);

use Capell\ThemeStudio\Education\EducationThemeServiceProvider;

it('declares the required first-party theme manifest boundaries', function (): void {
    $manifest = json_decode(file_get_contents(__DIR__ . '/../../capell.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['themeKey'])->toBe('education')
        ->and($manifest['extends'])->toBe('capell-app/foundation-theme')
        ->and($manifest['database']['migrations'])->toBeFalse()
        ->and($manifest['providers']['runtime'])->toContain(EducationThemeServiceProvider::class);
});
