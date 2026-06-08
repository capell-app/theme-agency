<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;

it('keeps declared marketplace screenshots backed by package files', function (): void {
    $invalid = [];

    foreach (manifest_truth_package_manifests() as $manifestPath => $entry) {
        $screenshots = data_get($entry['manifest'], 'marketplace.screenshots');

        if ($screenshots === null) {
            continue;
        }

        if (! is_array($screenshots)) {
            $invalid[$manifestPath][] = 'marketplace.screenshots must be an array when declared';

            continue;
        }

        foreach ($screenshots as $index => $screenshot) {
            $path = is_array($screenshot)
                ? ($screenshot['path'] ?? null)
                : (is_string($screenshot) ? $screenshot : null);

            if (! is_string($path) || $path === '') {
                $invalid[$manifestPath][] = sprintf('marketplace.screenshots.%d.path must be a non-empty string', $index);

                continue;
            }

            if (! is_file($entry['directory'] . DIRECTORY_SEPARATOR . $path)) {
                $invalid[$manifestPath][] = sprintf('marketplace.screenshots.%d.path file is missing [%s]', $index, $path);
            }
        }
    }

    expect($invalid)->toBe(
        [],
        'Marketplace screenshots must point at committed package files: ' .
        json_encode($invalid, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

it('backs console capabilities with at least one declared package command', function (): void {
    $invalid = [];

    foreach (manifest_truth_package_manifests() as $manifestPath => $entry) {
        $capabilities = array_values(array_filter(
            $entry['manifest']['capabilities'] ?? [],
            static fn (mixed $capability): bool => is_string($capability) && str_contains($capability, 'console'),
        ));

        if ($capabilities === []) {
            continue;
        }

        $commands = manifest_truth_declared_commands($entry['manifest']['commands'] ?? []);

        if ($commands === []) {
            $invalid[$manifestPath][] = sprintf(
                'console capabilities [%s] require a declared command',
                implode(', ', $capabilities),
            );
        }
    }

    expect($invalid)->toBe(
        [],
        'Manifest console capabilities must be backed by command metadata: ' .
        json_encode($invalid, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

it('declares health checks that resolve to the Diagnostics health contract', function (): void {
    $invalid = [];

    foreach (manifest_truth_package_manifests() as $manifestPath => $entry) {
        $healthChecks = $entry['manifest']['healthChecks'] ?? [];

        if (! is_array($healthChecks)) {
            $invalid[$manifestPath][] = 'healthChecks must be an array when declared';

            continue;
        }

        foreach ($healthChecks as $index => $healthCheck) {
            if (! is_array($healthCheck)) {
                $invalid[$manifestPath][] = sprintf('healthChecks.%d must be an object', $index);

                continue;
            }

            $className = $healthCheck['class'] ?? null;

            if (! is_string($className) || $className === '') {
                $invalid[$manifestPath][] = sprintf('healthChecks.%d.class must be a non-empty string', $index);

                continue;
            }

            if (! class_exists($className)) {
                $invalid[$manifestPath][] = sprintf('healthChecks.%d.class must exist [%s]', $index, $className);

                continue;
            }

            if (! is_subclass_of($className, ChecksExtensionHealth::class)) {
                $invalid[$manifestPath][] = sprintf('healthChecks.%d.class must implement ChecksExtensionHealth [%s]', $index, $className);
            }
        }
    }

    expect($invalid)->toBe(
        [],
        'Declared health checks must resolve to Diagnostics-compatible classes: ' .
        json_encode($invalid, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

it('keeps theme manifest parent metadata aligned with provider definitions', function (): void {
    $invalid = [];
    $manifests = manifest_truth_package_manifests();
    $manifestsByName = [];

    foreach ($manifests as $entry) {
        $name = $entry['manifest']['name'] ?? null;

        if (is_string($name) && $name !== '') {
            $manifestsByName[$name] = $entry['manifest'];
        }
    }

    foreach ($manifests as $manifestPath => $entry) {
        if (($entry['manifest']['kind'] ?? null) !== 'theme') {
            continue;
        }

        foreach (manifest_truth_provider_classes($entry['manifest']) as $providerClass) {
            if (! class_exists($providerClass) || ! method_exists($providerClass, 'definition')) {
                continue;
            }

            $definition = $providerClass::definition();

            if (! $definition instanceof ThemeDefinitionData) {
                continue;
            }

            $manifestExtends = $entry['manifest']['extends'] ?? null;
            $resolvedExtends = manifest_truth_resolved_theme_extends($manifestExtends, $manifestsByName);

            if ($resolvedExtends !== $definition->extends) {
                $invalid[$manifestPath][] = sprintf(
                    '%s manifest extends [%s] resolves to [%s], but provider definition extends [%s]',
                    $providerClass,
                    is_string($manifestExtends) ? $manifestExtends : 'null',
                    is_string($resolvedExtends) ? $resolvedExtends : 'null',
                    $definition->extends ?? 'null',
                );
            }
        }
    }

    expect($invalid)->toBe(
        [],
        'Theme manifest parent metadata must resolve to the provider runtime parent key: ' .
        json_encode($invalid, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    );
});

/**
 * @return array<string, array{directory: string, manifest: array<string, mixed>}>
 */
function manifest_truth_package_manifests(): array
{
    $root = dirname(__DIR__, 2);
    $manifests = [];

    foreach (glob($root . '/packages/*/capell.json') ?: [] as $manifestPath) {
        $relativePath = ltrim(str_replace($root, '', $manifestPath), DIRECTORY_SEPARATOR);
        $manifests[$relativePath] = [
            'directory' => dirname($manifestPath),
            'manifest' => capell_json_file_array($manifestPath),
        ];
    }

    ksort($manifests);

    return $manifests;
}

/**
 * @return list<string>
 */
function manifest_truth_declared_commands(mixed $commands): array
{
    if (! is_array($commands)) {
        return [];
    }

    $declared = [];

    foreach ($commands as $key => $value) {
        if (is_string($key) && str_ends_with($key, 'Params')) {
            continue;
        }

        if (is_string($value) && $value !== '') {
            $declared[] = $value;

            continue;
        }

        if (! is_array($value)) {
            continue;
        }

        foreach ($value as $nestedValue) {
            if (is_string($nestedValue) && $nestedValue !== '') {
                $declared[] = $nestedValue;
            }
        }
    }

    return array_values(array_unique($declared));
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<class-string>
 */
function manifest_truth_provider_classes(array $manifest): array
{
    $providers = [];

    foreach ($manifest['providers'] ?? [] as $bucket) {
        if (! is_array($bucket)) {
            continue;
        }

        foreach ($bucket as $providerClass) {
            if (is_string($providerClass) && $providerClass !== '') {
                $providers[] = $providerClass;
            }
        }
    }

    return array_values(array_unique($providers));
}

/**
 * @param  array<string, array<string, mixed>>  $manifestsByName
 */
function manifest_truth_resolved_theme_extends(mixed $manifestExtends, array $manifestsByName): ?string
{
    if ($manifestExtends === null) {
        return null;
    }

    if (! is_string($manifestExtends) || $manifestExtends === '') {
        return null;
    }

    $parentManifest = $manifestsByName[$manifestExtends] ?? null;
    $parentThemeKey = is_array($parentManifest) ? ($parentManifest['themeKey'] ?? null) : null;

    return is_string($parentThemeKey) && $parentThemeKey !== ''
        ? $parentThemeKey
        : $manifestExtends;
}
