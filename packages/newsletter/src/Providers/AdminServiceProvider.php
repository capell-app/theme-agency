<?php

declare(strict_types=1);

namespace Capell\Newsletter\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Data\MarketingStudioActionData;
use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Enums\MarketingStudioSectionEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Admin\Support\SiteScope;
use Capell\Core\Facades\CapellCore;
use Capell\Newsletter\Console\Commands\RequeueDueProviderSyncAttemptsCommand;
use Capell\Newsletter\Enums\ResourceEnum;
use Capell\Newsletter\Enums\SubscriberStatus;
use Capell\Newsletter\Enums\SyncStatus;
use Capell\Newsletter\Filament\Resources\FormMappings\FormMappingResource;
use Capell\Newsletter\Filament\Resources\ImportBatches\ImportBatchResource;
use Capell\Newsletter\Filament\Resources\NewsletterTags\NewsletterTagResource;
use Capell\Newsletter\Filament\Resources\ProviderAudiences\ProviderAudienceResource;
use Capell\Newsletter\Filament\Resources\ProviderConnections\ProviderConnectionResource;
use Capell\Newsletter\Filament\Resources\ProviderInterestMappings\ProviderInterestMappingResource;
use Capell\Newsletter\Filament\Resources\Segments\SegmentResource;
use Capell\Newsletter\Filament\Resources\Subscribers\SubscriberResource;
use Capell\Newsletter\Filament\Resources\SyncAttempts\SyncAttemptResource;
use Capell\Newsletter\Filament\Widgets\NewsletterOverviewStatsWidget;
use Capell\Newsletter\Models\FormMapping;
use Capell\Newsletter\Models\ImportBatch;
use Capell\Newsletter\Models\ProviderAudience;
use Capell\Newsletter\Models\ProviderConnection;
use Capell\Newsletter\Models\ProviderInterestMapping;
use Capell\Newsletter\Models\Segment;
use Capell\Newsletter\Models\Subscriber;
use Capell\Newsletter\Models\SyncAttempt;
use Capell\Newsletter\Policies\FormMappingPolicy;
use Capell\Newsletter\Policies\ImportBatchPolicy;
use Capell\Newsletter\Policies\ProviderAudiencePolicy;
use Capell\Newsletter\Policies\ProviderConnectionPolicy;
use Capell\Newsletter\Policies\ProviderInterestMappingPolicy;
use Capell\Newsletter\Policies\SegmentPolicy;
use Capell\Newsletter\Policies\SubscriberPolicy;
use Capell\Newsletter\Policies\SyncAttemptPolicy;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Override;

class AdminServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->booting(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerPolicies()
                ->registerResources();
        });
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'capell-newsletter');

        if (! $this->isPackageInstalled()) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->commands([RequeueDueProviderSyncAttemptsCommand::class]);
        }

        $this
            ->registerOverviewStats()
            ->registerDashboardWidgets()
            ->registerMarketingStudioActions();

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('newsletter:sync-retry-due')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->onOneServer();
        });
    }

    protected function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(NewsletterServiceProvider::$packageName);
    }

    private function registerOverviewStats(): self
    {
        CapellAdmin::registerOverviewStat(
            key: 'newsletter_overview',
            label: fn (): string => __('capell-newsletter::widgets.subscribed'),
            value: fn (): int => SiteScope::applyForCurrentActor(Subscriber::query())
                ->where('status', SubscriberStatus::Subscribed)
                ->count(),
            group: fn (): string => __('capell-newsletter::settings.fieldset'),
            sort: 140,
            settingsLabel: fn (): string => __('capell-newsletter::widgets.overview'),
        );

        CapellAdmin::registerOverviewStat(
            key: 'newsletter_overview.pending',
            label: fn (): string => __('capell-newsletter::widgets.pending'),
            value: fn (): int => SiteScope::applyForCurrentActor(Subscriber::query())
                ->where('status', SubscriberStatus::Pending)
                ->count(),
            group: fn (): string => __('capell-newsletter::settings.fieldset'),
            sort: 141,
            settingsKey: 'newsletter_overview',
            settingsLabel: fn (): string => __('capell-newsletter::widgets.overview'),
        );

        CapellAdmin::registerOverviewStat(
            key: 'newsletter_overview.sync_failures',
            label: fn (): string => __('capell-newsletter::widgets.sync_failures'),
            value: fn (): int => SyncAttempt::query()
                ->whereIn('sync_status', [
                    SyncStatus::Failed,
                    SyncStatus::RetryScheduled,
                ])
                ->whereHas('subscriber', function (Builder $query): void {
                    SiteScope::applyForCurrentActor($query);
                })
                ->count(),
            group: fn (): string => __('capell-newsletter::settings.fieldset'),
            color: 'danger',
            sort: 142,
            settingsKey: 'newsletter_overview',
            settingsLabel: fn (): string => __('capell-newsletter::widgets.overview'),
        );

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

    private function registerPolicies(): self
    {
        Gate::policy(FormMapping::class, FormMappingPolicy::class);
        Gate::policy(ImportBatch::class, ImportBatchPolicy::class);
        Gate::policy(ProviderAudience::class, ProviderAudiencePolicy::class);
        Gate::policy(ProviderConnection::class, ProviderConnectionPolicy::class);
        Gate::policy(ProviderInterestMapping::class, ProviderInterestMappingPolicy::class);
        Gate::policy(Segment::class, SegmentPolicy::class);
        Gate::policy(Subscriber::class, SubscriberPolicy::class);
        Gate::policy(SyncAttempt::class, SyncAttemptPolicy::class);

        return $this;
    }

    private function registerDashboardWidgets(): self
    {
        CapellAdmin::registerDashboardWidget(NewsletterOverviewStatsWidget::class, DashboardEnum::MarketingStudio);

        return $this;
    }

    private function registerMarketingStudioActions(): self
    {
        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'newsletter.subscribers',
            label: fn (): string => __('capell-newsletter::navigation.subscribers'),
            url: fn (): string => SubscriberResource::getUrl(),
            section: MarketingStudioSectionEnum::Audience,
            icon: 'heroicon-o-envelope',
            sort: 10,
        ));

        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'newsletter.segments',
            label: fn (): string => __('capell-newsletter::navigation.segments'),
            url: fn (): string => SegmentResource::getUrl(),
            section: MarketingStudioSectionEnum::Audience,
            icon: 'heroicon-o-users',
            sort: 20,
        ));

        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'newsletter.tags',
            label: fn (): string => __('capell-newsletter::navigation.newsletter_tags'),
            url: fn (): string => NewsletterTagResource::getUrl(),
            section: MarketingStudioSectionEnum::Audience,
            icon: 'heroicon-o-tag',
            sort: 30,
        ));

        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'newsletter.imports',
            label: fn (): string => __('capell-newsletter::navigation.import_batches'),
            url: fn (): string => ImportBatchResource::getUrl(),
            section: MarketingStudioSectionEnum::Audience,
            icon: 'heroicon-o-arrow-up-tray',
            sort: 40,
        ));

        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'newsletter.provider-connections',
            label: fn (): string => __('capell-newsletter::navigation.provider_connections'),
            url: fn (): string => ProviderConnectionResource::getUrl(),
            section: MarketingStudioSectionEnum::Advanced,
            icon: 'heroicon-o-globe-alt',
            sort: 10,
        ));

        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'newsletter.provider-audiences',
            label: fn (): string => __('capell-newsletter::navigation.provider_audiences'),
            url: fn (): string => ProviderAudienceResource::getUrl(),
            section: MarketingStudioSectionEnum::Advanced,
            icon: 'heroicon-o-user-group',
            sort: 15,
        ));

        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'newsletter.form-mappings',
            label: fn (): string => __('capell-newsletter::navigation.form_mappings'),
            url: fn (): string => FormMappingResource::getUrl(),
            section: MarketingStudioSectionEnum::Advanced,
            icon: 'heroicon-o-link',
            sort: 20,
        ));

        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'newsletter.interest-mappings',
            label: fn (): string => __('capell-newsletter::navigation.provider_interest_mappings'),
            url: fn (): string => ProviderInterestMappingResource::getUrl(),
            section: MarketingStudioSectionEnum::Advanced,
            icon: 'heroicon-o-adjustments-horizontal',
            sort: 30,
        ));

        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'newsletter.sync-attempts',
            label: fn (): string => __('capell-newsletter::navigation.sync_attempts'),
            url: fn (): string => SyncAttemptResource::getUrl(),
            section: MarketingStudioSectionEnum::Advanced,
            icon: 'heroicon-o-arrow-path',
            sort: 40,
        ));

        return $this;
    }
}
