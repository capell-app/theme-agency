<?php

declare(strict_types=1);

use Capell\ThemeStudio\Nonprofit\NonprofitThemeServiceProvider;
use Illuminate\Support\Facades\File;

it('declares the required first-party theme manifest boundaries', function (): void {
    $manifest = json_decode(File::get(__DIR__ . '/../../capell.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['themeKey'])->toBe('nonprofit')
        ->and($manifest['extends'])->toBe('capell-app/foundation-theme')
        ->and($manifest['database']['migrations'])->toBeFalse()
        ->and($manifest['providers']['runtime'])->toContain(NonprofitThemeServiceProvider::class);
});
