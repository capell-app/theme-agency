<?php

declare(strict_types=1);

namespace Capell\PublicActions\Tests;

use Capell\Admin\Providers\AdminServiceProvider;
use Capell\Admin\Providers\Filament\AdminPanelProvider;
use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Providers\FrontendServiceProvider;
use Capell\PublicActions\Contracts\PublicActionWebhookHostResolver;
use Capell\PublicActions\Providers\PublicActionsServiceProvider;
use Capell\PublicActions\Support\PublicActionHandlerRegistry;
use Capell\PublicActions\Tests\Fakes\FakePublicActionHandler;
use Capell\PublicActions\Tests\Fakes\FakePublicActionWebhookHostResolver;
use Capell\PublicActions\Tests\Fakes\FakeValidationPublicActionHandler;
use Capell\Tests\AbstractTestCase;
use Illuminate\Foundation\Application;
use Livewire\LivewireServiceProvider;
use Override;

abstract class PublicActionsTestCase extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->singleton(
            PublicActionWebhookHostResolver::class,
            static fn (): FakePublicActionWebhookHostResolver => new FakePublicActionWebhookHostResolver,
        );

        $registry = resolve(PublicActionHandlerRegistry::class);
        $registry->register('test.handler', FakePublicActionHandler::class);
        $registry->register('test.validation-handler', FakeValidationPublicActionHandler::class);
    }

    protected function getPackageServiceName(): string
    {
        return 'capell-public-actions';
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
            AdminPanelProvider::class,
            AdminServiceProvider::class,
            FrontendServiceProvider::class,
            PublicActionsServiceProvider::class,
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
        CapellCore::forcePackageInstalled(PublicActionsServiceProvider::$packageName);
    }
}
