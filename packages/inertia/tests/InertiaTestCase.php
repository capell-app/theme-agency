<?php

declare(strict_types=1);

namespace Capell\Inertia\Tests;

use Capell\Api\Providers\ApiServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Inertia\Providers\InertiaServiceProvider;
use Capell\LayoutBuilder\LayoutBuilderServiceProvider;
use Capell\Tests\Packages\PackagesTestCase;
use Composer\Autoload\ClassLoader;
use Illuminate\Foundation\Application;
use Inertia\Middleware;
use Override;

$composerAutoloader = require dirname(__DIR__, 3) . '/vendor/autoload.php';

if ($composerAutoloader instanceof ClassLoader) {
    foreach (glob(dirname(__DIR__, 2) . '/*/composer.json') ?: [] as $packageComposerFile) {
        $composerManifest = json_decode((string) file_get_contents($packageComposerFile), true);

        if (! is_array($composerManifest)) {
            continue;
        }

        $autoloadNamespaces = $composerManifest['autoload']['psr-4'] ?? [];

        if (! is_array($autoloadNamespaces)) {
            continue;
        }

        foreach ($autoloadNamespaces as $namespace => $paths) {
            foreach ((array) $paths as $path) {
                if (! is_string($namespace)) {
                    continue;
                }

                if (! is_string($path)) {
                    continue;
                }

                $sourcePath = dirname($packageComposerFile) . '/' . $path;

                if (is_dir($sourcePath)) {
                    $composerAutoloader->addPsr4($namespace, $sourcePath);
                }
            }
        }
    }
}

if (! class_exists(Middleware::class)) {
    require_once __DIR__ . '/Fixtures/InertiaLaravelStubs.php';
}

abstract class InertiaTestCase extends PackagesTestCase
{
    /**
     * @param  Application  $app
     * @return class-string[]
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ...parent::getPackageProviders($app),
            LayoutBuilderServiceProvider::class,
            ApiServiceProvider::class,
            InertiaServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        CapellCore::forcePackageInstalled(LayoutBuilderServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(ApiServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(InertiaServiceProvider::$packageName);
    }
}
