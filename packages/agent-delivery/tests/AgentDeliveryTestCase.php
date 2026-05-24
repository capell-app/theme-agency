<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Tests;

use Capell\AgentDelivery\Providers\AgentDeliveryServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Tests\Packages\PackagesTestCase;
use Illuminate\Foundation\Application;
use Override;

abstract class AgentDeliveryTestCase extends PackagesTestCase
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
            AgentDeliveryServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        CapellCore::forcePackageInstalled(AgentDeliveryServiceProvider::$packageName);
    }
}
