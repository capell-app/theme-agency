<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Facades\CapellCore;
use Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\SocialFeedConnectionResource;
use Capell\SocialFeeds\Filament\Resources\SocialFeedItems\SocialFeedItemResource;
use Illuminate\Support\ServiceProvider;
use Override;

final class AdminServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->registerAdminSurface();
    }

    public function boot(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this->registerAdminSurface();
    }

    private function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(SocialFeedsServiceProvider::$packageName);
    }

    private function registerAdminSurface(): void
    {
        if (! class_exists(CapellAdmin::class)) {
            return;
        }

        CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
            class: SocialFeedConnectionResource::class,
            group: 'SocialFeedConnection',
        ));

        CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
            class: SocialFeedItemResource::class,
            group: 'SocialFeedItem',
        ));
    }
}
