<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\CapellAdminManager;
use Capell\Core\Facades\CapellCore;
use Capell\CustomerPortal\Enums\ResourceEnum;
use Illuminate\Support\ServiceProvider;
use Override;

final class AdminServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->booted(function (): void {
            if (! CapellCore::isPackageInstalled(CustomerPortalServiceProvider::$packageName) || ! $this->app->bound(CapellAdminManager::class)) {
                return;
            }

            foreach (ResourceEnum::cases() as $resource) {
                CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                    class: $resource->value,
                    group: $resource->name,
                ));
            }
        });
    }
}
