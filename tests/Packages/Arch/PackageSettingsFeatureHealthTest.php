<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\Support\PropertyReflector;
use Symfony\Component\Finder\Finder;

it('all package settings classes can be reflected by Laravel Settings', function (): void {
    $failures = [];

    foreach (packageSettingsClasses() as $settingsClass) {
        if (! is_subclass_of($settingsClass, Settings::class)) {
            continue;
        }

        $reflectionClass = new ReflectionClass($settingsClass);

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            try {
                PropertyReflector::resolveType($property);
            } catch (Throwable $exception) {
                $failures[] = sprintf(
                    '%s::$%s failed Laravel Settings reflection: %s',
                    $settingsClass,
                    $property->getName(),
                    $exception->getMessage(),
                );
            }
        }
    }

    sort($failures);

    expect($failures)->toBe(
        [],
        'Package settings classes must be safe for Laravel Settings to reflect during admin/schema boot:' .
        "\n" . json_encode($failures, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

/**
 * @return list<class-string>
 */
function packageSettingsClasses(): array
{
    $autoloadMap = packagePsr4AutoloadMap();
    $settingsFiles = (new Finder)
        ->in(__DIR__ . '/../../../packages')
        ->path('/src\/Settings/')
        ->name('*.php');

    $classes = [];

    foreach ($settingsFiles as $settingsFile) {
        $path = $settingsFile->getRealPath();

        if ($path === false) {
            continue;
        }

        foreach ($autoloadMap as $namespace => $basePath) {
            $realBasePath = realpath($basePath);

            if ($realBasePath === false || ! str_starts_with($path, $realBasePath . DIRECTORY_SEPARATOR)) {
                continue;
            }

            $relativeClass = substr($path, strlen($realBasePath) + 1, -4);
            $class = $namespace . str_replace(DIRECTORY_SEPARATOR, '\\', $relativeClass);

            if (class_exists($class)) {
                $classes[] = $class;
            }

            continue 2;
        }
    }

    sort($classes);

    return array_values(array_unique($classes));
}

/**
 * @return array<string, string>
 */
function packagePsr4AutoloadMap(): array
{
    $composer = json_decode(
        file_get_contents(__DIR__ . '/../../../composer.json') ?: '{}',
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    $psr4 = $composer['autoload']['psr-4'] ?? [];
    $map = [];

    foreach ($psr4 as $namespace => $path) {
        if (! is_string($namespace) || ! is_string($path) || ! str_starts_with($path, 'packages/')) {
            continue;
        }

        $map[$namespace] = __DIR__ . '/../../../' . $path;
    }

    uksort($map, static fn (string $first, string $second): int => strlen($second) <=> strlen($first));

    return $map;
}
