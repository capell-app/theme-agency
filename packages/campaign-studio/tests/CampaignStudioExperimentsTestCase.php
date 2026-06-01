<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Tests;

use Capell\Core\Facades\CapellCore;
use Capell\Experiments\Providers\ExperimentsServiceProvider;
use Illuminate\Foundation\Application;
use Override;

abstract class CampaignStudioExperimentsTestCase extends CampaignStudioTestCase
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
            ExperimentsServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        CapellCore::forcePackageInstalled(ExperimentsServiceProvider::$packageName);
    }
}
