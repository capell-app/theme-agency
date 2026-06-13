<?php

declare(strict_types=1);

use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;

/**
 * @return array<string, array{directory: string, manifest: array<string, mixed>}>
 */
function capell_theme_manifest_entries(?string $root = null): array
{
    $root ??= dirname(__DIR__, 3);
    $manifests = [];

    foreach (glob($root . '/packages/*/capell.json') ?: [] as $manifestPath) {
        $manifest = capell_json_file_array($manifestPath);

        if (($manifest['kind'] ?? null) !== 'theme') {
            continue;
        }

        $relativePath = ltrim(str_replace($root, '', $manifestPath), DIRECTORY_SEPARATOR);
        $manifests[$relativePath] = [
            'directory' => dirname($manifestPath),
            'manifest' => $manifest,
        ];
    }

    ksort($manifests);

    return $manifests;
}

/**
 * @param  array<string, array{directory: string, manifest: array<string, mixed>}>  $entries
 * @return array<string, array<string, mixed>>
 */
function capell_theme_manifests_by_name(array $entries): array
{
    $manifestsByName = [];

    foreach ($entries as $entry) {
        $name = $entry['manifest']['name'] ?? null;

        if (is_string($name) && $name !== '') {
            $manifestsByName[$name] = $entry['manifest'];
        }
    }

    return $manifestsByName;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<class-string>
 */
function capell_theme_manifest_provider_classes(array $manifest): array
{
    $providers = [];
    $providerBuckets = $manifest['providers'] ?? [];

    if (! is_array($providerBuckets)) {
        return [];
    }

    foreach ($providerBuckets as $bucket) {
        if (! is_array($bucket)) {
            continue;
        }

        foreach ($bucket as $providerClass) {
            if (is_string($providerClass) && class_exists($providerClass)) {
                $providers[$providerClass] = $providerClass;
            }
        }
    }

    return array_values($providers);
}

/**
 * @param  array<string, array<string, mixed>>  $manifestsByName
 */
function capell_theme_manifest_resolved_extends(mixed $manifestExtends, array $manifestsByName): ?string
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

/**
 * @param  array<string, mixed>  $manifest
 * @param  array<string, array<string, mixed>>  $manifestsByName
 * @return list<string>
 */
function capell_theme_manifest_definition_issues(string $providerClass, array $manifest, ThemeDefinitionData $definition, array $manifestsByName): array
{
    $issues = [];
    $manifestName = $manifest['name'] ?? null;
    $manifestThemeKey = $manifest['themeKey'] ?? null;
    $manifestExtends = $manifest['extends'] ?? null;
    $resolvedExtends = capell_theme_manifest_resolved_extends($manifestExtends, $manifestsByName);

    if ($definition->package !== $manifestName) {
        $issues[] = sprintf(
            '%s provider package [%s] must match manifest name [%s]',
            $providerClass,
            $definition->package,
            is_string($manifestName) ? $manifestName : 'null',
        );
    }

    if ($definition->key !== $manifestThemeKey) {
        $issues[] = sprintf(
            '%s provider theme key [%s] must match manifest themeKey [%s]',
            $providerClass,
            $definition->key,
            is_string($manifestThemeKey) ? $manifestThemeKey : 'null',
        );
    }

    if ($resolvedExtends !== $definition->extends) {
        $issues[] = sprintf(
            '%s manifest extends [%s] resolves to [%s], but provider definition extends [%s]',
            $providerClass,
            is_string($manifestExtends) ? $manifestExtends : 'null',
            is_string($resolvedExtends) ? $resolvedExtends : 'null',
            $definition->extends ?? 'null',
        );
    }

    return $issues;
}
