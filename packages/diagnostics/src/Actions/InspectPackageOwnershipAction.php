<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions;

use Capell\Diagnostics\Data\PackageOwnershipCandidateData;
use Capell\Diagnostics\Data\PackageOwnershipInspectionData;
use Capell\Diagnostics\Support\DiagnosticsSnapshotCache;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route as RouteFacade;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\DataCollection;

/**
 * @method static PackageOwnershipInspectionData run(string $kind, string $name)
 */
final class InspectPackageOwnershipAction
{
    use AsAction;

    public function __construct(
        private readonly ?string $customPackagesPath = null,
        private readonly ?string $customInstalledJsonPath = null,
    ) {}

    public function handle(string $kind, string $name): PackageOwnershipInspectionData
    {
        $normalizedKind = $this->normalizeKind($kind);
        $normalizedName = trim($name);

        return DiagnosticsSnapshotCache::remember(
            'package-ownership:' . hash('sha256', implode('|', [
                $normalizedKind,
                $normalizedName,
                $this->customPackagesPath ?? base_path('packages'),
                $this->customInstalledJsonPath ?? base_path('vendor/composer/installed.json'),
            ])),
            fn (): PackageOwnershipInspectionData => $this->inspect($normalizedKind, $normalizedName),
        );
    }

    private function inspect(string $normalizedKind, string $normalizedName): PackageOwnershipInspectionData
    {
        $packages = $this->packages();

        $candidates = match ($normalizedKind) {
            'config' => $this->inspectConfig($packages, $normalizedName),
            'route' => $this->inspectRoute($packages, $normalizedName),
            'table' => $this->inspectTable($packages, $normalizedName),
            default => throw new InvalidArgumentException('Ownership inspection kind must be config, route, or table.'),
        };

        return new PackageOwnershipInspectionData(
            kind: $normalizedKind,
            name: $normalizedName,
            candidates: PackageOwnershipCandidateData::collect($candidates, DataCollection::class),
            foundCount: count($candidates),
        );
    }

    private function normalizeKind(string $kind): string
    {
        $normalizedKind = strtolower(trim($kind));

        return match ($normalizedKind) {
            'config', 'route', 'table' => $normalizedKind,
            default => throw new InvalidArgumentException('Ownership inspection kind must be config, route, or table.'),
        };
    }

    /**
     * @param  list<array{composerName: string, slug: string, displayName: ?string, namespace: ?string, path: string, manifest: array<string, mixed>}>  $packages
     * @return list<PackageOwnershipCandidateData>
     */
    private function inspectConfig(array $packages, string $name): array
    {
        $configName = str_ends_with($name, '.php') ? substr($name, 0, -4) : $name;
        $candidates = [];

        foreach ($packages as $package) {
            foreach (File::glob($package['path'] . '/config/*.php') as $configPath) {
                $configuredName = pathinfo((string) $configPath, PATHINFO_FILENAME);

                if ($configuredName !== $configName) {
                    continue;
                }

                $candidates[] = $this->candidate($package, 'config', $this->relativeEvidence($package['path'], (string) $configPath));
            }
        }

        return $candidates;
    }

    /**
     * @param  list<array{composerName: string, slug: string, displayName: ?string, namespace: ?string, path: string, manifest: array<string, mixed>}>  $packages
     * @return list<PackageOwnershipCandidateData>
     */
    private function inspectRoute(array $packages, string $name): array
    {
        $candidates = [];

        foreach (RouteFacade::getRoutes()->getRoutes() as $route) {
            $actionClass = $this->routeActionClass($route);

            if (! $this->routeMatches($route, $actionClass, $name)) {
                continue;
            }

            $package = $this->packageForClass($packages, $actionClass);

            if ($package === null) {
                continue;
            }

            $routeName = $route->getName();
            $evidence = sprintf(
                'name: %s; uri: %s; controller: %s',
                is_string($routeName) && $routeName !== '' ? $routeName : '(unnamed)',
                $route->uri(),
                $actionClass ?? '(closure)',
            );

            $candidates[] = $this->candidate($package, 'route', $evidence);
        }

        return $candidates;
    }

    /**
     * @param  list<array{composerName: string, slug: string, displayName: ?string, namespace: ?string, path: string, manifest: array<string, mixed>}>  $packages
     * @return list<PackageOwnershipCandidateData>
     */
    private function inspectTable(array $packages, string $name): array
    {
        $candidates = [];

        foreach ($packages as $package) {
            foreach ($this->requiredTables($package['manifest']) as $tableName) {
                if ($tableName === $name) {
                    $candidates[] = $this->candidate($package, 'manifest', 'capell.json database.requiredTables');
                }
            }

            foreach (File::glob($package['path'] . '/database/migrations/*.php') as $migrationPath) {
                if (! $this->migrationCreatesTable((string) $migrationPath, $name)) {
                    continue;
                }

                $candidates[] = $this->candidate($package, 'migration', $this->relativeEvidence($package['path'], (string) $migrationPath));
            }
        }

        return $candidates;
    }

    /**
     * @return list<array{composerName: string, slug: string, displayName: ?string, namespace: ?string, path: string, manifest: array<string, mixed>}>
     */
    private function packages(): array
    {
        $packages = [];

        foreach ($this->packageDirectories() as $packagePath) {
            $package = $this->packageFromPath($packagePath);

            if ($package === null) {
                continue;
            }

            $packages[$package['composerName']] = $package;
        }

        return array_values($packages);
    }

    /**
     * @return list<string>
     */
    private function packageDirectories(): array
    {
        $directories = [];
        $packagesPath = $this->customPackagesPath ?? base_path('packages');

        if (File::isDirectory($packagesPath)) {
            foreach (File::directories($packagesPath) as $packagePath) {
                $directories[] = $packagePath;
            }
        }

        $installedJsonPath = $this->customInstalledJsonPath ?? base_path('vendor/composer/installed.json');

        if (File::exists($installedJsonPath)) {
            /** @var array{packages?: list<array{name?: string, install_path?: string}>}|list<array{name?: string, install_path?: string}> $installedData */
            $installedData = json_decode(File::get($installedJsonPath), true) ?? [];
            $installedPackages = $installedData['packages'] ?? (array_is_list($installedData) ? $installedData : []);

            foreach ($installedPackages as $installedPackage) {
                $composerName = $installedPackage['name'] ?? null;
                $installPath = $installedPackage['install_path'] ?? null;
                if (! is_string($composerName)) {
                    continue;
                }

                if (! str_starts_with($composerName, 'capell-app/')) {
                    continue;
                }

                if (! is_string($installPath)) {
                    continue;
                }

                if ($installPath === '') {
                    continue;
                }

                $directories[] = str_starts_with($installPath, DIRECTORY_SEPARATOR)
                    ? $installPath
                    : dirname($installedJsonPath) . DIRECTORY_SEPARATOR . $installPath;
            }
        }

        return array_values(array_unique($directories));
    }

    /**
     * @return array{composerName: string, slug: string, displayName: ?string, namespace: ?string, path: string, manifest: array<string, mixed>}|null
     */
    private function packageFromPath(string $packagePath): ?array
    {
        if (! File::isDirectory($packagePath)) {
            return null;
        }

        $manifest = $this->readJson($packagePath . '/capell.json');
        $composer = $this->readJson($packagePath . '/composer.json');
        $composerName = $manifest['name'] ?? $composer['name'] ?? null;

        if (! is_string($composerName) || ! str_starts_with($composerName, 'capell-app/')) {
            return null;
        }

        $slug = $manifest['slug'] ?? str($composerName)->after('capell-app/')->toString();
        $displayName = $manifest['displayName'] ?? null;
        $namespace = $manifest['namespace'] ?? $this->namespaceFromComposer($composer);

        return [
            'composerName' => $composerName,
            'slug' => is_string($slug) && $slug !== '' ? $slug : basename($packagePath),
            'displayName' => is_string($displayName) ? $displayName : null,
            'namespace' => is_string($namespace) && $namespace !== '' ? rtrim($namespace, '\\') : null,
            'path' => $packagePath,
            'manifest' => $manifest,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function readJson(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode(File::get($path), true);

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>  $composer
     */
    private function namespaceFromComposer(array $composer): ?string
    {
        $autoload = $composer['autoload'] ?? null;

        if (! is_array($autoload)) {
            return null;
        }

        $psr4 = $autoload['psr-4'] ?? null;

        if (! is_array($psr4)) {
            return null;
        }

        foreach (array_keys($psr4) as $namespace) {
            if (! is_string($namespace)) {
                continue;
            }

            if (str_contains($namespace, 'Database\\Factories')) {
                continue;
            }

            return $namespace;
        }

        return null;
    }

    private function routeActionClass(Route $route): ?string
    {
        $actionName = $route->getActionName();

        if ($actionName === 'Closure') {
            return null;
        }

        $actionClass = str_contains($actionName, '@')
            ? strstr($actionName, '@', true)
            : $actionName;

        return is_string($actionClass) && $actionClass !== '' ? $actionClass : null;
    }

    private function routeMatches(Route $route, ?string $actionClass, string $name): bool
    {
        $routeName = $route->getName();
        $normalizedUri = trim($name, '/');
        if ($routeName === $name) {
            return true;
        }

        if ($route->uri() === $normalizedUri) {
            return true;
        }

        return $actionClass === $name;
    }

    /**
     * @param  list<array{composerName: string, slug: string, displayName: ?string, namespace: ?string, path: string, manifest: array<string, mixed>}>  $packages
     * @return array{composerName: string, slug: string, displayName: ?string, namespace: ?string, path: string, manifest: array<string, mixed>}|null
     */
    private function packageForClass(array $packages, ?string $className): ?array
    {
        if ($className === null) {
            return null;
        }

        foreach ($packages as $package) {
            $namespace = $package['namespace'];

            if ($namespace === null) {
                continue;
            }

            if ($className === $namespace || str_starts_with($className, $namespace . '\\')) {
                return $package;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @return list<string>
     */
    private function requiredTables(array $manifest): array
    {
        $database = $manifest['database'] ?? null;

        if (! is_array($database)) {
            return [];
        }

        $requiredTables = $database['requiredTables'] ?? null;

        if (! is_array($requiredTables)) {
            return [];
        }

        return array_values(array_filter(
            $requiredTables,
            static fn (mixed $tableName): bool => is_string($tableName) && $tableName !== '',
        ));
    }

    private function migrationCreatesTable(string $migrationPath, string $tableName): bool
    {
        $migration = File::get($migrationPath);
        $quotedTableName = preg_quote($tableName, '/');

        return preg_match(sprintf("/Schema::create\\(\\s*['\"]%s['\"]/", $quotedTableName), $migration) === 1;
    }

    /**
     * @param  array{composerName: string, slug: string, displayName: ?string, namespace: ?string, path: string, manifest: array<string, mixed>}  $package
     */
    private function candidate(array $package, string $source, string $evidence): PackageOwnershipCandidateData
    {
        return new PackageOwnershipCandidateData(
            composerName: $package['composerName'],
            slug: $package['slug'],
            displayName: $package['displayName'],
            source: $source,
            evidence: $evidence,
            packagePath: $package['path'],
        );
    }

    private function relativeEvidence(string $packagePath, string $path): string
    {
        return ltrim(str_replace($packagePath, '', $path), DIRECTORY_SEPARATOR);
    }
}
