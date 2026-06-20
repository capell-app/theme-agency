<?php

declare(strict_types=1);

it('declares the dark-product-system theme manifest contract', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

    expect($manifest['manifest-version'])->toBe(3)
        ->and($manifest['kind'])->toBe('theme')
        ->and($manifest['themeKey'])->toBe('dark-product-system')
        ->and($manifest['extends'])->toBe('default')
        ->and(data_get($manifest, 'commands.demo'))->toBe('capell:theme-dark-product-system-demo')
        ->and($manifest['capabilities'])->toContain('theme-dark-product-system', 'theme-dark-product-system-frontend')
        ->and(data_get($manifest, 'security.publicOutput.cacheSafe'))->toBeTrue();

    $screenshots = data_get($manifest, 'marketplace.screenshots', []);

    foreach (is_array($screenshots) ? $screenshots : [] as $screenshot) {
        $screenshotPath = is_array($screenshot) ? ($screenshot['path'] ?? null) : null;

        expect(is_file(__DIR__ . '/../../' . (is_string($screenshotPath) ? $screenshotPath : '')))->toBeTrue();
    }
});
