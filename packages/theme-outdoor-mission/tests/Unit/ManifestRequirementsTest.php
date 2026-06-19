<?php

declare(strict_types=1);

it('declares the outdoor-mission theme manifest contract', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

    expect($manifest['manifest-version'])->toBe(3)
        ->and($manifest['kind'])->toBe('theme')
        ->and($manifest['themeKey'])->toBe('outdoor-mission')
        ->and($manifest['extends'])->toBe('default')
        ->and(data_get($manifest, 'commands.demo'))->toBe('capell:theme-outdoor-mission-demo')
        ->and($manifest['capabilities'])->toContain('theme-outdoor-mission', 'theme-outdoor-mission-frontend')
        ->and(data_get($manifest, 'security.publicOutput.cacheSafe'))->toBeTrue();

    foreach (data_get($manifest, 'marketplace.screenshots', []) as $screenshot) {
        expect(is_file(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
    }
});
