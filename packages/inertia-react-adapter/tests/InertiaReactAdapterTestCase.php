<?php

declare(strict_types=1);

namespace Capell\InertiaReactAdapter\Tests;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Providers\CapellServiceProvider;
use Capell\Inertia\Providers\InertiaServiceProvider;
use Capell\InertiaReactAdapter\Providers\InertiaReactAdapterServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase;
use Override;

abstract class InertiaReactAdapterTestCase extends TestCase
{
    /**
     * @param  Application  $app
     * @return class-string[]
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            CapellServiceProvider::class,
            InertiaServiceProvider::class,
            InertiaReactAdapterServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        CapellCore::forcePackageInstalled(InertiaServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(InertiaReactAdapterServiceProvider::$packageName);
    }
}
