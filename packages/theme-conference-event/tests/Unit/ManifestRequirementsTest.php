<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('declares the conference-event theme manifest contract', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

    expect($manifest['manifest-version'])->toBe(3)
        ->and($manifest['kind'])->toBe('theme')
        ->and($manifest['themeKey'])->toBe('conference-event')
        ->and($manifest['extends'])->toBe('default')
        ->and(data_get($manifest, 'commands.demo'))->toBe('capell:theme-conference-event-demo')
        ->and($manifest['capabilities'])->toContain('theme-conference-event', 'theme-conference-event-frontend')
        ->and(data_get($manifest, 'security.publicOutput.cacheSafe'))->toBeTrue();

    foreach (data_get($manifest, 'marketplace.screenshots', []) as $screenshot) {
        expect(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});
