<?php

declare(strict_types=1);

it('runs the package doctor command successfully', function (): void {
    capell_artisan('capell:events-doctor')
        ->assertSuccessful();
});

it('declares the package doctor command in the events manifest', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['commands']['doctor'] ?? null)->toBe('capell:events-doctor');
});
