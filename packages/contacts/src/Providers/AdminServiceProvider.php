<?php

declare(strict_types=1);

namespace Capell\Contacts\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\CapellAdminManager;
use Capell\Contacts\Enums\ResourceEnum;
use Capell\Contacts\Filament\Widgets\ContactsOverviewStatsFilamentWidget;
use Capell\Contacts\Models\Contact;
use Capell\Contacts\Models\ContactActivity;
use Capell\Contacts\Models\Lead;
use Capell\Contacts\Models\Organisation;
use Capell\Contacts\Policies\ContactActivityPolicy;
use Capell\Contacts\Policies\ContactPolicy;
use Capell\Contacts\Policies\LeadPolicy;
use Capell\Contacts\Policies\OrganisationPolicy;
use Capell\Core\Facades\CapellCore;
use Illuminate\Support\Facades\Gate;
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

            $this
                ->registerPolicies()
                ->registerResources()
                ->registerDashboardFilamentWidgets();
        });
    }

    private function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(ContactsServiceProvider::$packageName);
    }

    private function registerPolicies(): self
    {
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(Organisation::class, OrganisationPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(ContactActivity::class, ContactActivityPolicy::class);

        return $this;
    }

    private function registerResources(): self
    {
        foreach (ResourceEnum::cases() as $resource) {
            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::resource(
                class: $resource->value,
                group: $resource->name,
            ));
        }

        return $this;
    }

    private function registerDashboardFilamentWidgets(): self
    {
        CapellAdmin::registerDashboardFilamentWidget(ContactsOverviewStatsFilamentWidget::class, DashboardEnum::Main);

        return $this;
    }
}
