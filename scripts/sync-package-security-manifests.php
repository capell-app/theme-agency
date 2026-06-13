<?php

declare(strict_types=1);

require_once __DIR__ . '/audit-package-security.php';

$root = dirname(__DIR__);
$updated = [];

foreach (capell_security_package_directories($root) as $slug => $packagePath) {
    $manifestPath = $packagePath . DIRECTORY_SEPARATOR . 'capell.json';

    if (! is_file($manifestPath)) {
        continue;
    }

    $contents = (string) file_get_contents($manifestPath);
    try {
        $manifest = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        $contents = capell_sync_strip_security_section($contents);
        $manifest = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
    }

    if (! is_array($manifest)) {
        continue;
    }

    $security = capell_sync_security_section($slug, $packagePath, $manifest);
    $securityJson = json_encode($security, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $securityJson = capell_sync_indent_json((string) $securityJson, 4);
    $replacement = '    "security": ' . ltrim($securityJson) . ",\n";

    if (preg_match('/    "security": \{.*?\n    \},\n    "performance": \{/s', $contents) === 1) {
        $newContents = (string) preg_replace_callback(
            '/    "security": \{.*?\n    \},\n    "performance": \{/s',
            static fn (): string => $replacement . '    "performance": {',
            $contents,
            1,
        );
    } else {
        $newContents = (string) preg_replace_callback(
            '/    "performance": \{/',
            static fn (): string => $replacement . '    "performance": {',
            $contents,
            1,
        );
    }

    if ($newContents === $contents) {
        continue;
    }

    file_put_contents($manifestPath, $newContents);
    $updated[] = 'packages/' . $slug . '/capell.json';
}

sort($updated);

foreach ($updated as $path) {
    fwrite(STDOUT, $path . PHP_EOL);
}

/**
 * @param  array<string, mixed>  $manifest
 * @return array<string, mixed>
 */
function capell_sync_security_section(string $slug, string $packagePath, array $manifest): array
{
    $routes = capell_security_route_records($packagePath);
    $routeNames = capell_sync_route_names($routes);
    $csrfExemptRoutes = capell_sync_route_names(array_values(array_filter($routes, static fn (array $route): bool => $route['csrfExempt'])));
    $signedRoutes = capell_sync_route_names(array_values(array_filter($routes, static fn (array $route): bool => $route['signed'])));
    $tokenizedRoutes = capell_sync_route_names(array_values(array_filter($routes, static fn (array $route): bool => $route['tokenized'])));
    $webhookRoutes = capell_sync_route_names(array_values(array_filter($routes, static fn (array $route): bool => $route['webhook'])));
    $throttledRoutes = capell_sync_route_names(array_values(array_filter($routes, static fn (array $route): bool => $route['throttled'])));
    $httpClients = capell_security_http_client_classes($packagePath);

    return [
        'riskTier' => capell_sync_risk_tier($slug, $manifest, $routes, $httpClients),
        'publicSurface' => [
            'routeNames' => $routeNames,
            'auth' => capell_sync_auth_mode($routes),
            'csrfExemptRoutes' => $csrfExemptRoutes,
            'signedRoutes' => $signedRoutes,
            'tokenizedRoutes' => $tokenizedRoutes,
            'webhookRoutes' => $webhookRoutes,
            'throttledRoutes' => $throttledRoutes,
        ],
        'sensitiveData' => [
            'encryptedFields' => capell_security_encrypted_fields($packagePath),
            'hashedTokenFields' => capell_security_hashed_token_fields($packagePath),
            'redactedOutputClasses' => capell_security_redacted_output_classes($packagePath),
            'plaintextJustifications' => capell_security_plaintext_justification_strings(
                capell_security_plaintext_sensitive_field_justifications($packagePath),
            ),
        ],
        'publicOutput' => [
            'cacheSafe' => ($manifest['performance']['cacheSafety']['sensitiveOutput'] ?? false) === false,
            'forbidAuthoringSurface' => true,
            'forbidSecrets' => true,
            'forbidPublicBladeQueries' => true,
        ],
        'externalHttpClients' => [
            'requiresTimeouts' => $httpClients !== [],
            'requiresSecretRedaction' => $httpClients !== [],
            'clients' => $httpClients,
        ],
        'adminSurface' => [
            'authorization' => capell_sync_admin_authorization($manifest, $packagePath),
            'permissions' => capell_security_string_list($manifest['permissions'] ?? []),
        ],
    ];
}

/**
 * @param  list<array{name: string}>  $routes
 * @return list<string>
 */
function capell_sync_route_names(array $routes): array
{
    $names = array_values(array_unique(array_map(static fn (array $route): string => $route['name'], $routes)));
    sort($names);

    return $names;
}

/**
 * @param  list<array<string, mixed>>  $routes
 * @param  list<string>  $httpClients
 * @param  array<string, mixed>  $manifest
 */
function capell_sync_risk_tier(string $slug, array $manifest, array $routes, array $httpClients): string
{
    if (in_array($slug, CAPELL_SECURITY_CRITICAL_PACKAGES, true)) {
        return 'critical';
    }

    if (in_array($slug, CAPELL_SECURITY_SENSITIVE_PACKAGES, true)) {
        return 'sensitive';
    }

    if ($routes !== [] || $httpClients !== [] || (($manifest['database']['migrations'] ?? false) === true)) {
        return 'standard';
    }

    return 'low';
}

/**
 * @param  list<array{authenticated: bool}>  $routes
 */
function capell_sync_auth_mode(array $routes): string
{
    if ($routes === []) {
        return 'none';
    }

    $authenticated = array_values(array_filter($routes, static fn (array $route): bool => $route['authenticated']));

    if ($authenticated === []) {
        return 'public';
    }

    return count($authenticated) === count($routes) ? 'authenticated' : 'mixed';
}

/**
 * @param  array<string, mixed>  $manifest
 */
function capell_sync_admin_authorization(array $manifest, string $packagePath): string
{
    $permissions = capell_security_string_list($manifest['permissions'] ?? []);
    $hasAdminSurface = in_array('admin', capell_security_string_list($manifest['surfaces'] ?? []), true);

    if ($permissions !== []) {
        return 'permissions';
    }

    $policiesPath = rtrim($packagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Policies';

    if (is_dir($policiesPath) && iterator_count((new FilesystemIterator($policiesPath))) > 0) {
        return 'policies';
    }

    return $hasAdminSurface ? 'panel-auth' : 'none';
}

function capell_sync_indent_json(string $json, int $spaces): string
{
    $indent = str_repeat(' ', $spaces);
    $lines = explode("\n", $json);

    return implode("\n", array_map(
        static fn (string $line): string => $line === '' ? $line : $indent . $line,
        $lines,
    ));
}

function capell_sync_strip_security_section(string $contents): string
{
    return (string) preg_replace(
        '/    "security": \{.*?\n    \},\n    "performance": \{/s',
        '    "performance": {',
        $contents,
        1,
    );
}
