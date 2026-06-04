<?php

declare(strict_types=1);

namespace Capell\FrontendOptimizer\Tests;

use Capell\Admin\Providers\AdminServiceProvider as CapellAdminServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Providers\FrontendServiceProvider;
use Capell\FrontendOptimizer\Providers\FrontendOptimizerServiceProvider;
use Capell\Tests\AbstractTestCase;
use Livewire\LivewireServiceProvider;
use Override;

abstract class FrontendOptimizerTestCase extends AbstractTestCase
{
    protected function getPackageServiceName(): string
    {
        return 'capell-frontend-optimizer';
    }

    /** @return array<int, class-string> */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ...parent::getPackageProviders($app),
            CapellAdminServiceProvider::class,
            FrontendServiceProvider::class,
            LivewireServiceProvider::class,
            FrontendOptimizerServiceProvider::class,
        ];
    }

    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        CapellCore::forcePackageInstalled(CapellAdminServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(FrontendServiceProvider::$packageName);
        CapellCore::registerPackage(
            FrontendOptimizerServiceProvider::$packageName,
            path: realpath(__DIR__ . '/../') ?: null,
        );
        CapellCore::forcePackageInstalled(FrontendOptimizerServiceProvider::$packageName);
    }
}
