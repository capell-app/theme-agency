<?php

declare(strict_types=1);

it('does not advertise cache dependency blocking without an implementation', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['capabilities'])->not->toContain('cache-blocking')
        ->and($manifest['performance']['cacheSafety']['cacheable'])->toBeFalse()
        ->and($manifest['performance']['cacheSafety']['invalidationSources'])->toBe([]);
});
