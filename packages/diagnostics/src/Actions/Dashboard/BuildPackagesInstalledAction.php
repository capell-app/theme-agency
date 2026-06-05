<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\Dashboard;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Diagnostics\Data\Dashboard\PackageInfoData;
use Capell\Diagnostics\Data\Dashboard\PackagesInstalledData;
use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;
use ReflectionClass;
use Spatie\LaravelData\DataCollection;

/**
 * @phpstan-type PackageMetadata array{short: string, config: ?string, docs: ?string, display?: ?string, bundle?: ?string, health_checks?: int, health_checks_declared?: int, health_checks_implemented?: int, health_checks_stub?: int, health_checks_broken?: int, install?: ?string, doctor?: ?string}
 * @phpstan-type ManifestMetadata array{composer: string, values: PackageMetadata}
 *
 * @method static PackagesInstalledData run()
 */
final class BuildPackagesInstalledAction
{
    use AsAction;

    private const string HEALTH_CHECK_METHOD = 'runDiagnostics';

    /**
     * Maps a composer package name to its short handle, config-file name, and docs URL.
     *
     * @var array<string, PackageMetadata>
     */
    private const array KNOWN_PACKAGES = [
        'capell-app/core' => [
            'short' => 'core',
            'config' => 'capell',
            'docs' => 'https://github.com/capell-app/capell/blob/4.x/packages/core/README.md',
        ],
        'capell-app/admin' => [
            'short' => 'admin',
            'config' => 'capell-admin',
            'docs' => 'https://github.com/capell-app/capell/blob/4.x/packages/admin/README.md',
        ],
        'capell-app/frontend' => [
            'short' => 'frontend',
            'config' => 'capell-frontend',
            'docs' => 'https://github.com/capell-app/capell/blob/4.x/packages/frontend/README.md',
        ],
        'capell-app/migration-assistant' => [
            'short' => 'migration-assistant',
            'config' => 'migration-assistant',
            'docs' => 'https://github.com/capell-app/capell/blob/4.x/packages/migration-assistant/README.md',
        ],
        'capell-app/wordpress-importer' => [
            'short' => 'wordpress-importer',
            'config' => 'wordpress-importer',
            'docs' => 'https://github.com/capell-app/capell-packages/blob/4.x/packages/wordpress-importer/README.md',
        ],
        'capell-app/capell-layout-builder' => [
            'short' => 'layout-builder',
            'config' => 'capell-layout-builder',
            'docs' => 'https://github.com/capell-app/capell-packages/blob/4.x/packages/layout-builder/README.md',
        ],
        'capell-app/capell-blog' => [
            'short' => 'blog',
            'config' => 'capell-blog',
            'docs' => 'https://github.com/capell-app/capell-packages/blob/4.x/packages/blog/README.md',
        ],
        'capell-app/capell-address' => [
            'short' => 'address',
            'config' => 'capell-address',
            'docs' => 'https://github.com/capell-app/capell-packages/blob/4.x/packages/address/README.md',
        ],
        'capell-app/seo-suite' => [
            'short' => 'seo-suite',
            'config' => 'capell-seo-suite',
            'docs' => 'https://github.com/capell-app/capell-packages/blob/4.x/packages/seo-suite/README.md',
        ],
        'capell-app/site-discovery' => [
            'short' => 'site-discovery',
            'config' => null,
            'docs' => 'https://github.com/capell-app/capell-packages/blob/4.x/packages/site-discovery/README.md',
        ],
    ];

    public function __construct(
        private readonly ?string $customInstalledJsonPath = null,
        private readonly ?string $customLocalPackagesPath = null,
    ) {}

    public function handle(): PackagesInstalledData
    {
        $installedJsonPath = $this->installedJsonPath();

        if (! File::exists($installedJsonPath)) {
            return new PackagesInstalledData(
                packages: PackageInfoData::collect([], DataCollection::class),
            );
        }

        /** @var array{packages?: array<int, array{name: string, version: string, install_path?: string}>}|array<int, array{name: string, version: string, install_path?: string}> $installedData */
        $installedData = json_decode(File::get($installedJsonPath), true) ?? [];

        /** @var array<int, array{name: string, version: string, install_path?: string}> $allPackages */
        $allPackages = $installedData['packages'] ?? (array_is_list($installedData) ? $installedData : []);
        $knownPackages = $this->knownPackages();

        $rows = [];

        foreach ($allPackages as $package) {
            $composerName = $package['name'] ?? '';

            if (! str_starts_with($composerName, 'capell-app/')) {
                continue;
            }

            $meta = $knownPackages[$composerName] ?? $this->metadataFromInstalledPackage($package) ?? [
                'short' => str($composerName)->after('capell-app/')->toString(),
                'config' => null,
                'docs' => null,
                'display' => null,
                'bundle' => null,
                'health_checks' => 0,
                'install' => null,
                'doctor' => null,
            ];
            $configPath = $meta['config'] !== null ? config_path($meta['config'] . '.php') : '';

            $rows[] = new PackageInfoData(
                name: $meta['short'],
                composerName: $composerName,
                version: $package['version'] ?? 'unknown',
                configPublished: File::exists($configPath),
                configPath: $configPath,
                docsUrl: $meta['docs'],
                displayName: $meta['display'] ?? null,
                bundle: $meta['bundle'] ?? null,
                healthCheckCount: $meta['health_checks'] ?? 0,
                healthCheckDeclaredCount: $meta['health_checks_declared'] ?? $meta['health_checks'] ?? 0,
                healthCheckImplementedCount: $meta['health_checks_implemented'] ?? 0,
                healthCheckStubCount: $meta['health_checks_stub'] ?? 0,
                healthCheckBrokenCount: $meta['health_checks_broken'] ?? 0,
                installCommand: $meta['install'] ?? null,
                doctorCommand: $meta['doctor'] ?? null,
            );
        }

        usort($rows, static fn (PackageInfoData $left, PackageInfoData $right): int => $left->name <=> $right->name);

        return new PackagesInstalledData(
            packages: PackageInfoData::collect($rows, DataCollection::class),
            healthCheckDeclaredCount: array_sum(array_map(
                static fn (PackageInfoData $package): int => $package->healthCheckDeclaredCount,
                $rows,
            )),
            healthCheckImplementedCount: array_sum(array_map(
                static fn (PackageInfoData $package): int => $package->healthCheckImplementedCount,
                $rows,
            )),
            healthCheckStubCount: array_sum(array_map(
                static fn (PackageInfoData $package): int => $package->healthCheckStubCount,
                $rows,
            )),
            healthCheckBrokenCount: array_sum(array_map(
                static fn (PackageInfoData $package): int => $package->healthCheckBrokenCount,
                $rows,
            )),
        );
    }

    private function installedJsonPath(): string
    {
        return $this->customInstalledJsonPath ?? base_path('vendor/composer/installed.json');
    }

    private function localPackagesPath(): string
    {
        return $this->customLocalPackagesPath ?? base_path('packages');
    }

    private function installedComposerDirectory(): string
    {
        return dirname($this->installedJsonPath());
    }

    /**
     * @return array<string, PackageMetadata>
     */
    private function knownPackages(): array
    {
        $knownPackages = self::KNOWN_PACKAGES;
        $packagesPath = $this->localPackagesPath();

        if (! File::isDirectory($packagesPath)) {
            return $knownPackages;
        }

        foreach (File::directories($packagesPath) as $packagePath) {
            $manifestPath = $packagePath . '/capell.json';

            if (! File::exists($manifestPath)) {
                continue;
            }

            $meta = $this->metadataFromManifestPath($manifestPath, $packagePath);

            if ($meta === null) {
                continue;
            }

            $knownPackages[$meta['composer']] = [
                ...($knownPackages[$meta['composer']] ?? []),
                ...$meta['values'],
            ];
        }

        /** @var array<string, PackageMetadata> $knownPackages */
        return $knownPackages;
    }

    /**
     * @param  array{name: string, version: string, install_path?: string}  $package
     * @return PackageMetadata|null
     */
    private function metadataFromInstalledPackage(array $package): ?array
    {
        $installPath = $package['install_path'] ?? null;

        if (! is_string($installPath) || $installPath === '') {
            return null;
        }

        $packagePath = str_starts_with($installPath, DIRECTORY_SEPARATOR)
            ? $installPath
            : $this->installedComposerDirectory() . DIRECTORY_SEPARATOR . $installPath;
        $manifestPath = $packagePath . '/capell.json';

        $meta = $this->metadataFromManifestPath($manifestPath, $packagePath);

        return $meta['values'] ?? null;
    }

    /**
     * @return ManifestMetadata|null
     */
    private function metadataFromManifestPath(string $manifestPath, string $packagePath): ?array
    {
        if (! File::exists($manifestPath)) {
            return null;
        }

        /** @var array<string, mixed>|null $manifest */
        $manifest = json_decode(File::get($manifestPath), true);

        if (! is_array($manifest)) {
            return null;
        }

        $composerName = $manifest['name'] ?? null;
        $slug = $manifest['slug'] ?? basename($packagePath);

        if (! is_string($composerName) || ! is_string($slug)) {
            return null;
        }

        return [
            'composer' => $composerName,
            'values' => [
                'short' => $slug,
                'config' => $this->configNameFor($packagePath),
                'docs' => 'https://github.com/capell-app/capell-packages/blob/4.x/packages/' . $slug . '/README.md',
                'display' => is_string($manifest['displayName'] ?? null) ? $manifest['displayName'] : null,
                'bundle' => $this->bundleFor($manifest),
                ...$this->healthCheckCountsFor($manifest),
                'install' => $this->commandFor($manifest, 'install'),
                'doctor' => $this->commandFor($manifest, 'doctor'),
            ],
        ];
    }

    private function configNameFor(string $packagePath): ?string
    {
        $configPaths = File::glob($packagePath . '/config/*.php');

        if ($configPaths === []) {
            return null;
        }

        $filename = pathinfo((string) $configPaths[0], PATHINFO_FILENAME);

        return is_string($filename) && $filename !== '' ? $filename : null;
    }

    /**
     * @param  array<string, mixed>  $manifest
     */
    private function bundleFor(array $manifest): ?string
    {
        $product = $manifest['product'] ?? null;

        if (! is_array($product)) {
            return null;
        }

        $bundle = $product['bundle'] ?? null;

        return is_string($bundle) ? $bundle : null;
    }

    /**
     * @param  array<string, mixed>  $manifest
     */
    private function commandFor(array $manifest, string $key): ?string
    {
        $commands = $manifest['commands'] ?? null;

        if (! is_array($commands)) {
            return null;
        }

        $command = $commands[$key] ?? null;

        return is_string($command) && $command !== '' ? $command : null;
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @return array{health_checks: int, health_checks_declared: int, health_checks_implemented: int, health_checks_stub: int, health_checks_broken: int}
     */
    private function healthCheckCountsFor(array $manifest): array
    {
        $healthChecks = $manifest['healthChecks'] ?? null;
        $declared = 0;
        $implemented = 0;
        $stub = 0;
        $broken = 0;

        if (! is_array($healthChecks)) {
            return [
                'health_checks' => 0,
                'health_checks_declared' => 0,
                'health_checks_implemented' => 0,
                'health_checks_stub' => 0,
                'health_checks_broken' => 0,
            ];
        }

        foreach ($healthChecks as $healthCheck) {
            if (! is_array($healthCheck)) {
                continue;
            }

            $declared++;
            $className = $healthCheck['class'] ?? null;

            if (! is_string($className) || $className === '' || ! class_exists($className)) {
                $broken++;

                continue;
            }

            if (! is_subclass_of($className, ChecksExtensionHealth::class)) {
                $broken++;

                continue;
            }

            if ($this->isRunnableHealthCheck($className)) {
                $implemented++;

                continue;
            }

            $stub++;
        }

        return [
            'health_checks' => $declared,
            'health_checks_declared' => $declared,
            'health_checks_implemented' => $implemented,
            'health_checks_stub' => $stub,
            'health_checks_broken' => $broken,
        ];
    }

    /**
     * @param  class-string  $className
     */
    private function isRunnableHealthCheck(string $className): bool
    {
        $reflection = new ReflectionClass($className);

        if (! $reflection->hasMethod(self::HEALTH_CHECK_METHOD)) {
            return false;
        }

        $method = $reflection->getMethod(self::HEALTH_CHECK_METHOD);

        return $method->isPublic() && $method->isStatic();
    }
}
