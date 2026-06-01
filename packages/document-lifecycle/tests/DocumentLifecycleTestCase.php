<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Tests;

use Capell\Admin\Providers\AdminServiceProvider;
use Capell\Admin\Providers\Filament\AdminPanelProvider;
use Capell\Core\Facades\CapellCore;
use Capell\CustomerPortal\Providers\CustomerPortalServiceProvider;
use Capell\DocumentLifecycle\Providers\DocumentLifecycleServiceProvider;
use Capell\PublishingStudio\Providers\PublishingStudioServiceProvider;
use Capell\Tests\AbstractTestCase;
use Illuminate\Foundation\Application;
use Livewire\LivewireServiceProvider;
use Override;

abstract class DocumentLifecycleTestCase extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerAndMigrateSettings(
            CapellCore::getSettingMigrations(),
            __DIR__ . '/../../../vendor/capell-app/core/database/settings',
        );
    }

    protected function getPackageServiceName(): string
    {
        return 'capell-document-lifecycle';
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
            AdminPanelProvider::class,
            CustomerPortalServiceProvider::class,
            PublishingStudioServiceProvider::class,
            DocumentLifecycleServiceProvider::class,
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
        CapellCore::forcePackageInstalled(CustomerPortalServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(PublishingStudioServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(DocumentLifecycleServiceProvider::$packageName);
    }
}
