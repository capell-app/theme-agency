<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Providers;

use Capell\Admin\Data\AdminSurfaceContributionData;
use Capell\Admin\Data\MarketingStudioActionData;
use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Enums\MarketingStudioSectionEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\CampaignStudio\Actions\BuildCampaignOverviewStatsAction;
use Capell\CampaignStudio\Enums\CampaignWidgetConfiguratorEnum;
use Capell\CampaignStudio\Enums\ResourceEnum;
use Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\CampaignConversionGoalResource;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\CampaignCtaWidgetResource;
use Capell\CampaignStudio\Filament\Resources\CampaignGroups\CampaignGroupResource;
use Capell\CampaignStudio\Filament\Resources\CampaignLandingPages\CampaignLandingPageResource;
use Capell\CampaignStudio\Filament\Widgets\TopCampaignStudioWidget;
use Capell\CampaignStudio\Filament\Widgets\TopLandingPagesWidget;
use Capell\Core\Facades\CapellCore;
use Capell\LayoutBuilder\Enums\ConfiguratorTypeEnum;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Override;

final class AdminServiceProvider extends ServiceProvider
{
    private const string REQUEST_CAMPAIGN_OVERVIEW_CACHE_KEY = 'capell.campaign-studio.admin.overview';

    private const string LAYOUT_BUILDER_CONFIGURATOR_TYPE_ENUM = ConfiguratorTypeEnum::class;

    #[Override]
    public function register(): void
    {
        $this->app->booting(function (): void {
            if (! $this->isPackageInstalled()) {
                return;
            }

            $this
                ->registerResources()
                ->registerConfigurators();
        });
    }

    public function boot(): void
    {
        if (! $this->isPackageInstalled()) {
            return;
        }

        $this
            ->registerOverviewStats()
            ->registerDashboardWidgets()
            ->registerMarketingStudioActions();
    }

    private function isPackageInstalled(): bool
    {
        return CapellCore::isPackageInstalled(CampaignStudioServiceProvider::$packageName);
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

    private function registerConfigurators(): self
    {
        if (! enum_exists(self::LAYOUT_BUILDER_CONFIGURATOR_TYPE_ENUM)) {
            return $this;
        }

        foreach (CampaignWidgetConfiguratorEnum::cases() as $configurator) {
            $configuratorClass = $configurator->value;

            if (! class_exists($configuratorClass)) {
                continue;
            }

            CapellAdmin::contributeToAdminSurface(AdminSurfaceContributionData::configurator(
                class: $configuratorClass,
                group: self::LAYOUT_BUILDER_CONFIGURATOR_TYPE_ENUM::Widget->value,
                name: $configuratorClass::getKey(),
            ));
        }

        return $this;
    }

    private function registerDashboardWidgets(): self
    {
        CapellAdmin::registerDashboardWidget(TopCampaignStudioWidget::class, DashboardEnum::Main, DashboardEnum::MarketingStudio);
        CapellAdmin::registerDashboardWidget(TopLandingPagesWidget::class, DashboardEnum::Main, DashboardEnum::MarketingStudio);

        return $this;
    }

    private function registerMarketingStudioActions(): self
    {
        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'campaign-studio.campaigns',
            label: fn (): string => __('capell-campaign-studio::navigation.campaign_groups'),
            url: fn (): string => CampaignGroupResource::getUrl(),
            section: MarketingStudioSectionEnum::Campaigns,
            icon: 'heroicon-o-megaphone',
            sort: 10,
            description: fn (): string => __('capell-campaign-studio::generic.campaign_groups'),
        ));

        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'campaign-studio.landing-pages',
            label: fn (): string => __('capell-campaign-studio::navigation.landing_pages'),
            url: fn (): string => CampaignLandingPageResource::getUrl(),
            section: MarketingStudioSectionEnum::Campaigns,
            icon: 'heroicon-o-rectangle-group',
            sort: 20,
        ));

        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'campaign-studio.ctas',
            label: fn (): string => __('capell-campaign-studio::navigation.cta_widgets'),
            url: fn (): string => CampaignCtaWidgetResource::getUrl(),
            section: MarketingStudioSectionEnum::Advanced,
            icon: 'heroicon-o-cursor-arrow-rays',
            sort: 30,
        ));

        CapellAdmin::registerMarketingStudioAction(new MarketingStudioActionData(
            key: 'campaign-studio.conversion-goals',
            label: fn (): string => __('capell-campaign-studio::navigation.conversion_goals'),
            url: fn (): string => CampaignConversionGoalResource::getUrl(),
            section: MarketingStudioSectionEnum::Advanced,
            icon: 'heroicon-o-chart-bar',
            sort: 40,
        ));

        return $this;
    }

    private function registerOverviewStats(): self
    {
        CapellAdmin::registerOverviewStat(
            key: 'campaign_overview',
            label: fn (): string => __('capell-campaign-studio::widgets.active_campaign-studio'),
            value: fn (): int => $this->campaignOverview()['active_campaign-studio'],
            group: fn (): string => __('capell-admin::navigation.group_marketing'),
            sort: 150,
            settingsLabel: fn (): string => __('capell-campaign-studio::widgets.campaign_overview'),
        );

        CapellAdmin::registerOverviewStat(
            key: 'campaign_overview.conversions',
            label: fn (): string => __('capell-campaign-studio::widgets.conversions'),
            value: fn (): int => $this->campaignOverview()['conversions'],
            group: fn (): string => __('capell-admin::navigation.group_marketing'),
            sort: 151,
            settingsKey: 'campaign_overview',
            settingsLabel: fn (): string => __('capell-campaign-studio::widgets.campaign_overview'),
        );

        CapellAdmin::registerOverviewStat(
            key: 'campaign_overview.conversion_rate',
            label: fn (): string => __('capell-campaign-studio::widgets.conversion_rate'),
            value: fn (): string => $this->campaignOverview()['conversion_rate'] . '%',
            group: fn (): string => __('capell-admin::navigation.group_marketing'),
            sort: 152,
            settingsKey: 'campaign_overview',
            settingsLabel: fn (): string => __('capell-campaign-studio::widgets.campaign_overview'),
        );

        return $this;
    }

    /**
     * @return array{active_campaign-studio: int, conversions: int, conversion_rate: int|float|string}
     */
    private function campaignOverview(): array
    {
        $request = $this->currentRequest();

        if ($request instanceof Request) {
            $cachedOverview = $request->attributes->get(self::REQUEST_CAMPAIGN_OVERVIEW_CACHE_KEY);

            if (is_array($cachedOverview)) {
                /** @var array{active_campaign-studio: int, conversions: int, conversion_rate: int|float|string} $cachedOverview */
                return $cachedOverview;
            }
        }

        $now = CarbonImmutable::now();
        $rangeStart = $now->startOfWeek();
        $rangeEnd = $now->endOfWeek();
        /** @var array{active_campaign-studio: int, conversions: int, conversion_rate: int|float|string} $overview */
        $overview = BuildCampaignOverviewStatsAction::run($rangeStart, $rangeEnd);

        if ($request instanceof Request) {
            $request->attributes->set(self::REQUEST_CAMPAIGN_OVERVIEW_CACHE_KEY, $overview);
        }

        return $overview;
    }

    private function currentRequest(): ?Request
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request instanceof Request ? $request : null;
    }
}
