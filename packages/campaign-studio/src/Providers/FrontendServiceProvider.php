<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Providers;

use Capell\CampaignStudio\Support\RenderHooks\RegisterCampaignTrackerHook;
use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\FrontendHookRegistrar;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class FrontendServiceProvider extends ServiceProvider
{
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
        resolve(FrontendHookRegistrar::class)->contribute(
            location: RenderHookLocation::BodyEnd,
            extension: new RegisterCampaignTrackerHook,
            owner: 'capell-app/campaign-studio',
            key: 'campaign-tracker',
            cacheSafe: false,
        );
    }
}
