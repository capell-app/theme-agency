<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('declares the recruitment-jobs theme manifest contract', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

    expect($manifest['manifest-version'])->toBe(3)
        ->and($manifest['kind'])->toBe('theme')
        ->and($manifest['themeKey'])->toBe('recruitment-jobs')
        ->and($manifest['extends'])->toBe('default')
        ->and(data_get($manifest, 'commands.demo'))->toBe('capell:theme-recruitment-jobs-demo')
        ->and($manifest['capabilities'])->toContain('theme-recruitment-jobs', 'theme-recruitment-jobs-frontend')
        ->and(data_get($manifest, 'security.publicOutput.cacheSafe'))->toBeTrue();

    $screenshots = data_get($manifest, 'marketplace.screenshots', []);

    foreach (is_array($screenshots) ? $screenshots : [] as $screenshot) {
        $screenshotPath = is_array($screenshot) ? ($screenshot['path'] ?? null) : null;

        expect(File::exists(__DIR__ . '/../../' . (is_string($screenshotPath) ? $screenshotPath : '')))->toBeTrue();
    }
});
