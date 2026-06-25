<?php

declare(strict_types=1);

namespace Capell\AiCreator\Tests;

use Capell\Admin\Providers\AdminServiceProvider;
use Capell\AgentBridge\Providers\AgentBridgeServiceProvider;
use Capell\AiCreator\Providers\AiCreatorServiceProvider;
use Capell\AIOrchestrator\Providers\AIOrchestratorServiceProvider;
use Capell\Core\Facades\CapellCore;
use Capell\FoundationTheme\Providers\FoundationThemeServiceProvider;
use Capell\Frontend\Providers\FrontendServiceProvider;
use Capell\LayoutBuilder\LayoutBuilderServiceProvider;
use Capell\Tests\AbstractTestCase;
use Illuminate\Foundation\Application;
use Livewire\LivewireServiceProvider;
use Override;
use Spatie\EventSourcing\EventSourcingServiceProvider;

class AiCreatorTestCase extends AbstractTestCase
{
    protected function getPackageServiceName(): string
    {
        return 'capell-ai-creator';
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
            EventSourcingServiceProvider::class,
            AdminServiceProvider::class,
            AgentBridgeServiceProvider::class,
            AIOrchestratorServiceProvider::class,
            AiCreatorServiceProvider::class,
            LayoutBuilderServiceProvider::class,
            FrontendServiceProvider::class,
            FoundationThemeServiceProvider::class,
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
        CapellCore::forcePackageInstalled(AgentBridgeServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(AIOrchestratorServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(AiCreatorServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(LayoutBuilderServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(FrontendServiceProvider::$packageName);
        CapellCore::forcePackageInstalled(FoundationThemeServiceProvider::$packageName);
    }
}
