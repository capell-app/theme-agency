<?php

declare(strict_types=1);

namespace Capell\Tests\Support;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Manifest\ManifestLoader;
use Capell\Core\Support\Manifest\ManifestValidator;
use Composer\InstalledVersions;
use Illuminate\Support\ServiceProvider;
use Override;

final class RegisterLocalPackageManifestsServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $loader = new ManifestLoader(new ManifestValidator);
        $manifestPaths = [
            ...$this->manifestPaths(__DIR__ . '/../../packages/*/capell.json'),
            ...$this->installedPackageManifestPaths([
                'capell-app/core',
                'capell-app/installer',
                'capell-app/marketplace',
            ]),
        ];

        foreach ($manifestPaths as $manifestPath) {
            $manifest = $loader->load($manifestPath);

            CapellCore::registerManifestPackage(
                $manifest,
                CapellCore::getInstalledPrettyVersion($manifest->name),
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function manifestPaths(string $pattern): array
    {
        $paths = glob($pattern, GLOB_BRACE);

        return $paths === false ? [] : $paths;
    }

    /**
     * @param  list<string>  $packageNames
     * @return list<string>
     */
    private function installedPackageManifestPaths(array $packageNames): array
    {
        return array_values(collect($packageNames)
            ->filter(fn (string $packageName): bool => InstalledVersions::isInstalled($packageName))
            ->map(fn (string $packageName): ?string => InstalledVersions::getInstallPath($packageName))
            ->filter(fn (?string $path): bool => is_string($path) && is_file($path . '/capell.json'))
            ->map(fn (string $path): string => $path . '/capell.json')
            ->values()
            ->all());
    }
}
