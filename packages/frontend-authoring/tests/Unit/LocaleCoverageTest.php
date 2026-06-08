<?php

declare(strict_types=1);

/**
 * @param  array<string, mixed>  $translations
 * @return list<string>
 */
function frontendAuthoringLocaleKeys(array $translations, string $prefix = ''): array
{
    $keys = [];

    foreach ($translations as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;

        if (is_array($value)) {
            array_push($keys, ...frontendAuthoringLocaleKeys(frontendAuthoringLocaleMap($value), $path));

            continue;
        }

        $keys[] = $path;
    }

    sort($keys);

    return $keys;
}

/**
 * @return array<string, mixed>
 */
function frontendAuthoringLocaleMap(mixed $value): array
{
    if (! is_array($value)) {
        return [];
    }

    $map = [];

    foreach ($value as $key => $item) {
        if (is_string($key)) {
            $map[$key] = $item;
        }
    }

    return $map;
}

it('keeps the Spanish authoring locale aligned with English keys', function (): void {
    $english = require __DIR__ . '/../../resources/lang/en/authoring.php';
    $spanish = require __DIR__ . '/../../resources/lang/es/authoring.php';

    expect(frontendAuthoringLocaleKeys(frontendAuthoringLocaleMap($spanish)))->toBe(frontendAuthoringLocaleKeys(frontendAuthoringLocaleMap($english)));
});
