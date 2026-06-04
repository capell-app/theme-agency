<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookingsVue\Tests;

use Capell\Core\Facades\CapellCore;
use Capell\Core\Providers\CapellServiceProvider;
use Capell\ThemeStudio\InertiaBookingsVue\Providers\InertiaBookingsVueServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase;
use Override;

abstract class InertiaBookingsVueTestCase extends TestCase
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
            InertiaBookingsVueServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        CapellCore::forcePackageInstalled(InertiaBookingsVueServiceProvider::$packageName);
    }
}
