<?php

declare(strict_types=1);

function frontendAuthoringLocaleKeys(array $translations, string $prefix = ''): array
{
    $keys = [];

    foreach ($translations as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix . '.' . (string) $key;

        if (is_array($value)) {
            array_push($keys, ...frontendAuthoringLocaleKeys($value, $path));

            continue;
        }

        $keys[] = $path;
    }

    sort($keys);

    return $keys;
}

it('keeps the Spanish authoring locale aligned with English keys', function (): void {
    $english = require __DIR__ . '/../../resources/lang/en/authoring.php';
    $spanish = require __DIR__ . '/../../resources/lang/es/authoring.php';

    expect(frontendAuthoringLocaleKeys($spanish))->toBe(frontendAuthoringLocaleKeys($english));
});
