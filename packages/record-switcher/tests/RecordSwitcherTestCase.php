<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Tests;

use Capell\Core\Facades\CapellCore;
use Capell\RecordSwitcher\Providers\RecordSwitcherServiceProvider;
use Capell\Tests\Packages\PackagesTestCase;
use Illuminate\Foundation\Application;
use Override;

abstract class RecordSwitcherTestCase extends PackagesTestCase
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
            RecordSwitcherServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        CapellCore::forcePackageInstalled(RecordSwitcherServiceProvider::$packageName);
    }

    #[Override]
    protected function getPackageServiceName(): string
    {
        return 'capell-record-switcher';
    }
}
