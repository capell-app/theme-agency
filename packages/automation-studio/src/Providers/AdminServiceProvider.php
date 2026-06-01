<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\CapellAdminManager;
use Capell\AutomationStudio\Enums\ResourceEnum;
use Capell\Core\Facades\CapellCore;
use Illuminate\Support\ServiceProvider;
use Override;

final class AdminServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->booted(function (): void {
            if (! $this->isPackageInstalled() || ! $this->app->bound(CapellAdminManager::class)) {
                return;
            }

            $this->registerResources();
        });
    }

    private function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(AutomationStudioServiceProvider::$packageName);
    }

    private function registerResources(): void
    {
        foreach (ResourceEnum::cases() as $resource) {
            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                class: $resource->value,
                group: $resource->name,
            ));
        }
    }
}
