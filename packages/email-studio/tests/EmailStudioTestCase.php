<?php

declare(strict_types=1);

namespace Capell\EmailStudio\Tests;

use Capell\Admin\Providers\AdminServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\EmailStudio\Providers\EmailStudioServiceProvider;
use Capell\EmailStudio\Settings\EmailStudioSettingsMigrationProvider;
use Capell\Frontend\Providers\FrontendServiceProvider;
use Capell\Tests\AbstractTestCase;
use Illuminate\Foundation\Application;
use jdavidbakr\MailTracker\MailTrackerServiceProvider;
use Livewire\LivewireServiceProvider;
use Override;

abstract class EmailStudioTestCase extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var EmailStudioSettingsMigrationProvider $emailStudioSettingsMigrationProvider */
        $emailStudioSettingsMigrationProvider = resolve(EmailStudioSettingsMigrationProvider::class);

        $this->registerAndMigrateSettings(
            $emailStudioSettingsMigrationProvider->getSettingMigrations(),
            $emailStudioSettingsMigrationProvider->path(),
        );
    }

    protected function getPackageServiceName(): string
    {
        return 'capell-email-studio';
    }

    /**
     * @param  Application  $app
     * @return class-string[]
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ...parent::getPackageProviders($app),
            AdminServiceProvider::class,
            FrontendServiceProvider::class,
            MailTrackerServiceProvider::class,
            EmailStudioServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    #[Override]
    protected function getEnvironmentSetUp(mixed $app): void
    {
        parent::getEnvironmentSetUp($app);

        CapellCore::forcePackageInstalled(AdminServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(FrontendServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(EmailStudioServiceProvider::$packageName);
    }
}
