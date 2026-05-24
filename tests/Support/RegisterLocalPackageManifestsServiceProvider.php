<?php

declare(strict_types=1);

namespace Capell\Tests\Support;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Manifest\ManifestLoader;
use Capell\Core\Support\Manifest\ManifestValidator;
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
            ...$this->manifestPaths(__DIR__ . '/../../../capell-4/packages/{core,installer,marketplace}/capell.json'),
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
}
