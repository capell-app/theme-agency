<?php

declare(strict_types=1);

it('does not advertise cache dependency blocking without an implementation', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

    expect(data_get($manifest, 'capabilities'))->not->toContain('cache-blocking')
        ->and(data_get($manifest, 'performance.cacheSafety.cacheable'))->toBeFalse()
        ->and(data_get($manifest, 'performance.cacheSafety.invalidationSources'))->toBe([]);
});
