<?php

declare(strict_types=1);

namespace Capell\Comments\Providers;

use Capell\Admin\Contracts\DashboardSettingsContributor;
use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Comments\Enums\ResourceEnum;
use Capell\Comments\Filament\Pages\CommentModerationInbox;
use Capell\Comments\Filament\Settings\Contributors\CommentsDashboardSettingsContributor;
use Capell\Comments\Filament\Widgets\CommentStatsFilamentWidget;
use Capell\Comments\Filament\Widgets\LatestCommentsFilamentWidget;
use Capell\Core\Facades\CapellCore;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this
            ->registerResources()
            ->registerPages()
            ->registerDashboardFilamentWidgets()
            ->registerDashboardSettingsContributor();
    }

    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(CommentsServiceProvider::$packageName);
    }

    private function registerResources(): self
    {
        foreach (ResourceEnum::cases() as $resource) {
            if (! class_exists($resource->value)) {
                continue;
            }

            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                class: $resource->value,
                group: $resource->name,
            ));
        }

        return $this;
    }

    private function registerPages(): self
    {
        CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::page(CommentModerationInbox::class));

        return $this;
    }

    private function registerDashboardFilamentWidgets(): self
    {
        CapellAdmin::registerDashboardFilamentWidget(CommentStatsFilamentWidget::class, DashboardEnum::Main);
        CapellAdmin::registerDashboardFilamentWidget(LatestCommentsFilamentWidget::class, DashboardEnum::Main);

        return $this;
    }

    private function registerDashboardSettingsContributor(): self
    {
        $this->app->tag([CommentsDashboardSettingsContributor::class], DashboardSettingsContributor::TAG);

        return $this;
    }
}
