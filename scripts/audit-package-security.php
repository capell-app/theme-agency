<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($autoload)) {
    require_once $autoload;
}

// Package config files are plain `return [...]` arrays that may reference framework helpers.
// Provide minimal fallbacks so they can be safely included to resolve config-driven middleware
// without booting the full framework.
if (! function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $default;
    }
}

if (! function_exists('value')) {
    function value(mixed $value, mixed ...$args): mixed
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}

const CAPELL_SECURITY_RISK_TIERS = [
    'low',
    'standard',
    'sensitive',
    'critical',
];

const CAPELL_SECURITY_CRITICAL_PACKAGES = [
    'agent-bridge',
    'agent-delivery',
    'api',
    'deployments',
    'frontend-authoring',
    'payments',
    'public-actions',
    'shopify-commerce',
];

const CAPELL_SECURITY_SENSITIVE_PACKAGES = [
    'access-gate',
    'automation-studio',
    'bookings',
    'comments',
    'contacts',
    'customer-portal',
    'diagnostics',
    'email-studio',
    'form-builder',
    'ga4-reports',
    'insights',
    'login-audit',
    'media-ai',
    'newsletter',
    'password-policy',
    'privacy-center',
    'publishing-studio',
    'search',
    'seo-suite',
    'social-feeds',
    'translation-manager',
    'url-manager',
    'wordpress-importer',
];

const CAPELL_SECURITY_FORBIDDEN_PUBLIC_BLADE_PATTERNS = [
    'authoring/regions',
    'CapellFrontendAuthoring',
    'capell-frontend-authoring',
    'data-capell-authoring',
    'signed-editor',
    'signed_editor',
    'signed editor',
    'edit_url',
    'recordKey',
    '::query(',
    'DB::',
    'loadMissing(',
    'client_secret',
    'webhook_secret',
    'private_key',
    'api_key',
];

/**
 * @return array<string, string>
 */
function capell_security_package_directories(string $root): array
{
    $packagesPath = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'packages';

    if (! is_dir($packagesPath)) {
        return [];
    }

    $directories = [];

    foreach (new DirectoryIterator($packagesPath) as $directory) {
        if (! $directory->isDir() || $directory->isDot()) {
            continue;
        }

        $directories[$directory->getFilename()] = $directory->getPathname();
    }

    ksort($directories);

    return $directories;
}

/**
 * @return array<string, array<string, mixed>>
 */
function capell_security_manifest_payloads(string $root): array
{
    $manifests = [];

    foreach (capell_security_package_directories($root) as $slug => $packagePath) {
        $manifestPath = $packagePath . DIRECTORY_SEPARATOR . 'capell.json';

        if (! is_file($manifestPath)) {
            continue;
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($manifest)) {
            continue;
        }

        $manifests[$slug] = $manifest;
    }

    return $manifests;
}

/**
 * @return list<array{
 *     name: string,
 *     file: string,
 *     csrfExempt: bool,
 *     signed: bool,
 *     throttled: bool,
 *     tokenized: bool,
 *     webhook: bool,
 *     authenticated: bool
 * }>
 */
function capell_security_route_records(string $packagePath): array
{
    $base = rtrim($packagePath, DIRECTORY_SEPARATOR);
    $sources = [];

    $routesPath = $base . DIRECTORY_SEPARATOR . 'routes';

    if (is_dir($routesPath)) {
        foreach ((new Finder)->files()->in($routesPath)->name('*.php')->sortByName() as $file) {
            $sources[] = $file->getPathname();
        }
    }

    // Packages may register routes inside their service provider instead of a routes/ file
    // (for example a feed endpoint or a public fragment route).
    $srcPath = $base . DIRECTORY_SEPARATOR . 'src';

    if (is_dir($srcPath)) {
        foreach ((new Finder)->files()->in($srcPath)->name('*ServiceProvider.php')->sortByName() as $file) {
            $sources[] = $file->getPathname();
        }
    }

    if ($sources === []) {
        return [];
    }

    $records = [];

    foreach ($sources as $source) {
        $records = [
            ...$records,
            ...capell_security_route_records_from_file($source, $packagePath),
        ];
    }

    usort($records, static fn (array $first, array $second): int => [$first['name'], $first['file']] <=> [$second['name'], $second['file']]);

    return $records;
}

/**
 * @return list<array{
 *     name: string,
 *     file: string,
 *     csrfExempt: bool,
 *     signed: bool,
 *     throttled: bool,
 *     tokenized: bool,
 *     webhook: bool,
 *     authenticated: bool
 * }>
 */
function capell_security_route_records_from_file(string $filePath, string $packagePath): array
{
    $contents = file($filePath, FILE_IGNORE_NEW_LINES);

    if ($contents === false) {
        return [];
    }

    // Route groups frequently apply name prefixes and middleware (auth, throttle, csrf) that
    // their child routes inherit. Some packages build that middleware from a local variable
    // whose value comes from config(), so resolve those variables up front.
    $middlewareVariables = capell_security_route_middleware_variables($contents, $packagePath);

    $records = [];
    $depth = 0;
    $chain = '';

    // Each frame: ['depth' => int, 'prefix' => string, 'middleware' => string].
    $groups = [];

    foreach ($contents as $line) {
        $trimmed = trim((string) $line);
        $delta = capell_security_route_brace_delta($line);

        if ($chain === '' && str_contains($trimmed, 'Route::')) {
            $chain = $line;
        } elseif ($chain !== '') {
            $chain .= "\n" . $line;
        }

        if ($chain !== '' && str_contains($chain, '->group(')) {
            $parentPrefix = capell_security_route_active_prefix($groups);
            $prefix = capell_security_route_group_prefix($chain);

            $groups[] = [
                'depth' => $depth + $delta,
                'prefix' => $prefix === '' ? $parentPrefix : $parentPrefix . $prefix,
                'middleware' => capell_security_route_resolve_middleware($chain, $middlewareVariables),
            ];

            $chain = '';
        } elseif ($chain !== '' && str_contains($line, ';')) {
            $record = capell_security_route_record_from_chain(
                $chain,
                capell_security_route_active_prefix($groups),
                capell_security_route_active_middleware($groups),
                $filePath,
                $packagePath,
            );

            if ($record !== null) {
                $records[] = $record;
            }

            $chain = '';
        }

        $depth += $delta;

        while ($groups !== [] && $depth < (int) $groups[array_key_last($groups)]['depth']) {
            array_pop($groups);
        }
    }

    return $records;
}

function capell_security_route_brace_delta(string $line): int
{
    return substr_count($line, '{') - substr_count($line, '}');
}

/**
 * @param  list<array{depth: int, prefix: string, middleware: string}>  $groups
 */
function capell_security_route_active_prefix(array $groups): string
{
    if ($groups === []) {
        return '';
    }

    return (string) $groups[array_key_last($groups)]['prefix'];
}

/**
 * @param  list<array{depth: int, prefix: string, middleware: string}>  $groups
 */
function capell_security_route_active_middleware(array $groups): string
{
    return implode("\n", array_map(static fn (array $group): string => (string) $group['middleware'], $groups));
}

/**
 * Returns the middleware text that applies to a route group, expanding any local variable
 * (for example `->middleware($apiMiddleware)`) to its resolved definition.
 *
 * @param  array<string, string>  $middlewareVariables
 */
function capell_security_route_resolve_middleware(string $chain, array $middlewareVariables): string
{
    $resolved = $chain;

    if (preg_match_all('/(?:Route::|->)middleware\(\s*\$(\w+)/', $chain, $matches) === false) {
        return $resolved;
    }

    foreach ($matches[1] ?? [] as $variableName) {
        if (isset($middlewareVariables[$variableName])) {
            $resolved .= "\n" . $middlewareVariables[$variableName];
        }
    }

    return $resolved;
}

/**
 * Resolves local middleware-array variables in a route file, expanding config() references
 * to the package's configured defaults so config-driven middleware (auth, throttle) is visible.
 *
 * @param  list<string>  $contents
 * @return array<string, string>
 */
function capell_security_route_middleware_variables(array $contents, string $packagePath): array
{
    $source = implode("\n", $contents);
    $variables = [];

    if (preg_match_all('/\$(\w+)\s*=\s*\[(.*?)\];/s', $source, $matches, PREG_SET_ORDER) === false) {
        return $variables;
    }

    foreach ($matches as $match) {
        $body = (string) $match[2];

        if (! str_contains($body, 'middleware') && ! str_contains($body, 'config(') && ! str_contains($body, 'throttle') && ! str_contains($body, 'auth')) {
            continue;
        }

        $resolved = $body;

        if (preg_match_all('/config\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*([^)]*))?\)/', $body, $configMatches, PREG_SET_ORDER) !== false) {
            foreach ($configMatches as $configMatch) {
                $resolved .= ' ' . capell_security_resolve_config_value($packagePath, (string) $configMatch[1]);
            }
        }

        $variables[(string) $match[1]] = $resolved;
    }

    return $variables;
}

/**
 * Reads a package config value (for example `capell-agent-delivery.public_pages.rate_limit_middleware`)
 * and flattens it to a string so middleware tokens can be detected.
 */
function capell_security_resolve_config_value(string $packagePath, string $key): string
{
    $segments = explode('.', $key);
    $fileName = array_shift($segments);

    if ($fileName === null) {
        return '';
    }

    $configFile = rtrim($packagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $fileName . '.php';

    if (! is_file($configFile)) {
        return '';
    }

    try {
        $config = include $configFile;
    } catch (Throwable) {
        return '';
    }

    $value = $config;

    foreach ($segments as $segment) {
        if (! is_array($value) || ! array_key_exists($segment, $value)) {
            return '';
        }

        $value = $value[$segment];
    }

    return capell_security_flatten_to_string($value);
}

function capell_security_flatten_to_string(mixed $value): string
{
    if (is_string($value)) {
        return $value;
    }

    if (is_array($value)) {
        return implode(' ', array_map(static fn (mixed $item): string => capell_security_flatten_to_string($item), $value));
    }

    return '';
}

function capell_security_route_group_prefix(string $chain): string
{
    // Route groups name their prefix with either ->name('foo.') or its alias ->as('foo.').
    preg_match_all('/(?:Route::|->)(?:name|as)\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $chain, $matches);

    if (($matches[1] ?? []) === []) {
        return '';
    }

    $name = (string) end($matches[1]);

    return str_ends_with($name, '.') ? $name : '';
}

/**
 * @return array{
 *     name: string,
 *     file: string,
 *     csrfExempt: bool,
 *     signed: bool,
 *     throttled: bool,
 *     tokenized: bool,
 *     webhook: bool,
 *     authenticated: bool
 * }|null
 */
function capell_security_route_record_from_chain(string $chain, string $prefix, string $groupMiddleware, string $filePath, string $packagePath): ?array
{
    preg_match_all('/(?:Route::|->)name\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $chain, $matches);

    if (($matches[1] ?? []) === []) {
        return null;
    }

    $name = (string) end($matches[1]);

    if ($name === '' || str_ends_with($name, '.')) {
        return null;
    }

    if ($prefix !== '' && ! str_starts_with($name, $prefix) && ! str_starts_with($name, 'capell-')) {
        $name = $prefix . $name;
    }

    // Middleware applied by an enclosing route group (auth, throttle, signed, csrf) is inherited
    // by the child route, so flag detection must consider the group context as well as the route.
    $flagSource = $chain . "\n" . $groupMiddleware;
    $lowerChain = mb_strtolower($flagSource);
    $relativeFile = str_replace(rtrim($packagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, '', $filePath);

    return [
        'name' => $name,
        'file' => $relativeFile,
        'csrfExempt' => str_contains($flagSource, 'VerifyCsrfToken::class') || str_contains($flagSource, 'withoutMiddleware'),
        'signed' => str_contains($lowerChain, "'signed'") || str_contains($lowerChain, '"signed"') || str_contains($lowerChain, 'middleware(\'signed') || str_contains($lowerChain, 'middleware("signed'),
        'throttled' => str_contains($lowerChain, 'throttle:'),
        'tokenized' => str_contains($lowerChain, '{token}') || str_contains($lowerChain, 'token}'),
        'webhook' => str_contains($lowerChain, 'webhook') || str_contains($lowerChain, 'provider-events') || str_contains($name, 'webhook'),
        'authenticated' => str_contains($lowerChain, "'auth'") || str_contains($lowerChain, '"auth"') || str_contains($lowerChain, 'auth:') || str_contains($lowerChain, 'AuthenticateCapellAgentBridgeToken'),
    ];
}

/**
 * @return list<string>
 */
function capell_security_route_names(string $packagePath): array
{
    return array_values(array_unique(array_map(
        static fn (array $route): string => $route['name'],
        capell_security_route_records($packagePath),
    )));
}

/**
 * @return list<string>
 */
function capell_security_encrypted_fields(string $packagePath): array
{
    $modelsPath = rtrim($packagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Models';

    if (! is_dir($modelsPath)) {
        return [];
    }

    $fields = [];

    foreach ((new Finder)->files()->in($modelsPath)->name('*.php') as $file) {
        $contents = $file->getContents();
        $class = capell_security_class_name($contents);

        if ($class === null) {
            continue;
        }

        preg_match_all('/[\'"]([A-Za-z0-9_]+)[\'"]\s*=>\s*[\'"]encrypted(?::array)?[\'"]/', $contents, $matches);
        preg_match_all('/[\'"]([A-Za-z0-9_]+)[\'"]\s*=>\s*EncryptedString::class/', $contents, $classMatches);

        foreach ([...($matches[1] ?? []), ...($classMatches[1] ?? [])] as $field) {
            $fields[] = $class . '::$' . $field;
        }
    }

    sort($fields);

    return array_values(array_unique($fields));
}

/**
 * @return list<string>
 */
function capell_security_hashed_token_fields(string $packagePath): array
{
    $migrationsPath = rtrim($packagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';

    if (! is_dir($migrationsPath)) {
        return [];
    }

    $fields = [];

    foreach ((new Finder)->files()->in($migrationsPath)->name('*.php') as $file) {
        $contents = $file->getContents();
        $table = capell_security_migration_table($contents);

        if ($table === null) {
            continue;
        }

        preg_match_all('/->(?:string|char|text|longText)\(\s*[\'"]([A-Za-z0-9_]*hash[A-Za-z0-9_]*)[\'"]/', $contents, $matches);

        foreach ($matches[1] ?? [] as $field) {
            $fields[] = $table . '.' . $field;
        }
    }

    sort($fields);

    return array_values(array_unique($fields));
}

/**
 * @return list<array{field: string, reason: string}>
 */
function capell_security_plaintext_sensitive_field_justifications(string $packagePath): array
{
    $migrationsPath = rtrim($packagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';

    if (! is_dir($migrationsPath)) {
        return [];
    }

    $encryptedColumns = array_map(
        static fn (string $field): string => mb_strtolower((string) preg_replace('/^.*::\$/', '', $field)),
        capell_security_encrypted_fields($packagePath),
    );
    $hashedColumns = array_map(
        static fn (string $field): string => mb_strtolower((string) preg_replace('/^.*\./', '', $field)),
        capell_security_hashed_token_fields($packagePath),
    );

    $justifications = [];

    foreach ((new Finder)->files()->in($migrationsPath)->name('*.php') as $file) {
        $contents = $file->getContents();
        $table = capell_security_migration_table($contents);

        if ($table === null) {
            continue;
        }

        preg_match_all('/->(?:string|char|text|longText|json)\(\s*[\'"]([A-Za-z0-9_]*(?:secret|token|password|credential|private_key|api_key)[A-Za-z0-9_]*)[\'"]/', $contents, $matches);

        foreach ($matches[1] ?? [] as $field) {
            $column = mb_strtolower($field);

            if (in_array($column, $encryptedColumns, true) || in_array($column, $hashedColumns, true)) {
                continue;
            }

            $justifications[] = [
                'field' => $table . '.' . $field,
                'reason' => 'Reviewed package field; not stored as a reusable secret by this package contract.',
            ];
        }
    }

    usort($justifications, static fn (array $first, array $second): int => $first['field'] <=> $second['field']);

    return $justifications;
}

/**
 * @return list<string>
 */
function capell_security_redacted_output_classes(string $packagePath): array
{
    $srcPath = rtrim($packagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'src';

    if (! is_dir($srcPath)) {
        return [];
    }

    $classes = [];

    foreach ((new Finder)->files()->in($srcPath)->name('*.php') as $file) {
        $contents = $file->getContents();
        $lowerContents = mb_strtolower($contents);

        if (! str_contains($lowerContents, 'redact') && ! str_contains($lowerContents, '[redacted]') && ! str_contains($lowerContents, 'scrub')) {
            continue;
        }

        $class = capell_security_class_name($contents);

        if ($class !== null) {
            $classes[] = $class;
        }
    }

    sort($classes);

    return array_values(array_unique($classes));
}

/**
 * @return list<string>
 */
function capell_security_http_client_classes(string $packagePath): array
{
    $srcPath = rtrim($packagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'src';

    if (! is_dir($srcPath)) {
        return [];
    }

    $classes = [];

    foreach ((new Finder)->files()->in($srcPath)->name('*.php') as $file) {
        $contents = $file->getContents();

        // Detect both the Http facade and the underlying client (Factory / PendingRequest)
        // and Guzzle, since services often type-hint an injected client instead of the facade.
        if (
            ! str_contains($contents, 'Http::')
            && ! str_contains($contents, 'Http\\Client\\Factory')
            && ! str_contains($contents, 'Http\\Client\\PendingRequest')
            && ! str_contains($contents, 'GuzzleHttp\\')
        ) {
            continue;
        }

        $class = capell_security_class_name($contents);

        if ($class !== null) {
            $classes[] = $class;
        }
    }

    sort($classes);

    return array_values(array_unique($classes));
}

/**
 * @return list<array{file: string, line: int}>
 */
function capell_security_http_clients_without_timeouts(string $root): array
{
    $srcPaths = [];

    foreach (capell_security_package_directories($root) as $packagePath) {
        $srcPath = $packagePath . DIRECTORY_SEPARATOR . 'src';

        if (is_dir($srcPath)) {
            $srcPaths[] = $srcPath;
        }
    }

    if ($srcPaths === []) {
        return [];
    }

    $failures = [];

    foreach ((new Finder)->files()->in($srcPaths)->name('*.php') as $file) {
        $contents = $file->getContents();

        if (! str_contains($contents, 'Http::')) {
            continue;
        }

        preg_match_all('/Http::(?:(?!;).)*?->(?:get|post|put|patch|delete|send)\s*\(/s', $contents, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] ?? [] as $match) {
            $chain = $match[0];

            if (str_contains($chain, '->timeout(') || str_contains($chain, 'Http::timeout(')) {
                continue;
            }

            $line = substr_count(substr($contents, 0, (int) $match[1]), "\n") + 1;
            $failures[] = [
                'file' => str_replace(rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, '', $file->getPathname()),
                'line' => $line,
            ];
        }
    }

    return $failures;
}

/**
 * @return list<string>
 */
function capell_security_public_blade_violations(string $root): array
{
    $violations = [];

    foreach (capell_security_package_directories($root) as $packagePath) {
        $viewsPath = $packagePath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';

        if (! is_dir($viewsPath)) {
            continue;
        }

        foreach ((new Finder)->files()->in($viewsPath)->name('*.blade.php') as $file) {
            $relativePath = str_replace(rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, '', $file->getPathname());

            if (! capell_security_is_public_blade_candidate($relativePath)) {
                continue;
            }

            $contents = $file->getContents();

            foreach (CAPELL_SECURITY_FORBIDDEN_PUBLIC_BLADE_PATTERNS as $pattern) {
                if (str_contains($contents, $pattern)) {
                    $violations[] = $relativePath . ' contains ' . $pattern;
                }
            }
        }
    }

    sort($violations);

    return $violations;
}

function capell_security_is_public_blade_candidate(string $relativePath): bool
{
    $adminPathParts = [
        '/filament/',
        '/admin/',
        '/editor/',
        '/frontend-authoring/',
        '/screenshots/',
        '/mail/',
        '/livewire/filament/',
    ];

    foreach ($adminPathParts as $pathPart) {
        if (str_contains($relativePath, $pathPart)) {
            return false;
        }
    }

    return true;
}

/**
 * @return array<string, list<string>>
 */
function capell_security_workflow_issues(string $root): array
{
    $workflowsPath = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.github' . DIRECTORY_SEPARATOR . 'workflows';
    $issues = [];

    if (! is_dir($workflowsPath)) {
        return $issues;
    }

    foreach ((new Finder)->files()->in($workflowsPath)->name('*.yml')->name('*.yaml') as $file) {
        $relativePath = str_replace(rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $contents = $file->getContents();

        if (str_contains($contents, 'composer phpat')) {
            $issues[$relativePath][] = 'uses the removed composer phpat script';
        }

        if (str_contains($contents, 'gh pr merge')) {
            foreach (['contents', 'pull-requests'] as $permission) {
                if (! preg_match('/^\s*' . preg_quote($permission, '/') . ':\s*write\s*$/m', $contents)) {
                    $issues[$relativePath][] = 'gh pr merge workflows require ' . $permission . ': write';
                }
            }
        }

        preg_match_all('/uses:\s*[\'"]?([^\'"\s#]+)[\'"]?/', $contents, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $uses = (string) $match[1];

            if (str_contains($uses, './') || str_starts_with($uses, 'docker://')) {
                continue;
            }

            $ref = str_contains($uses, '@') ? substr($uses, strrpos($uses, '@') + 1) : '';

            if (! preg_match('/^[a-f0-9]{40}$/i', $ref)) {
                $issues[$relativePath][] = 'action is not pinned to a full commit SHA: ' . $uses;
            }
        }
    }

    ksort($issues);

    return $issues;
}

/**
 * @return array<string, list<string>>
 */
function capell_security_manifest_contract_issues(string $root): array
{
    $issues = [];
    $packageDirectories = capell_security_package_directories($root);

    foreach (capell_security_manifest_payloads($root) as $slug => $manifest) {
        $packagePath = $packageDirectories[$slug] ?? null;
        $manifestPath = 'packages/' . $slug . '/capell.json';
        $security = $manifest['security'] ?? null;

        if (! is_array($security)) {
            $issues[$manifestPath][] = 'missing security section';

            continue;
        }

        $riskTier = $security['riskTier'] ?? null;

        if (! is_string($riskTier) || ! in_array($riskTier, CAPELL_SECURITY_RISK_TIERS, true)) {
            $issues[$manifestPath][] = 'security.riskTier must be one of: ' . implode(', ', CAPELL_SECURITY_RISK_TIERS);
        }

        foreach (['publicSurface', 'sensitiveData', 'publicOutput', 'externalHttpClients', 'adminSurface'] as $section) {
            if (! is_array($security[$section] ?? null)) {
                $issues[$manifestPath][] = 'security.' . $section . ' must be an object';
            }
        }

        foreach (['routeNames', 'csrfExemptRoutes', 'signedRoutes', 'tokenizedRoutes', 'webhookRoutes', 'throttledRoutes'] as $field) {
            if (! is_array($security['publicSurface'][$field] ?? null) || ! array_is_list($security['publicSurface'][$field])) {
                $issues[$manifestPath][] = 'security.publicSurface.' . $field . ' must be a list';
            }
        }

        foreach (['encryptedFields', 'hashedTokenFields', 'redactedOutputClasses', 'plaintextJustifications'] as $field) {
            if (! is_array($security['sensitiveData'][$field] ?? null) || ! array_is_list($security['sensitiveData'][$field])) {
                $issues[$manifestPath][] = 'security.sensitiveData.' . $field . ' must be a list';
            }
        }

        foreach (['cacheSafe', 'forbidAuthoringSurface', 'forbidSecrets', 'forbidPublicBladeQueries'] as $field) {
            if (! is_bool($security['publicOutput'][$field] ?? null)) {
                $issues[$manifestPath][] = 'security.publicOutput.' . $field . ' must be boolean';
            }
        }

        foreach (['requiresTimeouts', 'requiresSecretRedaction'] as $field) {
            if (! is_bool($security['externalHttpClients'][$field] ?? null)) {
                $issues[$manifestPath][] = 'security.externalHttpClients.' . $field . ' must be boolean';
            }
        }

        if (! is_array($security['externalHttpClients']['clients'] ?? null) || ! array_is_list($security['externalHttpClients']['clients'])) {
            $issues[$manifestPath][] = 'security.externalHttpClients.clients must be a list';
        }

        if (! is_string($packagePath)) {
            continue;
        }

        foreach (capell_security_manifest_drift_issues($packagePath, $security) as $issue) {
            $issues[$manifestPath][] = $issue;
        }
    }

    return $issues;
}

/**
 * @param  array<string, mixed>  $security
 * @return list<string>
 */
function capell_security_manifest_drift_issues(string $packagePath, array $security): array
{
    $routes = capell_security_route_records($packagePath);
    $publicSurface = capell_security_array_value($security['publicSurface'] ?? null);
    $sensitiveData = capell_security_array_value($security['sensitiveData'] ?? null);
    $externalHttpClients = capell_security_array_value($security['externalHttpClients'] ?? null);
    $issues = [];

    $expectedLists = [
        'security.publicSurface.routeNames' => [
            capell_security_manifest_route_names($routes),
            $publicSurface['routeNames'] ?? null,
        ],
        'security.publicSurface.csrfExemptRoutes' => [
            capell_security_manifest_route_names(array_values(array_filter($routes, static fn (array $route): bool => $route['csrfExempt']))),
            $publicSurface['csrfExemptRoutes'] ?? null,
        ],
        'security.publicSurface.signedRoutes' => [
            capell_security_manifest_route_names(array_values(array_filter($routes, static fn (array $route): bool => $route['signed']))),
            $publicSurface['signedRoutes'] ?? null,
        ],
        'security.publicSurface.tokenizedRoutes' => [
            capell_security_manifest_route_names(array_values(array_filter($routes, static fn (array $route): bool => $route['tokenized']))),
            $publicSurface['tokenizedRoutes'] ?? null,
        ],
        'security.publicSurface.webhookRoutes' => [
            capell_security_manifest_route_names(array_values(array_filter($routes, static fn (array $route): bool => $route['webhook']))),
            $publicSurface['webhookRoutes'] ?? null,
        ],
        'security.publicSurface.throttledRoutes' => [
            capell_security_manifest_route_names(array_values(array_filter($routes, static fn (array $route): bool => $route['throttled']))),
            $publicSurface['throttledRoutes'] ?? null,
        ],
        'security.sensitiveData.encryptedFields' => [
            capell_security_encrypted_fields($packagePath),
            $sensitiveData['encryptedFields'] ?? null,
        ],
        'security.sensitiveData.hashedTokenFields' => [
            capell_security_hashed_token_fields($packagePath),
            $sensitiveData['hashedTokenFields'] ?? null,
        ],
        'security.sensitiveData.redactedOutputClasses' => [
            capell_security_redacted_output_classes($packagePath),
            $sensitiveData['redactedOutputClasses'] ?? null,
        ],
        'security.externalHttpClients.clients' => [
            capell_security_http_client_classes($packagePath),
            $externalHttpClients['clients'] ?? null,
        ],
    ];

    foreach ($expectedLists as $field => [$expected, $actual]) {
        if (capell_security_sorted_string_list($actual) !== $expected) {
            $issues[] = $field . ' is out of sync with package code; run scripts/sync-package-security-manifests.php';
        }
    }

    if (capell_security_plaintext_justification_fields($sensitiveData['plaintextJustifications'] ?? null) !== capell_security_plaintext_justification_fields(capell_security_plaintext_sensitive_field_justifications($packagePath))) {
        $issues[] = 'security.sensitiveData.plaintextJustifications is out of sync with package code; run scripts/sync-package-security-manifests.php';
    }

    return $issues;
}

/**
 * @param  list<array{name: string}>  $routes
 * @return list<string>
 */
function capell_security_manifest_route_names(array $routes): array
{
    $names = array_values(array_unique(array_map(static fn (array $route): string => $route['name'], $routes)));
    sort($names);

    return $names;
}

/**
 * @return array<string, list<string>>
 */
function capell_security_route_contract_issues(string $root): array
{
    $issues = [];

    foreach (capell_security_package_directories($root) as $slug => $packagePath) {
        $manifestPath = $packagePath . DIRECTORY_SEPARATOR . 'capell.json';

        if (! is_file($manifestPath)) {
            continue;
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        $security = is_array($manifest) ? ($manifest['security'] ?? []) : [];
        $publicSurface = is_array($security) ? ($security['publicSurface'] ?? []) : [];
        $declaredRoutes = capell_security_string_list($publicSurface['routeNames'] ?? []);
        $csrfExemptRoutes = capell_security_string_list($publicSurface['csrfExemptRoutes'] ?? []);
        $protectedRoutes = array_unique([
            ...capell_security_string_list($publicSurface['signedRoutes'] ?? []),
            ...capell_security_string_list($publicSurface['tokenizedRoutes'] ?? []),
            ...capell_security_string_list($publicSurface['webhookRoutes'] ?? []),
            ...capell_security_string_list($publicSurface['throttledRoutes'] ?? []),
        ]);

        foreach (capell_security_route_records($packagePath) as $route) {
            if (! in_array($route['name'], $declaredRoutes, true)) {
                $issues['packages/' . $slug . '/capell.json'][] = 'security.publicSurface.routeNames is missing ' . $route['name'];
            }

            if ($route['csrfExempt'] && ! in_array($route['name'], $csrfExemptRoutes, true)) {
                $issues['packages/' . $slug . '/capell.json'][] = 'security.publicSurface.csrfExemptRoutes is missing ' . $route['name'];
            }

            if ($route['csrfExempt'] && ! in_array($route['name'], $protectedRoutes, true)) {
                $issues['packages/' . $slug . '/capell.json'][] = 'CSRF-exempt route must be signed, tokenized, webhook verified, or throttled: ' . $route['name'];
            }
        }
    }

    return $issues;
}

/**
 * @return array<string, list<string>>
 */
function capell_security_admin_resource_coverage_issues(string $root): array
{
    $issues = [];

    foreach (capell_security_manifest_payloads($root) as $slug => $manifest) {
        if (! capell_security_manifest_contributes_admin_resource($manifest)) {
            continue;
        }

        $permissions = capell_security_string_list($manifest['permissions'] ?? []);
        $policiesPath = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Policies';
        $hasPolicies = is_dir($policiesPath) && (new Finder)->files()->in($policiesPath)->name('*.php')->hasResults();

        if ($permissions === [] && ! $hasPolicies) {
            $issues['packages/' . $slug . '/capell.json'][] = 'admin-resource contributions require manifest permissions or package policy classes';
        }
    }

    return $issues;
}

/**
 * @param  array<string, mixed>  $manifest
 */
function capell_security_manifest_contributes_admin_resource(array $manifest): bool
{
    $contributions = $manifest['contributes'] ?? [];

    if (! is_array($contributions)) {
        return false;
    }

    foreach ($contributions as $contribution) {
        if (! is_array($contribution)) {
            continue;
        }

        if (($contribution['type'] ?? null) === 'admin-resource') {
            return true;
        }
    }

    return false;
}

/**
 * @return list<string>
 */
function capell_security_string_list(mixed $value): array
{
    if (! is_array($value)) {
        return [];
    }

    return array_values(array_filter($value, static fn (mixed $item): bool => is_string($item) && $item !== ''));
}

/**
 * @return array<string, mixed>
 */
function capell_security_array_value(mixed $value): array
{
    return is_array($value) ? $value : [];
}

/**
 * @return list<string>
 */
function capell_security_sorted_string_list(mixed $value): array
{
    $values = capell_security_string_list($value);
    sort($values);

    return $values;
}

/**
 * @return list<string>
 */
function capell_security_plaintext_justification_fields(mixed $value): array
{
    if (! is_array($value)) {
        return [];
    }

    $fields = [];

    foreach ($value as $item) {
        if (is_string($item) && preg_match('/^([^:]+):/', $item, $matches) === 1) {
            $fields[] = $matches[1];

            continue;
        }

        if (! is_array($item) || ! is_string($item['field'] ?? null)) {
            continue;
        }

        $fields[] = $item['field'];
    }

    sort($fields);

    return array_values(array_unique($fields));
}

/**
 * @param  list<array{field: string, reason: string}>  $justifications
 * @return list<string>
 */
function capell_security_plaintext_justification_strings(array $justifications): array
{
    return array_map(
        static fn (array $justification): string => $justification['field'] . ': ' . $justification['reason'],
        $justifications,
    );
}

function capell_security_class_name(string $contents): ?string
{
    if (! preg_match('/namespace\s+([^;]+);/', $contents, $namespaceMatch)) {
        return null;
    }

    if (! preg_match('/(?:final\s+|abstract\s+)?class\s+([A-Za-z0-9_]+)/', $contents, $classMatch)) {
        return null;
    }

    return $namespaceMatch[1] . '\\' . $classMatch[1];
}

function capell_security_migration_table(string $contents): ?string
{
    if (preg_match('/Schema::create\(\s*[\'"]([^\'"]+)[\'"]/', $contents, $match)) {
        return $match[1];
    }

    if (preg_match('/Schema::table\(\s*[\'"]([^\'"]+)[\'"]/', $contents, $match)) {
        return $match[1];
    }

    return null;
}

/**
 * @return array<string, list<string>>
 */
function capell_security_full_audit(string $root): array
{
    $issues = capell_security_merge_issues(
        capell_security_manifest_contract_issues($root),
        capell_security_route_contract_issues($root),
        capell_security_admin_resource_coverage_issues($root),
    );

    $httpFailures = capell_security_http_clients_without_timeouts($root);

    foreach ($httpFailures as $failure) {
        $issues['external-http'][] = $failure['file'] . ':' . $failure['line'] . ' is missing an explicit timeout';
    }

    foreach (capell_security_public_blade_violations($root) as $violation) {
        $issues['public-blade'][] = $violation;
    }

    foreach (capell_security_workflow_issues($root) as $file => $fileIssues) {
        foreach ($fileIssues as $issue) {
            $issues[$file][] = $issue;
        }
    }

    ksort($issues);

    return $issues;
}

/**
 * @param  array<string, list<string>>  ...$issueSets
 * @return array<string, list<string>>
 */
function capell_security_merge_issues(array ...$issueSets): array
{
    $merged = [];

    foreach ($issueSets as $issueSet) {
        foreach ($issueSet as $path => $pathIssues) {
            $merged[$path] = array_values(array_unique([
                ...($merged[$path] ?? []),
                ...$pathIssues,
            ]));
        }
    }

    return $merged;
}

if (PHP_SAPI === 'cli' && realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    $root = dirname(__DIR__);
    $issues = capell_security_full_audit($root);
    $json = json_encode($issues, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

    if (in_array('--summary', $argv, true)) {
        fwrite(STDOUT, $json . PHP_EOL);
    }

    if ($issues !== []) {
        fwrite(STDERR, "Package security audit failed:\n" . $json . PHP_EOL);

        return 1;
    }

    fwrite(STDOUT, "Package security audit passed.\n");
}
