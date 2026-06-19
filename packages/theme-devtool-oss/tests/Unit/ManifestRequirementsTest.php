<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('declares the devtool-oss theme manifest contract', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

    expect($manifest['manifest-version'])->toBe(3)
        ->and($manifest['kind'])->toBe('theme')
        ->and($manifest['themeKey'])->toBe('devtool-oss')
        ->and($manifest['extends'])->toBe('default')
        ->and(data_get($manifest, 'commands.demo'))->toBe('capell:theme-devtool-oss-demo')
        ->and($manifest['capabilities'])->toContain('theme-devtool-oss', 'theme-devtool-oss-frontend')
        ->and(data_get($manifest, 'security.publicOutput.cacheSafe'))->toBeTrue();

    foreach (data_get($manifest, 'marketplace.screenshots', []) as $screenshot) {
        expect(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});
