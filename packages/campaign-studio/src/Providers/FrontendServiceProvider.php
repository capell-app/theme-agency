<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Providers;

use Capell\CampaignStudio\Support\RenderHooks\RegisterCampaignTrackerHook;
use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Data\RenderHookContext;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use WeakMap;

final class FrontendServiceProvider extends ServiceProvider
{
    /** @var WeakMap<RenderHookRegistry<RenderHookContext>, true>|null */
    private ?WeakMap $frontendRenderHookRegistries = null;

    public function boot(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        Blade::anonymousComponentNamespace('Capell\\CampaignStudio\\View\\Components');

        $this->registerRenderHooks();
    }

    private function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(CampaignStudioServiceProvider::$packageName);
    }

    private function registerRenderHooks(): void
    {
        $this->app->afterResolving(
            RenderHookRegistry::class,
            function (RenderHookRegistry $registry): void {
                $this->registerRenderHooksForRegistry($registry);
            },
        );

        if ($this->app->bound(RenderHookRegistry::class)) {
            $this->registerRenderHooksForRegistry($this->app->make(RenderHookRegistry::class));
        }
    }

    /** @param RenderHookRegistry<RenderHookContext> $registry */
    private function registerRenderHooksForRegistry(RenderHookRegistry $registry): void
    {
        $this->frontendRenderHookRegistries ??= new WeakMap;

        if (isset($this->frontendRenderHookRegistries[$registry])) {
            return;
        }

        $this->frontendRenderHookRegistries[$registry] = true;

        (new RegisterCampaignTrackerHook($registry))->register();
    }
}
